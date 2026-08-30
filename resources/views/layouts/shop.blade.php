<!DOCTYPE html>
<html lang="ko">

<head>
    <title>Me9 market</title>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    {{-- Assuming $jscssVersion is static or managed otherwise, using hardcoded value for now based on source --}}
    @php $jscssVersion = '251120'; @endphp
    <link href="{{ asset('shop/css/base.css?v=' . $jscssVersion) }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('shop/css/common.css?v=' . $jscssVersion) }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('shop/css/main.css?v=' . $jscssVersion) }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('shop/css/sub.css?v=' . $jscssVersion) }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('shop/css/board.css?v=' . $jscssVersion) }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{ asset('shop/images/icon/favicon.ico') }}" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="{{ asset('shop/images/common/url_img_logo.jpg') }}">

    <script src="{{ asset('shop/js/jquery-3.7.0.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="{{ asset('shop/js/slick.min.js') }}"></script>
    <script src="{{ asset('shop/js/common.js') }}"></script>
</head>

<body id="@yield('page_type', 'sub')">
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
            <div style="line-height: 100px; font-size: 30px; font-weight: 700; text-align: center;">헤더 영역</div>
        </header><!-- //header -->

        @yield('content')

        <footer id="footer">
            <div style="line-height: 60px; font-size: 30px; color: #fff; font-weight: 700; text-align: center;">푸터 영역
            </div>
        </footer>
    </div><!-- //wrap -->
</body>

</html>
