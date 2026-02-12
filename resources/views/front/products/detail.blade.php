{{-- 참고: front/products/detail.blade.php는 FRONT 홈페이지에서 제품을 클릭하면 열리는 페이지입니다. --}} {{-- $productDetails, $categoryDetails, $totalStock은 Front/ProductsController.php의 detail() 메소드에서 전달됩니다. --}}
@extends('front.layout.layout')


@section('content')
    {{-- 제품 별점 (리뷰 탭) --}}
    <style>
        *{
            margin: 0;
            padding: 0;
        }
        .rate {
            float: left;
            height: 46px;
            padding: 0 10px;
        }
        .rate:not(:checked) > input {
            /* position:absolute; */
            position:inherit;
            top:-9999px;
        }
        .rate:not(:checked) > label {
            float:right;
            width:1em;
            overflow:hidden;
            white-space:nowrap;
            cursor:pointer;
            font-size:30px;
            color:#ccc;
        }
        .rate:not(:checked) > label:before {
            content: '★ ';
        }
        .rate > input:checked ~ label {
            color: #ffc700;    
        }
        .rate:not(:checked) > label:hover,
        .rate:not(:checked) > label:hover ~ label {
            color: #deb217;  
        }
        .rate > input:checked + label:hover,
        .rate > input:checked + label:hover ~ label,
        .rate > input:checked ~ label:hover,
        .rate > input:checked ~ label:hover ~ label,
        .rate > label:hover ~ input:checked ~ label {
            color: #c59b08;
        }
    </style>


    
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Detail</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="javascript:;">Detail</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->
    <!-- Single-Product-Full-Width-Page -->
    <div class="page-detail u-s-p-t-80">
        <div class="container">
            <!-- Product-Detail -->
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">



                    {{-- 마우스 오버 시 제품 이미지를 확대하는 EasyZoom 플러그인 --}}
                    {{-- 나의 EasyZoom (jQuery 이미지 줌 플러그인): https://i-like-robots.github.io/EasyZoom/ --}}

                    <!-- Product-zoom-area -->
                    <div class="easyzoom easyzoom--overlay easyzoom--with-thumbnails"> {{-- EasyZoom plugin --}}
                        <a      href="{{ asset('front/images/product_images/large/' . $productDetails['product_image']) }}">
                            <img src="{{ asset('front/images/product_images/large/' . $productDetails['product_image']) }}" alt="" width="500" height="500" />
                        </a>
                    </div>

                    <div class="thumbnails" style="margin-top: 30px"> {{-- EasyZoom plugin --}}
                        <a      href="{{ asset('front/images/product_images/large/' . $productDetails['product_image']) }}" data-standard="{{ asset('front/images/product_images/small/' . $productDetails['product_image']) }}">
                            <img src="{{ asset('front/images/product_images/small/' . $productDetails['product_image']) }}" width="120" height="120" alt="" />
                        </a>



                        {{-- Show the product Alternative images (`image` in `products_images` table) --}}
                        @foreach ($productDetails['images'] as $image)
                            {{-- EasyZoom plugin --}}
                            <a      href="{{ asset('front/images/product_images/large/' . $image['image']) }}" data-standard="{{ asset('front/images/product_images/small/' . $image['image']) }}">
                                <img src="{{ asset('front/images/product_images/small/' . $image['image']) }}" width="120" height="120" alt="" />
                            </a>
                        @endforeach



                    </div>
                    <!-- Product-zoom-area /- -->
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <!-- Product-details -->
                    <div class="all-information-wrapper">


                        {{-- 현재 비밀번호가 틀리거나 새 비밀번호와 확인 비밀번호가 일치하지 않는 경우의 부트스트랩 오류 코드: --}}
                        {{-- Determining If An Item Exists In The Session (using has() method): https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
                        @if (Session::has('error_message')) <!-- Check AdminController.php, updateAdminPassword() method -->
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error:</strong> {{ Session::get('error_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif


                        {{-- Displaying Laravel Validation Errors: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors --}}    
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif


                        {{-- Displaying The Validation Errors: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors AND https://laravel.com/docs/9.x/blade#validation-errors --}}
                        {{-- Determining If An Item Exists In The Session (using has() method): https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
                        {{-- 관리자 비밀번호 업데이트 성공 시 부트스트랩 성공 메시지: --}}
                        @if (Session::has('success_message')) <!-- Check AdminController.php, updateAdminPassword() method -->
                            <div class="alert alert-success alert-dismissible fade show" role="alert">

                                {{-- There are TWO ways to: Displaying Unescaped Data: https://laravel.com/docs/9.x/blade#displaying-unescaped-data --}}
                                <strong>Success:</strong> @php echo Session::get('success_message') @endphp       {{-- Displaying Unescaped Data: https://laravel.com/docs/9.x/blade#displaying-unescaped-data --}}

                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif



                        <div class="section-1-title-breadcrumb-rating">
                            <div class="product-title">
                                <h1>
                                    <a href="javascript:;">{{ $productDetails['product_name'] }}</a> {{-- $productDetails는 Front/ProductsController.php의 detail() 메소드에서 전달됩니다. --}}
                                </h1>
                            </div>



                            {{-- Breadcrumb --}}
                            <ul class="bread-crumb">
                                <li class="has-separator">
                                    <a href="{{ url('/') }}">Home</a> {{-- 홈 --}}
                                </li>
                                <li class="has-separator">
                                    <a href="javascript:;">{{ $productDetails['section']['name'] }}</a> {{-- 섹션 이름 --}}
                                </li>
                                @php echo $categoryDetails['breadcrumbs'] @endphp {{-- $categoryDetails는 Front/ProductsController.php의 detail() 메소드에서 전달됩니다. --}}
                            </ul>
                            {{-- Breadcrumb --}}



                            <div class="product-rating">
                                <div title="{{ $avgRating }} out of 5 - based on {{ count($ratings) }} Reviews">

                                    {{-- 별점 표시 --}}
                                    @if ($avgStarRating > 0) {{-- 제품이 한 번이라도 평가된 경우 "별" HTML 엔티티 표시 --}}
                                        @php
                                            $star = 1;
                                            while ($star < $avgStarRating):
                                        @endphp

                                                <span style="color: gold; font-size: 17px">&#9733;</span>

                                        @php
                                                $star++;
                                            endwhile;
                                        @endphp
                                        ({{ $avgRating }})
                                    @endif

                                </div>
                            </div>
                        </div>
                        <div class="section-2-short-description u-s-p-y-14">
                            <h6 class="information-heading u-s-m-b-8">Description:</h6>
                            <p>{{ $productDetails['description'] }}</p>
                        </div>
                        <div class="section-3-price-original-discount u-s-p-y-14">

                        

                            @php $getDiscountPrice = \App\Models\Product::getDiscountPrice($productDetails['id']) @endphp

                            <span class="getAttributePrice">{{-- 이 <span>은 jQuery에서 <select> 박스에서 선택된 'size'에 따라 각각의 'price'와 'stock'을 가져오는 데 사용됩니다 (AJAX 호출을 통해). front/js/custom.js 확인 --}}

                                @if ($getDiscountPrice > 0) {{-- 제품 가격에 할인이 있는 경우 --}}
                                    <div class="price">
                                        <h4>EGP{{ $getDiscountPrice }}</h4>
                                    </div>
                                    <div class="original-price">
                                        <span>Original Price:</span>
                                        <span>EGP{{ $productDetails['product_price'] }}</span> {{-- 제품 원래 가격(할인 제외) --}}
                                    </div>
                                @else {{-- 제품 가격에 할인이 없는 경우 --}}
                                    <div class="price">
                                        <h4>EGP{{ $productDetails['product_price'] }}</h4> {{-- 제품 원래 가격(할인 제외) --}}
                                    </div>
                                @endif

                            </span> 



                        </div>
                        <div class="section-4-sku-information u-s-p-y-14">
                            <h6 class="information-heading u-s-m-b-8">Sku Information:</h6>
                            <div class="left">
                                <span>Product Code:</span>
                                <span>{{ $productDetails['product_code'] }}</span>
                            </div>
                            <div class="left">
                                <span>Product Color:</span>
                                <span>{{ $productDetails['product_color'] }}</span>
                            </div>
                            <div class="availability">
                                <span>Availability:</span>


                                @if ($totalStock > 0)
                                    <span>In Stock</span>
                                @else
                                    <span style="color: red">Out of Stock (Sold-out)</span>
                                @endif



                            </div>



                            @if ($totalStock > 0)
                                <div class="left">
                                    <span>Only:</span>
                                    <span>{{ $totalStock }} left</span>
                                </div>
                            @endif



                        </div>



                        {{-- 벤더 상점 이름 표시 (관리자나 슈퍼관리자가 아닌 벤더가 추가한 제품인 경우에만) --}}
                        @if(isset($productDetails['vendor']))
                            <div>
                                {{-- 판매자: {{ $productDetails['vendor']['name'] }} --}}
                                Sold by: <a href="/products/{{ $productDetails['vendor']['id'] }}">
                                            {{ $productDetails['vendor']['vendorbusinessdetails']['shop_name'] }}
                                        </a>
                            </div>
                        @endif



                        {{-- 장바구니 담기 <form> --}} 
                        <form action="{{ url('cart/add') }}" method="Post" class="post-form">
                            @csrf {{-- Preventing CSRF Requests: https://laravel.com/docs/9.x/csrf#preventing-csrf-requests --}}


                            <input type="hidden" name="product_id" value="{{ $productDetails['id'] }}"> {{-- 장바구니 담기 <form> --}} 


                            <div class="section-5-product-variants u-s-p-y-14">



                                {{-- 제품 색상 관리 (`products` 테이블의 `group_code` 컬럼 사용) --}} 
                                @if (count($groupProducts) > 0) {{-- 현재 보고 있는 제품의 `group_code` 컬럼(products 테이블)에 값이 있는 경우 --}}
                                    <div>
                                        <div><strong>Product Colors</strong></div>
                                        <div style="margin-top: 10px">
                                            @foreach ($groupProducts as $product)
                                                <a href="{{ url('product/' . $product['id']) }}">
                                                    <img style="width: 80px" src="{{ asset('front/images/product_images/small/' . $product['product_image']) }}">
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif



                                <div class="sizes u-s-m-b-11" style="margin-top: 20px">
                                    <span>Available Size:</span>
                                    <div class="size-variant select-box-wrapper">
                                        <select class="select-box product-size" id="getPrice" product-id="{{ $productDetails['id'] }}" name="size" required> {{-- Check front/js/custom.js file --}}



                                            <option value="">Select Size</option>
                                            @foreach ($productDetails['attributes'] as $attribute)
                                                <option value="{{ $attribute['size'] }}">{{ $attribute['size'] }}</option>
                                            @endforeach



                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="section-6-social-media-quantity-actions u-s-p-y-14">

                                
                                <div class="quantity-wrapper u-s-m-b-22">
                                    <span>Quantity:</span>
                                    <div class="quantity">
                                        <input class="quantity-text-field" type="number" name="quantity" value="1">
                                    </div>
                                </div>
                                <div>
                                    <button class="button button-outline-secondary" type="submit">Add to cart</button>
                                    <button class="button button-outline-secondary far fa-heart u-s-m-l-6"></button>
                                    <button class="button button-outline-secondary far fa-envelope u-s-m-l-6"></button>
                                </div>



                            </div>
                        </form>


                        {{-- PIN 코드 가용성 확인: 사용자의 배송 주소 PIN 코드가 데이터베이스(`cod_pincodes` 및 `prepaid_pincodes`)에 존재하는지 여부를 AJAX를 통해 확인합니다. front/js/custom.js 확인 --}} 
                        <br><br><b>Delivery</b>
                        <input type="text" id="pincode" placeholder="Check Pincode" required>
                        <button type="button" id="checkPincode">Go</button> {{-- We'll use that checkPincode HTML id attribute in front/js/custom.js as a handle for jQuery --}}


                    </div>
                    <!-- Product-details /- -->
                </div>
            </div>
            <!-- Product-Detail /- -->
            <!-- Detail-Tabs -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="detail-tabs-wrapper u-s-p-t-80">
                        <div class="detail-nav-wrapper u-s-m-b-30">
                            <ul class="nav single-product-nav justify-content-center">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#video">Product Video</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#detail">Product Details</a>
                                </li>
                                <li class="nav-item">
                                    {{-- <a class="nav-link" data-toggle="tab" href="#review">Reviews (15)</a> --}}
                                    <a class="nav-link" data-toggle="tab" href="#review">Reviews {{ count($ratings) }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <!-- Description-Tab -->
                            <div class="tab-pane fade active show" id="video">
                                <div class="description-whole-container">



                                    @if ($productDetails['product_video'])
                                        <video controls>
                                            <source src="{{ url('front/videos/product_videos/' . $productDetails['product_video']) }}" type="video/mp4">
                                        </video>
                                    @else
                                        Product Video does not exist    
                                    @endif



                                </div>
                            </div>
                            <!-- Description-Tab /- -->
                            <!-- Details-Tab -->
                            <div class="tab-pane fade" id="detail">
                                <div class="specification-whole-container">
                                    <div class="spec-table u-s-m-b-50">
                                        <h4 class="spec-heading">Product Details</h4>
                                        <table>



                                            @php
                                                $productFilters = \App\Models\ProductsFilter::productFilters(); // 모든(활성/enabled) 필터 가져오기
                                                // dd($productFilters);
                                            @endphp

                                            @foreach ($productFilters as $filter) {{-- 모든(활성/enabled) 필터 표시 --}}
                                                @php
                                                    // echo '<pre>', var_dump($product), '</pre>';
                                                    // exit;
                                                    // echo '<pre>', var_dump($filter), '</pre>';
                                                    // exit;
                                                // dd($filter);

                                                @if (isset($productDetails['category_id'])) {{-- AJAX 호출에서 전달됨 (Admin/FilterController.php의 categoryFilters() 메소드를 통해), 또한 '제품 추가'가 아닌 '제품 편집'의 경우(Admin/ProductsController의 addEditProduct() 메소드) 위의 if 조건에서도 올 수 있음 --}}
                                                    @php
                                                        // dd($filter);
                                                        
                                                        // `products_filters` 테이블의 모든 필터에 대해, filterAvailable() 메서드를 사용하여 필터의 `cat_ids`를 가져온 후, 현재 카테고리 ID가 ($productDetails['category_id'] 변수와 URL에 따라) 필터의 `cat_ids`에 존재하는지 확인합니다. 존재하면 필터를 표시하고, 아니면 표시하지 않습니다.
                                                        $filterAvailable = \App\Models\ProductsFilter::filterAvailable($filter['id'], $productDetails['category_id']);
                                                    @endphp

                                                    @if ($filterAvailable == 'Yes') {{-- 필터의 cat_ids에 현재 productDetails['category_id']가 포함된 경우 --}}

                                                        <tr>
                                                            <td>{{ $filter['filter_name'] }}</td>
                                                            <td>
                                                                @foreach ($filter['filter_values'] as $value) {{-- 제품 필터의 관련 값 표시 --}}
                                                                    @php
                                                                        // echo '<pre>', var_dump($value), '</pre>'; exit;
                                                                    @endphp
                                                                    @if (!empty($productDetails[$filter['filter_column']]) && $productDetails[$filter['filter_column']] == $value['filter_value']) {{-- $value['filter_value'] is like '4GB' --}} {{-- $productDetails[$filter['filter_column']]    is like    $productDetails['screen_size']    which in turn, may be equal to    '5 to 5.4 in' --}}
                                                                        {{ ucwords($value['filter_value']) }}
                                                                    @endif
                                                                @endforeach
                                                            </td>
                                                        </tr>

                                                    @endif
                                                @endif
                                            @endforeach



                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Specifications-Tab /- -->
                            <!-- Reviews-Tab -->
                            <div class="tab-pane fade" id="review">
                                <div class="review-whole-container">
                                    <div class="row r-1 u-s-m-b-26 u-s-p-b-22">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="total-score-wrapper">
                                                <h6 class="review-h6">Average Rating</h6>
                                                <div class="circle-wrapper">
                                                    <h1>{{ $avgRating }}</h1>
                                                </div>
                                                <h6 class="review-h6">Based on {{ count($ratings) }} Reviews</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="total-star-meter">
                                                <div class="star-wrapper">
                                                    <span>5 Stars</span>
                                                    <div class="star">
                                                        <span style='width:0'></span>
                                                    </div>
                                                    <span>({{ $ratingFiveStarCount }})</span>
                                                </div>
                                                <div class="star-wrapper">
                                                    <span>4 Stars</span>
                                                    <div class="star">
                                                        <span style='width:0'></span>
                                                    </div>
                                                    <span>({{ $ratingFourStarCount }})</span>
                                                </div>
                                                <div class="star-wrapper">
                                                    <span>3 Stars</span>
                                                    <div class="star">
                                                        <span style='width:0'></span>
                                                    </div>
                                                    <span>({{ $ratingThreeStarCount }})</span>
                                                </div>
                                                <div class="star-wrapper">
                                                    <span>2 Stars</span>
                                                    <div class="star">
                                                        <span style='width:0'></span>
                                                    </div>
                                                    <span>({{ $ratingTwoStarCount }})</span>
                                                </div>
                                                <div class="star-wrapper">
                                                    <span>1 Star</span>
                                                    <div class="star">
                                                        <span style='width:0'></span>
                                                    </div>
                                                    <span>({{ $ratingOneStarCount }})</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row r-2 u-s-m-b-26 u-s-p-b-22">
                                        <div class="col-lg-12">


                                            {{-- 제품 별점 (리뷰 탭에서). --}}
                                            <form method="POST" action="{{ url('add-rating') }}" name="formRating" id="formRating">
                                                @csrf {{-- Preventing CSRF Requests: https://laravel.com/docs/9.x/csrf#preventing-csrf-requests --}}

                                                <input type="hidden" name="product_id" value="{{ $productDetails['id'] }}">
                                                <div class="your-rating-wrapper">
                                                    <h6 class="review-h6">Your Review matters.</h6>
                                                    <h6 class="review-h6">Have you used this product before?</h6>
                                                    <div class="star-wrapper u-s-m-b-8">


                                                        {{-- 제품 별점 (리뷰 탭). --}}
                                                        <div class="rate">
                                                            <input style="display: none" type="radio" id="star5" name="rating" value="5" />
                                                            <label for="star5" title="text">5 stars</label>

                                                            <input style="display: none" type="radio" id="star4" name="rating" value="4" />
                                                            <label for="star4" title="text">4 stars</label>

                                                            <input style="display: none" type="radio" id="star3" name="rating" value="3" />
                                                            <label for="star3" title="text">3 stars</label>

                                                            <input style="display: none" type="radio" id="star2" name="rating" value="2" />
                                                            <label for="star2" title="text">2 stars</label>

                                                            <input style="display: none" type="radio" id="star1" name="rating" value="1" />
                                                            <label for="star1" title="text">1 star</label>
                                                        </div>


                                                    </div>
                                                        <textarea class="text-area u-s-m-b-8" id="review-text-area" placeholder="Your Review" name="review" required></textarea>
                                                        <button class="button button-outline-secondary">Submit Review</button>
                                                    {{-- </form> --}}
                                                </div>
                                            </form>


                                        </div>
                                    </div>
                                    <!-- Get-Reviews -->
                                    <div class="get-reviews u-s-p-b-22">
                                        <!-- Review-Options -->
                                        <div class="review-options u-s-m-b-16">
                                            <div class="review-option-heading">
                                                <h6>Reviews
                                                    <span> ({{ count($ratings) }}) </span>
                                                </h6>
                                            </div>
                                        </div>
                                        <!-- Review-Options /- -->
                                        <!-- All-Reviews -->
                                        <div class="reviewers">

                                            {{-- Display/Show user's Ratings --}}
                                            @if (count($ratings) > 0) {{-- 제품에 대한 별점이 있는 경우 --}}
                                                @foreach($ratings as $rating)
                                                    <div class="review-data">
                                                        <div class="reviewer-name-and-date">
                                                            <h6 class="reviewer-name">{{ $rating['user']['name'] }}</h6>
                                                            <h6 class="review-posted-date">{{ date('d-m-Y H:i:s', strtotime($rating['created_at'])) }}</h6>
                                                        </div>
                                                        <div class="reviewer-stars-title-body">
                                                            <div class="reviewer-stars">


                                                                {{-- 리뷰/평점의 별점 표시 --}}
                                                                @php
                                                                    $count = 0;

                                                                    // Show the stars
                                                                    while ($count < $rating['rating']): // while $count is 0, 1, 2, 3, 4, or 5 Stars
                                                                @endphp

                                                                        <span style="color: gold">&#9733;</span> {{-- "BLACK STAR" HTML Entity --}} {{-- HTML Entities: https://www.w3schools.com/html/html_entities.asp --}}

                                                                @php
                                                                        $count++;
                                                                    endwhile;
                                                                @endphp


                                                            </div>
                                                            <p class="review-body">
                                                                {{ $rating['review'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                        </div>
                                        <!-- All-Reviews /- -->
                                        <!-- Pagination-Review -->

                                        <!-- Pagination-Review /- -->
                                    </div>
                                    <!-- Get-Reviews /- -->
                                </div>
                            </div>
                            <!-- Reviews-Tab /- -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Detail-Tabs /- -->
            <!-- Different-Product-Section -->
            <div class="detail-different-product-section u-s-p-t-80">
                <!-- Similar-Products -->
                <section class="section-maker">
                    <div class="container">
                        <div class="sec-maker-header text-center">
                            <h3 class="sec-maker-h3">Similar Products</h3>
                        </div>
                        <div class="slider-fouc">
                            <div class="products-slider owl-carousel" data-item="4">



                                {{-- 동일한 카테고리의 다른 제품을 가져와 유사한 제품(또는 관련 제품) 표시 (기능) --}}        
                                @foreach ($similarProducts as $product)
                                    <div class="item">
                                        <div class="image-container">
                                            <a class="item-img-wrapper-link" href="{{ url('product/' . $product['id']) }}">



                                                @php
                                                    $product_image_path = 'front/images/product_images/small/' . $product['product_image'];
                                                @endphp
                        
                                                @if (!empty($product['product_image']) && file_exists($product_image_path)) {{-- 제품 이미지가 데이터베이스 테이블과 파일 시스템(서버) 모두에 존재하는 경우 --}}
                                                    <img class="img-fluid" src="{{ asset($product_image_path) }}" alt="Product">
                                                @else {{-- 더미 이미지 표시 --}}
                                                    <img class="img-fluid" src="{{ asset('front/images/product_images/small/no-image.png') }}" alt="Product">
                                                @endif



                                            </a>
                                            <div class="item-action-behaviors">
                                                <a class="item-quick-look" data-toggle="modal" href="#quick-view">Quick Look</a>
                                                <a class="item-mail" href="javascript:void(0)">Mail</a>
                                                <a class="item-addwishlist" href="javascript:void(0)">Add to Wishlist</a>
                                                <a class="item-addCart" href="javascript:void(0)">Add to Cart</a>
                                            </div>
                                        </div>
                                        <div class="item-content">
                                            <div class="what-product-is">
                                                <ul class="bread-crumb">
                                                    <li class="has-separator">



                                                        <a href="shop-v1-root-category.html">{{ $product['product_code'] }}</a>
                                                    </li>
                                                    <li class="has-separator">
                                                        <a href="listing.html">{{ $product['product_color'] }}</a>
                                                    </li>
                                                    <li>
                                                        <a href="listing.html">{{ $product['brand']['name'] }}</a>



                                                    </li>
                                                </ul>
                                                <h6 class="item-title">
                                                    <a href="{{ url('product/' . $product['id']) }}">{{ $product['product_name'] }}</a>
                                                </h6>

                                            </div>



                                            {{-- 제품의 최종 가격을 결정하기 위해 Product.php 모델의 정적 getDiscountPrice() 메서드를 호출합니다. 제품은 '카테고리' 할인 또는 '제품' 할인 두 가지로 인해 할인을 받을 수 있기 때문입니다. --}}
                                            @php
                                                $getDiscountPrice = \App\Models\Product::getDiscountPrice($product['id']);
                                            @endphp

                                            @if ($getDiscountPrice > 0) {{-- 가격에 할인이 있는 경우, 할인 전 가격(원래 가격)과 할인 후 가격(새 가격)을 표시합니다. --}}
                                                <div class="price-template">
                                                    <div class="item-new-price">
                                                        EGP{{ $getDiscountPrice }} 
                                                    </div>
                                                    <div class="item-old-price">
                                                        EGP{{ $product['product_price'] }}
                                                    </div>
                                                </div>
                                            @else {{-- 가격에 할인이 없는 경우, 원래 가격을 표시합니다. --}}
                                                <div class="price-template">
                                                    <div class="item-new-price">
                                                        EGP{{ $product['product_price'] }}
                                                    </div>
                                                </div>
                                            @endif



                                        </div>
                                        <div class="tag new">
                                            <span>NEW</span>
                                        </div>
                                    </div>
                                @endforeach



                            </div>
                        </div>
                    </div>
                </section>
                <!-- Similar-Products /- -->
                <!-- Recently-View-Products  -->
                <section class="section-maker">
                    <div class="container">
                        <div class="sec-maker-header text-center">
                            <h3 class="sec-maker-h3">Recently Viewed Products</h3>
                        </div>
                        <div class="slider-fouc">
                            <div class="products-slider owl-carousel" data-item="4">




                                {{-- 최근 본 상품(아이템) 기능 --}}
                                @foreach ($recentlyViewedProducts as $product)
                                    <div class="item">
                                        <div class="image-container">
                                            <a class="item-img-wrapper-link" href="{{ url('product/' . $product['id']) }}">



                                                @php
                                                    $product_image_path = 'front/images/product_images/small/' . $product['product_image'];
                                                @endphp
                        
                                                @if (!empty($product['product_image']) && file_exists($product_image_path)) {{-- 제품 이미지가 데이터베이스 테이블과 파일 시스템(서버) 모두에 존재하는 경우 --}}
                                                    <img class="img-fluid" src="{{ asset($product_image_path) }}" alt="Product">
                                                @else {{-- 더미 이미지 표시 --}}
                                                    <img class="img-fluid" src="{{ asset('front/images/product_images/small/no-image.png') }}" alt="Product">
                                                @endif



                                            </a>
                                            <div class="item-action-behaviors">
                                                <a class="item-quick-look" data-toggle="modal" href="#quick-view">Quick Look</a>
                                                <a class="item-mail" href="javascript:void(0)">Mail</a>
                                                <a class="item-addwishlist" href="javascript:void(0)">Add to Wishlist</a>
                                                <a class="item-addCart" href="javascript:void(0)">Add to Cart</a>
                                            </div>
                                        </div>
                                        <div class="item-content">
                                            <div class="what-product-is">
                                                <ul class="bread-crumb">
                                                    <li class="has-separator">



                                                        <a href="shop-v1-root-category.html">{{ $product['product_code'] }}</a>
                                                    </li>
                                                    <li class="has-separator">
                                                        <a href="listing.html">{{ $product['product_color'] }}</a>
                                                    </li>
                                                    <li>
                                                        <a href="listing.html">{{ $product['brand']['name'] }}</a>



                                                    </li>
                                                </ul>
                                                <h6 class="item-title">
                                                    <a href="{{ url('product/' . $product['id']) }}">{{ $product['product_name'] }}</a>
                                                </h6>
                                            </div>



                                            {{-- 제품의 최종 가격을 결정하기 위해 Product.php 모델의 정적 getDiscountPrice() 메서드를 호출합니다. 제품은 '카테고리' 할인 또는 '제품' 할인 두 가지로 인해 할인을 받을 수 있기 때문입니다. --}}
                                            @php
                                                $getDiscountPrice = \App\Models\Product::getDiscountPrice($product['id']);
                                            @endphp

                                            @if ($getDiscountPrice > 0) {{-- 가격에 할인이 있는 경우, 할인 전 가격(원래 가격)과 할인 후 가격(새 가격)을 표시합니다. --}}
                                                <div class="price-template">
                                                    <div class="item-new-price">
                                                        EGP{{ $getDiscountPrice }} 
                                                    </div>
                                                    <div class="item-old-price">
                                                        EGP{{ $product['product_price'] }}
                                                    </div>
                                                </div>
                                            @else {{-- 가격에 할인이 없는 경우, 원래 가격을 표시합니다. --}}
                                                <div class="price-template">
                                                    <div class="item-new-price">
                                                        EGP{{ $product['product_price'] }}
                                                    </div>
                                                </div>
                                            @endif



                                        </div>
                                        <div class="tag new">
                                            <span>NEW</span>
                                        </div>
                                    </div>
                                @endforeach



                            </div>
                        </div>
                    </div>
                </section>
                <!-- Recently-View-Products /- -->
            </div>
            <!-- Different-Product-Section /- -->
        </div>
    </div>
    <!-- Single-Product-Full-Width-Page /- -->
@endsection