<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Notice;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\ShopChannel;
use App\Models\ShopChannelNotice;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Services\ChannelPointService;
use App\Services\ReturnAddressResolver;
use App\Services\ShopChannelOtpService;
use App\Services\ShopChannelRuntime;
use App\Services\ShopChannelSmsService;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class FrontController extends Controller
{
    public function index()
    {
        $notices = Notice::where('status', 1)->orderByDesc('is_important')->latest()->limit(5)->get();
        return view('front.index', compact('notices'));
    }

    public function notice(Request $request)
    {
        $query = Notice::where('status', 1)->orderBy('is_important', 'desc')->orderBy('created_at', 'desc');

        // 검색 기능
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%');
            });
        }

        $notices = $query->paginate(10);

        return view('front.pages.notice', compact('notices'));
    }

    public function noticeView($id)
    {
        $notice = Notice::where('status', 1)->findOrFail($id);

        // 조회수 증가
        $notice->increment('view_count');

        // 이전글/다음글
        $prevNotice = Notice::where('status', 1)
            ->where('id', '<', $id)
            ->orderBy('id', 'desc')
            ->first();

        $nextNotice = Notice::where('status', 1)
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->first();

        return view('front.pages.notice_view', compact('notice', 'prevNotice', 'nextNotice'));
    }

    public function faq(Request $request)
    {
        $query = Faq::where('status', 1)->orderBy('order', 'asc')->orderBy('created_at', 'desc');

        // 카테고리 필터
        if ($request->has('category') && $request->category != '' && $request->category != '전체') {
            $query->where('category', $request->category);
        }

        // 검색
        if ($request->has('search_value') && $request->search_value != '') {
            $search_type = $request->get('search_type', 'question');
            $search_value = $request->get('search_value');

            if ($search_type == 'question') {
                $query->where('question', 'like', '%'.$search_value.'%');
            } elseif ($search_type == 'answer') {
                $query->where('answer', 'like', '%'.$search_value.'%');
            } else {
                // 질문 + 답변
                $query->where(function ($q) use ($search_value) {
                    $q->where('question', 'like', '%'.$search_value.'%')
                        ->orWhere('answer', 'like', '%'.$search_value.'%');
                });
            }
        }

        $faqs = $query->paginate(10); // 한 페이지에 10개

        return view('front.pages.faq', compact('faqs'));
    }

    public function contact()
    {
        return view('front.pages.contact');
    }

    public function service()
    {
        return view('front.pages.service');
    }

    public function features()
    {
        return view('front.pages.features');
    }

    public function subscriptionInfo()
    {
        return view('front.pages.subscription_info');
    }

    public function nonmemberOrderCheck()
    {
        return view('front.pages.nonmember_order_check');
    }

    public function nonmemberOrderCheckSubmit(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'phone' => 'required',
        ]);

        // Clean values
        $cleanPhone = str_replace('-', '', $request->phone);
        $orderIdInput = trim($request->order_id);
        $cleanOrderId = $orderIdInput;
        if (str_contains($cleanOrderId, '-')) {
            $parts = explode('-', $cleanOrderId);
            $cleanOrderId = end($parts);
        }
        $cleanOrderId = preg_replace('/[^0-9]/', '', $cleanOrderId);
        $id = intval($cleanOrderId);

        $order = Order::where('id', $id)->first();
        if (! $order) {
            $order = Order::where('id', $request->order_id)->first();
        }

        if (! $order) {
            return redirect()->back()->with('flash_message_error', '입력하신 주문 정보와 일치하는 주문을 찾을 수 없습니다.');
        }

        $orderPhone = str_replace('-', '', $order->mobile);
        if ($orderPhone !== $cleanPhone) {
            return redirect()->back()->with('flash_message_error', '주문번호와 연락처가 일치하지 않습니다.');
        }

        Session::put('nonmember_order_id', $order->id);

        return redirect()->route('front.nonmember.order_details');
    }

    public function nonmemberOrderDetails()
    {
        $orderId = Session::get('nonmember_order_id');
        if (! $orderId) {
            return redirect()->route('front.nonmember.order_check')->with('flash_message_error', '주문 조회를 먼저 완료해 주세요.');
        }

        $order = Order::with(['orders_products.claims', 'orders_products.distributor', 'orders_products.product.distributor', 'claims'])->find($orderId);
        if (! $order) {
            return redirect()->route('front.nonmember.order_check')->with('flash_message_error', '해당 주문을 찾을 수 없습니다.');
        }
        $order->orders_products->each(function (OrdersProduct $item) {
            $item->setAttribute('manual_return_address', app(ReturnAddressResolver::class)->forOrderItem($item));
        });

        return view('front.pages.nonmember_order_details', compact('order'));
    }

    public function downloadOrderInvoice($id)
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();
        $sessionOrderIds = array_filter([
            Session::get('nonmember_order_id'),
            Session::get('shop_order_id'),
            Session::get('last_shop_order_id'),
        ]);

        $isOwnedByUser = $user && (int) $order->user_id === (int) $user->id;
        $isVerifiedSessionOrder = in_array((int) $order->id, array_map('intval', $sessionOrderIds), true);

        if (! $isOwnedByUser && ! $isVerifiedSessionOrder) {
            abort(403);
        }

        return app(\App\Services\OrderInvoiceService::class)->download($order);
    }

    public function nonmemberOrderClaimSubmit(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'order_product_id' => 'required',
            'type' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'required_unless:type,confirm',
            'recovery_method' => 'required_if:type,return,exchange|nullable|in:자동회수,수동회수,automatic,manual',
            'customer_courier_name' => 'nullable|string|max:100',
            'customer_tracking_number' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|between:0.5,5|multiple_of:0.5|required_with:review',
            'review' => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($request, $data) {
        $order = Order::find($request->order_id);
        if (! $order) {
            abort(404);
        }
        abort_unless((int) Session::get('nonmember_order_id') === (int) $order->id, 403);

        $ordersProduct = OrdersProduct::where('id', $request->order_product_id)
            ->where('order_id', $order->id)
            ->lockForUpdate()
            ->first();
        if (! $ordersProduct) {
            abort(404);
        }

        if (! OrderItemStatus::customerActionAllowed($data['type'], $ordersProduct->normalized_status)) {
            return back()->withErrors(['action' => '현재 주문 상태에서는 요청한 처리를 진행할 수 없습니다.']);
        }

        if ($request->type == 'confirm') {
            $ordersProduct->setStatus(OrderItemStatus::CONFIRMED);
            $ordersProduct->confirmed_at = now();
            $ordersProduct->save();
            app(\App\Services\OrderFulfillmentService::class)->confirmPoints($ordersProduct);
            $ordersProduct->loadMissing('shopChannel');
            if ($ordersProduct->shopChannel) {
                DB::afterCommit(fn () => app(ShopChannelSmsService::class)->send(
                    $ordersProduct->shopChannel,
                    $order,
                    $ordersProduct,
                    ShopChannelSmsService::TYPE_PURCHASE_CONFIRMED
                ));
            }

            // Save rating and review to ratings table
            if (! empty($data['rating']) && $order->user_id && User::whereKey($order->user_id)->exists()) {
                DB::table('ratings')->insert([
                    'user_id' => $order->user_id,
                    'product_id' => $ordersProduct->product_id,
                    'rating' => $data['rating'],
                    'review' => $data['review'] ?? '',
                    'status' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->back()->with('flash_message_success', '구매 확정이 완료되었습니다.');
        } else {
            $claimType = $request->type;
            $statusCodeMap = [
                'cancel' => OrderItemStatus::CANCEL_REQUESTED,
                'return' => OrderItemStatus::RETURN_REQUESTED,
                'exchange' => OrderItemStatus::EXCHANGE_REQUESTED,
            ];

            $ordersProduct->setStatus($statusCodeMap[$claimType]);
            $ordersProduct->save();

            $detailReason = $request->detail_reason ?? '';
            $pickupMethod = null;
            $returnAddress = null;
            if ($claimType == 'return' || $claimType == 'exchange') {
                $pickupMethod = in_array($data['recovery_method'], ['수동회수', 'manual'], true) ? 'manual' : 'automatic';
                $returnAddress = $pickupMethod === 'manual'
                    ? app(ReturnAddressResolver::class)->forOrderItem($ordersProduct)
                    : null;
            }

            OrderClaim::updateOrCreate([
                'order_product_id' => $ordersProduct->id,
                'type' => $claimType,
                'status' => 'requested',
            ], [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'vendor_id' => $ordersProduct->vendor_id,
                'reason' => $request->reason,
                'detail_reason' => $detailReason,
                'pickup_method' => $pickupMethod,
                'return_address' => $returnAddress,
                'customer_courier_name' => $data['customer_courier_name'] ?? null,
                'customer_tracking_number' => $data['customer_tracking_number'] ?? null,
                'customer_shipped_at' => ! empty($data['customer_tracking_number']) ? now() : null,
                'status' => 'requested',
            ]);

            $label = $claimType == 'cancel' ? '취소' : ($claimType == 'return' ? '반품' : '교환');

            return redirect()->back()->with('flash_message_success', $label.' 신청이 완료되었습니다.');
        }
        });
    }

    public function nonmemberOrderInquirySubmit(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'order_product_id' => 'required',
            'inquiry_category' => 'required|in:delivery,claim,product,payment,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $order = Order::find($request->order_id);
        if (! $order) {
            abort(404);
        }
        abort_unless((int) Session::get('nonmember_order_id') === (int) $order->id, 403);

        $ordersProduct = OrdersProduct::where('id', $request->order_product_id)
            ->where('order_id', $order->id)
            ->first();
        if (! $ordersProduct) {
            abort(404);
        }

        $shopChannelId = $ordersProduct->shop_channel_id;
        if (! $shopChannelId && $ordersProduct->shop_channel_product_id) {
            $shopChannelId = DB::table('shop_channel_products')
                ->where('id', $ordersProduct->shop_channel_product_id)
                ->value('shop_channel_id');
        }

        DB::table('contacts')->insert([
            'user_id' => $order->user_id ?: null,
            'vendor_id' => $ordersProduct->vendor_id,
            'shop_channel_id' => $shopChannelId,
            'order_id' => $order->id,
            'order_product_id' => $ordersProduct->id,
            'product_id' => $ordersProduct->product_id,
            'name' => $order->name,
            'email' => $order->email,
            'phone' => $order->mobile,
            'inquiry_category' => $data['inquiry_category'],
            'subject' => '[상품문의] '.$request->subject.' (상품: '.$ordersProduct->product_name.')',
            'message' => $request->message,
            'type' => 'inquiry',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('flash_message_success', '상품 문의가 등록되었습니다.');
    }

    public function shopGate(Request $request)
    {
        return view('shop.gate', ['channelCode' => (string) $request->query('channel', '')]);
    }

    public function shopGateSubmit(Request $request)
    {
        $request->validate([
            'entry_code' => 'required|string|max:80',
            'phone' => 'nullable|string|max:30',
            'otp' => 'nullable|digits:6',
        ]);

        $runtime = app(ShopChannelRuntime::class);
        $channel = ShopChannel::where('channel_code', trim($request->entry_code))->where('status', 1)->first();
        if (! $channel) {
            return redirect()->back()->withInput()->with('flash_message_error', '운영 중인 Shop 채널을 찾을 수 없습니다.');
        }

        if ((int) $channel->is_public === 0) {
            $request->validate(['phone' => 'required|string|max:30', 'otp' => 'required|digits:6']);
            $access = app(ShopChannelOtpService::class)->verify($request->entry_code, $request->phone, $request->otp);
            $shop = $runtime->enterPrivateAccess($access);
        } else {
            $shop = $runtime->enterChannel($request->entry_code);
        }
        if ($shop) {
            return redirect()->route('shop.channel_main')->with('flash_message_success', $shop->channel_name.'에 입장했습니다.');
        }

        return redirect()->back()->with('flash_message_error', '입장 코드가 올바르지 않습니다.');
    }

    public function shopOtpRequest(Request $request)
    {
        $data = $request->validate([
            'entry_code' => 'required|string|max:80',
            'phone' => 'required|string|max:30',
        ]);
        app(ShopChannelOtpService::class)->request($data['entry_code'], $data['phone']);

        return response()->json(['status' => true, 'message' => '인증번호를 발송했습니다.']);
    }

    public function shopEnter(string $channelCode)
    {
        $shop = app(ShopChannelRuntime::class)->enterChannel($channelCode);
        if (! $shop) {
            return redirect()->route('shop.gate', ['channel' => $channelCode])->with('flash_message_error', '비공개 채널은 휴대폰 SMS 인증이 필요합니다.');
        }

        return redirect()->route('shop.channel_main')->with('flash_message_success', $shop->channel_name.'에 입장했습니다.');
    }

    public function shopRegister()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $canRegister = $runtime->canRegister();

        return view('shop.social_join', compact('shop', 'canRegister'));
    }

    public function shopRegisterSubmit(Request $request)
    {
        if (! app(ShopChannelRuntime::class)->canRegister()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['terms_service' => '회원가입 약관을 준비 중입니다. 판매자에게 문의해 주세요.']);
        }
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8|max:72',
            'terms_service' => 'accepted',
            'terms_privacy' => 'accepted',
            'terms_third_party' => 'accepted',
            'marketing_opt_in' => 'nullable|boolean',
            'notification_opt_in' => 'nullable|boolean',
        ]);

        foreach (['marketing', 'notification'] as $optional) {
            if (app()->environment('production') && $request->boolean($optional.'_opt_in')
                && (! filter_var(config('shop_channel.'.$optional.'_url'), FILTER_VALIDATE_URL)
                    || parse_url(config('shop_channel.'.$optional.'_url'), PHP_URL_SCHEME) !== 'https')) {
                throw \Illuminate\Validation\ValidationException::withMessages([$optional.'_opt_in' => '선택 동의 안내 문서가 준비되지 않았습니다. 해당 동의를 해제해 주세요.']);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['email'],
            'email' => $data['email'],
            'mobile' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 1,
            'type' => 'general',
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
            'terms_accepted_at' => now(),
            'notification_opt_in' => $request->boolean('notification_opt_in'),
            'terms_snapshot' => [
                'version' => config('shop_channel.terms_version'),
                'terms_url' => config('shop_channel.terms_url'),
                'privacy_url' => config('shop_channel.privacy_url'),
                'third_party_url' => config('shop_channel.third_party_url'),
                'marketing_url' => $request->boolean('marketing_opt_in') ? config('shop_channel.marketing_url') : null,
                'notification_url' => $request->boolean('notification_opt_in') ? config('shop_channel.notification_url') : null,
                'terms_service' => true, 'terms_privacy' => true, 'terms_third_party' => true,
                'marketing_opt_in' => $request->boolean('marketing_opt_in'),
                'notification_opt_in' => $request->boolean('notification_opt_in'),
                'accepted_at' => now()->toIso8601String(),
            ],
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        app(ShopChannelRuntime::class)->recordAuthenticatedVisit();

        return redirect()->route('shop.channel_main')->with('flash_message_success', '간편회원 가입이 완료되었습니다!');
    }

    public function shopMain()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $products = $runtime->products()->sortByDesc('id')->take(8);
        $jointPurchases = $this->shopJointPurchaseQuery($shop->id)
            ->orderBy('joint_purchases.end_date')
            ->take(2)
            ->get();

        return view('shop.channel_main', compact('shop', 'products', 'jointPurchases'));
    }

    public function shopProducts(Request $request)
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $products = $runtime->products();
        $categories = $products->pluck('product.category')->filter()->unique('id')->sortBy('category_name');

        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'sort' => 'nullable|in:latest,price_asc,price_desc,rating',
            'category' => 'nullable|integer|min:1',
        ]);
        if (! empty($data['category'])) {
            $products = $products->where('product.category_id', (int) $data['category']);
        }
        if (! empty($data['search'])) {
            $products = $products->filter(fn ($item) => mb_stripos($item->product?->product_name ?? '', $data['search']) !== false);
        }
        $products = match ($data['sort'] ?? 'latest') {
            'price_asc' => $products->sortBy(fn ($item) => $item->selling_price ?: $item->product_price),
            'price_desc' => $products->sortByDesc(fn ($item) => $item->selling_price ?: $item->product_price),
            'rating' => $products->sortByDesc(fn ($item) => (float) $item->product->approved_ratings_avg_rating),
            default => $products->sortByDesc('id'),
        };
        $page = max(1, (int) $request->query('page', 1));
        $products = new \Illuminate\Pagination\LengthAwarePaginator($products->values()->forPage($page, 12), $products->count(), 12, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        return view('shop.products_list', compact('shop', 'products', 'categories'));
    }

    public function shopProductDetails($id)
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $shopProduct = ShopChannelProduct::with(['product' => fn ($query) => $query->with(['images', 'attributes', 'category'])->withCount('approvedRatings')->withAvg('approvedRatings', 'rating'), 'shopChannel'])
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('status', 1))
            ->where('id', $id)
            ->first();

        if (! $shopProduct) {
            abort(404);
        }

        $policy = \App\Models\ShopCancelRefundPolicy::where('vendor_id', $shopProduct->product->vendor_id)
            ->where('status', 1)->find($shopProduct->product->cancel_refund_policy_id);
        $inquiries = \App\Models\Contact::where('shop_channel_id', $shop->id)
            ->where('product_id', $shopProduct->product_id)
            ->when(auth()->check(), fn ($query) => $query->where('user_id', auth()->id()),
                fn ($query) => $query->whereIn('id', session('shop_inquiry_ids', [])))
            ->latest()->limit(20)->get();
        $reviews = $shopProduct->product->approvedRatings()->latest()->limit(20)->get();

        return view('shop.product_details', compact('shop', 'shopProduct', 'policy', 'inquiries', 'reviews'));
    }

    public function shopJointPurchases()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $jointPurchases = $this->shopJointPurchaseQuery($shop->id)
            ->orderBy('joint_purchases.end_date')
            ->get();

        return view('shop.joint_purchases_list', compact('shop', 'jointPurchases'));
    }

    public function shopJointPurchaseDetails($id)
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $jointPurchase = $this->shopJointPurchaseQuery($shop->id)
            ->where('joint_purchases.id', $id)
            ->first();

        if (! $jointPurchase) {
            abort(404);
        }

        return view('shop.joint_purchase_details', compact('shop', 'jointPurchase'));
    }

    private function shopJointPurchaseQuery(int $shopId)
    {
        return DB::table('joint_purchases')
            ->join('products', 'joint_purchases.product_id', '=', 'products.id')
            ->join('shop_channel_products', function ($join) use ($shopId) {
                $join->on('shop_channel_products.product_id', '=', 'products.id')
                    ->where('shop_channel_products.shop_channel_id', '=', $shopId)
                    ->where('shop_channel_products.status', '=', 1)
                    ->where('shop_channel_products.approval_status', '=', 'approved');
            })
            ->select('joint_purchases.*', 'products.product_name', 'products.product_code', 'products.product_price')
            ->where('joint_purchases.status', 1);
    }

    public function shopNotices(Request $request)
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $data = $request->validate(['search' => 'nullable|string|max:100']);
        $notices = ShopChannelNotice::where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->when(! empty($data['search']), fn ($query) => $query->where('title', 'like', '%'.$data['search'].'%'))
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withQueryString();

        return view('shop.notices', compact('shop', 'notices'));
    }

    public function shopNoticeDetails(int $id)
    {
        $shop = app(ShopChannelRuntime::class)->currentChannel();
        $notice = ShopChannelNotice::where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->findOrFail($id);
        $notice->increment('view_count');
        $adjacentQuery = ShopChannelNotice::where('shop_channel_id', $shop->id)->where('status', 1);
        $previousNotice = (clone $adjacentQuery)->where('id', '<', $notice->id)->orderByDesc('id')->first();
        $nextNotice = (clone $adjacentQuery)->where('id', '>', $notice->id)->orderBy('id')->first();

        return view('shop.notice_details', compact('shop', 'notice', 'previousNotice', 'nextNotice'));
    }

    public function shopNoticeAttachment(int $id)
    {
        $shop = app(ShopChannelRuntime::class)->currentChannel();
        $notice = ShopChannelNotice::where('shop_channel_id', $shop->id)->where('status', 1)->findOrFail($id);
        abort_unless($notice->attachment, 404);
        $path = public_path('uploads/notices/'.basename($notice->attachment));
        abort_unless(is_file($path), 404);

        return response()->download($path);
    }

}
