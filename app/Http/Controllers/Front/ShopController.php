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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopController extends Controller
{
    public function login(ShopChannelRuntime $runtime)
    {
        if (auth()->check()) {
            return redirect()->route($runtime->hasActiveChannelAccess() ? 'shop.channel_main' : 'shop.gate');
        }

        $shop = $runtime->hasActiveChannelAccess() ? $runtime->currentChannel() : null;

        return view('shop.login', compact('shop'));
    }

    public function loginSubmit(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'login_id' => 'required|string|max:150',
            'password' => 'required|string',
        ]);
        foreach (['username', 'email'] as $field) {
            if (Auth::attempt([$field => $data['login_id'], 'password' => $data['password'], 'status' => 1], $request->boolean('remember'))) {
                $request->session()->regenerate();
                if ($runtime->hasActiveChannelAccess()) {
                    $runtime->recordAuthenticatedVisit();

                    return redirect()->route('shop.channel_main');
                }

                return redirect()->route('shop.gate');
            }
        }

        throw ValidationException::withMessages(['login_id' => '아이디(이메일) 또는 비밀번호가 일치하지 않습니다.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.login');
    }

    public function orderConfirm(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();

        return view('front.shop.order_confirm', compact('shop'));
    }

    public function orderConfirmSubmit(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'order_id' => ['required', 'string', 'regex:/^(?:Me9-(?:Shop-)?)?[0-9]+$/i'],
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
        ]);
        $shop = $runtime->currentChannel();
        $id = (int) preg_replace('/^Me9-(?:Shop-)?/i', '', $data['order_id']);
        $order = Order::whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id))->find($id);
        $phone = preg_replace('/\D/', '', $data['phone']);
        if (! $order || ! $phone || ($order->buyer_name ?: $order->name) !== trim($data['name']) || preg_replace('/\D/', '', $order->buyer_mobile ?: $order->mobile) !== $phone) {
            throw ValidationException::withMessages(['order_id' => '입력하신 주문 정보와 일치하는 주문을 찾을 수 없습니다.']);
        }
        if (auth()->check() && (int) $order->user_id !== (int) auth()->id()) {
            throw ValidationException::withMessages(['order_id' => '로그인한 회원의 주문이 아닙니다. 비회원 주문은 로그아웃 후 조회해 주세요.']);
        }
        $request->session()->put('nonmember_order_id', $order->id);

        return redirect()->route('front.shop.order.view', $order->id);
    }

    public function orderView(int $id, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $query = Order::whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id));
        $this->constrainOrdersToCustomer($query);
        $order = $query->with(['orders_products' => fn ($query) => $query->where('shop_channel_id', $shop->id)
            ->with(['claims', 'product.images', 'distributor', 'product.distributor', 'exchangeReplacement'])])->findOrFail($id);
        $order->orders_products->each(fn ($item) => $item->setAttribute('manual_return_address', $this->returnAddressFor($item)));

        return view('front.shop.order_view', compact('shop', 'order'));
    }

    public function cart(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $cartItems = $runtime->cartItems();
        $totals = $runtime->totals();

        return view('front.shop.cart', compact('shop', 'cartItems', 'totals'));
    }

    public function addToCart(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'shop_product_id' => 'required|integer|exists:shop_channel_products,id',
            'qty' => 'nullable|integer|min:1',
            'option' => 'nullable|string|max:100',
            'options' => 'nullable|array|min:1|max:20',
            'options.*.option' => 'required|string|max:100',
            'options.*.qty' => 'required|integer|min:1|max:999',
        ]);

        $runtime->addOptionsToCart((int) $data['shop_product_id'], $data['options'] ?? [['qty' => (int) $request->input('qty', 1), 'option' => $request->input('option', '기본옵션')]]);

        if ($request->boolean('buy_now')) {
            return redirect()->route('front.shop.order.form');
        }

        return redirect()->back()->with('flash_message_success', '장바구니에 상품을 담았습니다.');
    }

    public function removeFromCart(Request $request, ShopChannelRuntime $runtime)
    {
        $request->validate(['shop_product_id' => 'required|integer', 'cart_key' => ['nullable', 'regex:/^[0-9]+(?::[a-f0-9]{16})?$/']]);
        abort_if($request->filled('cart_key') && (int) $request->cart_key !== (int) $request->shop_product_id, 404);
        $runtime->removeFromCart($request->input('cart_key') ?: (int) $request->shop_product_id);

        return redirect()->back()->with('flash_message_success', '장바구니에서 상품을 삭제했습니다.');
    }

    public function removeSelectedFromCart(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'shop_product_ids' => 'required|array|min:1',
            'shop_product_ids.*' => ['required', 'regex:/^[0-9]+(?::[a-f0-9]{16})?$/'],
        ]);
        foreach ($data['shop_product_ids'] as $id) {
            $runtime->removeFromCart($id);
        }

        return back()->with('flash_message_success', '선택한 상품을 삭제했습니다.');
    }

    public function updateCart(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'shop_product_id' => 'required|integer',
            'qty' => 'required|integer|min:1|max:999',
            'option' => 'required|string|max:100',
            'cart_key' => ['nullable', 'regex:/^[0-9]+(?::[a-f0-9]{16})?$/'],
        ]);
        $runtime->updateCart((int) $data['shop_product_id'], (int) $data['qty'], $data['option'], $data['cart_key'] ?? null);

        return back()->with('flash_message_success', '장바구니 상품 정보를 수정했습니다.');
    }

    public function order(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $cartItems = $runtime->cartItems();
        $totals = $runtime->totals();
        $canCheckout = $runtime->canCheckout();
        $deliveryAddresses = auth()->check() ? \App\Models\DeliveryAddress::where('user_id', auth()->id())->where('status', 1)->orderByDesc('is_default')->get() : collect();
        $pointBalances = auth()->check() ? app(\App\Services\CustomerPointService::class)->balances(auth()->id(), $shop->id) : ['channel' => 0, 'me9' => 0];

        return view('front.shop.order_form', compact('shop', 'cartItems', 'totals', 'canCheckout', 'deliveryAddresses', 'pointBalances'));
    }

    public function checkout(Request $request, ShopChannelRuntime $runtime)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|max:30',
            'email' => 'required|email|max:150',
            'pincode' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:Card',
            'buyer_name' => 'nullable|string|max:100',
            'buyer_mobile' => 'nullable|string|max:30',
            'delivery_memo' => 'nullable|string|max:500',
            'order_confirmed' => 'accepted',
            'channel_points' => 'nullable|integer|min:0|max:2147483647',
            'me9_points' => 'nullable|integer|min:0|max:2147483647',
        ]);

        $order = $runtime->checkout($request);

        return redirect()->route('front.shop.order.complete')->with('shop_order_id', $order->id);
    }

    public function orderComplete(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $orderId = session('shop_order_id') ?: session('last_shop_order_id');
        $query = Order::whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id));
        $this->constrainOrdersToCustomer($query);
        $order = $orderId ? $query->with(['orders_products' => fn ($query) => $query->where('shop_channel_id', $shop->id)])->find($orderId) : null;

        return view('front.shop.order_complete', compact('shop', 'order'));
    }

    public function orderDetails(Request $request, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();

        $status = $request->query('status', 'all');
        if (! in_array($status, ['all', 'confirm_pending', 'shipping', 'claims'], true)) {
            $status = 'all';
        }

        $baseQuery = Order::query()
            ->whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id));
        $this->constrainOrdersToCustomer($baseQuery);
        $this->filterOrders($request, $baseQuery);
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
            $query->where('shop_channel_id', $shop->id)->with(['claims', 'product.images', 'distributor', 'product.distributor', 'exchangeReplacement']);
        };
        $ordersQuery = (clone $baseQuery)->with(['orders_products' => $itemLoader]);
        if ($status !== 'all') {
            $statuses = $statusGroups[$status];
            $ordersQuery->whereHas('orders_products', function ($query) use ($shop, $statuses) {
                $query->where('shop_channel_id', $shop->id)->whereIn('status_code', $statuses);
            });
        }
        $orders = $ordersQuery->latest()->paginate(10)->withQueryString();

        foreach ($orders as $order) {
            $order->orders_products->each(function ($item) {
                $item->setAttribute('manual_return_address', $this->returnAddressFor($item));
            });
        }

        return view('front.shop.order_details', compact('shop', 'orders', 'status', 'counts'));
    }

    public function updateOrderItem(Request $request, $id, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();

        $data = $request->validate([
            'action' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'required_unless:action,confirm|nullable|string|max:255',
            'pickup_method' => 'required_if:action,return,exchange|nullable|in:automatic,manual',
            'customer_courier_name' => 'nullable|string|max:100',
            'customer_tracking_number' => 'nullable|string|max:100',
            'rating' => 'nullable|required_with:review|numeric|between:0.5,5|multiple_of:0.5',
            'review' => 'nullable|string|max:2000',
        ]);

        $statusByAction = [
            'cancel' => OrderItemStatus::CANCEL_REQUESTED,
            'return' => OrderItemStatus::RETURN_REQUESTED,
            'exchange' => OrderItemStatus::EXCHANGE_REQUESTED,
        ];

        $item = DB::transaction(function () use ($data, $id, $shop, $statusByAction) {
            $item = OrdersProduct::with('order')
                ->where('shop_channel_id', $shop->id)
                ->whereHas('order', fn ($query) => $this->constrainOrdersToCustomer($query))
                ->lockForUpdate()
                ->findOrFail($id);
            if (! OrderItemStatus::customerActionAllowed($data['action'], $item->normalized_status)) {
                throw ValidationException::withMessages(['action' => '현재 주문 상태에서는 요청한 처리를 진행할 수 없습니다.']);
            }
            if ($data['action'] === 'confirm') {
                $item->setStatus(OrderItemStatus::CONFIRMED);
                $item->confirmed_at = now();
                $item->save();
                app(\App\Services\OrderFulfillmentService::class)->confirmPoints($item);
                if (! empty($data['rating'])) {
                    DB::table('ratings')->insert([
                        'user_id' => $item->order->user_id,
                        'product_id' => $item->product_id,
                        'rating' => $data['rating'],
                        'review' => $data['review'] ?? '',
                        'status' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $item;
            }
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

            return $item;
        });

        if ($data['action'] === 'confirm') {
            app(ShopChannelSmsService::class)->send($shop, $item->order, $item, ShopChannelSmsService::TYPE_PURCHASE_CONFIRMED);

            return back()->with('flash_message_success', '구매확정 처리되었습니다.');
        }
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
        $contact = Contact::create([
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
        $request->session()->put('shop_inquiry_ids', collect($request->session()->get('shop_inquiry_ids', []))->push($contact->id)->unique()->take(-100)->values()->all());

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

    private function filterOrders(Request $request, $query): void
    {
        $data = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('from') ? ['after_or_equal:from'] : []),
            'search' => 'nullable|string|max:100',
        ]);
        if (! empty($data['from'])) {
            $query->whereDate('created_at', '>=', $data['from']);
        }
        if (! empty($data['to'])) {
            $query->whereDate('created_at', '<=', $data['to']);
        }
        if (! empty($data['search'])) {
            $search = $data['search'];
            $orderId = preg_match('/^(?:Me9-(?:Shop-)?)?([0-9]+)$/i', $search, $match) ? (int) $match[1] : 0;
            $query->where(function ($query) use ($search, $orderId) {
                $query->where('id', $orderId)->orWhereHas('orders_products', fn ($query) => $query
                    ->where('shop_channel_id', session('shop_channel_id'))->where('product_name', 'like', '%'.$search.'%'));
            });
        }
    }

    private function claimDetails(Request $request, ShopChannelRuntime $runtime, string $claimType)
    {
        $shop = $runtime->currentChannel();
        $query = Order::query()->whereHas('orders_products', fn ($query) => $query
            ->where('shop_channel_id', $shop->id)->whereHas('claims', fn ($query) => $query->where('type', $claimType)));
        $this->constrainOrdersToCustomer($query);
        $this->filterOrders($request, $query);
        $orders = $query->with(['orders_products' => fn ($query) => $query
            ->where('shop_channel_id', $shop->id)->whereHas('claims', fn ($query) => $query->where('type', $claimType))
            ->with(['claims' => fn ($query) => $query->where('type', $claimType)->latest(), 'product.images', 'exchangeReplacement'])])
            ->latest()->paginate(10)->withQueryString();

        return view('front.shop.'.$claimType.'_details', compact('shop', 'orders', 'claimType'));
    }

    public function cancelDetails(Request $request, ShopChannelRuntime $runtime)
    {
        return $this->claimDetails($request, $runtime, 'cancel');
    }

    public function exchangeDetails(Request $request, ShopChannelRuntime $runtime)
    {
        return $this->claimDetails($request, $runtime, 'exchange');
    }

    public function returnDetails(Request $request, ShopChannelRuntime $runtime)
    {
        return $this->claimDetails($request, $runtime, 'return');
    }
}
