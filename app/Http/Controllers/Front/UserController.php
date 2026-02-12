<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Cart;
use App\Models\Vendor;
use App\Models\Admin;
use App\Models\VendorsBusinessDetail;
use App\Models\VendorsBankDetail;

class UserController extends Controller
{
    // 새로운 로그인 액션
    public function loginUser(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'login_id' => 'required',
                'password' => 'required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // login_id가 이메일인지 사용자명인지 확인
            $field = filter_var($data['login_id'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            if (Auth::attempt([$field => $data['login_id'], 'password' => $data['password']])) {
                if (Auth::user()->status == 0) {
                    Auth::logout();
                    return redirect()->back()->with('error_message', '계정이 아직 활성화되지 않았습니다. 관리자에게 문의하세요.');
                }
                
                // 장바구니 업데이트
                if (!empty(Session::get('session_id'))) {
                    $user_id    = Auth::user()->id;
                    $session_id = Session::get('session_id');
                    Cart::where('session_id', $session_id)->update(['user_id' => $user_id]);
                }

                return redirect('/');
            } else {
                return redirect()->back()->with('error_message', '아이디(이메일) 또는 비밀번호가 일치하지 않습니다.');
            }
        }
    }

    // 새로운 회원가입 액션
    public function registerUser(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();

            // 폼 필드 결합
            if(isset($data['email_prefix']) && isset($data['email_suffix'])) {
                $data['email'] = $data['email_prefix'] . '@' . $data['email_suffix'];
            }
            if(isset($data['mobile']) && is_array($data['mobile'])) {
                $data['mobile_str'] = implode('-', $data['mobile']);
            }

            $rules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'mobile_str' => 'required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

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
    public function loginRegister() {
        return view('front.users.login_register');
    }

    public function registerMember(Request $request) {
        $type = $request->input('type', 'general'); // 'general' 또는 'social'
        
        // 소셜 로그인 모크 데이터 (실제 앱에서는 세션에서 가져옴)
        $socialData = [];
        if ($type == 'social') {
            $socialData = [
                'provider' => 'NAVER',
                'email' => 'social_user@naver.com',
                'name' => '홍길동'
            ];
        }

        return view('front.member.join', [
            'type' => $type,
            'socialData' => $socialData,
            'dep1_id' => '05',
            'dep1_tit' => '회원가입'
        ]);
    }

    // 커스텀 FormRequest 사용
    public function registerMemberSubmit(\App\Http\Requests\RegisterMemberRequest $request) {
        if ($request->isMethod('post')) {
            // Request 클래스에 의해 데이터가 이미 유효성 검사 및 정제됨
            $data = $request->validated();
            
            // 참고: $request->validated()는 rules 배열에 있는 필드만 포함함
            // prepareForValidation에서 필드를 결합했으므로 input()을 통해 접근하거나 
            // Ensure they are in the data array if needed, but for 'email' it is in rules.
            // email의 경우 rules에 있음
            
            $regType = $request->input('register_type', 'general');

            // 사용자 생성
            $user = new User;
            $user->email = $request->input('email'); 
            
            if ($regType == 'general') {
                $user->username = $data['username'];
                $user->name = $data['username']; 
                $user->password = bcrypt($data['password']);
            } else {
                $user->name = $data['name'];
                $user->mobile = $request->input('mobile_str'); // 정제된 입력값
                $user->password = bcrypt(\Illuminate\Support\Str::random(16));
            }

            $user->status = 1; 
            $user->save();

            // Axios를 위한 JSON 응답 반환
            return response()->json([
                'status' => 'success',
                'message' => '회원가입이 완료되었습니다. 로그인 해주세요.',
                'redirect_url' => route('front.member.login')
            ]);
    }
    }

    public function checkIdAvailability(Request $request) {
        if ($request->ajax()) {
            $data = $request->all();
            if (empty($data['username'])) {
                 return response()->json(['status' => 'error', 'message' => '아이디를 입력해주세요.']);
            }

            // 정규식 체크
            if (!preg_match('/^[a-zA-Z0-9]+$/', $data['username'])) {
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

    public function updateMemberStep1(Request $request) {
        if (!Auth::check()) {
            return redirect()->route('front.member.login')->with('error_message', '로그인이 필요합니다.');
        }

        $user = Auth::user();
        
        $data = $request->all();

        // 유효성 검사
        $rules = [
            'name' => 'required|string|max:100',
            'mobile_1' => 'required',
            'mobile_2' => 'required',
            'mobile_3' => 'required',
            'zipcode' => 'required',
            'address1' => 'required',
            'address2' => 'required'
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 사용자 업데이트
        $user->name = $data['name'];
        if(isset($data['gender'])) {
            $user->gender = $data['gender'];
        }

        if (isset($data['member_number']) && empty($user->member_number)) {
            $user->member_number = $data['member_number'];
        }

        $user->mobile = $data['mobile_1'] . '-' . $data['mobile_2'] . '-' . $data['mobile_3'];
        $user->pincode = $data['zipcode'];
        $user->address = $data['address1'];
        $user->city = $data['address2']; // 상세 주소를 기존 스키마에 따라 city/state에 저장하거나 적절히 매핑함
        
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => '회원정보가 수정되었습니다.'
            ]);
        }

        return redirect()->back()->with('success_message', '회원정보가 수정되었습니다.');
    }

    public function registerStep1() {
        if (!Auth::check()) {
            return redirect()->route('front.member.login')->with('error_message', '로그인이 필요합니다.');
        }

        $user = Auth::user();
        
        $memberNumber = $user->member_number;

        if (empty($memberNumber)) {
            // 회원 번호 생성: M9-YYMMDD-XXXX (당일 가입 순서)
            $dateStr = $user->created_at->format('ymd');
            $rank = User::whereDate('created_at', $user->created_at->format('Y-m-d'))
                        ->where('id', '<=', $user->id)
                        ->count();
            $memberNumber = 'M9-' . $dateStr . '-' . str_pad($rank, 4, '0', STR_PAD_LEFT);
        }

        return view('front.member.register.step1', compact('user', 'memberNumber'));
    }

    public function registerStep2() {
        if (!Auth::check()) {
            return redirect()->route('front.member.login')->with('error_message', '로그인이 필요합니다.');
        }

        $user = Auth::user();

        // 회원번호 로직 (Step 1과 동일)
        $memberNumber = $user->member_number;
        if (empty($memberNumber)) {
            $dateStr = $user->created_at->format('ymd');
            $rank = User::whereDate('created_at', $user->created_at->format('Y-m-d'))
                        ->where('id', '<=', $user->id)
                        ->count();
            $memberNumber = 'M9-' . $dateStr . '-' . str_pad($rank, 4, '0', STR_PAD_LEFT);
            // 이상적으로는 지금 저장해야 하지만, Step 1에서 처리하거나 Step 2에서 저장하도록 함.
        }

        $vendorDetails = null;
        $businessDetails = null;
        $bankDetails = null;

        if ($user->vendor_id) {
            $vendorDetails = Vendor::find($user->vendor_id);
            if ($vendorDetails) {
                $businessDetails = VendorsBusinessDetail::where('vendor_id', $user->vendor_id)->first();
                $bankDetails = VendorsBankDetail::where('vendor_id', $user->vendor_id)->first();
            }
        }

        return view('front.member.register.step2', compact('user', 'memberNumber', 'vendorDetails', 'businessDetails', 'bankDetails'));
    }

    public function updateMemberStep2(Request $request) {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $user = Auth::user();
        $data = $request->all();

        // Validation
        $rules = [
            'shop_name' => 'required',
            'shop_business_type' => 'required',
            'business_license_1' => 'required',
            'business_license_2' => 'required',
            'business_license_3' => 'required',
            'mobile_1' => 'required',
            'mobile_2' => 'required',
            'mobile_3' => 'required',
            'email_1' => 'required',
            'email_2' => 'required',
            'zipcode' => 'required',
            'address1' => 'required',
            'address2' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'account_holder_name' => 'required',
            'agree1' => 'required'
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => '필수 항목을 모두 입력해주세요.', 'errors' => $validator->errors()]);
        }

        // 1. Vendor가 존재하는지 확인하고 사용자와 연결
        if (!$user->vendor_id) {
            $vendor = new Vendor;
            $vendor->name = $user->name;
            $vendor->mobile = $user->mobile;
            $vendor->email = $user->email;
            $vendor->status = 0;
            $vendor->commission = 0;
            $vendor->confirm = 'No';
            $vendor->save();
            
            $user->vendor_id = $vendor->id;
            $user->save();
        }

        $vendor_id = $user->vendor_id;

        // 2. 사업자 상세 정보 업데이트
        $business = VendorsBusinessDetail::where('vendor_id', $vendor_id)->first();
        if (!$business) {
            $business = new VendorsBusinessDetail;
            $business->vendor_id = $vendor_id;
        }

        $business->shop_name = $data['shop_name'];
        $business->shop_business_type = $data['shop_business_type'];
        $business->business_license_number = $data['business_license_1'] . '-' . $data['business_license_2'] . '-' . $data['business_license_3'];
        $business->shop_mobile = $data['mobile_1'] . '-' . $data['mobile_2'] . '-' . $data['mobile_3'];
        $business->shop_email = $data['email_1'] . '@' . $data['email_2'];
        $business->shop_address = $data['address1'];
        $business->shop_address_detail = $data['address2'];
        $business->shop_pincode = $data['zipcode'];
        $business->shop_city = '';
        $business->shop_state = '';
        $business->shop_country = '';
        $business->address_proof = '';
        $business->address_proof_image = '';
        $business->gst_number = '';
        $business->pan_number = '';
        $business->bank_name = $data['bank_name'];
        $business->bank_account_number = $data['account_number'];
        $business->bank_account_holder_name = $data['account_holder_name'];

        // 이미지 업로드 처리
        if ($request->hasFile('bank_copy_image')) {
            $image_tmp = $request->file('bank_copy_image');
            if ($image_tmp->isValid()) {
                // 이미지 확장자 가져오기
                $extension = $image_tmp->getClientOriginalExtension();
                // 새로운 이미지 이름 생성
                $imageName = $vendor_id . '-' . rand(111, 99999) . '.' . $extension;
                $imagePath = 'front/images/bank_copies/' . $imageName;
                // 이미지 업로드
                $image_tmp->move(public_path('front/images/bank_copies'), $imageName);
                $business->bank_copy_image = $imageName;
            }
        }

        $business->save();

        // 3. 은행 상세 정보 업데이트 (필요 시 병렬 테이블)
        $bank = VendorsBankDetail::where('vendor_id', $vendor_id)->first();
        if (!$bank) {
            $bank = new VendorsBankDetail;
            $bank->vendor_id = $vendor_id;
        }
        $bank->bank_name = $data['bank_name'];
        $bank->account_number = $data['account_number'];
        $bank->account_holder_name = $data['account_holder_name'];
        $bank->bank_ifsc_code = ''; // 레거시 제약 조건 충족
        $bank->save();

        return response()->json([
            'status' => 'success',
            'message' => '기본정보가 저장되었습니다.'
        ]);
    }

    public function updateMemberStep3(Request $request) {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.']);
        }

        $user = Auth::user();
        $data = $request->all();

        // 유효성 검사 - agree1은 필수입니다.
        if (!isset($data['agree1'])) {
            return response()->json(['status' => 'error', 'message' => '필수 약관에 동의해야 합니다.']);
        }

        if (!$user->vendor_id) {
            return response()->json(['status' => 'error', 'message' => '회원사 정보(Step 2)를 먼저 입력해주세요.']);
        }

        $vendor_id = $user->vendor_id;

        // 1. 사업자 상세 정보 업데이트 (문서)
        $business = VendorsBusinessDetail::where('vendor_id', $vendor_id)->first();
        if (!$business) {
            $business = new VendorsBusinessDetail;
            $business->vendor_id = $vendor_id;
        }

        // 사업자등록증 업로드 처리
        if ($request->hasFile('address_proof_image')) {
            $image_tmp = $request->file('address_proof_image');
            if ($image_tmp->isValid()) {
                $extension = $image_tmp->getClientOriginalExtension();
                $imageName = $vendor_id . '-license-' . rand(111, 99999) . '.' . $extension;
                $image_tmp->move(public_path('front/images/bank_copies'), $imageName); // 가독성을 위해 동일한 폴더 사용
                $business->address_proof_image = $imageName;
                $business->address_proof = 'Business License';
            }
        }

        // 통장사본 업로드 처리
        if ($request->hasFile('bank_copy_image')) {
            $image_tmp = $request->file('bank_copy_image');
            if ($image_tmp->isValid()) {
                $extension = $image_tmp->getClientOriginalExtension();
                $imageName = $vendor_id . '-bankbook-' . rand(111, 99999) . '.' . $extension;
                $image_tmp->move(public_path('front/images/bank_copies'), $imageName);
                $business->bank_copy_image = $imageName;
            }
        }

        // 제공된 경우 계좌 정보 업데이트
        if (isset($data['bank_name'])) $business->bank_name = $data['bank_name'];
        if (isset($data['account_number'])) $business->bank_account_number = $data['account_number'];
        if (isset($data['account_holder_name'])) $business->bank_account_holder_name = $data['account_holder_name'];

        // 레거시 제약 조건 충족
        $business->shop_city = $business->shop_city ?? '';
        $business->shop_state = $business->shop_state ?? '';
        $business->shop_country = $business->shop_country ?? '';
        $business->gst_number = $business->gst_number ?? '';
        $business->pan_number = $business->pan_number ?? '';

        $business->save();

        // 2. 은행 상세 정보 테이블 업데이트
        $bank = VendorsBankDetail::where('vendor_id', $vendor_id)->first();
        if (!$bank) {
            $bank = new VendorsBankDetail;
            $bank->vendor_id = $vendor_id;
        }
        if (isset($data['bank_name'])) $bank->bank_name = $data['bank_name'];
        if (isset($data['account_number'])) $bank->account_number = $data['account_number'];
        if (isset($data['account_holder_name'])) $bank->account_holder_name = $data['account_holder_name'];
        $bank->bank_ifsc_code = $bank->bank_ifsc_code ?? '';
        $bank->save();

        return response()->json([
            'status' => 'success',
            'message' => '인증 요청이 접수되었습니다. (기본정보 저장 완료)'
        ]);
    }

