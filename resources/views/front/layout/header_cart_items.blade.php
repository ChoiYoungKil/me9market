{{-- 이 파일은 front/layout/header.blade.php에 'include' 됩니다. 우리는 미니 장바구니 위젯을 분리하여 front/layout/header.blade.php에서 여기로
잘라냈습니다. --}}


<!-- Mini Cart -->
<div class="mini-cart-wrapper">
    <div class="mini-cart">
        <div class="mini-cart-header">
            YOUR CART
            <button type="button" class="button ion ion-md-close" id="mini-cart-close"></button>
        </div>
        <ul class="mini-cart-list">


            {{-- 장바구니에 있는 모든 제품의 총 가격을 계산하기 위해 이 $total_price를 foreach 루프 안에 배치합니다. 다음 @endforeach 앞의 루프 끝 부분을 확인하세요.
            --}}
            @php $total_price = 0 @endphp

            @php
                $getCartItems = getCartItems(); // getCartItems() 함수는 'composer.json' 파일에 등록된 사용자 정의 Helpers/Helper.php 파일에 있습니다.
            @endphp

            @foreach ($getCartItems as $item) {{-- $getCartItems는 Front/ProductsController.php의 cart() 메소드에서 전달됩니다. --}}
                @php
                    $getDiscountAttributePrice = \App\Models\Product::getDiscountAttributePrice($item['product_id'], $item['size']); // products 테이블이 아닌 products_attributes 테이블에서
                    // dd($getDiscountAttributePrice);
                @endphp
                <li class="clearfix">
                    <a href="{{ url('product/' . $item['product_id']) }}">
                        <img src="{{ asset('front/images/product_images/small/' . $item['product']['product_image']) }}"
                            alt="Product">
                        <span class="mini-item-name">{{ $item['product']['product_name'] }}</span>
                        <span class="mini-item-price">EGP{{ $getDiscountAttributePrice['final_price'] }}</span>
                        <span class="mini-item-quantity"> x {{ $item['quantity'] }} </span>
                    </a>
                </li>
                {{-- 장바구니에 있는 모든 제품의 총 가격을 계산하기 위해 foreach 루프 내부에 배치되었습니다. --}}
                @php $total_price = $total_price + ($getDiscountAttributePrice['final_price'] * $item['quantity']) @endphp
            @endforeach



        </ul>
        <div class="mini-shop-total clearfix">
            <span class="mini-total-heading float-left">Total:</span>
            <span class="mini-total-price float-right">EGP{{ $total_price }}</span>
        </div>
        <div class="mini-action-anchors">
            <a href="{{ url('cart') }}" class="cart-anchor">View Cart</a>
            <a href="{{ url('checkout') }}" class="checkout-anchor">Checkout</a>
        </div>
    </div>
</div>
<!-- Mini Cart /- -->



{{-- 장바구니를 업데이트하거나 항목을 삭제한 후(즉, AJAX 호출 후) 미니 장바구니 위젯의 X 아이콘이 작동하지 않는(위젯이 닫히지 않는) 문제 해결. 장바구니 항목을 업데이트하거나 삭제할 때 AJAX를
사용하기 때문에 미니 장바구니 위젯 페이지가 다시 로드되고 AJAX를 통해 반환되지만 자바스크립트 없이 반환되기 때문입니다! --}}
{{--
<script>
    $('#mini-cart-close').on('click', function () {
        $('.mini-cart-wrapper').removeClass('mini-cart-open');
    });
</script> --}}