@extends('layouts.frontend')
@section('page_type', 'sub')
@section('content')
<main id="container" class="social-signup-page"><section class="social-signup-card">
    <h1>소셜 회원가입</h1>
    <p>{{ \App\Services\SocialLoginProvider::LABELS[$pending['provider']] }} 인증을 완료했습니다. 필수 약관에 동의하면 가입됩니다.</p>
    @if($errors->any())<div role="alert" class="social-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @unless($canRegister)<p role="status">회원가입 약관을 준비 중입니다. 판매자에게 문의해 주세요.</p>@endunless
    <form method="POST" action="{{ route('social.register') }}">@csrf
        <label>이메일 <input type="email" value="{{ $pending['email'] }}" readonly></label>
        <label>이름 <input name="name" value="{{ old('name', $pending['name']) }}" maxlength="100" required autocomplete="name"></label>
        @foreach(['terms_service' => ['terms_url', '이용약관'], 'terms_privacy' => ['privacy_url', '개인정보 수집 및 이용'], 'terms_third_party' => ['third_party_url', '제3자 정보제공']] as $field => [$urlKey, $label])
            <div class="social-consent"><label><input type="checkbox" name="{{ $field }}" value="1" required @checked(old($field))> {{ $label }} 동의 (필수)</label>
            @if(config('shop_channel.'.$urlKey))<a href="{{ config('shop_channel.'.$urlKey) }}" target="_blank" rel="noopener">전문 보기</a>@endif</div>
        @endforeach
        <button type="submit" @disabled(!$canRegister)>동의하고 가입하기</button>
        <a href="{{ route('front.member.login') }}">로그인으로 돌아가기</a>
    </form>
</section></main>
@endsection
