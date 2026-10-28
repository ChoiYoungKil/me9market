<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Me9 market') · Me9 market</title>
    <meta name="description" content="상품과 사람, 나만의 Shop 채널을 연결하는 미구마켓">
    <link rel="icon" href="{{ asset('me9market/images/icon/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('me9market/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('me9market/css/sub.css') }}">
    <link rel="stylesheet" href="{{ asset('me9market/css/board.css') }}">
    <link rel="stylesheet" href="{{ asset('common/social.css') }}">
    <link rel="stylesheet" href="{{ asset('me9market/css/home-story.css').'?v='.filemtime(public_path('me9market/css/home-story.css')) }}">
    <link rel="stylesheet" href="{{ asset('me9market/css/market-sub.css').'?v='.filemtime(public_path('me9market/css/market-sub.css')) }}">
    <script src="{{ asset('me9market/js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('common/axios.min.js') }}"></script>
    <script src="{{ asset('me9market/js/market-sub.js').'?v='.filemtime(public_path('me9market/js/market-sub.js')) }}" defer></script>
    <noscript><style>.market-sub .home-header{position:relative;height:auto;flex-wrap:wrap;padding:25px;gap:20px}.market-sub .home-menu{display:flex;position:static;height:auto;width:100%;padding:0;flex-wrap:wrap}.market-sub .home-menu-toggle{display:none}.market-sub .market-content{padding-top:0}</style></noscript>
    @env('local')<script src="{{ asset('me9market/js/preview-live.js') }}" defer></script>@endenv
    @stack('styles')
</head>
<body id="sub" class="home-page market-sub" data-home-theme="light" @if(request()->isMethod('post')) data-preview-preserve="true" @endif>
    <a class="home-skip" href="#market-content">본문 바로가기</a>
    @include('partials.market-header')
    <main id="market-content" class="market-content" tabindex="-1">
        @yield('content')
    </main>
    @include('partials.market-footer')
    @stack('scripts')
</body>
</html>
