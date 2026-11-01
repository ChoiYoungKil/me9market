@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="order_details">
    <div class="top_v"><h1 class="title">주문 상세</h1></div>
    <div class="shop-inner">
        <div class="shop-receipt-brand"><img src="{{ asset($shop->use_logo && $shop->logo_image ? $shop->logo_image : 'shop/images/common/logo.png') }}" alt="{{ $shop->channel_name }}"><p class="shop-order-channel">{{ $shop->channel_name }}에서 주문하신 내역입니다.</p></div>
        <section class="shop-section"><h2>주문 정보</h2><dl class="shop-meta"><dt>고객명</dt><dd>{{ $order->buyer_name ?: $order->name }}</dd><dt>주문번호</dt><dd>Me9-Shop-{{ str_pad($order->id,7,'0',STR_PAD_LEFT) }}</dd><dt>주문일시</dt><dd>{{ $order->created_at?->format('Y-m-d H:i') }}</dd></dl></section>
        <section class="shop-section"><h2>배송정보</h2><dl class="shop-meta"><dt>수신자명</dt><dd>{{ $order->name }}</dd><dt>휴대전화</dt><dd>{{ $order->mobile }}</dd><dt>이메일</dt><dd>{{ $order->recipient_email ?: $order->email }}</dd><dt>배송지주소</dt><dd>{{ $order->pincode }} {{ $order->address }} {{ $order->address_detail }}</dd><dt>배송 메모</dt><dd>{{ $order->delivery_memo ?: '-' }}</dd></dl></section>
        <section class="shop-section"><h2>주문상품</h2><div class="shop-receipt-products">
            <div class="shop-receipt-columns" aria-hidden="true"><span>상품정보</span><span>상품금액</span><span>수량</span><span>배송비</span><span>주문금액</span></div>
            @foreach($order->orders_products as $item)
                @php
                    $image=$item->product?->product_image ?: $item->product?->images->first()?->image;
                    $paidLine=$item->paid_line_total_snapshot ?? $item->line_total ?? $item->product_price*$item->product_qty;
                    $shipping=$item->shipping_amount_snapshot;
                @endphp
                <div class="shop-receipt-product"><div class="shop-receipt-description"><img src="{{ $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png') }}" alt="{{ $item->product_name }}"><div><p>{{ $item->product?->category_path }}</p><h3>{{ $item->product_name }}</h3><p>옵션: {{ $item->product_size ?: '기본옵션' }}</p></div></div><div data-label="상품금액">{{ number_format($paidLine) }}원</div><div data-label="수량">{{ $item->product_qty }}개</div><div data-label="배송비">{{ $shipping===null ? '기록 없음' : number_format($shipping).'원' }}</div><div data-label="주문금액"><strong>{{ $shipping===null ? '-' : number_format($paidLine+$shipping).'원' }}</strong></div></div>
            @endforeach
        </div></section>
        <section class="shop-section"><h2>결제 정보</h2>@include('front.shop.partials.payment_summary')</section>
        @include('front.shop.partials.fulfillment_details')
        <div class="shop-actions"><a class="shop-btn" href="{{ route('front.shop.order.details') }}">목록으로</a><a class="shop-btn primary" href="{{ route('shop.channel_main') }}">Shop 채널 바로가기</a></div>
    </div>
</div></div>
@endsection
