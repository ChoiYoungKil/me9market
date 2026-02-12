<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;



    // 모든 카테고리는 하나의 섹션에 속합니다.
    public function section() {
        return $this->belongsTo('App\Models\Section', 'section_id')->select('id', 'name'); 
    }



    // 다단계 카테고리 관계: 부모 카테고리
    public function parentCategory() { 
        return $this->belongsTo('App\Models\Category', 'parent_id')->select('id', 'category_name'); 
    }

    // 다단계 카테고리 관계: 자식(서브) 카테고리
    public function subCategories() { 
        return $this->hasMany('App\Models\Category', 'parent_id')->where('status', 1);
    }



    // URL에 해당하는 카테고리 정보 및 자식 카테고리 목록 가져오기
    public static function categoryDetails($url) { 
        $categoryDetails = Category::select('id', 'parent_id', 'category_name', 'url', 'description', 'meta_title', 'meta_description', 'meta_keywords')->with([ 
            'subCategories' => function($query) { 
                $query->select('id', 'parent_id', 'category_name', 'url', 'description', 'meta_title', 'meta_description', 'meta_keywords'); 
            }
        ])->where('url', $url)->first()->toArray(); 

        $catIds = array(); // 부모 카테고리 ID와 자식 카테고리 ID를 모두 포함할 배열
        $catIds[] = $categoryDetails['id']; // 부모 카테고리 ID 추가



        // 브레드크럼(Breadcrumb) 설정: 카테고리 및 서브카테고리 표시
        if ($categoryDetails['parent_id'] == 0) { // 부모 카테고리인 경우
            // 브레드크럼에 메인 카테고리만 표시
            $breadcrumbs = '
                <li class="is-marked"><a href="' . url($categoryDetails['url']) .'">' . $categoryDetails['category_name'] .'</a></li>
            ';
        } else { // 서브카테고리인 경우
            // 부모 카테고리와 서브카테고리 모두 표시
            $parentCategory = Category::select('category_name', 'url')->where('id', $categoryDetails['parent_id'])->first()->toArray();
            $breadcrumbs = '
                <li class="has-separator"><a href="' . url($parentCategory['url'])  .'">' . $parentCategory['category_name']  . '</a></li>
                <li class="is-marked"><a href="'     . url($categoryDetails['url']) .'">' . $categoryDetails['category_name'] . '</a></li>
            ';
        }



        // dd($categoryDetails);
        // 부모 카테고리에 속한 모든 서브카테고리 ID 가져오기
        foreach ($categoryDetails['sub_categories'] as $key => $subcat) { 
            $catIds[] = $subcat['id']; 
        }

        $resp = array(
            'catIds'          => $catIds, 
            'categoryDetails' => $categoryDetails, 
            'breadcrumbs'     => $breadcrumbs
        );


        return $resp;
    }



    // 카테고리 ID를 사용하여 카테고리 이름을 가져오는 메소드
    public static function getCategoryName($category_id) {
        $getCategoryName = Category::select('category_name')->where('id', $category_id)->first();


        return $getCategoryName->category_name;
    }

    // 비활성화된 카테고리에 속한 상품의 주문을 방지하기 위한 상태 확인 메소드
    public static function getCategoryStatus($category_id) {
        $getCategoryStatus = Category::select('status')->where('id', $category_id)->first();


        return $getCategoryStatus->status;
    }

}