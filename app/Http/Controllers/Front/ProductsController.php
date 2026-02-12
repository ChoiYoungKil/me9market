<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;
use App\Models\DeliveryAddress;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\Rating;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductsFilter;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Country;
use App\Models\ShippingCharge;
use App\Models\OrdersProduct;

class ProductsController extends Controller
{
    // listing() 메서드는 listing.blade.php 페이지를 렌더링하기 위한 GET 요청, 정렬 필터의 AJAX 요청을 위한 POST 메서드, 또는 검색 폼 제출 처리에 사용됩니다.    
    // Search Form
    public function listing(Request $request) { // using the Dynamic Routes with the foreach loop
        // listing.blade.php에서 AJAX를 사용한 정렬 필터. ajax_products_listing.blade.php를 로드하고 확인하세요.    
        if ($request->ajax()) {
            $data = $request->all();


            $url          = $data['url'];
            $_GET['sort'] = $data['sort'];
            // dd($url);
            $categoryCount = Category::where([
                'url'    => $url,
                'status' => 1
            ])->count();
            // dd($categoryCount);
    
            if ($categoryCount > 0) { // 브라우저 주소창에 입력된 카테고리 URL이 존재하는 경우
                // 브라우저 주소창에 입력된 URL의 카테고리 상세 정보 가져오기
                $categoryDetails = Category::categoryDetails($url); // 열린 $url에 따라 카테고리 정보 가져오기

                $categoryProducts = Product::with('brand')->whereIn('category_id', $categoryDetails['catIds'])->where('status', 1); // 정렬 필터 폼 확인 후 페이지네이션으로 이동함    



                // 동적 필터(listing.blade.php 페이지 왼쪽)를 작동시키는 두 가지 방법이 있습니다. 첫 번째는 jQuery를 사용한 정적 방법이고, 두 번째는 관리자 패널의 동적 방법입니다. 여기서는 첫 번째 방법(fabric 필터만 해당)을 사용합니다.
                // 참고: 체크박스 <input> 필드의 "name" 속성에 대괄호 []를 사용했으므로 체크된 체크박스는 배열로 제출됩니다. 또는 폼을 제출하지 않고 AJAX를 사용하여 값을 보냅니다.    

                // 동적 필터를 작동시키는 두 번째 방법    
                // 참고: 체크박스 <input> 필드의 "name" 속성에 대괄호 []를 사용했으므로 체크된 체크박스는 배열로 제출됩니다. 또는 폼을 제출하지 않고 AJAX를 사용하여 값을 보냅니다.
                $productFilters = ProductsFilter::productFilters(); // 모든 (활성화된) 필터 가져오기    
                foreach ($productFilters as $key => $filter) {
                        $categoryProducts->whereIn($filter['filter_column'], $data[$filter['filter_column']]); // `products` 테이블의 해당 컬럼에 대해 필터링    
                }

    
    
                // front/products/listing.blade.php에서 AJAX 없이 HTML <form>과 jQuery를 사용한 정렬 필터
                if (isset($_GET['sort']) && !empty($_GET['sort'])) {// URL 쿼리 스트링 파라미터에 '&sort=someValue'가 포함된 경우    
                    if ($_GET['sort'] == 'product_latest') {
                        $categoryProducts->orderBy('products.id', 'Desc');
                    } elseif ($_GET['sort'] == 'price_lowest') {
                        $categoryProducts->orderBy('products.product_price', 'Asc');
                    } elseif ($_GET['sort'] == 'price_highest') {
                        $categoryProducts->orderBy('products.product_price', 'Desc');
                    } elseif ($_GET['sort'] == 'name_z_a') {
                        $categoryProducts->orderBy('products.product_name', 'Desc');
                    } elseif ($_GET['sort'] == 'name_a_z') {
                        $categoryProducts->orderBy('products.product_name', 'Asc');
                    }
                }



                
                // 사이즈, 가격, 색상, 브랜드 등도 동적 필터이지만 다른 동적 필터처럼 관리되지 않고, 각각의 데이터베이스 테이블(예: products_attributes의 사이즈, products의 색상 및 가격, brands의 브랜드)에서 관리됩니다.
                // 첫 번째: 사이즈 필터 (products_attributes 데이터베이스 테이블에서)
                if (isset($data['size']) && !empty($data['size'])) { // front/js/custom.js의 AJAX 호출에서 전달됨
                    $productIds = ProductsAttribute::select('product_id')->whereIn('size', $data['size'])->pluck('product_id')->toArray(); // products_attributes 테이블에서 해당 사이즈의 상품 ID 가져오기    

                    $categoryProducts->whereIn('products.id', $productIds); // products 테이블의 id 컬럼 필터링    
                }

                
                // 두 번째: 색상 필터 (products 데이터베이스 테이블에서)
                if (isset($data['color']) && !empty($data['color'])) { // front/js/custom.js의 AJAX 호출에서 전달됨
                    $productIds = Product::select('id')->whereIn('product_color', $data['color'])->pluck('id')->toArray(); // products 테이블에서 해당 색상의 상품 ID 가져오기    

                    $categoryProducts->whereIn('products.id', $productIds); // products 테이블의 id 컬럼 필터링    
                }

                // 세 번째: 가격 필터 (products 데이터베이스 테이블에서)
                // 가격 확인 중
                $productIds = array();

                if (isset($data['price']) && !empty($data['price'])) {
                    foreach($data['price'] as $key => $price){
                        $priceArr = explode('-', $price); // 예: 첫 번째 반복: 0, 1000, 두 번째 반복: 1000, 2000...
                        if (isset($priceArr[0]) && isset($priceArr[1])) { 
                            $productIds[] = Product::select('id')->whereBetween('product_price', [$priceArr[0], $priceArr[1]])->pluck('id')->toArray(); // products 테이블에서 해당 가격 범위 내의 상품 ID 가져오기    
                        }
                    }

                    $productIds = array_unique(\Illuminate\Support\Arr::flatten($productIds)); // 중복된 상품 ID를 제거함
                    $categoryProducts->whereIn('products.id', $productIds);
                }                    



                
                // 네 번째: 브랜드 필터 (products 및 brands 데이터베이스 테이블에서)
                if (isset($data['brand']) && !empty($data['brand'])) { // front/js/custom.js의 AJAX 호출에서 전달됨
                    $productIds = Product::select('id')->whereIn('brand_id', $data['brand'])->pluck('id')->toArray(); // products 테이블에서 해당 브랜드 ID의 상품 ID 가져오기    

                    $categoryProducts->whereIn('products.id', $productIds); // products 테이블의 id 컬럼 필터링    
                }


    
                // 페이지네이션 (정렬 필터 이후)
                $categoryProducts = $categoryProducts->paginate(30); // 정렬 필터 폼 확인 후 페이지네이션 위치 변경

                // 동적 SEO (HTML 메타 태그): front/layout/layout.blade.php의 <meta> 및 <title> 태그 확인    
                $meta_title       = $categoryDetails['categoryDetails']['meta_title'];
                $meta_description = $categoryDetails['categoryDetails']['meta_description'];
                $meta_keywords    = $categoryDetails['categoryDetails']['meta_keywords'];


                return view('front.products.ajax_products_listing')->with(compact('categoryDetails', 'categoryProducts', 'url', 'meta_title', 'meta_description', 'meta_keywords'));

            } else {
                abort(404); // 나중에 404 페이지를 생성할 예정입니다.    
            }
        
        } else { // AJAX 없이 정렬 필터를 사용하거나(HTML <form>과 jQuery 사용) front/products/listing.blade.php에서 웹사이트 검색 폼 처리    

            // 웹사이트 검색 폼 (모든 웹사이트 상품 검색). front/layout/header.blade.php의 HTML 폼 확인    
            if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) { // 검색 폼이 사용된 경우 검색 폼 제출 처리
                // 신규 상품 (New Arrivals) // front/layout/header.blade.php 확인    
                if ($_REQUEST['search'] == 'new-arrivals') {
                    $search_product = $_REQUEST['search'];

                    // Category.php 모델의 categoryDetails() 메서드에서 반환되는 것과 동일한 인덱스/키로 $categoryDetails 배열을 수동으로 채움
                    $categoryDetails['breadcrumbs']                      = '신규 등록 상품';
                    $categoryDetails['categoryDetails']['category_name'] = '신규 등록 상품';
                    $categoryDetails['categoryDetails']['description']   = '신규 등록 상품';

                    // products 테이블을 categories 테이블과 조인함 (categories 테이블의 category_name 컬럼을 검색할 예정이므로)
                    // 참고: 테이블 컬럼 이름은 더 명확하게 설명적인 이름을 사용하는 것이 좋습니다 (예: products 테이블이면 id가 아니라 product_id). 또한 데이터베이스 테이블 전체에서 컬럼명이 겹치지 않게 고유하게 만드는 것이 좋습니다. 조인할 때 동일한 컬럼명이 있으면 두 번째 테이블의 컬럼명이 첫 번째를 덮어쓰기 때문입니다.    
                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join( 
                        'categories', // `categories` 테이블
                        'categories.id', '=', 'products.category_id' // `categories`.`id` = `products`.`category_id` 조건으로 조인
                    )->where('products.status', 1)->orderBy('id', 'Desc'); // 최신 상품부터 표시 (신규 등록 상품!)
                    // dd($categoryProducts);

                // 베스트셀러 (Best Sellers) // front/layout/header.blade.php 확인    
                } elseif ($_REQUEST['search'] == 'best-sellers') {
                    $search_product = $_REQUEST['search'];

                    // $categoryDetails 배열을 수동으로 채움
                    $categoryDetails['breadcrumbs']                      = '베스트셀러 상품';
                    $categoryDetails['categoryDetails']['category_name'] = '베스트셀러 상품';
                    $categoryDetails['categoryDetails']['description']   = '베스트셀러 상품';

                    // We join `products` table (at the `category_id` column) with `categoreis` table (becausee we're going to search `category_name` column in `categories` table)
                    // Note: It's best practice to name table columns with more verbose descriptive names (e.g. if the table name is `products`, then you should have a column called `product_id`, NOT `id`), and also, don't have repeated column names THROUGHOUT/ACROSS the tables of a certain (one) database (i.e. make all your database tables column names (throughout your database) UNIQUE (even columns in different tables!)). That's because of that problem that emerges when you join (JOIN clause) two tables which have the same column names, when you join them, the column names of the second table overrides the column names of the first table (similar column names override each other), leading to many problems. There are TWO ways/workarounds to tackle this problem
                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join( 
                        'categories', // `categories` 테이블
                        'categories.id', '=', 'products.category_id' 
                    )->where('products.status', 1)->where('products.is_bestseller', 'Yes');
                    // dd($categoryProducts);

                // 추천 상품 (Featured) // front/layout/header.blade.php 확인    
                } elseif ($_REQUEST['search'] == 'featured') {
                    $search_product = $_REQUEST['search'];

                    // $categoryDetails 배열을 수동으로 채움
                    $categoryDetails['breadcrumbs']                      = '추천 상품';
                    $categoryDetails['categoryDetails']['category_name'] = '추천 상품';
                    $categoryDetails['categoryDetails']['description']   = '추천 상품';

                    // We join `products` table (at the `category_id` column) with `categoreis` table (becausee we're going to search `category_name` column in `categories` table)
                    // Note: It's best practice to name table columns with more verbose descriptive names (e.g. if the table name is `products`, then you should have a column called `product_id`, NOT `id`), and also, don't have repeated column names THROUGHOUT/ACROSS the tables of a certain (one) database (i.e. make all your database tables column names (throughout your database) UNIQUE (even columns in different tables!)). That's because of that problem that emerges when you join (JOIN clause) two tables which have the same column names, when you join them, the column names of the second table overrides the column names of the first table (similar column names override each other), leading to many problems. There are TWO ways/workarounds to tackle this problem
                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join( 
                        'categories', // `categories` 테이블
                        'categories.id', '=', 'products.category_id' 
                    )->where('products.status', 1)->where('products.is_featured', 'Yes');
                    // dd($categoryProducts);

                // 할인 상품 (Discount) // front/layout/header.blade.php 확인    
                } elseif ($_REQUEST['search'] == 'discounted') {
                    $search_product = $_REQUEST['search'];

                    // $categoryDetails 배열을 수동으로 채움
                    $categoryDetails['breadcrumbs']                      = '할인 상품';
                    $categoryDetails['categoryDetails']['category_name'] = '할인 상품';
                    $categoryDetails['categoryDetails']['description']   = '할인 상품';

                    // We join `products` table (at the `category_id` column) with `categoreis` table (becausee we're going to search `category_name` column in `categories` table)
                    // Note: It's best practice to name table columns with more verbose descriptive names (e.g. if the table name is `products`, then you should have a column called `product_id`, NOT `id`), and also, don't have repeated column names THROUGHOUT/ACROSS the tables of a certain (one) database (i.e. make all your database tables column names (throughout your database) UNIQUE (even columns in different tables!)). That's because of that problem that emerges when you join (JOIN clause) two tables which have the same column names, when you join them, the column names of the second table overrides the column names of the first table (similar column names override each other), leading to many problems. There are TWO ways/workarounds to tackle this problem
                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join( 
                        'categories', // `categories` 테이블
                        'categories.id', '=', 'products.category_id' 
                    )->where('products.status', 1)->where('products.product_discount', '>', 0); // 할인이 0보다 큰 상품 표시
                    // dd($categoryProducts);

                } else { // 검색창 (Search Bar)
                    $search_product = $_REQUEST['search'];

                    // $categoryDetails 배열을 수동으로 채움
                    $categoryDetails['breadcrumbs']                      = $search_product;
                    $categoryDetails['categoryDetails']['category_name'] = $search_product;
                    $categoryDetails['categoryDetails']['description']   = $search_product . ' 에 대한 상품 검색 결과';

                    // We join `products` table (at the `category_id` column) with `categoreis` table (becausee we're going to search `category_name` column in `categories` table)
                    // Note: It's best practice to name table columns with more verbose descriptive names (e.g. if the table name is `products`, then you should have a column called `product_id`, NOT `id`), and also, don't have repeated column names THROUGHOUT/ACROSS the tables of a certain (one) database (i.e. make all your database tables column names (throughout your database) UNIQUE (even columns in different tables!)). That's because of that problem that emerges when you join (JOIN clause) two tables which have the same column names, when you join them, the column names of the second table overrides the column names of the first table (similar column names override each other), leading to many problems. There are TWO ways/workarounds to tackle this problem
                    $categoryProducts = Product::select(
                        'products.id', 'products.section_id', 'products.category_id', 'products.brand_id', 'products.vendor_id', 'products.product_name', 'products.product_code', 'products.product_color', 'products.product_price',  'products.product_discount', 'products.product_image', 'products.description'
                    )->with('brand')->join( // Joins: Inner Join Clause: https://laravel.com/docs/9.x/queries#inner-join-clause    // moving the paginate() method after checking for the sorting filter <form>    // Paginating Eloquent Results: https://laravel.com/docs/9.x/pagination#paginating-eloquent-results    // Displaying Pagination Results Using Bootstrap: https://laravel.com/docs/9.x/pagination#using-bootstrap        // https://laravel.com/docs/9.x/queries#additional-where-clauses    // using the brand() relationship method in Product.php model    // Eager Loading (using with() method): https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'brand' is the relationship method name in Product.php model
                        'categories', // `categories` table
                        'categories.id', '=', 'products.category_id' // JOIN both `products` and `categories` tables at    `categories`.`id` = `products`.`category_id`
                    )->where(function($query) use ($search_product) { // Constraining Eager Loads: https://laravel.com/docs/9.x/eloquent-relationships#constraining-eager-loads    // Subquery Where Clauses: https://laravel.com/docs/9.x/queries#subquery-where-clauses    // Advanced Subqueries: https://laravel.com/docs/9.x/eloquent#advanced-subqueries    // Eager Loading (using with() method): https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'brand' is the relationship method name in Product.php model    // function () use ()     syntax: https://www.php.net/manual/en/functions.anonymous.php#:~:text=the%20use%20language%20construct
                        // We'll search for the searched term by the user in the `product_name`, `product_code`, `product_color` and `description` columns in the `products` table and in the `category_name` column in the `categories` table
                        $query->where('products.product_name',    'like', '%' . $search_product . '%')  // 'like' SQL operator    // '%' SQL Wildcard Character    // Basic Where Clauses: Where Clauses: https://laravel.com/docs/9.x/queries#where-clauses
                            ->orWhere('products.product_code',    'like', '%' . $search_product . '%')  // 'like' SQL operator    // '%' SQL Wildcard Character    // Basic Where Clauses: Where Clauses: https://laravel.com/docs/9.x/queries#where-clauses
                            ->orWhere('products.product_color',   'like', '%' . $search_product . '%')  // 'like' SQL operator    // '%' SQL Wildcard Character    // Basic Where Clauses: Where Clauses: https://laravel.com/docs/9.x/queries#where-clauses
                            ->orWhere('products.description',     'like', '%' . $search_product . '%')  // 'like' SQL operator    // '%' SQL Wildcard Character    // Basic Where Clauses: Where Clauses: https://laravel.com/docs/9.x/queries#where-clauses
                            ->orWhere('categories.category_name', 'like', '%' . $search_product . '%'); // 'like' SQL operator    // '%' SQL Wildcard Character    // Basic Where Clauses: Where Clauses: https://laravel.com/docs/9.x/queries#where-clauses
                    })->where('products.status', 1);
                    // dd($categoryProducts);
                }


                // 사용자가 검색 폼 드롭다운 메뉴(HTML <select><option> 태그)에서 특정 섹션을 선택한 경우 section_id를 사용해 검색함
                if (isset($_REQUEST['section_id']) && !empty($_REQUEST['section_id'])) { 
                    $categoryProducts = $categoryProducts->where('products.section_id', $_REQUEST['section_id']);
                }

                $categoryProducts = $categoryProducts->get();
                // dd($categoryProducts);


                return view('front.products.listing')->with(compact('categoryDetails', 'categoryProducts'));

            } else { // 검색 폼이 사용되지 않은 경우, AJAX 없이 정렬 필터를 사용하여 listing.blade.php 페이지 렌더링 (HTML <form>과 jQuery 사용)
                $url = \Illuminate\Support\Facades\Route::getFacadeRoot()->current()->uri(); // 현재 라우트 접근    
                // dd($url);
                $categoryCount = Category::where([
                    'url'    => $url,
                    'status' => 1
                ])->count();
                // dd($categoryCount);
        
                if ($categoryCount > 0) { // 브라우저 주소창에 입력된 카테고리 URL이 존재하는 경우
                    // 브라우저 주소창에 입력된 URL의 카테고리 상세 정보 가져오기
                    $categoryDetails = Category::categoryDetails($url);
                    $categoryProducts = Product::with('brand')->whereIn('category_id', $categoryDetails['catIds'])->where('status', 1); // 정렬 필터 폼 확인 후 페이지네이션으로 이동함    
        
        
                    // front/products/listing.blade.php에서 AJAX 없이 HTML <form>과 jQuery를 사용한 정렬 필터
                    if (isset($_GET['sort']) && !empty($_GET['sort'])) {// URL 쿼리 스트링 파라미터에 '&sort=someValue'가 포함된 경우    
                        if ($_GET['sort'] == 'product_latest') {
                            $categoryProducts->orderBy('products.id', 'Desc');
                        } elseif ($_GET['sort'] == 'price_lowest') {
                            $categoryProducts->orderBy('products.product_price', 'Asc');
                        } elseif ($_GET['sort'] == 'price_highest') {
                            $categoryProducts->orderBy('products.product_price', 'Desc');
                        } elseif ($_GET['sort'] == 'name_z_a') {
                            $categoryProducts->orderBy('products.product_name', 'Desc');
                        } elseif ($_GET['sort'] == 'name_a_z') {
                            $categoryProducts->orderBy('products.product_name', 'Asc');
                        }
                    }
        
                    // 페이지네이션 (정렬 필터 이후)
                    $categoryProducts = $categoryProducts->paginate(30); // 정렬 필터 폼 확인 후 페이지네이션 위치 변경


                    // 동적 SEO (HTML 메타 태그): front/layout/layout.blade.php의 <meta> 및 <title> 태그 확인    
                    $meta_title       = $categoryDetails['categoryDetails']['meta_title'];
                    $meta_description = $categoryDetails['categoryDetails']['meta_description'];
                    $meta_keywords    = $categoryDetails['categoryDetails']['meta_keywords'];


                    return view('front.products.listing')->with(compact('categoryDetails', 'categoryProducts', 'url', 'meta_title', 'meta_description', 'meta_keywords'));

                } else {
                    abort(404); // 나중에 404 페이지를 생성할 예정입니다.    
                }

            }

        }
    }



    // front/products/detail.blade.php에서 단일 상품 상세 페이지 렌더링    
    public function detail($id) { // Required Parameters: https://laravel.com/docs/9.x/routing#required-parameters
        $productDetails = Product::with([
            'section', 'category', 'brand', 'attributes' => function($query) { 
                $query->where('stock', '>', 0)->where('status', 1); // 재고가 0보다 크고 상태가 1(활성)인 속성만 가져오도록 제한함
            }, 'images', 'vendor'
        ])->find($id)->toArray(); 


        $categoryDetails = Category::categoryDetails($productDetails['category']['url']); // 브레드크럼 링크를 가져와서 detail.blade.php에 표시함
        

        // 동일한 카테고리의 다른 상품을 가져와 유사한 상품(또는 관련 상품) 기능 구현
        $similarProducts = Product::with('brand')->where('category_id', $productDetails['category']['id'])->where('id', '!=', $id)->limit(4)->inRandomOrder()->get()->toArray(); // 현재 조회 중인 상품은 제외하고 랜덤으로 4개 상품을 가져옴


        // 최근 본 상품 기능 (recently_viewed_products 테이블을 생성했지만 모델은 필요하지 않음)
        // 사용자가 상품을 볼 때마다 상품 ID와 세션 ID를 recently_viewed_products 테이블에 삽입하고, 이전에 삽입된 데이터를 가져와 detail.blade.php에 표시함
        // 매우 중요한 참고: 이 테이블은 데이터가 매우 많아질 수 있으므로 주기적으로 비워주는 작업 스케줄링(Cron jobs)이 필요합니다.
        // 최근 본 상품에 대한 세션 설정
        if (empty(Session::get('session_id'))) { // 세션이 비어있는 경우(로그인하지 않은 경우), 게스트 사용자를 위한 랜덤 세션 ID 생성
            $session_id = md5(uniqid(rand(), true));
        } else { // 세션이 존재하는 경우 (사용자 로그인됨)
            $session_id = Session::get('session_id');
        }

        // Store the $session_id in the Session
        Session::put('session_id', $session_id); // (!! this shouble be inside the last if statement case that the user is NOT logged in ONLY !!) $session_id comes from one of the two cases of the last if statement    // Storing Data: https://laravel.com/docs/9.x/session#storing-data

        // 아직 존재하지 않는 경우, 현재 본 상품의 product_id와 session_id를 recently_viewed_products 테이블에 삽입 (단 한 번만)
        $countRecentlyViewedProducts = DB::table('recently_viewed_products')->where([ // 참고: 여기서는 DB 파사드를 직접 사용합니다. 
            'product_id' => $id,
            'session_id' => $session_id 
        ])->count(); // 동일한 상품과 세션을 통한 조회 횟수를 가져옵니다. 1회만 기록되어야 합니다.

        if ($countRecentlyViewedProducts == 0) { // 최근 본 상품 테이블에 존재하지 않는 경우 삽입
            DB::table('recently_viewed_products')->INSERT([ 
                'product_id' => $id,
                'session_id' => $session_id 
            ]);
        }

        // 최근 본 상품 ID 가져오기
        $recentProductsIds = DB::table('recently_viewed_products')->select('product_id')->where('product_id', '!=', $id)->where('session_id', $session_id)->inRandomOrder()->get()->take(4)->pluck('product_id'); // 현재 상품은 제외하고 4개를 무작위로 가져옴

        // 최근 본 상품 목록 가져오기
        $recentlyViewedProducts = Product::with('brand')->whereIn('id', $recentProductsIds)->get()->toArray(); 



        // 상품 색상 관리 (front/products/detail.blade.php에서)    
        // products 테이블의 group_code 컬럼을 통해 그룹 코드 상품을 가져옴
        $groupProducts = array();
        if (!empty($productDetails['group_code'])) { // 상품에 group_code가 있는 경우
            // 동일한 group_code를 가진 다른 모든 상품 가져오기
            $groupProducts = Product::select('id', 'product_image')->where('id', '!=', $id)->where([ // 현재 상품은 제외
                'group_code' => $productDetails['group_code'],
                'status'     => 1
            ])->get()->toArray();
        }


        // front/products/detail.blade.php에서 평점 및 리뷰 표시    
        $ratings = Rating::with('user')->where([ // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'user' is the relationship method name in Rating.php model
            'product_id' => $id,
            'status'     => 1
        ])->get()->toArray();

        // 상품의 평균 평점 계산:
        $ratingSum = Rating::where([
            'product_id' => $id,
            'status'     => 1
        ])->sum('rating');

        // 사용자에 의해 평점이 매겨진 횟수
        $ratingCount = Rating::where([
            'product_id' => $id,
            'status'     => 1
        ])->count();

        if ($ratingCount > 0) { // 평점이 적어도 하나 이상 있는 경우
            $avgRating     = round($ratingSum / $ratingCount, 2);
            $avgStarRating = round($ratingSum / $ratingCount); // HTML에서 별을 표시하기 위함
        } else {
            $avgRating     = 0;
            $avgStarRating = 0;
        }

        // 각 별점(1~5개)별 횟수 계산
        $ratingOneStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 1
        ])->count();

        $ratingTwoStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 2
        ])->count();

        $ratingThreeStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 3
        ])->count();

        $ratingFourStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 4
        ])->count();

        $ratingFiveStarCount = Rating::where([
            'product_id' => $id,
            'status'     => 1,
            'rating'     => 5
        ])->count();


        $totalStock = ProductsAttribute::where('product_id', $id)->sum('stock'); // products_attributes 테이블의 stock 컬럼 합계 계산


        // 동적 SEO (HTML 메타 태그): front/layout/layout.blade.php 확인    
        $meta_title       = $productDetails['meta_title'];
        $meta_description = $productDetails['meta_description'];
        $meta_keywords    = $productDetails['meta_keywords'];


        return view('front.products.detail')->with(compact('productDetails', 'categoryDetails', 'totalStock', 'similarProducts', 'recentlyViewedProducts', 'groupProducts', 'meta_title', 'meta_description', 'meta_keywords', 'ratings', 'avgRating', 'avgStarRating', 'ratingOneStarCount', 'ratingTwoStarCount', 'ratingThreeStarCount', 'ratingFourStarCount', 'ratingFiveStarCount'));
    }



    // front/js/custom.js의 AJAX 호출: front/products/detail.blade.php에서 사이즈를 선택하면 products_attributes 테이블에 따라 올바른 가격과 재고를 표시함    
    public function getProductPrice(Request $request) {
        if ($request->ajax()) { // if the request is coming via an AJAX call
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)
            
            $getDiscountAttributePrice = Product::getDiscountAttributePrice($data['product_id'], $data['size']); // $data['product_id'] and $data['size'] come from the 'data' object inside the $.ajax() method in front/js/custom.js file


            return $getDiscountAttributePrice;
        }
    }



    // front/products/vendor_listing.blade.php에서 모든 판매자 상품 표시    
    public function vendorListing($vendorid) { // Required Parameters: https://laravel.com/docs/9.x/routing#required-parameters
        // Get vendor shop name
        $getVendorShop = Vendor::getVendorShop($vendorid);

        // Get all vendor products
        $vendorProducts = Product::with('brand')->where('vendor_id', $vendorid)->where('status', 1); // Eager Loading (using with() method): https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'brand' is the relationship method name in Product.php model that is being Eager Loaded

        // $vendorProducts 페이지네이션
        $vendorProducts = $vendorProducts->paginate(30); 


        return view('front.products.vendor_listing')->with(compact('getVendorShop', 'vendorProducts'));
    }



    // front/products/detail.blade.php에서 장바구니에 추가 <form> 제출    
    public function cartAdd(Request $request) {
        if ($request->isMethod('post')) { // 장바구니에 추가 폼이 제출된 경우
            $data = $request->all();

            // Correcting an issue with Coupon Codes when adding an item to the Cart which already has items in it (added before)
            // We need to remove/empty (forget) the 'couponAmount' and 'couponCode' Session Variables (reset the whole process of Applying the Coupon) whenever a user applies a new coupon, or updates Cart items (changes items quantity for example) or deletes items from the Cart or even Adds new items in the Cart    
            Session::forget('couponAmount'); // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
            Session::forget('couponCode');   // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data


            // 수량이 0인 상품을 장바구니에 추가하는 것을 방지
            if ($data['quantity'] <= 0) { // 주문 수량이 0이면 최소 1로 변경함
                $data['quantity'] = 1;
            }


            // products_attributes 테이블에서 선택한 상품 ID와 사이즈에 사용할 수 있는 재고가 있는지 확인
            $getProductStock = ProductsAttribute::getProductStock($data['product_id'], $data['size']);

            if ($getProductStock < $data['quantity']) { // 가용 재고가 사용자가 주문하려는 수량보다 적은 경우
                return redirect()->back()->with('error_message', '요청하신 수량이 부족합니다!');
            }


            // 사용자가 인증되지 않은 경우(게스트), 로그인 없이 장바구니 추가가 가능하도록 session_id를 사용하고, 로그인 후에는 user_id를 사용하도록 처리함
            // 참고: 라라벨 기본 인증 가드 'web'을 사용함
            // session_id가 없으면 생성:
            // 세션 생성
            $session_id = Session::get('session_id'); // 이미 session_id가 존재하는 경우
            if (empty($session_id)) { // 세션이 비어있는 경우 게스트를 위한 랜덤 세션 ID 생성
                $session_id = Session::getId(); // 현재 세션 ID 가져오기
                Session::put('session_id', $session_id);  // 현재 session_id를 사용자 세션에 저장    
            }

            // 두 가지 경우(로그인 사용자 및 게스트)에 대해 $user_id와 $countProducts를 가져와서 동일한 상품과 사이즈가 이미 장바구니에 있는지 확인
            // 중복 방지를 위함
            if (Auth::check()) { // 기본 'web' 인증 가드 사용 (로그인 여부 확인)
                $user_id = Auth::user()->id; // Retrieving The Authenticated User: https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user

                // Check if that authenticated/logged in user has already THE SAME product `product_id` with THE SAME `size` (in `carts` table) in the Cart i.e. the `carts` table
                $countProducts = Cart::where([
                    'user_id'    => $user_id, // THAT EXACT authenticated/logged in user (using their `user_id` because they're authenticated/logged in)
                    'product_id' => $data['product_id'],
                    'size'       => $data['size']
                ])->count();

            } else { // 로그인하지 않은 사용자의 경우 (게스트)
                // 해당 게스트 사용자가 장바구니에 동일한 상품과 사이즈를 가지고 있는지 확인
                $user_id = 0; // 인증되지 않은 게스트이므로 0으로 설정
                $countProducts = Cart::where([ // 중복 방지를 위해 확인 
                    'session_id' => $session_id, // 해당 게스트 사용자 (session_id 사용)
                    'product_id' => $data['product_id'],
                    'size'       => $data['size']
                ])->count();
            }



            // 특정 사용자(로그인 또는 게스트)가 장바구니에 담은 동일한 피와 사이즈의 상품 중복 방지:
            if ($countProducts > 0) { // 이미 동일한 상품과 사이즈가 있는 경우 수량만 누적(UPDATE)함
                Cart::where([
                    'session_id' => $session_id, 
                    'user_id'    => $user_id ?? 0, 
                    'product_id' => $data['product_id'],
                    'size'       => $data['size']
                ])->increment('quantity', $data['quantity']); // 기존 수량에 추가된 수량을 더함
            } else { // if that `product_id` with that `size` was never ordered by that user `session_id` or `user_id` (i.e. that product with that size for that user doesn't exist in the `carts` table), INSERT it into the `carts` table for the first time
                // INSERT the ordered product `product_id`, the user's session ID `session_id`, `size` and `quantity` in the `carts` table
                $item = new Cart; // the `carts` table

                $item->session_id = $session_id; // $session_id will be stored whether the user is authenticated/logged in or NOT
                $item->user_id    = $user_id; // depending on the last if statement (whether user is authenticated/logged in or NOT (guest))    // $user_id will be always zero 0 if the user is NOT authenticated/logged in    // When user logins, their `user_id` gets updated (check userLogin() method in UserController.php)
                $item->product_id = $data['product_id'];
                $item->size       = $data['size'];
                $item->quantity   = $data['quantity'];

                $item->save();
            }


            return redirect()->back()->with('success_message', '상품이 장바구니에 추가되었습니다! <a href="/cart" style="text-decoration: underline !important">장바구니 보기</a>');
        }
    }

    // 장바구니 페이지 렌더링 (front/products/cart.blade.php)    
    public function cart() {
        // Get the Cart Items of a cerain user (using their `user_id` if they're authenticated/logged in or their `session_id` if they're not authenticated/not logged in (guest))    
        $getCartItems = Cart::getCartItems();

        // Static SEO (HTML meta tags): Check the HTML <meta> tags and <title> tag in front/layout/layout.blade.php    
        $meta_title       = '장바구니 - 멀티 벤더 이커머스';
        $meta_keywords    = '장바구니, 쇼핑카트, 멀티 벤더';


        return view('front.products.cart')->with(compact('getCartItems', 'meta_title', /* 'meta_description', */ 'meta_keywords'));
    }

    // Update Cart Item Quantity AJAX call in front/products/cart_items.blade.php. Check front/js/custom.js
    public function cartUpdate(Request $request) {
        if ($request->ajax()) { // if the request is coming via an AJAX call
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)


            // Correcting an issue with Coupon Codes when adding an item to the Cart which already has items in it (added before)
            // We need to remove/empty (forget) the 'couponAmount' and 'couponCode' Session Variables (reset the whole process of Applying the Coupon) whenever a user applies a new coupon, or updates Cart items (changes items quantity for example) or deletes items from the Cart or even Adds new items in the Cart    
            Session::forget('couponAmount'); // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
            Session::forget('couponCode');   // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
        


            // Apply some conditions (and showing them in the view!) before Update-ing the Cart Item Quantity (making sure that the desired quantity is not more than (doesn't exceed) the available `stock` in `products_attributes` table, and that the desired product `size` is not disabled/inactive (`status` is not zero 0) in `products_attributes` table)    
            // Get user's Cart details
            $cartDetails = Cart::find($data['cartid']); // $data['cartid'] comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file

            // The 1st condition: Make sure that the desired quantity is not more than (doesn't exceed) the available `stock` in `products_attributes` table
            // Get available product `stock` from `products_attributes` table
            $availableStock = ProductsAttribute::select('stock')->where([
                'product_id' => $cartDetails['product_id'],
                'size'       => $cartDetails['size']
            ])->first()->toArray();

            if ($data['qty'] > $availableStock['stock']) { // 사용자가 원하는 수량이 가용한 재고를 초과하는 경우
                // 수량 업데이트 후 해당 사용자의 장바구니 아이템 가져오기
                $getCartItems = Cart::getCartItems();

                return response()->json([ 
                    'status'     => false,
                    'message'    => '상품 재고가 부족합니다.',
                    // 'view' 키를 사용하여 front/js/custom.js에서 뷰를 다시 렌더링함
                    'view'       => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 

                    // 미니 장바구니 위젯 뷰
                    'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
                ]);
            }

            // 2단계: 원하는 상품 사이즈가 비활성화 상태(status가 0)가 아닌지 확인
            // products_attributes 테이블에서 상품 상태 가져오기
            $availableSize =  ProductsAttribute::where([
                'product_id' => $cartDetails['product_id'],
                'size'       => $cartDetails['size'],
                'status'     => 1 // 사이즈가 활성 상태인지 확인
            ])->count();

            if ($availableSize == 0) { // 상품 상태가 0(비활성)인 경우
                // 수량 업데이트 후 해당 사용자의 장바구니 아이템 가져오기
                $getCartItems = Cart::getCartItems();


                return response()->json([ 
                    'status'  => false,
                    'message' => '해당 상품 사이즈를 사용할 수 없습니다. 상품을 삭제하고 다른 사이즈를 선택해 주세요!', 
                    'view'    => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 
                    'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
                ]);
            }


            // 모든 조건 확인 후 carts 테이블의 수량 업데이트
            Cart::where('id', $data['cartid'])->update([ 
                'quantity' => $data['qty'] 
            ]);


            // Get the Cart Items (after UPDATE-ing the Cart Item Quantity) of a cerain user (using their `user_id` if they're authenticated/logged in or their `session_id` if they're not authenticated/not logged in (guest))
            $getCartItems = Cart::getCartItems();
            $totalCartItems = totalCartItems(); // totalCartItems() function is in our custom Helpers/Helper.php file that we have registered in 'composer.json' file    // We created the CSS class 'totalCartItems' in front/layout/header.blade.php to use it in front/js/custom.js to update the total cart items via AJAX, because in pages that we originally use AJAX to update the cart items (such as when we delete a cart item in http://127.0.0.1:8000/cart using AJAX), the number doesn't change in the header automatically because AJAX is already used and no page reload/refresh has occurred



            // We need to remove/empty (forget) the 'couponAmount' and 'couponCode' Session Variables (reset the whole process of Applying the Coupon) whenever a user applies a new coupon, or updates Cart items (changes items quantity for example) or deletes items from the Cart or even Adds new items in the Cart    
            Session::forget('couponAmount'); // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
            Session::forget('couponCode');   // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data



            return response()->json([ 
                'status'         => true,
                'totalCartItems' => $totalCartItems, 
                'view'           => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 
                'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
            ]);
        }
    }

    // front/products/cart_items.blade.php에서 장바구니 아이템 삭제 AJAX 호출. front/js/custom.js 확인    
    public function cartDelete(Request $request) {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            // 쿠폰 적용 프로세스 초기화를 위해 세션 제거
            Session::forget('couponAmount'); 
            Session::forget('couponCode');   


            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call)


            // Delete the Cart Item
            Cart::where('id', $data['cartid'])->delete(); // $data['cartid'] comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file


            // Get the Cart Items (after DELETE-ing the Cart Item Quantity) of a cerain user (using their `user_id` if they're authenticated/logged in or their `session_id` if they're not authenticated/not logged in (guest))
            $getCartItems = Cart::getCartItems();
            $totalCartItems = totalCartItems(); // totalCartItems() function is in our custom Helpers/Helper.php file that we have registered in 'composer.json' file    // We created the CSS class 'totalCartItems' in front/layout/header.blade.php to use it in front/js/custom.js to update the total cart items via AJAX, because in pages that we originally use AJAX to update the cart items (such as when we delete a cart item in http://127.0.0.1:8000/cart using AJAX), the number doesn't change in the header automatically because AJAX is already used and no page reload/refresh has occurred


            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                // 'status' => true,
                'totalCartItems' => $totalCartItems, // totalCartItems() function is in our custom Helpers/Helper.php file that we have registered in 'composer.json' file    // We created the CSS class 'totalCartItems' in front/layout/header.blade.php to use it in front/js/custom.js to update the total cart items via AJAX, because in pages that we originally use AJAX to update the cart items (such as when we delete a cart item in http://127.0.0.1:8000/cart using AJAX), the number doesn't change in the header automatically because AJAX is already used and no page reload/refresh has occurred
                // We'll use that array key 'view' as a JavaScript 'response' property to render the view (    $('#appendCartItems').html(resp.view);    ). Check front/js/custom.js
                'view'   => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), // View Responses: https://laravel.com/docs/9.x/responses#view-responses    // Creating & Rendering Views: https://laravel.com/docs/9.x/views#creating-and-rendering-views    // Passing Data To Views: https://laravel.com/docs/9.x/views#passing-data-to-views
                'headerview' => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) // View Responses: https://laravel.com/docs/9.x/responses#view-responses    // Creating & Rendering Views: https://laravel.com/docs/9.x/views#creating-and-rendering-views    // Passing Data To Views: https://laravel.com/docs/9.x/views#passing-data-to-views
            ]);
        }
    }



    // Note: For Coupons module, user must be logged in (authenticated) to be able to redeem them. Both 'admins' and 'vendors' can add Coupons. Coupons added by 'vendor' will be available for their products ONLY, but ones added by 'admins' will be available for ALL products.
    // Coupon Code redemption (Apply coupon) / Coupon Code HTML Form submission via AJAX in front/products/cart_items.blade.php, check front/js/custom.js    
    public function applyCoupon(Request $request) {
        if ($request->ajax()) { // if the request is coming via an AJAX call
            $data = $request->all(); // Getting the name/value pairs array that are sent from the AJAX request (AJAX call) (through the 'data' object)


            // We need to remove/empty (forget) the 'couponAmount' and 'couponCode' Session Variables (reset the whole process of Applying the Coupon) whenever a user applies a new coupon, or updates Cart items (changes items quantity for example) or deletes items from the Cart or even Adds new items in the Cart    
            Session::forget('couponAmount'); // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
            Session::forget('couponCode');   // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data


            $getCartItems = Cart::getCartItems();
            $totalCartItems = totalCartItems(); // totalCartItems() function is in our custom Helpers/Helper.php file that we have registered in 'composer.json' file    // We created the CSS class 'totalCartItems' in front/layout/header.blade.php to use it in front/js/custom.js to update the total cart items via AJAX, because in pages that we originally use AJAX to update the cart items (such as when we delete a cart item in http://127.0.0.1:8000/cart using AJAX), the number doesn't change in the header automatically because AJAX is already used and no page reload/refresh has occurred


            // Check the validity of the Coupon Code
            $couponCount = Coupon::where('coupon_code', $data['code'])->count(); // $data['code'] comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file

            if ($couponCount == 0) { // if the submitted coupon is wrong, send error message
                return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                    'status'         => false,
                    'totalCartItems' => $totalCartItems, // totalCartItems() function is in our custom Helpers/Helper.php file that we have registered in 'composer.json' file    // We created the CSS class 'totalCartItems' in front/layout/header.blade.php to use it in front/js/custom.js to update the total cart items via AJAX, because in pages that we originally use AJAX to update the cart items (such as when we delete a cart item in http://127.0.0.1:8000/cart using AJAX), the number doesn't change in the header automatically because AJAX is already used and no page reload/refresh has occurred
                    'message'        => 'The coupon is invalid!',
                    // We'll use that array key 'view' as a JavaScript 'response' property to render the view (    $('#appendCartItems').html(resp.view);    ). Check front/js/custom.js
                    'view'           => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), // View Responses: https://laravel.com/docs/9.x/responses#view-responses    // Creating & Rendering Views: https://laravel.com/docs/9.x/views#creating-and-rendering-views    // Passing Data To Views: https://laravel.com/docs/9.x/views#passing-data-to-views
                    'headerview'     => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) // View Responses: https://laravel.com/docs/9.x/responses#view-responses    // Creating & Rendering Views: https://laravel.com/docs/9.x/views#creating-and-rendering-views    // Passing Data To Views: https://laravel.com/docs/9.x/views#passing-data-to-views
                ]);

            } else { // if the submitted coupon is valid, check some conditions (do some validation)
                // SUBMITTED COUPON CODE VALIDATION:

                // Get the coupon submitted (via AJAX) details
                $couponDetails = Coupon::where('coupon_code', $data['code'])->first(); // $data['code'] comes from the 'data' object sent from inside the $.ajax() method in front/js/custom.js file


                // Check if the submitted coupon code is active/inactive (enabled/disabled/activated/deactivated)
                if ($couponDetails->status == 0) {
                    $message = 'The coupon is inactive!';
                }


                // Check if the submitted coupon code is expired
                $expiry_date  = $couponDetails->expiry_date;
                $current_date = date('Y-m-d'); // this date format is understandable by MySQL
                
                if ($expiry_date < $current_date) {
                    $message = 'The coupon is expired!';
                }


                // Managing coupon types in `coupons` table: 'Single Time' or 'Multiple Times'
                if ($couponDetails->coupon_type == 'Single Time') { // if the `coupon_type` in `coupons` table is 'Single Time'
                    // Check in the `orders` table if the currently authenticated/logged-in user really used this Coupon Code with their order
                    $couponCount = Order::where([
                        'coupon_code' => $data['code'],
                        'user_id'     => Auth::user()->id // Retrieving The Authenticated User: https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user
                    ])->count();

                    if ($couponCount >= 1) { // if this 'Single Time' coupon code has been used/redeemed more than one single time by this user (this authenticated/logged-in user) (i.e. meaning that if that coupon code is already existing in the `orders` table and has been used/redeemed by this authenticated/logged-in user)
                        $message = 'This coupon code is already availed by you!';
                    }
                }


                // Check if the submitted coupon code belongs to the correct relevant selected categories and subcategories of the coupon in the Admin Panel (for example, if the coupon is for Smartphones Category, user can't use it while buying T-shirts)
                // Get the coupon's categories and subcategories (if any)
                $catArr = explode(',', $couponDetails->categories);
                
                $total_amount = 0;

                foreach ($getCartItems as $key => $item) {
                    if (!in_array($item['product']['category_id'], $catArr)) { // if the category of one of the products in the Cart doesn't belong to the Coupon's categories (the categories of the coupon selected by 'vendor' or 'admin' in the Admin Panel for the coupon)
                        $message = 'This coupon code selected categories is not for one of the selected products category!';
                    }

                    
                    $attrPrice = Product::getDiscountAttributePrice($item['product_id'], $item['size']);
                    $total_amount = $total_amount + ($attrPrice['final_price'] * $item['quantity']);
                }


                // Check if the coupon code submitted by user is not available for that user (in case the coupon is already selected for certain specific users selected by 'admin' or 'vendor' in the Coupons tab in Admin Panel, and it's not available for all users)
                // Get the coupon's selected users
                if (isset($couponDetails->users) && !empty($couponDetails->users)) {
                    $usersArr = explode(',', $couponDetails->users);    
                    // Check if the submitted coupon code is available ONLY for some specific users (from the Coupons tab in Admin Panel in 'Select User (by email):') and check if the coupon is available or not for the user submitting the coupon code
                    if (count($usersArr)) { // if there's at least a one specific selected user for the coupon
                        // Get user ids of all the selected users that the coupon code are available for them
                        foreach ($usersArr as $key => $user) {
                            $getUserId = User::select('id')->where('email', $user)->first()->toArray();
                            $usersId[] = $getUserId['id'];
                        }
    
                        foreach ($getCartItems as $item) {
                            if (!in_array($item['user_id'], $usersId)) { // if the user id of one of the products in the Cart doesn't belong to the Coupon's specifically selected users (to check if the submitted coupon code is available to the user submitting it or not)
                                $message = 'This coupon code is not available for you! Try again with a valid coupon code! (The coupon code is available only for certain selected users!)';
                            }
                        }
                    }
                }


                // 제출된 쿠폰 코드가 상품의 판매자에게 속하는지 확인 (판매자가 추가한 쿠폰은 자신의 상품에만 적용되지만, 관리자 쿠폰은 모든 상품에 적용 가능함)
                // 판매자 쿠폰은 해당 판매자의 상품에만 적용 가능
                if ($couponDetails->vendor_id > 0) { // 제출된 쿠폰이 특정 '판매자'에게 속하는지 확인
                    // 해당 판매자의 모든 상품 ID 가져오기
                    $productIds = Product::select('id')->where('vendor_id', $couponDetails->vendor_id)->pluck('id')->toArray();
 
                    foreach ($getCartItems as $item) {
                        if (!in_array($item['product']['id'], $productIds)) { // 장바구니 상품 중 해당 판매자의 상품이 아닌 것이 있는 경우
                            $message = '이 쿠폰 코드는 현재 고객님께 제공되지 않습니다! 유효한 쿠폰 코드로 다시 시도해 주세요! 장바구니에 담긴 상품 중 일부가 해당 쿠폰을 발행한 판매자의 상품이 아닙니다.';
                        }
                    }
                }


                // 제출된 쿠폰 코드에 오류 메시지가 있는 경우 AJAX 호출에 응답 전송
                if (isset($message)) {
                    return response()->json([ 
                        'status'         => false,
                        'totalCartItems' => $totalCartItems, 
                        'message'        => $message,
                        'view'           => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 
                        'headerview'     => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
                    ]);
 
                } else { // 제출된 쿠폰 코드가 정확하고 모든 유효성 검사를 통과한 경우 (오류 없음)
                    

                    // 제출된 쿠폰의 금액 유형이 'Fixed'(고정 금액)인지 'Percentage'(백분율)인지 확인
                    if ($couponDetails->amount_type == 'Fixed') { // 고정 금액인 경우
                        $couponAmount = $couponDetails->amount; 
                    } else { // 백분율인 경우
                        $couponAmount = $total_amount * ($couponDetails->amount / 100);
                    }


                    $grand_total = $total_amount - $couponAmount;


                    // 쿠폰 코드와 $couponAmount를 세션 변수에 저장
                    Session::put('couponAmount', $couponAmount);
                    Session::put('couponCode'  , $data['code']); 

                    $message = '쿠폰 코드가 성공적으로 적용되었습니다. 할인이 적용됩니다!';


                    return response()->json([ 
                        'status'         => true,
                        'totalCartItems' => $totalCartItems, 
                        'couponAmount'   => $couponAmount,
                        'grand_total'    => $grand_total,
                        'message'        => $message,
                        'view'           => (String) \Illuminate\Support\Facades\View::make('front.products.cart_items')->with(compact('getCartItems')), 
                        'headerview'     => (String) \Illuminate\Support\Facades\View::make('front.layout.header_cart_items')->with(compact('getCartItems')) 
                    ]);
                }
            }
        }
    }



    // 체크아웃 페이지 (front/products/checkout.blade.php 렌더링을 위한 'GET' 요청 또는 동일 페이지의 'POST' 폼 제출 처리)
    public function checkout(Request $request) {
        // 데이터베이스의 countries 테이블에서 전 세계 국가 목록 가져오기
        $countries = Country::where('status', 1)->get()->toArray(); 
        
        // 특정 사용자의 장바구니 아이템 가져오기
        $getCartItems = Cart::getCartItems();

        // 장바구니가 비어 있는 경우 체크아웃 페이지 접근 제한
        if (count($getCartItems) == 0) {
            $message = '장바구니가 비어 있습니다! 체크아웃하려면 상품을 추가해 주세요.';

            return redirect('cart')->with('error_message', $message); 
        }


        // 총 가격 계산    
        $total_price  = 0;
        $total_weight = 0;

        foreach ($getCartItems as $item) {
            $attrPrice = Product::getDiscountAttributePrice($item['product_id'], $item['size']);
            $total_price = $total_price + ($attrPrice['final_price'] * $item['quantity']);

            
            $product_weight = $item['product']['product_weight'];
            $total_weight = $total_weight + $product_weight;
        }


        $deliveryAddresses = DeliveryAddress::deliveryAddresses(); // 현재 로그인된 사용자의 배송 주소들


        // 각 배송 주소별 배송비 계산 (국가 기준)
        foreach ($deliveryAddresses as $key => $value) {
            $shippingCharges = ShippingCharge::getShippingCharges($total_weight, $value['country']);

            // 각 배송 주소 배열에 배송비 추가
            $deliveryAddresses[$key]['shipping_charges'] = $shippingCharges;

            // cod_pincodes 및 prepaid_pincodes 테이블에서 COD 및 선결제 가능 여부 확인
            // 사용자의 해당 배송 주소 우편번호가 cod_pincodes 테이블에 있는지 확인
            $deliveryAddresses[$key]['codpincodeCount'] = DB::table('cod_pincodes')->where('pincode', $value['pincode'])->count(); 

            // 사용자의 해당 배송 주소 우편번호가 prepaid_pincodes 테이블에 있는지 확인
            $deliveryAddresses[$key]['prepaidpincodeCount'] = DB::table('prepaid_pincodes')->where('pincode', $value['pincode'])->count(); 
        }


        
        if ($request->isMethod('post')) { // 체크아웃 페이지의 폼(배송 주소 및 결제 수단)이 제출된 경우
            $data = $request->all();

            // 웹사이트 보안
            // 비활성화된 상품(status=0)에 대한 주문을 방지해야 함
            foreach ($getCartItems as $item) {
                // products 테이블 확인하여 비활성화된 상품 주문 방지
                $product_status = Product::getProductStatus($item['product_id']);
                if ($product_status == 0) { 
                    $message = $item['product']['product_name'] . '(' . $item['size'] . ' 사이즈) 상품은 현재 구매가 불가능합니다. 장바구니에서 삭제하고 다른 상품을 선택해 주세요.';
                    return redirect('/cart')->with('error_message', $message); 
                }
            }

            // 재고가 없는 상품 주문 방지
            $getProductStock = ProductsAttribute::getProductStock($item['product_id'], $item['size']); 
            if ($getProductStock == 0) { 
                $message = $item['product']['product_name'] . '(' . $item['size'] . ' 사이즈) 상품은 현재 구매가 불가능합니다. 장바구니에서 삭제하고 다른 상품을 선택해 주세요.';
                return redirect('/cart')->with('error_message', $message); 
            }

            // 비활성화된 상품 속성(status=0) 주문 방지
            $getAttributeStatus = ProductsAttribute::getAttributeStatus($item['product_id'], $item['size']); 
            if ($getAttributeStatus == 0) { 
                $message = $item['product']['product_name'] . '(' . $item['size'] . ' 사이즈) 상품은 현재 구매가 불가능합니다. 장바구니에서 삭제하고 다른 상품을 선택해 주세요.';
                return redirect('/cart')->with('error_message', $message); 
            }

            // 비활성화된 카테고리의 상품 주문 방지
            $getCategoryStatus = Category::getCategoryStatus($item['product']['category_id']);
            if ($getCategoryStatus == 0) { 
                $message = $item['product']['product_name'] . '(' . $item['size'] . ' 사이즈) 상품은 현재 구매가 불가능합니다. 장바구니에서 삭제하고 다른 상품을 선택해 주세요.';
                return redirect('/cart')->with('error_message', $message); 
            }


            // 유효성 검사:
            // 배송 주소 유효성 검사
            if (empty($data['address_id'])) { // 배송 주소를 선택하지 않은 경우
                $message = '배송 주소를 선택해 주세요!';

                return redirect()->back()->with('error_message', $message);
            }

            // 결제 수단 유효성 검사
            if (empty($data['payment_gateway'])) { 
                $message = '결제 수단을 선택해 주세요!';

                return redirect()->back()->with('error_message', $message);
            }

            // 이용 약관 동의 유효성 검사
            if (empty($data['accept'])) { 
                $message = '이용 약관에 동의해 주세요!';

                return redirect()->back()->with('error_message', $message);
            }



            // 유효성 검사를 통과하면 주문 프로세스 시작:


            // 참고: 주문 모듈을 위해 orders 및 orders_products 두 개의 테이블을 생성했습니다. 
            // 첫 번째 테이블은 주문에 대한 주요 정보를 저장하며, 두 번째 테이블은 주문된 상품들에 대한 상세 정보를 저장합니다.
            // 두 테이블은 일대다(one-to-many) 관계입니다.


            // 이제 데이터베이스 테이블을 채우기 위해 필요한 데이터를 수집합니다.    

            // address_id로부터 배송 주소 가져오기
            $deliveryAddress = DeliveryAddress::where('id', $data['address_id'])->first()->toArray();
            // dd($deliveryAddress);

            
            // 선택한 payment_gateway가 'COD'이면 payment_method도 'COD'로 설정(상태는 'New'), 그 외에는 'prepaid'(상태는 'Pending')로 설정
            if ($data['payment_gateway'] == 'COD') {
                $payment_method = 'COD';
                $order_status   = 'New';

            } else { // 'COD' 이외의 결제 수단을 선택한 경우
                $payment_method = 'Prepaid';
                $order_status   = 'Pending'; // 결제 확인 후 'Payment Captured' 또는 'Canceled'로 변경됨
            }


            // 중요: !!데이터베이스 트랜잭션!! 먼저 orders 테이블에 주문을 저장한 후, 새로 생성된 주문 id를 사용하여 orders_products 테이블을 채웁니다.    
            // Database Transactions: https://laravel.com/docs/9.x/database#database-transactions
            DB::beginTransaction();

            // 소계, 최종 합계, 쿠폰 할인 계산
            // 최종 합계 계산
            // 총 가격 가져오기 (소계)
            $total_price = 0;
            foreach ($getCartItems as $item) {
                $getDiscountAttributePrice = Product::getDiscountAttributePrice($item['product_id'], $item['size']); 
                $total_price = $total_price + ($getDiscountAttributePrice['final_price'] * $item['quantity']);
            }

            // 배송비 계산
            $shipping_charges = 0;

            // 선택한 배송 주소에 기반한 배송비 가져오기    
            $shipping_charges = ShippingCharge::getShippingCharges($total_weight, $deliveryAddress['country']);

            // 최종 합계
            $grand_total = $total_price + $shipping_charges - Session::get('couponAmount');

            // 나중에 필요할 때 사용할 수 있도록 최종 합계를 세션에 저장 (아이페이, 페이팔 등에서 사용)
            Session::put('grand_total', $grand_total); 


            // 수집된 데이터를 orders 테이블에 삽입
            $order = new Order; // Order.php 모델 객체 생성

            // orders 테이블에 저장할 데이터 할당
            $order->user_id          = Auth::user()->id; 
            $order->name             = $deliveryAddress['name'];
            $order->address          = $deliveryAddress['address'];
            $order->city             = $deliveryAddress['city'];
            $order->state            = $deliveryAddress['state'];
            $order->country          = $deliveryAddress['country'];
            $order->pincode          = $deliveryAddress['pincode'];
            $order->mobile           = $deliveryAddress['mobile'];
            $order->email            = Auth::user()->email; 
            $order->shipping_charges = $shipping_charges;
            $order->coupon_code      = Session::get('couponCode');   
            $order->coupon_amount    = Session::get('couponAmount'); 
            $order->order_status     = $order_status;
            $order->payment_method   = $payment_method;
            $order->payment_gateway  = $data['payment_gateway'];
            $order->grand_total      = $grand_total;

            $order->save(); // orders 테이블에 데이터 삽입

            // orders_products 테이블의 order_id 컬럼을 채우기 위해 마지막으로 생성된 주문 id 가져오기
            $order_id = DB::getPdo()->lastInsertId();


            // orders_products 테이블에 주문 상품 데이터 삽입
            foreach ($getCartItems as $item) {
                $cartItem = new OrdersProduct; 

                // orders_products 테이블에 삽입할 주문 상품 데이터 할당
                $cartItem->order_id = $order_id;
                $cartItem->user_id  = Auth::user()->id; 

                // orders_products 테이블을 채우기 위해 products 테이블에서 장바구니 상품의 세부 정보 가져오기
                $getProductDetails = Product::select('product_code', 'product_name', 'product_color', 'admin_id', 'vendor_id')->where('id', $item['product_id'])->first()->toArray();

                // orders_products 테이블 데이터 계속 입력
                $cartItem->admin_id        = $getProductDetails['admin_id'];
                $cartItem->vendor_id       = $getProductDetails['vendor_id'];

                
                if ($getProductDetails['vendor_id'] > 0) { // 판매자가 'vendor'인 경우
                    $vendorCommission = Vendor::getVendorCommission($getProductDetails['vendor_id']);
                    $cartItem->commission  = $vendorCommission;
                }

                $cartItem->product_id      = $item['product_id'];
                $cartItem->product_code    = $getProductDetails['product_code'];
                $cartItem->product_name    = $getProductDetails['product_name'];
                $cartItem->product_color   = $getProductDetails['product_color'];
                $cartItem->product_size    = $item['size'];

                $getDiscountAttributePrice = Product::getDiscountAttributePrice($item['product_id'], $item['size']); 
                $cartItem->product_price   = $getDiscountAttributePrice['final_price'];


                
                $getProductStock = ProductsAttribute::getProductStock($item['product_id'], $item['size']);
                if ($item['quantity'] > $getProductStock) { // 주문 수량이 기존 재고보다 많은 경우 주문 취소
                    $message = $getProductDetails['product_name'] . '(' . $item['size'] . ' 사이즈) 상품의 재고가 부족합니다. 수량을 줄여서 다시 시도해 주세요!';

                    return redirect('/cart')->with('error_message', $message); 
                }


                $cartItem->product_qty     = $item['quantity'];

                $cartItem->save(); // orders_products 테이블에 데이터 삽입


                // 인벤토리 관리 - 주문 시 재고 감소 처리
                $getProductStock = ProductsAttribute::getProductStock($item['product_id'], $item['size']); 
                $newStock = $getProductStock - $item['quantity']; // 기존 재고에서 주문 수량 차감
                ProductsAttribute::where([ 
                    'product_id' => $item['product_id'],
                    'size'       => $item['size']
                ])->update(['stock' => $newStock]);
            }


            // 나중에 필요할 때 사용할 수 있도록 order_id를 세션에 저장 (아이페이, 페이팔 등에서 사용)
            Session::put('order_id', $order_id); 


            DB::commit(); // 데이터베이스 트랜잭션 커밋


            // echo 'Order placed successfully!';
            // exit;


            // 사용자에게 주문 확인 이메일 전송    
            // 참고: 'COD'의 경우 즉시 확인 이메일을 보내지만, 페이팔 등의 경우 결제 완료 후 전송함
            $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); 

            if ($data['payment_gateway'] == 'COD') { // 'COD'인 경우 즉시 이메일 전송
                // 주문 확인 이메일 전송
                $email = Auth::user()->email; 

                // 이메일 뷰에 전달할 데이터/변수
                $messageData = [
                    'email'        => $email,
                    'name'         => Auth::user()->name, 
                    'order_id'     => $order_id,
                    'orderDetails' => $orderDetails
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('주문 안내 - 멀티 벤더 이커머스');
                });

                /*
                // 주문 확인 SMS 전송
                // SMS API 및 cURL을 사용하여 SMS 전송    
                $message = '고객님, 멀티 벤더 이커머스에서 주문하신 ' . $order_id . '번 주문이 성공적으로 접수되었습니다. 배송이 시작되면 다시 안내해 드리겠습니다.';
                $mobile = Auth::user()->moblie; 
                \App\Models\Sms::sendSms($message, $mobile); 
                */


                // PayPal payment gateway integration in Laravel
            } elseif ($data['payment_gateway'] == 'Paypal') {
                // 주문 정보 저장 후 페이팔 컨트롤러로 리다이렉트
                return redirect('/paypal');

                // iyzico 결제 연동    
            } elseif ($data['payment_gateway'] == 'iyzipay') {
                // 주문 정보 저장 후 아이페이 컨트롤러로 리다이렉트
                return redirect('/iyzipay');

            } else { // 'COD'나 페이팔 이외의 결제 수단인 경우
                echo '다른 결제 수단은 준비 중입니다.';
            }


            return redirect('thanks'); // 완료 페이지로 리다이렉트
        }


        return view('front.products.checkout')->with(compact('deliveryAddresses', 'countries', 'getCartItems', 'total_price'));
    }



    // 완료 페이지 렌더링 (주문 완료 후)    
    public function thanks() {
        if (Session::has('order_id')) { // 주문이 완료되어 세션에 order_id가 있는 경우, 장바구니 비우기    
            // 주문 후 장바구니 비우기
            Cart::where('user_id', Auth::user()->id)->delete(); 


            return view('front.products.thanks');
        } else { // 주문이 완료되지 않은 경우
            return redirect('cart'); 
        }
    }



    // 우편번호 사용 가능 여부 확인: 사용자의 배송 주소 우편번호가 데이터베이스에 존재하는지 확인 (AJAX 호출)    
    public function checkPincode(Request $request) {
        if ($request->ajax()) { 
            $data = $request->all(); 


            // cod_pincodes 및 prepaid_pincodes 테이블에서 COD 및 선결제 가능 여부 확인
            // cod_pincodes 테이블 확인
            $codPincodeCount = DB::table('cod_pincodes')->where('pincode', $data['pincode'])->count(); 
    
            // prepaid_pincodes 테이블 확인
            $prepaidPincodeCount = DB::table('prepaid_pincodes')->where('pincode', $data['pincode'])->count(); 

            // 우편번호가 두 테이블 모두에 없는 경우
            if ($codPincodeCount == 0 && $prepaidPincodeCount == 0) {
                echo '해당 우편번호는 배송이 불가능합니다.';
            } else {
                echo '해당 우편번호는 배송이 가능합니다.';
            }
        }
    }

}