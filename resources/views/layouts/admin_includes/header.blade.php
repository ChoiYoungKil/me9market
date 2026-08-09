<header id="header">
    <a href="{{ route('admin.dashboard') }}" class="logo">Me9 market</a>

    <div class="t_menu">
        <ul>
            <li><a href="javascript:void(0);" class="on">환경설정</a></li>
            <li><a href="javascript:void(0);">회원관리</a></li>
            <li><a href="javascript:void(0);">판매사이트관리</a></li>
            <li><a href="javascript:void(0);">대분류</a></li>
            <li><a href="javascript:void(0);">대분류</a></li>
        </ul>
    </div>

    <div class="r_bx">
        <div class="name">[{{ Auth::guard('admin')->user()->type ?? '관리자' }}]
            {{ Auth::guard('admin')->user()->name ?? '회원명' }}</div>
        <a href="{{ url('admin/logout') }}" class="btn icon1">logout</a>
    </div>
</header><!-- //header -->