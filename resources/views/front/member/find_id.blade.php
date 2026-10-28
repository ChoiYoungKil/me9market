@extends('layouts.market-sub')
@section('page_title', '아이디 찾기')
@section('content')
@php
    $number = old('member_number', request('member_number', ''));
    $email = old('email', request('email', ''));
@endphp
<div class="market-shell market-auth-layout">
    @include('front.member.recovery-intro')
    <section class="market-auth-form" aria-label="일반회원 아이디 찾기">
        <nav class="market-recovery-tabs" aria-label="계정 찾기"><a href="{{ route('front.member.find_id') }}" aria-current="page">아이디 찾기</a><a href="{{ route('front.member.find_pw') }}">비밀번호 찾기</a></nav>
        <div class="auth-form-heading"><h2>아이디 찾기</h2><p>회원번호와 가입하신 대표이메일을 입력해 주세요.</p></div>
        @if($errors->any())<div class="market-alert market-alert-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('front.member.find_id') }}">
            @csrf
            <div class="market-form-field"><label for="find-member-number">회원번호</label><input id="find-member-number" name="member_number" value="{{ is_string($number) ? $number : '' }}" placeholder="회원번호를 입력하세요" maxlength="50" autocomplete="off" required></div>
            <div class="market-form-field"><label for="find-id-email">대표이메일</label><input id="find-id-email" name="email" type="email" value="{{ is_string($email) ? $email : '' }}" placeholder="가입한 이메일을 입력하세요" maxlength="150" autocomplete="email" required></div>
            <button type="submit" class="market-button market-submit">아이디 찾기 <span aria-hidden="true">↗</span></button>
        </form>
        @if(isset($result))
            @if($result['type'] === 'success')
                <div class="market-alert market-recovery-result" role="status"><p>찾으시는 아이디는 <strong>{{ $result['username'] }}</strong> 입니다.</p></div>
            @else
                <div class="market-alert market-alert-error market-recovery-result" role="alert"><p>{{ $result['message'] }}</p></div>
            @endif
        @endif
        <nav class="market-auth-links" aria-label="로그인 및 가입"><a href="{{ route('front.member.login') }}">로그인</a><a href="{{ route('front.member.register.member') }}">회원가입</a></nav>
    </section>
</div>
@endsection
