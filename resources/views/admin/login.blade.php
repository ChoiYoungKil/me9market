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

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- CSS -->
    <link href="/master_assets/css/base.css" rel="stylesheet" type="text/css" />
    <link href="/master_assets/css/common.css" rel="stylesheet" type="text/css" />
    <link href="/master_assets/css/main.css" rel="stylesheet" type="text/css" />
    <link href="/master_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/master_assets/css/board.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="/master_assets/images/icon/favicon.ico" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="/master_assets/images/common/url_img_logo.jpg">

    <!-- JS -->
    <script src="/master_assets/js/jquery-3.7.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="/master_assets/js/slick.min.js"></script>
    <script src="/master_assets/js/common.js"></script>
</head>

<body id="sub">
    <div id="skipNavi">
        <ul>
            <li>
                <a href="#container">본문 바로가기</a>
                <a href="#gnb">주메뉴 바로가기</a>
            </li>
        </ul>
    </div>

    <div id="wrap" class="h_none">
        <header id="header">
            <a href="{{ route('admin.dashboard') }}" class="logo">Me9 market</a>

            <div class="t_menu">
                <ul>
                    <li><a href="#" class="on">환경설정</a></li>
                    <li><a href="#">회원관리</a></li>
                    <li><a href="#">판매사이트관리</a></li>
                    <li><a href="#">대분류</a></li>
                    <li><a href="#">대분류</a></li>
                </ul>
            </div>

            <div class="r_bx">
                <div class="name">[최고관리자] Admin</div>
                <a href="#" class="btn icon1">logout</a>
            </div>
        </header><!-- //header -->

        <div id="container">
            <div id="contents">
                <div id="login">
                    <div class="box box1">
                        <div class="inner_bx">
                            <img src="{{ asset('master_assets/images/common/logo1.png') }}" class="logo">
                            <form action="/admin/login" method="POST">
                                @csrf
                                <div class="f_bx">
                                    <input class="mt0" type="text" name="email" placeholder="아이디를 입력 해 주세요"
                                        value="{{ old('email') }}" required autofocus>
                                    <input type="password" name="password" placeholder="비밀번호를 입력 해 주세요" required>
                                    <ul class="chk01">
                                        <li>
                                            <input type="checkbox" id="idSave" name="remember">
                                            <label for="idSave">아이디 저장</label>
                                        </li>
                                    </ul>
                                    <button type="submit" class="btn">LOGIN</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- //contents -->
        </div><!-- //container -->
        <script type="text/javascript">
            $("#login").css("height", $(window).height());
            $(window).resize(function () {
                $("#login").css("height", $(window).height());
            });
        </script>

    </div><!-- //wrap -->
</body>

</html>