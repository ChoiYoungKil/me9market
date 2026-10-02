@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="shopping_basket">
    <div class="top_v"><h1 class="title">장바구니</h1></div>
    <div class="shop-inner">
        @if(empty($cartItems))
            <div class="shop-empty">장바구니가 비어 있습니다.<div class="shop-actions"><a class="shop-btn primary" href="{{ route('shop.products_list') }}">상품 보러가기</a></div></div>
        @else
            <form id="cart-remove-selected" action="{{ route('front.shop.cart.remove_selected') }}" method="POST">@csrf</form>
            <div class="shop-order-heading"><label class="shop-check"><input type="checkbox" data-select-all=".shop-cart-select">전체선택</label><button type="submit" form="cart-remove-selected" class="shop-btn small">선택삭제</button></div>
            <section class="shop-order"><div class="shop-order-heading"><h2>{{ $shop->channel_name }} ({{ $shop->channel_code }})</h2></div>
            @foreach($cartItems as $item)
                @php
                    $image = $item['product']?->product_image ?: $item['product']?->images->first()?->image;
                    $imageUrl = $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png');
                @endphp
                <div class="shop-item">
                    <img src="{{ $imageUrl }}" alt="{{ $item['product']->product_name }}" loading="lazy">
                    <div><label class="shop-check"><input class="shop-cart-select" type="checkbox" name="shop_product_ids[]" form="cart-remove-selected" value="{{ $item['key'] }}"><strong>{{ $item['product']->product_name }}</strong></label><p>{{ $item['option'] }} / {{ $item['qty'] }}개</p></div>
                    <div class="shop-item-side"><strong>{{ number_format($item['line_total']) }}원</strong><div class="shop-item-actions">
                        <button type="button" class="shop-btn small" data-dialog="cart-option-{{ $item['key'] }}">옵션/수량변경</button>
                        <a class="shop-btn small" href="{{ route('shop.product_details', $item['id']) }}">상품 보기</a>
                        <form action="{{ route('front.shop.cart.remove') }}" method="POST">@csrf<input type="hidden" name="shop_product_id" value="{{ $item['id'] }}"><input type="hidden" name="cart_key" value="{{ $item['key'] }}"><button type="submit" class="shop-btn small">삭제</button></form>
                    </div></div>
                </div>
                <dialog id="cart-option-{{ $item['key'] }}" class="shop-dialog" aria-labelledby="cart-option-title-{{ $item['key'] }}">
                    <div class="shop-dialog-head"><h2 id="cart-option-title-{{ $item['key'] }}">옵션/수량변경</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
                    <div class="shop-dialog-body"><form action="{{ route('front.shop.cart.update') }}" method="POST">
                        @csrf<input type="hidden" name="shop_product_id" value="{{ $item['id'] }}"><input type="hidden" name="cart_key" value="{{ $item['key'] }}">
                        <strong>{{ $item['product']->product_name }}</strong>
                        <label>상품 옵션<select class="shop-control" name="option" required>
                            @forelse($item['product']->attributes->where('status', 1) as $attribute)<option value="{{ $attribute->size }}" @selected($item['option'] === $attribute->size)>{{ $attribute->size }}</option>@empty<option value="{{ $item['option'] }}">{{ $item['option'] }}</option>@endforelse
                        </select></label>
                        @php
                            $minQty = 1;
                            $maxQty = min($item['shop_product']->purchase_limit ?: 999, $item['shop_product']->stock ?? 999, $item['product']->purchase_limit_enabled ? ($item['product']->purchase_max_qty ?: 999) : 999);
                        @endphp
                        <label>상품 수량<input class="shop-control" type="number" name="qty" value="{{ $item['qty'] }}" required min="{{ $minQty }}" max="{{ max($minQty, $maxQty) }}"></label>
                        <p>배송비: {{ app(\App\Services\ShopOrderTotals::class)->shippingLabel($item['product']) }}</p>
                        <button type="submit" class="shop-btn primary" @disabled($maxQty < $minQty)>변경하기</button>
                    </form></div>
                </dialog>
            @endforeach
            </section>
            <div class="shop-summary shop-cart-summary">
                <div><span>총 상품금액</span><strong>{{ number_format($totals['subtotal']) }}원</strong></div>
                <div><span>배송비</span><strong>{{ number_format($totals['shipping']) }}원</strong></div>
                <div class="total"><span>최종 결제예정금액</span><strong>{{ number_format($totals['total']) }}원</strong></div>
            </div>
            <div class="shop-actions"><a class="shop-btn" href="{{ route('shop.products_list') }}">쇼핑 계속하기</a><a class="shop-btn primary" href="{{ route('front.shop.order.form') }}">주문하기</a></div>
        @endif
    </div>
</div></div>
@endsection
