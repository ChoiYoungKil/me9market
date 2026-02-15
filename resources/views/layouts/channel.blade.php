<!DOCTYPE html>
<html lang="ko">

<head>
	<title>Me9 market</title>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<!-- CSS -->
	<link href="/channel_assets/css/base.css" rel="stylesheet" type="text/css" />
	<link href="/channel_assets/css/common.css" rel="stylesheet" type="text/css" />
	<link href="/channel_assets/css/main.css" rel="stylesheet" type="text/css" />
	<link href="/channel_assets/css/sub.css" rel="stylesheet" type="text/css" />
	<link href="/channel_assets/css/board.css" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="/channel_assets/images/icon/favicon.ico" type="image/x-icon">

	<meta property="og:type" content="website">
	<meta property="og:title" content="">
	<meta property="og:description" content="">
	<meta property="og:image" content="/channel_assets/images/common/url_img_logo.jpg">

	<!-- JS -->
	<script src="/channel_assets/js/jquery-3.7.0.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
	<script src="/channel_assets/js/slick.min.js"></script>
	<script src="/channel_assets/js/common.js"></script>

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
			<a href="/" class="logo">Me9 market</a>

			<div class="t_menu">
				<ul>
					<li><a href="{{ route('channel.shop_list') }}">Shop채널관리</a></li>
					<li><a href="{{ route('channel.product_own') }}">상품관리</a></li>
					<li><a href="#">공동구매관리</a></li>
					<li><a href="{{ route('channel.order.list') }}">주문관리</a></li>
					<li><a href="{{ route('channel.settlement.list') }}">정산관리</a></li>
				</ul>
			</div>

			<div class="r_bx">
				<div class="name">[상호명] {{ Auth::user()->name ?? 'Seller' }}</div>
				<a href="#" class="btn icon1"></a>
				<a href="#" class="btn icon2"></a>
				<div class="r_menu">
					<div class="r_menu_btn"><span>채널목록</span></div>
					<div class="menu_w">
						<ul>
							<li><a href="#"><span>내채널목록 내채널목록</span></a></li>
							<li><a href="#"><span>내채널목록</span></a></li>
							<li><a href="#"><span>내채널목록</span></a></li>
						</ul>
					</div>
				</div>
			</div>
		</header><!-- //header -->

		<script type="text/javascript">
			// $("#header .t_menu ul li:nth-child(<?php // echo $dep1_id; ?>) a").addClass("on");
			// Blade implementation of above:
			// This needs to be handled via passing variables or JS in view
		</script>

		<div id="container">
			<!-- 왼쪽 메뉴 -->
			@include('layouts.inc.channel_sidebar')

			<div id="container_w">
				@yield('content')
			</div>
		</div>

		<footer id="footer">

		</footer>
	</div><!-- //wrap -->
	@stack('scripts')
</body>

</html>