@extends('layouts.market-home')

@section('content')
<main id="home-main" class="home-stage" tabindex="-1" aria-label="미구마켓 서비스 소개">
    <section id="main" class="home-panel home-hero is-active" data-home-theme="dark" aria-labelledby="hero-title">
        <div class="hero-visual" aria-hidden="true">
            <img src="{{ asset('me9market/images/main/market-hero.webp') }}" alt="" width="1920" height="1080" fetchpriority="high">
        </div>
        <div class="home-panel-inner hero-inner">
            <p class="home-eyebrow home-reveal">CONNECT YOUR MARKET</p>
            <h1 id="hero-title" class="hero-title home-reveal">좋은 상품을,<br>나만의 <em>채널로.</em></h1>
            <p class="hero-description home-reveal">상품과 사람, 그리고 새로운 기회.<br>미구마켓에서 나만의 Shop을 시작하세요.</p>
            <button type="button" class="home-text-link home-reveal" data-home-go="about">미구마켓 알아보기 <span class="home-link-arrow" aria-hidden="true">↗</span></button>
        </div>
        <div class="hero-bottom">
            <p>PRODUCTS. PEOPLE. POSSIBILITIES.</p>
            <button class="home-scroll-cue" type="button" data-home-go="about"><span>SCROLL TO EXPLORE</span><span class="scroll-cue-arrow" aria-hidden="true">↓</span></button>
        </div>
    </section>

    <section id="about" class="home-panel home-about" data-home-theme="light" aria-labelledby="about-title">
        <div class="home-panel-inner about-inner">
            <div class="about-copy">
                <p class="home-eyebrow home-reveal">ABOUT ME9 MARKET</p>
                <h2 id="about-title" class="home-section-title home-reveal">상품의 가능성에<br>당신의 채널을<br><em>더합니다.</em></h2>
                <p class="home-body-copy home-reveal">좋은 상품을 가진 판매자와<br>자신만의 고객을 만나는 Shop 채널.<br>미구마켓은 그 사이를 연결합니다.</p>
                <a class="home-text-link home-reveal" href="{{ route('front.service') }}">서비스 자세히 보기 <span class="home-link-arrow" aria-hidden="true">↗</span></a>
            </div>
            <div class="connection-art home-reveal" role="img" aria-label="미구마켓을 중심으로 상품, Shop 채널, 고객이 연결됩니다">
                <div class="orbit orbit-outer"></div>
                <div class="orbit orbit-inner"></div>
                <span class="orbit-dot"></span>
                <div class="connection-center"><span>Me9</span><small>market</small></div>
                <div class="connection-node node-product"><span>좋은 상품</span><small>PRODUCT</small></div>
                <div class="connection-node node-shop"><span>나만의 채널</span><small>MY SHOP</small></div>
                <div class="connection-node node-people"><span>함께하는 고객</span><small>PEOPLE</small></div>
                <span class="connection-caption">MORE CONNECTIONS,<br>MORE POSSIBILITIES.</span>
            </div>
        </div>
        <button class="panel-next" type="button" data-home-go="how" aria-label="이용방법 구역으로 이동">이용방법 <span aria-hidden="true">↓</span></button>
    </section>

    <section id="how" class="home-panel home-how" data-home-theme="light" aria-labelledby="how-title">
        <div class="home-panel-inner how-inner">
            <div class="how-copy">
                <p class="home-eyebrow home-reveal">HOW TO ME9</p>
                <h2 id="how-title" class="home-section-title home-reveal">나만의 시작을,<br><em>더 간편하게.</em></h2>
                <p class="home-body-copy home-reveal">회원으로 미구마켓을 이용하고,<br>채널 관리자로 나만의 Shop을 운영하세요.</p>
                <div class="guide-tabs home-reveal" role="tablist" aria-label="이용방법 선택">
                    <button type="button" role="tab" id="guide-member-tab" aria-selected="true" aria-controls="guide-member" data-guide="member">마이페이지 <span aria-hidden="true">↗</span></button>
                    <button type="button" role="tab" id="guide-shop-tab" aria-selected="false" aria-controls="guide-shop" tabindex="-1" data-guide="shop">Shop 만들기 <span aria-hidden="true">↗</span></button>
                </div>
            </div>
            <div class="guide-content home-reveal">
                <div id="guide-member" role="tabpanel" aria-labelledby="guide-member-tab" tabindex="0">
                    <p class="guide-heading">내 쇼핑을 한곳에서</p>
                    <ol class="guide-steps">
                        <li><span class="guide-step-number">01</span><div><h3>회원가입하고 로그인하세요</h3><p>일반 회원가입 또는 제공되는 간편 로그인으로 시작합니다.</p></div></li>
                        <li><span class="guide-step-number">02</span><div><h3>마이페이지를 열어보세요</h3><p>상단 회원 메뉴에서 마이페이지로 이동할 수 있습니다.</p></div></li>
                        <li><span class="guide-step-number">03</span><div><h3>내 정보와 주문을 관리하세요</h3><p>회원정보, 배송지, 주문내역과 포인트를 확인하세요.</p></div></li>
                    </ol>
                    <a class="home-text-link" href="{{ auth()->check() ? route('mypage.dashboard') : route('front.member.login') }}">마이페이지 시작하기 <span class="home-link-arrow" aria-hidden="true">↗</span></a>
                </div>
                <div id="guide-shop" role="tabpanel" aria-labelledby="guide-shop-tab" tabindex="0" hidden>
                    <p class="guide-heading">상품을 나만의 Shop으로</p>
                    <ol class="guide-steps">
                        <li><span class="guide-step-number">01</span><div><h3>판매자 등록 후 채널관리자에 접속하세요</h3><p>판매자 정보와 채널 운영에 필요한 기본정보를 준비합니다.</p></div></li>
                        <li><span class="guide-step-number">02</span><div><h3>Shop 채널을 등록하세요</h3><p>채널관리자의 Shop 채널관리에서 공개 범위와 운영 정보를 설정합니다.</p></div></li>
                        <li><span class="guide-step-number">03</span><div><h3>상품을 연결하고 채널을 공유하세요</h3><p>판매상품을 구성하고 채널 주소 또는 QR로 고객을 초대하세요.</p></div></li>
                    </ol>
                    <a class="home-text-link" href="{{ route('channel.login') }}">채널관리자 시작하기 <span class="home-link-arrow" aria-hidden="true">↗</span></a>
                </div>
            </div>
        </div>
        <button class="panel-next" type="button" data-home-go="news" aria-label="공지사항 구역으로 이동">새로운 소식 <span aria-hidden="true">↓</span></button>
    </section>

    <section id="news" class="home-panel home-news" data-home-theme="dark" aria-labelledby="news-title">
        <div class="home-panel-inner news-inner">
            <div class="news-top">
                <div class="news-heading">
                    <p class="home-eyebrow home-reveal">NEWS & NOTICE</p>
                    <h2 id="news-title" class="home-section-title home-reveal">미구마켓의<br>새로운 소식.</h2>
                    <a class="home-text-link home-reveal" href="{{ route('cs.notice') }}">공지사항 전체보기 <span class="home-link-arrow" aria-hidden="true">↗</span></a>
                </div>
                <ul class="home-notices home-reveal" aria-label="최신 공지사항">
                    @forelse($notices as $notice)
                        <li><a href="{{ route('cs.notice.view', $notice->id) }}"><span class="notice-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span class="notice-title">{{ $notice->title }}</span><time datetime="{{ $notice->created_at?->toDateString() }}">{{ $notice->created_at?->format('Y.m.d') }}</time><span class="notice-arrow" aria-hidden="true">↗</span></a></li>
                    @empty
                        <li class="notice-empty">등록된 공지사항이 없습니다.</li>
                    @endforelse
                </ul>
            </div>
            <div class="home-entry-row">
                <form class="home-entry-form" action="{{ route('shop.gate') }}" method="GET">
                    <label for="home-channel-code">Shop 입장코드</label>
                    <div class="home-entry-field"><input id="home-channel-code" name="channel" placeholder="전달받은 입장코드를 입력하세요" maxlength="80" required autocomplete="off"><button type="submit">Shop 입장 <span aria-hidden="true">↗</span></button></div>
                </form>
                <a class="order-check-link" href="{{ route('front.nonmember.order_check') }}">비회원 주문조회 <span aria-hidden="true">↗</span></a>
            </div>
            <footer class="home-footer">
                <div class="home-company">
                    <p class="home-footer-name">{{ config('storefront.company_name') ?: 'Me9 market' }}</p>
                    <ul>
                        @foreach(['representative' => '대표자', 'business_number' => '사업자등록번호', 'commerce_number' => '통신판매업신고번호', 'address' => '주소', 'email' => '이메일'] as $key => $label)
                            @if(config('storefront.'.$key))<li><span>{{ $label }}</span> {{ config('storefront.'.$key) }}</li>@endif
                        @endforeach
                    </ul>
                    <p class="home-copyright">© {{ date('Y') }} {{ config('storefront.company_name') ?: 'Me9 market' }}. All rights reserved.</p>
                </div>
                <div class="home-footer-links">
                    @if(config('shop_channel.terms_url'))<a href="{{ config('shop_channel.terms_url') }}" rel="noopener">이용약관</a>@endif
                    @if(config('shop_channel.privacy_url'))<a href="{{ config('shop_channel.privacy_url') }}" rel="noopener">개인정보처리방침</a>@endif
                    <a href="{{ route('cs.contact') }}">제휴·문의</a>
                    <button type="button" data-home-go="main" aria-label="메인 첫 화면으로 이동">BACK TO TOP <span aria-hidden="true">↑</span></button>
                </div>
            </footer>
        </div>
    </section>
</main>
<noscript><p class="home-noscript">화면 전환 효과는 JavaScript를 켜면 이용할 수 있습니다. 아래로 스크롤하여 모든 내용을 확인하실 수 있습니다.</p></noscript>
@endsection
