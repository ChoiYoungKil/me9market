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
    <script src="/mypage_assets/js/common.js"></script>
    @stack('scripts')
</head>

<body id="@yield('page_type', 'main')">
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
                <a href="/" class="logo">Me9 market</a>

                <div class="t_menu">
                    <ul>
                        <li><a href="{{ route('channel.index') }}">채널관리자</a></li>
                        <li><a href="javascript:void(0);">{{ Auth::user()->name ?? 'User' }}</a></li>
                        <li><a href="javascript:void(0);">총 포인트 ({{ Auth::user()->point ?? 0 }})</a></li>
                        <li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">로그아웃</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>

                    <div class="r_menu_btn"><span>채널목록</span></div>

                    <div class="l_menu_btn">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <!-- 채널목록 메뉴 -->
                <div class="r_menu">
                    <div class="menu_w">
                        <ul class="con1">
                            <li><a href="javascript:void(0);"><span>A채널명채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>A채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>A채널명</span></a></li>
                        </ul>
                        <ul class="con2">
                            <li><a href="javascript:void(0);"><span>B채널명채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>B채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>B채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>B채널명채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>B채널명</span></a></li>
                            <li><a href="javascript:void(0);"><span>B채널명</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header><!-- //header -->

        <div id="container">
            <!-- 왼쪽 메뉴 -->
            @include('layouts.inc.mypage_sidebar')

            <div id="container_w">
                @yield('content')
            </div>
        </div>

        <footer id="footer">
            <div class="f_inner">
                <img src="/me9market/images/common/f_logo.png" class="logo">
                <ul class="info_bx">
                    <li><span>상호명</span>&nbsp;&nbsp;&nbsp;&nbsp;스카이테크</li>
                    <li><span>대표자</span>&nbsp;&nbsp;&nbsp;&nbsp;홍길동</li>
                    <li><span>사업자등록번호</span>&nbsp;&nbsp;&nbsp;&nbsp;123-45-67890</li>
                    <li><span>통신판매업신고번호</span>&nbsp;&nbsp;&nbsp;&nbsp;0000-1234-567호</li>
                    <li><span>주소</span>&nbsp;&nbsp;&nbsp;&nbsp;사업자주소영역입니다 도로명 주소 자료수급 요청드립니다</li>
                    <li><span>E-mail</span>&nbsp;&nbsp;&nbsp;&nbsp;abc1234@email.com</li>
                </ul>
                <ul class="link_bx">
                    <li><a href="javascript:void(0);">이용약관</a></li>
                    <li><a class="bold" href="javascript:void(0);">개인정보취급방침</a></li>
                </ul>
                <div class="btm_txt">ⓒ Skytech Co., Ltd</div>
                <div class="top_btn">TOP</div>
            </div>
        </footer>
    </div><!-- //wrap -->
</body>

</html>