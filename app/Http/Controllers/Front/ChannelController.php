<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Services\ChannelOrderMetrics;
use App\Support\OrderItemStatus;

class ChannelController extends Controller
{


    public function index(\App\Services\ChannelDashboard $dashboard)
    {
        $user = Auth::guard('admin')->user();
        if (! $user) return redirect()->route('channel.login');
        if ($user->type === 'vendor' && (int) $user->status === 0) {
            return redirect()->route('channel.complete_profile');
        }
        return view('channel.index', $dashboard->data($user) + ['user' => $user, 'dep1_id' => '00']);
    }

    public function saveDashboardShortcuts(Request $request, \App\Services\ChannelDashboard $dashboard)
    {
        $user = Auth::guard('admin')->user();
        $data = $request->validate([
            'shortcuts' => 'nullable|array|max:7',
            'shortcuts.*' => ['string', \Illuminate\Validation\Rule::in(array_keys($dashboard->shortcuts($user)))],
        ]);
        $user->channel_shortcuts = array_values(array_unique($data['shortcuts'] ?? []));
        $user->save();
        return redirect()->route('channel.index')->with('success_message', '바로가기 메뉴를 저장했습니다.');
    }

    public function openDashboardShop(int $id, \App\Services\ShopChannelRuntime $runtime)
    {
        $shop = \App\Models\ShopChannel::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->findOrFail($id);
        $runtime->enterManagedChannel($shop);
        return redirect()->route('shop.channel_main');
    }

    private const SAVED_CHANNEL_LOGIN_ID = 'me9_channel_login_id';

    public function login(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($admin && (int) $admin->status === 1 && in_array($admin->type, ['vendor','subadmin'], true)) {
            return redirect()->route('channel.index');
        }
        $saved = $request->cookie(self::SAVED_CHANNEL_LOGIN_ID, '');
        $saved = is_string($saved) ? $saved : '';
        $loginId = $request->session()->hasOldInput('login_id') ? old('login_id') : $saved;
        $loginId = is_string($loginId) ? $loginId : '';
        $rememberId = $request->session()->hasOldInput('login_id') ? (bool) old('remember_id') : $saved !== '';
        return view('channel.login', compact('loginId','rememberId'));
    }

