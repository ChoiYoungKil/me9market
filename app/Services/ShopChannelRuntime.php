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
        $query = ShopChannelProduct::with(['product' => fn ($query) => $query->with(['images', 'category'])->withCount('approvedRatings')->withAvg('approvedRatings', 'rating'), 'shopChannel'])
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1));

        if ($type) {
            $query->where('product_type', $type);
        }

        return $query->orderBy('id')->get();
    }

    public function cartItems(): array
    {
        $shop = $this->currentChannel();
        $cart = Session::get(self::CART_KEY, []);
        $ids = array_unique(array_map('intval', array_keys($cart)));
        $products = ShopChannelProduct::with(['product.images', 'product.attributes', 'shopChannel'])
            ->whereIn('id', $ids)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1))
            ->get()
            ->keyBy('id');

        $items = [];
        $quantities = [];
        foreach ($cart as $key => $row) {
            $quantities[(int) $key] = ($quantities[(int) $key] ?? 0) + max(1, (int) ($row['qty'] ?? 1));
        }
        foreach ($cart as $key => $row) {
            $id = (int) $key;
            if (! $products->has($id)) {
                unset($cart[$key]);

                continue;
            }

            $shopProduct = $products[$id];
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $jointPrice = app(JointPurchasePricingService::class)->projectedPriceForProduct((int) $shopProduct->product_id, $quantities[$id]);
            $price = (float) ($jointPrice['unit_price'] ?? ($shopProduct->selling_price ?: $shopProduct->product_price));
            $adjustment = (float) ($shopProduct->product->attributes->firstWhere('size', $row['option'] ?? '기본옵션')?->price_delta ?? 0);
            $price += $adjustment;
            $items[] = [
                'id' => $id,
                'key' => $key,
                'shop_product' => $shopProduct,
                'product' => $shopProduct->product,
                'joint_purchase' => $jointPrice['joint_purchase'] ?? null,
                'joint_price_tier_id' => $jointPrice['tier_id'] ?? null,
                'projected_joint_quantity' => $jointPrice['projected_quantity'] ?? null,
                'option' => $row['option'] ?? '기본옵션',
                'qty' => $qty,
                'price' => $price,
                'option_price_adjustment' => $adjustment,
                'line_total' => $price * $qty,
            ];
        }

        Session::put(self::CART_KEY, $cart);

        return $items;
    }

    public function totals(): array
    {
        return app(ShopOrderTotals::class)->calculate($this->cartItems());
    }

    public function addToCart(int $shopProductId, int $qty = 1, string $option = '기본옵션'): void
    {
        $this->addOptionsToCart($shopProductId, [['qty' => $qty, 'option' => $option]]);
    }

    public function addOptionsToCart(int $shopProductId, array $selections): void
    {
        $shop = $this->currentChannel();

        $shopProduct = ShopChannelProduct::where('id', $shopProductId)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1))
            ->firstOrFail();

        $cart = Session::get(self::CART_KEY, []);
        foreach ($selections as $selection) {
            $option = trim($selection['option'] ?? '') ?: '기본옵션';
            $this->validateOption($shopProduct, $option);
            $key = null;
            foreach ($cart as $existingKey => $row) {
                if ((int) $existingKey === $shopProductId && ($row['option'] ?? '기본옵션') === $option) {
                    $key = $existingKey;
                    break;
                }
            }
            if ($key === null) {
                $key = $shopProductId;
                $attempt = 0;
                // A row may have changed options while retaining its original key.
                while (isset($cart[$key])) {
                    $key = $shopProductId.':'.substr(hash('sha256', $option."\0".$attempt++), 0, 16);
                }
            }
            $cart[$key] = ['qty' => ($cart[$key]['qty'] ?? 0) + max(1, (int) $selection['qty']), 'option' => $option];
        }
        $quantity = collect($cart)->filter(fn ($row, $key) => (int) $key === $shopProductId)->sum('qty');
        $this->validatePurchasableQuantity($shopProduct, $quantity);

        foreach ($cart as $key => $row) {
            if ((int) $key === $shopProductId) {
                $this->validateOption($shopProduct, $row['option'], $row['qty']);
            }
        }

        Session::put(self::CART_KEY, $cart);
    }

    public function removeFromCart(int|string $cartKey): void
    {
        $cart = Session::get(self::CART_KEY, []);
        unset($cart[$cartKey]);
        Session::put(self::CART_KEY, $cart);
    }

    public function updateCart(int $shopProductId, int $qty, string $option, ?string $cartKey = null): void
    {
        $shop = $this->currentChannel();
        $shopProduct = ShopChannelProduct::whereKey($shopProductId)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1))
            ->firstOrFail();

        $cart = Session::get(self::CART_KEY, []);
        $key = $cartKey ?? (string) $shopProductId;
        abort_unless((int) $key === $shopProductId && isset($cart[$key]), 404);
        $option = trim($option) ?: '기본옵션';
        $this->validateOption($shopProduct, $option);
        $cart[$key] = [
            'qty' => max(1, $qty),
            'option' => $option,
        ];
        foreach ($cart as $otherKey => $row) {
            if ((string) $otherKey !== (string) $key && (int) $otherKey === $shopProductId && ($row['option'] ?? '기본옵션') === $option) {
                $cart[$key]['qty'] += $row['qty'];
                unset($cart[$otherKey]);
            }
        }
        $quantity = collect($cart)->filter(fn ($row, $key) => (int) $key === $shopProductId)->sum('qty');
        $this->validatePurchasableQuantity($shopProduct, $quantity);
        foreach ($cart as $rowKey => $row) {
            if ((int) $rowKey === $shopProductId) {
                $this->validateOption($shopProduct, $row['option'], $row['qty']);
            }
        }
        Session::put(self::CART_KEY, $cart);
    }

    public function canCheckout(): bool
    {
        return config('shop_channel.payment_driver') === 'mock' && app()->environment(['local', 'testing']);
    }

    public function canRegister(): bool
    {
        if (! app()->environment('production')) {
            return true;
        }
        foreach (['terms_url', 'privacy_url', 'third_party_url'] as $document) {
            $url = config('shop_channel.'.$document);
            if (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
                return false;
            }
        }

        return filled(config('shop_channel.terms_version'));
    }

    private function validateOption(ShopChannelProduct $shopProduct, string $option, int $quantity = 1): void
    {
        $attributes = $shopProduct->product->attributes;
        if ($attributes->isNotEmpty() && ! $attributes->contains(fn ($attribute) => (int) $attribute->status === 1 && $attribute->size === $option)) {
            throw ValidationException::withMessages(['option' => '판매 중인 상품 옵션을 선택해 주세요.']);
        }
        if ($shopProduct->product->stock_usage === 'used') {
            $attribute = $attributes->firstWhere('size', $option);
            if (! $attribute || (int) $attribute->stock < $quantity) {
                throw ValidationException::withMessages(['qty' => '선택한 옵션의 재고가 부족합니다.']);
            }
        }
    }

    public function checkout(Request $request): Order
    {
        if (! $this->canCheckout()) {
            throw ValidationException::withMessages(['payment_method' => '현재 결제를 이용할 수 없습니다. 판매자에게 문의해 주세요.']);
        }

        $shop = $this->currentChannel();
        $items = $this->cartItems();
        if (empty($items)) {
            abort(422, '장바구니가 비어 있습니다.');
        }

        return DB::transaction(function () use ($request, $shop, $items) {
            if (Auth::id()) {
                \App\Models\User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            }
            $lockedProducts = ShopChannelProduct::with('product')
                ->whereIn('id', collect($items)->pluck('id'))
                ->where('shop_channel_id', $shop->id)
                ->where('status', 1)
                ->where('approval_status', 'approved')
                ->whereHas('product', fn ($query) => $query->where('status', 1))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($lockedProducts->count() !== collect($items)->pluck('id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'cart' => '판매가 중지되었거나 주문할 수 없는 상품이 포함되어 있습니다.',
                ]);
            }

            $quantities = collect($items)->groupBy('id')->map(fn ($rows) => $rows->sum('qty'));
            $attributes = \App\Models\ProductsAttribute::whereIn('product_id', $lockedProducts->pluck('product_id'))
                ->orderBy('id')->lockForUpdate()->get()->groupBy('product_id');
            foreach ($lockedProducts as $lockedProduct) {
                $lockedProduct->product->setRelation('attributes', $attributes->get($lockedProduct->product_id, collect()));
            }
            $optionQuantities = collect($items)->groupBy(fn ($row) => $row['product']->id.':'.$row['option'])->map(fn ($rows) => $rows->sum('qty'));
            foreach ($items as &$item) {
                $shopProduct = $lockedProducts->get($item['id']);
                $this->validateOption($shopProduct, $item['option'], $optionQuantities[$shopProduct->product_id.':'.$item['option']]);
                $this->validatePurchasableQuantity($shopProduct, (int) $quantities[$item['id']]);
                $item['shop_product'] = $shopProduct;
                $item['product'] = $shopProduct->product;
                $jointPrice = app(JointPurchasePricingService::class)->projectedPriceForProduct((int) $shopProduct->product_id, (int) $quantities[$item['id']]);
                $item['joint_purchase'] = $jointPrice['joint_purchase'] ?? null;
                $item['joint_price_tier_id'] = $jointPrice['tier_id'] ?? null;
                $item['price'] = (float) ($jointPrice['unit_price'] ?? ($shopProduct->selling_price ?: $shopProduct->product_price));
                $item['option_price_adjustment'] = (float) ($shopProduct->product->attributes->firstWhere('size', $item['option'])?->price_delta ?? 0);
                $item['price'] += $item['option_price_adjustment'];
                if ($item['price'] < 0) {
                    throw ValidationException::withMessages(['option' => '옵션 적용 가격을 확인해 주세요.']);
                }
                $item['line_total'] = $item['price'] * $item['qty'];
            }
            unset($item);

            $totals = app(ShopOrderTotals::class)->calculate($items);
            $pointAllocations = app(CustomerPointService::class)->allocateForCheckout(
                (int) Auth::id(), (int) $shop->id,
                (int) $request->input('channel_points', 0), (int) $request->input('me9_points', 0), $items, $totals
            );
            $usedPoints = (int) $request->input('channel_points', 0) + (int) $request->input('me9_points', 0);

            $order = new Order;
            $order->user_id = Auth::id() ?: 0;
            $order->name = $request->input('name', Auth::user()->name ?? '비회원');
            $order->address = $request->input('address', '서울특별시 중구 세종대로 110');
            $order->city = $request->input('city') ?? '';
            $order->state = $request->input('state') ?? '';
            $order->country = '대한민국';
            $order->pincode = $request->input('pincode', '04524');
            $order->mobile = $request->input('mobile', '010-0000-0000');
            $order->email = $request->input('email', Auth::user()->email ?? 'guest@me9.local');
            $order->buyer_name = $request->input('buyer_name') ?: $order->name;
            $order->buyer_mobile = $request->input('buyer_mobile') ?: $order->mobile;
            $order->delivery_memo = $request->input('delivery_memo');
            $order->order_confirmed_at = now();
            $order->shipping_charges = $totals['shipping'];
            $order->coupon_code = '';
            $order->coupon_amount = 0;
            $order->order_status = 'Payment Captured';
            $order->payment_method = $request->input('payment_method', 'Card');
            $order->payment_gateway = 'Me9 Mock Payment';
            $order->used_point = $usedPoints;
            $order->grand_total = $totals['total'] - $usedPoints;
            $order->save();

            $jointPurchaseIds = [];
            $createdItems = collect();
            foreach ($items as $item) {
                $shopProduct = $item['shop_product'];
                $product = $item['product'];
                $status = OrderItemStatus::PAID;
                $originalPrice = (float) ($shopProduct->selling_price ?: $shopProduct->product_price) + $item['option_price_adjustment'];
                $isJointPurchase = ! empty($item['joint_purchase']);
                $policy = app(OrderSettlementPolicy::class)->capture($shop, $shopProduct, $product);
                $stockAttribute = $product->stock_usage === 'used' ? $product->attributes->firstWhere('size', $item['option']) : null;
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
                    'option_price_adjustment' => $item['option_price_adjustment'],
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
                    'payment_gateway_type' => $policy['payment_gateway_type'],
                    'settlement_policy_snapshot' => $policy,
                    'shipping_amount_snapshot' => $totals['shipping_by_item'][$item['key']],
                    'used_point_amount' => array_sum($pointAllocations[$item['key']]),
                    'point_usage_snapshot' => $pointAllocations[$item['key']],
                    'paid_line_total_snapshot' => $item['line_total'],
                    'stock_deducted_qty' => $shopProduct->stock !== null ? $item['qty'] : 0,
                    'stock_attribute_id' => $stockAttribute?->id,
                    'attribute_stock_deducted_qty' => $stockAttribute ? $item['qty'] : 0,
                ]);
                app(CustomerPointService::class)->recordSpend($order, $orderItem);
                $createdItems->push($orderItem);

                if ($stockAttribute) {
                    $stockAttribute->stock -= $item['qty'];
                    $stockAttribute->save();
                }

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
        $product = $shopProduct->product;
        if ($product?->purchase_limit_enabled) {
            if ($quantity < (int) $product->purchase_min_qty) {
                throw ValidationException::withMessages(['qty' => '이 상품은 최소 '.$product->purchase_min_qty.'개부터 주문할 수 있습니다.']);
            }
            $productLimit = (int) $product->purchase_max_qty;
            if ($productLimit > 0) {
                $purchaseLimit = $purchaseLimit > 0 ? min($purchaseLimit, $productLimit) : $productLimit;
            }
        }
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
