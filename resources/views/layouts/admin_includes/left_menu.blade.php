<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="con_w">
                <ul class="dep1_wrap">
                    <!-- Dashboard -->
                    <li class="dep1 icon1_1 {{ Request::is('admin/dashboard') ? 'on' : '' }}">
                        <a href="{{ url('admin/dashboard') }}">대시보드 (Dashboard)</a>
                    </li>

                    <!-- Settings -->
                    <li class="dep1 icon1_2 arrow {{ Request::is('admin/update-admin-*') ? 'on' : '' }}">
                        <a href="javascript:void(0);">설정 (Settings)</a>
                        <ul class="dep2_wrap">
                            <li class="{{ Request::is('admin/update-admin-password') ? 'on' : '' }}">
                                <a href="{{ url('admin/update-admin-password') }}">비밀번호 변경</a>
                            </li>
                            <li class="{{ Request::is('admin/update-admin-details') ? 'on' : '' }}">
                                <a href="{{ url('admin/update-admin-details') }}">내 정보 수정</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Catalogue Management -->
                    <li class="dep1 icon1_3 arrow {{ (Request::is('admin/sections') || Request::is('admin/categories*') || Request::is('admin/products*') || Request::is('admin/brands*') || Request::is('admin/coupons*')) ? 'on' : '' }}">
                        <a href="javascript:void(0);">상품 관리 (Catalogue)</a>
                        <ul class="dep2_wrap">
                            <li class="{{ Request::is('admin/sections') ? 'on' : '' }}">
                                <a href="{{ url('admin/sections') }}">섹션 (Sections)</a>
                            </li>
                            <li class="{{ Request::is('admin/categories') ? 'on' : '' }}">
                                <a href="{{ url('admin/categories') }}">카테고리 (Categories)</a>
                            </li>
                            <li class="{{ Request::is('admin/brands') ? 'on' : '' }}">
                                <a href="{{ url('admin/brands') }}">브랜드 (Brands)</a>
                            </li>
                            <li class="{{ Request::is('admin/products') ? 'on' : '' }}">
                                <a href="{{ url('admin/products') }}">상품 (Products)</a>
                            </li>
                            <li class="{{ Request::is('admin/coupons') ? 'on' : '' }}">
                                <a href="{{ url('admin/coupons') }}">쿠폰 (Coupons)</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Order Management -->
                    <li class="dep1 icon1_4 {{ Request::is('admin/orders*') ? 'on' : '' }}">
                        <a href="{{ url('admin/orders') }}">주문 관리 (Orders)</a>
                    </li>

                    <li class="dep1 icon1_4 {{ Request::is('admin/channel-points*') ? 'on' : '' }}">
                        <a href="{{ route('admin.channel_points.index') }}">포인트 판매/사용 내역</a>
                    </li>

                    <!-- User Management -->
                    <li class="dep1 icon1_5 arrow {{ (Request::is('admin/users') || Request::is('admin/subscribers') || Request::is('admin/admins*')) ? 'on' : '' }}">
                        <a href="javascript:void(0);">회원 관리 (Users)</a>
                       <ul class="dep2_wrap">
                            <li class="{{ Request::is('admin/users') ? 'on' : '' }}">
                                <a href="{{ url('admin/users') }}">회원 (Users)</a>
                            </li>
                            <li class="{{ Request::is('admin/subscribers') ? 'on' : '' }}">
                                <a href="{{ url('admin/subscribers') }}">구독자 (Subscribers)</a>
                            </li>
                             <li class="{{ Request::is('admin/admins*') ? 'on' : '' }}">
                                <a href="{{ url('admin/admins') }}">관리자/벤더 (Admins)</a>
                            </li>
                        </ul>
                    </li>

                     <!-- Banners Management -->
                     <li class="dep1 icon1_6 {{ Request::is('admin/banners*') ? 'on' : '' }}">
                        <a href="{{ url('admin/banners') }}">배너 관리 (Banners)</a>
                    </li>
                </ul>
            </div>
            
             <div class="con_w">
                <div class="c_ttl">바로가기 메뉴</div>
                <ul class="dep1_wrap type2">
                    <li class="dep1 icon2_1">
                         <a href="{{ url('/') }}" target="_blank">쇼핑몰 메인</a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
