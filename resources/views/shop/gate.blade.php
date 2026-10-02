@extends('layouts.shop')
@section('content')
<div id="contents"><div id="login"><div class="box box1"><div class="inner_bx">
    <h1 class="ttl">입장코드</h1>
    <form action="{{ route('shop.gate.submit') }}" method="POST"><div class="f_bx">
        @csrf
        <input type="text" class="mt0" name="entry_code" id="entry_code" value="{{ old('entry_code', $channelCode) }}" placeholder="채널 입장코드" aria-label="채널 입장코드" required maxlength="80">
        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="휴대폰번호 (비공개 채널)" aria-label="휴대폰번호" autocomplete="tel" maxlength="30">
        <button type="button" id="requestOtp" class="shop-btn shop-otp" data-url="{{ route('shop.otp.request') }}">SMS 인증번호 받기</button>
        <p id="otp-message" role="status"></p>
        <input type="text" name="otp" id="otp" value="{{ old('otp') }}" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="6자리 인증번호 (비공개 채널)" aria-label="인증번호" autocomplete="one-time-code">
        <button type="submit" class="btn">입장하기</button>
        <a href="{{ url('/') }}" class="btn col3">Me9 Market 바로가기</a>
    </div></form>
    <div class="shop-member-links"><a href="{{ route('shop.login') }}">회원 로그인</a></div>
</div></div></div></div>
@endsection
