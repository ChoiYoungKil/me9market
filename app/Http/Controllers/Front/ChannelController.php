<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Services\ChannelOrderMetrics;
use App\Services\ShopChannelRuntime;
use App\Support\OrderItemStatus;

class ChannelController extends Controller
{


    public function index()
    {
        // 판매자 제한 확인
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if ($user->type == 'vendor' && $user->status == 0) {
                return redirect()->route('channel.complete_profile');
            }
            
            $vendor_id = $user->vendor_id;

            // 1. Order Status Counts
            $data = app(ChannelOrderMetrics::class)->counts($vendor_id);

            // 2. Recent Orders (Top 3)
            $recentOrders = \App\Models\Order::with(['orders_products' => function($q) use ($vendor_id) {
                $q->where('vendor_id', $vendor_id);
            }, 'user'])
            ->whereHas('orders_products', function($q) use ($vendor_id) {
                $q->where('vendor_id', $vendor_id);
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
            // Transform for view
            $recentOrders->transform(function($order) {
                $order->items = $order->orders_products;
                return $order;
            });

            // 3. Monthly Sales (Line Chart) - Current Year
            $monthlySales = \App\Models\OrdersProduct::where('vendor_id', $vendor_id)
                ->whereYear('created_at', date('Y'))
                ->select(
                    \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month'),
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN line_total > 0 THEN line_total ELSE product_price * product_qty END) as total_sales')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total_sales', 'month')
                ->all();
            
            // Fill missing months with 0
            $chartData = [];
            for ($i = 1; $i <= 12; $i++) {
                $chartData[] = $monthlySales[$i] ?? 0;
            }

            $categoryRows = \App\Models\OrdersProduct::where('orders_products.vendor_id', $vendor_id)
                ->leftJoin('products', 'orders_products.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(categories.category_name, "미분류") as category_name'),
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN orders_products.line_total > 0 THEN orders_products.line_total ELSE orders_products.product_price * orders_products.product_qty END) as total_sales')
                )
                ->groupBy('category_name')
                ->orderByDesc('total_sales')
                ->limit(6)
                ->get();

            $categoryChart = [
                'labels' => $categoryRows->pluck('category_name')->values(),
                'data' => $categoryRows->pluck('total_sales')->map(fn ($value) => (float) $value)->values(),
            ];

