{{-- 참고: 이 파일 전체는 front/products/cart.blade.php에 'include' 됩니다(장바구니에서 주문 수량을 업데이트할 때 AJAX 호출을 허용하기 위해). --}}


<!-- Products-List-Wrapper -->
<div class="table-wrapper u-s-m-b-60">
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>


            {{-- 장바구니에 있는 모든 제품의 총 가격을 계산하기 위해 이 $total_price를 foreach 루프 안에 배치합니다. 다음 @endforeach 앞의 루프 끝 부분을 확인하세요.
            --}}
            @php $total_price = 0 @endphp

            @foreach ($getCartItems as $item) {{-- $getCartItems is passed in from cart() method in
                Front/ProductsController.php --}}
                @php
                    $getDiscountAttributePrice = \App\Models\Product::getDiscountAttributePrice($item['product_id'], $item['size']); // from the `products_attributes` table, not the `products` table
                    // dd($getDiscountAttributePrice);
                @endphp

                <tr>
                    <td>
                        <div class="cart-anchor-image">
                            <a href="{{ url('product/' . $item['product_id']) }}">
                                <img src="{{ asset('front/images/product_images/small/' . $item['product']['product_image']) }}"
                                    alt="Product">
                                <h6>
                                    {{ $item['product']['product_name'] }} ({{ $item['product']['product_code'] }}) -
                                    {{ $item['size'] }}
                                    <br>
                                    Color: {{ $item['product']['product_color'] }}
                                </h6>
                            </a>
                        </div>
                    </td>
                    <td>
                        <div class="cart-price">



                            @if ($getDiscountAttributePrice['discount'] > 0) {{-- 가격에 할인이 있는 경우, 할인 전 가격(원래 가격)과 할인 후 가격(새
                                가격)을 표시합니다. --}}
                                <div class="price-template">
                                    <div class="item-new-price">
                                        EGP{{ $getDiscountAttributePrice['final_price'] }}
                                    </div>
                                    <div class="item-old-price" style="margin-left: -40px">
                                        EGP{{ $getDiscountAttributePrice['product_price'] }}
                                    </div>
                                </div>
                            @else {{-- 가격에 할인이 없는 경우, 원래 가격을 표시합니다. --}}
                                <div class="price-template">
                                    <div class="item-new-price">
                                        EGP{{ $getDiscountAttributePrice['final_price'] }}
                                    </div>
                                </div>
                            @endif



                        </div>
                    </td>
                    <td>
                        <div class="cart-quantity">
                            <div class="quantity">
                                <input type="text" class="quantity-text-field" value="{{ $item['quantity'] }}">
                                <a data-max="1000" class="plus-a  updateCartItem" data-cartid="{{ $item['id'] }}"
                                    data-qty="{{ $item['quantity'] }}">&#43;</a> {{-- 더하기 기호: 항목 1개 증가 --}} {{--
                                .updateCartItem CSS 클래스와 사용자 정의 HTML 속성 data-cartid 및 data-qty는 front/js/custom.js에서 AJAX
                                호출을 수행하는 데 사용됩니다. --}}
                                <a data-min="1" class="minus-a updateCartItem" data-cartid="{{ $item['id'] }}"
                                    data-qty="{{ $item['quantity'] }}">&#45;</a> {{-- 빼기 기호: 항목 1개 감소 --}} {{--
                                .updateCartItem CSS 클래스와 사용자 정의 HTML 속성 data-cartid 및 data-qty는 front/js/custom.js에서 AJAX
                                호출을 수행하는 데 사용됩니다. --}}
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="cart-price">
                            EGP{{ $getDiscountAttributePrice['final_price'] * $item['quantity'] }} {{-- 모든 제품의 가격 (할인 후(있는
                            경우)) (= 가격(할인 후) * 제품 수량) --}}
                        </div>
                    </td>
                    <td>
                        <div class="action-wrapper">
                            {{-- <button class="button button-outline-secondary fas fa-sync"></button> --}}
                            <button class="button button-outline-secondary fas fa-trash deleteCartItem"
                                data-cartid="{{ $item['id'] }}"></button>{{-- .deleteCartItem CSS 클래스와 사용자 정의 HTML 속성
                            data-cartid는 front/js/custom.js에서 AJAX 호출을 수행하는 데 사용됩니다. --}}
                        </div>
                    </td>
                </tr>


                {{-- 장바구니에 있는 모든 제품의 총 가격을 계산하기 위해 foreach 루프 내부에 배치되었습니다. --}}
                @php $total_price = $total_price + ($getDiscountAttributePrice['final_price'] * $item['quantity']) @endphp
            @endforeach



        </tbody>
    </table>
</div>
<!-- Products-List-Wrapper /- -->





{{-- 쿠폰 코드 제출이 한 번만 작동하는 문제를 해결하기 위해, 쿠폰 부분을 cart_items.blade.php에서 여기 cart.blade.php로 옮겼습니다. --}} {{-- 문제 설명:
http://publicvoidlife.blogspot.com/2014/03/on-on-or-event-delegation-explained.html --}}





<!-- Billing -->
<div class="calculation u-s-m-b-60">
    <div class="table-wrapper-2">
        <table>
            <thead>
                <tr>
                    <th colspan="2">Cart Totals</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <h3 class="calc-h3 u-s-m-b-0">Sub Total</h3> {{-- 쿠폰 할인 전 총 가격 --}}
                    </td>
                    <td>
                        <span class="calc-text">EGP{{ $total_price }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3 class="calc-h3 u-s-m-b-0">Coupon Discount</h3>
                    </td>
                    <td>
                        <span class="calc-text couponAmount"> {{-- front/js/custom.js의 $('#applyCoupon').submit(); 함수
                            내부에서 AJAX 핸들로 사용하기 위해 'couponAmount' CSS 클래스를 생성합니다. --}}

                            @if (\Illuminate\Support\Facades\Session::has('couponAmount')) {{--
                                Front/ProductsController.php의 applyCoupon() 메소드 내부에서 세션 변수에 'couponAmount'를 저장했습니다. --}}
                                EGP{{ \Illuminate\Support\Facades\Session::get('couponAmount') }}
                            @else
                                EGP0
                            @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3 class="calc-h3 u-s-m-b-0">Grand Total</h3> {{-- 쿠폰 할인 후 총 가격(있는 경우) --}}
                    </td>
                    <td>
                        <span
                            class="calc-text grand_total">EGP{{ $total_price - \Illuminate\Support\Facades\Session::get('couponAmount') }}</span>
                        {{-- front/js/custom.js의 $('#applyCoupon').submit(); 함수 내부에서 AJAX 핸들로 사용하기 위해 'grand_total' CSS
                        클래스를 생성합니다. --}} {{-- Front/ProductsController.php의 applyCoupon() 메소드 내부에서 세션 변수에
                        'couponAmount'를 저장했습니다. --}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<!-- Billing /- -->