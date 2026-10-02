@extends('layouts.shop')
@section('content')
<div id="contents"><div id="login"><div class="box box1"><div class="inner_bx">
    <h1 class="ttl">주문조회</h1>
    <form action="{{ route('front.shop.order.confirm.submit') }}" method="POST"><div class="f_bx">
        @csrf
        <input class="mt0" type="text" name="order_id" value="{{ old('order_id') }}" placeholder="주문번호" aria-label="주문번호" required>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="주문자 이름" aria-label="주문자 이름" autocomplete="name" required maxlength="100">
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="주문자 휴대폰번호" aria-label="주문자 휴대폰번호" autocomplete="tel" required maxlength="30">
        <button type="submit" class="btn">주문조회</button>
    </div></form>
    <div class="shop-member-links"><a href="{{ route('shop.login') }}">회원 로그인</a><a href="{{ route('shop.channel_main') }}">채널 홈</a></div>
</div></div></div></div>
@endsection
