@extends('layouts.market-sub')
@section('page_title', '로그인')
@section('content')
<div class="market-shell market-auth-layout">
    <section class="market-auth-intro" aria-labelledby="login-title">
        <p class="home-eyebrow">WELCOME TO ME9 MARKET</p><h1 id="login-title">다시 만나<br><em>반갑습니다.</em></h1>
        <p>나만의 채널, 나를 위한 상품.<br>미구마켓에서 새로운 연결을 이어가세요.</p>
        <div class="auth-brand-mark" aria-hidden="true">Me9<span>market</span></div>
    </section>
    <section class="market-auth-form" aria-label="회원 로그인">
        <div class="auth-form-heading"><h2>로그인</h2><p>미구마켓 계정으로 로그인하세요.</p></div>
        @if(Session::has('error_message'))<p class="market-alert market-alert-error" role="alert">{{ Session::get('error_message') }}</p>@endif
        @if(Session::has('success_message'))<p class="market-alert" role="status">{{ Session::get('success_message') }}</p>@endif
        @if($errors->any())<div class="market-alert market-alert-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form action="{{ route('front.member.login.submit') }}" method="POST">
            @csrf
            <div class="market-form-field"><label for="login-id">아이디</label><input id="login-id" type="text" name="login_id" value="{{ $loginId }}" placeholder="아이디 또는 이메일" autocomplete="username" maxlength="255" required></div>
            <div class="market-form-field"><label for="login-password">비밀번호</label><div class="market-password-field"><input id="login-password" type="password" name="password" placeholder="비밀번호를 입력해 주세요" autocomplete="current-password" required><button type="button" data-password-toggle aria-controls="login-password" aria-pressed="false" aria-label="비밀번호 표시">표시</button></div></div>
            <label class="market-check"><input type="checkbox" id="idSave" name="remember_id" value="1" @checked($rememberId)><span>아이디 저장</span></label>
            <button class="market-button market-submit" type="submit">로그인 <span aria-hidden="true">↗</span></button>
        </form>
        <nav class="market-auth-links" aria-label="계정 찾기 및 가입"><a href="{{ route('front.member.find_id') }}">아이디 찾기</a><a href="{{ route('front.member.find_pw') }}">비밀번호 찾기</a><a href="{{ route('front.member.register.member') }}">회원가입</a></nav>
        @include('partials.social_login')
        <a class="market-nonmember" href="{{ route('front.nonmember.order_check') }}">비회원 구매조회 <span aria-hidden="true">↗</span></a>
    </section>
</div>
@endsection
