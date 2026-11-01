@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="shopping_basket">
    <div class="top_v"><h1 class="title">장바구니</h1></div>
    <div class="shop-inner">
        <p class="shop-cart-context">{{ $shop->channel_name }} ({{ $shop->channel_code }})</p>
        <nav class="shop-cart-types" aria-label="장바구니 상품 구분"><a href="{{ route('front.shop.cart.index',['type'=>'own']) }}" @if($cartType==='own') aria-current="page" @endif>자사상품 <span>({{ $cartCounts['own'] }})</span></a><a href="{{ route('front.shop.cart.index',['type'=>'public']) }}" @if($cartType==='public') aria-current="page" @endif>공개상품 <span>({{ $cartCounts['public'] }})</span></a></nav>
        @if(empty($cartItems))
            <div class="shop-empty">장바구니가 비어 있습니다.<div class="shop-actions"><a class="shop-btn primary" href="{{ route('shop.products_list') }}">상품 보러가기</a></div></div>
        @else
            <form id="cart-remove-selected" action="{{ route('front.shop.cart.checkout_selection') }}" method="POST" data-cart-totals-url="{{ route('front.shop.cart.selection_total') }}">@csrf</form>
            <p data-cart-feedback role="status"></p>
            <div class="shop-order-heading"><label class="shop-check"><input type="checkbox" data-select-all=".shop-cart-select">전체선택</label><button type="submit" form="cart-remove-selected" formaction="{{ route('front.shop.cart.remove_selected') }}" class="shop-btn small">선택삭제</button></div>
            @foreach(collect($cartItems)->groupBy(fn($item)=>$item['product']->vendor_id) as $sellerItems)
            <section class="shop-order"><div class="shop-order-heading"><h2>판매자: {{ $sellerItems->first()['product']->vendor?->name ?: $shop->channel_name }}</h2></div>
            @foreach($sellerItems->groupBy('id') as $rows)
            @foreach($rows as $item)
                @php
                    $image = $item['product']?->product_image ?: $item['product']?->images->where('status', 1)->first()?->image;
                    $imageUrl = $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png');
                @endphp
                <div class="shop-item">
                    <img src="{{ $imageUrl }}" alt="{{ $item['product']->product_name }}" loading="lazy">
                    <div><label class="shop-check"><input class="shop-cart-select" type="checkbox" checked name="shop_product_ids[]" form="cart-remove-selected" value="{{ $item['key'] }}"><strong>{{ $item['product']->product_name }}</strong></label><p>{{ $item['option'] }} / {{ $item['qty'] }}개</p><p>배송비: {{ app(\App\Services\ShopOrderTotals::class)->shippingLabel($item['product'], $item['shop_product'] ?? null) }}</p></div>
                    <div class="shop-item-side"><strong>{{ number_format($item['line_total']) }}원</strong><div class="shop-item-actions">
                        <button type="button" class="shop-btn small" data-dialog="cart-option-{{ $item['key'] }}">옵션/수량변경</button>
                        <a class="shop-btn small" href="{{ route('shop.product_details', $item['id']) }}">상품 보기</a>
                        <form action="{{ route('front.shop.cart.checkout_selection') }}" method="POST">@csrf<input type="hidden" name="shop_product_ids[]" value="{{ $item['key'] }}"><button type="submit" class="shop-btn small primary">이 상품 주문</button></form>
                        <form action="{{ route('front.shop.cart.remove') }}" method="POST">@csrf<input type="hidden" name="shop_product_id" value="{{ $item['id'] }}"><input type="hidden" name="cart_key" value="{{ $item['key'] }}"><button type="submit" class="shop-btn small">삭제</button></form>
                    </div></div>
                </div>
                <dialog id="cart-option-{{ $item['key'] }}" class="shop-dialog" data-reset-on-close aria-labelledby="cart-option-title-{{ $item['key'] }}">
                    <div class="shop-dialog-head"><h2 id="cart-option-title-{{ $item['key'] }}">옵션 변경</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
                    <div class="shop-dialog-body"><form action="{{ route('front.shop.cart.update') }}" method="POST" data-cart-option-form>
                        @csrf<input type="hidden" name="shop_product_id" value="{{ $item['id'] }}"><input type="hidden" name="cart_key" value="{{ $item['key'] }}">
                        <strong>{{ $item['product']->product_name }}</strong>
                        <label>옵션<select class="shop-control" name="option" required>
                            @forelse($item['product']->attributes->where('status', 1) as $attribute)<option value="{{ $attribute->size }}" @selected($item['option'] === $attribute->size)>{{ $attribute->size }}</option>@empty<option value="{{ $item['option'] }}">{{ $item['option'] }}</option>@endforelse
                        </select></label>
                        @php
                            $minQty = 1;
                            $maxQty = min($item['shop_product']->purchase_limit ?: 999, $item['shop_product']->stock ?? 999, $item['product']->purchase_limit_enabled ? ($item['product']->purchase_max_qty ?: 999) : 999);
                        @endphp
                        <label>수량 선택<div class="shop-stepper" data-stepper><button type="button" data-step="-1" aria-label="수량 줄이기">−</button><input type="number" name="qty" aria-label="수량 선택" value="{{ $item['qty'] }}" required min="{{ $minQty }}" max="{{ max($minQty, $maxQty) }}"><button type="button" data-step="1" aria-label="수량 늘리기">+</button></div></label>
                        <p>배송비: {{ app(\App\Services\ShopOrderTotals::class)->shippingLabel($item['product'], $item['shop_product'] ?? null) }}</p>
                        <div class="shop-actions"><button type="submit" class="shop-btn primary" @disabled($maxQty < $minQty)>옵션변경</button><button type="button" class="shop-btn" data-close>창닫기</button></div>
                    </form></div>
                </dialog>
            @endforeach
            @include('front.shop.partials.shipping_equation')
            @endforeach
            </section>
            @endforeach
            <div class="shop-summary shop-cart-summary">
                <div><span>총 상품금액</span><strong data-cart-subtotal>{{ number_format($totals['subtotal']) }}원</strong></div>
                <span class="shop-amount-operator" aria-hidden="true">+</span>
                <div><span>배송비</span><strong data-cart-shipping>{{ number_format($totals['shipping']) }}원</strong></div>
                <span class="shop-amount-operator" aria-hidden="true">=</span>
                <div class="total"><span>최종 결제금액</span><strong data-cart-total>{{ number_format($totals['total']) }}원</strong></div>
            </div>
            <div class="shop-actions shop-cart-final-actions"><a class="shop-btn" href="{{ route('shop.products_list') }}">쇼핑 계속하기</a><button class="shop-cart-checkoutbar" type="submit" form="cart-remove-selected"><span>총 <strong data-cart-quantity>{{ array_sum(array_column($cartItems,'qty')) }}</strong>개</span><span class="shop-cart-bar-divider" aria-hidden="true">|</span><strong data-cart-bar-total>{{ number_format($totals['total']) }}원</strong><span>주문하기 <span aria-hidden="true">〉</span></span></button></div>
        @endif
    </div>
</div></div>
@endsection
