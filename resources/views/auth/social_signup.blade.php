@extends('layouts.frontend')
@section('page_type', 'sub')
@section('content')
<main id="container" class="social-signup-page"><section class="social-signup-card">
    <h1>소셜 회원가입</h1>
    <p>{{ \App\Services\SocialLoginProvider::LABELS[$pending['provider']] }} 인증을 완료했습니다.@if($canRegister) 필수 약관에 동의하면 가입됩니다.@endif</p>
    @if($errors->any())<div role="alert" class="social-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @unless($canRegister)
        <div class="social-error" role="status" id="social-registration-unavailable">
            <p>현재 신규 회원가입을 이용할 수 없습니다.</p>
            <p>사이트의 필수 약관 문서를 준비 중입니다. 동의 항목을 모두 체크해도 가입할 수 없습니다. 약관 준비가 완료된 후 다시 이용해 주세요.</p>
        </div>
    @endunless
    <form method="POST" action="{{ route('social.register') }}">@csrf
        <label>이메일 <input type="email" value="{{ $pending['email'] }}" readonly></label>
        <label>이름 <input name="name" value="{{ old('name', $pending['name']) }}" maxlength="100" required autocomplete="name"></label>
        @foreach(['terms_service' => ['terms_url', '이용약관'], 'terms_privacy' => ['privacy_url', '개인정보 수집 및 이용'], 'terms_third_party' => ['third_party_url', '제3자 정보제공']] as $field => [$urlKey, $label])
            <div class="social-consent"><label><input type="checkbox" name="{{ $field }}" value="1" required @disabled(!$canRegister) @checked(old($field))> {{ $label }} 동의 (필수)</label>
            @if(config('shop_channel.'.$urlKey))<a href="{{ config('shop_channel.'.$urlKey) }}" target="_blank" rel="noopener">전문 보기</a>@endif</div>
        @endforeach
        <button type="submit" @disabled(!$canRegister) @if(!$canRegister) aria-describedby="social-registration-unavailable" @endif>{{ $canRegister ? '동의하고 가입하기' : '약관 준비 중' }}</button>
        <a href="{{ route('front.member.login') }}">로그인으로 돌아가기</a>
    </form>
</section></main>
@endsection
