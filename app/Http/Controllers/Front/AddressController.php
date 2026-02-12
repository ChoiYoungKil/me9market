<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\DeliveryAddress;
use App\Models\Country;


class AddressController extends Controller
{
    // 체크아웃 페이지 배송지 컨트롤러



    // AJAX를 통한 배송지 수정 (수정 버튼 클릭 시 delivery_addresses 테이블에서 인증된 사용자의 배송지 정보를 가져와서 입력 필드를 채움. front/js/custom.js 확인)    
    public function getDeliveryAddress(Request $request) {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            $data = $request->all(); // AJAX 요청에서 보낸 이름/값 쌍 배열 가져오기
            // dd($data);


            // 현재 인증된 사용자의 배송지 정보 가져오기
            $deliveryAddress = DeliveryAddress::where('id', $data['addressid'])->first()->toArray(); // 현재 인증된 사용자의 모든 배송지 가져오기    


            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'address' => $deliveryAddress
            ]);
        }
    }

    // AJAX를 통한 배송지 저장 (폼 제출 시 delivery_addresses 테이블에 인증된 사용자의 배송지 정보를 저장함. front/js/custom.js 확인)    
    public function saveDeliveryAddress(Request $request) {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            // 유효성 검사    
            // Manually Creating Validators: https://laravel.com/docs/9.x/validation#manually-creating-validators
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'delivery_name'    => 'required|string|max:100',   // string: https://laravel.com/docs/9.x/validation#rule-string    // max:value: https://laravel.com/docs/9.x/validation#rule-max
                'delivery_address' => 'required|string|max:100',   // string: https://laravel.com/docs/9.x/validation#rule-string    // max:value: https://laravel.com/docs/9.x/validation#rule-max
                'delivery_city'    => 'required|string|max:100',   // string: https://laravel.com/docs/9.x/validation#rule-string    // max:value: https://laravel.com/docs/9.x/validation#rule-max
                'delivery_state'   => 'required|string|max:100',   // string: https://laravel.com/docs/9.x/validation#rule-string    // max:value: https://laravel.com/docs/9.x/validation#rule-max
                'delivery_country' => 'required|string|max:100',   // string: https://laravel.com/docs/9.x/validation#rule-string    // max:value: https://laravel.com/docs/9.x/validation#rule-max
                'delivery_pincode' => 'required|digits:6',         // digits:value: https://laravel.com/docs/9.x/validation#rule-digits
                'delivery_mobile'  => 'required|numeric|digits:10' // digits:value: https://laravel.com/docs/9.x/validation#rule-digits
            ]);

            if ($validator->passes()) { // 유효성 검사를 통과하면 새로운 배송지를 추가(INSERT)하거나 수정(UPDATE)함
                $data = $request->all(); // AJAX 요청에서 보낸 이름/값 쌍 배열 가져오기
                // dd($data);
    
    
                $address = array();
                // address 변수를 사용하여 delivery_addresses 테이블을 업데이트(수정)하거나 추가(저장)할 수 있도록 배열에 컬럼명을 추가함:
                $address['user_id'] = Auth::user()->id; // 인증된 사용자 정보 가져오기
                $address['name']    = $data['delivery_name'];
                $address['address'] = $data['delivery_address'];
                $address['city']    = $data['delivery_city'];
                $address['state']   = $data['delivery_state'];
                $address['country'] = $data['delivery_country'];
                $address['pincode'] = $data['delivery_pincode'];
                $address['mobile']  = $data['delivery_mobile'];
    
    
                // 배송지 수정 (delivery_addresses 테이블 업데이트)
                if (!empty($data['delivery_id'])) { // AJAX를 통해 폼에서 배송지 ID가 제출된 경우, 이는 새 배송지 추가가 아니라 기존 배송지 수정(UPDATE)임을 의미함
                    // UPDATE the `delivery_addresses` database table
                    DeliveryAddress::where('id', $data['delivery_id'])->update($address); // $data['delivery_id'] comes from the 'data' object inside the $.ajax() method. Check front/js/custom.js
    
                // 새 배송지 추가 (delivery_addresses 테이블에 INSERT INTO)
                } else { // AJAX를 통해 폼에서 배송지 ID가 제출되지 않은 경우, 이는 기존 배송지 수정이 아니라 새 배송지 추가(INSERT INTO)임을 의미함                        
                    // delivery_addresses 테이블에 INSERT INTO
                    DeliveryAddress::create($address); 
                }
    
    
                // 참고: checkout() 메서드에서 전달된 것과 동일한 변수($deliveryAddresses 및 $countries)를 뷰에 전달해야 함
                $deliveryAddresses = DeliveryAddress::deliveryAddresses(); // 현재 인증된 사용자의 모든 배송지 가져오기    

                // countries 테이블에서 전 세계 국가 목록 가져오기
                $countries = Country::where('status', 1)->get()->toArray(); // 블랙리스트 국가를 제외하기 위해 status = 1인 국가만 가져옴
                // dd($countries);
    
    
                return response()->json([ 
                    // 참고: checkout() 메서드에서 전달된 것과 동일한 변수($deliveryAddresses 및 $countries)를 뷰에 전달해야 함
                    'view' => (string) \Illuminate\Support\Facades\View::make('front.products.delivery_addresses')->with(compact('deliveryAddresses', 'countries')) 
                ]);

            } else { // 유효성 검사에 실패하면 오류 메시지 반환
                // Working With Error Messages: https://laravel.com/docs/9.x/validation#working-with-error-messages    
                // dd($validator->messages());
                return response()->json([ 
                    'type'   => 'error',
                    'errors' => $validator->messages() // jQuery를 사용하여 유효성 검사 오류 메시지 배열을 반복하여 프론트엔드에 표시함 (front/js/custom.js의 $(document).on('submit', '#addressAddEditForm') 확인)    
                ]);
            }
        }
    }

    // AJAX를 통한 배송지 삭제 (삭제 버튼 클릭 시 delivery_addresses 테이블에서 해당 배송지를 삭제함. front/js/custom.js 확인)    
    public function removeDeliveryAddress(Request $request) {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            $data = $request->all(); // AJAX 요청에서 보낸 이름/값 쌍 배열 가져오기
            // dd($data);


            // delivery_addresses 테이블에서 배송지 삭제
            DeliveryAddress::where('id', $data['addressid'])->delete();
            // exit;


            // 참고: checkout() 메서드에서 전달된 것과 동일한 변수($deliveryAddresses 및 $countries)를 뷰에 전달해야 함
            $deliveryAddresses = DeliveryAddress::deliveryAddresses(); // 현재 인증된 사용자의 모든 배송지 가져오기   

            // countries 테이블에서 전 세계 국가 목록 가져오기
            $countries = Country::where('status', 1)->get()->toArray(); // 블랙리스트 국가를 제외하기 위해 status = 1인 국가만 가져옴
            // dd($countries);


            return response()->json([ 
                // 참고: checkout() 메서드에서 전달된 것과 동일한 변수($deliveryAddresses 및 $countries)를 뷰에 전달해야 함
                'view' => (string) \Illuminate\Support\Facades\View::make('front.products.delivery_addresses')->with(compact('deliveryAddresses', 'countries')) 
            ]);
        }
    }
}