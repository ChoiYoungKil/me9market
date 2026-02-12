{{-- 이 페이지는 Front/ProductsController.php 내부의 thanks() 메소드에 의해 렌더링됩니다. --}}
@extends('front.layout.layout')


@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Cart</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="index.html">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="#">Thanks</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->
    <!-- Cart-Page -->
    <div class="page-cart u-s-p-t-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-12" align="center">
                    <h3>YOUR ORDER HAS BEEN PLACED SUCCESSFULLY</h3>
                    <p>Your order number is {{ Session::get('order_id') }} and Grand Total is INR
                        {{ Session::get('grand_total') }}</p> {{-- 주문 번호는 orders 데이터베이스 테이블의 주문 id입니다. 우리는
                    Front/ProductsController.php의 checkout() 메소드에서 세션에 주문 ID를 저장했습니다. --}} {{-- 데이터 검색:
                    https://laravel.com/docs/10.x/session#retrieving-data --}}
                </div>
            </div>
        </div>
    </div>
    <!-- Cart-Page /- -->
@endsection



{{-- 주문을 완료한 후 세션에서 일부 데이터 삭제/제거 --}}
@php
    use Illuminate\Support\Facades\Session;

    Session::forget('grand_total');  // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
    Session::forget('order_id');     // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
    Session::forget('couponCode');   // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
    Session::forget('couponAmount'); // Deleting Data: https://laravel.com/docs/9.x/session#deleting-data
@endphp