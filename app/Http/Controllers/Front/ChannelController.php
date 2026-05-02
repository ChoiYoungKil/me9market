<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            $statusCounts = \App\Models\OrdersProduct::where('vendor_id', $vendor_id)
                ->select('item_status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('item_status')
                ->pluck('total', 'item_status')
                ->all();

            $data = [
                'total' => array_sum($statusCounts),
                'paid' => $statusCounts['결제완료'] ?? 0,
                'shipping_ready' => $statusCounts['배송대기'] ?? 0,
                'shipping' => $statusCounts['배송중'] ?? 0,
                'complete' => $statusCounts['구매확정'] ?? 0,
                'cancel_request' => $statusCounts['취소요청'] ?? 0,
                'return_request' => $statusCounts['반품요청'] ?? 0,
                'settlement_wait' => \App\Models\OrdersProduct::where('vendor_id', $vendor_id)->where('item_status', '구매확정')->count(),
                'settlement_complete' => 0, // Placeholder until settlement_status exists
            ];

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
                    \Illuminate\Support\Facades\DB::raw('SUM(product_price * product_qty) as total_sales')
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

            return view('channel.index', [
                'dep1_id' => '00',
                'user' => $user,
                'counts' => $data,
                'recentOrders' => $recentOrders,
                'chartData' => $chartData
            ]);
        }
        return redirect()->route('channel.login');
    }

    public function login()
    {
        return view('channel.login');
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

        // 비공개시 비밀번호 체크
        if (($data['is_public'] ?? '1') == '0') {
            $rules['password'] = 'required|min:4';
            $messages['password.required'] = '비공개 설정 시 비밀번호를 입력해 주세요.';
        }

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
        $shop->is_member_only = isset($data['is_member_only']) ? 1 : 0;
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

    public function shopProduct01(Request $request)
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

        $ownProducts = \App\Models\Product::where('vendor_id', $admin->vendor_id)
            ->where('status', 1)
            ->whereNotIn('id', $alreadyAddedProductIds)
            ->with(['category', 'images'])
            ->paginate(10, ['*'], 'own_page');

        return view('channel.sub01.shop_product01', [
            'dep1_id' => '01',
            'products' => $products,
            'shopId' => $shopId,
            'ownProducts' => $ownProducts
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
        }

        if (!$shopId) {
            return redirect()->route('channel.shop_list')->with('error_message', 'Shop 채널을 먼저 등록해 주세요.');
        }

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

        $ownProducts = \App\Models\Product::where('vendor_id', $admin->vendor_id)
            ->where('status', 1)
            ->whereNotIn('id', $alreadyAddedProductIds)
            ->with(['category', 'images'])
            ->paginate(10, ['*'], 'own_page');

        return view('channel.sub01.shop_product02', [
            'dep1_id' => '01',
            'products' => $products,
            'shopId' => $shopId,
            'ownProducts' => $ownProducts
        ]);
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

    public function productOwn()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $products = \App\Models\Product::where('vendor_id', $admin->vendor_id)
            ->with(['category', 'images'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('channel.sub02.product_own', [
            'dep1_id' => '02',
            'products' => $products
        ]);
    }

    public function productPublic()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $products = \App\Models\Product::where('is_public', 1)
            ->with(['category', 'images', 'vendor.vendorbusinessdetails'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('channel.sub02.product_public', [
            'dep1_id' => '02',
            'products' => $products
        ]);
    }

    public function productPartial()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $products = \App\Models\Product::where('is_partial', 1)
            ->with(['category', 'images', 'vendor.vendorbusinessdetails'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('channel.sub02.product_partial', [
            'dep1_id' => '02',
            'products' => $products
        ]);
    }

    public function productRequest(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        // Fetch requests for products belonging to THIS vendor (where others requested permission)
        $requests = \App\Models\ShopChannelProduct::whereHas('product', function($query) use ($admin) {
                $query->where('vendor_id', $admin->vendor_id);
            })
            ->where('product_type', 'partial')
            ->with(['product' => function($query) {
                $query->with(['category', 'images']);
            }, 'shopChannel' => function($query) {
                $query->with('vendor'); // To show who is requesting
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('channel.sub02.product_request', [
            'dep1_id' => '02',
            'requests' => $requests
        ]);
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
            if ($notice->attachment && file_exists(public_path('uploads/notices/' . $notice->attachment))) {
                unlink(public_path('uploads/notices/' . $notice->attachment));
            }
            $notice->attachment = null;
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file
            if ($notice->attachment && file_exists(public_path('uploads/channel/notices/' . $notice->attachment))) {
                unlink(public_path('uploads/channel/notices/' . $notice->attachment));
            }
            
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/channel/notices'), $filename);
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
        if ($notice->attachment && file_exists(public_path('uploads/shop_notices/' . $notice->attachment))) {
            unlink(public_path('uploads/shop_notices/' . $notice->attachment));
        }

        $notice->delete();

        return redirect()->route('channel.shop_community', ['shop_id' => $shop->id])
            ->with('success_message', '공지사항이 삭제되었습니다.');
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
            'shop' => $shop
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

        $request->validate($rules, $messages);

        // 2. Update Data
        $shop->status = $data['status'] ?? 0;
        $shop->is_public = $data['is_public'] ?? 1;
        $shop->password = $data['password'] ?? null;
        $shop->is_member_only = isset($data['is_member_only']) ? 1 : 0;
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

        return redirect()->route('channel.shop_info', ['id' => $shop->id])->with('success_message', '채널 정보가 수정되었습니다.');
    }

    // 상품 관리 (Sub02) 관련 메서드는 위에 이미 정의되어 있습니다.

    // 주문 관리 (Sub04)
    public function orderList()
    {
        $vendor_id = \Illuminate\Support\Facades\Auth::guard('admin')->user()->vendor_id;
        $orders = $this->fetchOrders($vendor_id);
        return view('channel.sub04.order_list', ['dep1_id' => '04', 'orders' => $orders]);
    }
    
    public function orderCancelList()
    {
        $vendor_id = \Illuminate\Support\Facades\Auth::guard('admin')->user()->vendor_id;
        $orders = $this->fetchOrders($vendor_id, ['취소요청', '취소완료']);
        return view('channel.sub04.order_list', ['dep1_id' => '04', 'orders' => $orders]);
    }

    public function orderReturnRequestList()
    {
        $vendor_id = \Illuminate\Support\Facades\Auth::guard('admin')->user()->vendor_id;
        $orders = $this->fetchOrders($vendor_id, ['반품요청', '반품완료']);
        return view('channel.sub04.order_list', ['dep1_id' => '04', 'orders' => $orders]);
    }

    public function orderExchangeRequestList()
    {
        $vendor_id = \Illuminate\Support\Facades\Auth::guard('admin')->user()->vendor_id;
        $orders = $this->fetchOrders($vendor_id, ['교환요청', '교환완료']);
        return view('channel.sub04.order_list', ['dep1_id' => '04', 'orders' => $orders]);
    }

    private function fetchOrders($vendor_id, $statusFilter = [])
    {
        $query = \App\Models\Order::with(['orders_products' => function($q) use ($vendor_id, $statusFilter) {
            $q->where('vendor_id', $vendor_id);
            if (!empty($statusFilter)) {
                $q->whereIn('item_status', $statusFilter);
            }
        }, 'user']);

        // Filter orders that have at least one product matching the criteria
        $query->whereHas('orders_products', function($q) use ($vendor_id, $statusFilter) {
            $q->where('vendor_id', $vendor_id);
            if (!empty($statusFilter)) {
                $q->whereIn('item_status', $statusFilter);
            }
        });

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        // Transform
        $orders->getCollection()->transform(function($order) {
            $order->order_no = 'Me9-' . str_pad($order->id, 8, '0', STR_PAD_LEFT); 
            $order->shop_name = 'Me9 Market'; 
            $order->user_name = $order->name; 
            
            // Note: $order->orders_products will only contain the filtered items due to 'with' usage above
            $vendorItems = $order->orders_products;
            
            $order->items = $vendorItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'status' => $item->item_status, 
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'option_name' => $item->product_color . '/' . $item->product_size,
                    'qty' => $item->product_qty,
                    'price' => $item->product_price,
                    'product_type' => '자사', 
                ];
            });

            $totalProductPrice = $vendorItems->sum(function($item) {
                return $item->product_price * $item->product_qty;
            });

            $order->total_product_price = $totalProductPrice;
            $order->total_sale_price = $totalProductPrice; 
            $order->total_profit = 0; 
            $order->total_selling_profit = 0; 
            
            $order->delivery_fee = 0; 
            
            $order->used_point = 0;
            $order->total_payment_price = $totalProductPrice; 
            $order->earned_point = 0;

            $order->status = $order->items->first()['status'] ?? 'Pending';
            
            return $order;
        });

        return $orders;
    }

    public function orderInfo()
    {
        return view('channel.sub04.inc.pop_order_info'); // 독립형 또는 팝업으로 사용될 수 있습니다.
    }
    
    // 추가 정보 / 설정 (Sub00)
    public function deliveryChargeList()
    {
        return view('channel.sub00.delivery_charge_list', ['dep1_id' => '00']);
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

            // 1. Update Vendor table (basic info)
            if (isset($data['shop_name'])) {
                \App\Models\Vendor::where('id', $admin->vendor_id)->update([
                    'name' => $data['shop_name']
                ]);
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

    public function orderManagerList()
    {
        return view('channel.sub00.order_manager_list', ['dep1_id' => '00']);
    }

    public function pointList()
    {
        return view('channel.sub00.point_list', ['dep1_id' => '00']);
    }
    
    public function subList()
    {
        return view('channel.sub00.sub_accounts_list', ['dep1_id' => '00']);
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
}
