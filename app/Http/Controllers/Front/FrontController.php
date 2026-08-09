<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Faq;
use App\Services\ShopChannelRuntime;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FrontController extends Controller
{
    public function index()
    {
        return view('front.index');
    }

    public function notice(Request $request)
    {
        $query = Notice::where('status', 1)->orderBy('is_important', 'desc')->orderBy('created_at', 'desc');

        // 검색 기능
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
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
                $query->where('question', 'like', '%' . $search_value . '%');
            } elseif ($search_type == 'answer') {
                $query->where('answer', 'like', '%' . $search_value . '%');
            } else {
                // 질문 + 답변
                $query->where(function($q) use ($search_value) {
                    $q->where('question', 'like', '%' . $search_value . '%')
                      ->orWhere('answer', 'like', '%' . $search_value . '%');
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

    private function ensureSampleOrderExists()
    {
        $orderId = 32022;
        $now = now();
        $shop = app(ShopChannelRuntime::class)->seedDemoDataIfAllowed();
        if (!$shop) {
            return;
        }
        $vendorId = (int) $shop->vendor_id;
        $adminId = (int) (DB::table('admins')
            ->where('vendor_id', $vendorId)
            ->where('type', 'vendor')
            ->value('id') ?: 1);

        $user = DB::table('users')->where('email', 'user@user.com')->first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'name' => '일반사용자',
                'username' => 'user@user.com',
                'email' => 'user@user.com',
                'password' => Hash::make('123456'),
                'mobile' => '01033334444',
                'address' => 'Seoul, Korea',
                'city' => 'Seoul',
                'state' => 'Seoul',
                'country' => 'Korea',
                'pincode' => '12345',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        } else {
            $userId = $user->id;
        }

        $sectionId = DB::table('sections')->where('name', '의류')->value('id');
        if (!$sectionId) {
            $sectionId = DB::table('sections')->insertGetId([
                'name' => '의류',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        $brandId = DB::table('brands')->where('name', 'Me9 브랜드')->value('id');
        if (!$brandId) {
            $brandId = DB::table('brands')->insertGetId([
                'name' => 'Me9 브랜드',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        $categoryId = DB::table('categories')->where('url', 't-shirts')->value('id');
        if (!$categoryId) {
            $categoryId = DB::table('categories')->insertGetId([
                'parent_id' => 0,
                'section_id' => $sectionId,
                'category_name' => '티셔츠',
                'category_image' => '',
                'url' => 't-shirts',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        $products = [
            'a0029' => [
                'product_name' => 'BlueViolet a omnis',
                'product_color' => 'BlueViolet',
                'product_price' => 3500,
                'description' => 'Sample product',
            ],
            'a0030' => [
                'product_name' => 'Red Rose T-Shirt',
                'product_color' => 'Red',
                'product_price' => 4500,
                'description' => 'Sample product 2',
            ],
        ];

        $productIds = [];
        foreach ($products as $code => $product) {
            $productId = DB::table('products')->where('product_code', $code)->value('id');
            if (!$productId) {
                $productId = DB::table('products')->insertGetId([
                    'section_id' => $sectionId,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'vendor_id' => $vendorId,
                    'admin_id' => $adminId,
                    'admin_type' => 'vendor',
                    'product_name' => $product['product_name'],
                    'product_code' => $code,
                    'product_color' => $product['product_color'],
                    'product_price' => $product['product_price'],
                    'product_discount' => 0,
                    'product_weight' => 1,
                    'description' => $product['description'],
                    'is_featured' => 'No',
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            } else {
                DB::table('products')->where('id', $productId)->update([
                    'vendor_id' => $vendorId,
                    'admin_id' => $adminId,
                    'admin_type' => 'vendor',
                    'updated_at' => $now,
                ]);
            }

            $productIds[$code] = $productId;
        }

        $shopProductIds = [];
        foreach ($products as $code => $product) {
            $shopProductId = DB::table('shop_channel_products')
                ->where('shop_channel_id', $shop->id)
                ->where('product_id', $productIds[$code])
                ->value('id');

            if (!$shopProductId) {
                $shopProductId = DB::table('shop_channel_products')->insertGetId([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $productIds[$code],
                    'product_type' => 'own',
                    'approval_status' => 'approved',
                    'status' => 1,
                    'constraint_type' => 'none',
                    'stock' => 100,
                    'purchase_limit' => 10,
                    'product_price' => $product['product_price'],
                    'selling_price' => $product['product_price'],
                    'profit' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $shopProductIds[$code] = $shopProductId;
        }

        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            DB::table('orders')->insert([
                'id' => $orderId,
                'user_id' => $userId,
                'name' => '홍길동',
                'address' => '서울시 마포구 공덕동 1118-12 B112',
                'city' => '서울시',
                'state' => '마포구',
                'country' => '대한민국',
                'pincode' => '00234',
                'mobile' => '010-1234-5678',
                'email' => 'test1234@naver.com',
                'shipping_charges' => 2500,
                'coupon_code' => '',
                'coupon_amount' => 0,
                'order_status' => 'Payment Captured',
                'payment_method' => 'Credit Card',
                'payment_gateway' => 'KCP',
                'grand_total' => 10500,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        $orderItems = [
            [
                'product_id' => $productIds['a0029'],
                'product_code' => 'a0029',
                'product_name' => 'BlueViolet a omnis',
                'product_color' => 'BlueViolet',
                'product_size' => 'RD/S',
                'product_price' => 3500,
                'product_qty' => 1,
                'status_code' => OrderItemStatus::PAID,
                'item_status' => OrderItemStatus::label(OrderItemStatus::PAID),
            ],
            [
                'product_id' => $productIds['a0030'],
                'product_code' => 'a0030',
                'product_name' => 'Red Rose T-Shirt',
                'product_color' => 'Red',
                'product_size' => 'RD/M',
                'product_price' => 4500,
                'product_qty' => 1,
                'status_code' => OrderItemStatus::SHIPPING,
                'item_status' => OrderItemStatus::label(OrderItemStatus::SHIPPING),
            ],
        ];

        foreach ($orderItems as $item) {
            $existing = DB::table('orders_products')
                ->where('order_id', $orderId)
                ->where('product_code', $item['product_code'])
                ->first();

            if ($existing) {
                DB::table('orders_products')->where('id', $existing->id)->update([
                    'user_id' => $userId,
                    'vendor_id' => $vendorId,
                    'shop_channel_id' => $shop->id,
                    'shop_channel_product_id' => $shopProductIds[$item['product_code']],
                    'admin_id' => $adminId,
                    'product_id' => $item['product_id'],
                    'status_code' => OrderItemStatus::normalize($existing->status_code ?: $existing->item_status),
                    'item_status' => OrderItemStatus::label(OrderItemStatus::normalize($existing->status_code ?: $existing->item_status)),
                    'updated_at' => $now,
                ]);
                continue;
            }

            DB::table('orders_products')->insert(array_merge($item, [
                'order_id' => $orderId,
                'user_id' => $userId,
                'vendor_id' => $vendorId,
                'shop_channel_id' => $shop->id,
                'shop_channel_product_id' => $shopProductIds[$item['product_code']],
                'admin_id' => $adminId,
                'supply_price' => $item['product_price'],
                'selling_price' => $item['product_price'],
                'line_total' => $item['product_price'] * $item['product_qty'],
                'created_at' => $now,
                'updated_at' => $now
            ]));
        }
    }

    public function nonmemberOrderCheck()
    {
        return view('front.pages.nonmember_order_check');
    }

    public function nonmemberOrderCheckSubmit(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'phone' => 'required'
        ]);

        $this->ensureSampleOrderExists();

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

        $order = \App\Models\Order::where('id', $id)->first();
        if (!$order) {
            $order = \App\Models\Order::where('id', $request->order_id)->first();
        }

        if (!$order) {
            return redirect()->back()->with('flash_message_error', '입력하신 주문 정보와 일치하는 주문을 찾을 수 없습니다.');
        }

        $orderPhone = str_replace('-', '', $order->mobile);
        if ($orderPhone !== $cleanPhone) {
            return redirect()->back()->with('flash_message_error', '주문번호와 연락처가 일치하지 않습니다.');
        }

        \Illuminate\Support\Facades\Session::put('nonmember_order_id', $order->id);

        return redirect()->route('front.nonmember.order_details');
    }

    public function nonmemberOrderDetails()
    {
        $this->ensureSampleOrderExists();

        $orderId = \Illuminate\Support\Facades\Session::get('nonmember_order_id');
        if (!$orderId) {
            return redirect()->route('front.nonmember.order_check')->with('flash_message_error', '주문 조회를 먼저 완료해 주세요.');
        }

        $order = \App\Models\Order::with(['orders_products.product', 'claims'])->find($orderId);
        if (!$order) {
            return redirect()->route('front.nonmember.order_check')->with('flash_message_error', '해당 주문을 찾을 수 없습니다.');
        }

        return view('front.pages.nonmember_order_details', compact('order'));
    }

    public function downloadOrderInvoice($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();
        $sessionOrderIds = array_filter([
            \Illuminate\Support\Facades\Session::get('nonmember_order_id'),
            \Illuminate\Support\Facades\Session::get('shop_order_id'),
            \Illuminate\Support\Facades\Session::get('last_shop_order_id'),
        ]);

        $isOwnedByUser = $user && (int) $order->user_id === (int) $user->id;
        $isVerifiedSessionOrder = in_array((int) $order->id, array_map('intval', $sessionOrderIds), true);

        if (!$isOwnedByUser && !$isVerifiedSessionOrder) {
            abort(403);
        }

        return app(\App\Http\Controllers\Admin\OrderController::class)->viewPDFInvoice($order->id);
    }

    public function nonmemberOrderClaimSubmit(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'order_product_id' => 'required',
            'type' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'required_unless:type,confirm'
        ]);

        $order = \App\Models\Order::find($request->order_id);
        if (!$order) abort(404);

        $ordersProduct = \App\Models\OrdersProduct::where('id', $request->order_product_id)
            ->where('order_id', $order->id)
            ->first();
        if (!$ordersProduct) abort(404);

        if ($request->type == 'confirm') {
            $ordersProduct->setStatus(OrderItemStatus::CONFIRMED);
            $ordersProduct->confirmed_at = now();
            $ordersProduct->save();
            app(\App\Services\ChannelPointService::class)->recordCustomerPayback($ordersProduct);

            // Save rating and review to ratings table
            \Illuminate\Support\Facades\DB::table('ratings')->insert([
                'user_id' => $order->user_id ?? 1,
                'product_id' => $ordersProduct->product_id,
                'rating' => intval($request->rating ?? 5),
                'review' => $request->review ?? '이 상품을 구매하겠습니다.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

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
            if ($claimType == 'return' || $claimType == 'exchange') {
                $recoveryMethod = $request->recovery_method ?? '자동회수';
                $recoveryAddress = $request->recovery_address ?? '';
                $detailReason = "[회수방법: {$recoveryMethod}] 주소: {$recoveryAddress}";
                if ($request->detail_reason) {
                    $detailReason .= " | 상세사유: " . $request->detail_reason;
                }
            }

            \App\Models\OrderClaim::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'vendor_id' => $ordersProduct->vendor_id,
                'order_product_id' => $ordersProduct->id,
                'type' => $claimType,
                'reason' => $request->reason,
                'detail_reason' => $detailReason,
                'status' => 'requested',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $label = $claimType == 'cancel' ? '취소' : ($claimType == 'return' ? '반품' : '교환');
            return redirect()->back()->with('flash_message_success', $label . ' 신청이 완료되었습니다.');
        }
    }

    public function nonmemberOrderInquirySubmit(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'order_product_id' => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $order = \App\Models\Order::find($request->order_id);
        if (!$order) abort(404);

        $ordersProduct = \App\Models\OrdersProduct::where('id', $request->order_product_id)
            ->where('order_id', $order->id)
            ->first();
        if (!$ordersProduct) abort(404);

        $shopChannelId = $ordersProduct->shop_channel_id;
        if (!$shopChannelId && $ordersProduct->shop_channel_product_id) {
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
            'subject' => '[상품문의] ' . $request->subject . ' (상품: ' . $ordersProduct->product_name . ')',
            'message' => $request->message,
            'type' => 'inquiry',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('flash_message_success', '상품 문의가 등록되었습니다.');
    }

    public function shopGate()
    {
        app(ShopChannelRuntime::class)->seedDemoDataIfAllowed();
        return view('shop.gate');
    }

    public function shopGateSubmit(Request $request)
    {
        $request->validate([
            'entry_code' => 'required'
        ]);

        $shop = app(ShopChannelRuntime::class)->enterChannel($request->entry_code);
        if ($shop) {
            return redirect()->route('shop.channel_main')->with('flash_message_success', $shop->channel_name . '에 입장했습니다.');
        }

        $message = '입장 코드가 올바르지 않습니다.';
        if (config('shop_channel.show_demo_credentials', false)) {
            $message .= ' 테스트 기본 코드는 me9 입니다.';
        }

        return redirect()->back()->with('flash_message_error', $message);
    }

    public function shopEnter(string $channelCode)
    {
        $shop = app(ShopChannelRuntime::class)->enterChannel($channelCode);
        if (!$shop) {
            return redirect()->route('shop.gate')->with('flash_message_error', '운영 중인 Shop 채널을 찾을 수 없습니다.');
        }

        return redirect()->route('shop.channel_main')->with('flash_message_success', $shop->channel_name . '에 입장했습니다.');
    }

    public function shopRegister()
    {
        return view('shop.register');
    }

    public function shopRegisterSubmit(Request $request)
    {
        app(ShopChannelRuntime::class)->enterChannel('me9');
        return redirect()->route('shop.channel_main')->with('flash_message_success', '간편회원 가입이 완료되었습니다!');
    }

    public function shopMain()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $products = $runtime->products()->take(4);
        $jointPurchases = \Illuminate\Support\Facades\DB::table('joint_purchases')
            ->join('products', 'joint_purchases.product_id', '=', 'products.id')
            ->select('joint_purchases.*', 'products.product_name', 'products.product_code', 'products.product_price')
            ->where('joint_purchases.status', 1)
            ->orderBy('joint_purchases.end_date')
            ->take(2)
            ->get();

        return view('shop.channel_main', compact('shop', 'products', 'jointPurchases'));
    }

    public function shopProducts()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $products = $runtime->products();

        return view('shop.products_list', compact('shop', 'products'));
    }

    public function shopProductDetails($id)
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $shopProduct = \App\Models\ShopChannelProduct::with(['product.images', 'shopChannel'])
            ->where('shop_channel_id', $shop->id)
            ->where('id', $id)
            ->first();

        if (!$shopProduct) {
            $shopProduct = \App\Models\ShopChannelProduct::with(['product.images', 'shopChannel'])
                ->where('shop_channel_id', $shop->id)
                ->where('status', 1)
                ->firstOrFail();
        }

        return view('shop.product_details', compact('shop', 'shopProduct'));
    }

    public function shopJointPurchases()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $jointPurchases = \Illuminate\Support\Facades\DB::table('joint_purchases')
            ->join('products', 'joint_purchases.product_id', '=', 'products.id')
            ->select('joint_purchases.*', 'products.product_name', 'products.product_code', 'products.product_price')
            ->where('joint_purchases.status', 1)
            ->orderBy('joint_purchases.end_date')
            ->get();

        return view('shop.joint_purchases_list', compact('shop', 'jointPurchases'));
    }

    public function shopJointPurchaseDetails($id)
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $jointPurchase = \Illuminate\Support\Facades\DB::table('joint_purchases')
            ->join('products', 'joint_purchases.product_id', '=', 'products.id')
            ->select('joint_purchases.*', 'products.product_name', 'products.product_code', 'products.product_price')
            ->where('joint_purchases.id', $id)
            ->first();

        if (!$jointPurchase) {
            $jointPurchase = \Illuminate\Support\Facades\DB::table('joint_purchases')
                ->join('products', 'joint_purchases.product_id', '=', 'products.id')
                ->select('joint_purchases.*', 'products.product_name', 'products.product_code', 'products.product_price')
                ->firstOrFail();
        }

        return view('shop.joint_purchase_details', compact('shop', 'jointPurchase'));
    }

    public function shopNotices()
    {
        $runtime = app(ShopChannelRuntime::class);
        $shop = $runtime->currentChannel();
        $notices = \App\Models\ShopChannelNotice::where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shop.notices', compact('shop', 'notices'));
    }

    public function storyboardTestbed()
    {
        app(ShopChannelRuntime::class)->seedDemoDataIfAllowed();

        return view('front.storyboard_testbed');
    }
}
