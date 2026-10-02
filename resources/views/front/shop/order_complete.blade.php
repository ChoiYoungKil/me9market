@extends('layouts.shop')
@section('content')
<div id="contents"><div class="order_completed"><div class="shop-inner">
    <div class="txt_bx"><strong>{{ $order ? '주문이 ' : '주문 내역을 ' }}<span>{{ $order ? '정상적으로 접수' : '확인' }}</span>{{ $order ? '되었습니다.' : '해 주세요.' }}</strong></div>
    @if($order)
        <div class="shop-completion-info"><dl class="shop-meta"><dt>주문번호</dt><dd>Me9-Shop-{{ str_pad($order->id, 7, '0', STR_PAD_LEFT) }}</dd><dt>Shop 채널</dt><dd>{{ $shop->channel_name }}</dd><dt>주문자</dt><dd>{{ $order->name }}</dd></dl>@include('front.shop.partials.payment_summary')</div>
    @endif
    <div class="btn_bx"><a href="{{ $order ? route('front.shop.order.view', $order->id) : route('front.shop.order.confirm') }}">구매내역 확인</a><a href="{{ route('front.shop.cart.index') }}" class="col2">장바구니 이동</a><a href="{{ route('shop.channel_main') }}" class="col3">메인페이지 이동</a></div>
</div></div></div>
@endsection
