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

    <div id="wrap" class="@yield('head_none')">
        <header id="header">
            <a href="{{ route('admin.dashboard') }}" class="logo">Me9 market</a>

            @php
                $currentPage = Session::get('page');
                $isUserManagement = in_array($currentPage, ['users', 'admins', 'add_edit_user', 'add_edit_admin', 'view_vendor_details']) || strpos($currentPage, 'view_') === 0;
                $isSettings = in_array($currentPage, ['update_admin_password', 'update_admin_details', 'update_personal_details', 'update_business_details', 'update_bank_details']);
                $isProductManagement = in_array($currentPage, ['products', 'categories', 'sections', 'brands', 'filters', 'attributes']);
                $isSupport = in_array($currentPage, ['notices', 'faqs', 'contacts']);
            @endphp
            <div class="t_menu">
                <ul>
                    <li><a href="#" class="{{ $isSettings ? 'on' : '' }}">환경설정</a></li>
                    <li><a href="{{ url('admin/users') }}" class="{{ $isUserManagement ? 'on' : '' }}">회원관리</a></li>
                    <li><a href="{{ url('admin/products') }}" class="{{ $isProductManagement ? 'on' : '' }}">상품관리</a>
                    </li>
                    <li><a href="{{ url('admin/notices') }}" class="{{ $isSupport ? 'on' : '' }}">고객센터</a></li>
                    <li><a href="#">판매사이트관리</a></li>
                </ul>
            </div>

            <div class="r_bx">
                <div class="name">[최고관리자] {{ Auth::user()->name ?? 'Admin' }}</div>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="btn icon1">로그아웃</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </header><!-- //header -->

        <div id="container">
            <!-- 왼쪽 메뉴 -->
            @include('layouts.inc.admin_sidebar')

            <div id="container_w">
                @yield('content')
            </div>
        </div>

        <footer id="footer">

        </footer>
    </div><!-- //wrap -->
</body>

</html>