@extends('layouts.market-sub')
@section('page_title', '비밀번호 찾기')
@section('content')
@php
    $username = old('username', request('username', ''));
    $email = old('email', request('email', ''));
@endphp
<div class="market-shell market-auth-layout">
    @include('front.member.recovery-intro')
    <section class="market-auth-form" aria-label="일반회원 비밀번호 찾기">
        <nav class="market-recovery-tabs" aria-label="계정 찾기"><a href="{{ route('front.member.find_id') }}">아이디 찾기</a><a href="{{ route('front.member.find_pw') }}" aria-current="page">비밀번호 찾기</a></nav>
        <div class="auth-form-heading"><h2>비밀번호 찾기</h2><p>가입하신 아이디와 대표이메일을 입력해 주세요.</p></div>
        @if($errors->any())<div class="market-alert market-alert-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('front.member.find_pw') }}">
            @csrf
            <div class="market-form-field"><label for="find-pw-username">아이디</label><input id="find-pw-username" name="username" value="{{ is_string($username) ? $username : '' }}" placeholder="아이디를 입력하세요" maxlength="255" autocomplete="username" required></div>
            <div class="market-form-field"><label for="find-pw-email">대표이메일</label><input id="find-pw-email" name="email" type="email" value="{{ is_string($email) ? $email : '' }}" placeholder="가입한 이메일을 입력하세요" maxlength="150" autocomplete="email" required></div>
            <button type="submit" class="market-button market-submit">비밀번호 찾기 <span aria-hidden="true">↗</span></button>
        </form>
        @if(isset($result) && $result['type'] === 'success')
            <div class="market-alert market-recovery-result" role="status"><p>{{ $result['message'] }}</p><p>임시비밀번호는 발급 후 60분 동안 로그인에 사용할 수 있습니다.</p>
                @if(isset($result['preview_url']))<a class="market-recovery-preview" href="{{ $result['preview_url'] }}" target="_blank" rel="noopener">로컬 검수 메일 보기 ↗</a><p>외부 이메일로 발송하지 않은 검수용 메일입니다.</p>@endif
            </div>
        @endif
        <nav class="market-auth-links" aria-label="로그인 및 가입"><a href="{{ route('front.member.login') }}">로그인</a><a href="{{ route('front.member.register.member') }}">회원가입</a></nav>
    </section>
</div>
@endsection
@if(isset($result) && $result['type'] === 'success')
    @push('scripts')<script>window.addEventListener('DOMContentLoaded', () => alert(@json($result['message'])));</script>@endpush
@endif
