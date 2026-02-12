<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Brand;


class BrandController extends Controller
{
    public function brands() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'brands');


        $brands = Brand::get()->toArray(); // 순수 PHP 배열
        // dd($brands);

        return view('admin.brands.brands')->with(compact('brands'));
    }

    public function updateBrandStatus(Request $request) { // AJAX를 사용하여 브랜드 상태 업데이트    
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Brand::where('id', $data['brand_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'   => $status,
                'brand_id' => $data['brand_id']
            ]);
        }
    }

    
    public function deleteBrand($id) {
        Brand::where('id', $id)->delete();
        
        $message = '브랜드가 성공적으로 삭제되었습니다!';
        
        return redirect()->back()->with('success_message', $message);
    }

    public function addEditBrand(Request $request, $id = null) { // 브랜드 추가 또는 수정    
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'brands');


        if ($id == '') { // $id가 없으면 브랜드 추가
            $title = '브랜드 추가';
            $brand = new Brand();
            $message = '브랜드가 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 브랜드 수정
            $title = '브랜드 수정';
            $brand = Brand::find($id);
            $message = '브랜드가 성공적으로 업데이트되었습니다!';
        }


        if ($request->isMethod('post')) { // 추가 또는 수정 폼 제출 시
            $data = $request->all();
            // dd($data);

            // 라라벨 유효성 검사    // 사용자 정의 오류 메시지: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // 사용자 정의 유효성 검사 규칙: https://laravel.com/docs/9.x/validation#custom-validation-rules
            $rules = [
                'brand_name' => 'required|regex:/^[\pL\s\-]+$/u', // 알파벳 문자와 공백만 허용
            ];

            $customMessages = [ 
                'brand_name.required' => '브랜드 이름을 입력해 주세요',
                'brand_name.regex'    => '유효한 브랜드 이름을 입력해 주세요',
            ];

            $this->validate($request, $rules, $customMessages);

            
            // 데이터 저장
            $brand->name   = $data['brand_name']; // 추가 또는 수정
            $brand->status = 1;  // 추가 또는 수정
            $brand->save(); // 데이터베이스에 저장


            return redirect('admin/brands')->with('success_message', $message);
        }


        return view('admin.brands.add_edit_brand')->with(compact('title', 'brand'));
    }
}