    public function registerStep3() {
        if (!Auth::check()) {
            return redirect()->route('front.member.login')->with('error_message', '로그인이 필요합니다.');
        }

        $user = Auth::user();

        // 회원번호 로직 (Step 1, 2와 동일)
        $memberNumber = $user->member_number;
        if (empty($memberNumber)) {
            $dateStr = $user->created_at->format('ymd');
            $rank = User::whereDate('created_at', $user->created_at->format('Y-m-d'))
                        ->where('id', '<=', $user->id)
                        ->count();
            $memberNumber = 'M9-' . $dateStr . '-' . str_pad($rank, 4, '0', STR_PAD_LEFT);
        }

        $vendorDetails = null;
        $businessDetails = null;
        $bankDetails = null;

        if ($user->vendor_id) {
            $vendorDetails = Vendor::find($user->vendor_id);
            if ($vendorDetails) {
                $businessDetails = VendorsBusinessDetail::where('vendor_id', $user->vendor_id)->first();
                $bankDetails = VendorsBankDetail::where('vendor_id', $user->vendor_id)->first();
            }
        }

        return view('front.member.register.step3', compact('user', 'memberNumber', 'vendorDetails', 'businessDetails', 'bankDetails'));
    }

    // 새로운 로그인 페이지
    public function login() {
        return view('front.member.login');
    }

