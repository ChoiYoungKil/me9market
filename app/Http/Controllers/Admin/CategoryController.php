<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;

use App\Models\Category;
use App\Models\Section;


class CategoryController extends Controller
{
    public function categories() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'categories');

        $categories = Category::with(['section', 'parentCategory'])->get()->toArray();
        // dd($categories);


        return view('admin.categories.categories')->with(compact('categories'));
    }

    public function updateCategoryStatus(Request $request) { // AJAX를 사용하여 카테고리 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Category::where('id', $data['category_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'      => $status,
                'category_id' => $data['category_id']
            ]);
        }
    }

    public function addEditCategory(Request $request, $id = null) { // 카테고리 추가 또는 수정
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'categories');


        if ($id == '') { // $id가 없으면 카테고리 추가
            $title = '카테고리 추가';
            $category = new Category();
            // dd($category);

            $getCategories = array(); // 해당 섹션의 모든 부모 카테고리 배열

            $message = '카테고리가 성공적으로 추가되었습니다!';
        } else { // $id가 라우트/URL 파라미터로 전달된 경우, 카테고리 수정을 의미합니다.
            $title = '카테고리 수정';
            $category = Category::find($id);
            // dd($category->parentCategory);

            $getCategories = Category::with('subCategories')->where([ // 부모 및 하위 카테고리 정보 가져오기
                'parent_id'  => 0, // 0인 경우 부모 카테고리
                'section_id' => $category['section_id']
            ])->get();


            $message = '카테고리가 성공적으로 업데이트되었습니다!';
        }


        if ($request->isMethod('post')) { // 추가 또는 수정 <form> 제출!!
            $data = $request->all();
            // dd($data);


            // 라라벨 유효성 검사    // 사용자 정의 오류 메시지: https://laravel.com/docs/9.x/validation#customizing-the-error-messages    // 사용자 정의 유효성 검사 규칙: https://laravel.com/docs/9.x/validation#custom-validation-rules
            $rules = [
                'category_name' => 'required|regex:/^[\pL\s\-]+$/u', // 알파벳 문자와 공백만 허용
                'section_id'    => 'required',
                'url'           => 'required',
            ];

            $customMessages = [ 
                'category_name.required' => '카테고리 이름을 입력해 주세요',
                'category_name.regex'    => '유효한 카테고리 이름을 입력해 주세요',
                'section_id.required'    => '섹션을 선택해 주세요',
                'url.required'           => '카테고리 URL을 입력해 주세요',
            ];

            $this->validate($request, $rules, $customMessages);


            if ($data['category_discount'] == '') {
                $data['category_discount'] = 0;
            }


            // 카테고리 이미지 업로드 (Intervention 패키지 사용)
            if ($request->hasFile('category_image')) { // HTML name 속성
                $image_tmp = $request->file('category_image'); // 업로드된 파일 가져오기: https://laravel.com/docs/9.x/requests#retrieving-uploaded-files
                if ($image_tmp->isValid()) {
                    // 이미지 확장자 가져오기
                    $extension = $image_tmp->getClientOriginalExtension();

                    // 업로드된 이미지의 랜덤 이름 생성 (이름 중복 방지)
                    $imageName = rand(111, 99999) . '.' . $extension;

                    // 'public' 폴더 내 업로드된 이미지 경로 할당
                    $imagePath = 'front/images/category_images/' . $imageName;

                    // 'Intervention' 패키지를 사용하여 이미지 업로드 및 'public' 폴더 내 경로에 저장
                    Image::make($image_tmp)->save($imagePath); // '\Image'는 Intervention 패키지입니다.

                    // 데이터베이스 테이블에 이미지 이름 저장
                    $category->category_image = $imageName;
                }

            } else { // 관리자가 다른 필드는 업데이트했지만 이미지는 업데이트하지 않은 경우 (또는 애초에 이미지가 없었던 경우)
                $category->category_image = '';
            }


            $category->section_id        = $data['section_id'];
            $category->parent_id         = $data['parent_id'];
            $category->category_name     = $data['category_name'];
            $category->category_discount = $data['category_discount'];
            $category->description       = $data['description'];
            $category->url               = $data['url'];
            $category->meta_title        = $data['meta_title'];
            $category->meta_description  = $data['meta_description'];
            $category->meta_keywords     = $data['meta_keywords'];
            $category->status            = 1;

            $category->save(); // 데이터베이스에 저장

            return redirect('admin/categories')->with('success_message', $message);
        }


        // Get all sections
        $getSections = Section::get()->toArray();
        // dd($getSections);


        return view('admin.categories.add_edit_category')->with(compact('title', 'category', 'getSections', 'getCategories'));
    }

    public function appendCategoryLevel(Request $request) { // (AJAX) 선택한 섹션에 따른 카테고리 목록 표시
        // 참고: AJAX 호출에 대한 응답으로 파일 전체를 반환하기 위해 별도의 파일로 뷰를 생성했습니다.
        if ($request->ajax()) { // AJAX 호출인 경우
            // if ($request->isMethod('get')) {
                $data = $request->all();
                // dd($data);

                $getCategories = Category::with('subCategories')->where([ // 부모 및 하위 카테고리 목록 가져오기
                    'parent_id'  => 0,
                    'section_id' => $data['section_id'] // $data['section_id'] comes from the 'data' object inside the $.ajax() method in admin/js/custom.js
                ])->get();
            // }

            return view('admin.categories.append_categories_level')->with(compact('getCategories')); // append_categories_level.blade.php 페이지 전체 전환
        }
    }

    public function deleteCategory($id) {
        Category::where('id', $id)->delete();

        $message = '카테고리가 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    public function deleteCategoryImage($id) { // AJAX 호출을 통한 카테고리 이미지 삭제 (서버 및 데이터베이스)
        // 데이터베이스에서 카테고리 이미지 레코드 가져오기
        $categoryImage = Category::select('category_image')->where('id', $id)->first();
        // dd($categoryImage);

        // 서버의 카테고리 이미지 경로
        $category_image_path = 'front/images/category_images/';

        // 서버에서 물리적 카테고리 이미지 삭제
        if (file_exists($category_image_path . $categoryImage->category_image)) {
            unlink($category_image_path . $categoryImage->category_image);
        }

        // `categories` 테이블에서 이미지명 필드 비우기
        Category::where('id', $id)->update(['category_image' => '']);

        $message = '카테고리 이미지가 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }
}
