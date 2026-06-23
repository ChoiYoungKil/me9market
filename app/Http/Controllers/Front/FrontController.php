<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Faq;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        return view('front.index');
    }

    public function notice(Request $request)
    {
        $this->ensureCustomerCenterSeeded();
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
        $this->ensureCustomerCenterSeeded();
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
        $order = \App\Models\Order::find($orderId);
        if (!$order) {
            // Ensure User 1 exists
            $user = \App\Models\User::find(1);
            if (!$user) {
                \Illuminate\Support\Facades\DB::table('users')->insert([
                    'id' => 1,
                    'name' => '일반사용자',
                    'username' => 'user@user.com',
                    'email' => 'user@user.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                    'mobile' => '01033334444',
                    'address' => 'Seoul, Korea',
                    'city' => 'Seoul',
                    'state' => 'Seoul',
                    'country' => 'Korea',
                    'pincode' => '12345',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Ensure Section 1 exists
            $section = \Illuminate\Support\Facades\DB::table('sections')->where('id', 1)->first();
            if (!$section) {
                \Illuminate\Support\Facades\DB::table('sections')->insert([
                    'id' => 1,
                    'name' => '의류',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Ensure Brand 1 exists
            $brand = \Illuminate\Support\Facades\DB::table('brands')->where('id', 1)->first();
            if (!$brand) {
                \Illuminate\Support\Facades\DB::table('brands')->insert([
                    'id' => 1,
                    'name' => 'Me9 브랜드',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Ensure Category 1 exists
            $category = \Illuminate\Support\Facades\DB::table('categories')->where('id', 1)->first();
            if (!$category) {
                \Illuminate\Support\Facades\DB::table('categories')->insert([
                    'id' => 1,
                    'parent_id' => 0,
                    'section_id' => 1,
                    'category_name' => '티셔츠',
                    'category_image' => '',
                    'url' => 't-shirts',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Ensure Product 1 exists
            $product = \App\Models\Product::find(1);
            if (!$product) {
                \Illuminate\Support\Facades\DB::table('products')->insert([
                    'id' => 1,
                    'section_id' => 1,
                    'category_id' => 1,
                    'brand_id' => 1,
                    'vendor_id' => 1,
                    'admin_id' => 1,
                    'admin_type' => 'admin',
                    'product_name' => 'BlueViolet a omnis',
                    'product_code' => 'a0029',
                    'product_color' => 'BlueViolet',
                    'product_price' => 3500,
                    'product_discount' => 0,
                    'product_weight' => 1,
                    'description' => 'Sample product',
                    'is_featured' => 'No',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Ensure Product 2 exists
            $product2 = \App\Models\Product::find(2);
            if (!$product2) {
                \Illuminate\Support\Facades\DB::table('products')->insert([
                    'id' => 2,
                    'section_id' => 1,
                    'category_id' => 1,
                    'brand_id' => 1,
                    'vendor_id' => 1,
                    'admin_id' => 1,
                    'admin_type' => 'admin',
                    'product_name' => 'Red Rose T-Shirt',
                    'product_code' => 'a0030',
                    'product_color' => 'Red',
                    'product_price' => 4500,
                    'product_discount' => 0,
                    'product_weight' => 1,
                    'description' => 'Sample product 2',
                    'is_featured' => 'No',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Create sample order
            \Illuminate\Support\Facades\DB::table('orders')->insert([
                'id' => $orderId,
                'user_id' => 1,
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
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create sample order_products
            \Illuminate\Support\Facades\DB::table('orders_products')->insert([
                [
                    'id' => 101,
                    'order_id' => $orderId,
                    'user_id' => 1,
                    'vendor_id' => 1,
                    'admin_id' => 1,
                    'product_id' => 1,
                    'product_code' => 'a0029',
                    'product_name' => 'BlueViolet a omnis',
                    'product_color' => 'BlueViolet',
                    'product_size' => 'RD/S',
                    'product_price' => 3500,
                    'product_qty' => 1,
                    'item_status' => 'New',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 102,
                    'order_id' => $orderId,
                    'user_id' => 1,
                    'vendor_id' => 1,
                    'admin_id' => 1,
                    'product_id' => 2,
                    'product_code' => 'a0030',
                    'product_name' => 'Red Rose T-Shirt',
                    'product_color' => 'Red',
                    'product_size' => 'RD/M',
                    'product_price' => 4500,
                    'product_qty' => 1,
                    'item_status' => 'Shipped',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
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

    public function nonmemberOrderClaimSubmit(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'order_product_id' => 'required',
            'type' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'required'
        ]);

        $order = \App\Models\Order::find($request->order_id);
        if (!$order) abort(404);

        $ordersProduct = \App\Models\OrdersProduct::where('id', $request->order_product_id)
            ->where('order_id', $order->id)
            ->first();
        if (!$ordersProduct) abort(404);

        if ($request->type == 'confirm') {
            $ordersProduct->item_status = 'Confirmed';
            $ordersProduct->updated_at = now();
            $ordersProduct->save();

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
            $statusMap = [
                'cancel' => 'Cancel Requested',
                'return' => 'Return Requested',
                'exchange' => 'Exchange Requested'
            ];

            $ordersProduct->item_status = $statusMap[$claimType];
            $ordersProduct->updated_at = now();
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

        \Illuminate\Support\Facades\DB::table('contacts')->insert([
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
        return view('shop.gate');
    }

    public function shopGateSubmit(Request $request)
    {
        $request->validate([
            'entry_code' => 'required'
        ]);
        if ($request->entry_code == 'me9') {
            return redirect()->route('shop.channel_main')->with('flash_message_success', '입장 코드가 인증되었습니다!');
        }
        return redirect()->back()->with('flash_message_error', '입장 코드가 올바르지 않습니다. (테스트 코드: me9)');
    }

    public function shopRegister()
    {
        return view('shop.register');
    }

    public function shopRegisterSubmit(Request $request)
    {
        return redirect()->route('shop.channel_main')->with('flash_message_success', '간편회원 가입이 완료되었습니다!');
    }

    public function shopMain()
    {
        return view('shop.channel_main');
    }

    public function shopProducts()
    {
        return view('shop.products_list');
    }

    public function shopProductDetails($id)
    {
        return view('shop.product_details', compact('id'));
    }

    public function shopJointPurchases()
    {
        return view('shop.joint_purchases_list');
    }

    public function shopJointPurchaseDetails($id)
    {
        return view('shop.joint_purchase_details', compact('id'));
    }

    public function shopNotices()
    {
        return view('shop.notices');
    }

    public function storyboardTestbed()
    {
        $this->ensureCustomerCenterSeeded();
        return view('front.storyboard_testbed');
    }

    private function ensureCustomerCenterSeeded()
    {
        // 1. Seed Notices if empty
        if (\Illuminate\Support\Facades\DB::table('notices')->count() == 0) {
            \Illuminate\Support\Facades\DB::table('notices')->insert([
                [
                    'id' => 1,
                    'title' => '[중요] Me9 Market 서비스 정기 점검 안내',
                    'content' => '안녕하세요. Me9 Market 입니다. 서비스 안정화를 위해 정기 점검이 진행될 예정이오니 서비스 이용에 참고하시기 바랍니다.',
                    'is_important' => true,
                    'view_count' => 124,
                    'status' => 1,
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(5)
                ],
                [
                    'id' => 2,
                    'title' => '배송 지연 관련 안내 및 사과문',
                    'content' => '최근 기상 악화로 인해 일부 지역 배송이 지연되고 있습니다. 머리 숙여 사과드리며 최대한 신속히 배송해 드리겠습니다.',
                    'is_important' => false,
                    'view_count' => 45,
                    'status' => 1,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2)
                ],
                [
                    'id' => 3,
                    'title' => '신용카드 무이자 할부 혜택 변경 안내',
                    'content' => '2026년 7월 기준 카드사 무이자 할부 혜택 변경 내용을 안내해 드립니다. 자세한 내용은 상세 이미지를 참고해 주세요.',
                    'is_important' => false,
                    'view_count' => 88,
                    'status' => 1,
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay()
                ]
            ]);
        }

        // 2. Seed Faqs if empty
        if (\Illuminate\Support\Facades\DB::table('faqs')->count() == 0) {
            \Illuminate\Support\Facades\DB::table('faqs')->insert([
                [
                    'id' => 1,
                    'category' => '주문/배송',
                    'question' => '비회원 주문 조회는 어떻게 하나요?',
                    'answer' => '메인 홈페이지 우측 상단 또는 로그인 화면 하단의 [주문조회] 메뉴에서 주문 완료 시 부여된 주문번호와 결제 시 입력했던 연락처를 입력하시면 실시간으로 주문상세 내역 확인 및 취소/반품/교환 처리가 가능합니다.',
                    'order' => 1,
                    'view_count' => 230,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 2,
                    'category' => '환불/반품',
                    'question' => '반품 수거 신청은 어떻게 처리되나요?',
                    'answer' => '반품 신청 시 [자동회수]를 선택하시면 지정된 배송주소로 택배사 기사님이 영업일 기준 2~3일 내에 수거하러 방문합니다. [수동회수]를 선택하시면 본인이 직접 선불 택배를 이용하여 지정된 회수 주소(서울시 마포구 공덕동)로 제품을 발송해 주셔야 합니다.',
                    'order' => 2,
                    'view_count' => 150,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 3,
                    'category' => '회원정보',
                    'question' => '간편 회원가입 시 필수 동의 항목이 무엇인가요?',
                    'answer' => '간편회원 가입 및 연동 진행 시 이용약관 동의 및 개인정보 수집 및 이용에 관한 안내는 필수 동의 사항입니다. 마케팅 활용동의 및 알림 정보 수신동의 등은 선택 동의 사항으로 회원 가입 후 회원정보 수정 메뉴에서 변경하실 수 있습니다.',
                    'order' => 3,
                    'view_count' => 95,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }
}

