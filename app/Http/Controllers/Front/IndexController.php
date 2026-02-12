<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Banner;
use App\Models\Product;

class IndexController extends Controller
{
    public function index() {
        // 모든 활성(사용 중) 배너 가져오기

        $sliderBanners = Banner::where('type', 'Slider')->where('status', 1)->get()->toArray(); 
        $fixBanners    = Banner::where('type', 'Fix')->where('status', 1)->get()->toArray(); 
        $newProducts   = Product::orderBy('id', 'Desc')->where('status', 1)->limit(8)->get()->toArray(); // 최신 등록 순으로 8개의 상품을 가져옴 (홈페이지의 'New Arrivals' 섹션 표시용)
        $bestSellers   = Product::where([
            'is_bestseller' => 'Yes',
            'status'        => 1 // 상품이 활성화 상태임
        ])->inRandomOrder()->get()->toArray(); // 'BestSeller' 상품을 랜덤 순서로 표시함. 'superadmin'만 상품을 '베스트셀러'로 표시할 수 있고, 'vendor'는 불가능함    
        $discountedProducts = Product::where('product_discount', '>' , 0)->where('status', 1)->limit(6)->inRandomOrder()->get()->toArray(); // '할인 상품'을 랜덤 순서로 표시함    
        $featuredProducts   = Product::where([
            'is_featured' => 'Yes',
            'status'      => 1 // 상품이 활성화 상태임
        ])->limit(6)->get()->toArray(); // '추천 상품' 표시    


        // 정적 SEO (HTML 메타 태그): front/layout/layout.blade.php의 <meta> 및 <title> 태그 확인    
        $meta_title       = 'Multi Vendor E-commerce Website';
        $meta_description = 'Online Shopping Website which deals in Clothing, Electronics & Appliances Products';
        $meta_keywords    = 'eshop website, online shopping, multi vendor e-commerce';


        return view('front.index')->with(compact('sliderBanners', 'fixBanners', 'newProducts', 'bestSellers', 'discountedProducts', 'featuredProducts', 'meta_title', 'meta_description', 'meta_keywords')); // return view('front/index'); 와 동일함
    }
}