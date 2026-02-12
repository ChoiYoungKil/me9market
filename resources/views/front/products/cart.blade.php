{{-- 참고: cart.blade.php는 FRONT 홈페이지에서 '카트'를 클릭했을 때 열리는 페이지입니다. --}}
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
                        <a href="cart.html">Cart</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->
    <!-- Cart-Page -->
    <div class="page-cart u-s-p-t-80">
        <div class="container">



            {{-- 유효성 검사 오류 표시: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors 및
            https://laravel.com/docs/9.x/blade#validation-errors --}}
            {{-- 세션에 항목이 존재하는지 확인 (has() 메서드 사용):
            https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
            {{-- 관리자 비밀번호 업데이트 성공 시 부트스트랩 성공 메시지: --}}
            {{-- 성공 메시지 표시 --}}
            @if (Session::has('success_message')) <!-- Check vendorRegister() method in Front/VendorController.php -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success:</strong> {{ Session::get('success_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            {{-- 오류 메시지 표시 --}}
            @if (Session::has('error_message')) <!-- Check vendorRegister() method in Front/VendorController.php -->
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ Session::get('error_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            {{-- 오류 메시지 표시 --}}
            @if ($errors->any()) <!-- Check vendorRegister() method in Front/VendorController.php -->
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> @php echo implode('', $errors->all('<div>:message</div>')); @endphp
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif



            <div class="row">
                <div class="col-lg-12">




                    <div id="appendCartItems"> {{-- 장바구니에서 주문 수량을 업데이트할 때 front/js/custom.js에서 AJAX 호출을 허용하기 위해 이 파일을
                        'include'했습니다. --}}
                        @include('front.products.cart_items')
                    </div>





                    {{-- 쿠폰 코드 제출이 한 번만 작동하는 문제를 해결하기 위해, 쿠폰 부분을 cart_items.blade.php에서 여기 cart.blade.php로 옮겼습니다. --}} {{--
                    문제 설명: http://publicvoidlife.blogspot.com/2014/03/on-on-or-event-delegation-explained.html --}}
                    <!-- Coupon -->
                    <div class="coupon-continue-checkout u-s-m-b-60">
                        <div class="coupon-area">
                            <h6>Enter your coupon code if you have one.</h6>
                            <div class="coupon-field">



                                {{-- 참고: 쿠폰을 사용하려면 사용자가 로그인(인증)되어 있어야 합니다. '관리자'와 '벤더' 모두 쿠폰을 추가할 수 있습니다. '벤더'가 추가한 쿠폰은 해당
                                벤더의 제품에만 사용할 수 있지만, '관리자'가 추가한 쿠폰은 모든 제품에 사용할 수 있습니다. --}}

                                <form id="applyCoupon" method="post" action="javascript:void(0)" @if (\Illuminate\Support\Facades\Auth::check()) user=1 @endif> {{-- AJAX를 통한 제출을 위해 jQuery에서
                                    핸들로 사용할 수 있도록 이 <form>에 ID를 생성했습니다. front/js/custom.js 확인 --}} {{-- 로그인(인증)된 사용자만 쿠폰을
                                        사용할 수 있으므로, 사용자가 로그인된 경우 'user = 1'이라는 사용자 정의 HTML 속성을 생성하여 jQuery가 폼 제출에 사용할 수 있도록
                                        합니다. front/js/custom.js 확인 --}} {{-- 참고: AJAX 호출을 사용하여 제출할 것이므로 'action' HTML 속성을
                                        비활성화(action="javascript:void(0)" 사용)해야 합니다. front/js/custom.js 확인 --}}
                                        <label class="sr-only" for="coupon-code">Apply Coupon</label>
                                        <input type="text" class="text-field" placeholder="Enter Coupon Code" id="code"
                                            name="code">
                                        <button type="submit" class="button">Apply Coupon</button>
                                    </form>



                            </div>
                        </div>
                        <div class="button-area">
                            <a href="{{ url('/') }}" class="continue">Continue Shopping</a>
                            <a href="{{ url('/checkout') }}" class="checkout">Proceed to Checkout</a>
                        </div>
                    </div>
                    <!-- Coupon /- -->





                </div>
            </div>
        </div>
    </div>
    <!-- Cart-Page /- -->
@endsection