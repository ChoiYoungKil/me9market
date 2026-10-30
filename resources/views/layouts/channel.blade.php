@php
    $channelAdmin=Auth::guard('admin')->user();
    $channelAssignment=$channelAdmin?->type==='subadmin' ? \App\Models\ChannelSubAccount::where('admin_id',$channelAdmin->id)->where('vendor_id',$channelAdmin->vendor_id)->activeNow()->first() : null;
    $channelCan=fn($key)=>$channelAdmin?->type!=='subadmin' || in_array($key,$channelAssignment?->permissions ?? [],true);
@endphp
<!DOCTYPE html>
<html lang="ko">

	<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Me9 market</title>
		<meta charset="utf-8" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
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

    @stack('styles')
    <link href="{{ asset('common/portal.css').'?v='.filemtime(public_path('common/portal.css')) }}" rel="stylesheet">
</head>

<body class="portal portal-channel" id="@yield('page_type', 'main')">
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
            <button class="portal-menu-toggle" type="button" aria-controls="l_menu" aria-expanded="false">메뉴</button>
			<a href="/" class="logo">Me9 market</a>

			<div class="t_menu" id="gnb" role="navigation" aria-label="주 메뉴">
				<ul>
@if($channelCan('shop'))					<li><a href="{{ route('channel.shop_list') }}" class="{{ request()->is('channel/shop*') ? 'on' : '' }}">Shop채널관리</a></li>@endif
@if($channelCan('product'))					<li><a href="{{ route('channel.product_own') }}" class="{{ request()->is('channel/product*') ? 'on' : '' }}">상품관리</a></li>@endif
@if($channelCan('joint_purchase'))					<li><a href="{{ route('channel.joint_purchase.list') }}" class="{{ request()->is('channel/joint-purchase*') ? 'on' : '' }}">공동구매관리</a></li>@endif
@if($channelCan('order'))					<li><a href="{{ route('channel.order.list') }}" class="{{ request()->is('channel/order*') || request()->is('channel/inquiries*') ? 'on' : '' }}">주문관리</a></li>@endif
@if($channelCan('settings'))					<li><a href="{{ route('channel.settlement.list') }}" class="{{ request()->is('channel/settlement*') ? 'on' : '' }}">정산관리</a></li>@endif
				</ul>
			</div>

			<div class="r_bx">
				<div class="name">[{{ $channelCompanyName ?: '회원사' }}] {{ Auth::guard('admin')->user()->name ?? 'Seller' }}</div>
				@if($channelAdmin?->type!=='subadmin')<a href="{{ route('channel.complete_profile') }}" class="btn icon1" title="내 정보" aria-label="내 정보"></a>@endif
				<a href="{{ route('channel.logout') }}" class="btn icon2" title="로그아웃"></a>
				@if($channelCan('shop'))<div class="r_menu">
					<div class="r_menu_btn"><span>채널목록{{ isset($headerChannels) && $headerChannels->count() ? ' (' . $headerChannels->count() . ')' : '' }}</span></div>
					<div class="menu_w">
						<ul>
							@forelse($headerChannels ?? [] as $channel)
								<li>
									<a href="{{ route('channel.shop_info', ['id' => $channel->id]) }}">
										<span>
											{{ \Illuminate\Support\Str::limit($channel->channel_name, 24) }}
											<small style="display:block; margin-top:3px; color:#8a8a8a; font-size:11px;">
												/{{ $channel->channel_code }} · {{ $channel->status == 1 ? '운영' : '중지' }} · {{ $channel->is_public == 1 ? '공개' : '비공개' }}
											</small>
										</span>
									</a>
								</li>
							@empty
								<li><a href="{{ route('channel.shop_register') }}"><span>등록된 채널이 없습니다. 채널등록</span></a></li>
							@endforelse
@if($channelCan('shop'))							<li><a href="{{ route('channel.shop_list') }}"><span>전체 채널목록 보기</span></a></li>@endif
						</ul>
					</div>
				</div>
                @endif
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

    <script src="{{ asset('common/portal.js').'?v='.filemtime(public_path('common/portal.js')) }}"></script>
    @stack('scripts')
</body>

</html>
