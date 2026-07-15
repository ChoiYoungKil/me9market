<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="con_w">
                <ul class="dep1_wrap">
                    <li class="dep1 icon1_1">
                        <a href="{{ url('admin/dashboard') }}">대시보드</a>
                    </li>
                    <li
                        class="dep1 icon1_2 arrow @if(Session::get('page') == 'users' || Session::get('page') == 'admins' || strpos(Session::get('page'), 'view_') !== false || Session::get('page') == 'add_edit_user' || Session::get('page') == 'add_edit_admin') on @endif">
                        <a href="#">회원관리</a>
                        <ul class="dep2_wrap">
                            <li @if(Session::get('page') == 'users' || Session::get('page') == 'add_edit_user') class="on"
                            @endif><a href="{{ url('admin/users') }}">회원 리스트</a></li>
                            <li @if(Session::get('page') == 'admins' || strpos(Session::get('page'), 'view_') !== false || Session::get('page') == 'add_edit_admin') class="on" @endif><a
                                    href="{{ url('admin/admins') }}">관리자/판매자 리스트</a></li>
                        </ul>
                    </li>
                    <li
                        class="dep1 icon1_3 arrow @if(in_array(Session::get('page'), ['products', 'categories', 'sections', 'brands', 'filters', 'attributes'])) on @endif">
                        <a href="#">상품관리</a>
                        <ul class="dep2_wrap">
                            <li @if(Session::get('page') == 'categories') class="on" @endif><a
                                    href="{{ url('admin/categories') }}">분류관리</a></li>
                            <li @if(Session::get('page') == 'products') class="on" @endif><a
                                    href="{{ url('admin/products') }}">상품 리스트</a></li>
                        </ul>
                    </li>
                    <li
                        class="dep1 icon1_4 arrow @if(in_array(Session::get('page'), ['notices', 'faqs', 'contacts'])) on @endif">
                        <a href="#">고객센터</a>
                        <ul class="dep2_wrap">
                            <li @if(Session::get('page') == 'notices') class="on" @endif><a
                                    href="{{ url('admin/notices') }}">공지사항</a></li>
                            <li @if(Session::get('page') == 'faqs') class="on" @endif><a
                                    href="{{ url('admin/faqs') }}">자주묻는질문</a></li>
                            <li @if(Session::get('page') == 'contacts') class="on" @endif><a
                                    href="{{ url('admin/contacts') }}">제휴/문의</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_5 arrow">
                        <a href="#">발주관리담당</a>
                        <ul class="dep2_wrap">
                            <li class="on"><a href="#">세부메뉴 001</a></li> <!-- on -->
                            <li><a href="#">세부메뉴 002</a></li>
                            <li><a href="#">세부메뉴 003</a></li>
                            <li><a href="#">로딩화면</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_5">
                        <a href="#">포인트관리</a>
                    </li>
                    <li class="dep1 icon1_6">
                        <a href="#">배송비설정</a>
                    </li>
                    <li class="dep1 icon1_6">
                        <a href="#">취소/환불안내</a>
                    </li>
                    <li class="dep1 icon1_5 @if(Session::get('page') == 'settlements') on @endif">
                        <a href="{{ route('admin.settlements.index') }}">정산관리</a>
                    </li>
                </ul>
            </div>
            <div class="con_w">
                <div class="c_ttl">바로가기 메뉴</div>
                <ul class="dep1_wrap type2">
                    <li class="dep1 icon2_1">
                        <a href="#">바로가기 메뉴</a>
                    </li>
                    <li class="dep1">
                        <a href="#">바로가기 메뉴</a>
                    </li>
                    <li class="dep1">
                        <a href="#">바로가기 메뉴</a>
                    </li>
                    <li class="dep1">
                        <a href="#">바로가기 메뉴</a>
                    </li>
                    <li class="dep1">
                        <a href="#">바로가기 메뉴</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
