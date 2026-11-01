<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CustomerPointService;
use App\Services\OrderFulfillmentService;
use App\Services\ReturnAddressResolver;
use App\Services\ShopChannelRuntime;
use App\Services\ShopChannelSmsService;
use App\Services\ShopEntry;
use App\Services\ShopOrderTotals;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopController extends Controller
{
    public function login(Request $request, ShopChannelRuntime $runtime)
    {
        $entry = app(ShopEntry::class);
        $shop = $entry->context($request);
        if ($request->filled('channel') && ! $shop) {
            return redirect()->route('shop.gate', ['channel' => $request->input('channel')])->with('flash_message_error', '현재 입장할 수 없는 Shop 채널입니다.');
        }
        if ($shop) {
            $entry->remember($shop);
        }
        $entry->restorePublicAccess($shop);
        if (auth()->check()) {
            return $entry->destination($shop);
        }

        return response()->view('shop.login', compact('shop'))->header('Cache-Control', 'no-store, private');
    }

    public function loginSubmit(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'login_id' => 'required|string|max:150',
            'password' => 'required|string',
            'channel' => 'nullable|string|max:80',
            'save_id' => 'nullable|boolean',
        ]);
        $entry = app(ShopEntry::class);
        $shop = $entry->context($request);
        if ($request->filled('channel') && ! $shop) {
            throw ValidationException::withMessages(['channel' => '현재 입장할 수 없는 Shop 채널입니다.']);
        }
        foreach (['username', 'email'] as $field) {
            if (Auth::attemptWhen([$field => $data['login_id'], 'password' => $data['password'], 'status' => 1], fn ($user) => ! $user->temporaryPasswordIsExpired(), false)) {
                $request->session()->regenerate();
                $entry->restorePublicAccess($shop);
                if ($runtime->hasActiveChannelAccess() && (! $shop || $runtime->currentChannel()->id === $shop->id)) {
                    $runtime->recordAuthenticatedVisit();

                    return redirect()->route('shop.channel_main');
                }

                return $entry->destination($shop);
            }
        }

        throw ValidationException::withMessages(['login_id' => '아이디(이메일) 또는 비밀번호가 일치하지 않습니다.']);
    }

    public function logout(Request $request)
    {
        $shop = app(ShopEntry::class)->context($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.login', $shop ? ['channel' => $shop->channel_code] : []);
    }

    public function orderConfirm(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();

        return view('front.shop.order_confirm', compact('shop'));
    }

    public function orderConfirmSubmit(Request $request, ShopChannelRuntime $runtime)
    {
        if ($request->has('phone_prefix')) {
            $request->validate(['phone_prefix'=>'required|in:010,011,016,017,018,019','phone_middle'=>['required','regex:/^[0-9]{3,4}$/'],'phone_last'=>['required','regex:/^[0-9]{4}$/']]);
            $request->merge(['phone'=>implode('-', $request->only('phone_prefix','phone_middle','phone_last'))]);
        }
        // A failed lookup must not retain authorization from a previous lookup.
        $request->session()->forget(['shop_verified_orders','nonmember_order_id']);
        $data = $request->validate([
            'order_id' => ['nullable', 'string', 'regex:/^(?:Me9-(?:Shop-)?)?[0-9]+$/i'],
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
        ]);
        $shop = $runtime->currentChannel();
        if (empty($data['order_id'])) {
            $phone = preg_replace('/\D/', '', $data['phone']);
            if (!preg_match('/^01[0-9]{8,9}$/', $phone)) throw ValidationException::withMessages(['phone'=>'주문자 연락처를 확인해 주세요.']);
            $query = Order::whereHas('orders_products', fn($q)=>$q->where('shop_channel_id',$shop->id))
                ->whereRaw("COALESCE(NULLIF(buyer_name, ''), name) = ?", [trim($data['name'])])
                ->whereRaw("REPLACE(REPLACE(REPLACE(COALESCE(NULLIF(buyer_mobile, ''), mobile), '-', ''), ' ', ''), '+', '') = ?", [$phone])
                ->where('user_id', auth()->id() ?: 0);
            $ids = $query->latest('id')->limit(100)->pluck('id')->all();
            if (!$ids) throw ValidationException::withMessages(['phone'=>'입력하신 주문 정보와 일치하는 주문을 찾을 수 없습니다.']);
            $request->session()->put('shop_verified_orders',['shop_id'=>$shop->id,'ids'=>$ids,'expires'=>now()->addMinutes(30)->timestamp]);
            $request->session()->regenerate();
            return redirect()->route('front.shop.order.details');
        }
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

    public function cart(Request $request, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $data = $request->validate(['type' => 'nullable|in:own,public']);
        $cartType = $data['type'] ?? 'own';
        $allItems = $runtime->cartItems();
        $isPublic = fn($item) => in_array($item['shop_product']->product_type, ['public','partial'], true);
        $cartCounts = ['own' => count(array_filter($allItems, fn($item)=>!$isPublic($item))), 'public' => count(array_filter($allItems, $isPublic))];
        $cartItems = array_values(array_filter($allItems, fn($item)=>($cartType === 'public') === $isPublic($item)));
        $totals = app(ShopOrderTotals::class)->calculate($cartItems);

        return view('front.shop.cart', compact('shop', 'cartItems', 'totals', 'cartType', 'cartCounts'));
    }

    public function toggleWishlist(int $id, ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $product = ShopChannelProduct::where('shop_channel_id', $shop->id)->where('status', 1)
            ->where('approval_status', 'approved')->whereHas('product', fn ($query) => $query->where('status', 1))->findOrFail($id);
        $added = DB::transaction(function () use ($product) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $entry = Wishlist::where('user_id', auth()->id())->where('shop_channel_product_id', $product->id)->first();
            if ($entry) {
                $entry->delete();

                return false;
            }
            Wishlist::create(['user_id' => auth()->id(), 'shop_channel_product_id' => $product->id]);

            return true;
        });

        return back()->with('flash_message_success', $added ? '찜한 상품에 등록했습니다.' : '찜한 상품에서 삭제했습니다.');
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
            $options = array_column($data['options'] ?? [['option' => $request->input('option', '기본옵션')]], 'option');
            $runtime->selectForCheckout(array_column(array_filter($runtime->cartItems(), fn ($row) => (int)$row['id'] === (int)$data['shop_product_id'] && in_array($row['option'], $options, true)), 'key'));
            return redirect()->route('front.shop.order.form');
        }

        $listing = ShopChannelProduct::findOrFail($data['shop_product_id']);
        return redirect()->route('front.shop.cart.index', in_array($listing->product_type, ['public','partial'],true) ? ['type'=>'public'] : [])->with('flash_message_success', '장바구니에 상품을 담았습니다.');
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

    public function selectCheckout(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate(['shop_product_ids' => 'required|array|min:1|max:100', 'shop_product_ids.*' => ['required', 'regex:/^[0-9]+(?::[a-f0-9]{16})?$/']], ['shop_product_ids.required' => '주문할 상품을 선택해 주세요.']);
        $runtime->selectForCheckout($data['shop_product_ids']);
        return redirect()->route('front.shop.order.form');
    }

    public function cartSelectionTotal(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate(['shop_product_ids' => 'nullable|array|max:100', 'shop_product_ids.*' => ['required', 'regex:/^[0-9]+(?::[a-f0-9]{16})?$/']]);
        $keys = array_map('strval', $data['shop_product_ids'] ?? []);
        $items = $runtime->cartItems();
        if(array_diff($keys, array_map(fn($item)=>(string)$item['key'],$items))) throw ValidationException::withMessages(['cart'=>'선택 상품을 다시 확인해 주세요.']);
        $selected = array_values(array_filter($items,fn($item)=>in_array((string)$item['key'],$keys,true)));
        $totals = app(ShopOrderTotals::class)->calculate($selected);
        return response()->json(array_intersect_key($totals,array_flip(['subtotal','shipping','total'])) + ['quantity'=>array_sum(array_column($selected,'qty'))]);
    }

    public function removeCheckoutItem(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate(['cart_key' => ['required', 'regex:/^[0-9]+(?::[a-f0-9]{16})?$/']]);
        $items = $runtime->checkoutItems();
        abort_unless(collect($items)->contains(fn ($item) => (string)$item['key'] === $data['cart_key']), 404);
        $keys = array_values(array_filter(array_map(fn ($item) => (string)$item['key'], $items), fn ($key) => $key !== $data['cart_key']));
        $request->session()->put('shop_checkout_selection', ['channel_id' => $runtime->currentChannel()->id, 'keys' => $keys]);
        return redirect()->route('front.shop.order.form')->with('flash_message_success', '주문서에서 상품을 삭제했습니다. 장바구니에는 보관됩니다.');
    }

    public function order(ShopChannelRuntime $runtime)
    {
        $shop = $runtime->currentChannel();
        $cartItems = $runtime->checkoutItems();
        $totals = app(ShopOrderTotals::class)->calculate($cartItems);
        $canCheckout = $runtime->canCheckout();
        $privacyDocument = json_decode(file_get_contents(resource_path('policies/privacy.json')), true, 512, JSON_THROW_ON_ERROR);
        $privacyDocument['blocks'] = array_slice($privacyDocument['blocks'], 2);
        $deliveryAddresses = auth()->check() ? DeliveryAddress::where('user_id', auth()->id())->where('status', 1)->orderByDesc('is_default')->get() : collect();
        $pointBalances = auth()->check() ? app(CustomerPointService::class)->balances(auth()->id(), $shop->id) : ['channel' => 0, 'me9' => 0];
        $fingerprint = hash('sha256', json_encode([auth()->id(), $shop->id, collect($cartItems)->map(fn ($item) => [$item['key'], $item['qty'], $item['line_total']])->all()]));
        $tokens = session('shop_checkout_tokens', []);
        $checkoutToken = collect($tokens)->search(fn ($value) => $value['fingerprint'] === $fingerprint && $value['expires'] > now()->timestamp);
        if (!$checkoutToken) {
            $checkoutToken = (string) \Illuminate\Support\Str::uuid();
            $tokens[$checkoutToken] = ['fingerprint' => $fingerprint, 'shop_id' => $shop->id, 'user_id' => auth()->id() ?: 0, 'expires' => now()->addMinutes(30)->timestamp];
            session(['shop_checkout_tokens' => array_slice($tokens, -20, null, true)]);
        }

        return view('front.shop.order_form', compact('shop', 'cartItems', 'totals', 'canCheckout', 'deliveryAddresses', 'pointBalances', 'checkoutToken', 'privacyDocument'));
    }

    public function shippingQuote(Request $request, ShopChannelRuntime $runtime)
    {
        $data = $request->validate([
            'address' => 'required|string|max:255',
            'pincode' => ['required', 'regex:/^[0-9]{5}$/'],
        ]);
        $totals = app(ShopOrderTotals::class)->calculate($runtime->checkoutItems(), $data);
        unset($totals['shipping_policies']);

        return response()->json($totals);
    }

    public function checkout(Request $request, ShopChannelRuntime $runtime)
    {
        if (auth()->check()) $request->merge(['email' => auth()->user()->email, 'buyer_mobile' => auth()->user()->mobile]);
        if ($request->has('recipient_email_local') || $request->has('recipient_email_domain')) {
            $request->merge(['recipient_email' => trim((string) $request->input('recipient_email_local')).'@'.trim((string) $request->input('recipient_email_domain'))]);
        }
        if ($request->has('recipient_phone_prefix')) {
            $request->validate(['recipient_phone_prefix' => 'required|in:010,011,016,017,018,019', 'recipient_phone_middle' => ['required', 'regex:/^[0-9]{3,4}$/'], 'recipient_phone_last' => ['required', 'regex:/^[0-9]{4}$/']]);
            $request->merge(['mobile' => implode('-', $request->only('recipient_phone_prefix', 'recipient_phone_middle', 'recipient_phone_last'))]);
        }
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|max:30',
            'email' => 'required|email|max:150',
            'recipient_email' => 'nullable|email|max:150',
            'point_use' => 'nullable|integer|min:0|max:2147483647',
            'pincode' => ['required', 'regex:/^[0-9]{5}$/'],
            'expected_total' => 'nullable|numeric|min:0',
            'checkout_token' => 'nullable|uuid',
            'address_detail' => 'nullable|string|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:Card,BankTransfer',
            'buyer_name' => 'nullable|string|max:100',
            'buyer_mobile' => 'nullable|string|max:30',
            'delivery_memo' => 'nullable|string|max:500',
            'order_confirmed' => 'accepted',
            'channel_points' => 'nullable|integer|min:0|max:2147483647',
            'me9_points' => 'nullable|integer|min:0|max:2147483647',
        ]);
        if ($request->filled('checkout_token')) {
            $context = session('shop_checkout_tokens.'.$request->input('checkout_token'));
            if (!$context || $context['expires'] < now()->timestamp || $context['shop_id'] !== $runtime->currentChannel()->id || $context['user_id'] !== (auth()->id() ?: 0)) {
                throw ValidationException::withMessages(['checkout_token' => '주문서 유효시간이 지났습니다. 주문서를 다시 열어 주세요.']);
            }
        }

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
        if (! in_array($status, ['all', 'confirm_pending', 'paid', 'ready_to_ship', 'shipping', 'delivered', 'confirmed', 'claims'], true)) {
            $status = 'all';
        }

        $baseQuery = Order::query()
            ->whereHas('orders_products', fn ($query) => $query->where('shop_channel_id', $shop->id));
        $this->constrainOrdersToCustomer($baseQuery);
        $this->filterOrders($request, $baseQuery);
        $statusGroups = [
            'paid'=>[OrderItemStatus::PAID],
            'ready_to_ship'=>[OrderItemStatus::READY_TO_SHIP],
            'delivered'=>[OrderItemStatus::DELIVERED],
            'confirmed'=>[OrderItemStatus::CONFIRMED],
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

        if ($request->has('reason_code')) {
            $options = \App\Support\ShopClaimReasons::for((string) $request->input('action'));
            $request->validate(['reason_code'=>['required', \Illuminate\Validation\Rule::in(array_keys($options))], 'reason_detail'=>'nullable|required_if:reason_code,other|string|max:255']);
            $request->merge(['reason'=>$options[$request->input('reason_code')], 'detail_reason'=>$request->input('reason_code')==='other' ? trim((string)$request->input('reason_detail')) : null]);
            if ($request->input('reason_code')==='other' && !$request->input('detail_reason')) throw ValidationException::withMessages(['reason_detail'=>'기타 사유를 입력해 주세요.']);
        }

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

        $detailReason = $request->has('reason_code') ? $request->input('detail_reason') : $request->input('reason');
        $item = DB::transaction(function () use ($data, $id, $shop, $statusByAction, $detailReason) {
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
                app(OrderFulfillmentService::class)->confirmPoints($item);
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
            $item->claim_previous_status = $item->normalized_status;
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
                    'detail_reason' => $detailReason,
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
        DB::transaction(function () use ($claim, $data) {
        $item = OrdersProduct::whereKey($claim->order_product_id)->lockForUpdate()->firstOrFail();
        if (!in_array($item->normalized_status, array_merge(OrderItemStatus::pendingReturnStatuses(),OrderItemStatus::pendingExchangeStatuses()), true)) {
            throw ValidationException::withMessages(['customer_tracking_number'=>'진행 중인 반품·교환 신청에서만 송장정보를 수정할 수 있습니다.']);
        }
        $claim->forceFill([
            'customer_courier_name' => $data['customer_courier_name'],
            'customer_tracking_number' => $data['customer_tracking_number'],
            'customer_shipped_at' => now(),
        ])->save();
        });

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
            'message' => 'required|string|max:20000',
        ]);
        $message = app(\App\Services\RefundPolicyContent::class)->forStorage($data['message']);
        $plainMessage = trim(preg_replace('/[\s\x{00a0}\x{200b}]+/u', ' ', html_entity_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($plainMessage === '' || mb_strlen($plainMessage) > 3000) {
            throw ValidationException::withMessages(['message' => '문의 내용을 1~3,000자로 입력해 주세요.']);
        }
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
            'message' => $message,
            'type' => 'inquiry',
            'status' => 'pending',
        ]);
        $request->session()->put('shop_inquiry_ids', collect($request->session()->get('shop_inquiry_ids', []))->push($contact->id)->unique()->take(-100)->values()->all());

        if (!empty($data['shop_product_id'])) {
            return redirect(route('shop.product_details', $data['shop_product_id']).'#inquiry-panel')->with('flash_message_success', '상품 문의가 접수되었습니다.');
        }
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

        $verified = session('shop_verified_orders', []);
        $verifiedIds = ($verified['shop_id'] ?? null) === session('shop_channel_id') && ($verified['expires'] ?? 0) > now()->timestamp ? ($verified['ids'] ?? []) : [];
        $orderIds = collect(array_merge([
            session('last_shop_order_id'),
            session('nonmember_order_id'),
        ], $verifiedIds))->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

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
        $claimFilter = $request->validate(['claim_status'=>'nullable|in:all,requested,approved,received,held,completed,withdrawn,rejected']);
        $claimState = $claimFilter['claim_status'] ?? 'all';
        $claimStates = $claimState === 'held' ? ['held','held_before','held_after'] : [$claimState];
        if ($claimState !== 'all') $query->whereHas('orders_products', fn($q)=>$q->where('shop_channel_id',$shop->id)->whereHas('claims',fn($c)=>$c->where('type',$claimType)->whereIn('status',$claimStates)));
        $orders = $query->with(['orders_products' => fn ($query) => $query
            ->where('shop_channel_id', $shop->id)->whereHas('claims', fn ($query) => $query->where('type', $claimType)->when($claimState!=='all',fn($q)=>$q->whereIn('status',$claimStates)))
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