    public function loginUser(Request $request)
    {
        $loginId = $request->input('login_id', $request->input('email', ''));
        $request->merge(['login_id'=>is_string($loginId) ? trim($loginId) : $loginId]);
        $data = $request->validate([
            'login_id'=>'required|string|max:255', 'password'=>'required|string|max:4096', 'remember_id'=>'nullable|boolean',
        ], ['login_id.required'=>'아이디를 입력해 주세요.', 'password.required'=>'비밀번호를 입력해 주세요.']);
        $member = \App\Models\User::where(fn ($query) => $query->where('username', $data['login_id'])->orWhere('email', $data['login_id']))->first();
        if ($member && \Illuminate\Support\Facades\Hash::check($data['password'], $member->password)) {
            $admin = app(\App\Services\ChannelMemberAccess::class)->administratorFor($member);
            Auth::guard('web')->login($member);
        } else {
            $admin = \App\Models\Admin::where('email', $data['login_id'])->first();
            if (! $admin || ! \Illuminate\Support\Facades\Hash::check($data['password'], $admin->password)) {
                return redirect()->route('channel.login')->with('error_message', '아이디 또는 비밀번호가 일치하지 않습니다.')
                    ->withInput($request->only('login_id','remember_id'));
            }
            if (! in_array($admin->type, ['vendor','subadmin'], true)) {
                return redirect()->route('channel.login')->with('error_message', '채널관리자 전용 로그인입니다. 전체관리자 계정은 관리자 로그인 화면을 이용해 주세요.')
                    ->withInput($request->only('login_id','remember_id'));
            }
            if ((int) $admin->status !== 1 || $admin->confirm === 'No') {
                return redirect()->route('channel.login')->with('error_message', '비활성화되었거나 이메일 인증이 필요한 채널관리자 계정입니다.')
                    ->withInput($request->only('login_id','remember_id'));
            }
        }
        if ($admin->type === 'subadmin' && \App\Models\ChannelSubAccount::where('admin_id',$admin->id)->whereNotNull('user_id')->exists()
            && !($member && \Illuminate\Support\Facades\Hash::check($data['password'],$member->password))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['login_id'=>'연결된 회원의 아이디와 비밀번호로 로그인해 주세요.']);
        }
        if ($admin->type === 'subadmin' && !\App\Models\ChannelSubAccount::where('admin_id',$admin->id)->where('vendor_id',$admin->vendor_id)->activeNow()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['login_id'=>'서브관리자 이용기간 또는 회원 상태를 확인해 주세요.']);
        }
        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();
        $response = redirect()->route('channel.index');
        return $request->boolean('remember_id')
            ? $response->withCookie(cookie(self::SAVED_CHANNEL_LOGIN_ID, $data['login_id'], 43200, '/channel', null, $request->isSecure(), true, false, 'lax'))
            : $response->withoutCookie(self::SAVED_CHANNEL_LOGIN_ID, '/channel');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('channel.login');
    }

    // 상점 관리 (Sub01)
    public function shopList(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $query = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id);
        $params=$request->validate(['search_name'=>'nullable|string|max:255','search_keyword'=>'nullable|string|max:50',
            'status'=>'nullable|in:all,0,1','is_public'=>'nullable|array','is_public.*'=>'in:0,1','is_member_only'=>'nullable|array','is_member_only.*'=>'in:0,1']);

        // 검색 필터
        if ($request->filled('search_name')) {
            $query->where('channel_name', 'like', '%' . $request->search_name . '%');
        }
        if ($request->filled('search_keyword')) {
            $pattern='%'.$request->search_keyword.'%';
            $query->whereRaw("CASE WHEN JSON_VALID(keywords) THEN JSON_SEARCH(keywords, 'one', ?) IS NOT NULL ELSE keywords LIKE ? END",[$pattern,$pattern]);
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

        $shops = $query->withCount(['shopChannelProducts'=>fn($q)=>$q->where('approval_status','approved')->whereNotNull('listed_at')])->orderBy('created_at', 'desc')->orderByDesc('id')->paginate(10);

        return view('channel.sub01.shop_list', [
            'dep1_id' => '01',
            'shops' => $shops,
            'params' => $params
        ]);
    }

    public function shopRegister(\App\Services\ShopChannelConfiguration $configuration)
    {
        return view('channel.sub01.shop_register', $configuration->form(Auth::guard('admin')->user()));
    }

    public function previewPrivateAccess(Request $request, \App\Services\PrivateAccessImport $import)
    {
        $request->validate(['private_access_file'=>'required|file|max:5120|mimes:xlsx,xls,csv,txt']);
        return response()->json(['rows'=>$import->file($request->file('private_access_file'))]);
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

    public function shopRegisterSubmit(Request $request, \App\Services\ShopChannelConfiguration $configuration)
    {
        $configuration->save($request, Auth::guard('admin')->user());
        return redirect()->route('channel.shop_list')->with('success_message','Shop 채널을 등록했습니다.');
    }

    public function shopInfo(Request $request)
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shop = \App\Models\ShopChannel::with('privateAccesses')->where('vendor_id', $admin->vendor_id)
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

    public function copyShop(int $id)
    {
        $admin = Auth::guard('admin')->user();
        $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)->findOrFail($id);
        $copy = $shop->replicate([
            'channel_code', 'closure_status', 'closure_requested_at', 'closure_approved_at',
            'closure_rejected_at', 'closure_reviewed_by', 'closure_memo',
            'admin_name', 'admin_login_id', 'admin_password',
        ]);
        $copy->channel_code = 'shop-'.\Illuminate\Support\Str::uuid();
        $copy->channel_name = \Illuminate\Support\Str::limit($shop->channel_name, 90, '').' (복사)';
        $copy->status = 0;
        $copy->use_admin = 0;
        $copy->save();

        return redirect()->route('channel.info_update', $copy->id)
            ->with('success_message', 'Shop 채널 정보가 복사되었습니다. 설정을 확인한 후 운영 상태로 변경해 주세요.');
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
            ->where('status', 1)->where('approval_status','approved')->whereNotNull('listed_at')
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
        ] + app(\App\Services\ShopCatalog::class)->data($request,$shop));
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
            ->where('status', 0)->where('approval_status','approved')->whereNotNull('listed_at')
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
        ] + app(\App\Services\ShopCatalog::class)->data($request,$shop));
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

        $shop = \App\Models\ShopChannel::where('vendor_id', $admin->vendor_id)
            ->when($request->filled('shop_id'), fn($query) => $query->whereKey($request->input('shop_id')))
            ->first();
        $shopId = $shop?->id;
        if (!$shop) {
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
            'shopId' => $shopId,'shop'=>$shop,
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
        $query = \App\Models\Product::where('is_public', 'Yes')->where('vendor_id','!=',$admin->vendor_id)
            ->whereHas('shopChannelProducts',fn($q)=>$q->where('product_type','public')->where('approval_status','approved')->whereNotNull('listed_at')->whereHas('shopChannel',fn($c)=>$c->where('vendor_id',$admin->vendor_id)))
            ->with(['category','images','vendor.vendorbusinessdetails','shopChannelProducts'=>fn($q)=>$q->where('product_type','public')->where('approval_status','approved')->whereNotNull('listed_at')->whereHas('shopChannel',fn($c)=>$c->where('vendor_id',$admin->vendor_id))->with('shopChannel')]);
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
        $query = \App\Models\Product::where('is_partial', 'Yes')->where('vendor_id','!=',$admin->vendor_id)
            ->whereHas('shopChannelProducts',fn($q)=>$q->where('product_type','partial')->where('approval_status','approved')->whereNotNull('listed_at')->whereHas('shopChannel',fn($c)=>$c->where('vendor_id',$admin->vendor_id)))
            ->with(['category','images','vendor.vendorbusinessdetails','shopChannelProducts'=>fn($q)=>$q->where('product_type','partial')->where('approval_status','approved')->whereNotNull('listed_at')->whereHas('shopChannel',fn($c)=>$c->where('vendor_id',$admin->vendor_id))->with('shopChannel')]);
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
            $query->where('approval_status',['0'=>'pending','1'=>'approved','2'=>'rejected'][(string)$filters['request_status']]);
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
                        $businessQuery->where('shop_name', 'like', '%' . $filters['seller'] . '%');
                    });
            });
        }

        if ($filters['price_min'] !== '' && is_numeric($filters['price_min'])) {
            $query->whereRaw('CASE WHEN price_constraint_enabled = 1 AND price_constraint_type = ? THEN price_fixed WHEN price_constraint_enabled = 1 AND price_constraint_type = ? THEN price_max ELSE product_price END >= ?', ['fixed', 'range', (float) $filters['price_min']]);
        }
        if ($filters['price_max'] !== '' && is_numeric($filters['price_max'])) {
            $query->whereRaw('CASE WHEN price_constraint_enabled = 1 AND price_constraint_type = ? THEN price_fixed WHEN price_constraint_enabled = 1 AND price_constraint_type = ? THEN price_min ELSE product_price END <= ?', ['fixed', 'range', (float) $filters['price_max']]);
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
        $shop=\App\Models\ShopChannel::where('vendor_id',Auth::guard('admin')->user()->vendor_id)
            ->when($request->filled('shop_id'),fn($q)=>$q->whereKey($request->input('shop_id')))->first();
        if(!$shop)return redirect()->route('channel.shop_list');
        return view('channel.sub01.community_register',['dep1_id'=>'01','shopId'=>$shop->id,'shop'=>$shop]);
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
            'shopId' => $shop->id,'shop'=>$shop,
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
            'shopId' => $shop->id,'shop'=>$shop
        ]);
    }

    public function communityRegisterSubmit(Request $request, \App\Services\ShopCommunity $community)
    {
        $shop=\App\Models\ShopChannel::where('vendor_id',Auth::guard('admin')->user()->vendor_id)->findOrFail($request->input('shop_id'));
        $notice=$community->save($request,$shop);
        return redirect()->route('channel.community.view',$notice->id)->with('success_message','게시글을 등록했습니다.');
    }
    public function communityUpdateSubmit(Request $request,$id, \App\Services\ShopCommunity $community)
    {
        $notice=\App\Models\ShopChannelNotice::whereHas('shopChannel',fn($q)=>$q->where('vendor_id',Auth::guard('admin')->user()->vendor_id))->findOrFail($id);
        $community->save($request,$notice->shopChannel,$notice);
        return redirect()->route('channel.community.view',$id)->with('success_message','게시글을 수정했습니다.');
    }
    public function communityAttachment($id, \App\Services\ShopCommunity $community)
    {
        $notice=\App\Models\ShopChannelNotice::whereHas('shopChannel',fn($q)=>$q->where('vendor_id',Auth::guard('admin')->user()->vendor_id))->findOrFail($id);
        $path=$notice->attachment?$community->path($notice->attachment):null;abort_unless($path,404);
        return response()->download($path,$notice->attachment_name?:basename($path),['X-Content-Type-Options'=>'nosniff','Cache-Control'=>'no-store, private']);
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
        if($filename)app(\App\Services\ShopCommunity::class)->delete($filename);
    }

    public function infoUpdate($id, \App\Services\ShopChannelConfiguration $configuration)
    {
        $admin=Auth::guard('admin')->user();
        $shop=\App\Models\ShopChannel::where('vendor_id',$admin->vendor_id)->findOrFail($id);
        return view('channel.sub01.info_update',$configuration->form($admin,$shop));
    }

    public function infoUpdateSubmit(Request $request,$id, \App\Services\ShopChannelConfiguration $configuration)
    {
        $admin=Auth::guard('admin')->user();
        $shop=\App\Models\ShopChannel::where('vendor_id',$admin->vendor_id)->findOrFail($id);
        $configuration->save($request,$admin,$shop);
        return redirect()->route('channel.shop_info',['id'=>$shop->id])->with('success_message','채널 정보를 수정했습니다.');
    }

    // 상품 관리 (Sub02) 관련 메서드는 위에 이미 정의되어 있습니다.

    // 주문 관리 (Sub04)
    public function orderList(Request $request)
    {
        if (!$request->query->has('order_type')) $request->merge(['order_type' => 'all']);
        return $this->renderOrderList($request, 'list', []);
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
            OrderItemStatus::RETURN_RECEIVED,
            OrderItemStatus::RETURN_HOLD,
            OrderItemStatus::RETURNED,
        ]);
    }

    public function orderExchangeRequestList(Request $request)
    {
        return $this->renderOrderList($request, 'exchange', [
            OrderItemStatus::EXCHANGE_REQUESTED,
            OrderItemStatus::EXCHANGE_APPROVED,
            OrderItemStatus::EXCHANGE_HOLD_BEFORE,
            OrderItemStatus::EXCHANGE_RECEIVED,
            OrderItemStatus::EXCHANGE_HOLD_AFTER,
            OrderItemStatus::EXCHANGED,
        ]);
    }

    private function renderOrderList(Request $request, string $pageType, array $baseStatusKeys)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $filters = $this->orderListFilters($request);
        $filters['claim_type'] = in_array($pageType, ['cancel','return','exchange'], true) ? $pageType : null;
        $filters['claim_statuses'] = $baseStatusKeys;
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
            OrderItemStatus::AWAITING_PAYMENT,
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

        $claimMatch = [];
        if (in_array($searchType, ['all','order_no'], true)) preg_match('/^Me9-(Cancel|Return|Exchange|Change)-(\d+)$/i', trim((string)$request->input('keyword','')), $claimMatch);
        return [
            'claim_id' => isset($claimMatch[2]) ? (int)$claimMatch[2] : null,
            'claim_search_type' => isset($claimMatch[1]) ? (strtolower($claimMatch[1]) === 'change' ? 'exchange' : strtolower($claimMatch[1])) : null,
            'search_type' => $searchType,
            'keyword' => trim((string) $request->input('keyword', '')),
            'order_id' => max(0, $request->integer('order_id')),
            'joint_purchase_id' => max(0, $request->integer('joint_purchase_id')),
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
        $vendorProductIds = \App\Models\Product::where('vendor_id', $vendor_id)
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selectedStatusKeys = $filters['order_statuses'] ?? [];
        $effectiveStatusKeys = !empty($filters['claim_type']) ? [] : $baseStatusKeys;
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

            $this->applyOrderClaimFilter($q, $filters);
            $this->applyOrderTypeFilter($q, $filters['order_type'] ?? '');
            if (!empty($filters['joint_purchase_id'])) $q->where('joint_purchase_id', $filters['joint_purchase_id']);

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

        $detailFilter = function ($q) use ($vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds) {
            $q->where(function ($owner) use ($vendor_id, $vendorShopIds, $vendorShopProductIds, $vendorProductIds) {
                $owner->where('vendor_id', $vendor_id);
                if ($vendorShopIds) $owner->orWhereIn('shop_channel_id', $vendorShopIds);
                if ($vendorShopProductIds) $owner->orWhereIn('shop_channel_product_id', $vendorShopProductIds);
                if ($vendorProductIds) $owner->orWhereIn('product_id', $vendorProductIds);
            });
        };

        $query = \App\Models\Order::with([
            'orders_products' => function($q) use ($detailFilter) {
                $detailFilter($q);
                $q->with(['shopChannel', 'shopChannelProduct', 'product.attributes']);
            },
            'user',
            'claims',
            'statusLogs',
            'payments',
        ]);

        $query->whereHas('orders_products', $itemFilter);
        if (!empty($filters['order_id'])) $query->whereKey($filters['order_id']);

        $keyword = !empty($filters['claim_id']) ? '' : ($filters['keyword'] ?? '');
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
                        ->orWhere('buyer_name', 'like', '%' . $keyword . '%')
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
                    $this->applyOrderClaimFilter($itemQuery, $filters);
                    $this->applyOrderTypeFilter($itemQuery, $filters['order_type'] ?? '');
                    if (!empty($filters['joint_purchase_id'])) $itemQuery->where('joint_purchase_id', $filters['joint_purchase_id']);
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
                    ->orWhere('buyer_name', 'like', '%' . $buyer . '%')
                    ->orWhere('buyer_mobile', 'like', '%' . $buyer . '%')
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
        $orders->getCollection()->loadMissing('orders_products.exchangeReplacement');

        $matchingIds = \App\Models\OrdersProduct::whereIn('order_id', $orders->getCollection()->pluck('id'))
            ->where($itemFilter)->pluck('id')->flip();

        $orders->getCollection()->transform(function($order) use ($matchingIds) {
            $order->order_no = 'Me9-' . str_pad($order->id, 8, '0', STR_PAD_LEFT); 
            $order->user_name = $order->buyer_name ?: $order->name;
            $order->setRelation('claims', $order->claims->whereIn('order_product_id', $order->orders_products->pluck('id'))->values());
            $order->claims_data = $order->claims;
            $order->events = $order->statusLogs->whereIn('order_item_id', $order->orders_products->pluck('id'))->map(fn ($log) => ['order_product_id'=>$log->order_item_id,'created_at'=>$log->created_at?->format('Y-m-d H:i:s'),'status_label'=>$log->order_status,'actor'=>$log->actor ?: '관리자'])->values();
            $order->payment_logs = $order->payments->map(fn ($payment) => $payment->only(['payment_id','amount','currency','payment_status','created_at']))->values();
            
            $vendorItems = $order->orders_products;
            $firstItem = $vendorItems->first();
            $order->shop_name = $firstItem?->shopChannel?->channel_name ?? 'Me9 Market';
            
            $order->items = $vendorItems->map(function($item) {
                $lineTotal = $item->line_total > 0
                    ? $item->line_total
                    : $item->product_price * $item->product_qty;
                $isJointPurchase = (bool) $item->joint_purchase_id;
                $payment = app(\App\Services\SettlementCalculator::class)->paymentAmounts($item);
                $financial = app(\App\Services\SettlementCalculator::class)->orderFinancialAmounts($item, (int) Auth::guard('admin')->user()->vendor_id);

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'is_exchange_replacement' => (bool) $item->is_exchange_replacement,
                    'recipient_name' => $item->recipient_name,
                    'recipient_mobile' => $item->recipient_mobile,
                    'recipient_address' => $item->recipient_address,
                    'product_url' => $item->shop_channel_product_id ? route('shop.product_details', $item->shop_channel_product_id).'?shop_id='.$item->shop_channel_id : null,
                    'shop_name' => $item->shopChannel?->channel_name ?? 'Me9 Market',
                    'can_manage' => (int) $item->vendor_id === (int) Auth::guard('admin')->user()->vendor_id,
                    'channel_financial' => app(\App\Services\SettlementCalculator::class)->channelReportAmounts($item, (int) Auth::guard('admin')->user()->vendor_id),
                    'exchange_shipping_fee' => (int) $item->exchange_shipping_fee,
                    'shipped_at' => $item->shipped_at?->format('Y-m-d H:i:s'),
                    'delivered_at' => $item->delivered_at?->format('Y-m-d H:i:s'),
                    'confirmed_at' => $item->confirmed_at?->format('Y-m-d H:i:s'),
                    'status' => $item->normalized_status,
                    'replacement' => $item->exchangeReplacement ? [
                        'id' => $item->exchangeReplacement->id,
                        'status_label' => $item->exchangeReplacement->status_label,
                        'courier_name' => $item->exchangeReplacement->courier_name,
                        'tracking_number' => $item->exchangeReplacement->tracking_number,
                        'recipient_name'=>$item->exchangeReplacement->recipient_name,
                        'recipient_address'=>$item->exchangeReplacement->recipient_address,
                    ] : null,
                    'status_label' => $item->status_label,
                    'order_type' => $isJointPurchase ? 'joint' : 'normal',
                    'order_type_label' => $isJointPurchase ? '공동구매' : '일반',
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'option_name' => trim(($item->product_color ?: '-') . '/' . ($item->product_size ?: '-'), '/'),
                    'exchange_options' => ($item->product?->attributes ?? collect())->filter(fn ($attribute) => (int) $attribute->status === 1 && abs((float) $attribute->price_delta - (float) $item->option_price_adjustment) < 0.001)->pluck('size')->values()->all(),
                    'qty' => $item->product_qty,
                    'price' => $item->selling_price ?: $item->product_price,
                    'line_total' => $lineTotal,
                    'shipping_amount' => $payment['shipping'],
                    'discount_amount' => $payment['coupon'],
                    'paid_line_total' => $payment['paid_line_total'],
                    'used_point_amount' => $payment['points'],
                    'paid_cash_amount' => $payment['cash'],
                    'estimated_payout' => $financial['payout'],
                    'sales_profit' => $financial['sales_profit'],
                    'reward_points' => $item->is_exchange_replacement ? 0 : $payment['reward'],
                    'actual_refund_cash_amount' => (float) $item->refund_cash_amount,
                    'refund_cash_amount' => $item->financial_reversed_at ? (float) $item->refund_cash_amount : max(0, $payment['cash'] - (float) $item->return_shipping_fee),
                    'refund_status' => $item->refund_status,
                    'refund_status_label' => $item->refund_status_label,
                    'return_shipping_fee' => (float) $item->return_shipping_fee,
                    'original_line_total' => $item->original_line_total,
                    'repriced_line_total' => $item->repriced_line_total,
                    'reprice_adjustment_amount' => $item->reprice_adjustment_amount,
                    'reprice_status' => $item->reprice_status,
                    'courier_name' => $item->courier_name,
                    'tracking_number' => $item->tracking_number,
                    'product_type' => $item->shopChannelProduct?->product_type === 'public' ? '공유' : ($item->shopChannelProduct?->product_type === 'partial' ? '제휴' : '자사'),
                ];
            });

            $order->list_items = $order->items->filter(fn ($item) => $matchingIds->has($item['id']))->values();

            $totalProductPrice = $vendorItems->sum(function($item) {
                return $item->line_total > 0 ? $item->line_total : $item->product_price * $item->product_qty;
            });

            $order->total_product_price = $totalProductPrice;
            $order->total_sale_price = $totalProductPrice; 
            $order->total_profit = $order->items->sum('estimated_payout');
            $order->total_selling_profit = $order->items->sum('sales_profit');
            $order->delivery_fee = $order->items->sum('shipping_amount');
            $order->used_point = $order->items->sum('used_point_amount');
            $order->total_payment_price = $order->items->sum('paid_cash_amount');
            $order->earned_point = $order->items->sum('reward_points');
            $order->status = $order->items->first()['status_label'] ?? OrderItemStatus::label(OrderItemStatus::PAID);
            $order->order_type_label = $order->items->pluck('order_type_label')->unique()->implode(', ');
            
            return $order;
        });

        return $orders;
    }

    private function applyOrderClaimFilter($query, array $filters): void
    {
        if (!empty($filters['claim_type'])) {
            $values=$this->orderStatusQueryValues($filters['claim_statuses'] ?? []);
            $query->where(function ($claimQuery) use ($filters,$values) {
                $claimQuery->whereHas('claims', fn($claim)=>$claim->where('type',$filters['claim_type']))
                    ->orWhereIn('status_code',$values)->orWhereIn('item_status',$values);
            });
        }
        if (!empty($filters['claim_id'])) $query->whereHas('claims',fn($claim)=>$claim->whereKey($filters['claim_id'])->where('type',$filters['claim_search_type']));
    }

    private function applyOrderTypeFilter($query, string $orderType): void
    {
        if ($orderType === 'joint') {
            $query->whereNotNull('joint_purchase_id');
        } elseif ($orderType === 'normal') {
            $query->whereNull('joint_purchase_id');
        }
    }

    private function orderStatusOptions(array $statusKeys = []): array
    {
        $options = [
            OrderItemStatus::AWAITING_PAYMENT => '입금대기',
            OrderItemStatus::PAID => '결제완료',
            OrderItemStatus::READY_TO_SHIP => '배송대기',
            OrderItemStatus::SHIPPING => '배송중',
            OrderItemStatus::DELIVERED => '배송완료',
            OrderItemStatus::CONFIRMED => '구매확정',
            OrderItemStatus::CANCEL_REQUESTED => '취소요청',
            OrderItemStatus::CANCELLED => '취소완료',
            OrderItemStatus::RETURN_REQUESTED => '반품요청',
            OrderItemStatus::RETURN_RECEIVED => '반품회수완료',
            OrderItemStatus::RETURN_HOLD => '반품보류',
            OrderItemStatus::RETURNED => '반품완료',
            OrderItemStatus::EXCHANGE_REQUESTED => '교환요청',
            OrderItemStatus::EXCHANGE_APPROVED => '교환승인',
            OrderItemStatus::EXCHANGE_HOLD_BEFORE => '교환회수전보류',
            OrderItemStatus::EXCHANGE_RECEIVED => '교환회수완료',
            OrderItemStatus::EXCHANGE_HOLD_AFTER => '교환회수후보류',
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
            OrderItemStatus::RETURN_RECEIVED => [],
            OrderItemStatus::RETURN_HOLD => [],
            OrderItemStatus::RETURNED => ['Returned'],
            OrderItemStatus::EXCHANGE_REQUESTED => ['Exchange Requested'],
            OrderItemStatus::EXCHANGE_APPROVED => [],
            OrderItemStatus::EXCHANGE_HOLD_BEFORE => [],
            OrderItemStatus::EXCHANGE_RECEIVED => [],
            OrderItemStatus::EXCHANGE_HOLD_AFTER => [],
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

        $query = Contact::with(['order', 'orderProduct', 'product', 'shopChannel'])
            ->where('vendor_id', $vendorId)
            ->where(function ($query) {
                $query->whereNotNull('order_product_id')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('shop_channel_id')->whereNotNull('product_id');
                    });
            });

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

        $inquiry = Contact::with(['order', 'orderProduct', 'product', 'shopChannel'])
            ->where('vendor_id', $vendorId)
            ->where(function ($query) {
                $query->whereNotNull('order_product_id')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('shop_channel_id')->whereNotNull('product_id');
                    });
            })
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
            ->where(function ($query) {
                $query->whereNotNull('order_product_id')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('shop_channel_id')->whereNotNull('product_id');
                    });
            })
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
    public function infoManagement(\App\Services\ChannelInformation $information)
    {
        $admin = Auth::guard('admin')->user();
        return view('channel.sub00.info_management', $information->data($admin) + ['dep1_id'=>'00']);
    }

    public function updateInfo(Request $request, \App\Services\ChannelInformation $information)
    {
        $result = $information->update($request, Auth::guard('admin')->user());
        return redirect()->route('channel.info.management')->with('success_message', $result === 'requested'
            ? '판매인증 요청이 접수되었습니다. 관리자 심사 후 승인됩니다.' : '회원사 정보를 저장했습니다.');
    }

    public function informationDocument(string $kind, string $mode, \App\Services\MemberRegistration $registration)
    {
        return $registration->document((int) Auth::guard('admin')->user()->vendor_id, $kind, $mode === 'download');
    }

    public function updatePassword(Request $request)
    {
        if ($request->ajax()) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'current_password' => ['required', 'string'],
                'new_password' => ['required', \Illuminate\Validation\Rules\Password::min(12)->mixedCase()->letters()->numbers()],
                'confirm_password' => ['required', 'same:new_password'],
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
            }

            $data = $validator->validated();
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

            // 2. Check current password
            if (!\Illuminate\Support\Facades\Hash::check($data['current_password'], $admin->password)) {
                return response()->json(['status' => 'error', 'message' => '현재 비밀번호가 일치하지 않습니다.']);
            }

            \App\Models\Admin::where('id', $admin->id)->update([
                'password' => \Illuminate\Support\Facades\Hash::make($data['new_password'])
            ]);

            return response()->json(['status' => 'success', 'message' => '비밀번호가 성공적으로 변경되었습니다.']);
        }
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

        return redirect()->back()->with('success_message', 'Shop 채널 운영중지 요청이 접수되었습니다. 최고관리자 승인 후 고객의 Me9 포인트 전환이 가능합니다.');
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
                'password' => ['required', \Illuminate\Validation\Rules\Password::min(12)->mixedCase()->letters()->numbers()],
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
