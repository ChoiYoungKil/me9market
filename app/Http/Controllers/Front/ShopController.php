<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\ShopChannelProduct;
use App\Services\ChannelPointService;
use App\Services\ReturnAddressResolver;
use App\Services\ShopChannelRuntime;
use App\Services\ShopChannelSmsService;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function cart(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $cartItems = $runtime->cartItems();
        $totals = $runtime->totals();

        return view('front.shop.cart', compact('shop', 'cartItems', 'totals'));
    }

    public function addToCart(Request $request, ShopChannelRuntime $runtime)
    {
        $request->validate([
            'shop_product_id' => 'required|integer|exists:shop_channel_products,id',
            'qty' => 'nullable|integer|min:1',
            'option' => 'nullable|string|max:100',
        ]);

        $runtime->addToCart((int) $request->shop_product_id, (int) $request->input('qty', 1), $request->input('option', '기본옵션'));

        if ($request->boolean('buy_now')) {
            return redirect()->route('front.shop.cart.index');
        }

        return redirect()->back()->with('flash_message_success', '장바구니에 상품을 담았습니다.');
    }

    public function removeFromCart(Request $request, ShopChannelRuntime $runtime)
    {
        $request->validate(['shop_product_id' => 'required|integer']);
        $runtime->removeFromCart((int) $request->shop_product_id);

        return redirect()->back()->with('flash_message_success', '장바구니에서 상품을 삭제했습니다.');
    }

    public function updateCart(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'shop_product_id' => 'required|integer',
            'qty' => 'required|integer|min:1|max:999',
            'option' => 'required|string|max:100',
        ]);
        $runtime->updateCart((int) $data['shop_product_id'], (int) $data['qty'], $data['option']);

        return back()->with('flash_message_success', '장바구니 상품 정보를 수정했습니다.');
    }

    public function order(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $cartItems = $runtime->cartItems();
        $totals = $runtime->totals();

        return view('front.shop.order_form', compact('shop', 'cartItems', 'totals'));
    }

    public function checkout(Request $request, ShopChannelRuntime $runtime)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|max:30',
            'email' => 'required|email|max:150',
            'pincode' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        $order = $runtime->checkout($request);

        return redirect()->route('front.shop.order.complete')->with('shop_order_id', $order->id);
    }

    public function orderComplete(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $orderId = session('shop_order_id') ?: session('last_shop_order_id');
        $order = $orderId ? Order::with('orders_products')->find($orderId) : null;

        return view('front.shop.order_complete', compact('shop', 'order'));
    }

    public function orderDetails(Request $request, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $orderId = $request->query('id') ?: session('last_shop_order_id') ?: session('nonmember_order_id');

        $status = $request->query('status', 'all');
        if (! in_array($status, ['all', 'confirm_pending', 'shipping', 'claims'], true)) {
            $status = 'all';
        }

        $baseQuery = Order::query()
            ->whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id));
        $this->constrainOrdersToCustomer($baseQuery);
        $statusGroups = [
            'confirm_pending' => [OrderItemStatus::DELIVERED],
            'shipping' => [OrderItemStatus::SHIPPING],
            'claims' => [
                OrderItemStatus::CANCEL_REQUESTED,
                OrderItemStatus::CANCELLED,
                OrderItemStatus::RETURN_REQUESTED,
                OrderItemStatus::RETURN_RECEIVED,
                OrderItemStatus::RETURN_HOLD,
                OrderItemStatus::RETURNED,
                OrderItemStatus::EXCHANGE_REQUESTED,
                OrderItemStatus::EXCHANGE_APPROVED,
                OrderItemStatus::EXCHANGE_HOLD_BEFORE,
                OrderItemStatus::EXCHANGE_RECEIVED,
                OrderItemStatus::EXCHANGE_HOLD_AFTER,
                OrderItemStatus::EXCHANGED,
            ],
        ];
        $counts = ['all' => (clone $baseQuery)->count()];
        foreach ($statusGroups as $key => $statuses) {
            $counts[$key] = (clone $baseQuery)->whereHas('orders_products', function ($query) use ($shop, $statuses) {
                $query->where('shop_channel_id', $shop->id)->whereIn('status_code', $statuses);
            })->count();
        }

        $itemLoader = function ($query) use ($shop) {
            $query->where('shop_channel_id', $shop->id)->with(['claims', 'distributor', 'product.distributor']);
        };
        $ordersQuery = (clone $baseQuery)->with(['orders_products' => $itemLoader]);
        if ($status !== 'all') {
            $statuses = $statusGroups[$status];
            $ordersQuery->whereHas('orders_products', function ($query) use ($shop, $statuses) {
                $query->where('shop_channel_id', $shop->id)->whereIn('status_code', $statuses);
            });
        }
        $orders = $ordersQuery->latest()->paginate(10)->withQueryString();

        $detailQuery = (clone $baseQuery)->with(['orders_products' => $itemLoader]);
        if ($status !== 'all') {
            $detailQuery->whereHas('orders_products', function ($query) use ($shop, $statuses) {
                $query->where('shop_channel_id', $shop->id)->whereIn('status_code', $statuses);
            });
        }
        $order = $orderId ? $detailQuery->find($orderId) : null;
        $order = $order ?: $orders->first();
        if ($order) {
            $order->orders_products->each(function ($item) {
                $item->setAttribute('manual_return_address', $this->returnAddressFor($item));
            });
        }

        return view('front.shop.order_details', compact('shop', 'order', 'orders', 'status', 'counts'));
    }

    public function updateOrderItem(Request $request, $id, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();

        $data = $request->validate([
            'action' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'nullable|string|max:255',
            'pickup_method' => 'required_if:action,return,exchange|nullable|in:automatic,manual',
            'customer_courier_name' => 'nullable|string|max:100',
            'customer_tracking_number' => 'nullable|string|max:100',
        ]);

        $item = OrdersProduct::with('order')
            ->where('shop_channel_id', $shop->id)
            ->whereHas('order', fn ($query) => $this->constrainOrdersToCustomer($query))
            ->findOrFail($id);

        if (! OrderItemStatus::customerActionAllowed($data['action'], $item->normalized_status)) {
            return back()->withErrors(['action' => '현재 주문 상태에서는 요청한 처리를 진행할 수 없습니다.']);
        }

        if ($data['action'] === 'confirm') {
            $item->setStatus(OrderItemStatus::CONFIRMED);
            $item->confirmed_at = now();
            $item->save();
            app(ChannelPointService::class)->recordCustomerPayback($item);
            app(ShopChannelSmsService::class)->send($shop, $item->order, $item, ShopChannelSmsService::TYPE_PURCHASE_CONFIRMED);

            return back()->with('flash_message_success', '구매확정 처리되었습니다.');
        }

        $statusByAction = [
            'cancel' => OrderItemStatus::CANCEL_REQUESTED,
            'return' => OrderItemStatus::RETURN_REQUESTED,
            'exchange' => OrderItemStatus::EXCHANGE_REQUESTED,
        ];

        DB::transaction(function () use ($data, $item, $statusByAction) {
            $item->setStatus($statusByAction[$data['action']]);
            $item->save();

            $reason = $data['reason'] ?? null;
            OrderClaim::updateOrCreate(
                [
                    'order_product_id' => $item->id,
                    'type' => $data['action'],
                    'status' => 'requested',
                ],
                [
                    'order_id' => $item->order_id,
                    'user_id' => $item->order?->user_id ?? 0,
                    'vendor_id' => $item->vendor_id,
                    'reason' => $reason ?: 'Shop 채널 주문상세에서 요청',
                    'detail_reason' => $reason,
                    'pickup_method' => $data['pickup_method'] ?? null,
                    'return_address' => ($data['pickup_method'] ?? null) === 'manual' ? app(ReturnAddressResolver::class)->forOrderItem($item) : null,
                    'customer_courier_name' => $data['customer_courier_name'] ?? null,
                    'customer_tracking_number' => $data['customer_tracking_number'] ?? null,
                    'customer_shipped_at' => ! empty($data['customer_tracking_number']) ? now() : null,
                ]
            );
        });

        if (in_array($data['action'], ['cancel', 'return'], true)) {
            app(ShopChannelSmsService::class)->send(
                $shop,
                $item->order,
                $item,
                $data['action'] === 'cancel' ? ShopChannelSmsService::TYPE_CANCEL : ShopChannelSmsService::TYPE_RETURN
            );
        }

        return back()->with('flash_message_success', $item->status_label.' 상태로 접수되었습니다.');
    }

    public function updateClaimShipment(Request $request, int $id, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $data = $request->validate([
            'customer_courier_name' => 'required|string|max:100',
            'customer_tracking_number' => 'required|string|max:100',
        ]);
        $claim = OrderClaim::whereKey($id)
            ->where('pickup_method', 'manual')
            ->whereHas('product', fn ($query) => $query->where('shop_channel_id', $shop->id))
            ->whereHas('product.order', fn ($query) => $this->constrainOrdersToCustomer($query))
            ->firstOrFail();
        $claim->forceFill([
            'customer_courier_name' => $data['customer_courier_name'],
            'customer_tracking_number' => $data['customer_tracking_number'],
            'customer_shipped_at' => now(),
        ])->save();

        return back()->with('flash_message_success', '고객 발송 송장정보가 등록되었습니다.');
    }

    public function storeInquiry(Request $request, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $data = $request->validate([
            'order_product_id' => 'nullable|required_without:shop_product_id|integer',
            'shop_product_id' => 'nullable|required_without:order_product_id|integer',
            'inquiry_category' => 'required|in:delivery,claim,product,payment,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:3000',
        ]);
        $item = null;
        $product = null;
        if (! empty($data['order_product_id'])) {
            $item = OrdersProduct::with('order')
                ->where('shop_channel_id', $shop->id)
                ->whereHas('order', fn ($query) => $this->constrainOrdersToCustomer($query))
                ->findOrFail($data['order_product_id']);
            $product = $item->product;
        } else {
            $shopProduct = ShopChannelProduct::with('product')
                ->where('shop_channel_id', $shop->id)
                ->where('status', 1)
                ->where('approval_status', 'approved')
                ->findOrFail($data['shop_product_id']);
            $product = $shopProduct->product;
        }
        Contact::create([
            'user_id' => auth()->id(),
            'vendor_id' => $shop->vendor_id,
            'shop_channel_id' => $shop->id,
            'order_id' => $item?->order_id,
            'order_product_id' => $item?->id,
            'product_id' => $item?->product_id ?: $product?->id,
            'name' => $item?->order?->name ?: auth()->user()?->name ?: '비회원',
            'email' => $item?->order?->email ?: auth()->user()?->email ?: 'guest@me9.local',
            'phone' => $item?->order?->mobile ?: auth()->user()?->mobile,
            'inquiry_category' => $data['inquiry_category'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'type' => 'inquiry',
            'status' => 'pending',
        ]);

        return back()->with('flash_message_success', '상품 문의가 접수되었습니다.');
    }

    private function returnAddressFor(OrdersProduct $item): string
    {
        return app(ReturnAddressResolver::class)->forOrderItem($item);
    }

    private function constrainOrdersToCustomer($query): void
    {
        if (auth()->check()) {
            $query->where('user_id', auth()->id());

            return;
        }

        $orderIds = collect([
            session('last_shop_order_id'),
            session('nonmember_order_id'),
        ])->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        $orderIds ? $query->whereIn('id', $orderIds) : $query->whereRaw('1 = 0');
    }

    public function cancelDetails()
    {
        return redirect()->route('front.shop.order.details', ['status' => 'claims']);
    }

    public function exchangeDetails()
    {
        return redirect()->route('front.shop.order.details', ['status' => 'claims']);
    }

    public function returnDetails()
    {
        return redirect()->route('front.shop.order.details', ['status' => 'claims']);
    }
}
