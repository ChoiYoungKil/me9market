{{-- 모든 벤더 제품 페이지 표시(front/products/detail.blade.php에서 상점 이름을 클릭할 때) --}} {{-- 이 뷰는 Front/ProductsController.php의
vendorListing() 메소드에서 반환됩니다. --}}
@extends('front.layout.layout')


@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>{{ $getVendorShop }}</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="index.html">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="listing.html">{{ $getVendorShop }}</a>
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


                    <li>{{ $getVendorShop }}</li>



                </ul>
            </div>
            <!-- Shop-Intro /- -->
            <div class="row">



                <!-- Shop-Right-Wrapper -->
                <div class="col-lg-9 col-md-9 col-sm-12">
                    <!-- Page-Bar -->
                    <div class="page-bar clearfix">


                        <!-- //end Toolbar Sorter 2  -->
                    </div>
                    <!-- Page-Bar /- -->


                    <!-- Row-of-Product-Container -->

                    {{-- AJAX를 사용한 정렬 필터. ajax_products_listing.blade.php 확인 --}}
                    <div class="">
                        @include('front.products.vendor_products_listing')
                    </div>

                    <!-- Row-of-Product-Container /- -->



                    {{-- 라라벨 페이지네이션과 부트스트랩 페이지네이션을 사용한 표시 --}}
                    {{-- <div>{{ $vendorProducts->links() }}</div> --}}


                    {{-- 정렬 필터와 함께 사용할 때 발생하는 라라벨 페이지네이션 문제 해결 (정렬이 페이지네이션과 함께 엉키는 문제). 문제의 원인은 페이지네이션 링크(예: 2페이지로 이동)를 클릭할
                    때 URL 쿼리 문자열 매개변수에 페이지 번호(예: 'page=2')가 포함되지만 필터 쿼리 문자열 매개변수(예: '&sort=desc')는 손실되기 때문입니다. 따라서 페이지 번호 쿼리
                    문자열 매개변수에 정렬 필터 쿼리 문자열 매개변수를 항상 추가해야 합니다. --}}
                    {{-- 쿼리 문자열 값 추가: https://laravel.com/docs/9.x/pagination#appending-query-string-values --}}
                    @if (isset($_GET['sort'])) {{-- 정렬 필터가 사용된 경우 --}}
                        <div>
                            {{ $vendorProducts->appends(['sort' => $_GET['sort']])->links() }}
                        </div>
                    @else
                        <div>
                            {{ $vendorProducts->links() }}
                        </div>
                    @endif


                    <div>&nbsp;</div>
                </div>
                <!-- Shop-Right-Wrapper /- -->


                <!-- Shop-Pagination -->



                <!-- Shop-Pagination /- -->


            </div>
        </div>
    </div>
    <!-- Shop-Page /- -->
@endsection