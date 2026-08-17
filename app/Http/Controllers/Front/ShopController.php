<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Services\ShopChannelSmsService;
use App\Services\ShopChannelRuntime;
use App\Support\OrderItemStatus;

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
        $order = $orderId ? \App\Models\Order::with('orders_products')->find($orderId) : null;

        return view('front.shop.order_complete', compact('shop', 'order'));
    }

    public function orderDetails(Request $request, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $orderId = $request->query('id') ?: session('last_shop_order_id') ?: session('nonmember_order_id');

        $order = null;
        if ($orderId) {
            $order = \App\Models\Order::with(['orders_products' => function ($query) use ($shop) {
                $query->where('shop_channel_id', $shop->id);
            }])
                ->whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id))
                ->find($orderId);
        }

        if (!$order) {
            $order = \App\Models\Order::with(['orders_products' => function ($query) use ($shop) {
                $query->where('shop_channel_id', $shop->id);
            }])
                ->whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id))
                ->latest()
                ->first();
        }

        return view('front.shop.order_details', compact('shop', 'order'));
    }

    public function updateOrderItem(Request $request, $id, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();

        $data = $request->validate([
            'action' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'nullable|string|max:255',
        ]);

        $item = OrdersProduct::with('order')
            ->where('shop_channel_id', $shop->id)
            ->findOrFail($id);

        if ($data['action'] === 'confirm') {
            $item->setStatus(OrderItemStatus::CONFIRMED);
            $item->confirmed_at = now();
            $item->save();
            app(\App\Services\ChannelPointService::class)->recordCustomerPayback($item);
            app(ShopChannelSmsService::class)->send($shop, $item->order, $item, ShopChannelSmsService::TYPE_PURCHASE_CONFIRMED);

            return back()->with('flash_message_success', '구매확정 처리되었습니다.');
        }

        $statusByAction = [
            'cancel' => OrderItemStatus::CANCEL_REQUESTED,
            'return' => OrderItemStatus::RETURN_REQUESTED,
            'exchange' => OrderItemStatus::EXCHANGE_REQUESTED,
        ];

        $item->setStatus($statusByAction[$data['action']]);
        $item->save();

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
                'reason' => $data['reason'] ?: 'Shop 채널 주문상세에서 요청',
                'detail_reason' => $data['reason'] ?: null,
            ]
        );

        if (in_array($data['action'], ['cancel', 'return'], true)) {
            app(ShopChannelSmsService::class)->send(
                $shop,
                $item->order,
                $item,
                $data['action'] === 'cancel' ? ShopChannelSmsService::TYPE_CANCEL : ShopChannelSmsService::TYPE_RETURN
            );
        }

        return back()->with('flash_message_success', $item->status_label . ' 상태로 접수되었습니다.');
    }

    public function cancelDetails()
    {
        return view('front.shop.cancel_details');
    }

    public function exchangeDetails()
    {
        return view('front.shop.exchange_details');
    }

    public function returnDetails()
    {
        return view('front.shop.return_details');
    }
}
