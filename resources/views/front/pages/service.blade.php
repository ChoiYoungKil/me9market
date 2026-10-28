@extends('layouts.market-sub')
@section('page_title', '서비스안내')
@section('content')
<div class="market-shell">
    <nav class="market-breadcrumb" aria-label="현재 위치"><a href="{{ route('home') }}">HOME</a><span aria-hidden="true">/</span><span>서비스안내</span></nav>
    <section class="service-cover" aria-labelledby="service-title">
        <div class="service-cover-copy">
            <p class="home-eyebrow">ABOUT ME9 MARKET</p>
            <h1 id="service-title">좋은 상품과<br>나만의 채널이<br><em>만나는 곳.</em></h1>
            <p class="market-lead">판매자와 고객을 이어주는 새로운 연결.<br>미구마켓에서 나만의 Shop을 만들어보세요.</p>
            <a class="home-text-link" href="{{ route('front.subscription_info') }}">미구마켓 시작하는 방법 <span class="home-link-arrow" aria-hidden="true">↗</span></a>
        </div>
        <div class="service-cover-visual">
            <img src="{{ asset('me9market/images/main/market-hero.webp') }}" alt="상품과 채널의 연결을 표현한 보랏빛 고리와 제품 패키지" width="1672" height="941" fetchpriority="high">
            <div><span>CONNECT YOUR MARKET</span><strong>More connections.<br>More possibilities.</strong></div>
        </div>
    </section>
    <section class="market-editorial" aria-labelledby="service-about">
        <div><p class="home-eyebrow">OUR SERVICE</p><h2 id="service-about">상품의 가능성에<br><em>당신의 채널을 더합니다.</em></h2></div>
        <div class="market-editorial-copy"><p>좋은 상품을 가진 판매자와 자신만의 고객을 만나는 Shop 채널. 미구마켓은 상품을 연결하고 채널을 운영하는 이들의 공간입니다.</p><p>공개 범위와 판매상품을 구성해 나만의 Shop을 만들고, 채널 주소나 QR로 고객과 연결할 수 있습니다.</p></div>
    </section>
    <section class="service-principles" aria-label="미구마켓 서비스 구성">
        <article><span>01 / PRODUCT</span><h3>좋은 상품을<br>더 넓은 기회로.</h3><p>자사상품과 공유·제휴상품을 관리하고, 채널에 맞는 판매상품을 구성합니다.</p></article>
        <article><span>02 / MY SHOP</span><h3>나의 고객을 위한<br>나만의 공간.</h3><p>공개·비공개 설정과 채널 정보로 Shop을 운영하고 고객을 초대합니다.</p></article>
        <article><span>03 / TOGETHER</span><h3>운영의 흐름을<br>한곳에서.</h3><p>상품부터 주문·배송·포인트·정산까지 채널 운영에 필요한 정보를 관리합니다.</p></article>
    </section>
    <aside class="market-next-banner"><div><p class="home-eyebrow">EXPLORE ME9</p><h2>미구마켓에서 할 수 있는 일.</h2></div><a class="home-text-link" href="{{ route('front.features') }}">주요기능 살펴보기 <span class="home-link-arrow" aria-hidden="true">↗</span></a></aside>
</div>
@endsection
