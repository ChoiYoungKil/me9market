@extends('layouts.shop')
@section('shop_auth', '1')
@push('styles')<link rel="stylesheet" href="{{ asset('shop/css/auth.css') }}?v={{ filemtime(public_path('shop/css/auth.css')) }}">@endpush
@push('scripts')<script src="{{ asset('shop/js/auth.js') }}?v={{ filemtime(public_path('shop/js/auth.js')) }}" defer></script>@endpush
@section('content')
<div class="shop-auth-wrap"><section class="shop-auth-card" aria-labelledby="shop-login-title">
    @include('shop.partials.auth_brand')
    <div class="shop-auth-content"><p class="shop-auth-eyebrow">WELCOME TO SHOP</p><h1 id="shop-login-title">회원 로그인</h1>
        <p class="shop-auth-description">미구마켓 회원 아이디로 로그인해 주세요.</p>
        <form action="{{ route('shop.login.submit') }}" method="POST" class="shop-auth-form" data-shop-login>
            @csrf
            <input type="hidden" name="channel" value="{{ $shop?->channel_code ?? '' }}">
            <label for="shop-login-id">아이디 또는 이메일</label><input id="shop-login-id" type="text" name="login_id" value="{{ is_string(old('login_id')) ? old('login_id') : '' }}" placeholder="ID를 입력해 주세요" autocomplete="username" required maxlength="150">
            <label for="shop-login-password">비밀번호</label><input id="shop-login-password" type="password" name="password" placeholder="PW를 입력해 주세요" autocomplete="current-password" required>
            <label class="shop-auth-check"><input type="checkbox" name="save_id" value="1" data-save-id @checked(old('save_id'))>아이디 저장</label>
            <button type="submit" class="shop-auth-primary">LOGIN</button>
        </form>
        @include('partials.social_login', ['socialContext' => 'shop'])
        <button type="button" class="shop-auth-kakao" disabled>카카오 계정으로 로그인 · 연동 대기</button>
        <a class="shop-auth-market" href="{{ url('/') }}">Me9 Market 바로가기 <span aria-hidden="true">↗</span></a>
    </div>
</section></div>
@endsection
