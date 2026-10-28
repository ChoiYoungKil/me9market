<header class="market-page-head market-shell">
    <nav class="market-breadcrumb" aria-label="현재 위치"><a href="{{ route('home') }}">HOME</a><span aria-hidden="true">/</span><span>고객센터</span></nav>
    <p class="home-eyebrow">CUSTOMER SUPPORT</p>
    <h1>함께하는 미구마켓,<br><em>무엇이 궁금하세요?</em></h1>
    <p class="market-page-description">공지사항부터 이용 중 궁금한 점까지, 필요한 안내를 확인하세요.</p>
    <nav class="market-support-tabs" aria-label="고객센터 메뉴">
        <a href="{{ route('cs.notice') }}" @if(request()->routeIs('cs.notice*')) aria-current="page" @endif>공지사항 <span aria-hidden="true">↗</span></a>
        <a href="{{ route('cs.faq') }}" @if(request()->routeIs('cs.faq')) aria-current="page" @endif>자주 묻는 질문 <span aria-hidden="true">↗</span></a>
        <a href="{{ route('cs.contact') }}" @if(request()->routeIs('cs.contact')) aria-current="page" @endif>제휴·문의 <span aria-hidden="true">↗</span></a>
    </nav>
</header>
