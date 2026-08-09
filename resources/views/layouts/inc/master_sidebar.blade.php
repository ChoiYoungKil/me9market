<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="con_w">
                <ul class="dep1_wrap">
                    <li class="dep1 icon1_1 {{ Request::is('master/sub01*') ? 'on' : '' }}">
                        <a href="{{ route('master.sub01') }}">정보관리</a>
                    </li>
                    <li class="dep1 icon1_2 arrow {{ Request::is('master/sub02*') ? 'on' : '' }}">
                        <a href="{{ route('master.sub02') }}">서브관리자</a>
                        <ul class="dep2_wrap">
                            <li><a href="#">세부메뉴 001</a></li>
                            <li><a href="#">세부메뉴 002</a></li>
                            <li><a href="#">세부메뉴 003</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_3 arrow {{ Request::is('master/sub03*') ? 'on' : '' }}">
                        <a href="{{ route('master.sub03') }}">발주관리담당</a>
                        <ul class="dep2_wrap">
                            <li class="{{ Request::is('master/sub01') ? 'on' : '' }}"><a
                                    href="{{ route('master.sub01') }}">세부메뉴 001</a></li>
                            <li class="{{ Request::is('master/sub02') ? 'on' : '' }}"><a
                                    href="{{ route('master.sub02') }}">세부메뉴 002</a></li>
                            <li class="{{ Request::is('master/sub03') ? 'on' : '' }}"><a
                                    href="{{ route('master.sub03') }}">세부메뉴 003</a></li>
                            <li><a href="{{ route('master.loading') }}">로딩화면</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_4">
                        <a href="#">포인트관리</a>
                    </li>
                    <li class="dep1 icon1_5">
                        <a href="#">배송비설정</a>
                    </li>
                    <li class="dep1 icon1_6">
                        <a href="#">취소/환불안내</a>
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
                </ul>
            </div>
        </div>
    </div>
</div>