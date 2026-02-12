<?php
// 'enabled' 상태인 섹션과 그 하위 카테고리(서브카테고리 포함)만 가져오기
$sections = \App\Models\Section::sections();
// dd($sections);
?>



<!-- Header -->
<header>
    <!-- Top-Header -->
    <div class="full-layer-outer-header">
        <div class="container clearfix">
            <nav>
                <ul class="primary-nav g-nav">
                    <li>
                        <a href="tel:+201255845857">
                            <i class="fas fa-phone u-c-brand u-s-m-r-9"></i>
                            Telephone: +201255845857</a>
                    </li>
                    <li>
                        <a href="mailto:info@multi-vendore-commerce.com">
                            <i class="fas fa-envelope u-c-brand u-s-m-r-9"></i>
                            E-mail: info@multi-vendore-commerce.com
                        </a>
                    </li>
                </ul>
            </nav>
            <nav>
                <ul class="secondary-nav g-nav">
                    <li>



                        <a>
                            {{-- 사용자가 인증/로그인 상태면 'My Account' 표시, 아니면 'Login/Register' 표시 --}}
                            @if (\Illuminate\Support\Facades\Auth::check()) {{-- 현재 사용자 인증 여부 확인:
                                https://laravel.com/docs/9.x/authentication#determining-if-the-current-user-is-authenticated
                                --}}
                                My Account
                            @else
                                Login/Register
                            @endif

                            <i class="fas fa-chevron-down u-s-m-l-9"></i>
                        </a>
                        <ul class="g-dropdown" style="width:200px">
                            <li>
                                <a href="{{ url('cart') }}">
                                    <i class="fas fa-cog u-s-m-r-9"></i>
                                    My Cart</a>
                            </li>
                            <li>
                                <a href="{{ url('checkout') }}">
                                    <i class="far fa-check-circle u-s-m-r-9"></i>
                                    Checkout</a>
                            </li>



                            {{-- 사용자가 인증/로그인 상태면 'My Account'와 'Logout' 표시, 아니면 'Customer Login'과 'Vendor Login' 표시 --}}
                            @if (\Illuminate\Support\Facades\Auth::check()) {{-- 현재 사용자 인증 여부 확인:
                                https://laravel.com/docs/9.x/authentication#determining-if-the-current-user-is-authenticated
                                --}}
                                <li>
                                    <a href="{{ url('user/account') }}">
                                        <i class="fas fa-sign-in-alt u-s-m-r-9"></i>
                                        My Account
                                    </a>
                                </li>


                                <li>
                                    <a href="{{ url('user/orders') }}">
                                        <i class="fas fa-sign-in-alt u-s-m-r-9"></i>
                                        My Orders
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ url('user/logout') }}">
                                        <i class="fas fa-sign-in-alt u-s-m-r-9"></i>
                                        Logout
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ url('user/login-register') }}">
                                        <i class="fas fa-sign-in-alt u-s-m-r-9"></i>
                                        Customer Login
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('vendor/login-register') }}">
                                        <i class="fas fa-sign-in-alt u-s-m-r-9"></i>
                                        Vendor Login
                                    </a>
                                </li>
                            @endif



                        </ul>
                    </li>
                    <li>
                        <a>EGP
                            <i class="fas fa-chevron-down u-s-m-l-9"></i>
                        </a>
                        <ul class="g-dropdown" style="width:90px">
                            <li>
                                <a href="#" class="u-c-brand">LE EGP</a>
                            </li>
                            <li>
                                <a href="#">($) USD</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a>ENG
                            <i class="fas fa-chevron-down u-s-m-l-9"></i>
                        </a>
                        <ul class="g-dropdown" style="width:70px">
                            <li>
                                <a href="#" class="u-c-brand">ENG</a>
                            </li>
                            <li>
                                <a href="#">ARB</a>
                            </li>
                        </ul>
                </ul>
            </nav>
        </div>
    </div>
    <!-- Top-Header /- -->
    <!-- Mid-Header -->
    <div class="full-layer-mid-header">
        <div class="container">
            <div class="row clearfix align-items-center">
                <div class="col-lg-3 col-md-9 col-sm-6">
                    <div class="brand-logo text-lg-center">


                        <a href="{{ url('/') }}">


                            <img src="{{ asset('front/images/main-logo/main-logo.png') }}"
                                alt="Multi-vendor E-commerce Application" class="app-brand-logo">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 u-d-none-lg">



                    {{-- 웹사이트 검색 폼 (모든 웹사이트 상품 검색) --}}
                    <form class="form-searchbox" action="{{ url('/search-products') }}" method="get">
                        <label class="sr-only" for="search-landscape">Search</label>
                        <input id="search-landscape" type="text" class="text-field" placeholder="Search everything"
                            name="search" @if (isset($_REQUEST['search']) && !empty($_REQUEST['search']))
                            value="{{ $_REQUEST['search'] }}" @endif> {{-- 검색 폼 제출을 위해 "name" HTML 속성을 키/이름으로, "value"
                        HTML 속성을 값으로 사용합니다. 아래 <option> 태그 내부의 "value" HTML 속성도 확인하세요! --}} {{-- 사용자가 검색 폼을 사용하는 경우 --}}
                            <div class="select-box-position">
                                <div class="select-box-wrapper select-hide">
                                    <label class="sr-only" for="select-category">Choose category for search</label>
                                    <select class="select-box" id="select-category" name="section_id">

                                        <option selected="selected" value="">All</option>
                                        @foreach ($sections as $section)
                                            <option value="{{ $section['id'] }}" @if (isset($_REQUEST['section_id']) && !empty($_REQUEST['section_id']) && $_REQUEST['section_id'] == $section['id'])
                                            selected @endif>{{ $section['name'] }}</option> {{-- 상단 검색 바 드롭다운 메뉴 --}}
                                            {{-- 검색 폼 제출을 위해 "value" HTML 속성을 "name" HTML 속성의 값으로 사용합니다. 위쪽 <input> 태그 내부의
                                            "name" HTML 속성도 확인하세요! --}}
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <button id="btn-search" type="submit" class="button button-primary fas fa-search"></button>
                    </form>

                    @php
                        // dd($_GET);
                    @endphp



                </div>
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <nav>
                        <ul class="mid-nav g-nav">
                            <li class="u-d-none-lg">
                                <a href="{{ url('/') }}">
                                    <i class="ion ion-md-home u-c-brand"></i>
                                </a>
                            </li>
                            <li>
                                <a id="mini-cart-trigger">
                                    <i class="ion ion-md-basket"></i>
                                    <span class="item-counter totalCartItems">{{ totalCartItems() }}</span> {{--
                                    totalCartItems() 함수는 composer.json 파일에 등록된 사용자 정의 Helpers/Helper.php 파일에 있습니다. --}}
                                    {{-- 'totalCartItems' CSS 클래스를 생성하여 front/js/custom.js에서 AJAX를 통해 총 장바구니 품목 수를
                                    업데이트합니다. (예: http://127.0.0.1:8000/cart에서 AJAX를 사용하여 장바구니 항목을 삭제할 때 페이지 새로고침 없이 헤더의
                                    숫자가 자동으로 변경되지 않으므로 AJAX 사용) --}}
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Mid-Header /- -->
    <!-- Responsive-Buttons -->
    <div class="fixed-responsive-container">
        <div class="fixed-responsive-wrapper">
            <button type="button" class="button fas fa-search" id="responsive-search"></button>
        </div>
    </div>
    <!-- Responsive-Buttons /- -->



    <!-- Mini Cart Widget -->
    <div id="appendHeaderCartItems"> {{-- 'appendHeaderCartItems' CSS 클래스를 생성하여 front/js/custom.js에서 AJAX를 통해 미니 장바구니
        위젯의 총 장바구니 품목 수를 업데이트합니다. (예: http://127.0.0.1:8000/cart에서 AJAX를 사용하여 장바구니 항목을 삭제할 때 페이지 새로고침 없이 헤더의 숫자가 자동으로
        변경되지 않으므로 AJAX 사용) --}}
        @include('front.layout.header_cart_items')
    </div>
    <!-- Mini Cart Widget /- -->



    <!-- Bottom-Header -->
    <div class="full-layer-bottom-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3">
                    <div class="v-menu v-close">
                        <span class="v-title">
                            <i class="ion ion-md-menu"></i>
                            All Categories
                            <i class="fas fa-angle-down"></i>
                        </span>
                        <nav>
                            <div class="v-wrapper">
                                <ul class="v-list animated fadeIn">



                                    @foreach ($sections as $section)
                                        @if (count($section['categories']) > 0) {{-- 섹션에 하위 카테고리가 있으면 섹션 이름을 표시하고, 없으면 표시하지
                                            않음 --}}
                                            <li class="js-backdrop">
                                                <a href="javascript:;">
                                                    <i class="ion-ios-add-circle"></i>


                                                    {{ $section['name'] }} {{-- 섹션 이름 표시 --}}


                                                    <i class="ion ion-ios-arrow-forward"></i>
                                                </a>
                                                <button class="v-button ion ion-md-add"></button>
                                                <div class="v-drop-right" style="width: 700px;">
                                                    <div class="row">



                                                        @foreach ($section['categories'] as $category) {{-- 섹션의 하위 카테고리 표시 --}}
                                                            <div class="col-lg-4">
                                                                <ul class="v-level-2">
                                                                    <li>
                                                                        <a
                                                                            href="{{ url($category['url']) }}">{{ $category['category_name'] }}</a>
                                                                        <ul>



                                                                            @foreach ($category['sub_categories'] as $subcategory)
                                                                                {{-- 섹션의 하위 카테고리의 하위 서브카테고리 표시 --}}
                                                                                <li>
                                                                                    <a
                                                                                        href="{{ url($subcategory['url']) }}">{{ $subcategory['category_name'] }}</a>
                                                                                </li>
                                                                            @endforeach



                                                                        </ul>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                    @endforeach


                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-9">
                    <ul class="bottom-nav g-nav u-d-none-lg">
                        <li>
                            <a href="{{ url('search-products?search=new-arrivals') }}">New Arrivals
                                <span class="superscript-label-new">NEW</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('search-products?search=best-sellers') }}">Best Seller
                                <span class="superscript-label-hot">HOT</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('search-products?search=featured') }}">Featured
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('search-products?search=discounted') }}">Discounted
                                <span class="superscript-label-discount">>10%</span>
                            </a>
                        </li>
                        <li class="mega-position">
                            <a>More
                                <i class="fas fa-chevron-down u-s-m-l-9"></i>
                            </a>
                            <div class="mega-menu mega-3-colm">
                                <ul>
                                    <li class="menu-title">COMPANY</li>
                                    <li>
                                        <a href="{{ url('about-us') }}" class="u-c-brand">About Us</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('contact') }}">Contact Us</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('faq') }}">FAQ</a>
                                    </li>
                                </ul>
                                <ul>
                                    <li class="menu-title">COLLECTION</li>
                                    <li>
                                        <a href="{{ url('men') }}">Men Clothing</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('women') }}">Women Clothing</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('kids') }}">Kids Clothing</a>
                                    </li>
                                </ul>
                                <ul>
                                    <li class="menu-title">ACCOUNT</li>
                                    <li>
                                        <a href="{{ url('user/account') }}">My Account</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('user/orders') }}">My Orders</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom-Header /- -->
</header>
<!-- Header /- -->