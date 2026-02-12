{{-- 참고: listing.blade.php는 FRONT 홈페이지에서 카테고리를 클릭했을 때 열리는 페이지입니다. (Front/ProductsController.php의 listing() 메소드에 의해 렌더링됨)
--}}
@extends('front.layout.layout')


@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Shop</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="index.html">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="listing.html">Shop</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->

    <!-- Shop-Page -->
    <div class="page-shop u-s-p-t-80">
        <div class="container">
            <!-- Shop-Intro -->
            <div class="shop-intro">
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <a href="{{ url('/') }}">Home</a>
                    </li>


                    {{-- 브레드크럼 --}}
                    @php echo $categoryDetails['breadcrumbs']; @endphp



                </ul>
            </div>
            <!-- Shop-Intro /- -->
            <div class="row">



                {{-- 리스팅 페이지 사이드바(제품 필터(size, color...)) 포함 --}}
                @include('front.products.filters')



                <!-- Shop-Right-Wrapper -->
                <div class="col-lg-9 col-md-9 col-sm-12">
                    <!-- Page-Bar -->
                    <div class="page-bar clearfix">



                        {{-- 검색 폼이 front/layout/header.blade.php에서 사용되지 않는 경우. 검색 폼을 사용하는 경우 필터는 숨겨지고 작동하지 않습니다. --}}
                        @if (!isset($_REQUEST['search']))


                            <!-- Toolbar Sorter 1  -->
                            {{-- AJAX 없는 정렬 필터 (HTML <form> 및 jQuery 사용). 관련 스크립트는 front/js/custom.js 파일을 확인하세요. --}}
                                {{-- 참고: 백엔드로 <form>을 제출하는 데는 두 가지 방법이 있습니다. 첫째, <button type="submit">을 사용하는 일반적인 방법, 둘째,
                                        <input> 필드의 "value" 속성을 전송하여 AJAX를 사용하는 방법 --}}
                                        <form name="sortProducts" id="sortProducts"> {{-- "action" 속성이 없으면 <form> 데이터를 동일한 페이지로
                                                제출하고, "method" 속성이 없으면 기본 "method"인 "GET"을 사용합니다 --}}

                                                {{-- AJAX를 사용한 정렬 필터. ajax_products_listing.blade.php 확인 --}}
                                                <input type="hidden" name="url" id="url" value="{{ $url }}"> {{-- $url은
                                                Front/ProductsController.php의 listing() 메소드에서 전달됩니다. --}}

                                                <div class="toolbar-sorter">
                                                    <div class="select-box-wrapper">
                                                        <label class="sr-only" for="sort-by">Sort By</label>
                                                        <select name="sort" id="sort" class="select-box">
                                                            {{-- <option selected="selected" value="">Sort By: Best Selling
                                                            </option> --}}
                                                            <option value="" selected>Select</option>
                                                            <option value="product_latest" @if(isset($_GET['sort']) && $_GET['sort'] == 'product_latest') selected @endif>Sort By: Latest
                                                            </option>
                                                            <option value="price_lowest" @if(isset($_GET['sort']) && $_GET['sort'] == 'price_lowest') selected @endif>Sort By: Lowest
                                                                Price</option>
                                                            <option value="price_highest" @if(isset($_GET['sort']) && $_GET['sort'] == 'price_highest') selected @endif>Sort By: Highest
                                                                Price</option>
                                                            <option value="name_a_z" @if(isset($_GET['sort']) && $_GET['sort'] == 'name_a_z') selected @endif>Sort By: Name A - Z
                                                            </option>
                                                            <option value="name_z_a" @if(isset($_GET['sort']) && $_GET['sort'] == 'name_z_a') selected @endif>Sort By: Name Z - A
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                            <!-- //end Toolbar Sorter 1  -->


                        @endif



                                        <!-- Toolbar Sorter 2  -->
                                        <div class="toolbar-sorter-2">
                                            <div class="select-box-wrapper">
                                                <label class="sr-only" for="show-records">Show Records Per Page</label>
                                                <select class="select-box" id="show-records">
                                                    <option selected="selected" value="">Showing:
                                                        {{ count($categoryProducts) }}</option>
                                                    <option value="">Showing: All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- //end Toolbar Sorter 2  -->
                    </div>
                    <!-- Page-Bar /- -->


                    <!-- Row-of-Product-Container -->

                    {{-- AJAX를 사용한 정렬 필터. ajax_products_listing.blade.php 확인 --}}
                    <div class="filter_products">
                        @include('front.products.ajax_products_listing')
                    </div>

                    <!-- Row-of-Product-Container /- -->



                    {{-- 라라벨 페이지네이션과 부트스트랩 페이지네이션을 사용한 표시 --}}
                    {{-- <div>{{ $categoryProducts->links() }}</div> --}}



                    {{-- 검색 폼이 front/layout/header.blade.php에서 사용되지 않는 경우. 검색 폼을 사용하는 경우 필터는 숨겨지고 작동하지 않습니다. --}}
                    @if (!isset($_REQUEST['search']))


                        {{-- 정렬 필터와 함께 사용할 때 발생하는 라라벨 페이지네이션 문제 해결 (정렬이 페이지네이션과 함께 엉키는 문제). 문제의 원인은 페이지네이션 링크(예: 2페이지로 이동)를 클릭할
                        때 URL 쿼리 문자열 매개변수에 페이지 번호(예: 'page=2')가 포함되지만 필터 쿼리 문자열 매개변수(예: '&sort=desc')는 손실되기 때문입니다. 따라서 페이지 번호 쿼리
                        문자열 매개변수에 정렬 필터 쿼리 문자열 매개변수를 항상 추가해야 합니다. --}}
                        {{-- 쿼리 문자열 값 추가: https://laravel.com/docs/9.x/pagination#appending-query-string-values --}}
                        @if (isset($_GET['sort'])) {{-- 정렬 필터가 사용된 경우 --}}
                            <div>
                                {{ $categoryProducts->appends(['sort' => $_GET['sort']])->links() }} {{-- 쿼리 문자열 값 추가:
                                https://laravel.com/docs/9.x/pagination#appending-query-string-values --}} {{-- 페이지네이션 결과 표시:
                                https://laravel.com/docs/9.x/pagination#displaying-pagination-results --}}
                            </div>
                        @else
                            <div>
                                {{ $categoryProducts->links() }} {{-- 페이지네이션 결과 표시:
                                https://laravel.com/docs/9.x/pagination#displaying-pagination-results --}}
                            </div>
                        @endif


                    @endif


                    <div>&nbsp;</div>

                    {{-- 카테고리 및 하위 카테고리 설명 표시 --}}
                    <div>{{ $categoryDetails['categoryDetails']['description'] }}</div>



                </div>
                <!-- Shop-Right-Wrapper /- -->


                <!-- Shop-Pagination -->


                <!-- Shop-Pagination /- -->


            </div>
        </div>
    </div>
    <!-- Shop-Page /- -->
@endsection