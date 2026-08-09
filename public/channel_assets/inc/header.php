<!DOCTYPE html>
<html lang="ko">
<head>
<title>Me9 market</title>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="format-detection" content="telephone=no"/>
	<meta name="description" content=""/>
	<meta name="keywords" content="" />

	<!--<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0, user-scalable=no" />-->
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link href="../css/base.css?<?php echo $jscssVersion ?>" rel="stylesheet" type="text/css" />
	<link href="../css/common.css?<?php echo $jscssVersion ?>" rel="stylesheet" type="text/css" />
	<link href="../css/main.css?<?php echo $jscssVersion ?>" rel="stylesheet" type="text/css" />
	<link href="../css/sub.css?<?php echo $jscssVersion ?>" rel="stylesheet" type="text/css" />
	<link href="../css/board.css?<?php echo $jscssVersion ?>" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="../images/icon/favicon.ico" type="image/x-icon">

	<meta property="og:type" content="website">
	<meta property="og:title" content="">
	<meta property="og:description" content="">
	<meta property="og:image" content="../images/common/url_img_logo.jpg">

	<script src="../js/jquery-3.7.0.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
	<script src="../js/slick.min.js"></script>
	<script src="../js/common.js"></script>
</head>

<body id="<?php echo $page_type; ?>">
	<div id="skipNavi">
		<ul>
			<li>
				<a href="#container">본문 바로가기</a>
				<a href="#gnb">주메뉴 바로가기</a>
			</li>
		</ul>
	</div>

	<div id="wrap" class="<?php echo $head_none; ?>">
		<header id="header">
			<a href="../main/" class="logo">Me9 market</a>
			
			<div class="t_menu">
			    <ul>
			        <li><a href="../sub01/shop_list.php">Shop채널관리</a></li>
			        <li><a href="../sub02/product_own.php">상품관리</a></li>
			        <li><a href="./">공동구매관리</a></li>
			        <li><a href="../sub04/order_list.php">주문관리</a></li>
			        <li><a href="./">정산관리</a></li>
			    </ul>
			</div>
			
			<div class="r_bx">
			    <div class="name">[상호명] 회원명</div>
			    <a href="./" class="btn icon1"></a>
			    <a href="./" class="btn icon2"></a>
			    <div class="r_menu">
			        <div class="r_menu_btn"><span>채널목록</span></div>
			        <div class="menu_w">
			            <ul>
			                <li><a href="./"><span>내채널목록 내채널목록</span></a></li>
			                <li><a href="./"><span>내채널목록</span></a></li>
			                <li><a href="./"><span>내채널목록</span></a></li>
			            </ul>
			        </div>
			    </div>
			</div>
		</header><!-- //header -->
		<script type="text/javascript">
            $("#header .t_menu ul li:nth-child(<?php echo $dep1_id; ?>) a").addClass("on");
        </script>

		<div id="container">
            <!-- 왼쪽 메뉴 -->
		    <?php include __DIR__ ."/../inc/left_menu.php"; ?>