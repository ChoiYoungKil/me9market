<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\Coupon;



class CouponsController extends Controller
{
    // 관리자 패널의 쿠폰 목록 페이지 렌더링
    public function coupons() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'coupons');


        // 입점업체인 경우 본인의 쿠폰만 표시하며, 계정이 활성화된 상태인지 확인합니다.
        $adminType = Auth::guard('admin')->user()->type; 
        $vendor_id = Auth::guard('admin')->user()->vendor_id; 

        if ($adminType == 'vendor') { // 로그인한 사용자가 'vendor'인 경우 상태 확인
            $vendorStatus = Auth::guard('admin')->user()->status; 
            // dd($vendorStatus);
            if ($vendorStatus == 0) { // 입점업체 계정이 비활성화된 경우
                return redirect('admin/update-vendor-details/personal')->with('error_message', '입점업체 계정이 아직 승인되지 않았습니다. 개인, 사업자 및 은행 정보를 정확히 입력해 주세요.'); 
            }

            $coupons = Coupon::where('vendor_id', $vendor_id)->get()->toArray(); // 본인의 쿠폰만 가져오기

        } else { // 관리자인 경우
            $coupons = Coupon::get()->toArray();
            // dd($coupons);
        }


        return view('admin.coupons.coupons')->with(compact('coupons'));
    }

    // AJAX를 통한 쿠폰 상태 업데이트
    public function updateCouponStatus(Request $request) {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Coupon::where('id', $data['coupon_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'   => $status,
                'coupon_id' => $data['coupon_id']
            ]);
        }
    }

    // AJAX를 통한 쿠폰 삭제
    public function deleteCoupon($id) {
        Coupon::where('id', $id)->delete();

        $message = '쿠폰이 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    // 쿠폰 추가 또는 수정 페이지 렌더링 및 폼 제출 처리
    public function addEditCoupon(Request $request, $id = null) { 
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'coupons');


        if ($id == '') { // $id가 없으면 쿠폰 추가
            // 쿠폰 추가
            $title = '쿠폰 추가';
            $coupon = new Coupon;
            // dd($coupon);

            $selCats   = array();
            $selBrands = array();
            $selUsers  = array();

            $message = '쿠폰이 성공적으로 추가되었습니다!';

        } else { // $id가 있으면 쿠폰 수정
            // 쿠폰 수정
            $title = '쿠폰 수정';
            $coupon = Coupon::find($id);
            // dd($coupon);

            $selCats   = explode(',', $coupon['categories']); // 선택된 카테고리
            $selBrands = explode(',', $coupon['brands']);     // 선택된 브랜드
            $selUsers  = explode(',', $coupon['users']);      // 선택된 사용자

            $message = '쿠폰이 성공적으로 업데이트되었습니다!';
        }



        if ($request->isMethod('post')) { // HTML 폼이 제출된 경우 (추가 또는 수정!)
            $data = $request->all();
            // dd($data);


            // 라라벨 유효성 검사    // 사용자 정의 오류 메시지: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // 사용자 정의 유효성 검사 규칙: https://laravel.com/docs/9.x/validation#custom-validation-rules
            $rules = [
                'categories'    => 'required',
                'brands'        => 'required',
                'coupon_option' => 'required',
                'coupon_type'   => 'required',
                'amount_type'   => 'required',
                'amount'        => 'required|numeric',
                'expiry_date'   => 'required'
            ];

            $customMessages = [ 
                'categories.required'    => '카테고리를 선택해 주세요',
                'brands.required'        => '브랜드를 선택해 주세요',
                'coupon_option.required' => '쿠폰 옵션을 선택해 주세요',
                'coupon_type.required'   => '쿠폰 유형을 선택해 주세요',
                'amount_type.required'   => '금액 유형을 선택해 주세요',
                'amount.required'        => '금액을 입력해 주세요',
                'amount.numeric'         => '유효한 금액을 입력해 주세요',
                'expiry_date.required'   => '만료일을 입력해 주세요',
            ];

            $this->validate($request, $rules, $customMessages);



            if (isset($data['categories'])) {
                $categories = implode(',', $data['categories']);
            } else {
                $categories = '';
            }

            if (isset($data['brands'])) {
                $brands = implode(',', $data['brands']);
            } else {
                $brands = '';
            }

            if (isset($data['users'])) {
                $users = implode(',', $data['users']);
            } else {
                $users = '';
            }


            // 'Automatic'인 경우 랜덤 쿠폰 코드를 생성하고, 'Manual'인 경우 입력된 코드를 사용합니다.
            if ($data['coupon_option'] == 'Automatic') {
                $coupon_code = \Illuminate\Support\Str::random(8); 
            } else { // 관리자나 입점업체가 직접 입력한 코드를 사용
                $coupon_code = $data['coupon_code'];
            }


            $adminType = Auth::guard('admin')->user()->type; // 현재 인증된 사용자의 'type' 가져오기 (admin/vendor)    // 특정 Guard 인스턴스 접근: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances
            if ($adminType == 'vendor') {
                $coupon->vendor_id = Auth::guard('admin')->user()->vendor_id; // 특정 Guard 인스턴스 접근: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances
            } else {
                $coupon->vendor_id = 0;
            }


            // dd($data);


            // `coupons` 테이블에 데이터 삽입
            $coupon->coupon_option = $data['coupon_option'];
            $coupon->coupon_code   = $coupon_code;
            $coupon->categories    = $categories;
            $coupon->brands        = $brands;
            $coupon->users         = $users;
            $coupon->coupon_type   = $data['coupon_type'];
            $coupon->amount_type   = $data['amount_type'];
            $coupon->amount        = $data['amount'];
            $coupon->expiry_date   = $data['expiry_date'];
            $coupon->status        = 1;

            $coupon->save();


            return redirect('admin/coupons')->with('success_message', $message);
        }



        // 모든 섹션과 해당 카테고리, 하위 카테고리 가져오기
        $categories = \App\Models\Section::with('categories')->get()->toArray();
        // dd($categories);

        // 모든 브랜드 가져오기
        $brands = \App\Models\Brand::where('status', 1)->get()->toArray();
        // dd($brands);

        // 모든 사용자 이메일 가져오기
        $users = \App\Models\User::select('email')->where('status', 1)->get();
        // dd($users);


        return view('admin.coupons.add_edit_coupon')->with(compact('title', 'coupon', 'categories', 'brands', 'users', 'selCats', 'selBrands', 'selUsers'));
    }

}