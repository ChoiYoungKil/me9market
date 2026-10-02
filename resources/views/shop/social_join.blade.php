@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join">
    <div class="top_v"><h1 class="title">간편회원가입</h1></div>
    <div class="shop-inner">
        <div class="logo_bx">
            @if($shop->use_logo && $shop->logo_image)<img src="{{ asset($shop->logo_image) }}" alt="{{ $shop->channel_name }}"><span class="plus" aria-hidden="true"></span>@endif
            <img src="{{ asset('shop/images/common/logo.png') }}" alt="Me9 Market">
        </div>
        <form action="{{ route('shop.register.submit') }}" method="POST">
            @csrf
            <section class="shop-form-section"><h2>회원정보입력</h2>
                <div class="shop-form-grid shop-table-form">
                    <label>이름<input class="shop-control" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></label>
                    <label>이메일<input class="shop-control" type="email" name="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email"></label>
                    <label>휴대폰번호<input class="shop-control" type="tel" name="phone" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel"></label>
                    <label>비밀번호<input class="shop-control" type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password"></label>
                </div>
            </section>
            <section class="shop-form-section"><h2>약관동의</h2>
                <div class="shop-agreements">
                    <label class="shop-check"><input type="checkbox" data-select-all=".shop-agreement">전체 약관 동의</label>
                    <div><label class="shop-check"><input class="shop-agreement" type="checkbox" name="terms_service" value="1" required @checked(old('terms_service'))>이용약관 동의 (필수)</label><details><summary>전문보기</summary><p>@if(config('shop_channel.terms_url'))<a href="{{ config('shop_channel.terms_url') }}" target="_blank" rel="noopener">Me9 Market 이용약관</a>@else이용약관을 준비 중입니다.@endif</p></details></div>
                    <div><label class="shop-check"><input class="shop-agreement" type="checkbox" name="terms_privacy" value="1" required @checked(old('terms_privacy'))>개인정보 수집 및 이용 동의 (필수)</label><details><summary>전문보기</summary><p>@if(config('shop_channel.privacy_url'))<a href="{{ config('shop_channel.privacy_url') }}" target="_blank" rel="noopener">개인정보 수집 및 이용 안내</a>@else개인정보 수집 및 이용 안내를 준비 중입니다.@endif</p></details></div>
                    <div><label class="shop-check"><input class="shop-agreement" type="checkbox" name="terms_third_party" value="1" required @checked(old('terms_third_party'))>개인정보 제3자 제공 동의 (필수)</label><details><summary>전문보기</summary><p>@if(config('shop_channel.third_party_url'))<a href="{{ config('shop_channel.third_party_url') }}" target="_blank" rel="noopener">개인정보 제3자 제공 안내</a>@else개인정보 제3자 제공 안내를 준비 중입니다.@endif</p></details></div>
                    @foreach(['marketing' => '마케팅 정보 수신 동의', 'notification' => '알림 수신 동의'] as $key => $label)
                        <div><label class="shop-check"><input type="hidden" name="{{ $key }}_opt_in" value="0"><input class="shop-agreement" type="checkbox" name="{{ $key }}_opt_in" value="1" @checked(old($key.'_opt_in')) @disabled(app()->environment('production') && !config('shop_channel.'.$key.'_url'))>{{ $label }} (선택)</label><details><summary>전문보기</summary><p>@if(config('shop_channel.'.$key.'_url'))<a href="{{ config('shop_channel.'.$key.'_url') }}" target="_blank" rel="noopener">{{ $label }} 안내</a>@else안내 문서를 준비 중입니다.@endif</p></details></div>
                    @endforeach
                </div>
            </section>
            @unless($canRegister)<p role="status">회원가입 약관을 준비 중입니다. 판매자에게 문의해 주세요.</p>@endunless
            <div class="shop-actions"><a class="shop-btn" href="{{ route('shop.login') }}">로그인</a><button class="shop-btn primary" type="submit" @disabled(!$canRegister)>가입완료</button></div>
        </form>
    </div>
</div></div>
@endsection
