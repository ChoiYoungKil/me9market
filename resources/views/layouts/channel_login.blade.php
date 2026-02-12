<!DOCTYPE html>
<html lang="ko">

<head>
    <title>Me9 market - Channel Login</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="telephone=no" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />
    <link href="{{ asset('channel_assets/css/base.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('channel_assets/css/common.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('channel_assets/css/main.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('channel_assets/css/sub.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('channel_assets/css/board.css') }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{ asset('channel_assets/images/icon/favicon.ico') }}" type="image/x-icon" />
    <script src="{{ asset('channel_assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="{{ asset('channel_assets/js/slick.min.js') }}"></script>
    <script src="{{ asset('channel_assets/js/common.js') }}"></script>
    @yield('styles')
</head>

<body id="main">
    @yield('content')
    @yield('scripts')
</body>

</html>