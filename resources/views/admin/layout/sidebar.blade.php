{{-- 세션을 사용하여 Skydash 관리자 패널 사이드바 문제 수정 --}}


<!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a @if (Session::get('page') == 'dashboard') style="background: #052CA3 !important; color: #FFF !important"
            @endif class="nav-link" href="{{ url('admin/dashboard') }}">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>



        {{-- 인증된 사용자('admin' 가드 사용)의 타입이 'vendor'인 경우 --}}
        @if (Auth::guard('admin')->user()->type == 'vendor') {{-- 특정 Guard 인스턴스 접근:
            https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances --}}
            <li class="nav-item">
                <a @if (Session::get('page') == 'update_personal_details' || Session::get('page') == 'update_business_details' || Session::get('page') == 'update_bank_details')
                style="background: #052CA3 !important; color: #FFF !important" @endif class="nav-link"
                    data-toggle="collapse" href="#ui-vendors" aria-expanded="false" aria-controls="ui-vendors">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Vendor Details</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-vendors">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'update_personal_details')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/update-vendor-details/personal') }}">Personal Details</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'update_business_details')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/update-vendor-details/business') }}">Business Details</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'update_bank_details')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/update-vendor-details/bank') }}">Bank Details</a></li>
                    </ul>
                </div>
            </li>


            <li class="nav-item">
                <a @if (Session::get('page') == 'sections' || Session::get('page') == 'categories' || Session::get('page') == 'products' || Session::get('page') == 'brands' || Session::get('page') == 'filters' || Session::get('page') == 'coupons') style="background: #052CA3 !important; color: #FFF !important" @endif
                    class="nav-link" data-toggle="collapse" href="#ui-catalogue" aria-expanded="false"
                    aria-controls="ui-catalogue">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Catalogue Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-catalogue">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'products')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/products') }}">Products</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'coupons')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/coupons') }}">Coupons</a></li>
                    </ul>
                </div>
            </li>

            {{-- 인증된 사용자가 'vendor'인 경우, 해당 'vendor'가 추가한 상품의 주문만 표시합니다. ('admin'인 경우 모든 주문 표시) --}}
            <li class="nav-item">
                <a @if (Session::get('page') == 'orders') style="background: #052CA3 !important; color: #FFF !important"
                @endif class="nav-link" data-toggle="collapse" href="#ui-orders" aria-expanded="false"
                    aria-controls="ui-orders">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Orders Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-orders">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'orders')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/orders') }}">Orders</a></li>
                    </ul>
                </div>
            </li>

        @else {{-- 인증된 사용자가 'superadmin', 'admin', 'subadmin'인 경우 --}}
            <li class="nav-item">
                <a @if (Session::get('page') == 'update_admin_password' || Session::get('page') == 'update_admin_details')
                style="background: #052CA3 !important; color: #FFF !important" @endif class="nav-link"
                    data-toggle="collapse" href="#ui-settings" aria-expanded="false" aria-controls="ui-settings">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Settings</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-settings">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'update_admin_password')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/update-admin-password') }}">Update Admin Password</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'update_admin_details')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/update-admin-details') }}">Update Admin Details</a></li>
                    </ul>
                </div>
            </li>



            <li class="nav-item">
                <a @if (Session::get('page') == 'view_admins' || Session::get('page') == 'view_subadmins' || Session::get('page') == 'view_vendors' || Session::get('page') == 'view_all')
                style="background: #052CA3 !important; color: #FFF !important" @endif class="nav-link"
                    data-toggle="collapse" href="#ui-admins" aria-expanded="false" aria-controls="ui-admins">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Admin Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-admins">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">

                        {{-- 라우트 파라미터 {type}은 admins 테이블의 type 컬럼(admin, subadmin, vendor)입니다. 파라미터가 없으면 모든 관리자 표시 --}}
                        <li class="nav-item"> <a @if (Session::get('page') == 'view_admins')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/admins/admin') }}">Admins</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'view_subadmins')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/admins/subadmin') }}">Subadmins</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'view_vendors')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/admins/vendor') }}">Vendors</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'view_all')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/admins') }}">All</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a @if (Session::get('page') == 'sections' || Session::get('page') == 'categories' || Session::get('page') == 'products' || Session::get('page') == 'brands' || Session::get('page') == 'filters' || Session::get('page') == 'coupons') style="background: #052CA3 !important; color: #FFF !important" @endif
                    class="nav-link" data-toggle="collapse" href="#ui-catalogue" aria-expanded="false"
                    aria-controls="ui-catalogue">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Catalogue Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-catalogue">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'sections')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/sections') }}">Sections</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'categories')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/categories') }}">Categories</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'brands')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/brands') }}">Brands</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'products')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/products') }}">Products</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'coupons')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/coupons') }}">Coupons</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'filters')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/filters') }}">Filters</a></li>
                    </ul>
                </div>
            </li>


            <li class="nav-item">
                <a @if (Session::get('page') == 'orders') style="background: #052CA3 !important; color: #FFF !important"
                @endif class="nav-link" data-toggle="collapse" href="#ui-orders" aria-expanded="false"
                    aria-controls="ui-orders">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Orders Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-orders">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'orders')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/orders') }}">Orders</a></li>
                    </ul>
                </div>
            </li>


            <li class="nav-item">
                <a @if (Session::get('page') == 'ratings') style="background: #052CA3 !important; color: #FFF !important"
                @endif class="nav-link" data-toggle="collapse" href="#ui-ratings" aria-expanded="false"
                    aria-controls="ui-ratings">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Ratings Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-ratings">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'ratings')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/ratings') }}">Product Ratings & Reviews</a></li>
                    </ul>
                </div>
            </li>



            <li class="nav-item">
                <a @if (Session::get('page') == 'users' || Session::get('page') == 'subscribers')
                style="background: #052CA3 !important; color: #FFF !important" @endif class="nav-link"
                    data-toggle="collapse" href="#ui-users" aria-expanded="false" aria-controls="ui-users">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Users Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-users">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'users')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/users') }}">Users</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'subscribers')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/subscribers') }}">Subscribers</a></li>
                    </ul>
                </div>
            </li>



            <li class="nav-item">
                <a @if (Session::get('page') == 'banners') style="background: #052CA3 !important; color: #FFF !important"
                @endif class="nav-link" data-toggle="collapse" href="#ui-banners" aria-expanded="false"
                    aria-controls="ui-banners">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Banners Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-banners">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'banners')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/banners') }}">Home Page Banners</a></li>
                    </ul>
                </div>
            </li>


            <li class="nav-item">
                <a @if (Session::get('page') == 'shipping') style="background: #052CA3 !important; color: #FFF !important"
                @endif class="nav-link" data-toggle="collapse" href="#ui-shipping" aria-expanded="false"
                    aria-controls="ui-shipping">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Shipping Management</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-shipping">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'shipping')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ url('admin/shipping-charges') }}">Shipping Charges</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a @if (in_array(Session::get('page'), ['settlements', 'shop-channel-closures', 'channel-points']))
                style="background: #052CA3 !important; color: #FFF !important" @endif class="nav-link"
                    data-toggle="collapse" href="#ui-me9-operations" aria-expanded="false"
                    aria-controls="ui-me9-operations">
                    <i class="icon-layout menu-icon"></i>
                    <span class="menu-title">Me9 운영관리</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-me9-operations">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'settlements')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ route('admin.settlements.index') }}">정산관리</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'channel-points')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ route('admin.channel_points.index') }}">판매자 포인트</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'shop-channel-closures')
                        style="background: #052CA3 !important; color: #FFF !important" @else
                                style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link"
                                href="{{ route('admin.shop_channel_closures.index') }}">운영중지 승인</a></li>
                    </ul>
                </div>
            </li>

        @endif

    </ul>
</nav>
