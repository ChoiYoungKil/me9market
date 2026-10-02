<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="format-detection" content="telephone=no">
    <title>@yield('title', ($shop->channel_name ?? 'Me9 Market').' | Shop')</title>
    <meta name="description" content="{{ $shop->og_description ?? '' }}">
    <meta property="og:title" content="{{ $shop->og_title ?? $shop->channel_name ?? 'Me9 Market' }}">
    <meta property="og:description" content="{{ $shop->og_description ?? '' }}">
    <meta property="og:image" content="{{ asset($shop->og_image ?? 'shop/images/common/logo.png') }}">
    @foreach(['base', 'common', 'main', 'sub', 'board', 'runtime'] as $stylesheet)
        <link rel="stylesheet" href="{{ asset('shop/css/'.$stylesheet.'.css') }}?v={{ filemtime(public_path('shop/css/'.$stylesheet.'.css')) }}">
    @endforeach
    <link rel="icon" type="image/png" href="{{ asset('shop/images/common/logo2.png') }}">
    @stack('styles')
</head>
<body id="@yield('page_type', 'sub')" class="shop-runtime">
<div id="skipNavi"><a href="#container">본문 바로가기</a></div>
<div id="wrap">
    <header id="header">
        <div class="h_inner">
            <a href="{{ route('shop.channel_main') }}" class="logo" aria-label="{{ $shop->channel_name ?? 'Me9 Market' }} 홈">
                <img src="{{ asset(($shop->use_logo ?? false) && ($shop->logo_image ?? null) ? $shop->logo_image : 'shop/images/common/logo2.png') }}" alt="{{ $shop->channel_name ?? 'Me9 Market' }}">
            </a>
            <button type="button" class="search_btn" aria-label="검색 열기" aria-expanded="false" aria-controls="shop-search"><span></span><span></span></button>
            <form id="shop-search" class="search_bx" action="{{ route('shop.products_list') }}" method="GET" role="search">
                <input name="search" value="{{ request('search') }}" maxlength="100" aria-label="상품 검색">
                <button class="shop-search-submit" type="submit" title="검색" aria-label="검색"></button>
            </form>
            <div class="r_bx">
                <a href="{{ auth()->check() || session('last_shop_order_id') || session('nonmember_order_id') ? route('front.shop.order.details') : route('front.shop.order.confirm') }}" class="icon1" title="주문관리" aria-label="주문관리"></a>
                <a href="{{ route('front.shop.cart.index') }}" class="icon2" title="장바구니" aria-label="장바구니"></a>
                @auth
                    <form method="POST" action="{{ route('shop.logout') }}">@csrf<button type="submit" class="shop-account" title="로그아웃" aria-label="로그아웃"></button></form>
                @else
                    <a href="{{ route('shop.login') }}" class="icon3" title="로그인" aria-label="로그인"></a>
                @endauth
            </div>
        </div>
    </header>
    <main id="container">
        @if(session('flash_message_success'))
            <div class="shop-message success" role="status">{{ session('flash_message_success') }}</div>
        @endif
        @if(session('flash_message_error'))
            <div class="shop-message error" role="alert">{{ session('flash_message_error') }}</div>
        @endif
        @if($errors->any())
            <div class="shop-message error" role="alert">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
    <footer id="footer">
        <div class="f_inner">
            <div class="txt1">&copy; {{ $shop->copyright ?? $shop->channel_name ?? 'Me9 Market' }} All rights Reserved.</div>
            <ul class="link_bx"><li><a href="{{ url('/') }}">Me9 Market</a></li><li><a href="{{ route('shop.notices') }}" class="bold">공지사항</a></li></ul>
        </div>
    </footer>
</div>
<script src="{{ asset('shop/js/runtime.js') }}?v={{ filemtime(public_path('shop/js/runtime.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
