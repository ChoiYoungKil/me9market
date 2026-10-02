@extends('layouts.shop')
@section('content')
<div id="contents"><div id="login"><div class="box box1"><div class="inner_bx">
    <img src="{{ asset('shop/images/common/logo.png') }}" class="logo" alt="Me9 Market">
    <form action="{{ route('shop.login.submit') }}" method="POST"><div class="f_bx">
        @csrf
        <input class="mt0" type="text" name="login_id" value="{{ old('login_id') }}" placeholder="아이디 또는 이메일" aria-label="아이디 또는 이메일" autocomplete="username" required maxlength="150">
        <input type="password" name="password" placeholder="비밀번호" aria-label="비밀번호" autocomplete="current-password" required>
        <ul class="chk01"><li><input type="checkbox" name="remember" value="1" id="remember" @checked(old('remember'))><label for="remember">로그인 유지</label></li></ul>
        <button type="submit" class="btn">LOGIN</button>
        <a href="{{ url('/') }}" class="btn col3">Me9 Market 바로가기</a>
    </div></form>
    <div class="shop-member-links"><a href="{{ route('shop.register') }}">간편회원가입</a><a href="{{ route('front.shop.order.confirm') }}">주문조회</a><a href="{{ route('shop.gate') }}">입장코드</a></div>
</div></div></div></div>
@endsection
