<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\ProductsFilter;
use App\Models\ProductsFiltersValue;

class FilterController extends Controller
{
    // 관리자 패널의 상품 동적 필터 관리



    public function filters() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'filters');


        $filters = ProductsFilter::get()->toArray();
        // dd($filters);


        return view('admin.filters.filters')->with(compact('filters'));
    }

    public function updateFilterStatus(Request $request) { // AJAX를 사용하여 필터 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            ProductsFilter::where('id', $data['filter_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'    => $status,
                'filter_id' => $data['filter_id']
            ]);
        }
    }

    public function updateFilterValueStatus(Request $request) { // AJAX를 사용하여 필터 값 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 이름/값 쌍 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            ProductsFiltersValue::where('id', $data['filter_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'    => $status,
                'filter_id' => $data['filter_id']
            ]);
        }
    }

    public function filtersValues() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'filters');


        $filters_values = ProductsFiltersValue::get()->toArray();
        // dd($filters);


        return view('admin.filters.filters_values')->with(compact('filters_values'));
    }

    public function addEditFilter(Request $request, $id = null) { // 필터 추가 또는 수정
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'filters');


        // 첫째, 요청 메소드가 'GET'인 경우, add_edit_filter.blade.php 페이지를 렌더링합니다:
        if ($id == '') { // $id가 없으면 필터 추가
            $title   = '필터 컬럼 추가';
            $filter  = new ProductsFilter;
            $message = '필터가 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 필터 수정
            $title   = '필터 컬럼 수정';
            $filter  = ProductsFilter::find($id);
            $message = '필터가 성공적으로 업데이트되었습니다!';
        }


        // 둘째, 요청 메소드가 'POST'인 경우, add_edit_filter.blade.php 페이지의 HTML <form>을 제출합니다 (필터 추가 또는 수정):
        if ($request->isMethod('post')) { // 추가 또는 수정 <form> 제출!!
            $data = $request->all();
            // dd($data);

            $cat_ids = implode(',', $request['cat_ids']); // implode()를 사용하여 배열을 문자열로 변환 (products_filters 테이블의 cat_ids 컬럼에 문자열로 저장하기 위함)    // 참고: $request['cat_ids']는 add_edit_filter.blade.php의 <select> 박스 "value" HTML 속성에서 가져옵니다.


            // DB 트랜잭션: 필터 데이터를 `products_filters` 테이블에 저장하고, `products` 테이블에 새로운 컬럼을 추가합니다.
            // 첫째: `products_filters` 테이블에 필터 정보 저장
            $filter->cat_ids       = $cat_ids;
            $filter->filter_name   = $data['filter_name'];
            $filter->filter_column = $data['filter_column'];
            $filter->status        = 1;

            $filter->save(); // 데이터베이스에 저장


            // 둘째: `products` 테이블의 `description` 컬럼 뒤에 새로운 필터 컬럼 추가
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `products` ADD ' . $data['filter_column'] . ' VARCHAR(255) AFTER `description`'); 


            return redirect('admin/filters')->with('success_message', $message); // $message는 첫 번째 if-else 문에서 정의되었습니다 (추가 또는 수정 케이스)
        }


        // 참고: 동적 필터는 섹션이 아닌 카테고리(부모 카테고리 및 하위 카테고리)에 적용됩니다.
        // 모든 섹션과 해당 카테고리, 하위 카테고리 가져오기
        $categories = \App\Models\Section::with('categories')->get()->toArray(); 
        // dd($categories);


        return view('admin.filters.add_edit_filter')->with(compact('title', 'categories', 'filter'));
    }

    public function addEditFilterValue(Request $request, $id = null) { // 필터 값 추가 또는 수정
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'filters');


        // 첫째, 요청 메소드가 'GET'인 경우, add_edit_filter_value.blade.php 페이지를 렌더링합니다:
        if ($id == '') { // $id가 없으면 필터 값 추가
            $title   = '필터 값 추가';
            $filter  = new ProductsFiltersValue;
            $message = '필터 값이 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 필터 값 수정
            $title   = '필터 값 수정';
            $filter  = ProductsFiltersValue::find($id);
            $message = '필터 값이 성공적으로 업데이트되었습니다!';
        }


        // 둘째, 요청 메소드가 'POST'인 경우, add_edit_filter_value.blade.php 페이지의 HTML <form>을 제출합니다 (필터 값 추가 또는 수정):
        if ($request->isMethod('post')) { // 추가 또는 수정 <form> 제출!!
            $data = $request->all();
            // dd($data);


            // 입력된 데이터 저장 (필터 값 추가 또는 수정)
            $filter->filter_id    = $data['filter_id'];
            $filter->filter_value = $data['filter_value'];
            $filter->status       = 1;

            $filter->save(); // 데이터베이스에 저장


            return redirect('admin/filters-values')->with('success_message', $message); // $message는 첫 번째 if-else 문에서 정의되었습니다 (추가 또는 수정 케이스)
        }


        // 활성화된 모든 필터 가져오기
        $filters = ProductsFilter::where('status', 1)->get()->toArray();
        // dd($filters);


        return view('admin.filters.add_edit_filter_value')->with(compact('title', 'filter', 'filters'));
    }

    public function categoryFilters(Request $request) { // 선택한 카테고리에 따른 필터 표시
        if ($request->ajax()) {
            $data = $request->all();
            // dd($data);


            $category_id = $data['category_id']; // ['category_id']는 AJAX 호출에서 전달됩니다 (admin/js/custom.js)


            return response()->json([ // JSON 응답: https://laravel.com/docs/9.x/responses#json-responses
                'view' => (String) \Illuminate\Support\Facades\View::make('admin.filters.category_filters')->with(compact('category_id')) // 뷰 응답: https://laravel.com/docs/9.x/responses#view-responses    // 뷰 생성 및 렌더링: https://laravel.com/docs/9.x/views#creating-and-rendering-views    // 뷰에 데이터 전달: https://laravel.com/docs/9.x/views#passing-data-to-views
            ]);
        }
    }

}