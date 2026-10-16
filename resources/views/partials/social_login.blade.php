@php($socialProviders = app(\App\Services\SocialLoginProvider::class)->available())
@if($socialProviders)
    <div class="social-login-options" aria-label="소셜 로그인">
        @foreach($socialProviders as $socialProvider => $socialLabel)
            <a class="social-login-button social-login-{{ $socialProvider }}" href="{{ route('social.start', ['provider' => $socialProvider, 'context' => $socialContext ?? 'member']) }}">{{ $socialLabel }}로 계속하기</a>
        @endforeach
    </div>
@endif
