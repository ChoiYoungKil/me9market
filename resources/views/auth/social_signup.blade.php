@extends('layouts.market-sub')
@section('page_title', '간편 회원가입')
@section('content')
@php
    $providerLabel = \App\Services\SocialLoginProvider::LABELS[$pending['provider']];
    $optionalReady = static fn ($key) => !app()->environment('production') || (filter_var(config('shop_channel.'.$key), FILTER_VALIDATE_URL) && parse_url(config('shop_channel.'.$key), PHP_URL_SCHEME) === 'https');
@endphp
<div class="market-shell market-auth-layout market-social-signup">
    <section class="market-auth-intro" aria-labelledby="social-signup-title">
        <p class="home-eyebrow">JOIN ME9 MARKET</p>
        <h1 id="social-signup-title">새로운 연결을<br><em>시작하세요.</em></h1>
        <p>{{ $providerLabel }} 계정으로 간편하게.<br>정보와 약관을 확인하고 미구마켓에 함께하세요.</p>
        <div class="auth-brand-mark" aria-hidden="true">Me9<span>market</span></div>
    </section>
    <section class="market-auth-form" aria-labelledby="social-form-title">
        <div class="auth-form-heading"><h2 id="social-form-title">간편 회원가입</h2><p>{{ $providerLabel }} 인증을 완료했습니다.@if($canRegister) 필수 약관에 동의하면 가입됩니다.@endif</p></div>
        @if($errors->any())<div role="alert" class="market-alert market-alert-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @unless($canRegister)
            <div class="market-alert market-alert-error" role="status" id="social-registration-unavailable">
                <p>현재 신규 회원가입을 이용할 수 없습니다.</p>
                <p>사이트의 필수 약관 문서를 준비 중입니다. 동의 항목을 모두 체크해도 가입할 수 없습니다. 약관 준비가 완료된 후 다시 이용해 주세요.</p>
            </div>
        @endunless
        <form method="POST" action="{{ route('social.register') }}" id="social-signup-form">
            @csrf
            <h3 class="market-social-section-title">회원정보</h3>
            <dl class="market-social-identity"><dt>가입경로</dt><dd><span class="market-social-provider">{{ $providerLabel }}</span></dd><dt>회원번호</dt><dd class="market-social-muted">가입 완료 시 자동 발급</dd></dl>
            <div class="market-form-field"><label for="social-email">이메일</label><input id="social-email" type="email" value="{{ $pending['email'] }}" readonly autocomplete="email"></div>
            <div class="market-form-field"><label for="social-name">이름</label><input id="social-name" name="name" value="{{ is_string(old('name')) ? old('name') : $pending['name'] }}" maxlength="100" required autocomplete="name"></div>
            <section class="market-social-consents" data-consent-group aria-labelledby="social-consent-title">
                <h3 class="market-social-section-title" id="social-consent-title">약관동의</h3>
                <label class="market-check market-social-all"><input type="checkbox" id="social-agree-all" data-consent-all @disabled(!$canRegister)><span>전체 약관 동의</span></label>
                <div class="market-social-consent-list">
                    @foreach([
                        'terms_service'=>['terms_url','이용약관','필수',true],
                        'terms_third_party'=>['third_party_url','제3자 정보제공','필수',true],
                        'marketing_opt_in'=>['marketing_url','마케팅 활용','선택',$optionalReady('marketing_url')],
                        'notification_opt_in'=>['notification_url','알림정보 수신','선택',$optionalReady('notification_url')],
                        'terms_privacy'=>['privacy_url','개인정보 수집 및 이용','필수',true],
                    ] as $field=>[$urlKey,$label,$kind,$available])
                        <div class="market-social-consent-row">
                            <label class="market-check"><input type="checkbox" name="{{ $field }}" value="1" data-consent-item @if($kind==='필수') required @endif @disabled(!$canRegister || !$available) @checked(old($field))><span>{{ $label }} 동의 <small>({{ $kind }})</small></span></label>
                            @if(config('shop_channel.'.$urlKey))<a href="{{ config('shop_channel.'.$urlKey) }}" target="_blank" rel="noopener">전문보기 <span aria-hidden="true">↗</span></a>@endif
                        </div>
                    @endforeach
                </div>
            </section>
            <button class="market-button market-submit" type="submit" @disabled(!$canRegister) @if(!$canRegister) aria-describedby="social-registration-unavailable" @endif>{{ $canRegister ? '동의하고 가입하기' : '약관 준비 중' }} <span aria-hidden="true">↗</span></button>
            <a class="market-nonmember" href="{{ route('front.member.login') }}">로그인으로 돌아가기 <span aria-hidden="true">↗</span></a>
        </form>
    </section>
</div>
@endsection
