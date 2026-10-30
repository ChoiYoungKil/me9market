<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterMemberRequest;
use App\Models\Admin;
use App\Models\Cart;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBankDetail;
use App\Models\VendorsBusinessDetail;
use App\Models\VisitedChannel;
use App\Models\Wishlist;
use App\Services\ChannelPointService;
use App\Services\ReturnAddressResolver;
use App\Services\ShopChannelSmsService;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private const SAVED_LOGIN_ID_COOKIE = 'me9_saved_login_id';

    // 새로운 로그인 액션
    public function loginUser(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'login_id' => 'required|string|max:255',
                'password' => 'required|string|max:4096',
                'remember_id' => 'nullable|boolean',
            ];

            $validator = Validator::make($data, $rules, [
                'login_id.required' => '아이디 또는 이메일을 입력해 주세요.',
                'login_id.string' => '아이디 또는 이메일을 올바르게 입력해 주세요.',
                'login_id.max' => '아이디 또는 이메일은 255자 이내로 입력해 주세요.',
                'password.required' => '비밀번호를 입력해 주세요.',
                'password.string' => '비밀번호를 올바르게 입력해 주세요.',
                'password.max' => '비밀번호를 확인해 주세요.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput($request->only('login_id', 'remember_id', 'remember'));
            }

            $saveLoginId = $request->boolean('remember_id') || $request->boolean('remember');
            if (! $saveLoginId) {
                \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget(self::SAVED_LOGIN_ID_COOKIE));
            }

            $credentials = [
                ['username' => $data['login_id'], 'password' => $data['password']],
                ['email' => $data['login_id'], 'password' => $data['password']],
            ];

            $authenticated = false;
            $temporaryExpired = false;
            foreach ($credentials as $credential) {
                if (Auth::attemptWhen($credential, function ($user) use (&$temporaryExpired) {
                    $temporaryExpired = $user->temporaryPasswordIsExpired();
                    return ! $temporaryExpired;
                }, false)) {
                    $authenticated = true;
                    break;
                }
            }

            if ($authenticated) {
                $request->session()->regenerate();
                if ((string) Auth::user()->status === '0') {
                    $withdrawn = DB::table('member_withdrawals')->where('user_id', Auth::id())->exists();
                    Auth::logout();

                    return redirect()->back()->with('error_message', $withdrawn ? '탈퇴 처리된 계정입니다. 로그인할 수 없습니다.' : '계정이 아직 활성화되지 않았습니다. 관리자에게 문의하세요.')
                        ->withInput($request->only('login_id', 'remember_id', 'remember'));
                }

                // 장바구니 업데이트
                if (! empty(Session::get('session_id'))) {
                    $user_id = Auth::user()->id;
                    $session_id = Session::get('session_id');
                    Cart::where('session_id', $session_id)->update(['user_id' => $user_id]);
                }

                if ($saveLoginId) {
                    \Illuminate\Support\Facades\Cookie::queue(cookie(self::SAVED_LOGIN_ID_COOKIE, $data['login_id'], 60 * 24 * 30,
                        '/', null, $request->isSecure(), true, false, 'lax'));
                }

                $response = redirect()->intended('/');
                if (Auth::user()->temporary_password_expires_at) {
                    $response->with('success_message', '임시비밀번호로 로그인했습니다. 회원정보에서 새 비밀번호로 변경해 주세요.');
                }
                return $response;
            } else {
                return redirect()->back()->with('error_message', $temporaryExpired ? '임시비밀번호의 유효시간이 지났습니다. 비밀번호 찾기에서 다시 발급해 주세요.' : '아이디(이메일) 또는 비밀번호가 일치하지 않습니다.')
                    ->withInput($request->only('login_id', 'remember_id', 'remember'));
            }
        }
    }

    // 새로운 회원가입 액션
    public function registerUser(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            // 폼 필드 결합
            if (isset($data['email_prefix']) && isset($data['email_suffix'])) {
                $data['email'] = $data['email_prefix'].'@'.$data['email_suffix'];
            }
            if (isset($data['mobile']) && is_array($data['mobile'])) {
                $data['mobile_str'] = implode('-', $data['mobile']);
            }

            $rules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'mobile_str' => 'required',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // 사용자 생성
            $user = new User;
            $user->name = $data['name'];
            $user->gender = $data['gender'];
            $user->email = $data['email'];
            $user->mobile = $data['mobile_str'];
            $user->password = bcrypt($data['password']);
            $user->status = 1; // 현재는 자동 활성화, 이메일 인증이 필요한 경우 0으로 설정
            $user->save();

            // 자동 로그인
            Auth::login($user);

            return redirect('/');
        }
    }

    // 사용자 로그인/회원가입 페이지 렌더링 (front/users/login_register.blade.php)
    public function loginRegister()
    {
        return view('front.users.login_register');
    }

    public function registerMember(Request $request)
    {
        return view('front.member.join', [
            'type' => 'general',
            'socialData' => [],
            'dep1_id' => '05',
            'dep1_tit' => '회원가입',
        ]);
    }

    // 커스텀 FormRequest 사용
    public function registerMemberSubmit(RegisterMemberRequest $request)
    {
        if ($request->isMethod('post')) {
            // Request 클래스에 의해 데이터가 이미 유효성 검사 및 정제됨
            $data = $request->validated();

            // 참고: $request->validated()는 rules 배열에 있는 필드만 포함함
            // prepareForValidation에서 필드를 결합했으므로 input()을 통해 접근하거나
            // Ensure they are in the data array if needed, but for 'email' it is in rules.
            // email의 경우 rules에 있음

            // 사용자 생성
            $user = new User;
            $user->email = $request->input('email');
            $user->username = $data['username'];
            $user->name = $data['username'];
            $user->password = Hash::make($data['password']);

            $user->status = 1;
            $user->fill(app(\App\Services\RegistrationConsent::class)->capture($request, ['agree_terms', 'agree_privacy', 'agree_third_party']));
            $user->save();

            // 즉시 자동 로그인 처리
            $request->session()->forget(['social_signup', 'social_oauth']);
            Auth::login($user);
            $request->session()->regenerate();

            // Axios를 위한 JSON 응답 반환 (로그인 화면이 아닌 1단계 기본정보 입력창으로 이동)
            return response()->json([
                'status' => 'success',
                'message' => '회원가입 약관 동의가 완료되었습니다. 기본 정보 입력 단계로 이동합니다.',
                'redirect_url' => route('front.member.register.step1'),
            ]);
        }
    }

    public function checkIdAvailability(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            if (empty($data['username'])) {
                return response()->json(['status' => 'error', 'message' => '아이디를 입력해주세요.']);
            }

            // 정규식 체크
            if (! preg_match('/^[a-zA-Z0-9]+$/', $data['username'])) {
                return response()->json(['status' => 'error', 'message' => '아이디는 영문 대소문자와 숫자만 사용 가능합니다.']);
            }

            $count = User::where('username', $data['username'])->count();
            if ($count > 0) {
                return response()->json(['status' => 'unavailable', 'message' => '이미 사용중인 아이디입니다.']);
            } else {
                return response()->json(['status' => 'available', 'message' => '사용 가능한 아이디입니다.']);
            }
        }
    }

    public function updateMemberStep1(Request $request)
    {
        abort_unless(Auth::check(), 401);
        app(\App\Services\MemberRegistration::class)->saveProfile($request, Auth::user());
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => '기본정보가 저장되었습니다.']);
        }
        return back()->with('success_message', '기본정보가 저장되었습니다.');
    }

    public function registerStep1()
    {
        return $this->registrationStep(1);
    }

    public function registerStep2()
    {
        return $this->registrationStep(2);
    }

    public function registerStep3()
    {
        return $this->registrationStep(3);
    }

    private function registrationStep(int $step)
    {
        if (! Auth::check()) {
            return redirect()->route('front.member.login')->with('error_message', '로그인이 필요합니다.');
        }
        return view('front.member.register.step'.$step, app(\App\Services\MemberRegistration::class)->details(Auth::user()));
    }

    public function updateMemberStep2(Request $request)
    {
        abort_unless(Auth::check(), 401);
        app(\App\Services\MemberRegistration::class)->saveCompany($request, Auth::user());
        return response()->json(['status' => 'success', 'message' => '회원사 정보가 저장되었습니다.']);
    }

    public function updateMemberStep3(Request $request)
    {
        abort_unless(Auth::check(), 401);
        $requested = app(\App\Services\MemberRegistration::class)->saveSeller($request, Auth::user());
        return response()->json([
            'status' => 'success',
            'message' => $requested ? '판매 권한 인증 요청이 접수되었습니다. 심사 후 권한이 부여됩니다.' : '판매자 정보가 저장되었습니다. 인증 요청은 아직 접수되지 않았습니다.',
            'requested' => $requested,
            'redirect_url' => $requested ? route('front.member.register.complete') : route('front.member.register.step3'),
        ]);
    }

    public function registrationComplete()
    {
        $user = Auth::user();
        if (! $user->profile_completed_at) {
            return redirect()->route('front.member.register.step1');
        }
        return view('front.member.register.complete', app(\App\Services\MemberRegistration::class)->details($user));
    }

    public function registrationDocument(string $kind)
    {
        abort_unless(Auth::user()->vendor_id, 404);
        return app(\App\Services\MemberRegistration::class)->document(Auth::user()->vendor_id, $kind);
    }

    // 새로운 로그인 페이지
    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('mypage.dashboard');
        }
        $savedLoginId = $request->cookie(self::SAVED_LOGIN_ID_COOKIE, '');
        $savedLoginId = is_string($savedLoginId) ? $savedLoginId : '';
        $hasOldLoginId = $request->session()->hasOldInput('login_id');
        $loginId = $hasOldLoginId ? old('login_id') : $savedLoginId;
        $loginId = is_string($loginId) ? $loginId : '';
        $rememberId = $hasOldLoginId ? (bool) old('remember_id', old('remember', false)) : $savedLoginId !== '';

        return view('front.member.login', compact('loginId', 'rememberId'));
    }

    // 새로운 회원가입 페이지
    public function register()
    {
        return view('front.member.register.step1');
    }

    // 마이페이지 대시보드
    public function dashboard()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('front.member.login');
        }

        // Format mobile number with dashes if missing
        $mobileWithDashes = $user->mobile;
        if (strlen($user->mobile) === 11 && ! str_contains($user->mobile, '-')) {
            $mobileWithDashes = substr($user->mobile, 0, 3).'-'.substr($user->mobile, 3, 4).'-'.substr($user->mobile, 7);
        }

        // Fetch user's inquiries from contacts table (matching email or phone)
        $inquiries = Contact::where('user_id', $user->id)
            ->orWhere(fn ($query) => $query->whereNull('user_id')->where('email', $user->email))
            ->orderBy('id', 'desc')
            ->get();

        // Calculate counts for dashboard stats
        $ordersCount = DB::table('orders_products')->where('user_id', $user->id)->count();
        $confirmedCount = DB::table('orders_products')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status_code', OrderItemStatus::CONFIRMED)
                    ->orWhereIn('item_status', ['Confirmed', '구매확정']);
            })
            ->count();
        $cancelCount = DB::table('orders_products')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status_code', OrderItemStatus::CANCEL_REQUESTED)
                    ->orWhereIn('item_status', ['Cancel Requested', '취소요청']);
            })
            ->count();
        $returnCount = DB::table('orders_products')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status_code', OrderItemStatus::RETURN_REQUESTED)
                    ->orWhereIn('item_status', ['Return Requested', '반품요청']);
            })
            ->count();

        $recentOrders=Order::with('orders_products')->where('user_id',$user->id)->latest()->limit(5)->get();
        $visited=VisitedChannel::with(['shopChannel','vendor.vendorbusinessdetails'])->where('user_id',$user->id)->latest('updated_at')->limit(5)->get();
        $statusCounts=OrdersProduct::where('user_id',$user->id)->get()->countBy(fn ($item)=>$item->normalized_status);
        $portal=app(\App\Services\MemberPortal::class);
        $shortcuts=$portal->shortcuts(); $selectedShortcuts=$user->mypage_shortcuts ?? ['profile','delivery','orders'];
        $subAccounts=$portal->subAccounts($user);
        return view('front.mypage.index', compact('user', 'inquiries', 'ordersCount', 'confirmedCount', 'cancelCount', 'returnCount',
            'recentOrders','visited','statusCounts','shortcuts','selectedShortcuts','subAccounts'));
    }

    public function saveMypageShortcuts(Request $request)
    {
        $choices=app(\App\Services\MemberPortal::class)->shortcuts();
        $data=$request->validate(['shortcuts'=>'nullable|array|max:7','shortcuts.*'=>['string',\Illuminate\Validation\Rule::in(array_keys($choices))]]);
        $user=Auth::user();$user->mypage_shortcuts=array_values(array_unique($data['shortcuts']??[]));$user->save();
        return redirect()->route('mypage.dashboard')->with('success_message','바로가기를 저장했습니다.');
    }

    public function openMemberChannel(Request $request)
    {
        if ($request->filled('sub_account_id')) {
            $sub=app(\App\Services\MemberPortal::class)->subAccounts(Auth::user())->firstWhere('id',(int)$request->input('sub_account_id'));
            abort_unless($sub,403);$admin=$sub->admin;
        } else {
            $admin=app(\App\Services\MemberPortal::class)->owner(Auth::user());
        }
        Auth::guard('admin')->login($admin);$request->session()->regenerate();
        return redirect()->route('channel.index');
    }

    public function orderView(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('front.member.login');
        }

        $id = $request->get('id');
        if (! $id) {
            $order = Order::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        } else {
            $order = Order::where('user_id', $user->id)->find($id);
        }

        if (! $order) {
            return redirect()->route('mypage.order.list')->with('error_message', '주문 정보를 찾을 수 없습니다.');
        }

        // Eager load order products and claims
        $order->load(['orders_products.product.category', 'orders_products.shopChannel', 'orders_products.vendor.vendorbusinessdetails', 'claims']);
        $payment = app(\App\Services\OrderInvoiceService::class)->data($order);

        return view('front.mypage.order.view', compact('user', 'order') + ['paymentTotals' => $payment['totals']]);
    }

    public function orderClaimSubmit(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => 'required|integer',
            'type' => 'required|in:cancel,return,exchange,confirm',
            'reason' => 'required_unless:type,confirm|nullable|string|max:150',
            'detail_reason' => 'required_if:reason,other|nullable|string|max:2000',
            'recovery_method' => 'required_if:type,return,exchange|nullable|in:자동회수,수동회수,automatic,manual',
            'customer_courier_name' => 'nullable|string|max:100',
            'customer_tracking_number' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|between:0.5,5|multiple_of:0.5|required_with:review',
            'review' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
        }

        return DB::transaction(function () use ($request, $data, $user) {
        $item = OrdersProduct::where('id', $request->order_item_id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if (! $item) {
            return response()->json(['success' => false, 'message' => '주문 상품 정보를 찾을 수 없습니다.'], 404);
        }

        // Find the associated order
        $order = Order::find($item->order_id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => '주문 정보를 찾을 수 없습니다.'], 404);
        }

        if (! OrderItemStatus::customerActionAllowed($data['type'], $item->normalized_status)) {
            return response()->json([
                'success' => false,
                'message' => '현재 주문 상태에서는 요청한 처리를 진행할 수 없습니다.',
            ], 422);
        }

        if ($request->type === 'confirm') {
            $item->setStatus(OrderItemStatus::CONFIRMED);
            $item->confirmed_at = now();
            $item->save();
            app(\App\Services\OrderFulfillmentService::class)->confirmPoints($item);
            $item->loadMissing('shopChannel');
            if ($item->shopChannel) {
                DB::afterCommit(fn () => app(ShopChannelSmsService::class)->send(
                    $item->shopChannel,
                    $order,
                    $item,
                    ShopChannelSmsService::TYPE_PURCHASE_CONFIRMED
                ));
            }

            // Save rating and review to ratings table
            if (! empty($data['rating'])) DB::table('ratings')->insert([
                'user_id' => $user->id,
                'product_id' => $item->product_id,
                'rating' => $data['rating'],
                'review' => $data['review'] ?? '',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '구매 확정이 완료되었습니다.',
            ]);
        } else {
            $claimType = $data['type'];
            $statusCodeMap = [
                'cancel' => OrderItemStatus::CANCEL_REQUESTED,
                'return' => OrderItemStatus::RETURN_REQUESTED,
                'exchange' => OrderItemStatus::EXCHANGE_REQUESTED,
            ];

            $item->setStatus($statusCodeMap[$claimType]);
            $item->save();

            $detailReason = $request->detail_reason ?? '';
            $pickupMethod = null;
            $returnAddress = null;
            if ($claimType === 'return' || $claimType === 'exchange') {
                $pickupMethod = in_array($data['recovery_method'], ['수동회수', 'manual'], true) ? 'manual' : 'automatic';
                $returnAddress = $pickupMethod === 'manual'
                    ? app(ReturnAddressResolver::class)->forOrderItem($item)
                    : null;
            }

            OrderClaim::updateOrCreate([
                'order_product_id' => $item->id,
                'type' => $claimType,
                'status' => 'requested',
            ], [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'vendor_id' => $item->vendor_id,
                'reason' => $request->reason,
                'detail_reason' => $detailReason,
                'pickup_method' => $pickupMethod,
                'return_address' => $returnAddress,
                'customer_courier_name' => $data['customer_courier_name'] ?? null,
                'customer_tracking_number' => $data['customer_tracking_number'] ?? null,
                'customer_shipped_at' => ! empty($data['customer_tracking_number']) ? now() : null,
                'status' => 'requested',
            ]);

            $label = $claimType === 'cancel' ? '취소' : ($claimType === 'return' ? '반품' : '교환');

            return response()->json([
                'success' => true,
                'message' => $label.' 신청이 완료되었습니다.',
            ]);
        }
        });
    }

    public function orderInquirySubmit(Request $request)
    {
        $data = $request->validate(['order_item_id'=>'required|integer','subject'=>'required|string|max:255','message'=>'required|string|max:3000']);
        $item = OrdersProduct::with('order')->where('user_id', Auth::id())
            ->whereHas('order', fn ($query) => $query->where('user_id', Auth::id()))->findOrFail($data['order_item_id']);
        Contact::create(['user_id'=>Auth::id(),'vendor_id'=>$item->vendor_id,'shop_channel_id'=>$item->shop_channel_id,
            'order_id'=>$item->order_id,'order_product_id'=>$item->id,'product_id'=>$item->product_id,
            'name'=>Auth::user()->name,'email'=>Auth::user()->email,'phone'=>Auth::user()->mobile,
            'inquiry_category'=>'product','subject'=>$data['subject'],'message'=>$data['message'],'type'=>'inquiry','status'=>'pending']);

        return back()->with('success_message','판매자 문의가 접수되었습니다.');
    }

    public function profileEdit()
    {
        $user = Auth::user();

        $vendor = null;
        $business = null; // businessDetail
        $bank = null; // bankDetail

        if ($user->vendor_id) {
            $vendor = Vendor::find($user->vendor_id);
            if ($vendor) {
                $business = VendorsBusinessDetail::where('vendor_id', $vendor->id)->first();
                $bank = VendorsBankDetail::where('vendor_id', $vendor->id)->first();
            }
        }

        return view('front.mypage.profile', compact('user', 'vendor', 'business', 'bank'));
    }

    public function profileUpdate(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'name' => 'required|string|max:100',
                'gender'=>'nullable|in:Male,Female',
                'birth_date'=>'nullable|date_format:Y-m-d|after:1900-01-01|before_or_equal:today',
                'mobile_1' => ['required','regex:/^0[0-9]{1,2}$/'],
                'mobile_2' => ['required','regex:/^[0-9]{3,4}$/'],
                'mobile_3' => ['required','regex:/^[0-9]{4}$/'],
                'email_1' => 'required|string|max:100',
                'email_2' => 'required|string|max:100',
                'zipcode' => ['required','regex:/^[0-9]{5}$/'],
                'address1' => 'required|string|max:255',
                'address2' => 'required|string|max:255',
                'marketing_opt_in'=>'nullable|boolean','notification_opt_in'=>'nullable|boolean',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // 연락처 결합
            $mobile = $data['mobile_1'].'-'.$data['mobile_2'].'-'.$data['mobile_3'];

            // 이메일 결합
            $email = $data['email_1'].'@'.$data['email_2'];
            validator(['email'=>$email],['email'=>'required|email|max:150'])->validate();

            // 중복 확인 (현재 사용자 제외)
            $user_id = Auth::user()->id;
            $emailCount = User::where('email', $email)->where('id', '!=', $user_id)->count();
            if ($emailCount > 0) {
                return redirect()->back()->with('error_message', '이 이메일은 이미 사용 중입니다.')->withInput();
            }

            $user = User::find($user_id);
            $user->name = $data['name'];
            $user->gender = $data['gender'] ?? $user->gender;
            $user->birth_date = $data['birth_date'] ?? $user->birth_date;
            foreach (['marketing_opt_in','notification_opt_in'] as $field) {
                if ($request->has($field)) { $user->$field=$request->boolean($field); }
            }
            $user->mobile = $mobile;
            $user->email = $email;

            $user->pincode = $data['zipcode'];
            $user->address = $data['address1'];
            $user->city = $data['address2']; // 상세 주소를 city 컬럼에 저장

            $user->save();

            return redirect()->back()->with('success_message', '회원정보가 수정되었습니다.');
        }
    }

    public function updateMemberPg(Request $request)
    {
        $vendor=Auth::user()->vendor_id ? Vendor::find(Auth::user()->vendor_id) : null;
        abort_unless($vendor && (int)$vendor->status===1,403);
        $data=$request->validate(['use_own_pg'=>'required|boolean','pg_provider'=>'nullable|in:inicis,kcp,toss',
            'pg_merchant_id'=>'nullable|string|max:255','pg_site_code'=>'nullable|string|max:255',
            'pg_client_key'=>'nullable|string|max:255','pg_secret_key'=>'nullable|string|max:255']);
        $enabled=$request->boolean('use_own_pg');
        if ($enabled) {
            $providerChanged=$request->filled('pg_provider') && $data['pg_provider'] !== $vendor->pg_provider;
            foreach (['pg_provider','pg_merchant_id','pg_site_code','pg_client_key','pg_secret_key'] as $field) {
                $value=$request->filled($field)?$data[$field]:($providerChanged ? null : $vendor->$field);
                if (! $value) throw ValidationException::withMessages([$field=>'자사 PG 연결 정보를 입력해 주세요.']);
                $vendor->$field=$value;
            }
        }
        DB::transaction(function () use ($vendor,$enabled) {
            $vendor->use_own_pg=$enabled;$vendor->save();
            foreach (ShopChannel::where('vendor_id',$vendor->id)->get() as $shop) {
                if (!$enabled) $shop->use_own_pg=false;
                elseif ($shop->use_own_pg) foreach (['pg_provider','pg_merchant_id','pg_site_code','pg_client_key','pg_secret_key'] as $field) $shop->$field=$vendor->$field;
                $shop->save();
            }
        });
        return redirect()->route('mypage.profile')->with('success_message','자사 PG 설정을 저장했습니다.');
    }

    public function delivery()
    {
        $defaultDelivery = DeliveryAddress::where('user_id', Auth::id())->where('is_default', 1)->first();
        $deliveries = DeliveryAddress::where('user_id', Auth::id())->where('is_default', 0)->paginate(10);

        return view('front.mypage.sub01.delivery_destination', compact('defaultDelivery', 'deliveries'));
    }

    public function addDelivery(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            // Validation
            $rules = [
                'name' => 'required|string|max:100',
                'zipcode' => ['required','regex:/^[0-9]{5}$/'],
                'address1' => 'required|string|max:255',
                'address2' => 'required|string|max:255',
                // 'mobile' => 'required', // 필요하다면 추가
                'is_default' => 'required|boolean',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($data) {
            $user_id = Auth::id();
            User::whereKey($user_id)->lockForUpdate()->firstOrFail();

            // 만약 '기본(1)'으로 설정한다면 기존 기본 배송지를 일반(0)으로 변경
            $is_default = (int) $data['is_default'];
            if ($is_default === 1) {
                DeliveryAddress::where('user_id', $user_id)->where('is_default', 1)->update(['is_default' => 0]);
            }

            // 배송지 추가
            $delivery = new DeliveryAddress;
            $delivery->user_id = $user_id;
            $delivery->name = $data['name'];
            $delivery->pincode = $data['zipcode'];
            $delivery->address = $data['address1']; // 기본 주소
            $delivery->city = $data['address2']; // 상세 주소
            $delivery->state = '';
            $delivery->country = 'South Korea';
            $delivery->mobile = $data['mobile'] ?? '';
            $delivery->is_default = $is_default;
            $delivery->status = 1;
            $delivery->save();

            });

            return redirect()->back()->with('success_message', '배송지가 추가되었습니다.');
        }
    }

    public function updateDefaultDelivery(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'name' => 'required|string|max:100',
                'zipcode' => ['required','regex:/^[0-9]{5}$/'],
                'address1' => 'required|string|max:255',
                'address2' => 'required|string|max:255',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($data) {
            $user_id = Auth::id();
            User::whereKey($user_id)->lockForUpdate()->firstOrFail();

            // 기존 기본 배송지 확인
            $delivery = DeliveryAddress::where('user_id', $user_id)->where('is_default', 1)->first();

            if (! $delivery) {
                // 없으면 새로 생성 (기본값으로 설정)
                $delivery = new DeliveryAddress;
                $delivery->user_id = $user_id;
                $delivery->is_default = 1;
                $delivery->status = 1;
                $delivery->mobile = '';
                $delivery->state = '';
                $delivery->country = 'South Korea';
            }

            $delivery->name = $data['name'];
            $delivery->pincode = $data['zipcode'];
            $delivery->address = $data['address1'];
            $delivery->city = $data['address2'];

            $delivery->save();

            });

            return redirect()->back()->with('success_message', '기본 배송지가 수정되었습니다.');
        }
    }

    public function updateDelivery(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'name' => 'required|string|max:100',
                'zipcode' => ['required','regex:/^[0-9]{5}$/'],
                'address1' => 'required|string|max:255',
                'address2' => 'required|string|max:255',
                'is_default' => 'required|boolean',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($data) {
            $user_id = Auth::id();
            User::whereKey($user_id)->lockForUpdate()->firstOrFail();
            $delivery = DeliveryAddress::where('id', $data['id'])->where('user_id', $user_id)->first();

            if (! $delivery) {
                abort(404, '배송지를 찾을 수 없습니다.');
            }

            $is_default = (int) $data['is_default'];

            // 추가 배송지를 기본으로 변경하는 경우
            if ($is_default === 1 && $delivery->is_default != 1) {
                DeliveryAddress::where('user_id', $user_id)->where('is_default', 1)->update(['is_default' => 0]);
            }

            $delivery->name = $data['name'];
            $delivery->pincode = $data['zipcode'];
            $delivery->address = $data['address1'];
            $delivery->city = $data['address2'];
            $delivery->is_default = $is_default;

            $delivery->save();

            });

            return redirect()->back()->with('success_message', '배송지가 수정되었습니다.');
        }
    }

    public function deleteDelivery(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            DeliveryAddress::where('id', $data['id'])->where('user_id', Auth::id())->delete();

            return redirect()->back()->with('success_message', '배송지가 삭제되었습니다.');
        }
    }

    public function withdraw(\App\Services\MemberWithdrawal $withdrawal)
    {
        $user = Auth::user();
        $sellingCount = $withdrawal->sharedSellingCount($user);
        $pointBalance = (int) PointTransaction::where('user_id', $user->id)->sum('points');

        return view('front.mypage.sub01.withdraw_confirm', compact('user', 'sellingCount', 'pointBalance'));
    }

    public function withdrawSubmit(Request $request, \App\Services\MemberWithdrawal $withdrawal)
    {
        $data = $request->validate([
            'password' => 'required|string|max:255|confirmed',
            'reason' => 'required|in:'.implode(',', array_keys(config('array.withdraw_reasons'))),
            'reason_detail' => 'nullable|required_if:reason,reason_other|string|max:2000',
        ], [
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'reason_detail.required_if' => '기타 탈퇴 사유를 입력해 주세요.',
        ]);
        if (! Hash::check($data['password'], Auth::user()->password)) {
            return back()->with('error_message', '비밀번호가 일치하지 않습니다.')
                ->withInput($request->only('reason', 'reason_detail'));
        }

        $receipt = $withdrawal->withdraw(Auth::user(), $data['reason'], $data['reason_detail'] ?? null);
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('member_withdrawal_receipt', $receipt);

        return redirect()->route('mypage.withdraw.success');
    }

    public function withdrawSuccess(Request $request)
    {
        $receipt = $request->session()->get('member_withdrawal_receipt');
        abort_unless(is_array($receipt) && ($receipt['expires_at'] ?? 0) > now()->timestamp, 404);

        return response()->view('front.mypage.sub01.withdraw_success', compact('receipt'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function withdrawLogout()
    {
        Auth::logout();

        return redirect('/');
    }

    // 방문한 채널 목록
    public function visitedChannels(Request $request)
    {
        $data = $request->validate(['search' => 'nullable|string|max:100']);
        $query = VisitedChannel::with(['shopChannel', 'vendor.vendorbusinessdetails'])->where('user_id', Auth::id());
        if ($search = trim($data['search'] ?? '')) {
            $query->whereHas('shopChannel', fn ($channel) => $channel->where(function ($channel) use ($search) {
                $channel->where('channel_name', 'like', '%'.$search.'%')->orWhere('channel_code', 'like', '%'.$search.'%');
            }));
        }
        $visitedChannels = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('front.mypage.sub01.visited_channels', compact('visitedChannels'));
    }

    // 방문한 채널 전체 삭제
    public function deleteAllVisitedChannels()
    {
        VisitedChannel::where('user_id', Auth::id())->delete();

        return redirect()->back()->with('success_message', '모든 방문 기록이 삭제되었습니다.');
    }

    // 방문한 채널 개별 삭제
    public function deleteVisitedChannel($id)
    {
        VisitedChannel::where(['user_id' => Auth::id(), 'id' => $id])->delete();

        return redirect()->back()->with('success_message', '해당 방문 기록이 삭제되었습니다.');
    }

    // 사용자 등록 (front/users/login_register.blade.php) AJAX 요청을 사용한 <form> 제출. front/js/custom.js 확인
    public function userRegister(Request $request)
    {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            $data = $request->all(); // AJAX 요청에서 보낸 이름/값 쌍 배열 가져오기

            // --- 사용자(회원) 등록 로직 ---

            // 유효성 검사
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'mobile' => 'required|numeric|digits:11',
                'email' => 'required|email|max:150|unique:users',
                'password' => ['required', \Illuminate\Validation\Rules\Password::min(12)->mixedCase()->letters()->numbers()],
                'accept' => 'required',

            ], [
                'accept.required' => '이용약관 및 개인정보 처리방침에 동의해 주세요.',
            ]);

            if ($validator->passes()) {
                $user = new User;
                $user->name = $data['name'];
                $user->mobile = $data['mobile'];
                $user->email = $data['email'];
                $user->password = bcrypt($data['password']);
                $user->status = 0;
                $user->save();

                // 확인 이메일 발송 후 사용자 활성화
                $email = $data['email'];
                $messageData = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'code' => base64_encode($data['email']),
                ];
                Mail::send('emails.confirmation', $messageData, function ($message) use ($email) {
                    $message->to($email)->subject('멀티 벤더 이커머스 계정 확인을 완료해 주세요');
                });

                // 성공 메시지와 함께 사용자 리다이렉트
                $redirectTo = url('user/login-register');

                return response()->json([
                    'type' => 'success',
                    'url' => $redirectTo,
                    'message' => '계정 활성화를 위해 이메일을 확인해 주세요!',
                ]);

            } else {
                return response()->json([
                    'type' => 'error',
                    'errors' => $validator->messages(),
                ]);
            }
        }
    }

    // 판매자 등록 페이지 렌더링
    public function vendorRegisterPage()
    {
        return view('front.users.register_vendor');
    }

    // 판매자 등록 로직
    public function vendorRegister(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'mobile' => 'required|numeric|digits_between:10,15|unique:admins|unique:vendors',
                'email' => 'required|email|max:150|unique:admins|unique:vendors',
                'password' => 'required|min:6',
                // 'shop_name' => 'required|string|max:150', // These might be in Step 2 (Channel Portal) not here?
                // 'shop_mobile' => 'required|numeric|digits_between:10,15',
                // 'business_license_number' => 'required|string|max:150',
                'accept' => 'required',
            ], [
                'accept.required' => 'Please accept our Terms & Conditions',
                'email.unique' => 'Email already exists for a vendor account',
                'mobile.unique' => 'Mobile number already exists',
            ]);

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    // 1. Create Vendor
                    $vendor = new Vendor;
                    $vendor->name = $data['name'];
                    $vendor->mobile = $data['mobile'];
                    $vendor->email = $data['email'];
                    $vendor->status = 0; // Inactive until verified by Admin
                    $vendor->confirm = 'Yes'; // Auto-confirm email for Development
                    date_default_timezone_set('Asia/Seoul');
                    $vendor->created_at = date('Y-m-d H:i:s');
                    $vendor->updated_at = date('Y-m-d H:i:s');
                    $vendor->save();

                    $vendor_id = DB::getPdo()->lastInsertId();

                    // 2. Create Vendor Business Details (Placeholder for now)
                    // If the form doesn't have shop details yet, we create a placeholder or skip
                    if (isset($data['shop_name'])) {
                        DB::table('vendors_business_details')->insert([
                            'vendor_id' => $vendor_id,
                            'shop_name' => $data['shop_name'],
                            'shop_mobile' => $data['shop_mobile'] ?? '',
                            'business_license_number' => $data['business_license_number'] ?? '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    // 3. Admin 생성 (유형: Vendor)
                    $admin = new Admin;
                    $admin->type = 'vendor';
                    $admin->vendor_id = $vendor_id;
                    $admin->name = $data['name'];
                    $admin->mobile = $data['mobile'];
                    $admin->email = $data['email'];
                    $admin->password = bcrypt($data['password']);
                    $admin->status = 0; // Inactive
                    $admin->confirm = 'Yes'; // Auto-confirm email
                    $admin->created_at = date('Y-m-d H:i:s');
                    $admin->updated_at = date('Y-m-d H:i:s');
                    $admin->save();

                    DB::commit();

                    // 성공 응답 반환
                    $redirectTo = route('channel.login');

                    return response()->json([
                        'type' => 'success',
                        'url' => $redirectTo,
                        'message' => 'Vendor account created successfully! Please login to complete your profile.',
                    ]);

                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json([
                        'type' => 'error',
                        // 'errors'  => ['error' => [$e->getMessage()]]
                        'errors' => ['email' => ['Registration failed: '.$e->getMessage()]],
                    ]);
                }

            } else {
                return response()->json([
                    'type' => 'error',
                    'errors' => $validator->messages(),
                ]);
            }
        }
    }

    // 사용자 로그인 (front/users/login_register.blade.php) AJAX 요청을 사용한 <form> 제출. front/js/custom.js 확인
    public function userLogin(Request $request)
    {
        if ($request->ajax()) { // if the request is coming via an AJAX call
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)

            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators
            $validator = Validator::make($request->all(), [
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Validation Rules
                'email' => 'required|email|max:150|exists:users', // 'exists:users'    means it must already exist in the `users` table    // exists:table,column: https://laravel.com/docs/9.x/validation#rule-exists
                'password' => 'required|min:6',
            ]);

            // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages
            // dd($validator->messages());
            // echo '<pre>', var_dump($validator->messages()), '</pre>';
            // exit;

            if ($validator->passes()) { // if validation passes (is successful), log the user in (but check first if they're inactive), and update the user's Cart (update the user's `user_id` column in `carts` table)
                // Log the user in
                if (Auth::attemptWhen([ // 기본 회원 인증
                    'email' => $data['email'],   // $data['email']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'password' => $data['password'], // $data['password'] comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                ], fn ($user) => ! $user->temporaryPasswordIsExpired())) {
                    // First, check if the user being authenticated/logged in is inactive/disabled/deactivated by an admin (`status` is zero 0 in `users` table), logout the user, then return them back with a message
                    if (Auth::user()->status == 0) {
                        Auth::logout(); // logout the user

                        // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                        return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                            'type' => 'inactive',
                            // 'message' => 'Your account is inactive. Please contact Admin'
                            'message' => 'Your account is not activated! Please confirm your account (by clicking on the Activation Link in the Confirmation Mail) to activate your account.',
                        ]);
                    }

                    // 사용자의 장바구니 업데이트 (`carts` 테이블의 `user_id` 컬럼) (로그인 전에는 세션으로만 저장됨) (Front/ProductsController.php의 cartAdd() 메서드 확인)
                    if (! empty(Session::get('session_id'))) {
                        $user_id = Auth::user()->id;
                        $session_id = Session::get('session_id');

                        Cart::where('session_id', $session_id)->update(['user_id' => $user_id]);
                    }

                    // 장바구니 페이지로 리다이렉트
                    $redirectTo = url('cart');

                    // AJAX 요청을 통한 응답이므로 JSON 반환
                    return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                        'type' => 'success',
                        'url' => $redirectTo, // 장바구니 페이지로 리다이렉트
                    ]);

                } else { // 유효성 검사는 통과했으나 로그인 정보가 틀린 경우
                    // AJAX 요청을 통해 HTML <form> 데이터를 제출하고 있으므로 JSON 응답을 반환함
                    return response()->json([
                        'type' => 'incorrect',
                        'message' => '이메일 또는 비밀번호가 일치하지 않습니다!',
                    ]);
                }

            } else { // 유효성 검사 실패 시 유효성 검사 오류 메시지 전송
                // AJAX 요청을 통해 HTML <form> 데이터를 제출하고 있으므로 JSON 응답을 반환함
                return response()->json([
                    'type' => 'error',
                    'errors' => $validator->messages(), // jQuery를 사용하여 오류 메시지를 프론트엔드에 표시함 (front/js/custom.js 확인)
                ]);
            }
        }
    }

    // 사용자 로그아웃 (이 라우트는 헤더의 로그아웃 탭에서 접근됨 (front/layout/header.blade.php))
    public function userLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // 사용자 계정 확인 이메일 (계정 활성화를 위한 '활성화 링크' 포함) (resources/views/emails/confirmation.blade.php, Mailtrap 사용)
    public function confirmAccount($code) // {code}는 이메일로 전송된 base64 인코딩된 계정 활성화 코드입니다.
    {
        $email = base64_decode($code); // $code is the encoded $email (check userRegister() method in UserController.php)    // we use the opposite (base64_decode()) of what we used in the userRegister() (base_64encode)
        // dd($email);

        // For Security Reasons, check if that decoded user's $email exists in the `users` database table
        $userCount = User::where('email', $email)->count();
        if ($userCount > 0) { // if the user's email exists in `users` table
            // Check if the user is alreay active
            $userDetails = User::where('email', $email)->first();
            if ($userDetails->status == 1) { // if the user's account is already activated
                // Redirect the user to the User Login/Register page with an 'error' message
                return redirect('user/login-register')->with('error_message', 'Your account is already activated. You can login now.');
            } else { // if the user's account is not yet activated, activate it (update `status` to 1) and send a 'Welcome' Email
                User::where('email', $email)->update([
                    'status' => 1,
                ]);

                // Send a Welcome Email to user after confirmation (clicking on the 'Activation Link' inside the Confirmation Email)    // HELO / Mailtrap / MailHog: https://laravel.com/docs/9.x/mail#mailtrap

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'name' => $userDetails->name, // the user's name that they entered while submitting the registration form
                    'mobile' => $userDetails->mobile, // the user's mobile that they entered while submitting the registration form
                    'email' => $email, // the user's email that they entered while submitting the registration form
                    // 'code'   => base64_encode($data['email']) // We base64 code the user's $email and send it as a Route Parameter from user_confirmation.blade.php to the 'user/confirm/{code}' route in web.php, then it gets base64 decoded again in confirmUser() method in Front/UserController.php    // we will use the opposite: base64_decode() in the confirmAccount() method (encode X decode)
                ];
                Mail::send('emails.register', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.register' is the register.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that register.blade.php will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('Welcome to Multi-vendor E-commerce Application');
                });

                // Note: Here, we have TWO options, either redirect user with a success message or Log the user In IMMDEIATELY, AUTOMATICALLY and DIRECTLY

                // Redirect the user to the User Login/Register page with a 'success' message
                return redirect('user/login-register')->with('success_message', 'Your account is activated. You can login now.');
            }

        } else { // if the user's email doesn't exist (hacking or cyber attack!!)
            abort(404);
        }
    }

    // 사용자 비밀번호 찾기 기능 (이 라우트는 'GET' 요청을 통해 front/users/login_register.blade.php의 <a> 태그에서 접근하고, front/users/forgot_password.blade.php의 HTML 폼이 제출될 때 'POST' 요청을 통해 접근함)
    public function forgotPassword(Request $request) // 비밀번호 찾기 기능 (GET 요청 시 페이지 렌더링, POST 요청 시 폼 제출 처리)
    {
        if ($request->ajax()) { // AJAX를 통한 비밀번호 찾기 폼 제출인 경우 (front/users/forgot_password.blade.php)
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)
            // dd($data);

            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:150|exists:users', // 'exists:users'    means it must already exist in the `users` table    // exists:table,column: https://laravel.com/docs/9.x/validation#rule-exists

            ], [ // Customizing The Error Messages: https://laravel.com/docs/9.x/validation#manual-customizing-the-error-messages
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Custom Messages
                // 'accept.required' => 'Please accept our Terms & Conditions'
                'email.exists' => '공인된 이메일이 아닙니다.',
            ]);

            if ($validator->passes()) { // if validation passes (is successful), generate a new password for the user
                $new_password = \Illuminate\Support\Str::random(16);

                // Generate a new password
                // Change the current password immediately as the user forgot it, update it to a new random password, untill the user updates it by themselves
                User::where('email', $data['email'])->update([
                    'password' => bcrypt($new_password), // storing the HASH-ed password (not the original password) in the database    // bcrypt(): https://laravel.com/docs/9.x/helpers#method-bcrypt
                ]);

                // Get user details
                $userDetails = User::where('email', $data['email'])->first()->toArray();

                // Send an email to the user to get the new password (reset their password)    // HELO / Mailtrap / MailHog: https://laravel.com/docs/9.x/mail#mailtrap
                $email = $data['email']; // the user's email that they entered while submitting the registration form

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'name' => $userDetails['name'], // the user's name that they entered while submitting the registration form
                    'email' => $email, // the user's email that they entered while submitting the registration form
                    'password' => $new_password, // the user's email that they entered while submitting the registration form
                    // 'code'  => base64_encode($data['email']) // We base64 code the user's $email and send it as a Route Parameter from user_confirmation.blade.php to the 'user/confirm/{code}' route in web.php, then it gets base64 decoded again in confirmUser() method in Front/UserController.php    // we will use the opposite: base64_decode() in the confirmUser() method (encode X decode)
                ];
                Mail::send('emails.user_forgot_password', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.user_forgot_password' is the resources/views/emails/user_forgot_password.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that the user_forgot_password.blade.php file will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('New Password - Multi-vendor E-commerce Application');
                });

                // Redirect user with a success message
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request. Check    $('#forgotForm').submit();    in front/js/custom.js
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type' => 'success',
                    'message' => '등록한 이메일로 임시 비밀번호가 전송되었습니다.',
                ]);

            } else { // if validation fails (is unsuccessful), send the Validation Error Messages
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type' => 'error',
                    'errors' => $validator->messages(), // we'll loop over the Validation Errors Messages array using jQuery to show them in the frontend (check front/js/custom.js)    // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages
                ]);
            }

        } else { // if the 'GET' request is coming from the <a> tag in front/users/login_register.blade.php, render the front/users/forgot_password.blade.php page
            return view('front.users.forgot_password');
        }

    }

    // 'GET' 요청으로 사용자 계정 페이지 렌더링 (front/users/user_account.blade.php), 또는 AJAX를 사용한 동일 페이지의 HTML 폼 제출 ('POST' 요청으로 사용자 상세 정보 업데이트). front/js/custom.js 확인
    public function userAccount(Request $request)
    {
        if ($request->ajax()) { // AJAX를 통한 사용자 정보 변경 요청인 경우 (update user details)
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)

            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators
            $validator = Validator::make($request->all(), [
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Validation Rules
                'name' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'address' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'mobile' => 'required|numeric|digits:11',
                'pincode' => 'required|digits:6',

            ] /*, [ // Customizing The Error Messages: https://laravel.com/docs/9.x/validation#manual-customizing-the-error-messages
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Custom Messages
                'accept.required' => 'Please accept our Terms & Conditions'
            ]*/);

            // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages
            // dd($validator->messages());
            // echo '<pre>', var_dump($validator->messages()), '</pre>';
            // exit;

            if ($validator->passes()) { // if validation passes (is successful), register (INSERT) the new user into the database `users` table, and log the user in IMMEDIATELY and AUTOMATICALLY and DIRECTLY, and redirect them to the Cart cart.blade.php page
                // Update user details in `users` table
                User::where('id', Auth::user()->id)->update([ // Retrieving The Authenticated User: https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user
                    'name' => $data['name'],    // $data['name']       comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'mobile' => $data['mobile'],  // $data['mobile']     comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'city' => $data['city'],    // $data['city']       comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'state' => $data['state'],   // $data['state']      comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'country' => $data['country'], // $data['country']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'pincode' => $data['pincode'], // $data['pincode']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'address' => $data['address'], // $data['address']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                ]);

                // Redirect user back with a success message
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type' => 'success',
                    // 'url'     => $redirectTo, // redirect user to the Cart cart.blade.php page
                    'message' => '연락처/결제 정보가 성공적으로 업데이트되었습니다!',
                ]);

            } else { // if validation fails (is unsuccessful), send the Validation Error Messages
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type' => 'error',
                    'errors' => $validator->messages(), // we'll loop over the Validation Errors Messages array using jQuery to show them in the frontend (Check    $('#accountForm').submit();    in front/js/custom.js)    // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages
                ]);
            }

        } else { // if it's a 'GET' request, render front/users/user_account.blade.php
            // Fetch all of the world countries from the database table `countries`
            $countries = Country::where('status', 1)->get()->toArray(); // get the countries which have status = 1 (to ignore the blacklisted countries, in case)

            return view('front.users.user_account')->with(compact('countries'));
        }
    }

    // AJAX를 통한 사용자 계정 비밀번호 업데이트 HTML 폼 제출. front/js/custom.js 확인
    public function userUpdatePassword(Request $request)
    {
        if ($request->ajax()) { // AJAX를 통한 비밀번호 변경 요청인 경우 (update user details)
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)

            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators
            $validator = Validator::make($request->all(), [
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Validation Rules
                'current_password' => 'required',
                'new_password' => ['required', 'different:current_password', \Illuminate\Validation\Rules\Password::min(12)->mixedCase()->letters()->numbers()],
                'confirm_password' => 'required|same:new_password',

            ] /*, [ // Customizing The Error Messages: https://laravel.com/docs/9.x/validation#manual-customizing-the-error-messages
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Custom Messages
                'accept.required' => 'Please accept our Terms & Conditions'
            ]*/);

            // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages
            // dd($validator->messages());
            // echo '<pre>', var_dump($validator->messages()), '</pre>';
            // exit;

            if ($validator->passes()) { // if validation passes (is successful), update the user's current password
                $current_password = $data['current_password']; // $data['current_password']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                $checkPassword = User::where('id', Auth::user()->id)->first();

                if (Hash::check($current_password, $checkPassword->password)) { // if the entered current password is correct, update the current password    // Confirming The Password: https://laravel.com/docs/9.x/authentication#confirming-the-password
                    // Update the user's current password to the new password
                    $user = User::find(Auth::user()->id);
                    $user->temporary_password_issued_at = null;
                    $user->temporary_password_expires_at = null;
                    $user->password = bcrypt($data['new_password']); // $data['new_password']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    $user->save();

                    // Redirect user back with a success message
                    // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                    return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                        'type' => 'success',
                        'message' => '비밀번호 변경이 정상적으로 처리되었습니다.',
                    ]);

                } else { // if the entered current password is incorrect/wrong, redirect with an error message
                    // Redirect user back with an error message
                    // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                    return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                        'type' => 'incorrect',
                        'message' => '현재 비밀번호가 일치하지 않습니다.',
                    ]);
                }

            } else { // 유효성 검사 실패 시 유효성 검사 오류 메시지 전송
                // AJAX 요청을 통한 응답이므로 JSON 반환
                return response()->json([
                    'type' => 'error',
                    'errors' => $validator->messages(), // jQuery를 사용하여 오류 메시지를 프론트엔드에 표시함 (front/js/custom.js 확인)
                ]);
            }

        }
    }

    /**
     * 아이디 찾기 - GET: 폼 표시, POST: 회원번호+이메일로 아이디 검색
     */
    public function findId(Request $request)
    {
        $result = null;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'member_number' => 'required|string|max:50',
                'email' => 'required|string|email|max:150',
            ], [
                'member_number.required' => '회원번호를 입력해 주세요.',
                'member_number.string' => '회원번호를 올바르게 입력해 주세요.',
                'email.required' => '대표이메일을 입력해 주세요.',
                'email.email' => '이메일 형식을 확인해 주세요.',
            ]);
            $memberNumber = $data['member_number'];
            $email = $data['email'];

            // 회원번호와 이메일로 사용자 검색
            $user = User::where('member_number', $memberNumber)
                ->where('email', $email)
                ->where('status', 1)
                ->first();

            if ($user) {
                $result = [
                    'type' => 'success',
                    'username' => $user->username,
                ];
            } else {
                $result = [
                    'type' => 'fail',
                    'message' => '일치하는 정보가 없습니다.',
                ];
            }
        }

        return response()->view('front.member.find_id', compact('result'))->header('Cache-Control', 'no-store, private');
    }

    /**
     * 비밀번호 찾기 - PPT 36 임시비밀번호 메일을 발송합니다.
     */
    public function findPw(Request $request)
    {
        $result = null;
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|string|email|max:150',
            ], [
                'username.required' => '아이디를 입력해 주세요.',
                'email.required' => '대표이메일을 입력해 주세요.',
                'email.email' => '이메일 형식을 확인해 주세요.',
            ]);
            try {
                $previewId = app(\App\Services\MemberPasswordRecovery::class)->issue($request, $data);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                report($exception);
                return back()->withInput($request->only('username', 'email'))
                    ->withErrors(['email' => '메일을 발송하지 못했습니다. 비밀번호는 변경되지 않았습니다. 잠시 후 다시 시도해 주세요.']);
            }
            $result = ['type' => 'success', 'message' => $previewId
                ? '검수용 메일함에 임시비밀번호 메일이 생성되었습니다.'
                : '대표이메일로 임시비밀번호를 발송했습니다.'];
            if ($previewId) {
                $request->session()->put('account_recovery_previews.'.$previewId, now()->addMinutes(60)->timestamp);
                $result['preview_url'] = route('local.preview.recovery_mail', $previewId);
            }
        }
        return response()->view('front.member.find_pw', compact('result'))->header('Cache-Control', 'no-store, private');
    }

    public function pointStatus()
    {
        $user = Auth::user();
        $channelPoints = PointTransaction::where('user_id', $user->id)
            ->whereNotNull('shop_channel_id')
            ->sum('points');
        $me9Points = PointTransaction::where('user_id', $user->id)
            ->whereNull('shop_channel_id')
            ->sum('points');

        $balances = PointTransaction::where('user_id', $user->id)->whereNotNull('shop_channel_id')
            ->select('shop_channel_id')->selectRaw('SUM(points) as balance')->groupBy('shop_channel_id')->pluck('balance', 'shop_channel_id');
        $shopIds = VisitedChannel::where('user_id', $user->id)->whereNotNull('shop_channel_id')->pluck('shop_channel_id')
            ->merge($balances->keys())->unique();
        $shops = ShopChannel::with('vendor.vendorbusinessdetails')->whereIn('id', $shopIds)->orderByDesc('id')->get();
        $operatingShops = ShopChannel::whereIn('vendor_id', $shops->pluck('vendor_id'))->where('status', 1)
            ->where('closure_status', '!=', 'approved')->where('use_own_pg', false)->get()
            ->filter(fn ($shop) => (int) $shop->use_period_type !== 1 ||
                ((!$shop->start_at || now()->gte($shop->start_at)) && (!$shop->end_at || now()->lte($shop->end_at))))
            ->groupBy('vendor_id');
        $pointsList = $shops->values()->map(function ($shop, $index) use ($balances) {
            return ['no' => $index + 1, 'vendor_id' => $shop->vendor_id,
                'seller_name' => $shop->vendor?->vendorbusinessdetails?->shop_name ?: ($shop->vendor?->name ?? $shop->channel_name),
                'channel_name' => $shop->channel_name, 'channel_code' => $shop->channel_code,
                'status' => (int) $shop->status === 1 ? '운영' : ($shop->closure_status === 'approved' ? '운영종료 승인' : '중지 · 승인 대기'),
                'available_points' => (int) ($balances[$shop->id] ?? 0), 'shop_channel_id' => $shop->id,
                'can_convert' => ($balances[$shop->id] ?? 0) > 0 && (int) $shop->status !== 1 && $shop->closure_status === 'approved'];
        });
        $shopChannelsByVendor = $operatingShops->map(fn ($channels) => $channels->map(function ($shop) use ($balances) {
            $url = route('shop.enter', $shop->channel_code);
            return ['name'=>$shop->channel_name, 'code'=>$shop->channel_code, 'url'=>$url,
                'points'=>(int)($balances[$shop->id] ?? 0),
                'qr'=>'data:image/png;base64,'.\DNS2D::getBarcodePNG($url, 'QRCODE', 4, 4),
                'large_qr'=>'data:image/png;base64,'.\DNS2D::getBarcodePNG($url, 'QRCODE', 10, 10)];
        }));

        return view('front.mypage.sub01.point_status', compact('user', 'channelPoints', 'me9Points', 'pointsList', 'shopChannelsByVendor'));
    }

    public function convertChannelPoint(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'shop_channel_id' => 'required|integer|exists:shop_channels,id',
        ]);

        $shop = ShopChannel::findOrFail($data['shop_channel_id']);
        if ((int) $shop->status === 1 || ($shop->closure_status ?? 'none') !== 'approved') {
            return back()->with('error_message', 'Shop 채널 운영중지 승인 완료 후 Me9 포인트로 전환할 수 있습니다.');
        }

        try {
            app(\App\Services\CustomerPointService::class)->convertClosedChannel($user->id, $shop);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success_message', '채널 포인트를 Me9 포인트로 전환했습니다.');
    }

    public function pointHistory(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d',
            'shop_channel' => 'nullable|string|max:100', 'channel_code' => 'nullable|string|max:100',
            'point_type' => 'nullable|in:적립,소진,earn,use,in,out',
            'point_min' => 'nullable|integer', 'point_max' => 'nullable|integer', 'detail' => 'nullable|string|max:255',
        ];
        if ($request->filled('start_date') && $request->filled('end_date')) $rules['end_date'] .= '|after_or_equal:start_date';
        if ($request->filled('point_min') && $request->filled('point_max')) $rules['point_max'] .= '|gte:point_min';
        $filters = $request->validate($rules, [
            'end_date.after_or_equal' => '종료일은 시작일 이후로 선택해 주세요.',
            'point_max.gte' => '최대 포인트는 최소 포인트 이상으로 입력해 주세요.',
        ]);

        $query = PointTransaction::with('shopChannel')
            ->where('user_id', $user->id);

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (! empty($filters['shop_channel'])) {
            $query->whereHas('shopChannel', function ($q) use ($filters) {
                $q->where('channel_name', 'like', '%'.$filters['shop_channel'].'%');
            });
        }

        if (! empty($filters['channel_code'])) {
            $query->whereHas('shopChannel', function ($q) use ($filters) {
                $q->where('channel_code', 'like', '%'.$filters['channel_code'].'%');
            });
        }

        if (! empty($filters['point_type'])) {
            if (in_array($filters['point_type'], ['적립', 'earn', 'in'], true)) {
                $query->where('points', '>=', 0);
            } elseif (in_array($filters['point_type'], ['소진', 'use', 'out'], true)) {
                $query->where('points', '<', 0);
            }
        }

        if (isset($filters['point_min']) && $filters['point_min'] !== '') {
            $query->where('points', '>=', (int) $filters['point_min']);
        }

        if (isset($filters['point_max']) && $filters['point_max'] !== '') {
            $query->where('points', '<=', (int) $filters['point_max']);
        }

        if (! empty($filters['detail'])) {
            $query->where('description', 'like', '%'.$filters['detail'].'%');
        }

        $pointHistory = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $first = $pointHistory->firstItem() ?? 1;
        $pointHistory->through(function ($transaction, $index) use ($first) {
                return [
                    'no' => $first + $index,
                    'created_at' => optional($transaction->created_at)->format('Y-m-d'),
                    'channel_name' => $transaction->shopChannel?->channel_name ?? 'Me9 통합',
                    'channel_code' => $transaction->shopChannel?->channel_code ?? 'ME9',
                    'type' => $transaction->points >= 0 ? '적립' : '소진',
                    'points' => $transaction->points,
                    'description' => $transaction->description ?: '포인트 이력',
                ];
            });

        return view('front.mypage.sub01.point_history', compact('user', 'pointHistory', 'filters'));
    }

    public function cartList(Request $request)
    {
        $user = Auth::user();
        if ($shop = ShopChannel::find(Session::get('shop_channel_id'))) {
            app(\App\Services\MemberCart::class)->read($shop);
        }
        $channelName = $request->validate(['channel_name'=>'nullable|string|max:100'])['channel_name'] ?? null;
        $legacy = Cart::with('product.attributes')->where('user_id', $user->id)->latest('id')->get()->map(function ($cart) {
            $row = $this->mypageProductRow($cart->product, $cart->id, $cart->size, $cart->quantity);
            if ($row) $row['delete_url'] = route('mypage.cart.delete', $cart->id);
            return $row;
        });
        $entries = \App\Models\MemberShopCart::with(['shopChannelProduct.product.attributes','shopChannelProduct.shopChannel'])
            ->where('user_id', $user->id)->latest('id')->get();
        $quantities = $entries->groupBy('shop_channel_product_id')->map->sum('quantity');
        $memberRows = $entries->map(function ($cart) use ($quantities) {
            $row = $this->mypageProductRow($cart->shopChannelProduct?->product, $cart->id, $cart->option_name, $cart->quantity, $cart->shopChannelProduct, $quantities[$cart->shop_channel_product_id]);
            if ($row) $row['delete_url'] = route('mypage.shop_cart.delete', $cart->id);
            return $row;
        });
        $cartItems = $memberRows->concat($legacy)->filter()->when($channelName, fn ($rows) => $rows->filter(fn ($row) => str_contains($row['shop_channel'], $channelName)))->values()->all();

        return view('front.mypage.sub01.cart_list', compact('user', 'cartItems', 'channelName'));
    }

    public function deleteShopCartItem(int $id)
    {
        \App\Models\MemberShopCart::where('user_id', Auth::id())->whereKey($id)->delete();

        return redirect()->route('mypage.cart')->with('success_message', '장바구니 상품을 삭제했습니다.');
    }

    public function wishlist(Request $request)
    {
        $user = Auth::user();
        $channelName = $request->validate(['channel_name'=>'nullable|string|max:100'])['channel_name'] ?? null;

        $wishlistItems = Wishlist::with('shopChannelProduct.product')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($wishlist) {
                $shopProduct = $wishlist->shopChannelProduct;
                $row = $this->mypageProductRow($shopProduct?->product, $wishlist->id, '기본옵션', 1, $shopProduct);
                if ($row) {
                    unset($row['quantity'], $row['qr_code']);
                }

                return $row;
            })
            ->filter()
            ->when($channelName, function ($items) use ($channelName) {
                return $items->filter(fn ($item) => str_contains($item['shop_channel'], $channelName));
            })
            ->values()
            ->all();

        return view('front.mypage.sub01.wishlist', compact('user', 'wishlistItems', 'channelName'));
    }

    public function deleteCartItem($id)
    {
        Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('mypage.cart')->with('success_message', '장바구니 상품을 삭제했습니다.');
    }

    public function deleteWishlistItem($id)
    {
        Wishlist::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('mypage.wishlist')->with('success_message', '찜한 상품을 삭제했습니다.');
    }

    private function mypageProductRow($product, int $id, string $option = '기본옵션', int $quantity = 1, $shopProduct = null, ?int $pricingQuantity = null): ?array
    {
        if (! $product) {
            return null;
        }

        $candidates = !$shopProduct ? ShopChannelProduct::with('shopChannel')
            ->where('product_id', $product->id)
            ->where('status', 1)->where('approval_status', 'approved')
            ->whereHas('shopChannel', fn ($query) => $query->where('status', 1)->where(fn ($q) => $q->whereNull('closure_status')->orWhere('closure_status', '!=', 'approved')))
            ->limit(2)->get() : collect();
        $shopProduct = $shopProduct ?: ($candidates->count() === 1 ? $candidates->first() : null);
        $shop = $shopProduct?->shopChannel;
        $visitUrl = $shop ? route('shop.enter', ['channelCode' => $shop->channel_code, 'product' => $shopProduct->id]) : route('shop.gate');
        $jointPrice = $shopProduct ? app(\App\Services\JointPurchasePricingService::class)->projectedPriceForProduct($product->id, $pricingQuantity ?? $quantity) : null;
        $product->loadMissing('attributes');
        $price = (float)($jointPrice['unit_price'] ?? ($shopProduct?->selling_price ?: $product->product_price));
        $price += (float)($product->attributes->firstWhere('size', $option)?->price_delta ?? 0);
        $channelUrl = $shop ? route('shop.enter', $shop->channel_code) : route('shop.gate');

        return [
            'id' => $id,
            'image' => $product->product_image
                ? asset('front/images/product_images/small/'.$product->product_image)
                : asset('front/images/product_images/small/no-image.png'),
            'code' => $product->product_code,
            'name' => $product->product_name,
            'option' => $option ?: '기본옵션',
            'quantity' => $quantity,
            'price' => $price,
            'line_total' => $price * $quantity,
            'qr_code' => 'data:image/png;base64,'.\DNS2D::getBarcodePNG($channelUrl, 'QRCODE', 10, 10),
            'channel_url' => $channelUrl,
            'product_url' => $visitUrl,
            'shop_channel' => $shop?->channel_name ?? 'Me9 Shop',
            'visit_url' => $visitUrl,
        ];
    }

    /**
     * 쿠폰 목록 (PPT Slide 48) - 사용자가 보유한 쿠폰 조회
     */
    public function couponList(Request $request)
    {
        $user = Auth::user();

        // 기간 검색 필터
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // DB에서 활성 쿠폰 조회 (사용자에게 해당하는 쿠폰)
        $query = Coupon::where('status', 1)
            ->where('expiry_date', '>=', now()->format('Y-m-d'));

        // 기간 필터 적용
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate.' 23:59:59']);
        }

        $query->where(function ($query) use ($user) {
            $query->whereNull('users')->orWhere('users', '')->orWhereRaw("FIND_IN_SET(?, REPLACE(users, ' ', '')) > 0", [$user->email]);
        });
        $coupons = $query->orderBy('expiry_date')->orderBy('id')->paginate(20)->withQueryString();

        $totalCount = $coupons->total();

        return view('front.mypage.sub01.coupon_list', compact('user', 'coupons', 'totalCount', 'startDate', 'endDate'));
    }

    public function orderList(Request $request)
    {
        $user = Auth::user();

        // Ensure user is loaded
        if (! $user) {
            return redirect()->route('front.member.login');
        }

        $filterRules = ['tab'=>'nullable|in:order,cancel,return,exchange','status'=>'nullable|string|max:40',
            'vendor_id'=>'nullable|integer','page'=>'nullable|integer|min:1','start_date'=>'nullable|date_format:Y-m-d','end_date'=>'nullable|date_format:Y-m-d'];
        if ($request->filled('start_date') && $request->filled('end_date')) $filterRules['end_date'] .= '|after_or_equal:start_date';
        $request->validate($filterRules);

        // Status filter from request
        $status = $request->input('status', 'all');
        $tab = $request->input('tab', 'order');
        $vendorId = $request->input('vendor_id'); // Optional filter by channel
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Fetch real orders from database
        $ordersQuery = Order::with(['orders_products.product.category','orders_products.shopChannel'])
            ->where('user_id', $user->id);

        if (! empty($startDate)) {
            $ordersQuery->whereDate('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $ordersQuery->whereDate('created_at', '<=', $endDate);
        }

        // Filter by vendor if requested (for organic visited channel orders link)
        if (! empty($vendorId)) {
            $ordersQuery->whereHas('orders_products', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        }

        $dbOrders = $ordersQuery->orderBy('id', 'desc')->get();

        // Format and map database orders to the structure expected by the view
        $formattedOrders = [];
        foreach ($dbOrders as $order) {
            $items = [];
            foreach ($order->orders_products as $item) {
                // If filtering by vendor, only keep items for that vendor
                if (! empty($vendorId) && $item->vendor_id != $vendorId) {
                    continue;
                }

                $normalizedStatus = $item->normalized_status;
                $itemStatus = $item->status_label;
                $keepItem = false;

                // 1. Tab Filtering
                if ($tab === 'order') {
                    if (in_array($normalizedStatus, [
                        OrderItemStatus::PAID,
                        OrderItemStatus::READY_TO_SHIP,
                        OrderItemStatus::SHIPPING,
                        OrderItemStatus::DELIVERED,
                        OrderItemStatus::CONFIRMED,
                    ], true)) {
                        $keepItem = true;
                    }
                } elseif ($tab === 'cancel') {
                    if (in_array($normalizedStatus, [OrderItemStatus::CANCEL_REQUESTED, OrderItemStatus::CANCELLED], true)) {
                        $keepItem = true;
                    }
                } elseif ($tab === 'return') {
                    if (in_array($normalizedStatus, array_merge(OrderItemStatus::pendingReturnStatuses(), [OrderItemStatus::RETURNED]), true)) {
                        $keepItem = true;
                    }
                } elseif ($tab === 'exchange') {
                    if (in_array($normalizedStatus, array_merge(OrderItemStatus::pendingExchangeStatuses(), [OrderItemStatus::EXCHANGED]), true)) {
                        $keepItem = true;
                    }
                }

                if (! $keepItem) {
                    continue;
                }

                // 2. Status Filtering (Sub-filter)
                if ($status !== 'all') {
                    $keepItem = false;
                    $filterStatusMap = [
                        'payment_completed' => OrderItemStatus::PAID,
                        'preparing_shipment' => OrderItemStatus::READY_TO_SHIP,
                        'shipping' => OrderItemStatus::SHIPPING,
                        'delivered' => OrderItemStatus::DELIVERED,
                        'purchase_confirmed' => OrderItemStatus::CONFIRMED,
                        'cancel_request' => OrderItemStatus::CANCEL_REQUESTED,
                        'cancel_completed' => OrderItemStatus::CANCELLED,
                        'return_request' => OrderItemStatus::pendingReturnStatuses(),
                        'return_completed' => OrderItemStatus::RETURNED,
                        'exchange_request' => OrderItemStatus::pendingExchangeStatuses(),
                        'exchange_completed' => OrderItemStatus::EXCHANGED,
                    ];
                    $keepItem = in_array($normalizedStatus, (array) ($filterStatusMap[$status] ?? []), true);
                }

                if (! $keepItem) {
                    continue;
                }

                // Button rendering rules
                $buttons = [];
                if (in_array($normalizedStatus, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP], true)) {
                    $buttons[] = 'cancel';
                } elseif (in_array($normalizedStatus, [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED], true)) {
                    $buttons[] = 'return';
                    $buttons[] = 'exchange';
                    $buttons[] = 'confirm';
                } elseif ($normalizedStatus === OrderItemStatus::CONFIRMED) {
                    $buttons[] = 'review';
                }

                $shopName = DB::table('vendors_business_details')
                    ->where('vendor_id', $item->vendor_id)
                    ->value('shop_name') ?? 'Me9 브랜드 전용관';

                $productImage = asset('front/images/product_images/small/no-image.png');
                if ($item->product && ! empty($item->product->product_image)) {
                    $productImage = asset('front/images/product_images/small/'.$item->product->product_image);
                }

                $itemPayment = app(\App\Services\SettlementCalculator::class)->paymentAmounts($item);
                $items[] = [
                    'id' => $item->id,
                    'order_item_id' => $item->id,
                    'shop_name' => $item->shopChannel?->channel_name ?: $shopName,
                    'category_path' => $item->product?->category_path ?? '상품 정보',
                    'seller_name' => 'Seller',
                    'product_image' => $productImage,
                    'product_name' => $item->product_name,
                    'product_id' => $item->product_id,
                    'option' => '옵션: '.$item->product_size.' / '.$item->product_qty.'개'.($item->is_exchange_replacement ? ' · 교환 재발송 (추가 상품 결제 없음)' : ''),
                    'price' => $item->product_price * $item->product_qty,
                    'status' => $itemStatus,
                    'shipping_fee' => $itemPayment['shipping'] > 0 ? '배송비: '.number_format($itemPayment['shipping']).' 원' : '무료배송',
                    'buttons' => $buttons,
                    'return_address' => app(ReturnAddressResolver::class)->forOrderItem($item),
                    // Additional dates for UI output
                    'cancel_request_date' => $normalizedStatus === OrderItemStatus::CANCEL_REQUESTED ? $item->updated_at->format('Y.m.d') : null,
                    'cancel_complete_date' => $normalizedStatus === OrderItemStatus::CANCELLED ? $item->updated_at->format('Y.m.d') : null,
                    'return_request_date' => $normalizedStatus === OrderItemStatus::RETURN_REQUESTED ? $item->updated_at->format('Y.m.d') : null,
                    'exchange_request_date' => $normalizedStatus === OrderItemStatus::EXCHANGE_REQUESTED ? $item->updated_at->format('Y.m.d') : null,
                ];
            }

            if (! empty($items)) {
                $formattedOrders[] = [
                    'id' => $order->id,
                    'order_no' => 'Me9-'.sprintf('%08d', $order->id),
                    'created_at' => $order->created_at->format('Y.m.d'),
                    'items' => $items,
                ];
            }
        }

        $page = max(1, (int) $request->input('page', 1));
        $orders = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($formattedOrders)->forPage($page, 10)->values(), count($formattedOrders), 10, $page,
            ['path'=>route('mypage.order.list'), 'query'=>$request->query()]
        );

        return view('front.mypage.order.list', compact('user', 'orders', 'status', 'tab', 'startDate', 'endDate'));
    }

    public function cancelReturnList()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('front.member.login');
        }

        $filterType = request('type', 'all');
        $orders = [];

        // 1. Fetch claims if type is all, cancel, return, or exchange
        if ($filterType == 'all' || in_array($filterType, ['cancel', 'return', 'exchange'])) {
            $query = DB::table('order_claims')
                ->join('orders', 'order_claims.order_id', '=', 'orders.id')
                ->join('orders_products', 'order_claims.order_product_id', '=', 'orders_products.id')
                ->where('order_claims.user_id', $user->id)
                ->select('order_claims.*', 'orders.created_at as order_date', 'orders_products.product_name', 'orders_products.product_size as option', 'orders_products.product_qty as qty', 'orders_products.product_price as price');

            if (in_array($filterType, ['cancel', 'return', 'exchange'])) {
                $query->where('order_claims.type', $filterType);
            }

            $claims = $query->orderBy('order_claims.created_at', 'desc')->get();

            foreach ($claims as $claim) {
                $orders[] = [
                    'date' => date('Y-m-d', strtotime($claim->created_at)),
                    'order_no' => 'Me9-Shop-'.sprintf('%07d', $claim->order_id),
                    'product_name' => $claim->product_name,
                    'option' => $claim->option,
                    'qty' => $claim->qty,
                    'price' => $claim->price,
                    'status' => $claim->status == 'requested' ? '신청완료' : ($claim->status == 'completed' ? '처리완료' : $claim->status),
                    'type' => $claim->type == 'cancel' ? '취소' : ($claim->type == 'return' ? '반품' : '교환'),
                    'type_raw' => $claim->type,
                    'timestamp' => strtotime($claim->created_at),
                ];
            }
        }

        // 2. Fetch confirmed items if type is all or confirm
        if ($filterType == 'all' || $filterType == 'confirm') {
            $confirmedItems = DB::table('orders_products')
                ->join('orders', 'orders_products.order_id', '=', 'orders.id')
                ->where('orders_products.user_id', $user->id)
                ->where(function ($query) {
                    $query->where('orders_products.status_code', OrderItemStatus::CONFIRMED)
                        ->orWhereIn('orders_products.item_status', ['Confirmed', '구매확정']);
                })
                ->select('orders_products.*', 'orders.created_at as order_date')
                ->orderBy('orders_products.updated_at', 'desc')
                ->get();

            foreach ($confirmedItems as $item) {
                $orders[] = [
                    'date' => date('Y-m-d', strtotime($item->updated_at)),
                    'order_no' => 'Me9-Shop-'.sprintf('%07d', $item->order_id),
                    'product_name' => $item->product_name,
                    'option' => $item->product_size,
                    'qty' => $item->product_qty,
                    'price' => $item->product_price,
                    'status' => '완료',
                    'type' => '구매확정',
                    'type_raw' => 'confirm',
                    'timestamp' => strtotime($item->updated_at),
                ];
            }
        }

        // Sort combined list by date descending
        usort($orders, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return view('front.mypage.cancel_return_list', compact('user', 'orders', 'filterType'));
    }

}
