@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="order_details">
    <div class="top_v"><h1 class="title">주문 상세</h1></div>
    <div class="shop-inner">
        <div class="shop-order-heading"><strong>Me9-Shop-{{ str_pad($order->id, 7, '0', STR_PAD_LEFT) }}</strong><span>{{ $order->created_at?->format('Y-m-d H:i') }}</span></div>
        <section class="shop-section"><h2>주문 상품</h2>
            @foreach($order->orders_products as $item) @include('front.shop.partials.order_item') @endforeach
        </section>
        <section class="shop-section"><h2>주문자 / 배송 정보</h2>
            <dl class="shop-meta"><dt>주문자</dt><dd>{{ $order->buyer_name ?: $order->name }} / {{ $order->buyer_mobile ?: $order->mobile }}</dd><dt>수신자 이름</dt><dd>{{ $order->name }}</dd><dt>연락처</dt><dd>{{ $order->mobile }}</dd><dt>이메일</dt><dd>{{ $order->email }}</dd><dt>배송지</dt><dd>{{ $order->pincode }} {{ $order->address }} {{ $order->city }} {{ $order->state }}</dd><dt>배송 메모</dt><dd>{{ $order->delivery_memo ?: '-' }}</dd></dl>
        </section>
        <section class="shop-section"><h2>결제 정보</h2>
            @include('front.shop.partials.payment_summary')
        </section>
        <div class="shop-actions"><a class="shop-btn" href="{{ route('front.shop.order.details') }}">목록으로</a><a class="shop-btn primary" href="{{ route('shop.products_list') }}">쇼핑 계속하기</a></div>
    </div>
</div></div>
@endsection
