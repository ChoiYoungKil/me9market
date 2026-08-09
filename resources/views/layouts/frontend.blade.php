<!DOCTYPE html>
<html lang="ko">

<head>
    <title>Me9 market</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0, user-scalable=no" />

    <!-- CSS -->
    <link href="/me9market/css/base.css" rel="stylesheet" type="text/css" />
    <link href="/me9market/css/common.css" rel="stylesheet" type="text/css" />
    <link href="/me9market/css/main.css" rel="stylesheet" type="text/css" />
    <link href="/me9market/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/me9market/css/board.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="/me9market/images/icon/favicon.ico" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="/me9market/images/common/url_img_logo.jpg">

    <!-- JS -->
    <script src="/me9market/js/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/me9market/js/slick.min.js"></script>
    <script src="/me9market/js/common.js"></script>
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

                <div class="menu_btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="menu_bx">
                    <ul class="dep1_wrap">
                        <li class="dep1 dep01"><a href="{{ url()->current() }}">서비스안내</a></li>
                        <li class="dep1 dep02"><a href="{{ url()->current() }}">주요기능</a></li>
                        <li class="dep1 dep03"><a href="{{ url()->current() }}">가입안내</a></li>
                        <li class="dep1 dep04"><a href="{{ route('cs.notice') }}">고객센터</a></li>
                    </ul>

                    <div class="r_btn">
                        <ul>
                            @guest
                                <!-- 로그인전 -->
                                <li><a href="{{ route('front.member.login') }}">로그인</a></li>
                                <li><a href="{{ route('front.member.register.member') }}">회원가입</a></li>
                            @else
                                <!-- 로그인후 -->
                                <li><a href="{{ route('channel.index') }}">채널관리자</a></li>
                                <li><a>{{ Auth::user()->name }} 님</a></li>
                                <li><a href="{{ route('mypage.dashboard') }}">마이페이지</a></li>
                                <li>
                                    <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">로그아웃</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </div>
        </header><!-- //header -->

        @yield('content')

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
                    <li><a href="{{ url()->current() }}">이용약관</a></li>
                    <li><a class="bold" href="{{ url()->current() }}">개인정보취급방침</a></li>
                </ul>
                <div class="btm_txt">ⓒ Skytech Co., Ltd</div>
                <div class="top_btn">TOP</div>
            </div>
        </footer>
    </div><!-- //wrap -->
</body>

</html>