            $recentInquiries = Contact::with(['orderProduct', 'shopChannel'])
                ->where('vendor_id', $vendor_id)
                ->whereNotNull('order_product_id')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            return view('channel.index', [
                'dep1_id' => '00',
                'user' => $user,
                'counts' => $data,
                'recentOrders' => $recentOrders,
                'recentInquiries' => $recentInquiries,
                'chartData' => $chartData,
                'categoryChart' => $categoryChart,
            ]);
        }
        return redirect()->route('channel.login');
    }

    public function login()
    {
        app(ShopChannelRuntime::class)->seedDemoDataIfAllowed();

        return view('channel.login');
    }

    public function loginUser(Request $request)
    {
        app(ShopChannelRuntime::class)->seedDemoDataIfAllowed();

        if (Auth::guard('admin')->check()) {
            if (Auth::guard('admin')->user()->type == 'vendor') {
                return redirect()->route('channel.index');
            }
            return redirect('/admin/dashboard');
        }

        $rules = [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ];

        $customMessages = [
            'email.required'    => '이메일 주소를 입력해 주세요!',
            'email.email'       => '유효한 이메일 주소를 입력해 주세요',
            'password.required' => '비밀번호를 입력해 주세요!',
        ];

        $request->validate($rules, $customMessages);

        // Attempt login using 'admin' guard
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('admin')->user();

            if ($user->type !== 'vendor') {
                Auth::guard('admin')->logout();
                return redirect()->route('channel.login')->with('error_message', '판매자 전용 로그인 페이지입니다. 최고관리자 계정은 로그인할 수 없습니다.');
            }

            if ($user->confirm == 'No') {
                Auth::guard('admin')->logout();
                return redirect()->route('channel.login')->with('error_message', '판매자 계정 활성화를 위해 이메일 인증을 완료해 주세요.');
            }

            if ($user->status == 0) {
                Auth::guard('admin')->logout();
                return redirect()->route('channel.login')->with('error_message', '비활성화된 판매자 계정입니다. 관리자 승인을 대기해 주세요.');
            }

            return redirect()->route('channel.index');
        } else {
            return redirect()->route('channel.login')->with('error_message', '이메일 또는 비밀번호가 일치하지 않습니다.');
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('channel.login');
    }

    // 상점 관리 (Sub01)
    public function shopList(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $query = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id);

        // 검색 필터
        if ($request->filled('search_name')) {
            $query->where('channel_name', 'like', '%' . $request->search_name . '%');
        }
        if ($request->filled('search_keyword')) {
            $query->where('keywords', 'like', '%' . $request->search_keyword . '%');
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 범위 필터 (공개/비공개, 회원전용)
        if ($request->filled('is_public')) {
            $query->whereIn('is_public', $request->is_public);
        }
        if ($request->filled('is_member_only')) {
            $query->whereIn('is_member_only', $request->is_member_only);
        }

        $shops = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('channel.sub01.shop_list', [
            'dep1_id' => '01',
            'shops' => $shops,
            'params' => $request->all()
        ]);
    }

    public function shopRegister()
    {
        return view('channel.sub01.shop_register', ['dep1_id' => '01']);
    }

    public function privateAccessTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('비공개 입장자');
        $sheet->fromArray([
            ['휴대폰번호', '입장코드', '회원ID(선택)'],
            ['010-1234-5678', 'ABCD1234', ''],
        ]);
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A:C')->getNumberFormat()->setFormatCode('@');
        foreach (['A' => 20, 'B' => 20, 'C' => 16] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'shop-channel-private-access-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function shopRegisterSubmit(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        // 1. Validation Rules
        $rules = [
            'channel_name' => 'required|max:255',
            'copyright' => 'required|max:255',
            'keywords' => 'required|array|min:1',
        ];

        $messages = [
            'channel_name.required' => '채널명을 입력해 주세요.',
            'copyright.required' => '카피라이트를 입력해 주세요.',
            'keywords.required' => '최소 하나 이상의 키워드를 입력해 주세요.',
        ];

        // 조건부 유효성 검사 추가
        $data = $request->all();

        $messages = [
            'channel_name.required' => '채널명을 입력해 주세요.',
            'copyright.required' => '카피라이트를 입력해 주세요.',
            'keywords.required' => '최소 하나 이상의 키워드를 입력해 주세요.',
            'start_date.required' => '사용 시작 날짜를 선택해 주세요.',
            'start_hour.required' => '사용 시작 시간을 선택해 주세요.',
            'end_date.required' => '사용 종료 날짜를 선택해 주세요.',
            'end_date.after_or_equal' => '종료일은 시작일 이후여야 합니다.',
            'end_hour.required' => '사용 종료 시간을 선택해 주세요.',
        ];

        // 비공개시 비밀번호 체크
        if (($data['is_public'] ?? '1') == '0') {
            $rules['password'] = 'required|min:4';
            $messages['password.required'] = '비공개 설정 시 비밀번호를 입력해 주세요.';
        }
        $rules['private_access_rows'] = 'nullable|string';
        $rules['private_access_file'] = 'nullable|file|max:5120|mimes:xlsx,xls,csv,txt';
        $rules['purchase_sms_templates.purchase'] = 'nullable|string|max:500';
        $rules['purchase_sms_templates.purchase_confirmed'] = 'nullable|string|max:500';
        $rules['purchase_sms_templates.cancel'] = 'nullable|string|max:500';
        $rules['purchase_sms_templates.return'] = 'nullable|string|max:500';

        // 사용기간(기간제) 체크
        if (($data['use_period_type'] ?? '0') == '1') {
            $rules['start_date'] = 'required|date';
            $rules['start_hour'] = 'required';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $rules['end_hour'] = 'required';
            $messages['start_date.required'] = '사용 시작 날짜를 선택해 주세요.';
            $messages['start_hour.required'] = '사용 시작 시간을 선택해 주세요.';
            $messages['end_date.required'] = '사용 종료 날짜를 선택해 주세요.';
            $messages['end_date.after_or_equal'] = '종료일은 시작일 이후여야 합니다.';
            $messages['end_hour.required'] = '사용 종료 시간을 선택해 주세요.';
        }

        // 로고 이미지 체크
        if (($data['use_logo'] ?? '0') == '1') {
            if (!$request->hasFile('logo_image')) {
                $rules['logo_image'] = 'required|image';
                $messages['logo_image.required'] = '사용할 로고 이미지를 업로드해 주세요.';
            }
        }

        // 메인 배너 체크
        if (($data['use_banner'] ?? '0') == '1') {
            if (!$request->hasFile('banner_files')) {
                $rules['banner_files'] = 'required|array|min:1';
                $messages['banner_files.required'] = '배너 이미지를 최소 1개 이상 등록해 주세요.';
            }
        }

        // OG TAG 체크
        if (($data['use_og'] ?? '0') == '1') {
            $rules['og_title'] = 'required|max:255';
            $rules['og_description'] = 'required|max:500';
            if (!$request->hasFile('og_image')) {
                $rules['og_image'] = 'required|image';
            }
            $messages['og_title.required'] = 'OG Title을 입력해 주세요.';
            $messages['og_description.required'] = 'OG Description을 입력해 주세요.';
            $messages['og_image.required'] = 'OG 이미지를 업로드해 주세요.';
        }

        if (($data['use_own_pg'] ?? '0') == '1') {
            $vendor = \App\Models\Vendor::find($admin->vendor_id);
            if (!$vendor || (int) $vendor->status !== 1) {
                return back()->withInput()->withErrors(['use_own_pg' => '판매인증이 완료된 채널만 자사 PG를 사용할 수 있습니다.']);
            }

            $rules['pg_provider'] = 'required|in:inicis,kcp,toss';
            $rules['pg_merchant_id'] = 'required|string|max:255';
            $rules['pg_client_key'] = 'required|string|max:255';
            $rules['pg_secret_key'] = 'required|string|max:255';
            $messages['pg_provider.required'] = '자사 PG사를 선택해 주세요.';
            $messages['pg_provider.in'] = '지원하는 PG사를 선택해 주세요.';
            $messages['pg_merchant_id.required'] = '자사 PG 상점 ID를 입력해 주세요.';
            $messages['pg_client_key.required'] = '자사 PG Client Key를 입력해 주세요.';
            $messages['pg_secret_key.required'] = '자사 PG Secret Key를 입력해 주세요.';
        }

        // 관리자 정보 체크
        if (($data['use_admin'] ?? '0') == '1') {
            $rules['admin_name'] = 'required|max:50';
            $rules['admin_login_id'] = 'required|max:50|unique:shop_channels,admin_login_id';
            $rules['admin_password'] = 'required|min:6';
            
            if (($data['settlement_type'] ?? '1') == '1') {
                $rules['settlement_rate_percent'] = 'required|numeric|between:0,100';
                $messages['settlement_rate_percent.required'] = '정산 요율(%)을 입력해 주세요.';
            } else {
                $rules['settlement_rate_amount'] = 'required|numeric|min:0';
                $messages['settlement_rate_amount.required'] = '정산 금액(원)을 입력해 주세요.';
            }

            $messages['admin_name.required'] = '관리자 성명을 입력해 주세요.';
            $messages['admin_login_id.required'] = '로그인 ID를 입력해 주세요.';
            $messages['admin_login_id.unique'] = '이미 사용 중인 관리자 ID입니다.';
            $messages['admin_password.required'] = '관리자 비밀번호를 입력해 주세요.';
        }

        $request->validate($rules, $messages);

        $data = $request->all();

        // 2. Create Instance
        $shop = new \App\Models\ShopChannel();
        $shop->vendor_id = $admin->vendor_id;
        $shop->channel_code = $data['channel_code'] ?? 'Me9-' . date('Y-md') . rand(10, 99);
        $shop->status = $data['status'] ?? 0;
        $shop->is_public = $data['is_public'] ?? 1;
        $shop->password = $data['password'] ?? null;
        $shop->is_member_only = ((string) ($data['is_public'] ?? '1') === '0') ? 0 : (isset($data['is_member_only']) ? 1 : 0);
        $shop->channel_name = $data['channel_name'];
        $shop->copyright = $data['copyright'];
        $shop->keywords = json_encode($data['keywords'], JSON_UNESCAPED_UNICODE);

        // 사용주기
        $shop->use_period_type = $data['use_period_type'] ?? 0;
        if ($shop->use_period_type == 1) {
            if (!empty($data['start_date']) && isset($data['start_hour']) && $data['start_hour'] !== '') {
                try {
                    $shop->start_at = \Carbon\Carbon::createFromFormat('Y-m-d H', $data['start_date'] . ' ' . $data['start_hour'])->startOfHour();
                } catch (\Exception $e) {
                    $shop->start_at = null;
                }
            }
            if (!empty($data['end_date']) && isset($data['end_hour']) && $data['end_hour'] !== '') {
                try {
                    $shop->end_at = \Carbon\Carbon::createFromFormat('Y-m-d H', $data['end_date'] . ' ' . $data['end_hour'])->startOfHour();
                } catch (\Exception $e) {
                    $shop->end_at = null;
                }
            }
        } else {
            $shop->start_at = null;
            $shop->end_at = null;
        }

        // 로고 이미지 처리
        $shop->use_logo = $data['use_logo'] ?? 0;
        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');
            $fileName = time() . '_logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/shop/logo'), $fileName);
            $shop->logo_image = 'uploads/shop/logo/' . $fileName;
        }

        // 메인 배너 처리
        $shop->use_banner = $data['use_banner'] ?? 0;
        if ($request->hasFile('banner_files')) {
            $banners = [];
            foreach ($request->file('banner_files') as $file) {
                $fileName = time() . '_' . rand(100, 999) . '_banner.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/shop/banner'), $fileName);
                $banners[] = 'uploads/shop/banner/' . $fileName;
            }
            $shop->banner_images = json_encode($banners);
        }

        // OG TAG 처리
        $shop->use_og = $data['use_og'] ?? 0;
        $shop->og_title = $data['og_title'] ?? null;
        $shop->og_description = $data['og_description'] ?? null;
        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $fileName = time() . '_og.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/shop/og'), $fileName);
            $shop->og_image = 'uploads/shop/og/' . $fileName;
        }

        $shop->use_own_pg = ($data['use_own_pg'] ?? '0') == '1';
        $shop->pg_provider = $shop->use_own_pg ? ($data['pg_provider'] ?? null) : null;
        $shop->pg_merchant_id = $shop->use_own_pg ? ($data['pg_merchant_id'] ?? null) : null;
        $shop->pg_site_code = $shop->use_own_pg ? ($data['pg_site_code'] ?? null) : null;
        $shop->pg_client_key = $shop->use_own_pg ? ($data['pg_client_key'] ?? null) : null;
        $shop->pg_secret_key = $shop->use_own_pg ? ($data['pg_secret_key'] ?? null) : null;

        $shop->use_purchase_sms = ($data['use_purchase_sms'] ?? '0') == '1';
        $shop->purchase_sms_templates = $shop->use_purchase_sms ? $this->purchaseSmsTemplates($data) : null;

        // 관리자 정보 처리
        $shop->use_admin = $data['use_admin'] ?? 0;
        $shop->admin_name = $data['admin_name'] ?? null;
        $shop->admin_login_id = $data['admin_login_id'] ?? null;
        if (!empty($data['admin_password'])) {
            $shop->admin_password = bcrypt($data['admin_password']);
        }
        $shop->settlement_type = $data['settlement_type'] ?? 1;
        if ($shop->settlement_type == 1) {
            $shop->settlement_rate = $data['settlement_rate_percent'] ?? 0;
        } else {
            $shop->settlement_rate = $data['settlement_rate_amount'] ?? 0;
        }

        $shop->save();
        $this->syncPrivateAccesses($shop, $this->combinedPrivateAccessRows($request, $shop));

        return redirect()->route('channel.shop_list')->with('success_message', 'Shop 채널이 성공적으로 등록되었습니다.');
    }

    public function shopInfo(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)
            ->where('id', $request->id)
            ->first();

        if (!$shop) {
            return redirect()->route('channel.shop_list')->with('error_message', '존재하지 않거나 권한이 없는 채널입니다.');
        }

        return view('channel.sub01.shop_info', [
            'dep1_id' => '01',
            'shop' => $shop
        ]);
    }

    public function deleteShop($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)
            ->where('id', $id)
            ->firstOrFail();

        $hasOrderItems = \App\Models\OrdersProduct::where('shop_channel_id', $shop->id)
            ->orWhereIn('shop_channel_product_id', function ($query) use ($shop) {
                $query->select('id')
                    ->from('shop_channel_products')
                    ->where('shop_channel_id', $shop->id);
            })
            ->exists();

        if ($hasOrderItems) {
            return redirect()->route('channel.shop_list')
                ->with('error_message', '주문 이력이 있는 Shop 채널은 삭제할 수 없습니다. 운영중지로 전환해 주세요.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($shop) {
            \App\Models\ShopChannelNotice::where('shop_channel_id', $shop->id)->delete();
            \App\Models\ShopChannelProduct::where('shop_channel_id', $shop->id)->delete();
            $shop->delete();
        });

        return redirect()->route('channel.shop_list')->with('success_message', 'Shop 채널이 삭제되었습니다.');
    }

    public function shopProduct01(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        // Get shop_id from request or use first shop for this vendor
        $shopId = $request->get('shop_id');
        if (!$shopId) {
            $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->first();
            $shopId = $shop ? $shop->id : null;
        } else {
            $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->where('id', $shopId)->first();
        }

        if (!$shopId || !$shop) {
            return redirect()->route('channel.shop_list')->with('error_message', 'Shop 채널을 먼저 등록해 주세요.');
        }

        $popupFilters = $this->shopProductPopupFilters($request);

        // Fetch products for this shop channel with status = 1 (판매중)
        $products = \App\Models\ShopChannelProduct::where('shop_channel_id', $shopId)
            ->where('status', 1)
            ->with(['product' => function($query) {
                $query->with(['category', 'images']);
            }])
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Fetch vendor's own products that are not yet added to this shop channel (for popup)
        $alreadyAddedProductIds = \App\Models\ShopChannelProduct::where('shop_channel_id', $shopId)
            ->pluck('product_id')
            ->toArray();

        $ownQuery = \App\Models\Product::where('vendor_id', $admin->vendor_id)
            ->where('status', 1)
            ->whereNotIn('id', $alreadyAddedProductIds)
            ->with(['category', 'images']);
        $this->applyPopupProductSearch($ownQuery, $popupFilters['own_q']);
        $ownProducts = $ownQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'own_page')
            ->appends($request->query());

        $publicProducts = $this->shopPopupProducts(
            $admin,
            $shopId,
            'public',
            $popupFilters,
            $request
        );
        $partialProducts = $this->shopPopupProducts(
            $admin,
            $shopId,
            'partial',
            $popupFilters,
            $request
        );

        return view('channel.sub01.shop_product01', [
            'dep1_id' => '01',
            'products' => $products,
            'shopId' => $shopId,
            'shop' => $shop,
            'ownProducts' => $ownProducts,
            'publicProducts' => $publicProducts,
            'partialProducts' => $partialProducts,
            'popupFilters' => $popupFilters,
        ]);
    }

    public function shopProduct02(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        // Get shop_id from request or use first shop for this vendor
        $shopId = $request->get('shop_id');
        if (!$shopId) {
            $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->first();
            $shopId = $shop ? $shop->id : null;
        } else {
            $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->where('id', $shopId)->first();
        }

        if (!$shopId || !$shop) {
            return redirect()->route('channel.shop_list')->with('error_message', 'Shop 채널을 먼저 등록해 주세요.');
        }

        $popupFilters = $this->shopProductPopupFilters($request);

        // Fetch products for this shop channel with status = 0 (판매중지)
        $products = \App\Models\ShopChannelProduct::where('shop_channel_id', $shopId)
            ->where('status', 0)
            ->with(['product' => function($query) {
                $query->with(['category', 'images']);
            }])
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Fetch vendor's own products that are not yet added to this shop channel (for popup)
        $alreadyAddedProductIds = \App\Models\ShopChannelProduct::where('shop_channel_id', $shopId)
            ->pluck('product_id')
            ->toArray();

        $ownQuery = \App\Models\Product::where('vendor_id', $admin->vendor_id)
            ->where('status', 1)
            ->whereNotIn('id', $alreadyAddedProductIds)
            ->with(['category', 'images']);
        $this->applyPopupProductSearch($ownQuery, $popupFilters['own_q']);
        $ownProducts = $ownQuery->orderByDesc('id')
            ->paginate(10, ['*'], 'own_page')
            ->appends($request->query());

        $publicProducts = $this->shopPopupProducts(
            $admin,
            $shopId,
            'public',
            $popupFilters,
            $request
        );
        $partialProducts = $this->shopPopupProducts(
            $admin,
            $shopId,
            'partial',
            $popupFilters,
            $request
        );

        return view('channel.sub01.shop_product02', [
            'dep1_id' => '01',
            'products' => $products,
            'shopId' => $shopId,
            'shop' => $shop,
            'ownProducts' => $ownProducts,
            'publicProducts' => $publicProducts,
            'partialProducts' => $partialProducts,
            'popupFilters' => $popupFilters,
        ]);
    }

    private function shopProductPopupFilters(Request $request): array
    {
        return [
            'own_q' => trim((string) $request->input('popup_own_q', '')),
            'public_q' => trim((string) $request->input('popup_public_q', '')),
            'partial_q' => trim((string) $request->input('popup_partial_q', '')),
        ];
    }

    private function applyPopupProductSearch($query, string $keyword): void
    {
        if ($keyword === '') {
            return;
        }

        $query->where(function ($search) use ($keyword) {
            $search->where('product_name', 'like', '%' . $keyword . '%')
                ->orWhere('product_code', 'like', '%' . $keyword . '%');
        });
    }

    private function shopPopupProducts($admin, int $shopId, string $type, array $filters, Request $request)
    {
        $alreadyAddedProductIds = \App\Models\ShopChannelProduct::where('shop_channel_id', $shopId)
            ->when($type === 'public', fn ($query) => $query->where('product_type', 'public'))
            ->when($type === 'partial', fn ($query) => $query->where('product_type', 'partial')->whereIn('approval_status', ['pending', 'approved']))
            ->pluck('product_id')
            ->toArray();

        $query = \App\Models\Product::with(['category', 'images', 'vendor.vendorbusinessdetails'])
            ->where('status', 1)
            ->where('vendor_id', '!=', $admin->vendor_id)
            ->whereNotIn('id', $alreadyAddedProductIds);

        if ($type === 'public') {
            $query->where('is_public', 'Yes');
            $this->applyPopupProductSearch($query, $filters['public_q']);
            $pageName = 'public_page';
        } else {
            $query->where('is_partial', 'Yes');
            $this->applyPopupProductSearch($query, $filters['partial_q']);
            $pageName = 'partial_page';
        }

        $products = $query->orderByDesc('id')
            ->paginate(10, ['*'], $pageName)
            ->appends($request->query());

        $products->getCollection()->transform(fn ($product) => $this->shopPopupProductRow($product, $shopId, $type));

        return $products;
    }

    private function shopPopupProductRow($product, int $shopId, string $type): array
    {
        $mainImage = $product->images->first();
        $imageUrl = $mainImage
            ? asset('front/images/product_images/small/' . $mainImage->image)
            : asset('channel_assets/images/sub/thum01.jpg');
        $seller = $product->vendor?->vendorbusinessdetails?->shop_name
            ?? $product->vendor?->name
            ?? $product->vendor?->email
            ?? '-';

        $stockText = ($product->stock_usage === 'used' || $product->stock)
            ? number_format((int) ($product->stock ?? 0)) . '개'
            : '수량제한없음';

        $priceConstraint = '제약 없음';
        $priceRange = number_format((float) $product->product_price) . '원';
        if ($product->price_constraint_enabled) {
            if ($product->price_constraint_type === 'fixed') {
                $priceConstraint = number_format((float) ($product->price_fixed ?: $product->product_price)) . ' 원';
                $priceRange = $priceConstraint;
            } elseif ($product->price_constraint_type === 'range') {
                $priceConstraint = number_format((float) ($product->price_min ?? 0)) . ' 원 ~ ' . number_format((float) ($product->price_max ?? 0)) . ' 원';
                $priceRange = number_format((float) ($product->price_min ?? $product->product_price)) . '원 ~ ' . number_format((float) ($product->price_max ?? $product->product_price)) . '원';
            }
        }

        $profitConstraint = '제약 없음';
        if ($product->profit_share_type === 'fixed') {
            $profitConstraint = '판매 개당 ' . number_format((float) $product->profit_share_value) . ' 원';
        } elseif ($product->profit_share_type === 'percent') {
            $profitConstraint = '판매가의 ' . rtrim(rtrim(number_format((float) $product->profit_share_value, 2), '0'), '.') . ' %';
        }

        $purchaseLimit = '제한 없음';
        if ($product->purchase_limit_enabled) {
            $parts = [];
            if ($product->purchase_min_qty) {
                $parts[] = number_format((int) $product->purchase_min_qty) . '개 이상';
            }
            if ($product->purchase_max_qty) {
                $parts[] = number_format((int) $product->purchase_max_qty) . '개 이하';
            }
            $purchaseLimit = implode(' / ', $parts) ?: '제한 없음';
        }

        $row = [
            'id' => $product->id,
            'code' => $product->product_code,
            'name' => $product->product_name,
            'category' => $product->category_path,
            'img' => $imageUrl,
            'seller' => $seller,
            'stock_text' => $stockText,
            'stock' => (string) ($product->stock ?? 99999),
            'price_range' => $priceRange,
            'price_constraint' => $priceConstraint,
            'profit_constraint' => $profitConstraint,
            'purchase_limit' => $purchaseLimit,
            'sales_period' => '무기한',
        ];

        if ($type === 'partial') {
            $requestRow = \App\Models\ShopChannelProduct::where('shop_channel_id', $shopId)
                ->where('product_id', $product->id)
                ->where('product_type', 'partial')
                ->first();
            $status = $requestRow?->approval_status ?: 'new';
            $row['request_status'] = $status;
            $row['request_status_text'] = match ($status) {
                'pending' => '판매요청중',
                'approved' => '판매허용',
                'rejected' => '요청거부',
                default => '판매요청',
            };
            $row['request_btn_class'] = match ($status) {
                'pending' => 'btn02',
                'approved' => 'btn02 col2',
                'rejected' => 'btn02 col4',
                default => 'btn02 col5',
            };
        }

        return $row;
    }

    public function shopCommunity(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        // Get shop_id from request or use first shop for this vendor
        $shopId = $request->get('shop_id');
        if (!$shopId) {
            $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->first();
            $shopId = $shop ? $shop->id : null;
        }

        if (!$shopId) {
            return redirect()->route('channel.shop_list')->with('error_message', 'Shop 채널을 먼저 등록해 주세요.');
        }

        // Fetch notices for this shop channel with search
        $query = \App\Models\ShopChannelNotice::where('shop_channel_id', $shopId);
        
        // Search functionality
        $searchType = $request->input('search_type', 'both');
        $searchValue = $request->input('search_value', $request->input('search'));

        if ($searchValue) {
            $query->where(function ($q) use ($searchType, $searchValue) {
                if ($searchType == 'both') {
                    $q->where('title', 'like', '%' . $searchValue . '%')
                      ->orWhere('content', 'like', '%' . $searchValue . '%');
                } elseif ($searchType == 'title') {
                    $q->where('title', 'like', '%' . $searchValue . '%');
                } elseif ($searchType == 'content') {
                    $q->where('content', 'like', '%' . $searchValue . '%');
                }
            });
        }

        // Determine items per page
        $perPage = $request->input('per_page', 20);
        if (!in_array($perPage, [20, 40, 60, 80, 100])) {
            $perPage = 20;
        }
        
        // Order by type (notice first) then by created_at
        $notices = $query->orderByRaw("CASE WHEN type = 'notice' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('channel.sub01.shop_community', [
            'dep1_id' => '01',
            'notices' => $notices,
            'shopId' => $shopId,
            'perPage' => $perPage,
            'searchType' => $searchType,
            'searchValue' => $searchValue
        ]);
    }

    public function productOwn(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $filters = $this->productListFilters($request);
        $query = \App\Models\Product::where('vendor_id', $admin->vendor_id)
            ->with([
                'category',
                'images',
                'shopChannelProducts' => function ($query) {
                    $query->with(['shopChannel.vendor'])->orderByDesc('created_at');
                },
            ])
            ->withCount([
                'shopChannelProducts as shop_channels_count' => function ($query) {
                    $query->where('approval_status', 'approved');
                },
                'shopChannelProducts as sales_request_count' => function ($query) {
                    $query->where('product_type', 'partial');
                },
            ]);
        $this->applyProductListFilters($query, $filters, false);

        $products = $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        return view('channel.sub02.product_own', [
            'dep1_id' => '02',
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => $this->productCategoryOptions(),
        ]);
    }

    public function productPublic(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $filters = $this->productListFilters($request);
        $query = \App\Models\Product::where('is_public', 'Yes')
            ->with(['category', 'images', 'vendor.vendorbusinessdetails']);
        $this->applyProductListFilters($query, $filters, true);

        $products = $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        return view('channel.sub02.product_public', [
            'dep1_id' => '02',
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => $this->productCategoryOptions(),
        ]);
    }

    public function productPartial(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $filters = $this->productListFilters($request);
        $filters['request_states'] = collect((array) $request->input('request_states', []))
            ->map(fn ($state) => (string) $state)
            ->filter(fn ($state) => in_array($state, ['available', 'pending', 'approved', 'rejected'], true))
            ->values()
            ->all();
        $query = \App\Models\Product::where('is_partial', 'Yes')
            ->with(['category', 'images', 'vendor.vendorbusinessdetails']);
        $this->applyProductListFilters($query, $filters, true);
        if (!empty($filters['request_states'])) {
            $shopChannelIds = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->pluck('id')->all();
            $query->where(function ($stateQuery) use ($filters, $shopChannelIds) {
                if (in_array('available', $filters['request_states'], true)) {
                    $stateQuery->orWhereDoesntHave('shopChannelProducts', function ($requestQuery) use ($shopChannelIds) {
                        $requestQuery->whereIn('shop_channel_id', $shopChannelIds)
                            ->where('product_type', 'partial');
                    });
                }

                foreach (['pending', 'approved', 'rejected'] as $approvalStatus) {
                    if (in_array($approvalStatus, $filters['request_states'], true)) {
                        $stateQuery->orWhereHas('shopChannelProducts', function ($requestQuery) use ($shopChannelIds, $approvalStatus) {
                            $requestQuery->whereIn('shop_channel_id', $shopChannelIds)
                                ->where('product_type', 'partial')
                                ->where('approval_status', $approvalStatus);
                        });
                    }
                }
            });
        }

        $products = $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        return view('channel.sub02.product_partial', [
            'dep1_id' => '02',
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => $this->productCategoryOptions(),
        ]);
    }

    public function productRequest(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'requester' => trim((string) $request->input('requester', '')),
            'request_status' => $request->input('request_status', ''),
            'per_page' => $this->normalizedPerPage($request),
        ];

        // Fetch requests for products belonging to THIS vendor (where others requested permission)
        $query = \App\Models\ShopChannelProduct::whereHas('product', function($query) use ($admin, $filters) {
                $query->where('vendor_id', $admin->vendor_id);
                if ($filters['q'] !== '') {
                    $query->where(function ($search) use ($filters) {
                        $search->where('product_name', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('product_code', 'like', '%' . $filters['q'] . '%');
                    });
                }
            })
            ->where('product_type', 'partial');

        if ($filters['request_status'] !== '' && in_array((string) $filters['request_status'], ['0', '1', '2'], true)) {
            $query->where('status', (int) $filters['request_status']);
        }
        if ($filters['requester'] !== '') {
            $query->whereHas('shopChannel', function ($shopQuery) use ($filters) {
                $shopQuery->where('channel_name', 'like', '%' . $filters['requester'] . '%')
                    ->orWhere('channel_code', 'like', '%' . $filters['requester'] . '%')
                    ->orWhereHas('vendor', function ($vendorQuery) use ($filters) {
                        $vendorQuery->where('name', 'like', '%' . $filters['requester'] . '%')
                            ->orWhere('email', 'like', '%' . $filters['requester'] . '%');
                    });
            });
        }

        $requests = $query
            ->with(['product' => function($query) {
                $query->with(['category', 'images']);
            }, 'shopChannel' => function($query) {
                $query->with('vendor'); // To show who is requesting
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        return view('channel.sub02.product_request', [
            'dep1_id' => '02',
            'requests' => $requests,
            'filters' => $filters,
        ]);
    }

    private function normalizedPerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 20);
        return in_array($perPage, [20, 40, 60, 80, 100], true) ? $perPage : 20;
    }

    private function productListFilters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'category_id' => (int) $request->input('category_id', 0),
            'status' => $request->input('status', ''),
            'sale_scope' => $request->input('sale_scope', ''),
            'seller' => trim((string) $request->input('seller', '')),
            'price_min' => $request->input('price_min', ''),
            'price_max' => $request->input('price_max', ''),
            'per_page' => $this->normalizedPerPage($request),
        ];
    }

    private function applyProductListFilters($query, array $filters, bool $includeSeller): void
    {
        if ($filters['q'] !== '') {
            $query->where(function ($search) use ($filters) {
                $search->where('product_name', 'like', '%' . $filters['q'] . '%')
                    ->orWhere('product_code', 'like', '%' . $filters['q'] . '%');
            });
        }

        if ($filters['category_id'] > 0) {
            $query->whereIn('category_id', $this->categoryWithDescendantIds($filters['category_id']));
        }

        if ($filters['status'] !== '') {
            if ($filters['status'] === 'stop_notice') {
                $query->whereNotNull('stop_notice_at');
            } elseif (in_array((string) $filters['status'], ['0', '1'], true)) {
                $query->where('status', (int) $filters['status']);
            }
        }

        if ($filters['sale_scope'] !== '') {
            if ($filters['sale_scope'] === 'own') {
                $query->where('is_public', 'No')->where('is_partial', 'No');
            } elseif ($filters['sale_scope'] === 'public') {
                $query->where('is_public', 'Yes');
            } elseif ($filters['sale_scope'] === 'partial') {
                $query->where('is_partial', 'Yes');
            }
        }

        if ($includeSeller && $filters['seller'] !== '') {
            $query->whereHas('vendor', function ($vendorQuery) use ($filters) {
                $vendorQuery->where('name', 'like', '%' . $filters['seller'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['seller'] . '%')
                    ->orWhereHas('vendorbusinessdetails', function ($businessQuery) use ($filters) {
                        $businessQuery->where('shop_name', 'like', '%' . $filters['seller'] . '%')
                            ->orWhere('company_name', 'like', '%' . $filters['seller'] . '%');
                    });
            });
        }

        if ($filters['price_min'] !== '' && is_numeric($filters['price_min'])) {
            $query->where('product_price', '>=', (float) $filters['price_min']);
        }
        if ($filters['price_max'] !== '' && is_numeric($filters['price_max'])) {
            $query->where('product_price', '<=', (float) $filters['price_max']);
        }
    }

    private function categoryWithDescendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $children = \App\Models\Category::where('parent_id', $categoryId)->pluck('id')->all();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->categoryWithDescendantIds((int) $childId));
        }
        return array_values(array_unique($ids));
    }

    private function productCategoryOptions(): array
    {
        $categories = \App\Models\Category::where('status', 1)
            ->select('id', 'parent_id', 'category_name')
            ->orderBy('parent_id')
            ->orderBy('category_name')
            ->get()
            ->groupBy('parent_id');

        $options = [];
        $walk = function ($parentId, $prefix = '') use (&$walk, &$options, $categories) {
            foreach ($categories->get($parentId, collect()) as $category) {
                $options[] = [
                    'id' => $category->id,
                    'name' => $prefix . $category->category_name,
                ];
                $walk($category->id, $prefix . $category->category_name . ' > ');
            }
        };
        $walk(0);

        return $options;
    }
    
    public function community()
    {
        return view('channel.sub01.shop_community', ['dep1_id' => '01']);
    }

    public function communityRegister(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shopId = $request->get('shop_id');
        if (!$shopId) {
            $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->first();
            $shopId = $shop ? $shop->id : null;
        }

        if (!$shopId) {
            return redirect()->route('channel.shop_list')->with('error_message', 'Shop 채널을 먼저 등록해 주세요.');
        }

        return view('channel.sub01.community_register', [
            'dep1_id' => '01',
            'shopId' => $shopId
        ]);
    }

    public function communityView(Request $request, $id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $notice = \App\Models\ShopChannelNotice::findOrFail($id);
        
        // Verify this notice belongs to vendor's shop channel
        $shop = \App\Models\ShopChannel::where('id', $notice->shop_channel_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        // Increment view count
        $notice->increment('view_count');

        // Get previous notice (older)
        $prevNotice = \App\Models\ShopChannelNotice::where('shop_channel_id', $notice->shop_channel_id)
            ->where('id', '<', $id)
            ->orderBy('id', 'desc')
            ->first();

        // Get next notice (newer)
        $nextNotice = \App\Models\ShopChannelNotice::where('shop_channel_id', $notice->shop_channel_id)
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->first();

        return view('channel.sub01.community_view', [
            'dep1_id' => '01',
            'notice' => $notice,
            'shopId' => $shop->id,
            'prevNotice' => $prevNotice,
            'nextNotice' => $nextNotice
        ]);
    }

    public function communityUpdate(Request $request, $id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $notice = \App\Models\ShopChannelNotice::findOrFail($id);
        
        // Verify this notice belongs to vendor's shop channel
        $shop = \App\Models\ShopChannel::where('id', $notice->shop_channel_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        return view('channel.sub01.community_update', [
            'dep1_id' => '01',
            'notice' => $notice,
            'shopId' => $shop->id
        ]);
    }

    public function communityRegisterSubmit(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shopId = $request->input('shop_id');
        
        // Verify shop belongs to this vendor
        $shop = \App\Models\ShopChannel::where('id', $shopId)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        $validated = $request->validate([
            'type' => 'required|in:notice,general',
            'title' => 'required|string|max:500',
            'author' => 'nullable|string|max:100',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ], [
            'type.required' => '분류를 선택해 주세요.',
            'title.required' => '제목을 입력해 주세요.',
            'content.required' => '내용을 입력해 주세요.',
        ]);

        $data = [
            'shop_channel_id' => $shopId,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'author' => $validated['author'] ?? $admin->name,
            'content' => $validated['content'],
            'status' => 1,
            'view_count' => 0,
        ];

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!is_dir(public_path('uploads/notices'))) {
                mkdir(public_path('uploads/notices'), 0755, true);
            }
            $file->move(public_path('uploads/notices'), $filename);
            $data['attachment'] = $filename;
        }

        \App\Models\ShopChannelNotice::create($data);

        return redirect()->route('channel.shop_community', ['shop_id' => $shopId])
            ->with('success_message', '공지사항이 등록되었습니다.');
    }

    public function communityUpdateSubmit(Request $request, $id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $notice = \App\Models\ShopChannelNotice::findOrFail($id);
        
        // Verify this notice belongs to vendor's shop channel
        $shop = \App\Models\ShopChannel::where('id', $notice->shop_channel_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        $validated = $request->validate([
            'type' => 'required|in:notice,general',
            'title' => 'required|string|max:500',
            'author' => 'nullable|string|max:100',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ], [
            'type.required' => '분류를 선택해 주세요.',
            'title.required' => '제목을 입력해 주세요.',
            'content.required' => '내용을 입력해 주세요.',
        ]);

        $notice->type = $validated['type'];
        $notice->title = $validated['title'];
        $notice->author = $validated['author'] ?? $admin->name;
        $notice->content = $validated['content'];

        // Handle file deletion
        if ($request->has('delete_attachment') && $request->delete_attachment == '1') {
            $this->deleteCommunityNoticeAttachment($notice->attachment);
            $notice->attachment = null;
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file
            $this->deleteCommunityNoticeAttachment($notice->attachment);
            
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!is_dir(public_path('uploads/notices'))) {
                mkdir(public_path('uploads/notices'), 0755, true);
            }
            $file->move(public_path('uploads/notices'), $filename);
            $notice->attachment = $filename;
        }

        $notice->save();

        return redirect()->route('channel.shop_community', ['shop_id' => $shop->id])
            ->with('success_message', '공지사항이 수정되었습니다.');
    }

    public function communityDelete($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $notice = \App\Models\ShopChannelNotice::findOrFail($id);
        
        // Verify this notice belongs to vendor's shop channel
        $shop = \App\Models\ShopChannel::where('id', $notice->shop_channel_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        // Delete attachment file if exists
        $this->deleteCommunityNoticeAttachment($notice->attachment);

        $notice->delete();

        return redirect()->route('channel.shop_community', ['shop_id' => $shop->id])
            ->with('success_message', '공지사항이 삭제되었습니다.');
    }

    private function deleteCommunityNoticeAttachment(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        foreach (['uploads/notices', 'uploads/channel/notices', 'uploads/shop_notices'] as $directory) {
            $path = public_path($directory . '/' . $filename);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }


    public function infoUpdate($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)
            ->where('id', $id)
            ->first();

        if (!$shop) {
            return redirect()->route('channel.shop_list')->with('error_message', '존재하지 않거나 권한이 없는 채널입니다.');
        }

        return view('channel.sub01.info_update', [
            'dep1_id' => '01',
            'shop' => $shop,
            'privateAccessRows' => $this->privateAccessRowsForForm($shop),
        ]);
    }

    public function infoUpdateSubmit(Request $request, $id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)
            ->where('id', $id)
            ->firstOrFail();

        // 1. Validation Rules (similar to register but less strict on files if already exists)
        $rules = [
            'channel_name' => 'required|max:255',
            'copyright' => 'required|max:255',
            'keywords' => 'required|array|min:1',
        ];

        $data = $request->all();

        // 비공개시 비밀번호 체크
        if (($data['is_public'] ?? '1') == '0') {
            $rules['password'] = 'required|min:4';
        }
        $rules['private_access_rows'] = 'nullable|string';
        $rules['private_access_file'] = 'nullable|file|max:5120|mimes:xlsx,xls,csv,txt';
        $rules['purchase_sms_templates.purchase'] = 'nullable|string|max:500';
        $rules['purchase_sms_templates.purchase_confirmed'] = 'nullable|string|max:500';
        $rules['purchase_sms_templates.cancel'] = 'nullable|string|max:500';
        $rules['purchase_sms_templates.return'] = 'nullable|string|max:500';

        // 사용기간(기간제) 체크
        if (($data['use_period_type'] ?? '0') == '1') {
            $rules['start_date'] = 'required|date';
            $rules['start_hour'] = 'required';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $rules['end_hour'] = 'required';
        }

        // 관리자 정보 체크
        if (($data['use_admin'] ?? '0') == '1') {
            $rules['admin_name'] = 'required|max:50';
            $rules['admin_login_id'] = 'required|max:50|unique:shop_channels,admin_login_id,' . $shop->id;
            
            if (($data['settlement_type'] ?? '1') == '1') {
                $rules['settlement_rate_percent'] = 'required|numeric|between:0,100';
            } else {
                $rules['settlement_rate_amount'] = 'required|numeric|min:0';
            }
        }

        if (($data['use_own_pg'] ?? '0') == '1') {
            $vendor = \App\Models\Vendor::find($admin->vendor_id);
            if (!$vendor || (int) $vendor->status !== 1) {
                return back()->withInput()->withErrors(['use_own_pg' => '판매인증이 완료된 채널만 자사 PG를 사용할 수 있습니다.']);
            }

            $rules['pg_provider'] = 'required|in:inicis,kcp,toss';
            $rules['pg_merchant_id'] = 'required|string|max:255';
            $rules['pg_client_key'] = 'required|string|max:255';
            $rules['pg_secret_key'] = ($shop->pg_secret_key ? 'nullable' : 'required') . '|string|max:255';
            $messages['pg_provider.required'] = '자사 PG사를 선택해 주세요.';
            $messages['pg_provider.in'] = '지원하는 PG사를 선택해 주세요.';
            $messages['pg_merchant_id.required'] = '자사 PG 상점 ID를 입력해 주세요.';
            $messages['pg_client_key.required'] = '자사 PG Client Key를 입력해 주세요.';
            $messages['pg_secret_key.required'] = '자사 PG Secret Key를 입력해 주세요.';
        }

        $request->validate($rules, $messages);

        // 2. Update Data
        $shop->status = $data['status'] ?? 0;
        $shop->is_public = $data['is_public'] ?? 1;
        $shop->password = $data['password'] ?? null;
        $shop->is_member_only = ((string) ($data['is_public'] ?? '1') === '0') ? 0 : (isset($data['is_member_only']) ? 1 : 0);
        $shop->channel_name = $data['channel_name'];
        $shop->copyright = $data['copyright'];
        $shop->keywords = json_encode($data['keywords'], JSON_UNESCAPED_UNICODE);

        // 사용주기
        $shop->use_period_type = $data['use_period_type'] ?? 0;
        if ($shop->use_period_type == 1) {
            if (!empty($data['start_date']) && isset($data['start_hour']) && $data['start_hour'] !== '') {
                try {
                    $shop->start_at = \Carbon\Carbon::createFromFormat('Y-m-d H', $data['start_date'] . ' ' . $data['start_hour'])->startOfHour();
                } catch (\Exception $e) {
                    // Ignore or handle
                }
            }
            if (!empty($data['end_date']) && isset($data['end_hour']) && $data['end_hour'] !== '') {
                try {
                    $shop->end_at = \Carbon\Carbon::createFromFormat('Y-m-d H', $data['end_date'] . ' ' . $data['end_hour'])->startOfHour();
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        } else {
            $shop->start_at = null;
            $shop->end_at = null;
        }

        // 로고 이미지 처리
        $shop->use_logo = $data['use_logo'] ?? 0;
        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');
            $fileName = time() . '_logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/shop/logo'), $fileName);
            $shop->logo_image = 'uploads/shop/logo/' . $fileName;
        }

        // 메인 배너 처리
        $shop->use_banner = $data['use_banner'] ?? 0;
        $banners = $data['existing_banners'] ?? []; // 기존 유지할 배너들
        if ($request->hasFile('banner_files')) {
            foreach ($request->file('banner_files') as $file) {
                $fileName = time() . '_' . rand(100, 999) . '_banner.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/shop/banner'), $fileName);
                $banners[] = 'uploads/shop/banner/' . $fileName;
            }
        }
        $shop->banner_images = json_encode(array_slice($banners, 0, 5));

        // OG TAG 처리
        $shop->use_og = $data['use_og'] ?? 0;
        $shop->og_title = $data['og_title'] ?? null;
        $shop->og_description = $data['og_description'] ?? null;
        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $fileName = time() . '_og.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/shop/og'), $fileName);
            $shop->og_image = 'uploads/shop/og/' . $fileName;
        }

        $shop->use_own_pg = ($data['use_own_pg'] ?? '0') == '1';
        $shop->pg_provider = $shop->use_own_pg ? ($data['pg_provider'] ?? null) : null;
        $shop->pg_merchant_id = $shop->use_own_pg ? ($data['pg_merchant_id'] ?? null) : null;
        $shop->pg_site_code = $shop->use_own_pg ? ($data['pg_site_code'] ?? null) : null;
        $shop->pg_client_key = $shop->use_own_pg ? ($data['pg_client_key'] ?? null) : null;
        if ($shop->use_own_pg && array_key_exists('pg_secret_key', $data)) {
            $shop->pg_secret_key = $data['pg_secret_key'] ?: $shop->pg_secret_key;
        } elseif (!$shop->use_own_pg) {
            $shop->pg_secret_key = null;
        }

        $shop->use_purchase_sms = ($data['use_purchase_sms'] ?? '0') == '1';
        $shop->purchase_sms_templates = $shop->use_purchase_sms ? $this->purchaseSmsTemplates($data) : null;

        // 관리자 정보 처리
        $shop->use_admin = $data['use_admin'] ?? 0;
        $shop->admin_name = $data['admin_name'] ?? null;
        $shop->admin_login_id = $data['admin_login_id'] ?? null;
        if (!empty($data['admin_password'])) {
            $shop->admin_password = bcrypt($data['admin_password']);
        }
        $shop->settlement_type = $data['settlement_type'] ?? 1;
        if ($shop->settlement_type == 1) {
            $shop->settlement_rate = $data['settlement_rate_percent'] ?? 0;
        } else {
            $shop->settlement_rate = $data['settlement_rate_amount'] ?? 0;
        }

        $shop->save();
        $this->syncPrivateAccesses($shop, $this->combinedPrivateAccessRows($request, $shop));

        return redirect()->route('channel.shop_info', ['id' => $shop->id])->with('success_message', '채널 정보가 수정되었습니다.');
    }

    private function purchaseSmsTemplates(array $data): array
    {
        $templates = $data['purchase_sms_templates'] ?? [];

        return [
            'purchase' => trim((string) ($templates['purchase'] ?? $templates['customer'] ?? '')),
            'purchase_confirmed' => trim((string) ($templates['purchase_confirmed'] ?? '')),
            'cancel' => trim((string) ($templates['cancel'] ?? '')),
            'return' => trim((string) ($templates['return'] ?? '')),
        ];
    }

    private function privateAccessRowsForForm(\App\Models\ShopChannel $shop): string
    {
        return $shop->privateAccesses()
            ->orderBy('id')
            ->get()
            ->map(fn ($access) => trim($access->phone . ',' . $access->entry_code))
            ->implode("\n");
    }

    private function syncPrivateAccesses(\App\Models\ShopChannel $shop, ?string $rawRows): void
    {
        $rows = $this->parsePrivateAccessRows($rawRows, (string) $shop->password);
        $keepIds = [];

        foreach ($rows as $row) {
            $access = \App\Models\ShopChannelPrivateAccess::updateOrCreate(
                [
                    'shop_channel_id' => $shop->id,
                    'phone_normalized' => $row['phone_normalized'],
                ],
                [
                    'phone' => $row['phone'],
                    'entry_code' => $row['entry_code'],
                    'user_id' => $row['user_id'],
                ]
            );
            $keepIds[] = $access->id;
        }

        $query = $shop->privateAccesses();
        if (!empty($keepIds)) {
            $query->whereNotIn('id', $keepIds);
        }
        $query->delete();
    }

    private function combinedPrivateAccessRows(Request $request, \App\Models\ShopChannel $shop): string
    {
        $rows = trim((string) $request->input('private_access_rows', ''));
        $uploadedRows = $this->privateAccessRowsFromUpload($request->file('private_access_file'), (string) $shop->password);

        return trim(implode("\n", array_filter([$rows, $uploadedRows], fn ($value) => trim((string) $value) !== '')));
    }

    private function privateAccessRowsFromUpload($file, string $defaultEntryCode): string
    {
        if (!$file) {
            return '';
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $rows = in_array($extension, ['xlsx', 'xls'], true)
            ? $this->spreadsheetRows($file->getRealPath())
            : $this->csvRows($file->getRealPath());

        $lines = [];
        foreach ($rows as $row) {
            $phone = trim((string) ($row[0] ?? ''));
            $entryCode = trim((string) ($row[1] ?? $defaultEntryCode));
            $userId = trim((string) ($row[2] ?? ''));

            if (\App\Models\ShopChannelPrivateAccess::normalizePhone($phone) === '') {
                continue;
            }

            $line = [$phone, $entryCode !== '' ? $entryCode : $defaultEntryCode];
            if ($userId !== '') {
                $line[] = $userId;
            }
            $lines[] = implode(',', $line);
        }

        return implode("\n", $lines);
    }

    private function spreadsheetRows(string $path): array
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        } catch (\Throwable $e) {
            return [];
        }

        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    private function csvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function parsePrivateAccessRows(?string $rawRows, string $defaultEntryCode): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $rawRows) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', str_getcsv($line));
            $phone = $parts[0] ?? '';
            $entryCode = $parts[1] ?? $defaultEntryCode;
            $normalizedPhone = \App\Models\ShopChannelPrivateAccess::normalizePhone($phone);
            if ($normalizedPhone === '' || trim((string) $entryCode) === '') {
                continue;
            }

            $rows[$normalizedPhone] = [
                'phone' => $phone,
                'phone_normalized' => $normalizedPhone,
                'entry_code' => trim((string) $entryCode),
                'user_id' => isset($parts[2]) && is_numeric($parts[2]) ? (int) $parts[2] : null,
            ];
        }

        return array_values($rows);
    }

    // 상품 관리 (Sub02) 관련 메서드는 위에 이미 정의되어 있습니다.

    // 주문 관리 (Sub04)
    public function orderList(Request $request)
    {
        if (!$request->query->has('order_type')) {
            $request->merge(['order_type' => 'normal']);
        }

        return $this->renderOrderList($request, 'list', $this->normalOrderStatusKeys());
    }

    public function orderJointPurchaseList(Request $request)
    {
        $request->merge(['order_type' => 'joint']);

        return $this->renderOrderList($request, 'joint', $this->normalOrderStatusKeys());
    }
    
    public function orderCancelList(Request $request)
    {
        return $this->renderOrderList($request, 'cancel', [
            OrderItemStatus::CANCEL_REQUESTED,
            OrderItemStatus::CANCELLED,
        ]);
    }

    public function orderReturnRequestList(Request $request)
    {
        return $this->renderOrderList($request, 'return', [
            OrderItemStatus::RETURN_REQUESTED,
            OrderItemStatus::RETURNED,
        ]);
    }

    public function orderExchangeRequestList(Request $request)
    {
        return $this->renderOrderList($request, 'exchange', [
            OrderItemStatus::EXCHANGE_REQUESTED,
            OrderItemStatus::EXCHANGED,
        ]);
    }

    private function renderOrderList(Request $request, string $pageType, array $baseStatusKeys)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $filters = $this->orderListFilters($request);
        $orders = $this->fetchOrders($admin->vendor_id, $baseStatusKeys, $filters)
            ->appends($request->query());

        $titles = [
            'list' => '주문목록',
            'joint' => '공동구매 주문목록',
            'cancel' => '취소목록',
            'return' => '반품목록',
            'exchange' => '교환목록',
        ];

        return view('channel.sub04.order_list', [
            'dep1_id' => '04',
            'orders' => $orders,
            'filters' => $filters,
            'orderPageType' => $pageType,
            'orderPageTitle' => $titles[$pageType] ?? '주문목록',
            'orderStatusOptions' => $this->orderStatusOptions($baseStatusKeys),
        ]);
    }

    private function normalOrderStatusKeys(): array
    {
        return [
            OrderItemStatus::PAID,
            OrderItemStatus::READY_TO_SHIP,
            OrderItemStatus::SHIPPING,
            OrderItemStatus::DELIVERED,
            OrderItemStatus::CONFIRMED,
        ];
    }

    private function orderListFilters(Request $request): array
    {
        $searchType = $request->input('search_type', 'all');
        if (!in_array($searchType, ['all', 'order_no', 'product_name', 'product_code', 'tracking_number'], true)) {
            $searchType = 'all';
        }

        $statusInputs = collect((array) $request->input('order_statuses', []))
            ->flatMap(fn ($status) => is_array($status) ? $status : explode(',', (string) $status));

        $statusFilter = trim((string) $request->input('status_filter', ''));
        if ($statusFilter !== '') {
            $statusInputs = $statusInputs->merge(explode(',', $statusFilter));
        }

        $statusKeys = $statusInputs
            ->map(fn ($status) => trim((string) $status))
            ->filter()
            ->map(fn ($status) => OrderItemStatus::normalize($status))
            ->filter(fn ($status) => array_key_exists($status, OrderItemStatus::labels()))
            ->values()
            ->unique()
            ->all();

        $perPage = (int) $request->input('per_page', 20);
        $orderType = $request->input('order_type', '');
        if (!in_array($orderType, ['', 'all', 'normal', 'joint'], true)) {
            $orderType = '';
        }
        if ($orderType === 'all') {
            $orderType = '';
        }

        return [
            'search_type' => $searchType,
            'keyword' => trim((string) $request->input('keyword', '')),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
            'order_statuses' => $statusKeys,
            'buyer' => trim((string) $request->input('buyer', '')),
            'price_min' => $request->input('price_min', ''),
            'price_max' => $request->input('price_max', ''),
            'order_type' => $orderType,
            'per_page' => in_array($perPage, [20, 40, 60, 80, 100], true) ? $perPage : 20,
        ];
    }

    private function fetchOrders($vendor_id, array $baseStatusKeys = [], array $filters = [])
    {
        $vendorShopIds = DB::table('shop_channels')
            ->where('vendor_id', $vendor_id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $vendorShopProductIds = empty($vendorShopIds)
            ? []
            : DB::table('shop_channel_products')
                ->whereIn('shop_channel_id', $vendorShopIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        $vendorProductIds = empty($vendorShopIds)
            ? []
            : DB::table('shop_channel_products')
                ->whereIn('shop_channel_id', $vendorShopIds)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        $selectedStatusKeys = $filters['order_statuses'] ?? [];
        $effectiveStatusKeys = $baseStatusKeys;
        if (!empty($selectedStatusKeys)) {
            $effectiveStatusKeys = empty($baseStatusKeys)
                ? $selectedStatusKeys
                : array_values(array_intersect($baseStatusKeys, $selectedStatusKeys));
        }

        $statusValues = $this->orderStatusQueryValues($effectiveStatusKeys);
        $hasImpossibleStatusFilter = !empty($selectedStatusKeys) && !empty($baseStatusKeys) && empty($effectiveStatusKeys);
        $itemFilter = function ($q) use ($vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds, $filters, $statusValues, $hasImpossibleStatusFilter) {
            $q->where(function ($ownerQuery) use ($vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds) {
                $ownerQuery->where('vendor_id', $vendor_id);
                if (!empty($vendorShopIds)) {
                    $ownerQuery->orWhereIn('shop_channel_id', $vendorShopIds);
                }
                if (!empty($vendorShopProductIds)) {
                    $ownerQuery->orWhereIn('shop_channel_product_id', $vendorShopProductIds);
                }
                if (!empty($vendorProductIds)) {
                    $ownerQuery->orWhereIn('product_id', $vendorProductIds);
                }
            });

            if ($hasImpossibleStatusFilter) {
                $q->whereRaw('1 = 0');
                return;
            }

            if (!empty($statusValues)) {
                $q->where(function ($statusQuery) use ($statusValues) {
                    $statusQuery->whereIn('status_code', $statusValues)
                        ->orWhereIn('item_status', $statusValues);
                });
            }

            $this->applyOrderTypeFilter($q, $filters['order_type'] ?? '');

            $keyword = $filters['keyword'] ?? '';
            $searchType = $filters['search_type'] ?? 'all';
            if ($keyword !== '' && in_array($searchType, ['product_name', 'product_code', 'tracking_number'], true)) {
                $q->where(function ($search) use ($keyword, $searchType) {
                    if ($searchType === 'product_name') {
                        $search->orWhere('product_name', 'like', '%' . $keyword . '%');
                    }
                    if ($searchType === 'product_code') {
                        $search->orWhere('product_code', 'like', '%' . $keyword . '%');
                    }
                    if ($searchType === 'tracking_number') {
                        $search->orWhere('tracking_number', 'like', '%' . $keyword . '%');
                    }
                });
            }

            if (($filters['price_min'] ?? '') !== '' && is_numeric($filters['price_min'])) {
                $q->whereRaw('CASE WHEN line_total > 0 THEN line_total ELSE product_price * product_qty END >= ?', [(float) $filters['price_min']]);
            }
            if (($filters['price_max'] ?? '') !== '' && is_numeric($filters['price_max'])) {
                $q->whereRaw('CASE WHEN line_total > 0 THEN line_total ELSE product_price * product_qty END <= ?', [(float) $filters['price_max']]);
            }
        };

        $query = \App\Models\Order::with([
            'orders_products' => function($q) use ($itemFilter) {
                $itemFilter($q);
                $q->with(['shopChannel', 'shopChannelProduct']);
            },
            'user',
            'claims',
        ]);

        $query->whereHas('orders_products', $itemFilter);

        $keyword = $filters['keyword'] ?? '';
        $searchType = $filters['search_type'] ?? 'all';
        if ($keyword !== '' && $searchType === 'all') {
            $query->where(function ($mixedQuery) use ($keyword, $vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds, $statusValues, $hasImpossibleStatusFilter, $filters) {
                $orderNumber = $this->orderNumberSearchValue($keyword);
                $mixedQuery->where(function ($orderQuery) use ($keyword, $orderNumber) {
                    if ($orderNumber !== null) {
                        $orderQuery->orWhere('id', (int) $orderNumber)
                            ->orWhere('id', 'like', '%' . $orderNumber . '%');
                    }
                    $orderQuery->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('mobile', 'like', '%' . $keyword . '%');
                })->orWhereHas('orders_products', function ($itemQuery) use ($keyword, $vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds, $statusValues, $hasImpossibleStatusFilter, $filters) {
                    $itemQuery->where(function ($ownerQuery) use ($vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds) {
                        $ownerQuery->where('vendor_id', $vendor_id);
                        if (!empty($vendorShopIds)) {
                            $ownerQuery->orWhereIn('shop_channel_id', $vendorShopIds);
                        }
                        if (!empty($vendorShopProductIds)) {
                            $ownerQuery->orWhereIn('shop_channel_product_id', $vendorShopProductIds);
                        }
                        if (!empty($vendorProductIds)) {
                            $ownerQuery->orWhereIn('product_id', $vendorProductIds);
                        }
                    });
                    if ($hasImpossibleStatusFilter) {
                        $itemQuery->whereRaw('1 = 0');
                        return;
                    }
                    if (!empty($statusValues)) {
                        $itemQuery->where(function ($statusQuery) use ($statusValues) {
                            $statusQuery->whereIn('status_code', $statusValues)
                                ->orWhereIn('item_status', $statusValues);
                        });
                    }
                    $this->applyOrderTypeFilter($itemQuery, $filters['order_type'] ?? '');
                    if (($filters['price_min'] ?? '') !== '' && is_numeric($filters['price_min'])) {
                        $itemQuery->whereRaw('CASE WHEN line_total > 0 THEN line_total ELSE product_price * product_qty END >= ?', [(float) $filters['price_min']]);
                    }
                    if (($filters['price_max'] ?? '') !== '' && is_numeric($filters['price_max'])) {
                        $itemQuery->whereRaw('CASE WHEN line_total > 0 THEN line_total ELSE product_price * product_qty END <= ?', [(float) $filters['price_max']]);
                    }
                    $itemQuery->where(function ($itemSearch) use ($keyword) {
                        $itemSearch->where('product_name', 'like', '%' . $keyword . '%')
                            ->orWhere('product_code', 'like', '%' . $keyword . '%')
                            ->orWhere('tracking_number', 'like', '%' . $keyword . '%');
                    });
                });
            });
        } elseif ($keyword !== '' && $searchType === 'order_no') {
            $orderNumber = $this->orderNumberSearchValue($keyword);
            $query->where(function ($orderQuery) use ($orderNumber) {
                if ($orderNumber === null) {
                    $orderQuery->whereRaw('1 = 0');
                    return;
                }
                $orderQuery->where('id', (int) $orderNumber)
                    ->orWhere('id', 'like', '%' . $orderNumber . '%');
            });
        }

        if (($filters['buyer'] ?? '') !== '') {
            $buyer = $filters['buyer'];
            $query->where(function ($buyerQuery) use ($buyer) {
                $buyerQuery->where('name', 'like', '%' . $buyer . '%')
                    ->orWhere('email', 'like', '%' . $buyer . '%')
                    ->orWhere('mobile', 'like', '%' . $buyer . '%');
            });
        }

        if (($filters['date_from'] ?? '') !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (($filters['date_to'] ?? '') !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 20);

        $productIds = $orders->getCollection()
            ->flatMap(fn ($order) => $order->orders_products->pluck('product_id'))
            ->filter()
            ->unique()
            ->values();

        $jointProductIds = $productIds->isEmpty()
            ? collect()
            : DB::table('joint_purchases')
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->map(fn ($productId) => (int) $productId)
                ->flip();

        $orders->getCollection()->transform(function($order) use ($jointProductIds) {
            $order->order_no = 'Me9-' . str_pad($order->id, 8, '0', STR_PAD_LEFT); 
            $order->user_name = $order->name; 
            $order->claims_data = $order->claims; 
            
            $vendorItems = $order->orders_products;
            $firstItem = $vendorItems->first();
            $order->shop_name = $firstItem?->shopChannel?->channel_name ?? 'Me9 Market';
            
            $order->items = $vendorItems->map(function($item) use ($jointProductIds) {
                $lineTotal = $item->line_total > 0
                    ? $item->line_total
                    : $item->product_price * $item->product_qty;
                $isJointPurchase = $jointProductIds->has((int) $item->product_id);

                return [
                    'id' => $item->id,
                    'status' => $item->status_code ?: $item->item_status,
                    'status_label' => $item->status_label,
                    'order_type' => $isJointPurchase ? 'joint' : 'normal',
                    'order_type_label' => $isJointPurchase ? '공동구매' : '일반',
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'option_name' => trim(($item->product_color ?: '-') . '/' . ($item->product_size ?: '-'), '/'),
                    'qty' => $item->product_qty,
                    'price' => $item->selling_price ?: $item->product_price,
                    'line_total' => $lineTotal,
                    'original_line_total' => $item->original_line_total,
                    'repriced_line_total' => $item->repriced_line_total,
                    'reprice_adjustment_amount' => $item->reprice_adjustment_amount,
                    'reprice_status' => $item->reprice_status,
                    'courier_name' => $item->courier_name,
                    'tracking_number' => $item->tracking_number,
                    'product_type' => $item->shopChannelProduct?->product_type === 'public' ? '공유' : ($item->shopChannelProduct?->product_type === 'partial' ? '제휴' : '자사'),
                ];
            });

            $totalProductPrice = $vendorItems->sum(function($item) {
                return $item->line_total > 0 ? $item->line_total : $item->product_price * $item->product_qty;
            });

            $order->total_product_price = $totalProductPrice;
            $order->total_sale_price = $totalProductPrice; 
            $order->total_profit = 0; 
            $order->total_selling_profit = 0; 
            $order->delivery_fee = (int) ($order->shipping_charges ?? 0);
            $order->used_point = 0;
            $order->total_payment_price = $totalProductPrice + $order->delivery_fee;
            $order->earned_point = 0;
            $order->status = $order->items->first()['status_label'] ?? OrderItemStatus::label(OrderItemStatus::PAID);
            $order->order_type_label = $order->items->pluck('order_type_label')->unique()->implode(', ');
            
            return $order;
        });

        return $orders;
    }

    private function applyOrderTypeFilter($query, string $orderType): void
    {
        if ($orderType === 'joint') {
            $query->whereExists(function ($jointQuery) {
                $jointQuery->select(DB::raw(1))
                    ->from('joint_purchases')
                    ->whereColumn('joint_purchases.product_id', 'orders_products.product_id');
            });
        } elseif ($orderType === 'normal') {
            $query->whereNotExists(function ($jointQuery) {
                $jointQuery->select(DB::raw(1))
                    ->from('joint_purchases')
                    ->whereColumn('joint_purchases.product_id', 'orders_products.product_id');
            });
        }
    }

    private function orderStatusOptions(array $statusKeys = []): array
    {
        $options = [
            OrderItemStatus::PAID => '결제완료',
            OrderItemStatus::READY_TO_SHIP => '배송대기',
            OrderItemStatus::SHIPPING => '배송중',
            OrderItemStatus::DELIVERED => '배송완료',
            OrderItemStatus::CONFIRMED => '구매확정',
            OrderItemStatus::CANCEL_REQUESTED => '취소요청',
            OrderItemStatus::CANCELLED => '취소완료',
            OrderItemStatus::RETURN_REQUESTED => '반품요청',
            OrderItemStatus::RETURNED => '반품완료',
            OrderItemStatus::EXCHANGE_REQUESTED => '교환요청',
            OrderItemStatus::EXCHANGED => '교환완료',
        ];

        if (empty($statusKeys)) {
            return $options;
        }

        return array_intersect_key($options, array_flip($statusKeys));
    }

    private function orderNumberSearchValue(string $keyword): ?string
    {
        $keyword = trim($keyword);
        $keyword = preg_replace('/^me9[\s\-_]*/i', '', $keyword);
        preg_match('/\d+/', $keyword, $matches);

        if (empty($matches[0])) {
            return null;
        }

        return ltrim($matches[0], '0') ?: '0';
    }

    private function orderStatusQueryValues(array $statusKeys): array
    {
        $legacyValues = [
            OrderItemStatus::PAID => ['New', 'Payment Captured'],
            OrderItemStatus::READY_TO_SHIP => ['In Process', '배송준비중'],
            OrderItemStatus::SHIPPING => ['Shipped', 'shipping', 'shipped'],
            OrderItemStatus::DELIVERED => ['Delivered'],
            OrderItemStatus::CONFIRMED => ['Confirmed'],
            OrderItemStatus::CANCEL_REQUESTED => ['Cancel Requested'],
            OrderItemStatus::CANCELLED => ['Cancelled'],
            OrderItemStatus::RETURN_REQUESTED => ['Return Requested'],
            OrderItemStatus::RETURNED => ['Returned'],
            OrderItemStatus::EXCHANGE_REQUESTED => ['Exchange Requested'],
            OrderItemStatus::EXCHANGED => ['Exchanged'],
        ];

        $values = [];
        foreach ($statusKeys as $statusKey) {
            $values[] = $statusKey;
            $values[] = OrderItemStatus::label($statusKey);
            foreach ($legacyValues[$statusKey] ?? [] as $legacyValue) {
                $values[] = $legacyValue;
            }
        }

        return array_values(array_unique(array_filter($values)));
    }

    public function inquiryList(Request $request)
    {
        $vendorId = Auth::guard('admin')->user()->vendor_id;

        $query = Contact::with(['order', 'orderProduct', 'shopChannel'])
            ->where('vendor_id', $vendorId)
            ->whereNotNull('order_product_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('subject', 'like', '%' . $keyword . '%')
                    ->orWhere('message', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        $inquiries = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('channel.inquiries.index', [
            'dep1_id' => '04',
            'inquiries' => $inquiries,
            'statusLabels' => $this->contactStatusLabels(),
        ]);
    }

    public function inquiryView(int $id)
    {
        $vendorId = Auth::guard('admin')->user()->vendor_id;

        $inquiry = Contact::with(['order', 'orderProduct', 'shopChannel'])
            ->where('vendor_id', $vendorId)
            ->whereNotNull('order_product_id')
            ->findOrFail($id);

        return view('channel.inquiries.show', [
            'dep1_id' => '04',
            'inquiry' => $inquiry,
            'statusLabels' => $this->contactStatusLabels(),
        ]);
    }

    public function inquiryReply(Request $request, int $id)
    {
        $request->validate([
            'admin_reply' => 'required|string',
            'status' => 'required|in:processing,completed',
        ]);

        $vendorId = Auth::guard('admin')->user()->vendor_id;

        $inquiry = Contact::where('vendor_id', $vendorId)
            ->whereNotNull('order_product_id')
            ->findOrFail($id);

        $inquiry->admin_reply = $request->admin_reply;
        $inquiry->status = $request->status;
        $inquiry->replied_at = now();
        $inquiry->save();

        return redirect()
            ->route('channel.inquiries.show', $inquiry->id)
            ->with('success_message', '상품문의 답변이 저장되었습니다.');
    }

    private function contactStatusLabels(): array
    {
        return [
            'pending' => '대기중',
            'processing' => '처리중',
            'completed' => '답변완료',
        ];
    }

    public function orderInfo()
    {
        return view('channel.sub04.inc.pop_order_info'); // 독립형 또는 팝업으로 사용될 수 있습니다.
    }
    
    // 추가 정보 / 설정 (Sub00)
    public function deliveryChargeList()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        $charges = \App\Models\ChannelDeliveryCharge::where('vendor_id', (int) $admin->vendor_id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('channel.sub00.delivery_charge_list', [
            'dep1_id' => '00',
            'charges' => $charges,
            'shippingTypes' => $this->channelDeliveryShippingTypes(),
            'paymentTypes' => $this->channelDeliveryPaymentTypes(),
        ]);
    }

    public function storeDeliveryCharge(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();

        \App\Models\ChannelDeliveryCharge::create($this->deliveryChargePayload($request, (int) $admin->vendor_id));

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비가 등록되었습니다.');
    }

    public function updateDeliveryCharge(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        $charge = \App\Models\ChannelDeliveryCharge::where('vendor_id', (int) $admin->vendor_id)->findOrFail($id);
        $charge->update($this->deliveryChargePayload($request, (int) $admin->vendor_id));

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비가 수정되었습니다.');
    }

    public function deleteDeliveryCharge($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        \App\Models\ChannelDeliveryCharge::where('vendor_id', (int) $admin->vendor_id)->findOrFail($id)->delete();

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비가 삭제되었습니다.');
    }

    private function deliveryChargePayload(Request $request, int $vendorId): array
    {
        $data = $request->validate([
            'status' => 'required|in:0,1',
            'name' => 'required|string|max:100',
            'courier' => 'nullable|string|max:100',
            'shipping_type' => 'required|in:free,conditional,fixed',
            'payment_type' => 'required|in:prepaid,cod',
            'base_fee' => 'nullable|integer|min:0|max:99999999',
            'free_order_amount' => 'nullable|integer|min:0|max:99999999',
            'free_order_quantity' => 'nullable|integer|min:0|max:999999',
            'fixed_fee' => 'nullable|integer|min:0|max:99999999',
            'memo' => 'nullable|string|max:1000',
        ]);

        $data['vendor_id'] = $vendorId;
        $data['base_fee'] = (int) ($data['base_fee'] ?? 0);
        $data['free_order_amount'] = $data['free_order_amount'] ?? null;
        $data['free_order_quantity'] = $data['free_order_quantity'] ?? null;
        $data['fixed_fee'] = $data['fixed_fee'] ?? null;

        if ($data['shipping_type'] === 'free') {
            $data['base_fee'] = 0;
            $data['free_order_amount'] = null;
            $data['free_order_quantity'] = null;
            $data['fixed_fee'] = null;
        }

        if ($data['shipping_type'] === 'fixed') {
            $data['fixed_fee'] = (int) ($data['fixed_fee'] ?? $data['base_fee']);
            $data['base_fee'] = $data['fixed_fee'];
            $data['free_order_amount'] = null;
            $data['free_order_quantity'] = null;
        }

        return $data;
    }

    private function channelDeliveryShippingTypes(): array
    {
        return [
            'free' => '무료배송',
            'conditional' => '무료배송(조건부)',
            'fixed' => '고정 배송비',
        ];
    }

    private function channelDeliveryPaymentTypes(): array
    {
        return [
            'prepaid' => '선결제',
            'cod' => '착불',
        ];
    }

    public function cancelRefundList()
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $policies = \App\Models\ShopCancelRefundPolicy::where('vendor_id', $admin->vendor_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('channel.sub00.cancel_refund_list', [
            'dep1_id' => '00',
            'policies' => $policies
        ]);
    }

    public function infoManagement()
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');
        
        $vendor = \App\Models\Vendor::where('id', $admin->vendor_id)->first();
        $details = \App\Models\VendorsBusinessDetail::where('vendor_id', $admin->vendor_id)->first();
        
        return view('channel.sub00.info_management', [
            'dep1_id' => '00',
            'admin' => $admin,
            'vendor' => $vendor,
            'details' => $details
        ]);
    }

    public function updateInfo(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        if ($request->isMethod('post')) {
            $data = $request->all();

            $vendor = \App\Models\Vendor::find($admin->vendor_id);

            if ($request->boolean('use_own_pg') && (int) ($vendor->status ?? 0) !== 1) {
                return redirect()->back()
                    ->withInput()
                    ->with('error_message', '판매인증이 완료된 회원사만 자사 PG를 등록할 수 있습니다.');
            }

            $request->validate([
                'pg_provider' => 'nullable|required_if:use_own_pg,1|in:inicis,kcp,toss',
                'pg_merchant_id' => 'nullable|required_if:use_own_pg,1|string|max:255',
                'pg_site_code' => 'nullable|string|max:255',
                'pg_client_key' => 'nullable|required_if:use_own_pg,1|string|max:255',
                'pg_secret_key' => 'nullable|string|max:255',
            ]);

            // 1. Update Vendor table (basic info + Ver3 PG info)
            if ($vendor) {
                $vendorPayload = [];
                if (isset($data['shop_name'])) {
                    $vendorPayload['name'] = $data['shop_name'];
                }

                $vendorPayload['use_own_pg'] = $request->boolean('use_own_pg');
                if ($request->boolean('use_own_pg')) {
                    $vendorPayload['pg_provider'] = $data['pg_provider'] ?? null;
                    $vendorPayload['pg_merchant_id'] = $data['pg_merchant_id'] ?? null;
                    $vendorPayload['pg_site_code'] = $data['pg_site_code'] ?? null;
                    $vendorPayload['pg_client_key'] = $data['pg_client_key'] ?? null;
                    if (!empty($data['pg_secret_key'])) {
                        $vendorPayload['pg_secret_key'] = $data['pg_secret_key'];
                    }
                } else {
                    $vendorPayload['pg_provider'] = null;
                    $vendorPayload['pg_merchant_id'] = null;
                    $vendorPayload['pg_site_code'] = null;
                    $vendorPayload['pg_client_key'] = null;
                    $vendorPayload['pg_secret_key'] = null;
                }

                $vendor->fill($vendorPayload)->save();
            }

            // 2. Prepare Detailed Info
            $updateData = [];
            if (isset($data['shop_name'])) $updateData['shop_name'] = $data['shop_name'];
            if (isset($data['shop_business_type'])) $updateData['shop_business_type'] = $data['shop_business_type'];
            
            if (isset($data['brn1']) && isset($data['brn2']) && isset($data['brn3'])) {
                $updateData['business_license_number'] = $data['brn1'] . '-' . $data['brn2'] . '-' . $data['brn3'];
            }
            
            if (isset($data['mobile1']) && isset($data['mobile2']) && isset($data['mobile3'])) {
                $updateData['shop_mobile'] = $data['mobile1'] . '-' . $data['mobile2'] . '-' . $data['mobile3'];
            }
            
            if (isset($data['email1']) && isset($data['email2'])) {
                $updateData['shop_email'] = $data['email1'] . '@' . $data['email2'];
            }
            
            if (isset($data['shop_pincode'])) $updateData['shop_pincode'] = $data['shop_pincode'];
            if (isset($data['shop_address'])) $updateData['shop_address'] = $data['shop_address'];
            if (isset($data['shop_address_detail'])) $updateData['shop_address_detail'] = $data['shop_address_detail'];
            
            if (isset($data['bank_name'])) $updateData['bank_name'] = $data['bank_name'];
            if (isset($data['bank_account_number'])) $updateData['bank_account_number'] = $data['bank_account_number'];
            if (isset($data['bank_account_holder_name'])) $updateData['bank_account_holder_name'] = $data['bank_account_holder_name'];

            // 3. Handle File Uploads
            $upload_path = public_path('front/images/bank_copies');
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            // Bank Copy
            if ($request->hasFile('bank_copy')) {
                $file = $request->file('bank_copy');
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'bank_' . rand(111, 99999) . '.' . $extension;
                    $file->move($upload_path, $fileName);
                    $updateData['bank_copy_image'] = $fileName;
                }
            } elseif ($request->hasFile('cert_bank_copy')) {
                 $file = $request->file('cert_bank_copy');
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'bank_' . rand(111, 99999) . '.' . $extension;
                    $file->move($upload_path, $fileName);
                    $updateData['bank_copy_image'] = $fileName;
                }
            }

            // Address Proof / BRN Image
            if ($request->hasFile('cert_brn')) {
                $file = $request->file('cert_brn');
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'brn_' . rand(111, 99999) . '.' . $extension;
                    $file->move($upload_path, $fileName);
                    $updateData['address_proof_image'] = $fileName;
                }
            }

            if (!empty($updateData)) {
                \App\Models\VendorsBusinessDetail::where('vendor_id', $admin->vendor_id)->update($updateData);
            }

            return redirect()->back()->with('success_message', '회원사 정보가 성공적으로 업데이트되었습니다.');
        }

        return redirect()->back()->with('error_message', '잘못된 접근입니다.');
    }

    public function updatePassword(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

            // 1. Validate
            if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
                return response()->json(['status' => 'error', 'message' => '모든 항목을 입력해 주세요.']);
            }

            // 2. Check current password
            if (!\Illuminate\Support\Facades\Hash::check($data['current_password'], $admin->password)) {
                return response()->json(['status' => 'error', 'message' => '현재 비밀번호가 일치하지 않습니다.']);
            }

            // 3. New password match
            if ($data['new_password'] !== $data['confirm_password']) {
                return response()->json(['status' => 'error', 'message' => '새로운 비밀번호와 확인 비밀번호가 일치하지 않습니다.']);
            }

            // 4. Update
            \App\Models\Admin::where('id', $admin->id)->update([
                'password' => \Illuminate\Support\Facades\Hash::make($data['new_password'])
            ]);

            return response()->json(['status' => 'success', 'message' => '비밀번호가 성공적으로 변경되었습니다.']);
        }
    }

    public function orderManagerList(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        app(\App\Services\ShopChannelRuntime::class)->seedDemoDataIfAllowed();

        $keyword = trim((string) $request->query('keyword', ''));

        $admin = Auth::guard('admin')->user();

        $managers = $this->orderManagersForVendor((int) ($admin->vendor_id ?? 0))
            ->withCount('products')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('email', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('channel.sub00.order_manager_list', [
            'dep1_id' => '00',
            'managers' => $managers,
            'keyword' => $keyword,
        ]);
    }

    public function storeOrderManager(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $data = $request->validate([
            'status' => 'required|in:0,1',
            'email' => 'required|email|max:255|unique:distributors,email',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|max:100',
        ]);

        $admin = Auth::guard('admin')->user();

        $payload = [
            'status' => (int) $data['status'],
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($data['password'] ?? '123456'),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('distributors', 'vendor_id')) {
            $payload['vendor_id'] = (int) ($admin->vendor_id ?? 0);
        }

        \App\Models\Distributor::create($payload);

        return back()->with('flash_message_success', '발주담당자가 등록되었습니다. 비밀번호 미입력 시 기본 비밀번호는 123456입니다.');
    }

    public function updateOrderManager(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        $manager = $this->orderManagersForVendor((int) ($admin->vendor_id ?? 0))->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:0,1',
            'email' => 'required|email|max:255|unique:distributors,email,' . $manager->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|max:100',
        ]);

        $payload = [
            'status' => (int) $data['status'],
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        $manager->update($payload);

        return back()->with('flash_message_success', '발주담당자 정보가 수정되었습니다.');
    }

    public function openOrderManagerPortal($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        $manager = $this->orderManagersForVendor((int) ($admin->vendor_id ?? 0))->findOrFail($id);

        \Illuminate\Support\Facades\Session::put('distributor_id', $manager->id);
        \Illuminate\Support\Facades\Session::put('distributor_name', $manager->name);
        \Illuminate\Support\Facades\Session::put('distributor_email', $manager->email);

        return redirect()->route('distributor.orders.pending')
            ->with('flash_message_success', $manager->name . ' 발주사 페이지로 연결되었습니다.');
    }

    private function orderManagersForVendor(int $vendorId)
    {
        $query = \App\Models\Distributor::query();

        if (\Illuminate\Support\Facades\Schema::hasColumn('distributors', 'vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        return $query;
    }

    public function pointList(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        $vendorId = (int) ($admin->vendor_id ?? 0);
        $pointService = app(\App\Services\ChannelPointService::class);
        $history = $request->query('history', 'all');
        $status = $request->query('status', 'all');
        $transactions = \App\Models\ChannelPointTransaction::with('shopChannel')
            ->where('vendor_id', $vendorId);

        if ($history === 'purchase') {
            $transactions->where('type', \App\Services\ChannelPointService::TYPE_PURCHASE);
        } elseif ($history === 'use') {
            $transactions->whereIn('type', [
                \App\Services\ChannelPointService::TYPE_CUSTOMER_PAYBACK,
                \App\Services\ChannelPointService::TYPE_SMS,
            ]);
        } elseif ($history === 'refund') {
            $transactions->where('type', \App\Services\ChannelPointService::TYPE_REFUND);
        }

        if ($status !== 'all') {
            $transactions->where('status', $status);
        }

        $transactions = $transactions->latest()->paginate(20)->withQueryString();

        return view('channel.sub00.point_list', [
            'dep1_id' => '00',
            'summary' => $pointService->summaryForVendor($vendorId),
            'transactions' => $transactions,
            'canRequestRefund' => $pointService->canRequestRefund($vendorId),
            'hasActiveChannel' => $pointService->hasActiveChannel($vendorId),
            'hasPendingClosure' => $pointService->hasPendingClosure($vendorId),
            'filters' => [
                'history' => $history,
                'status' => $status,
            ],
        ]);
    }

    public function requestOperationStop(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $data = $request->validate([
            'closure_memo' => 'nullable|string|max:255',
        ]);

        $admin = Auth::guard('admin')->user();
        $vendorId = (int) ($admin->vendor_id ?? 0);
        $channels = \App\Models\ShopChannel::where('vendor_id', $vendorId)->get();

        if ($channels->isEmpty()) {
            return redirect()->back()->with('error_message', '운영중지 요청할 Shop 채널이 없습니다.');
        }

        $activeChannels = $channels->where('status', 1);
        if ($activeChannels->isNotEmpty()) {
            return redirect()->back()->with('error_message', '운영 중인 Shop 채널이 있어 운영중지 요청을 접수할 수 없습니다. 먼저 판매 상태를 종료해 주세요.');
        }

        foreach ($channels as $channel) {
            if ($channel->closure_status === 'approved') {
                continue;
            }

            $channel->forceFill([
                'closure_status' => 'requested',
                'closure_requested_at' => now(),
                'closure_approved_at' => null,
                'closure_rejected_at' => null,
                'closure_reviewed_by' => null,
                'closure_memo' => $data['closure_memo'] ?? null,
            ])->save();
        }

        return redirect()->back()->with('success_message', 'Shop 채널 운영중지 요청이 접수되었습니다. 최고관리자 승인 후 포인트 환급과 Me9 포인트 전환이 가능합니다.');
    }

    public function requestPointPurchase(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $data = $request->validate([
            'points' => 'required|integer|min:1000',
            'payment_method' => 'required|in:card,transfer',
            'memo' => 'nullable|string|max:255',
        ]);

        $admin = Auth::guard('admin')->user();
        app(\App\Services\ChannelPointService::class)->requestPurchase(
            (int) $admin->vendor_id,
            (int) $data['points'],
            $data['payment_method'],
            $data['memo'] ?? null,
            null,
            (int) $admin->id
        );

        return redirect()->route('channel.point.list')->with('success_message', '포인트 구매 요청이 접수되었습니다. 최고관리자 승인 후 보유 포인트에 반영됩니다.');
    }

    public function requestPointRefund(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $data = $request->validate([
            'points' => 'required|integer|min:1000',
            'memo' => 'nullable|string|max:255',
        ]);

        $admin = Auth::guard('admin')->user();
        app(\App\Services\ChannelPointService::class)->requestRefund(
            (int) $admin->vendor_id,
            (int) $data['points'],
            $data['memo'] ?? null,
            null,
            (int) $admin->id
        );

        return redirect()->route('channel.point.list')->with('success_message', '포인트 환급 요청이 접수되었습니다. 최고관리자 승인 후 환급 처리됩니다.');
    }
    
    public function subList(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $admin = Auth::guard('admin')->user();
        $keyword = trim((string) $request->query('keyword', ''));

        $accounts = \App\Models\ChannelSubAccount::with('admin')
            ->where('vendor_id', (int) $admin->vendor_id)
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('member_no', 'like', '%' . $keyword . '%')
                        ->orWhereHas('admin', function ($adminQuery) use ($keyword) {
                            $adminQuery->where('email', 'like', '%' . $keyword . '%')
                                ->orWhere('name', 'like', '%' . $keyword . '%')
                                ->orWhere('mobile', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('channel.sub00.sub_accounts_list', [
            'dep1_id' => '00',
            'accounts' => $accounts,
            'keyword' => $keyword,
            'permissionLabels' => $this->subAccountPermissionLabels(),
        ]);
    }

    public function storeSubAccount(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $owner = Auth::guard('admin')->user();
        $data = $this->subAccountValidatedData($request, null);

        $admin = new \App\Models\Admin;
        $admin->name = $data['name'];
        $admin->type = 'subadmin';
        $admin->vendor_id = (int) $owner->vendor_id;
        $admin->mobile = $data['mobile'] ?? '';
        $admin->email = $data['email'];
        $admin->password = \Illuminate\Support\Facades\Hash::make($data['password'] ?? '123456');
        $admin->confirm = 'Yes';
        $admin->status = (int) $data['status'];
        $admin->save();

        \App\Models\ChannelSubAccount::create([
            'vendor_id' => (int) $owner->vendor_id,
            'admin_id' => $admin->id,
            'member_no' => $data['member_no'] ?? null,
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('channel.sub_accounts.list')->with('flash_message_success', '서브관리자가 등록되었습니다.');
    }

    public function updateSubAccount(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $owner = Auth::guard('admin')->user();
        $account = \App\Models\ChannelSubAccount::with('admin')
            ->where('vendor_id', (int) $owner->vendor_id)
            ->findOrFail($id);

        $data = $this->subAccountValidatedData($request, $account->admin_id);

        $account->admin->forceFill([
            'name' => $data['name'],
            'mobile' => $data['mobile'] ?? '',
            'email' => $data['email'],
            'status' => (int) $data['status'],
        ]);

        if (!empty($data['password'])) {
            $account->admin->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        $account->admin->save();
        $account->update([
            'member_no' => $data['member_no'] ?? null,
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('channel.sub_accounts.list')->with('flash_message_success', '서브관리자가 수정되었습니다.');
    }

    public function deleteSubAccount($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $owner = Auth::guard('admin')->user();
        $account = \App\Models\ChannelSubAccount::with('admin')
            ->where('vendor_id', (int) $owner->vendor_id)
            ->findOrFail($id);
        $admin = $account->admin;
        $account->delete();

        if ($admin && $admin->type === 'subadmin') {
            $admin->delete();
        }

        return redirect()->route('channel.sub_accounts.list')->with('flash_message_success', '서브관리자가 삭제되었습니다.');
    }

    private function subAccountValidatedData(Request $request, ?int $adminId): array
    {
        return $request->validate([
            'status' => 'required|in:0,1',
            'member_no' => 'nullable|string|max:50',
            'email' => 'required|email|max:255|unique:admins,email' . ($adminId ? ',' . $adminId : ''),
            'name' => 'required|string|max:100',
            'mobile' => 'nullable|string|max:50',
            'password' => ($adminId ? 'nullable' : 'required') . '|string|min:6|max:100',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:shop,product,joint_purchase,order,settings',
        ]);
    }

    private function subAccountPermissionLabels(): array
    {
        return [
            'shop' => 'Shop 채널',
            'product' => '상품관리',
            'joint_purchase' => '공동상품관리',
            'order' => '주문관리',
            'settings' => '설정관리',
        ];
    }

    // 채널 등록 (판매자)
    public function register()
    {
        return view('channel.register');
    }

    public function registerSubmit(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // 유효성 검사
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name'     => 'required|string|max:100',
                'mobile'   => 'required|numeric|digits_between:10,15|unique:admins|unique:vendors',
                'email'    => 'required|email|max:150|unique:admins|unique:vendors',
                'password' => 'required|min:6',
                'shop_name' => 'required|string|max:150',
                'shop_mobile' => 'required|numeric|digits_between:10,15',
                'business_license_number' => 'required|string|max:150',
                'shop_business_type' => 'required|in:1,2,3', // 1: 개인, 2: 개인 사업자, 3: 법인 사업자
                'shop_pincode' => 'nullable|string|max:20',
                'shop_address' => 'nullable|string|max:255',
                'shop_address_detail' => 'nullable|string|max:255',
                'bank_name' => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:100',
                'bank_account_holder_name' => 'required|string|max:100',
                'bank_copy_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
                'accept'   => 'required'
            ], [
                'accept.required' => '이용약관 및 개인정보 처리방침에 동의해 주세요.',
                'email.unique'    => '이미 존재하는 이메일입니다.',
                'mobile.unique'   => '이미 존재하는 휴대폰 번호입니다.',
                'shop_business_type.required' => '사업자 유형을 선택해 주세요.'
            ]);

            if ($validator->passes()) {
                \Illuminate\Support\Facades\DB::beginTransaction();

                try {
                    // 1. 판매자 생성
                    $vendor = new \App\Models\Vendor;
                    $vendor->name   = $data['name'];
                    $vendor->mobile = $data['mobile'];
                    $vendor->email  = $data['email'];
                    $vendor->status = 0; // 관리자 승인 전까지 비활성 상태
                    date_default_timezone_set('Asia/Seoul'); 
                    $vendor->created_at = date('Y-m-d H:i:s');
                    $vendor->updated_at = date('Y-m-d H:i:s');
                    $vendor->save();

                    $vendor_id = \Illuminate\Support\Facades\DB::getPdo()->lastInsertId();

                    // 파일 업로드
                    $bank_copy_path = null;
                    if ($request->hasFile('bank_copy_image')) {
                        $image_tmp = $request->file('bank_copy_image');
                        if ($image_tmp->isValid()) {
                            $extension = $image_tmp->getClientOriginalExtension();
                            $fileName = 'bank_copy_' . time() . '.' . $extension;
                            $destinationPath = public_path('images/proofs/bank');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $image_tmp->move($destinationPath, $fileName);
                            $bank_copy_path = 'images/proofs/bank/' . $fileName;
                        }
                    }

                    // 2. 판매자 사업자 상세 정보 생성
                    \Illuminate\Support\Facades\DB::table('vendors_business_details')->insert([
                        'vendor_id' => $vendor_id,
                        'shop_name' => $data['shop_name'],
                        'shop_mobile' => $data['shop_mobile'],
                        'business_license_number' => $data['business_license_number'],
                        'shop_business_type' => $data['shop_business_type'] ?? null,
                        'shop_address' => $data['shop_address'] ?? '',
                        'shop_address_detail' => $data['shop_address_detail'] ?? '',
                        'shop_city' => '', // 주소에서 추출 가능하지만 현재는 수동 입력
                        'shop_state' => '',
                        'shop_country' => '',
                        'shop_pincode' => $data['shop_pincode'] ?? '',
                        'bank_name' => $data['bank_name'] ?? '',
                        'bank_account_number' => $data['bank_account_number'] ?? '',
                        'bank_account_holder_name' => $data['bank_account_holder_name'] ?? '',
                        'bank_copy_image' => $bank_copy_path,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // 3. Admin 생성 (유형: Vendor)
                    $admin = new \App\Models\Admin;
                    $admin->type      = 'vendor';
                    $admin->vendor_id = $vendor_id;
                    $admin->name      = $data['name'];
                    $admin->mobile    = $data['mobile'];
                    $admin->email     = $data['email'];
                    $admin->password  = bcrypt($data['password']);
                    $admin->status    = 0; // 비활성 상태
                    $admin->created_at = date('Y-m-d H:i:s');
                    $admin->updated_at = date('Y-m-d H:i:s');
                    $admin->save();

                    // 4. 확인 이메일 발송
                    $email = $data['email'];
                    $messageData = [
                        'email' => $data['email'],
                        'name'  => $data['name'],
                        'code'  => base64_encode($data['email'])
                    ];

                    \Illuminate\Support\Facades\Mail::send('emails.vendor_confirmation', $messageData, function ($message) use ($email) {
                        $message->to($email)->subject('판매자 계정 확인');
                    });

                    \Illuminate\Support\Facades\DB::commit();

                    // 성공 응답 반환
                    $redirectTo = route('channel.login'); 
                    return response()->json([
                        'type'    => 'success',
                        'url'     => $redirectTo,
                        'message' => '판매자 등록 신청이 완료되었습니다. 이메일을 확인하여 계정 확인을 완료해 주세요.'
                    ]);

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollback();
                    return response()->json([
                        'type'    => 'error',
                        'errors'  => ['email' => [$e->getMessage()]]
                    ]);
                }

            } else {
                return response()->json([
                    'type'   => 'error',
                    'errors' => $validator->messages()
                ]);
            }
        }
    }
    // 프로필 완성 (상태가 0인 판매자용)
    public function completeProfile()
    {
        // 로그인 확인
        if (!\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($user->type != 'vendor') {
            return redirect()->route('admin.dashboard');
        }

        // 이미 제출되었는지 확인 (간단한 확인: 상세 정보에 유효한 사업자 등록 번호가 있는지 확인)
        $details = \Illuminate\Support\Facades\DB::table('vendors_business_details')
                        ->where('vendor_id', $user->vendor_id)->first();

        // 상세 정보가 존재하고 사업자 번호가 있으면 '심사 중' 페이지 표시
        if ($details && !empty($details->business_license_number)) {
             return view('channel.application_submitted');
        }

        return view('channel.complete_profile');
    }

    public function completeProfileSubmit(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // 유효성 검사 (입력 양식과 비슷하지만 사업자 상세 정보에 대해 엄격함)
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'shop_name' => 'required|string|max:150',
                'shop_business_type' => 'required|in:1,2,3',
                'business_license_number' => 'required|string|max:150',
                'shop_mobile' => 'required|numeric|digits_between:10,15',
                'bank_name' => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:100',
                'bank_account_holder_name' => 'required|string|max:100',
                'bank_copy_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
                'accept'   => 'required'
            ], [
                'accept.required' => '이용약관 및 개인정보 처리방침에 동의해 주세요.',
                'shop_business_type.required' => '사업자 유형을 선택해 주세요.'
            ]);

            if ($validator->passes()) {
                $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                $vendor_id = $user->vendor_id;

                try {
                    // 파일 업로드
                    $bank_copy_path = null;
                    if ($request->hasFile('bank_copy_image')) {
                        $image_tmp = $request->file('bank_copy_image');
                        if ($image_tmp->isValid()) {
                            $extension = $image_tmp->getClientOriginalExtension();
                            $fileName = 'bank_copy_' . time() . '.' . $extension;
                            $destinationPath = public_path('images/proofs/bank');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $image_tmp->move($destinationPath, $fileName);
                            $bank_copy_path = 'images/proofs/bank/' . $fileName;
                        }
                    }

                    // 사업자 상세 정보 업데이트 또는 삽입
                    \Illuminate\Support\Facades\DB::table('vendors_business_details')->updateOrInsert(
                        ['vendor_id' => $vendor_id],
                        [
                            'shop_name' => $data['shop_name'],
                            'shop_mobile' => $data['shop_mobile'],
                            'business_license_number' => $data['business_license_number'],
                            'shop_business_type' => $data['shop_business_type'],
                            'shop_address' => $data['shop_address'] ?? '',
                            'shop_address_detail' => $data['shop_address_detail'] ?? '',
                            'shop_pincode' => $data['shop_pincode'] ?? '',
                            'bank_name' => $data['bank_name'],
                            'bank_account_number' => $data['bank_account_number'],
                            'bank_account_holder_name' => $data['bank_account_holder_name'],
                            // 새로운 이미지가 제공된 경우에만 이미지 업데이트
                            // For simplicty in updateOrInsert, if $bank_copy_path is null, we might lose old one if we validly overwrite?
                            // Actually updateOrInsert merges. But if we pass null, it sets null?
                            // Let's check first.
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    );

                    // 이미지가 제공된 경우, 기존 이미지가 null로 덮어씌워지지 않도록 명시적으로 업데이트
                    if ($bank_copy_path) {
                        \Illuminate\Support\Facades\DB::table('vendors_business_details')
                            ->where('vendor_id', $vendor_id)
                            ->update(['bank_copy_image' => $bank_copy_path]);
                    } elseif (\Illuminate\Support\Facades\DB::table('vendors_business_details')->where('vendor_id', $vendor_id)->doesntExist()) {
                         // 새로 삽입하고 이미지가 없으면 null입니다.
                    }

                    // 삽입된 경우 created_at이 설정되었는지 확인
                    // (updateOrInsert doesn't automatically handle created_at on insert if not in values)
                    // We can just rely on updated_at for now.

                    return response()->json([
                        'type'    => 'success',
                        'url'     => route('channel.complete_profile'), // 심사 중 페이지를 보여주기 위해 새로고침
                        'message' => '신청서가 성공적으로 제출되었습니다!'
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'type'    => 'error',
                        'errors'  => ['error' => [$e->getMessage()]]
                    ]);
                }
            } else {
                 return response()->json([
                    'type'   => 'error',
                    'errors' => $validator->messages()
                ]);
            }
        }
    }

    // 취소/환불 정책 CRUD 메서드들
    public function storeCancelRefundPolicy(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $validated = $request->validate([
            'type' => 'required|in:default,custom',
            'status' => 'required|in:active,inactive',
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
        ], [
            'type.required' => '설정구분을 선택해 주세요.',
            'status.required' => '상태를 선택해 주세요.',
            'name.required' => '취소/환불안내 명칭을 입력해 주세요.',
        ]);

        $policy = \App\Models\ShopCancelRefundPolicy::create([
            'vendor_id' => $admin->vendor_id,
            'type' => $validated['type'],
            'status' => $validated['status'],
            'name' => $validated['name'],
            'content' => $validated['content'] ?? '',
            'product_count' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '취소/환불안내가 등록되었습니다.',
            'policy' => $policy
        ]);
    }

    public function getCancelRefundPolicy($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $policy = \App\Models\ShopCancelRefundPolicy::where('id', $id)
            ->where('vendor_id', $admin->vendor_id)
            ->first();

        if (!$policy) {
            return response()->json(['status' => 'error', 'message' => '정책을 찾을 수 없습니다.']);
        }

        return response()->json([
            'status' => 'success',
            'policy' => $policy
        ]);
    }

    public function updateCancelRefundPolicy(Request $request, $id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $policy = \App\Models\ShopCancelRefundPolicy::where('id', $id)
            ->where('vendor_id', $admin->vendor_id)
            ->first();

        if (!$policy) {
            return response()->json(['status' => 'error', 'message' => '정책을 찾을 수 없습니다.']);
        }

        $validated = $request->validate([
            'type' => 'required|in:default,custom',
            'status' => 'required|in:active,inactive',
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
        ], [
            'type.required' => '설정구분을 선택해 주세요.',
            'status.required' => '상태를 선택해 주세요.',
            'name.required' => '취소/환불안내 명칭을 입력해 주세요.',
        ]);

        $policy->update([
            'type' => $validated['type'],
            'status' => $validated['status'],
            'name' => $validated['name'],
            'content' => $validated['content'] ?? '',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '취소/환불안내가 수정되었습니다.',
            'policy' => $policy
        ]);
    }

    public function deleteCancelRefundPolicy($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $policy = \App\Models\ShopCancelRefundPolicy::where('id', $id)
            ->where('vendor_id', $admin->vendor_id)
            ->first();

        if (!$policy) {
            return response()->json(['status' => 'error', 'message' => '정책을 찾을 수 없습니다.']);
        }

        $policy->delete();

        return response()->json([
            'status' => 'success',
            'message' => '취소/환불안내가 삭제되었습니다.'
        ]);
    }

    public function copyCancelRefundPolicy($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $policy = \App\Models\ShopCancelRefundPolicy::where('id', $id)
            ->where('vendor_id', $admin->vendor_id)
            ->first();

        if (!$policy) {
            return response()->json(['status' => 'error', 'message' => '정책을 찾을 수 없습니다.']);
        }

        $newPolicy = \App\Models\ShopCancelRefundPolicy::create([
            'vendor_id' => $admin->vendor_id,
            'type' => $policy->type,
            'status' => 'inactive', // 복사본은 기본적으로 중지 상태
            'name' => $policy->name . ' (복사본)',
            'content' => $policy->content,
            'product_count' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '취소/환불안내가 복사되었습니다.',
            'policy' => $newPolicy
        ]);
    }

    public function jointPurchaseList()
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $jointPurchases = \Illuminate\Support\Facades\DB::table('joint_purchases')
            ->leftJoin('products', 'joint_purchases.product_id', '=', 'products.id')
            ->select('joint_purchases.*', 'products.product_name', 'products.product_code')
            ->where('products.vendor_id', $admin->vendor_id)
            ->orderBy('joint_purchases.id', 'desc')
            ->paginate(10);

        return view('channel.sub03.joint_purchase_list', [
            'dep1_id' => '03',
            'jointPurchases' => $jointPurchases
        ]);
    }

    public function jointPurchaseCreate()
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        // Fetch vendor products to choose from
        $products = \App\Models\Product::where('vendor_id', $admin->vendor_id)->get();
        return view('channel.sub03.joint_purchase_create', [
            'dep1_id' => '03',
            'products' => $products
        ]);
    }

    public function jointPurchaseStore(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'min_quantity' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = \App\Models\Product::where('id', $request->product_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        $pricing = app(\App\Services\JointPurchasePricingService::class);
        $tiers = $pricing->normalizeTierInput($request->all());
        if (empty($tiers)) {
            return back()->withErrors(['tier_unit_price' => '공동구매 수량별 가격을 1개 이상 입력해 주세요.'])->withInput();
        }

        $jointPurchaseId = \Illuminate\Support\Facades\DB::table('joint_purchases')->insertGetId([
            'product_id' => $product->id,
            'min_quantity' => $request->min_quantity,
            'current_quantity' => 0,
            'discount_price' => $tiers[0]['unit_price'],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $pricing->syncTiers((int) $jointPurchaseId, $tiers);

        return redirect()->route('channel.joint_purchase.list')->with('success_message', '공동구매 상품이 성공적으로 등록되었습니다.');
    }

    public function jointPurchaseEdit($id)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $jointPurchase = \Illuminate\Support\Facades\DB::table('joint_purchases')
            ->join('products', 'joint_purchases.product_id', '=', 'products.id')
            ->select('joint_purchases.*')
            ->where('joint_purchases.id', $id)
            ->where('products.vendor_id', $admin->vendor_id)
            ->first();

        if (!$jointPurchase) {
            abort(404);
        }

        $products = \App\Models\Product::where('vendor_id', $admin->vendor_id)->get();
        $tiers = app(\App\Services\JointPurchasePricingService::class)->tiers((int) $id);

        return view('channel.sub03.joint_purchase_edit', [
            'dep1_id' => '03',
            'jointPurchase' => $jointPurchase,
            'products' => $products,
            'tiers' => $tiers,
        ]);
    }

    public function jointPurchaseUpdate(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required',
            'min_quantity' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $jointPurchase = \Illuminate\Support\Facades\DB::table('joint_purchases')
            ->join('products', 'joint_purchases.product_id', '=', 'products.id')
            ->where('joint_purchases.id', $id)
            ->where('products.vendor_id', $admin->vendor_id)
            ->select('joint_purchases.id')
            ->first();

        if (!$jointPurchase) {
            abort(404);
        }

        $product = \App\Models\Product::where('id', $request->product_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        $pricing = app(\App\Services\JointPurchasePricingService::class);
        $tiers = $pricing->normalizeTierInput($request->all());
        if (empty($tiers)) {
            return back()->withErrors(['tier_unit_price' => '공동구매 수량별 가격을 1개 이상 입력해 주세요.'])->withInput();
        }

        \Illuminate\Support\Facades\DB::table('joint_purchases')->where('id', $id)->update([
            'product_id' => $product->id,
            'min_quantity' => $request->min_quantity,
            'discount_price' => $tiers[0]['unit_price'],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'updated_at' => now()
        ]);
        $pricing->syncTiers((int) $id, $tiers);
        $pricing->repricePurchase((int) $id);

        return redirect()->route('channel.joint_purchase.list')->with('success_message', '공동구매 정보가 성공적으로 수정되었습니다.');
    }
}
