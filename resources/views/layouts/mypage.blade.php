<!DOCTYPE html>
<html lang="ko">

<head>
    <title>Me9 market</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0, user-scalable=no" />

    <!-- CSS (Using Mypage specific CSS or inheriting from Main/Master? Source uses ../css relative to main, so same as me9market) -->
    <!-- Assuming assets are in /mypage/ if present, but typically frontend uses /me9market/css -->
    <!-- Checking mypage/inc/header.php, it links ../css/base.css. If mypage/main/index.php is base, then ../css is mypage/css OR me9market/css if structure allows. -->
    <!-- Given I copied mypage/* to public/mypage/, I will check if css exists there. If not, fallback to /me9market/css -->

    <link href="/mypage_assets/css/base.css" rel="stylesheet" type="text/css" />
    <link href="/mypage_assets/css/common.css" rel="stylesheet" type="text/css" />
    <link href="/mypage_assets/css/main.css" rel="stylesheet" type="text/css" />
    <link href="/mypage_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/mypage_assets/css/board.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="/mypage_assets/images/icon/favicon.ico" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="/mypage_assets/images/common/url_img_logo.jpg">

    <!-- JS -->
    <script src="/mypage_assets/js/jquery-3.7.0.min.js"></script>
    <script src="/mypage_assets/js/slick.min.js"></script>
    <script src="{{ asset('mypage_assets/js/common.js').'?v='.filemtime(public_path('mypage_assets/js/common.js')) }}"></script>

    @stack('styles')
    <link href="{{ asset('common/portal.css').'?v='.filemtime(public_path('common/portal.css')) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('common/social.css').'?v='.filemtime(public_path('common/social.css')) }}">
    <link rel="stylesheet" href="{{ asset('mypage_assets/css/market-mypage.css').'?v='.filemtime(public_path('mypage_assets/css/market-mypage.css')) }}">
    @env('local')<script src="{{ asset('me9market/js/preview-live.js') }}" defer></script>@endenv
</head>

<body class="portal portal-mypage" id="@yield('page_type', 'main')" data-mypage-user-id="{{ Auth::id() }}" @if(request()->isMethod('post')) data-preview-preserve="true" @endif>
    <div id="skipNavi">
        <ul>
            <li>
                <a href="#container">본문 바로가기</a>
                <a href="#gnb">주메뉴 바로가기</a>
            </li>
        </ul>
    </div>

    <div id="wrap">
        <header id="header">
            <div class="h_inner">
                <a href="/" class="logo" aria-label="Me9 market 홈"><img src="{{ asset('me9market/images/common/logo.png') }}" alt="Me9 market" width="157" height="37"></a>

                <div class="t_menu">
                    <ul>
                        <li><form class="mypage-channel-launch" action="{{ route('mypage.channel_admin') }}" method="post">@csrf<button type="submit">채널관리자</button></form></li>
                        <li><a href="{{ route('mypage.profile') }}">{{ Auth::user()->name ?? 'User' }}</a></li>
                        <li><a href="{{ url('/mypage/points/status') }}">총 포인트 ({{ number_format($mypagePointTotal) }})</a></li>
                        <li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">로그아웃</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>

                    <div class="r_menu_btn"><span>채널목록</span></div>

                    <button class="l_menu_btn portal-menu-toggle" type="button" aria-label="메뉴" aria-controls="l_menu" aria-expanded="false">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>

                <!-- 채널목록 메뉴 -->
                <div class="r_menu">
                    <div class="menu_w">
                        <ul class="con1">
                            @forelse($mypageSubAccounts as $account)
                                <li><form action="{{ route('mypage.channel_admin') }}" method="post">@csrf<input type="hidden" name="sub_account_id" value="{{ $account->id }}"><button type="submit">{{ $account->vendor?->vendorbusinessdetails?->shop_name ?? '배정된 회원사' }}</button></form></li>
                            @empty
                                <li>배정된 관리채널이 없습니다.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </header><!-- //header -->

        <div id="container">
            <!-- 왼쪽 메뉴 -->
            @include('layouts.inc.mypage_sidebar')

            <div id="container_w">
                @if(session('success_message'))<p class="portal-message" role="status">{{ session('success_message') }}</p>@endif
                @if(session('error_message'))<p class="portal-message error" role="alert">{{ session('error_message') }}</p>@endif
                @if($errors->any())<div class="portal-message error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                @yield('content')
            </div>
        </div>

        <footer id="footer">
            <div class="f_inner">
                <img src="/me9market/images/common/f_logo.png" class="logo">
                <ul class="info_bx">
                    @foreach(['company_name' => '상호명', 'representative' => '대표자', 'business_number' => '사업자등록번호', 'commerce_number' => '통신판매업신고번호', 'address' => '주소', 'email' => 'E-mail'] as $key => $label)
                        @if(config('storefront.'.$key))<li><span>{{ $label }}</span> {{ config('storefront.'.$key) }}</li>@endif
                    @endforeach
                </ul>
                <ul class="link_bx">
                    @if(config('shop_channel.terms_url'))<li><a href="{{ config('shop_channel.terms_url') }}" rel="noopener">이용약관</a></li>@endif
                    @if(config('shop_channel.privacy_url'))<li><a class="bold" href="{{ config('shop_channel.privacy_url') }}" rel="noopener">개인정보처리방침</a></li>@endif
                </ul>
                <div class="btm_txt">© {{ date('Y') }} {{ config('storefront.company_name') ?: 'Me9 market' }}</div>
                <div class="top_btn">TOP</div>
            </div>
        </footer>
    </div><!-- //wrap -->
    <script src="{{ asset('common/portal.js').'?v='.filemtime(public_path('common/portal.js')) }}"></script>
    @stack('scripts')
</body>

</html>
