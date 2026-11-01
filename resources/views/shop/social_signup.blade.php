@extends('layouts.shop')
@section('shop_auth', '1')
@push('styles')<link rel="stylesheet" href="{{ asset('shop/css/auth.css') }}?v={{ filemtime(public_path('shop/css/auth.css')) }}">@endpush
@section('content')
@php($providerLabel = \App\Services\SocialLoginProvider::LABELS[$pending['provider']])
@if($isPreview)<aside class="shop-auth-preview" role="status">PPT 215번 가입 화면 미리보기입니다. 아래는 검수용 표시 자료이며 실제 소셜 인증이나 회원가입을 수행하지 않습니다. 신규가입의 저장·차단 결과는 별도 테스트 DB 검증으로 구분합니다.</aside>@endif
<div class="shop-auth-wrap"><section class="shop-auth-card" aria-labelledby="shop-signup-title">
    @include('shop.partials.auth_brand')
    <div class="shop-auth-content"><p class="shop-auth-eyebrow">JOIN THIS SHOP</p><h1 id="shop-signup-title">간편회원가입</h1>
        <p class="shop-auth-description">{{ $isPreview ? '가입 화면의 회원정보와 동의 항목을 확인해 주세요.' : $providerLabel.' 인증 정보를 확인한 후 필수 약관에 동의해 주세요.' }}</p>
        <h2>회원정보입력</h2>
        <dl class="shop-auth-identity"><dt>가입경로</dt><dd>{{ $providerLabel }}</dd><dt>회원번호</dt><dd>가입 완료 시 자동 발급</dd></dl>
        <form action="{{ $isPreview ? route('local.preview.shop_signup', ['channel' => $shop->id]) : route('social.register') }}" method="{{ $isPreview ? 'GET' : 'POST' }}" class="shop-auth-form" data-shop-social-signup>
            @unless($isPreview) @csrf @endunless
            @if($isPreview)<input type="hidden" name="channel" value="{{ $shop->id }}">@endif
            <label for="shop-social-name">이름 <span aria-label="필수">*</span></label><input id="shop-social-name" name="name" value="{{ is_string(old('name')) ? old('name') : $pending['name'] }}" maxlength="100" required autocomplete="name">
            <fieldset class="shop-auth-gender"><legend>성별</legend><label><input type="radio" name="gender" value="Male" @checked(old('gender')==='Male')>남성</label><label><input type="radio" name="gender" value="Female" @checked(old('gender')==='Female')>여성</label></fieldset>
            <label for="shop-social-email">이메일</label><input id="shop-social-email" type="email" value="{{ $pending['email'] }}" readonly autocomplete="email">
            <section class="shop-auth-agreements" aria-label="가입 약관 동의"><h2>약관동의</h2>
                @if($isPreview)
                    <div class="shop-auth-review-note" role="status"><strong>약관 검토 중 · 승인 대기</strong><p>각 동의 항목의 내용을 확인해 주세요. 승인 전까지 실제 가입 제출과 운영 반영을 보류합니다.</p></div>
                @endif
                @foreach($documents as $field => $item)
                    <div><label class="shop-auth-check"><input class="shop-signup-agreement" type="checkbox" name="{{ $field }}" value="1" @if($item['required']) required @endif @checked(old($field)) @disabled(!$item['available'] || (!$canRegister && !$isPreview))>{{ $item['label'] }} ({{ $item['required'] ? '필수' : '선택' }})</label>
                        <details><summary>{{ $item['label'] }} 전문보기</summary>
                            @foreach($item['documents'] as $document)
                                <div class="shop-auth-policy" tabindex="0" aria-label="{{ $item['label'] }} 내용">@include('front.policies.content',['document'=>$document])</div>
                            @endforeach
                        </details>
                    </div>
                @endforeach
                <label class="shop-auth-check"><input type="checkbox" data-select-all=".shop-signup-agreement">전체 약관 동의</label>
            </section>
            @unless($canRegister)<p role="status">회원가입 약관을 준비 중입니다. 약관 승인 전까지 가입 완료를 보류합니다.</p>@endunless
            <button type="submit" class="shop-auth-primary" @disabled(!$canRegister || $isPreview)>가입완료{{ $isPreview ? ' · 약관 승인 대기 (미리보기에서는 제출하지 않음)' : '' }}</button>
            <a class="shop-auth-market" href="{{ route('shop.login') }}">로그인으로 돌아가기</a>
        </form>
        <img class="shop-auth-master-logo" src="{{ asset('shop/images/common/logo2.png') }}" alt="Me9 Market">
    </div>
</section></div>
@endsection
