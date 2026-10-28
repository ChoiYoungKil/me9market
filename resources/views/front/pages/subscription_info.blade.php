@extends('layouts.market-sub')
@section('page_title', '가입안내')
@section('content')
<div class="market-shell">
    <header class="market-page-head">
        <nav class="market-breadcrumb" aria-label="현재 위치"><a href="{{ route('home') }}">HOME</a><span aria-hidden="true">/</span><span>가입안내</span></nav>
        <p class="home-eyebrow">START YOUR MARKET</p><h1>나만의 시작을,<br><em>미구마켓과 함께.</em></h1>
        <p class="market-page-description">회원으로 쇼핑을 시작하거나, 판매자로 나만의 Shop 채널을 운영하세요.</p>
    </header>
    <section class="market-onboarding" aria-labelledby="member-guide">
        <div class="onboarding-heading"><span>FOR MEMBERS</span><h2 id="member-guide">미구마켓을<br>이용하고 싶다면.</h2><a class="market-button" href="{{ route('front.member.register.member') }}">일반 회원가입 <span aria-hidden="true">↗</span></a></div>
        <ol><li><span>01</span><div><h3>회원가입</h3><p>아이디·이메일·비밀번호를 입력하고 필요한 약관에 동의합니다.</p></div></li><li><span>02</span><div><h3>로그인 및 정보 관리</h3><p>마이페이지에서 회원정보와 배송지를 관리합니다.</p></div></li><li><span>03</span><div><h3>Shop 채널 이용</h3><p>전달받은 채널 주소나 입장코드로 방문하고 주문내역을 확인하세요.</p></div></li></ol>
    </section>
    <section class="market-onboarding" aria-labelledby="seller-guide">
        <div class="onboarding-heading"><span>FOR SELLERS</span><h2 id="seller-guide">나만의 Shop을<br>운영하고 싶다면.</h2><a class="market-button market-button-outline" href="{{ route('front.seller.register') }}">판매자 등록 <span aria-hidden="true">↗</span></a></div>
        <ol><li><span>01</span><div><h3>판매자 정보 등록</h3><p>회원 로그인 후 판매자 등록을 진행하고 필요한 운영정보를 준비합니다.</p></div></li><li><span>02</span><div><h3>채널 운영정보 확인</h3><p>승인 상태와 채널 정보를 확인하고 판매·정산에 필요한 정보를 등록합니다.</p></div></li><li><span>03</span><div><h3>Shop 생성 및 상품 연결</h3><p>채널관리자에서 Shop을 등록하고 판매상품과 공개 범위를 구성하세요.</p></div></li></ol>
    </section>
    <aside class="market-next-banner"><div><p class="home-eyebrow">NEED HELP?</p><h2>시작하기 전, 궁금한 점이 있나요?</h2></div><a class="home-text-link" href="{{ route('cs.faq') }}">자주 묻는 질문 <span class="home-link-arrow" aria-hidden="true">↗</span></a></aside>
</div>
@endsection
