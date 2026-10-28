    <header class="home-header">
        <a class="home-logo" href="{{ route('home') }}" aria-label="Me9 market 홈">
            <img class="home-logo-on-dark" src="{{ asset('me9market/images/common/f_logo.png') }}" alt="Me9 market" width="204" height="48">
            <img class="home-logo-on-light" src="{{ asset('me9market/images/common/logo.png') }}" alt="" aria-hidden="true" width="189" height="45">
        </a>
        <button class="home-menu-toggle" type="button" aria-expanded="false" aria-controls="home-menu">
            <span class="sr-only">전체 메뉴 열기</span><span></span><span></span>
        </button>
        <div class="home-menu" id="home-menu">
            <nav class="home-primary-nav" aria-label="주 메뉴">
                <a href="{{ route('front.service') }}" @if(request()->routeIs('front.service')) aria-current="page" @endif>서비스안내</a>
                <a href="{{ route('front.features') }}" @if(request()->routeIs('front.features')) aria-current="page" @endif>주요기능</a>
                <a href="{{ route('front.subscription_info') }}" @if(request()->routeIs('front.subscription_info')) aria-current="page" @endif>가입안내</a>
                <a href="{{ route('cs.notice') }}" @if(request()->routeIs('cs.*')) aria-current="page" @endif>고객센터</a>
            </nav>
            <nav class="home-member-nav" aria-label="회원 메뉴">
                @guest
                    <a href="{{ route('front.member.login') }}">로그인</a>
                    <a class="home-join" href="{{ route('front.member.register.member') }}">회원가입 <span aria-hidden="true">↗</span></a>
                @else
                    <a href="{{ route('mypage.dashboard') }}">마이페이지</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">로그아웃</button>
                    </form>
                @endguest
            </nav>
        </div>
    </header>
