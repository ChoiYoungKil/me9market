<!-- partial:partials/_navbar.html -->
<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="{{ url('admin/dashboard') }}"><img
                src="{{ asset('admin/images/logo.svg') }}" class="mr-2" alt="logo" /></a>
        <a class="navbar-brand brand-logo-mini" href="{{ url('admin/dashboard') }}"><img
                src="{{ asset('admin/images/logo-mini.svg') }}" alt="logo" /></a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav mr-lg-2">
            <li class="nav-item d-none d-lg-block">
                <a href="{{ url('admin/users') }}" class="nav-link"
                    style="display: flex; align-items: center; font-size: 1rem; color: #000; margin-right: 20px;">
                    <i class="icon-head menu-icon" style="margin-right: 5px;"></i>
                    <span class="menu-title">Users</span>
                </a>
            </li>
            <li class="nav-item d-none d-lg-block">
                <a href="{{ url('admin/admins') }}" class="nav-link"
                    style="display: flex; align-items: center; font-size: 1rem; color: #000; margin-right: 20px;">
                    <i class="icon-head menu-icon" style="margin-right: 5px;"></i>
                    <span class="menu-title">Admins/Vendors</span>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" href="{{ url('admin/update-admin-details') }}"
                    data-toggle="dropdown" id="profileDropdown">


                    {{-- 관리자 이미지가 있으면 표시 --}}
                    @if (!empty(Auth::guard('admin')->user()->image)) {{-- 특정 Guard 인스턴스 접근:
                        https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances --}}
                        <img src="{{ url('admin/images/photos/' . Auth::guard('admin')->user()->image) }}" alt="profile">
                        {{-- Accessing Specific Guard Instances:
                        https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances --}}
                    @else
                        <img src="{{ url('admin/images/photos/no-image.gif') }}" alt="profile">
                    @endif


                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <a href="{{ url('admin/update-admin-details') }}" class="dropdown-item">
                        <i class="ti-settings text-primary"></i>
                        Settings
                    </a>
                    <a href="{{ url('admin/logout') }}" class="dropdown-item">
                        <i class="ti-power-off text-primary"></i>
                        Logout
                    </a>
                </div>
            </li>
            <li class="nav-item nav-settings d-none d-lg-flex">
                <a class="nav-link" href="#">
                    <i class="icon-ellipsis"></i>
                </a>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="icon-menu"></span>
        </button>
    </div>
</nav>