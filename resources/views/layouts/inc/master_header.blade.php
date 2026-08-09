<header id="header">
    <a href="{{ route('master.index') }}" class="logo">Me9 market</a>

    <div class="t_menu">
        <ul>
            <li><a href="{{ url()->current() }}" class="on">환경설정</a></li>
            <li><a href="{{ url()->current() }}">회원관리</a></li>
            <li><a href="{{ url()->current() }}">판매사이트관리</a></li>
            <li><a href="{{ url()->current() }}">대분류</a></li>
            <li><a href="{{ url()->current() }}">대분류</a></li>
        </ul>
    </div>

    <div class="r_bx">
        <div class="name">[최고관리자] {{ Auth::user()->name ?? '회원명' }}</div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <a href="{{ url()->current() }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="btn icon1">logout</a>
    </div>
</header><!-- //header -->