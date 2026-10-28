<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="좋은 상품과 나만의 Shop 채널을 연결하는 미구마켓. 서비스 소개와 이용방법을 확인하세요.">
    <title>Me9 market · 상품과 채널을 잇다</title>
    <link rel="icon" href="{{ asset('me9market/images/icon/favicon.ico') }}">
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('me9market/css/font/Pretendard-Regular.woff2') }}" crossorigin>
    <link rel="preload" as="image" href="{{ asset('me9market/images/main/market-hero.webp') }}">
    <noscript><style>.home-page [data-home-go],.home-page-count,.home-section-nav,.home-menu-toggle{display:none!important}.home-header{position:relative;height:auto;min-height:80px;flex-wrap:wrap;gap:20px;padding:22px 25px}.home-menu{display:flex!important;position:static!important;width:100%;height:auto!important;padding:0!important;flex-direction:column;align-items:flex-start;gap:20px}.home-primary-nav{flex-direction:row;flex-wrap:wrap;gap:16px}.home-primary-nav a{font-size:14px}.home-member-nav{padding-top:0;border:0}.home-panel{min-height:auto}.home-panel-inner{padding-top:65px}#guide-shop[hidden]{display:block!important;margin-top:35px}</style></noscript>
    <link rel="stylesheet" href="{{ asset('me9market/css/home-story.css').'?v='.filemtime(public_path('me9market/css/home-story.css')) }}">
    <script src="{{ asset('me9market/js/home-story.js').'?v='.filemtime(public_path('me9market/js/home-story.js')) }}" defer></script>
    @env('local')<script src="{{ asset('me9market/js/preview-live.js') }}" defer></script>@endenv
</head>
<body class="home-page" data-home-theme="dark">
    <a class="home-skip" href="#home-main">본문 바로가기</a>
    @include('partials.market-header')

    @yield('content')

    <nav class="home-section-nav" aria-label="메인 구역 이동">
        <button type="button" data-home-go="main" aria-current="true"><span class="section-nav-name">메인</span><span class="section-nav-line"></span><span>01</span></button>
        <button type="button" data-home-go="about"><span class="section-nav-name">미구마켓 소개</span><span class="section-nav-line"></span><span>02</span></button>
        <button type="button" data-home-go="how"><span class="section-nav-name">이용방법</span><span class="section-nav-line"></span><span>03</span></button>
        <button type="button" data-home-go="news"><span class="section-nav-name">새로운 소식</span><span class="section-nav-line"></span><span>04</span></button>
    </nav>
    <div class="home-page-count" aria-hidden="true"><span data-home-count>01</span><span class="count-rule"></span><span>04</span></div>
</body>
</html>
