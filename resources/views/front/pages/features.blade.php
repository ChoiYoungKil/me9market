@extends('layouts.market-sub')
@section('page_title', '주요기능')
@section('content')
<div class="market-shell">
    <header class="market-page-head">
        <nav class="market-breadcrumb" aria-label="현재 위치"><a href="{{ route('home') }}">HOME</a><span aria-hidden="true">/</span><span>주요기능</span></nav>
        <p class="home-eyebrow">WHAT YOU CAN DO</p>
        <h1>채널 운영의 모든 순간을,<br><em>하나의 흐름으로.</em></h1>
        <p class="market-page-description">상품을 구성하는 순간부터 고객을 만나는 과정까지, 미구마켓의 주요 기능을 소개합니다.</p>
    </header>
    <section class="market-feature-list" aria-label="주요 기능 안내">
        <article><span class="feature-index">01</span><div><p class="home-eyebrow">PRODUCT & CHANNEL</p><h2>상품과 Shop 채널 관리</h2></div><p>자사·공유·제휴상품과 공동구매를 관리합니다. Shop의 공개 범위, 운영기간, 배너와 판매상품을 설정하고 주소·QR로 공유하세요.</p></article>
        <article><span class="feature-index">02</span><div><p class="home-eyebrow">ORDER & DELIVERY</p><h2>주문부터 배송까지</h2></div><p>주문과 취소·반품·교환 신청을 확인합니다. 발주 담당자는 전용 화면에서 담당 주문과 송장 정보를 관리할 수 있습니다.</p></article>
        <article><span class="feature-index">03</span><div><p class="home-eyebrow">POINTS & SETTLEMENT</p><h2>포인트와 정산 내역</h2></div><p>채널 포인트 정책과 사용 이력을 관리하고 정산기간별 내역을 확인합니다. 판매·배송·수수료 관련 정보를 함께 살펴보세요.</p></article>
        <article><span class="feature-index">04</span><div><p class="home-eyebrow">MEMBER & SUPPORT</p><h2>고객과 이어지는 운영</h2></div><p>회원은 마이페이지에서 내 정보와 주문을 확인합니다. 공지사항과 상품문의, 채널별 고객 관리를 통해 필요한 안내를 전달하세요.</p></article>
    </section>
    <aside class="market-next-banner"><div><p class="home-eyebrow">YOUR NEXT STEP</p><h2>이제, 나만의 채널을 만나보세요.</h2></div><a class="home-text-link" href="{{ route('front.subscription_info') }}">가입안내 확인하기 <span class="home-link-arrow" aria-hidden="true">↗</span></a></aside>
</div>
@endsection
