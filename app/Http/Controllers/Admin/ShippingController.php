<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\ShippingCharge;

class ShippingController extends Controller
{
    // 배송비 모듈은 단순 형태(국가별 고정 배송비)와 고급 형태(무게별 배송비 차등 적용) 두 가지가 있습니다.
    // `shipping_charges` 테이블을 통해 이를 관리하며, 관리자 전용 기능입니다.
    


    // 관리자 패널의 배송비 관리 페이지(admin/shipping/shipping_charges.blade.php) 렌더링
    public function shippingCharges() {
        // 사이드바 '배송비 관리' 탭 활성화
        Session::put('page', 'shipping');

        $shippingCharges = ShippingCharge::get()->toArray();


        return view('admin.shipping.shipping_charges')->with(compact('shippingCharges'));
    }

    // AJAX를 사용한 배송 상태(활성/비활성) 업데이트
    public function updateShippingStatus(Request $request) {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            ShippingCharge::where('id', $data['shipping_id'])->update(['status' => $status]); // $data['shipping_id']는 $.ajax() 메소드 내 'data' 객체에서 가져옵니다.
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON 응답: https://laravel.com/docs/9.x/responses#json-responses
                'status'      => $status,
                'shipping_id' => $data['shipping_id']
            ]);
        }
    }

    // 배송비 수정 페이지 렌더링 및 폼 제출 처리
    public function editShippingCharges($id, Request $request) { // 라우트 파라미터: 필수 파라미터: https://laravel.com/docs/9.x/routing#required-parameters
        // 사이드바 '배송비 관리' 탭 활성화
        Session::put('page', 'shipping');

        if ($request->isMethod('post')) { // 배송비 수정 폼 제출 시
            $data = $request->all();
            // dd($data);

            ShippingCharge::where('id', $id)->update([
                '0_500g'      => $data['0_500g'],
                '501g_1000g'  => $data['501g_1000g'],
                '1001_2000g'  => $data['1001_2000g'],
                '2001g_5000g' => $data['2001g_5000g'],
                'above_5000g' => $data['above_5000g'],
            ]);
            $message = '배송비가 성공적으로 업데이트되었습니다!';


            return redirect()->back()->with('success_message', $message);
        }

        $shippingDetails = ShippingCharge::where('id', $id)->first();
        $title = '배송비 수정';


        return view('admin.shipping.edit_shipping_charges')->with(compact('shippingDetails', 'title'));
    }

}