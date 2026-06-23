<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Auth without a namespace here works fine because the Admin.php model extends Authenticatable
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;
use Symfony\Component\VarDumper\VarDumper;

use App\Models\Admin;
use App\Models\Section;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Brand;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use App\Models\VendorsBankDetail;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function sub01() { return view('admin.sub.sub01'); }
    public function sub02() { return view('admin.sub.sub02'); }
    public function sub03() { return view('admin.sub.sub03'); }
    public function view() { return view('admin.sub.view'); }
    public function newpage() { return view('admin.sub.newpage'); }
    public function loading() { return view('admin.sub.loading'); }

    public function dashboard() {
        // Skydash 관리자 패널 사이드바의 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'dashboard');


        $sectionsCount   = Section::count();
        $categoriesCount = Category::count();
        $productsCount   = Product::count();
        $ordersCount     = Order::count();
        $couponsCount    = Coupon::count();
        $brandsCount     = Brand::count();
        $usersCount      = User::count();


        return view('admin/dashboard')->with(compact('sectionsCount', 'categoriesCount', 'productsCount', 'ordersCount', 'couponsCount', 'brandsCount', 'usersCount')); 
    }

    public function login(Request $request) { // 'admin' 가드를 사용한 로그인 (판매자 또는 관리자)
        if (Auth::guard('admin')->check()) {
             // 이미 로그인된 경우 유형에 따라 리다이렉트
             if (Auth::guard('admin')->user()->type == 'vendor') {
                 return redirect()->route('channel.index');
             }
             return redirect('/admin/dashboard');
        }
        if ($request->isMethod('post')) {
            $data = $request->all();

            // Validation
            $rules = [
                'email'    => 'required|email|max:255',
                'password' => 'required',
            ];

            $customMessages = [
                'email.required'    => '이메일 주소를 입력해 주세요!',
                'email.email'       => '유효한 이메일 주소를 입력해 주세요',
                'password.required' => '비밀번호를 입력해 주세요!',
            ];

            $this->validate($request, $rules, $customMessages);

            // 인증 진행
            if (Auth::guard('admin')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
                $user = Auth::guard('admin')->user();

                if ($user->type == 'vendor') {
                     // 이메일 인증 여부 확인
                    if ($user->confirm == 'No') {
                        Auth::guard('admin')->logout();
                        return redirect()->back()->with('error_message', '판매자 계정 활성화를 위해 이메일 인증을 완료해 주세요');
                    }
                    // 판매자 로그인 성공 -> 채널 페이지로 리다이렉트
                    return redirect()->route('channel.index');

                } else {
                    // 일반 관리자 로그인
                    if ($user->status == '0') {
                        Auth::guard('admin')->logout();
                        return redirect()->back()->with('error_message', '관리자 계정이 비활성 상태입니다');
                    }
                    // 관리자 로그인 성공 -> 관리자 대시보드로 리다이렉트
                    return redirect()->intended('/admin/dashboard');
                }

            } else { // 로그인 정보가 일치하지 않는 경우
                return redirect()->back()->with('error_message', '이메일 또는 비밀번호가 일치하지 않습니다');
            }
        }

        return view('admin/login');
    }

    public function logout() {
        Auth::guard('admin')->logout(); 
        return redirect('admin/login');
    }

    public function updateAdminPassword(Request $request) {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'update_admin_password');


        // 비밀번호 변경 폼 제출 처리 (POST 요청)
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);


            // 현재 관리자 비밀번호가 일치하는지 확인
            if (Hash::check($data['current_password'], Auth::guard('admin')->user()->password)) { 
                // 새 비밀번호와 확인 비밀번호가 일치하는지 확인
                if ($data['confirm_password'] == $data['new_password']) {
                    Admin::where('id', Auth::guard('admin')->user()->id)->update([ 
                        'password' => bcrypt($data['new_password'])
                    ]); 

                    return redirect()->back()->with('success_message', '관리자 비밀번호가 성공적으로 업데이트되었습니다!');

                } else { // 새 비밀번호와 확인 비밀번호가 일치하지 않는 경우
                    return redirect()->back()->with('error_message', '새 비밀번호와 확인 비밀번호가 일치하지 않습니다!');
                }
            } else {
                return redirect()->back()->with('error_message', '현재 관리자 비밀번호가 일치하지 않습니다!');
            }
        }


        $adminDetails = Admin::where('email', Auth::guard('admin')->user()->email)->first()->toArray(); // 'Admin' is the Admin.php model    // Auth::guard('admin') is the authenticated user using the 'admin' guard we created in auth.php    // https://laravel.com/docs/9.x/eloquent#retrieving-models    // Accessing Specific Guard Instances: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances


        return view('admin/settings/update_admin_password')->with(compact('adminDetails'));
    }

    public function checkAdminPassword(Request $request) { // admin/js/custom.js의 AJAX 호출을 통해 실행됨
        $data = $request->all();
        // dd($data);


        // Hashing Passwords: https://laravel.com/docs/9.x/hashing#hashing-passwords
        if (Hash::check($data['current_password'], Auth::guard('admin')->user()->password)) { 
            return 'true';
        } else {
            return 'false';
        }
    }

    public function updateAdminDetails(Request $request) { // update_admin_details.blade.php
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'update_admin_details');


        if ($request->isMethod('post')) { // 업데이트 폼 제출 시
            $data = $request->all();
            // dd($data);

            // Laravel's Validation
            // Customizing Laravel's Validation Error Messages: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // Customizing Validation Rules: https://laravel.com/docs/9.x/validation#custom-validation-rules
            $rules = [
                'admin_name'   => 'required|regex:/^[\pL\s\-]+$/u', // only alphabetical characters and spaces
                'admin_mobile' => 'required|numeric',
            ];

            $customMessages = [ 
                'admin_name.required'   => '이름을 입력해 주세요',
                'admin_name.regex'      => '유효한 이름을 입력해 주세요',
                'admin_mobile.required' => '휴대폰 번호를 입력해 주세요',
                'admin_mobile.numeric'  => '유효한 휴대폰 번호를 입력해 주세요',
            ];

            $this->validate($request, $rules, $customMessages);



            // 관리자 사진 업로드 (Intervention 패키지 사용)
            if ($request->hasFile('admin_image')) { 
                $image_tmp = $request->file('admin_image');

                if ($image_tmp->isValid()) {
                    // 이미지 확장자 가져오기
                    $extension = $image_tmp->getClientOriginalExtension();

                    // 업로드된 이미지의 중복 방지를 위해 랜덤 이름 생성
                    $imageName = rand(111, 99999) . '.' . $extension;

                    // 'public' 폴더 내 업로드 경로 지정
                    $imagePath = 'admin/images/photos/' . $imageName;

                    // Intervention 패키지를 사용하여 이미지 업로드 및 저장
                    Image::make($image_tmp)->save($imagePath); 
                }

            } else if (!empty($data['current_admin_image'])) { // 새 이미지를 업로드하지 않았지만 기존 이미지가 있는 경우
                $imageName = $data['current_admin_image'];
            } else { // 새 이미지를 업로드하지 않았고 기존 이미지도 없는 경우
                $imageName = '';
            }


            // 관리자 세부 정보 업데이트
            Admin::where('id', Auth::guard('admin')->user()->id)->update([ 
                'name'   => $data['admin_name'],
                'mobile' => $data['admin_mobile'],
                'image'  => $imageName
            ]); 
            
            return redirect()->back()->with('success_message', '관리자 정보가 성공적으로 업데이트되었습니다!');
        }


        return view('admin/settings/update_admin_details');
    }

    public function updateVendorDetails($slug, Request $request) { // $slug: 'personal', 'business', 'bank' 중 하나
        if ($slug == 'personal') {
            // 사이드바 활성 페이지 설정을 위해 세션 사용
            Session::put('page', 'update_personal_details');


            // 판매자 개인 정보 업데이트 폼 제출 처리
            if ($request->isMethod('post')) { // 폼 제출 시
                $data = $request->all();
                // dd($data);

                // Laravel's Validation    // Customizing Laravel's Validation Error Messages: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // Customizing Validation Rules: https://laravel.com/docs/9.x/validation#custom-validation-rules
                $rules = [
                    'vendor_name'   => 'required|regex:/^[\pL\s\-]+$/u', // only alphabetical characters and spaces
                    'vendor_city'   => 'required|regex:/^[\pL\s\-]+$/u', // only alphabetical characters and spaces
                    'vendor_mobile' => 'required|numeric',
                ];

                $customMessages = [ 
                    'vendor_name.required'   => '이름을 입력해 주세요',
                    'vendor_city.required'   => '도시를 입력해 주세요',
                    'vendor_city.regex'      => '유효한 도시 이름을 입력해 주세요',
                    'vendor_name.regex'      => '유효한 이름을 입력해 주세요',
                    'vendor_mobile.required' => '휴대폰 번호를 입력해 주세요',
                    'vendor_mobile.numeric'  => '유효한 휴대폰 번호를 입력해 주세요',
                ];

                $this->validate($request, $rules, $customMessages);


                // 판매자 사진 업로드 (Intervention 패키지 사용)
                if ($request->hasFile('vendor_image')) { 
                    $image_tmp = $request->file('vendor_image');

                    if ($image_tmp->isValid()) {
                        // 이미지 확장자 가져오기
                        $extension = $image_tmp->getClientOriginalExtension();

                        // 이미지 중복 방지를 위해 랜덤 이름 생성
                        $imageName = rand(111, 99999) . '.' . $extension;

                        // 업로드 경로 지정
                        $imagePath = 'admin/images/photos/' . $imageName;

                        // 이미지 업로드 및 저장
                        Image::make($image_tmp)->save($imagePath); 
                    }

                } else if (!empty($data['current_vendor_image'])) { // 새 이미지를 업로드하지 않았지만 기존 이미지가 있는 경우
                    $imageName = $data['current_vendor_image'];
                } else { // 새 이미지를 업로드하지 않았고 기존 이미지도 없는 경우
                    $imageName = '';
                }


                // 판매자 정보는 admins 및 vendors 테이블 모두에서 업데이트되어야 함:
                // admins 테이블의 판매자 정보 업데이트
                Admin::where('id', Auth::guard('admin')->user()->id)->update([ 
                    'name'   => $data['vendor_name'],
                    'mobile' => $data['vendor_mobile'],
                    'image'  => $imageName
                ]); 

                // vendors 테이블의 판매자 정보 업데이트
                Vendor::where('id', Auth::guard('admin')->user()->vendor_id)->update([ 
                    'name'    => $data['vendor_name'],
                    'mobile'  => $data['vendor_mobile'],
                    'address' => $data['vendor_address'],
                    'city'    => $data['vendor_city'],
                    'state'   => $data['vendor_state'],
                    'country' => $data['vendor_country'],
                    'pincode' => $data['vendor_pincode'],
                ]);


                return redirect()->back()->with('success_message', '판매자 정보가 성공적으로 업데이트되었습니다!');
            }


            $vendorDetails = Vendor::where('id', Auth::guard('admin')->user()->vendor_id)->first()->toArray(); 

        } else if ($slug == 'business') {
            // 사이드바 활성 페이지 설정을 위해 세션 사용
            Session::put('page', 'update_business_details');


            if ($request->isMethod('post')) { // if the <form> is submitted
                $data = $request->all();
                // dd($data);

                // Laravel's Validation    // Customizing Laravel's Validation Error Messages: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // Customizing Validation Rules: https://laravel.com/docs/9.x/validation#custom-validation-rules
                $rules = [
                    'shop_name'           => 'required|regex:/^[\pL\s\-]+$/u', // only alphabetical characters and spaces
                    'shop_city'           => 'required|regex:/^[\pL\s\-]+$/u', // only alphabetical characters and spaces
                    'shop_mobile'         => 'required|numeric',
                    'address_proof'       => 'required',
                ];

                $customMessages = [ 
                    'shop_name.required'           => '상점 이름을 입력해 주세요',
                    'shop_city.required'           => '도시를 입력해 주세요',
                    'shop_city.regex'              => '유효한 도시 이름을 입력해 주세요',
                    'shop_name.regex'              => '유효한 상점 이름을 입력해 주세요',
                    'shop_mobile.required'         => '휴대폰 번호를 입력해 주세요',
                    'shop_mobile.numeric'          => '유효한 휴대폰 번호를 입력해 주세요',
                ];

                $this->validate($request, $rules, $customMessages);


                // 주소 증빙 서류 업로드 (Intervention 패키지 사용)
                if ($request->hasFile('address_proof_image')) { 
                    $image_tmp = $request->file('address_proof_image');

                    if ($image_tmp->isValid()) {
                        // 이미지 확장자 가져오기
                        $extension = $image_tmp->getClientOriginalExtension();

                        // 중복 방지를 위해 랜덤 이름 생성
                        $imageName = rand(111, 99999) . '.' . $extension;

                        // 업로드 경로 지정
                        $imagePath = 'admin/images/proofs/' . $imageName;

                        // 이미지 업로드 및 저장
                        Image::make($image_tmp)->save($imagePath); 
                    }

                } else if (!empty($data['current_address_proof'])) { // 새 이미지를 업로드하지 않았지만 기존 이미지가 있는 경우
                    $imageName = $data['current_address_proof'];
                } else { // 새 이미지를 업로드하지 않았고 기존 이미지도 없는 경우
                    $imageName = '';
                }


                $vendorCount = VendorsBusinessDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->count(); 
                if ($vendorCount > 0) { // 이미 판매자 정보가 존재하는 경우 업데이트
                    // `vendors_business_details` 테이블 업데이트
                    VendorsBusinessDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->update([ 
                        'shop_name'               => $data['shop_name'],
                        'shop_mobile'             => $data['shop_mobile'],
                        'shop_website'            => $data['shop_website'],
                        'shop_address'            => $data['shop_address'],
                        'shop_city'               => $data['shop_city'],
                        'shop_state'              => $data['shop_state'],
                        'shop_country'            => $data['shop_country'],
                        'shop_pincode'            => $data['shop_pincode'],
                        'business_license_number' => $data['business_license_number'],
                        'gst_number'              => $data['gst_number'],
                        'pan_number'              => $data['pan_number'],
                        'address_proof'           => $data['address_proof'],
                        'address_proof_image'     => $imageName,
                    ]);

                } else { // 판매자 정보가 없는 경우 새로 삽입
                    // `vendors_business_details` 테이블에 삽입
                    VendorsBusinessDetail::insert([
                        'vendor_id'               => Auth::guard('admin')->user()->vendor_id, 
                        'shop_name'               => $data['shop_name'],
                        'shop_mobile'             => $data['shop_mobile'],
                        'shop_website'            => $data['shop_website'],
                        'shop_address'            => $data['shop_address'],
                        'shop_city'               => $data['shop_city'],
                        'shop_state'              => $data['shop_state'],
                        'shop_country'            => $data['shop_country'],
                        'shop_pincode'            => $data['shop_pincode'],
                        'business_license_number' => $data['business_license_number'],
                        'gst_number'              => $data['gst_number'],
                        'pan_number'              => $data['pan_number'],
                        'address_proof'           => $data['address_proof'],
                        'address_proof_image'     => $imageName,
                    ]);
                }


                return redirect()->back()->with('success_message', '판매자 정보가 성공적으로 업데이트되었습니다!');
            }


            $vendorCount = VendorsBusinessDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->count(); 

            if ($vendorCount > 0) {
                $vendorDetails = VendorsBusinessDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->first()->toArray(); 
            } else {
                $vendorDetails = array();
            }

        } else if ($slug == 'bank') {
            // 사이드바 활성 페이지 설정을 위해 세션 사용
            Session::put('page', 'update_bank_details');


            if ($request->isMethod('post')) { // 폼 제출 시
                $data = $request->all();
                // dd($data);

                // Laravel's Validation    // Customizing Laravel's Validation Error Messages: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // Customizing Validation Rules: https://laravel.com/docs/9.x/validation#custom-validation-rules
                $rules = [
                    'account_holder_name' => 'required|regex:/^[\pL\s\-]+$/u', // only alphabetical characters and spaces
                    'bank_name'           => 'required', // only alphabetical characters and spaces
                    'account_number'      => 'required|numeric',
                    'bank_ifsc_code'      => 'required',
                ];

                $customMessages = [ 
                    'account_holder_name.required' => '예금주명을 입력해 주세요',
                    'bank_name.required'           => '은행명을 입력해 주세요',
                    'account_holder_name.regex'    => '유효한 예금주명을 입력해 주세요',
                    'account_number.required'      => '계좌번호를 입력해 주세요',
                    'account_number.numeric'       => '유효한 계좌번호를 입력해 주세요',
                    'bank_ifsc_code.required'      => '은행 IFSC 코드를 입력해 주세요',
                ];

                $this->validate($request, $rules, $customMessages);


                $vendorCount = VendorsBankDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->count(); 
                if ($vendorCount > 0) { // 이미 정보가 존재하는 경우 업데이트
                    // `vendors_bank_details` 테이블 업데이트
                    VendorsBankDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->update([ 
                        'account_holder_name' => $data['account_holder_name'],
                        'bank_name'           => $data['bank_name'],
                        'account_number'      => $data['account_number'],
                        'bank_ifsc_code'      => $data['bank_ifsc_code'],
                    ]);

                } else { // 판매자 정보가 없는 경우 새로 삽입
                    // `vendors_bank_details` 테이블에 삽입
                    VendorsBankDetail::insert([
                        'vendor_id'           => Auth::guard('admin')->user()->vendor_id, 
                        'account_holder_name' => $data['account_holder_name'],
                        'bank_name'           => $data['bank_name'],
                        'account_number'      => $data['account_number'],
                        'bank_ifsc_code'      => $data['bank_ifsc_code'],
                    ]);
                }


                return redirect()->back()->with('success_message', '판매자 정보가 성공적으로 업데이트되었습니다!');
            }


            $vendorCount = VendorsBankDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->count();
            if ($vendorCount > 0) {
                $vendorDetails = VendorsBankDetail::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->first()->toArray();
            } else {
                $vendorDetails = array();
            }

        }


        // Fetch all of the world countries from the database table `countries`
        $countries = Country::where('status', 1)->get()->toArray(); // get the countries which have `status` = 1 (to ignore the blacklisted countries, in case)
        // dd($countries);


        // The 'GET' request: to show the update_vendor_details.blade.php page
        // We'll create one view (not 3) for the 3 pages, but parts inside it will change depending on the $slug value
        return view('admin/settings/update_vendor_details')->with(compact('slug', 'vendorDetails', 'countries'));
    }

    // Update the vendor's commission percentage (by the Admin) in `vendors` table (for every vendor on their own) in the Admin Panel in admin/admins/view_vendor_details.blade.php (Commissions module: Every vendor must pay a certain commission (that may vary from a vendor to another) for the website owner (admin) on every item sold, and it's defined by the website owner (admin))
    public function updateVendorCommission(Request $request) {
        if ($request->isMethod('post')) { // if the HTML Form is submitted (in admin/admins/view_vendor_details.blade.php)
            $data = $request->all();
            // dd($data);

            // `vendors` 테이블의 `commission` 퍼센트 업데이트
            Vendor::where('id', $data['vendor_id'])->update(['commission' => $data['commission']]);


            return redirect()->back()->with('success_message', '판매자 수수료가 성공적으로 업데이트되었습니다!');
        }
    }

    public function updateVendorCertification(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            // vendors 테이블 업데이트
            Vendor::where('id', $data['vendor_id'])->update([
                'status' => $data['seller_status'],
            ]);

            // admins 테이블도 같이 업데이트 (로그인 및 권한 연동을 위해)
            Admin::where('vendor_id', $data['vendor_id'])->update([
                'status' => $data['seller_status'],
            ]);

            return redirect()->back()->with('success_message', '판매 인증 상태가 성공적으로 업데이트되었습니다!');
        }
    }

    public function admins(Request $request, $type = null) { // $type is the `type` column in the `admins` which can only be: superadmin, admin, subadmin or vendor    // A default value of null (to allow not passing a {type} slug, and in this case, the page will view ALL of the superadmin, admins, subadmins and vendors at the same time)
        $query = Admin::query();

        // Type Filter (from URL parameter or Search)
        if (!empty($type)) {
            $query->where('type', $type);
            $title = ucfirst($type) . 's';
            Session::put('page', 'view_' . strtolower($title));
        } else {
            $title = 'Admins/Subadmins/Vendors';
            Session::put('page', 'view_all');
        }

        // Search Filter
        if ($request->has('search_value') && $request->search_value != '') {
            $search_value = $request->search_value;
            // Search in multiple fields if type not specified in search (Url Type is handled above)
            $query->where(function($q) use ($search_value) {
                $q->where('name', 'like', '%' . $search_value . '%')
                  ->orWhere('email', 'like', '%' . $search_value . '%')
                  ->orWhere('mobile', 'like', '%' . $search_value . '%');
            });
        }
        
        // Specific Type Search (overrides URL type if both present, though usually exclusive)
        if ($request->has('type') && $request->type != '') {
             $query->where('type', $request->type);
        }

        // Status Filter
        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status', $request->status);
        }

        $admins = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('admin/admins/admins')->with(compact('admins', 'title'));
    }

    public function viewVendorDetails($id) { // View further 'vendor' details inside Admin Management table (if the authenticated user is superadmin, admin or subadmin)
    Session::put('page', 'view_vendor_details');
        $vendorDetails = Admin::with('vendorPersonal', 'vendorBusiness','vendorBank')->where('id', $id)->first(); // Using the relationship defined in the Admin.php model to be able to get data from `vendors`, `vendors_business_details` and `vendors_bank_details` tables
        $vendorDetails = json_decode(json_encode($vendorDetails), true); // We used json_decode(json_encode($variable), true) to convert $vendorDetails to an array instead of Laravel's toArray() method
        // dd($vendorDetails);

        return view('admin/admins/view_vendor_details')->with(compact('vendorDetails'));
    }

    public function updateAdminStatus(Request $request) { // Update Admin Status using AJAX in admins.blade.php
        if ($request->ajax()) { // if the request is coming via an AJAX call
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)
            // dd($data);

            if ($data['status'] == 'Active' || $data['status'] == '활성') { // $data['status'] comes from the 'data' object inside the $.ajax() method    // reverse the 'status' from (ative/inactive) 0 to 1 and 1 to 0 (and vice versa)
                $status = 0;
            } else {
                $status = 1;
            }


            // Note: Vendor CONFIRMATION occurs automatically through vendor clicking on the confirmation link sent in the email, but vendor ACTIVATION (active/inactive/disabled) occurs manually where 'superadmin' or 'admin' activates the `status` from the Admin Panel in 'Admin Management' tab, then clicks Status. Also, Vendor CONFIRMATION is related to the `confirm` columns in BOTH `admins` and `vendors` tables, but vendor ACTIVATION (active/inactive/disabled) is related to the `status` columns in BOTH `admins` and `vendors` tables!
            // Note: Vendor receives THREE emails: the first one when they register (please click on the confirmation link mail (in emails/vendor_confirmation.blade.php)), the second one when they click on the confirmation link sent in the first email (telling them that they have been confirmed and asking them to complete filling in their personal, business and bank details to get ACTIVATED/APPROVED (`status gets 1) (in emails/vendor_confirmed.blade.php)), the third email when the 'admin' or 'superadmin' manually activates (`status` becomes 1) the vendor from the Admin Panel from 'Admin Management' tab, then clicks Status (the email tells them they have been approved (activated and `status` became 1) and asks them to add their products on the website (in emails/vendor_approved.blade.php))

            // (!! Database Transaction !!) UPDATE the `status` columns in BOTH `admins` and `vendors` tables (I did the code of `vendors` myself!) (!! Database Transaction !!)
            Admin::where('id', $data['admin_id'])->update(['status' => $status]); // $data['admin_id'] comes from the 'data' object inside the $.ajax() method
            // echo '<pre>', var_dump($data), '</pre>';

            // Send a THIRD Approval Email to the vendor when the superadmin or admin approves their account (`status` column in the `admins` table becomes 1 instead of 0) so that they can add their products on the website now
            $adminDetails = Admin::where('id', $data['admin_id'])->first()->toArray(); // get the admin that his `status` has been approved


            if ($adminDetails['type'] == 'vendor' && $status == 1) { // 판매자 유형이고 상태가 활성화(1)로 변경된 경우 세 번째 확인 메일 발송
                Vendor::where('id', $adminDetails['vendor_id'])->update(['status' => $status]); // vendors 테이블의 판매자 상태 업데이트

                // 판매자 계정 승인 완료 이메일 발송
                $email = $adminDetails['email']; 

                // 이메일 뷰에 전달할 데이터
                $messageData = [
                    'email'  => $adminDetails['email'],
                    'name'   => $adminDetails['name'],
                    'mobile' => $adminDetails['mobile'],
                ];
                
                // Mail sending might fail if not configured, use try-catch or just proceed
                try {
                    \Illuminate\Support\Facades\Mail::send('emails.vendor_approved', $messageData, function ($message) use ($email) { 
                        $message->to($email)->subject('판매자 계정이 승인되었습니다');
                    });
                } catch (\Exception $e) {
                    // Log error but proceed
                }
            }

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'   => $status,
                'admin_id' => $data['admin_id']
            ]);
        }
    }

    public function addEditAdmin(Request $request, $id = null)
    {
        Session::put('page', 'add_edit_admin');
        if ($id == "") {
            $title = "관리자/판매자 등록";
            $admin = new Admin;
            $admin->type = 'vendor'; // Default to vendor to show sections
            $message = "관리자/판매자가 성공적으로 등록되었습니다!";
        } else {
            $title = "관리자/판매자 수정";
            $admin = Admin::with(['vendorPersonal', 'vendorBusiness', 'vendorBank'])->find($id);
            $message = "관리자/판매자 정보가 성공적으로 수정되었습니다!";
        }

        if ($request->isMethod('post')) {
            $data = $request->all();
            
            // Combine mobile number
            if (!empty($data['mobile_1']) && !empty($data['mobile_2']) && !empty($data['mobile_3'])) {
                $data['mobile'] = $data['mobile_1'] . '-' . $data['mobile_2'] . '-' . $data['mobile_3'];
            }

            $rules = [
                'name' => 'required|regex:/^[\pL\s\-]+$/u',
                'mobile' => 'required',
                'email' => 'required|email|unique:admins,email,' . $id,
                'type' => 'required',
            ];

            if ($id == "") {
                $rules['password'] = 'required|min:6';
            }

            $this->validate($request, $rules);

            // Image Upload
            if ($request->hasFile('image')) {
                $image_tmp = $request->file('image');
                if ($image_tmp->isValid()) {
                    $extension = $image_tmp->getClientOriginalExtension();
                    $imageName = rand(111, 99999) . '.' . $extension;
                    $imagePath = 'admin/images/photos/' . $imageName;
                    Image::make($image_tmp)->save($imagePath);
                    $admin->image = $imageName;
                }
            }

            if ($data['type'] == 'vendor') {
                // Combine business license number
                $license_number = "";
                if (!empty($data['business_license_1']) && !empty($data['business_license_2']) && !empty($data['business_license_3'])) {
                    $license_number = $data['business_license_1'] . '-' . $data['business_license_2'] . '-' . $data['business_license_3'];
                }

                \Illuminate\Support\Facades\DB::beginTransaction();
                try {
                    if ($id == "") {
                        // 1. Create Vendor (Minimal)
                        $vendor = new Vendor;
                        $vendor->name = $data['name'];
                        $vendor->mobile = $data['mobile'];
                        $vendor->email = $data['email'];
                        $vendor->status = $data['seller_status'] ?? 0;
                        $vendor->save();
                        $vendor_id = $vendor->id;

                        // 2. Create Admin
                        $admin->vendor_id = $vendor_id;
                        $admin->type = 'vendor';
                        $admin->name = $data['name'];
                        $admin->mobile = $data['mobile'];
                        $admin->email = $data['email'];
                        $admin->password = bcrypt($data['password']);
                        $admin->status = $data['status'] ?? 0;
                        $admin->save();
                    } else {
                        // Update Admin
                        $admin->name = $data['name'];
                        $admin->mobile = $data['mobile'];
                        $admin->email = $data['email'];
                        $admin->status = $data['status'] ?? 1;
                        if (!empty($data['password'])) {
                            $admin->password = bcrypt($data['password']);
                        }
                        $admin->save();

                        // Update Vendor status
                        Vendor::where('id', $admin->vendor_id)->update([
                            'status' => $data['seller_status'] ?? 0,
                            'name' => $data['name'],
                            'mobile' => $data['mobile']
                        ]);
                        $vendor_id = $admin->vendor_id;
                    }

                    // 3. Handle Business Details (Step 2)
                    $businessData = [
                        'shop_name' => $data['shop_name'] ?? '',
                        'shop_business_type' => $data['shop_business_type'] ?? '',
                        'business_license_number' => $license_number,
                        'shop_mobile' => $data['shop_mobile'] ?? '',
                        'shop_pincode' => $data['zipcode'] ?? '',
                        'shop_address' => $data['address1'] ?? '',
                        'shop_address_detail' => $data['address2'] ?? '',
                    ];

                    if ($request->hasFile('address_proof_image')) {
                        $img = $request->file('address_proof_image');
                        if ($img->isValid()) {
                            $imgName = 'license_' . rand(111, 99999) . '.' . $img->getClientOriginalExtension();
                            $img->move('front/images/bank_copies/', $imgName);
                            $businessData['address_proof_image'] = $imgName;
                        }
                    }

                    VendorsBusinessDetail::updateOrCreate(['vendor_id' => $vendor_id], $businessData);

                    // 4. Handle Bank Details (Step 3)
                    $bankData = [
                        'bank_name' => $data['bank_name'] ?? '',
                        'account_number' => $data['account_number'] ?? '',
                        'account_holder_name' => $data['account_holder_name'] ?? '',
                    ];

                    if ($request->hasFile('bank_copy_image')) {
                        $img = $request->file('bank_copy_image');
                        if ($img->isValid()) {
                            $imgName = 'bankbook_' . rand(111, 99999) . '.' . $img->getClientOriginalExtension();
                            $img->move('front/images/bank_copies/', $imgName);
                            $bankData['bank_copy_image'] = $imgName;
                        }
                    }

                    VendorsBankDetail::updateOrCreate(['vendor_id' => $vendor_id], $bankData);

                    \Illuminate\Support\Facades\DB::commit();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollback();
                    return redirect()->back()->with('error_message', '데이터 처리 중 오류 발생: ' . $e->getMessage());
                }
            } else {
                // Regular Admin/Subadmin
                $admin->name = $data['name'];
                $admin->mobile = $data['mobile'];
                $admin->email = $data['email'];
                $admin->type = $data['type'];
                $admin->status = $data['status'] ?? 1;

                if (!empty($data['password'])) {
                    $admin->password = bcrypt($data['password']);
                }
                $admin->save();
            }

            return redirect('admin/admins')->with('success_message', $message);
        }
        
        return view('admin.admins.add_edit_admin')->with(compact('title', 'admin'));
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::find($id);
        if ($admin->type == 'vendor') {
            // Delete associated vendor data
            // Should probably use SoftDeletes in real world, but for now hard delete
            Vendor::where('id', $admin->vendor_id)->delete();
            VendorsBusinessDetail::where('vendor_id', $admin->vendor_id)->delete();
            VendorsBankDetail::where('vendor_id', $admin->vendor_id)->delete();
        }
        $admin->delete();
        
        return redirect()->back()->with('success_message', 'Admin/Vendor deleted successfully!');
    }

    public function layerLarge() {
        return view('admin.sub.layer_large');
    }
}
