@extends('layouts.channel_login')
@section('title', '채널관리자 로그인 · Me9 market')
@section('body_class', 'portal-channel-login')
@section('styles')
<link rel="stylesheet" href="{{ asset('channel_assets/css/login.css').'?v='.filemtime(public_path('channel_assets/css/login.css')) }}">
@endsection
@section('content')
<main id="login">
    <div class="box box1"><section class="inner_bx" aria-labelledby="channel-login-title">
        <a class="channel-login-brand" href="{{ url('/') }}"><img src="{{ asset('me9market/images/common/logo.png') }}" alt="Me9 market" width="157" height="37"></a>
        <p class="channel-login-eyebrow">MY CHANNEL · ME9 MARKET</p>
        <h1 id="channel-login-title">채널관리자 로그인</h1>
        <p class="channel-login-intro">회원 아이디 또는 이메일로 로그인하세요.</p>
        @if(session('error_message'))<p class="channel-login-error" role="alert">{{ session('error_message') }}</p>@endif
        @if($errors->any())<div class="channel-login-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form action="{{ route('channel.login.submit') }}" method="POST">
            @csrf
            <label class="channel-login-label" for="channel-login-id">아이디</label>
            <input id="channel-login-id" type="text" name="login_id" value="{{ $loginId }}" placeholder="아이디 또는 이메일" autocomplete="username" maxlength="255" required>
            <label class="channel-login-label" for="channel-login-password">비밀번호</label>
            <div class="channel-password"><input id="channel-login-password" type="password" name="password" placeholder="비밀번호를 입력해 주세요" autocomplete="current-password" maxlength="4096" required><button id="channel-password-toggle" type="button" aria-controls="channel-login-password" aria-pressed="false" aria-label="비밀번호 표시">표시</button></div>
            <label class="channel-remember" for="idSave"><input type="checkbox" id="idSave" name="remember_id" value="1" @checked($rememberId)><span>아이디 저장</span></label>
            <button type="submit" class="channel-login-submit">LOGIN</button>
        </form>
        <nav class="channel-login-help" aria-label="계정 찾기"><a href="{{ route('front.member.find_id') }}">아이디 찾기</a><a href="{{ route('front.member.find_pw') }}">비밀번호 찾기</a></nav>
        <div class="channel-social" aria-label="간편회원 로그인">
            <p>간편회원 로그인</p>
            <button class="channel-social-button kakao" type="button" disabled>카카오 계정으로 로그인 <small>연동 대기</small></button>
            @foreach(['naver'=>'네이버', 'google'=>'구글'] as $provider=>$label)
                @if(app(\App\Services\SocialLoginProvider::class)->ready($provider))
                    @if(app(\App\Services\SocialLoginProvider::class)->blockedOnHttp($provider))
                        <button class="channel-social-button {{ $provider }}" type="button" onclick="alert(@js(\App\Services\SocialLoginProvider::GOOGLE_HTTPS_NOTICE))">{{ $label }} 계정으로 로그인 <span aria-hidden="true">↗</span></button>
                    @else
                        <a class="channel-social-button {{ $provider }}" href="{{ route('social.start', ['provider'=>$provider,'context'=>'channel']) }}">{{ $label }} 계정으로 로그인 <span aria-hidden="true">↗</span></a>
                    @endif
                @else
                    <button class="channel-social-button {{ $provider }}" type="button" disabled>{{ $label }} 계정으로 로그인 <small>연동 대기</small></button>
                @endif
            @endforeach
        </div>
        <p class="channel-login-note">기존 채널 관리자·서브관리자는 관리자 이메일로 로그인할 수 있습니다.</p>
        <a class="channel-member-link" href="{{ route('front.member.login') }}">회원 로그인으로 이동</a>
    </section></div>
</main>
@endsection
@section('scripts')
<script>
document.getElementById('channel-password-toggle').addEventListener('click', function () {
    const input = document.getElementById('channel-login-password');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    this.textContent = show ? '숨김' : '표시';
    this.setAttribute('aria-pressed', String(show));
    this.setAttribute('aria-label', show ? '비밀번호 숨김' : '비밀번호 표시');
});
</script>
@endsection