    // 새로운 회원가입 페이지
    public function register() {
         return view('front.member.register.step1');
    }

    // 마이페이지 대시보드
    public function dashboard()
    {
        return view('front.mypage.index');
    }

    public function orderView()
    {
        return view('front.mypage.order.view');
    }

    public function profileEdit()
    {
        return view('front.mypage.profile');
    }

    public function profileUpdate(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'name' => 'required',
                'mobile_1' => 'required',
                'mobile_2' => 'required',
                'mobile_3' => 'required',
                'email_1' => 'required',
                'email_2' => 'required',
                'zipcode' => 'required',
                'address1' => 'required',
                'address2' => 'required',
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // 연락처 결합
            $mobile = $data['mobile_1'] . '-' . $data['mobile_2'] . '-' . $data['mobile_3'];
            
            // 이메일 결합
            $email = $data['email_1'] . '@' . $data['email_2'];
            
            // 중복 확인 (현재 사용자 제외)
            $user_id = Auth::user()->id;
            $emailCount = User::where('email', $email)->where('id', '!=', $user_id)->count();
            if ($emailCount > 0) {
                return redirect()->back()->with('error_message', '이 이메일은 이미 사용 중입니다.')->withInput();
            }

            $user = User::find($user_id);
            $user->name = $data['name'];
            $user->gender = $data['gender'];
            $user->mobile = $mobile;
            $user->email = $email;
            
            $user->pincode = $data['zipcode'];
            $user->address = $data['address1'];
            $user->city = $data['address2']; // 상세 주소를 city 컬럼에 저장

            $user->save();

            return redirect()->back()->with('success_message', '회원정보가 수정되었습니다.');
        }
    }

    public function delivery()
    {
        return view('front.mypage.sub01.delivery_destination');
    }

    public function withdraw()
    {
        return view('front.mypage.sub01.withdraw_confirm');
    }


    // 사용자 등록 (front/users/login_register.blade.php) AJAX 요청을 사용한 <form> 제출. front/js/custom.js 확인    
    public function userRegister(Request $request) {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            $data = $request->all(); // AJAX 요청에서 보낸 이름/값 쌍 배열 가져오기

            // --- 사용자(회원) 등록 로직 ---

            // 유효성 검사
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name'     => 'required|string|max:100',
                'mobile'   => 'required|numeric|digits:11',
                'email'    => 'required|email|max:150|unique:users', 
                'password' => 'required|min:6',
                'accept'   => 'required'

            ], [ 
                'accept.required' => '이용약관 및 개인정보 처리방침에 동의해 주세요.'
            ]);

            if ($validator->passes()) { 
                $user = new User;
                $user->name     = $data['name'];
                $user->mobile   = $data['mobile']; 
                $user->email    = $data['email'];  
                $user->password = bcrypt($data['password']); 
                $user->status   = 0; 
                $user->save();

                // 확인 이메일 발송 후 사용자 활성화
                $email = $data['email']; 
                $messageData = [
                    'name'   => $data['name'],   
                    'email'  => $data['email'],  
                    'code'   => base64_encode($data['email']) 
                ];
                \Illuminate\Support\Facades\Mail::send('emails.confirmation', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('멀티 벤더 이커머스 계정 확인을 완료해 주세요');
                });

                // 성공 메시지와 함께 사용자 리다이렉트
                $redirectTo = url('user/login-register'); 

                return response()->json([ 
                    'type'    => 'success',
                    'url'     => $redirectTo, 
                    'message' => '계정 활성화를 위해 이메일을 확인해 주세요!'
                ]);

            } else { 
                return response()->json([ 
                    'type'   => 'error',
                    'errors' => $validator->messages() 
                ]);
            }
        }
    }

    // 판매자 등록 페이지 렌더링
    public function vendorRegisterPage() {
        return view('front.users.register_vendor');
    }

    // 판매자 등록 로직
    public function vendorRegister(Request $request) {
        if ($request->ajax()) {
            $data = $request->all();

            // Validation
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name'     => 'required|string|max:100',
                'mobile'   => 'required|numeric|digits_between:10,15|unique:admins|unique:vendors',
                'email'    => 'required|email|max:150|unique:admins|unique:vendors',
                'password' => 'required|min:6',
                // 'shop_name' => 'required|string|max:150', // These might be in Step 2 (Channel Portal) not here?
                // 'shop_mobile' => 'required|numeric|digits_between:10,15',
                // 'business_license_number' => 'required|string|max:150',
                'accept'   => 'required'
            ], [
                'accept.required' => 'Please accept our Terms & Conditions',
                'email.unique'    => 'Email already exists for a vendor account',
                'mobile.unique'   => 'Mobile number already exists'
            ]);

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    // 1. Create Vendor
                    $vendor = new Vendor;
                    $vendor->name   = $data['name'];
                    $vendor->mobile = $data['mobile'];
                    $vendor->email  = $data['email'];
                    $vendor->status = 0; // Inactive until verified by Admin
                    $vendor->confirm = 'Yes'; // Auto-confirm email for Development
                    date_default_timezone_set('Asia/Seoul'); 
                    $vendor->created_at = date('Y-m-d H:i:s');
                    $vendor->updated_at = date('Y-m-d H:i:s');
                    $vendor->save();

                    $vendor_id = DB::getPdo()->lastInsertId();

                    // 2. Create Vendor Business Details (Placeholder for now)
                    // If the form doesn't have shop details yet, we create a placeholder or skip
                    if(isset($data['shop_name'])) {
                         DB::table('vendors_business_details')->insert([
                            'vendor_id' => $vendor_id,
                            'shop_name' => $data['shop_name'],
                            'shop_mobile' => $data['shop_mobile'] ?? '',
                            'business_license_number' => $data['business_license_number'] ?? '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                    // 3. Admin 생성 (유형: Vendor)
                    $admin = new Admin;
                    $admin->type      = 'vendor';
                    $admin->vendor_id = $vendor_id;
                    $admin->name      = $data['name'];
                    $admin->mobile    = $data['mobile'];
                    $admin->email     = $data['email'];
                    $admin->password  = bcrypt($data['password']);
                    $admin->status    = 0; // Inactive
                    $admin->confirm   = 'Yes'; // Auto-confirm email
                    $admin->created_at = date('Y-m-d H:i:s');
                    $admin->updated_at = date('Y-m-d H:i:s');
                    $admin->save();

                    DB::commit();

                    // 성공 응답 반환
                    $redirectTo = route('channel.login'); 
                    return response()->json([
                        'type'    => 'success',
                        'url'     => $redirectTo,
                        'message' => 'Vendor account created successfully! Please login to complete your profile.'
                    ]);

                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'type'    => 'error',
                        // 'errors'  => ['error' => [$e->getMessage()]]
                        'errors'  => ['email' => ['Registration failed: ' . $e->getMessage()]]
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

    // 사용자 로그인 (front/users/login_register.blade.php) AJAX 요청을 사용한 <form> 제출. front/js/custom.js 확인    
    public function userLogin(Request $request) {
        if ($request->ajax()) { // if the request is coming via an AJAX call
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)


            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators    
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Validation Rules
                'email'    => 'required|email|max:150|exists:users', // 'exists:users'    means it must already exist in the `users` table    // exists:table,column: https://laravel.com/docs/9.x/validation#rule-exists
                'password' => 'required|min:6'
            ]);


            // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages    
            // dd($validator->messages());
            // echo '<pre>', var_dump($validator->messages()), '</pre>';
            // exit;


            if ($validator->passes()) { // if validation passes (is successful), log the user in (but check first if they're inactive), and update the user's Cart (update the user's `user_id` column in `carts` table)
                // Log the user in
                if (Auth::attempt([ // Here, we use the Laravel's default 'web' Authentication Guard, whose 'Provider' is the User.php model i.e. `users` table    // Manually Authenticating Users: https://laravel.com/docs/9.x/authentication#other-authentication-methods
                    'email'    => $data['email'],   // $data['email']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'password' => $data['password'] // $data['password'] comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                ])) {
                    // First, check if the user being authenticated/logged in is inactive/disabled/deactivated by an admin (`status` is zero 0 in `users` table), logout the user, then return them back with a message
                    if (Auth::user()->status == 0) {
                        Auth::logout(); // logout the user

                        // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                        return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                            'type'    => 'inactive',
                            // 'message' => 'Your account is inactive. Please contact Admin'
                            'message' => 'Your account is not activated! Please confirm your account (by clicking on the Activation Link in the Confirmation Mail) to activate your account.'
                        ]);
                    }


                    // 사용자의 장바구니 업데이트 (`carts` 테이블의 `user_id` 컬럼) (로그인 전에는 세션으로만 저장됨) (Front/ProductsController.php의 cartAdd() 메서드 확인)    
                    if (!empty(Session::get('session_id'))) {
                        $user_id    = Auth::user()->id;
                        $session_id = Session::get('session_id');

                        Cart::where('session_id', $session_id)->update(['user_id' => $user_id]);
                    }

 
                    // 장바구니 페이지로 리다이렉트
                    $redirectTo = url('cart');

                    // AJAX 요청을 통한 응답이므로 JSON 반환
                    return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                        'type' => 'success',
                        'url'  => $redirectTo // 장바구니 페이지로 리다이렉트
                    ]);

                } else { // 유효성 검사는 통과했으나 로그인 정보가 틀린 경우
                    // AJAX 요청을 통해 HTML <form> 데이터를 제출하고 있으므로 JSON 응답을 반환함
                    return response()->json([ 
                        'type'    => 'incorrect',
                        'message' => '이메일 또는 비밀번호가 일치하지 않습니다!'
                    ]);
                }

            } else { // 유효성 검사 실패 시 유효성 검사 오류 메시지 전송
                // AJAX 요청을 통해 HTML <form> 데이터를 제출하고 있으므로 JSON 응답을 반환함
                return response()->json([ 
                    'type'   => 'error',
                    'errors' => $validator->messages() // jQuery를 사용하여 오류 메시지를 프론트엔드에 표시함 (front/js/custom.js 확인)    
                ]);
            }
        }
    }

    // 사용자 로그아웃 (이 라우트는 헤더의 로그아웃 탭에서 접근됨 (front/layout/header.blade.php))    
    public function userLogout() {
        Auth::logout(); 


        // 로그아웃 시 장바구니를 비우기 위해 세션 초기화
        Session::flush();


        return redirect('/');
    }



    // 사용자 계정 확인 이메일 (계정 활성화를 위한 '활성화 링크' 포함) (resources/views/emails/confirmation.blade.php, Mailtrap 사용)    
    public function confirmAccount($code) { // {code}는 이메일로 전송된 base64 인코딩된 계정 활성화 코드입니다.
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
                    'status' => 1
                ]);

                // Send a Welcome Email to user after confirmation (clicking on the 'Activation Link' inside the Confirmation Email)    // HELO / Mailtrap / MailHog: https://laravel.com/docs/9.x/mail#mailtrap    

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'name'   => $userDetails->name, // the user's name that they entered while submitting the registration form
                    'mobile' => $userDetails->mobile, // the user's mobile that they entered while submitting the registration form
                    'email'  => $email // the user's email that they entered while submitting the registration form
                    // 'code'   => base64_encode($data['email']) // We base64 code the user's $email and send it as a Route Parameter from user_confirmation.blade.php to the 'user/confirm/{code}' route in web.php, then it gets base64 decoded again in confirmUser() method in Front/UserController.php    // we will use the opposite: base64_decode() in the confirmAccount() method (encode X decode)
                ];
                \Illuminate\Support\Facades\Mail::send('emails.register', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.register' is the register.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that register.blade.php will use    // https://www.php.net/manual/en/functions.anonymous.php
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
    public function forgotPassword(Request $request) { // 비밀번호 찾기 기능 (GET 요청 시 페이지 렌더링, POST 요청 시 폼 제출 처리)
        if ($request->ajax()) { // AJAX를 통한 비밀번호 찾기 폼 제출인 경우 (front/users/forgot_password.blade.php)
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)
            // dd($data);


            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators    
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'email'    => 'required|email|max:150|exists:users', // 'exists:users'    means it must already exist in the `users` table    // exists:table,column: https://laravel.com/docs/9.x/validation#rule-exists

            ], [ // Customizing The Error Messages: https://laravel.com/docs/9.x/validation#manual-customizing-the-error-messages
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Custom Messages
                // 'accept.required' => 'Please accept our Terms & Conditions'
                'email.exists' => '공인된 이메일이 아닙니다.'
            ]);



            if ($validator->passes()) { // if validation passes (is successful), generate a new password for the user
                $new_password = \Illuminate\Support\Str::random(16);

                // Generate a new password
                // Change the current password immediately as the user forgot it, update it to a new random password, untill the user updates it by themselves
                User::where('email', $data['email'])->update([
                    'password' => bcrypt($new_password) // storing the HASH-ed password (not the original password) in the database    // bcrypt(): https://laravel.com/docs/9.x/helpers#method-bcrypt
                ]);

                // Get user details
                $userDetails = User::where('email', $data['email'])->first()->toArray();

                // Send an email to the user to get the new password (reset their password)    // HELO / Mailtrap / MailHog: https://laravel.com/docs/9.x/mail#mailtrap    
                $email = $data['email']; // the user's email that they entered while submitting the registration form

                // The email message data/variables that will be passed in to the email view
                $messageData = [
                    'name'     => $userDetails['name'], // the user's name that they entered while submitting the registration form
                    'email'    => $email, // the user's email that they entered while submitting the registration form
                    'password' => $new_password // the user's email that they entered while submitting the registration form
                    // 'code'  => base64_encode($data['email']) // We base64 code the user's $email and send it as a Route Parameter from user_confirmation.blade.php to the 'user/confirm/{code}' route in web.php, then it gets base64 decoded again in confirmUser() method in Front/UserController.php    // we will use the opposite: base64_decode() in the confirmUser() method (encode X decode)
                ];
                \Illuminate\Support\Facades\Mail::send('emails.user_forgot_password', $messageData, function ($message) use ($email) { // Sending Mail: https://laravel.com/docs/9.x/mail#sending-mail    // 'emails.user_forgot_password' is the resources/views/emails/user_forgot_password.blade.php file inside the 'resources/views/emails' folder that will be sent as an email    // We pass in all the variables that the user_forgot_password.blade.php file will use    // https://www.php.net/manual/en/functions.anonymous.php
                    $message->to($email)->subject('New Password - Multi-vendor E-commerce Application');
                });

                // Redirect user with a success message
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request. Check    $('#forgotForm').submit();    in front/js/custom.js
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type'    => 'success',
                    'message' => '등록한 이메일로 임시 비밀번호가 전송되었습니다.'
                ]);

            } else { // if validation fails (is unsuccessful), send the Validation Error Messages
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type'   => 'error',
                    'errors' => $validator->messages() // we'll loop over the Validation Errors Messages array using jQuery to show them in the frontend (check front/js/custom.js)    // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages    
                ]);
            }


        } else { // if the 'GET' request is coming from the <a> tag in front/users/login_register.blade.php, render the front/users/forgot_password.blade.php page
            return view('front.users.forgot_password');
        }

    }



    // 'GET' 요청으로 사용자 계정 페이지 렌더링 (front/users/user_account.blade.php), 또는 AJAX를 사용한 동일 페이지의 HTML 폼 제출 ('POST' 요청으로 사용자 상세 정보 업데이트). front/js/custom.js 확인    
    public function userAccount(Request $request) {
        if ($request->ajax()) { // AJAX를 통한 사용자 정보 변경 요청인 경우 (update user details)
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)


            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators    
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Validation Rules
                'name'    => 'required|string|max:100',
                'city'    => 'required|string|max:100',
                'state'   => 'required|string|max:100',
                'address' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'mobile'  => 'required|numeric|digits:11',
                'pincode' => 'required|digits:6',

            ] /*, [ // Customizing The Error Messages: https://laravel.com/docs/9.x/validation#manual-customizing-the-error-messages
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Custom Messages
                'accept.required' => 'Please accept our Terms & Conditions'
            ]*/ );


            // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages    
            // dd($validator->messages());
            // echo '<pre>', var_dump($validator->messages()), '</pre>';
            // exit;

            if ($validator->passes()) { // if validation passes (is successful), register (INSERT) the new user into the database `users` table, and log the user in IMMEDIATELY and AUTOMATICALLY and DIRECTLY, and redirect them to the Cart cart.blade.php page
                // Update user details in `users` table
                User::where('id', Auth::user()->id)->update([ // Retrieving The Authenticated User: https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user
                    'name'    => $data['name'],    // $data['name']       comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'mobile'  => $data['mobile'],  // $data['mobile']     comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'city'    => $data['city'],    // $data['city']       comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'state'   => $data['state'],   // $data['state']      comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'country' => $data['country'], // $data['country']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'pincode' => $data['pincode'], // $data['pincode']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    'address' => $data['address'], // $data['address']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                ]);

                // Redirect user back with a success message
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type'    => 'success',
                    // 'url'     => $redirectTo, // redirect user to the Cart cart.blade.php page
                    'message' => '연락처/결제 정보가 성공적으로 업데이트되었습니다!'
                ]);

            } else { // if validation fails (is unsuccessful), send the Validation Error Messages
                // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'type'   => 'error',
                    'errors' => $validator->messages() // we'll loop over the Validation Errors Messages array using jQuery to show them in the frontend (Check    $('#accountForm').submit();    in front/js/custom.js)    // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages    
                ]);
            }

        } else { // if it's a 'GET' request, render front/users/user_account.blade.php
            // Fetch all of the world countries from the database table `countries`
            $countries = \App\Models\Country::where('status', 1)->get()->toArray(); // get the countries which have status = 1 (to ignore the blacklisted countries, in case)


            return view('front.users.user_account')->with(compact('countries'));
        }
    }



    // AJAX를 통한 사용자 계정 비밀번호 업데이트 HTML 폼 제출. front/js/custom.js 확인    
    public function userUpdatePassword(Request $request) {
        if ($request->ajax()) { // AJAX를 통한 비밀번호 변경 요청인 경우 (update user details)
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)


            // Validation    // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators    
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Validation Rules
                'current_password'  => 'required',
                'new_password'     => 'required|min:6',
                'confirm_password' => 'required|min:6|same:new_password' // same:field: https://laravel.com/docs/9.x/validation#rule-same

            ] /*, [ // Customizing The Error Messages: https://laravel.com/docs/9.x/validation#manual-customizing-the-error-messages
                // the 'name' HTML attribute of the request (the array key of the $request array) (ATTRIBUTE) => Custom Messages
                'accept.required' => 'Please accept our Terms & Conditions'
            ]*/ );


            // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages    
            // dd($validator->messages());
            // echo '<pre>', var_dump($validator->messages()), '</pre>';
            // exit;

            if ($validator->passes()) { // if validation passes (is successful), update the user's current password
                $current_password = $data['current_password']; // $data['current_password']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                $checkPassword    = User::where('id', Auth::user()->id)->first();

                if (Hash::check($current_password, $checkPassword->password)) { // if the entered current password is correct, update the current password    // Confirming The Password: https://laravel.com/docs/9.x/authentication#confirming-the-password
                    // Update the user's current password to the new password
                    $user = User::find(Auth::user()->id);
                    $user->password = bcrypt($data['new_password']); // $data['new_password']    comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file
                    $user->save();

                    // Redirect user back with a success message
                    // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                    return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                        'type'    => 'success',
                        'message' => '비밀번호 변경이 정상적으로 처리되었습니다.'
                    ]);

                } else { // if the entered current password is incorrect/wrong, redirect with an error message
                    // Redirect user back with an error message
                    // Here, we return a JSON response because the request is ORIGINALLY submitting an HTML <form> data using an AJAX request
                    return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                        'type'    => 'incorrect',
                        'message' => '현재 비밀번호가 일치하지 않습니다.'
                    ]);
                }

            } else { // 유효성 검사 실패 시 유효성 검사 오류 메시지 전송
                // AJAX 요청을 통한 응답이므로 JSON 반환
                return response()->json([ 
                    'type'   => 'error',
                    'errors' => $validator->messages() // jQuery를 사용하여 오류 메시지를 프론트엔드에 표시함 (front/js/custom.js 확인)    
                ]);
            }

        }
    }



    public function findId()
    {
        return view('front.member.find_id');
    }

    public function findPw()
    {
        return view('front.member.find_pw');
    }

    public function withdrawSuccess()
    {
        return view('front.mypage.sub01.withdraw');
    }
}
