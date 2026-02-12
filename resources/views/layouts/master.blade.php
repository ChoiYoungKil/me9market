<!DOCTYPE html>
<html lang="ko">

<head>
    <title>Me9 market - Admin</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="{{ asset('master_assets/css/base.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master_assets/css/common.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master_assets/css/main.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master_assets/css/sub.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master_assets/css/board.css') }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{ asset('master_assets/images/icon/favicon.ico') }}" type="image/x-icon">

    <script src="{{ asset('master_assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="{{ asset('master_assets/js/slick.min.js') }}"></script>
    <script src="{{ asset('master_assets/js/common.js') }}"></script>
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
        @include('layouts.inc.master_header')

        <div id="container">
            @include('layouts.inc.master_sidebar')

            <div id="container_w">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>