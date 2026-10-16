@php($connectedSocial = \App\Models\SocialAccount::where('user_id', auth()->id())->get()->keyBy('provider'))
<section class="social-connections box">
    <h2>소셜 계정 연결</h2>
    <p>연결 또는 해제를 요청하면 비밀번호를 확인합니다. 확인 후 원하는 작업을 다시 선택해 주세요.</p>
    @if(session('success_message'))<p role="status">{{ session('success_message') }}</p>@endif
    @error('social')<p role="alert">{{ $message }}</p>@enderror
    @foreach(\App\Services\SocialLoginProvider::LABELS as $provider => $label)
        @if(isset($connectedSocial[$provider]))
            <form method="POST" action="{{ route('social.disconnect', $provider) }}">@csrf @method('DELETE')<span>{{ $label }} 연결됨</span><button type="submit">연결 해제</button></form>
        @elseif(app(\App\Services\SocialLoginProvider::class)->ready($provider))
            <form method="POST" action="{{ route('social.connect', $provider) }}">@csrf<span>{{ $label }}</span><button type="submit">계정 연결</button></form>
        @endif
    @endforeach
</section>
