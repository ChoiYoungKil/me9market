<?php

namespace App\Services;

use App\Mail\ShopOrderConfirmation;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelPrivateAccess;
use App\Models\ShopChannelProduct;
use App\Models\VisitedChannel;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class ShopChannelRuntime
{
    private const CART_KEY = 'shop_channel_cart';

    private const CHANNEL_KEY = 'shop_channel_id';

    private const PRIVATE_ACCESS_KEY = 'shop_channel_private_access_id';

    public function seedDemoDataIfAllowed(): ?ShopChannel
    {
        return null;
    }

    public function currentChannel(): ShopChannel
    {
        $shop = Session::has(self::CHANNEL_KEY)
            ? ShopChannel::find(Session::get(self::CHANNEL_KEY))
            : null;

        abort_unless($shop && $this->isChannelAvailable($shop), 403, 'Shop 채널 입장이 필요합니다.');

        return $shop;
    }

    public function hasActiveChannelAccess(): bool
    {
        if (! Session::has(self::CHANNEL_KEY)) {
            return false;
        }

        $shop = ShopChannel::find(Session::get(self::CHANNEL_KEY));
        if (! $shop || ! $this->isChannelAvailable($shop)) {
            $this->leaveChannel();

            return false;
        }

        if ((int) $shop->is_public === 1) {
            return true;
        }

        $accessId = Session::get(self::PRIVATE_ACCESS_KEY);

        return $accessId && ShopChannelPrivateAccess::whereKey($accessId)
            ->where('shop_channel_id', $shop->id)
            ->exists();
    }

    public function leaveChannel(): void
    {
        Session::forget([self::CHANNEL_KEY, self::PRIVATE_ACCESS_KEY, self::CART_KEY]);
    }

    public function enterChannel(string $entryCode): ?ShopChannel
    {
        $shop = ShopChannel::where('channel_code', trim($entryCode))->first();

        if (! $shop || ! $this->isChannelAvailable($shop) || (int) $shop->is_public !== 1) {
            return null;
        }

        Session::forget(self::PRIVATE_ACCESS_KEY);

        Session::put(self::CHANNEL_KEY, $shop->id);
        $this->recordAuthenticatedVisit($shop);

        return $shop;
    }

    public function enterPrivateAccess(ShopChannelPrivateAccess $access): ?ShopChannel
    {
        $access->loadMissing('shopChannel');
        if (! $access->shopChannel || ! $this->isChannelAvailable($access->shopChannel) || (int) $access->shopChannel->is_public !== 0) {
            return null;
        }
        $access->forceFill([
            'first_accessed_at' => $access->first_accessed_at ?: now(),
            'access_count' => (int) $access->access_count + 1,
        ])->save();

        Session::put(self::PRIVATE_ACCESS_KEY, $access->id);
        Session::put(self::CHANNEL_KEY, $access->shopChannel->id);
        $this->recordAuthenticatedVisit($access->shopChannel);

        return $access->shopChannel;
    }

    public function recordAuthenticatedVisit(?ShopChannel $shop = null): void
    {
        if (! Auth::id()) {
            return;
        }
        $shop = $shop ?: $this->currentChannel();

        $visit = VisitedChannel::where('user_id', Auth::id())
            ->where('vendor_id', $shop->vendor_id)
            ->where(function ($query) use ($shop) {
                $query->where('shop_channel_id', $shop->id)->orWhereNull('shop_channel_id');
            })
            ->first();
        if ($visit) {
            $visit->shop_channel_id = $shop->id;
            $visit->touch();
            $visit->save();
        } else {
            VisitedChannel::create([
                'user_id' => Auth::id(),
                'vendor_id' => $shop->vendor_id,
                'shop_channel_id' => $shop->id,
            ]);
        }

        app(ChannelPointService::class)->recordFirstVisit($shop, (int) Auth::id());
    }

    private function isChannelAvailable(ShopChannel $shop): bool
    {
        if ((int) $shop->status !== 1 || $shop->closure_status === 'approved') {
            return false;
        }
        if ((int) $shop->use_period_type === 1) {
            if ($shop->start_at && now()->lt($shop->start_at)) {
                return false;
            }
            if ($shop->end_at && now()->gt($shop->end_at)) {
                return false;
            }
        }

        return true;
    }

    public function products(?string $type = null)
    {
        $shop = $this->currentChannel();
        $query = ShopChannelProduct::with(['product.images', 'shopChannel'])
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved');

        if ($type) {
            $query->where('product_type', $type);
        }

        return $query->orderBy('id')->get();
    }

    public function cartItems(): array
    {
        $shop = $this->currentChannel();
        $cart = Session::get(self::CART_KEY, []);
        $ids = array_keys($cart);
        $products = ShopChannelProduct::with(['product.images', 'shopChannel'])
            ->whereIn('id', $ids)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($cart as $id => $row) {
            if (! $products->has($id)) {
                unset($cart[$id]);

                continue;
            }

            $shopProduct = $products[$id];
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $jointPrice = app(JointPurchasePricingService::class)->projectedPriceForProduct((int) $shopProduct->product_id, $qty);
            $price = (float) ($jointPrice['unit_price'] ?? ($shopProduct->selling_price ?: $shopProduct->product_price));
            $items[] = [
                'id' => $id,
                'shop_product' => $shopProduct,
                'product' => $shopProduct->product,
                'joint_purchase' => $jointPrice['joint_purchase'] ?? null,
                'joint_price_tier_id' => $jointPrice['tier_id'] ?? null,
                'projected_joint_quantity' => $jointPrice['projected_quantity'] ?? null,
                'option' => $row['option'] ?? '기본옵션',
                'qty' => $qty,
                'price' => $price,
                'line_total' => $price * $qty,
            ];
        }

        Session::put(self::CART_KEY, $cart);

        return $items;
    }

    public function totals(): array
    {
        $subtotal = array_sum(array_column($this->cartItems(), 'line_total'));
        $shipping = $subtotal > 0 && $subtotal < 30000 ? 2500 : 0;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
        ];
    }

    public function addToCart(int $shopProductId, int $qty = 1, string $option = '기본옵션'): void
    {
        $shop = $this->currentChannel();

        $shopProduct = ShopChannelProduct::where('id', $shopProductId)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1))
            ->firstOrFail();

        $cart = Session::get(self::CART_KEY, []);
        $requestedQty = ($cart[$shopProduct->id]['qty'] ?? 0) + max(1, $qty);
        $this->validatePurchasableQuantity($shopProduct, $requestedQty);
        $cart[$shopProduct->id] = [
            'qty' => $requestedQty,
            'option' => $option ?: '기본옵션',
        ];

        Session::put(self::CART_KEY, $cart);
    }

    public function removeFromCart(int $shopProductId): void
    {
        $cart = Session::get(self::CART_KEY, []);
        unset($cart[$shopProductId]);
        Session::put(self::CART_KEY, $cart);
    }

    public function updateCart(int $shopProductId, int $qty, string $option): void
    {
        $shop = $this->currentChannel();
        $shopProduct = ShopChannelProduct::whereKey($shopProductId)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1))
            ->firstOrFail();

        $cart = Session::get(self::CART_KEY, []);
        abort_unless(isset($cart[$shopProductId]), 404);
        $this->validatePurchasableQuantity($shopProduct, $qty);
        $cart[$shopProductId] = [
            'qty' => max(1, $qty),
            'option' => trim($option) ?: '기본옵션',
        ];
        Session::put(self::CART_KEY, $cart);
    }

    public function checkout(Request $request): Order
    {
        $shop = $this->currentChannel();
        $items = $this->cartItems();
        if (empty($items)) {
            abort(422, '장바구니가 비어 있습니다.');
        }

        return DB::transaction(function () use ($request, $shop, $items) {
            $lockedProducts = ShopChannelProduct::with('product')
                ->whereIn('id', collect($items)->pluck('id'))
                ->where('shop_channel_id', $shop->id)
                ->where('status', 1)
                ->where('approval_status', 'approved')
                ->whereHas('product', fn ($query) => $query->where('status', 1))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($lockedProducts->count() !== count($items)) {
                throw ValidationException::withMessages([
                    'cart' => '판매가 중지되었거나 주문할 수 없는 상품이 포함되어 있습니다.',
                ]);
            }

            foreach ($items as &$item) {
                $shopProduct = $lockedProducts->get($item['id']);
                $this->validatePurchasableQuantity($shopProduct, (int) $item['qty']);
                $item['shop_product'] = $shopProduct;
                $item['product'] = $shopProduct->product;
            }
            unset($item);

            $subtotal = array_sum(array_column($items, 'line_total'));
            $totals = [
                'subtotal' => $subtotal,
                'shipping' => $subtotal > 0 && $subtotal < 30000 ? 2500 : 0,
                'total' => $subtotal + ($subtotal > 0 && $subtotal < 30000 ? 2500 : 0),
            ];

            $order = new Order;
            $order->user_id = Auth::id() ?: 0;
            $order->name = $request->input('name', Auth::user()->name ?? '비회원');
            $order->address = $request->input('address', '서울특별시 중구 세종대로 110');
            $order->city = $request->input('city', '서울특별시');
            $order->state = $request->input('state', '중구');
            $order->country = '대한민국';
            $order->pincode = $request->input('pincode', '04524');
            $order->mobile = $request->input('mobile', '010-0000-0000');
            $order->email = $request->input('email', Auth::user()->email ?? 'guest@me9.local');
            $order->shipping_charges = $totals['shipping'];
            $order->coupon_code = '';
            $order->coupon_amount = 0;
            $order->order_status = 'Payment Captured';
            $order->payment_method = $request->input('payment_method', 'Card');
            $order->payment_gateway = 'Me9 Mock Payment';
            $order->grand_total = $totals['total'];
            $order->save();

            $jointPurchaseIds = [];
            $createdItems = collect();
            foreach ($items as $item) {
                $shopProduct = $item['shop_product'];
                $product = $item['product'];
                $status = OrderItemStatus::PAID;
                $originalPrice = (float) ($shopProduct->selling_price ?: $shopProduct->product_price);
                $isJointPurchase = ! empty($item['joint_purchase']);
                $orderItem = OrdersProduct::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'vendor_id' => $shop->vendor_id,
                    'shop_channel_id' => $shop->id,
                    'shop_channel_product_id' => $shopProduct->id,
                    'joint_purchase_id' => $item['joint_purchase']->id ?? null,
                    'joint_price_tier_id' => $item['joint_price_tier_id'] ?? null,
                    'admin_id' => $product->admin_id ?? 0,
                    'product_id' => $product->id,
                    'distributor_id' => $shopProduct->distributor_id ?: $product->distributor_id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'product_color' => $product->product_color ?: '-',
                    'product_size' => $item['option'],
                    'product_price' => $item['price'],
                    'supply_price' => $shopProduct->product_price ?: $product->product_price,
                    'selling_price' => $item['price'],
                    'original_unit_price' => $isJointPurchase ? $originalPrice : null,
                    'original_line_total' => $isJointPurchase ? round($originalPrice * $item['qty'], 2) : null,
                    'repriced_unit_price' => $isJointPurchase ? $item['price'] : null,
                    'repriced_line_total' => $isJointPurchase ? $item['line_total'] : null,
                    'reprice_adjustment_amount' => $isJointPurchase ? round(($originalPrice * $item['qty']) - $item['line_total'], 2) : 0,
                    'reprice_status' => $isJointPurchase && $originalPrice != $item['price'] ? 'pending_repayment' : null,
                    'product_qty' => $item['qty'],
                    'line_total' => $item['line_total'],
                    'item_status' => OrderItemStatus::label($status),
                    'status_code' => $status,
                    'commission' => round($item['line_total'] * 0.1),
                    'settlement_status' => 'pending',
                ]);
                $createdItems->push($orderItem);

                if ($shopProduct->stock !== null) {
                    $shopProduct->stock = (int) $shopProduct->stock - (int) $item['qty'];
                    $shopProduct->save();
                }

                if ($isJointPurchase) {
                    $jointPurchaseIds[] = (int) $item['joint_purchase']->id;
                }
            }

            foreach (array_unique($jointPurchaseIds) as $jointPurchaseId) {
                app(JointPurchasePricingService::class)->repricePurchase($jointPurchaseId);
            }

            if (Session::has(self::PRIVATE_ACCESS_KEY)) {
                ShopChannelPrivateAccess::where('id', Session::get(self::PRIVATE_ACCESS_KEY))
                    ->where('shop_channel_id', $shop->id)
                    ->increment('purchase_count');
            }

            if ($shop->use_purchase_sms) {
                DB::afterCommit(fn () => app(ShopChannelSmsService::class)->send(
                    $shop,
                    $order,
                    $createdItems->first(),
                    ShopChannelSmsService::TYPE_PURCHASE
                ));
            }

            DB::afterCommit(function () use ($shop, $order, $createdItems) {
                try {
                    Mail::to($order->email, $order->name)
                        ->queue(new ShopOrderConfirmation($shop, $order, $createdItems));
                } catch (\Throwable $e) {
                    Log::error('Shop order email failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);
                }
            });

            Session::forget(self::CART_KEY);
            Session::put('last_shop_order_id', $order->id);
            Session::put('nonmember_order_id', $order->id);

            return $order;
        });
    }

    private function validatePurchasableQuantity(ShopChannelProduct $shopProduct, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['qty' => '주문 수량은 1개 이상이어야 합니다.']);
        }

        $purchaseLimit = (int) ($shopProduct->purchase_limit ?? 0);
        if ($purchaseLimit > 0 && $quantity > $purchaseLimit) {
            throw ValidationException::withMessages([
                'qty' => '이 상품은 한 번에 '.$purchaseLimit.'개까지 주문할 수 있습니다.',
            ]);
        }

        if ($shopProduct->stock !== null && $quantity > (int) $shopProduct->stock) {
            throw ValidationException::withMessages([
                'qty' => '재고가 부족합니다. 현재 주문 가능 수량은 '.max(0, (int) $shopProduct->stock).'개입니다.',
            ]);
        }
    }
}
