{{-- 참고: 이 페이지(view)는 Front/ProductsController.php의 checkout() 메소드에 의해 렌더링됩니다. --}}
@extends('front.layout.layout')


@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Checkout</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="index.html">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="checkout.html">Checkout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->
    <!-- Checkout-Page -->
    <div class="page-checkout u-s-p-t-80">
        <div class="container">

            {{-- 다음 HTML 폼 유효성 검사 오류 표시: (Front/ProductsController.php의 checkout() 메소드 확인) --}}
            {{-- 세션에 항목이 존재하는지 확인 (has() 메서드 사용):
            https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
            @if (Session::has('error_message')) <!-- AdminController.php의 updateAdminPassword() 메소드 확인 -->
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ Session::get('error_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif



            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <!-- Second Accordion /- -->

                    <div class="row">
                        <!-- Billing-&-Shipping-Details -->
                        <div class="col-lg-6" id="deliveryAddresses"> {{-- jQuery AJAX가 이 페이지를 새로 고칠 때 핸들로 사용하기 위해
                            id="deliveryAddresses"를 생성했습니다. front/js/custom.js 확인 --}}





                            @include('front.products.delivery_addresses')



                        </div>
                        <!-- Billing-&-Shipping-Details /- -->
                        <!-- Checkout -->
                        <div class="col-lg-6">



                            {{-- 사용자가 배송 주소와 결제 방법을 제출하는 전체 HTML 폼 --}}
                            <form name="checkoutForm" id="checkoutForm" action="{{ url('/checkout') }}" method="post">
                                @csrf {{-- Preventing CSRF Requests:
                                https://laravel.com/docs/9.x/csrf#preventing-csrf-requests --}}




                                @if (count($deliveryAddresses) > 0) {{-- 현재 인증/로그인된 사용자에 대한 $deliveryAddresses가 있는지 확인 --}}
                                    {{-- $deliveryAddresses 변수는 Front/ProductsController.php의 checkout() 메소드에서 전달됩니다. --}}

                                    <h4 class="section-h4">Delivery Addresses</h4>

                                    @foreach ($deliveryAddresses as $address)
                                        <div class="control-group" style="float: left; margin-right: 5px">
                                            {{-- 사용자 정의 HTML 데이터 속성(shipping_charges, total_price, coupon_amount, codpincodeCount,
                                            prepaidpincodeCount)을 사용하여 "주문 내역" 섹션의 계산을 변경하는 데 jQuery 핸들로 사용합니다. front/js/custom.js
                                            파일 확인 --}}
                                            <input type="radio" id="address{{ $address['id'] }}" name="address_id"
                                                value="{{ $address['id'] }}" shipping_charges="{{ $address['shipping_charges'] }}"
                                                total_price="{{ $total_price }}"
                                                coupon_amount="{{ \Illuminate\Support\Facades\Session::get('couponAmount') }}"
                                                codpincodeCount="{{ $address['codpincodeCount'] }}"
                                                prepaidpincodeCount="{{ $address['prepaidpincodeCount'] }}"> {{-- $total_price 변수는
                                            Front/ProductsController.php의 checkout() 메소드에서 전달됩니다. --}} {{-- <label> HTML 요소가 해당
                                                <input>을 가리킬 수 있도록 주소의 고유 ID를 가져오기 위해 사용자 정의 HTML 속성 id="address{{ $address['id']
                                                }}"를 생성했습니다. --}}
                                        </div>
                                        <div>
                                            <label class="control-label" for="address{{ $address['id'] }}">
                                                {{ $address['name'] }}, {{ $address['address'] }}, {{ $address['city'] }},
                                                {{ $address['state'] }}, {{ $address['country'] }} ({{ $address['mobile'] }})
                                            </label>
                                            <a href="javascript:;" data-addressid="{{ $address['id'] }}" class="removeAddress"
                                                style="float: right; margin-left: 10px">Remove</a> {{-- <a> 링크가 클릭되는 것을 방지하기 위해
                                                href="javascript:;"를 사용했습니다. (<a> 기능 또는 작업을 중지). jQuery AJAX를 사용하여 이 링크를 클릭할 것이기
                                                    때문입니다. front/js/custom.js 확인 --}} {{-- front/js/custom.js에서 AJAX 요청의 핸들로
                                                    class="removeAddress"를 사용합니다. --}}
                                                    <a href="javascript:;" data-addressid="{{ $address['id'] }}" class="editAddress"
                                                        style="float: right">Edit</a> {{-- <a> 링크가 클릭되는 것을 방지하기 위해
                                                        href="javascript:;"를 사용했습니다. (AJAX 요청을 위해). front/js/custom.js 확인 --}} {{--
                                                        front/js/custom.js에서 AJAX 요청의 핸들로 class="editAddress"를 사용합니다. --}}
                                        </div>
                                    @endforeach
                                    <br>
                                @endif


                                <h4 class="section-h4">Your Order</h4>
                                <div class="order-table">
                                    <table class="u-s-m-b-13">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>



                                            {{-- 장바구니에 있는 모든 제품의 총 가격을 계산하기 위해 이 $total_price를 foreach 루프 안에 배치합니다. 다음
                                            @endforeach 앞의 루프 끝 부분을 확인하세요. --}}
                                            @php $total_price = 0 @endphp

                                            @foreach ($getCartItems as $item) {{-- $getCartItems is passed in from cart()
                                                method in Front/ProductsController.php --}}
                                                @php
                                                    $getDiscountAttributePrice = \App\Models\Product::getDiscountAttributePrice($item['product_id'], $item['size']); // from the `products_attributes` table, not the `products` table
                                                    // dd($getDiscountAttributePrice);
                                                @endphp


                                                <tr>
                                                    <td>
                                                        <a href="{{ url('product/' . $item['product_id']) }}">
                                                            <img width="50px"
                                                                src="{{ asset('front/images/product_images/small/' . $item['product']['product_image']) }}"
                                                                alt="Product">
                                                            <h6 class="order-h6">{{ $item['product']['product_name'] }}
                                                                <br>
                                                                {{ $item['size'] }}/{{ $item['product']['product_color'] }}
                                                            </h6>
                                                        </a>
                                                        <span class="order-span-quantity">x {{ $item['quantity'] }}</span>
                                                    </td>
                                                    <td>
                                                        <h6 class="order-h6">
                                                            EGP{{ $getDiscountAttributePrice['final_price'] * $item['quantity'] }}
                                                        </h6> {{-- 모든 제품의 가격 (할인 후(있는 경우)) (= 가격(할인 후) * 제품 수량) --}}
                                                    </td>
                                                </tr>



                                                {{-- 장바구니에 있는 모든 제품의 총 가격을 계산하기 위해 foreach 루프 내부에 배치되었습니다. --}}
                                                @php $total_price = $total_price + ($getDiscountAttributePrice['final_price'] * $item['quantity']) @endphp
                                            @endforeach


                                            <tr>
                                                <td>
                                                    <h3 class="order-h3">Subtotal</h3>
                                                </td>
                                                <td>
                                                    <h3 class="order-h3">EGP{{ $total_price }}</h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h6 class="order-h6">Shipping Charges</h6>
                                                </td>
                                                <td>
                                                    <h6 class="order-h6">
                                                        <span class="shipping_charges">EGP0</span>
                                                    </h6>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h6 class="order-h6">Coupon Discount</h6>
                                                </td>
                                                <td>
                                                    <h6 class="order-h6">

                                                        @if (\Illuminate\Support\Facades\Session::has('couponAmount')) {{--
                                                            Front/ProductsController.php의 applyCoupon() 메소드 내부에서 세션 변수에
                                                            'couponAmount'를 저장했습니다. --}}
                                                            <span
                                                                class="couponAmount">EGP{{ \Illuminate\Support\Facades\Session::get('couponAmount') }}</span>
                                                        @else
                                                            EGP0
                                                        @endif
                                                    </h6>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h3 class="order-h3">Grand Total</h3>
                                                </td>
                                                <td>
                                                    <h3 class="order-h3">
                                                        <strong
                                                            class="grand_total">EGP{{ $total_price - \Illuminate\Support\Facades\Session::get('couponAmount') }}</strong>
                                                        {{-- front/js/custom.js의 $('#applyCoupon').submit(); 함수 내부에서 AJAX
                                                        핸들로 사용하기 위해 'grand_total' CSS 클래스를 생성합니다. --}} {{--
                                                        Front/ProductsController.php의 applyCoupon() 메소드 내부에서 세션 변수에
                                                        'couponAmount'를 저장했습니다. --}}
                                                    </h3>
                                                </td>
                                            </tr>


                                        </tbody>
                                    </table>
                                    <div class="u-s-m-b-13 codMethod"> {{-- 해당 사용자의 배송 주소 PIN 코드가 `cod_pincodes` 데이터베이스 테이블에
                                        존재하지 않는 경우 해당 결제 방법을 비활성화하기 위해(front/js/custom.js 확인) codMethod CSS 클래스를 추가했습니다.
                                        --}}
                                        <input type="radio" class="radio-box" name="payment_gateway" id="cash-on-delivery"
                                            value="COD">
                                        <label class="label-text" for="cash-on-delivery">Cash on Delivery</label>
                                    </div>
                                    <div class="u-s-m-b-13 prepaidMethod"> {{-- 해당 사용자의 배송 주소 PIN 코드가 `prepaid_pincodes`
                                        데이터베이스 테이블에 존재하지 않는 경우 해당 결제 방법을 비활성화하기 위해(front/js/custom.js 확인) prepaidMethod CSS
                                        클래스를 추가했습니다. --}}
                                        <input type="radio" class="radio-box" name="payment_gateway" id="paypal"
                                            value="Paypal">
                                        <label class="label-text" for="paypal">PayPal</label>
                                    </div>


                                    {{-- iyzico Payment Gateway integration in/with Laravel --}}
                                    <div class="u-s-m-b-13 prepaidMethod"> {{-- 해당 사용자의 배송 주소 PIN 코드가 `prepaid_pincodes`
                                        데이터베이스 테이블에 존재하지 않는 경우 해당 결제 방법을 비활성화하기 위해(front/js/custom.js 확인) prepaidMethod CSS
                                        클래스를 추가했습니다. --}}
                                        <input type="radio" class="radio-box" name="payment_gateway" id="iyzipay"
                                            value="iyzipay">
                                        <label class="label-text" for="iyzipay">iyzipay</label>
                                    </div>


                                    <div class="u-s-m-b-13">
                                        <input type="checkbox" class="check-box" id="accept" name="accept" value="Yes"
                                            title="Please agree to T&C">
                                        <label class="label-text no-color" for="accept">I’ve read and accept the
                                            <a href="terms-and-conditions.html" class="u-c-brand">terms & conditions</a>
                                        </label>
                                    </div>
                                    <button type="submit" id="placeOrder" class="button button-outline-secondary">Place
                                        Order</button> {{-- id="placeOrder" HTML 속성을 사용하여 <form>이 제출되는 동안 프리로더/로더/로딩
                                        페이지/프리로딩 화면을 표시합니다. front/js/custom.js 확인 --}}
                                </div>
                            </form>


                        </div>
                        <!-- Checkout /- -->
                    </div>

                </div>
            </div>


        </div>
    </div>
    <!-- Checkout-Page /- -->
@endsection