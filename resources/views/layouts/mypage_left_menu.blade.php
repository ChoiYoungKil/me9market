<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="ttl on">주문목록</div> <!-- 알림: on -->
            <ul>
                <li>
                    <a href="{{ url('/mypage/order/view') }}">
                        <div class="icon icon1">
                            <span class="num">99+</span>
                        </div>
                        <div class="txt">결제완료</div>
                    </a>
                </li>
                <li>
                    <a href="{{ url()->current() }}">
                        <div class="icon icon2">
                            <span class="num">20</span>
                        </div>
                        <div class="txt">배송대기중</div>
                    </a>
                </li>
                <li>
                    <a href="{{ url()->current() }}">
                        <div class="icon icon3">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">배송중</div>
                    </a>
                </li>
                <li>
                    <a href="{{ url()->current() }}">
                        <div class="icon icon4">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">구매확정</div>
                    </a>
                </li>
                <li>
                    <a href="{{ url()->current() }}">
                        <div class="icon icon5">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">취소요청</div>
                    </a>
                </li>
                <li>
                    <a href="{{ url()->current() }}">
                        <div class="icon icon6">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">반품신청</div>
                    </a>
                </li>
            </ul>
        </div>
        <div class="con2">
            <div class="con_w dep02">
                <div class="c_ttl">주요메뉴</div>
                <ul class="dep1_wrap">
                    <li class="dep1 icon1_1 arrow">
                        <!-- on -->
                        <a href="{{ url()->current() }}">정보관리</a>
                        <ul class="dep2_wrap">
                            <li><a href="{{ url('/mypage/profile/edit') }}">회원 정보 수정</a></li> <!-- on -->
                            <li><a href="{{ url('/mypage/delivery') }}">배송지 설정</a></li>
                            <li><a href="{{ url('/mypage/withdraw') }}">회원 탈퇴 신청</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_2">
                        <a href="{{ url()->current() }}">방문한 채널</a>
                    </li>
                    <li class="dep1 icon1_3">
                        <a href="{{ url()->current() }}">포인트관리</a>
                    </li>
                    <li class="dep1 icon1_4">
                        <a href="{{ url()->current() }}">장바구니 목록</a>
                    </li>
                    <li class="dep1 icon1_5">
                        <a href="{{ url()->current() }}">찜한 상품 목록</a>
                    </li>
                </ul>
            </div>
            <div class="con_w dep03">
                <div class="c_ttl">Me9 Market</div>
                <ul class="dep1_wrap">
                    <li class="dep1 icon2_1">
                        <a href="{{ url()->current() }}">서비스 안내</a>
                    </li>
                    <li class="dep1 icon2_2">
                        <a href="{{ url()->current() }}">주요기능</a>
                    </li>
                    <li class="dep1 icon2_3">
                        <a href="{{ url()->current() }}">가입안내</a>
                    </li>
                    <li class="dep1 icon2_4 arrow">
                        <a href="{{ url()->current() }}">고객센터</a>
                        <ul class="dep2_wrap">
                            <li><a href="{{ url()->current() }}">공지사항</a></li>
                            <li><a href="{{ url()->current() }}">자주묻는질문</a></li>
                            <li><a href="{{ url()->current() }}">제휴문의</a></li>
                            <li><a href="{{ url()->current() }}">주문조회</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="con_w m_show">
                <div class="c_ttl">회원명</div>
                <ul>
                    <li class="icon1"><a href="{{ url()->current() }}">채널관리자</a></li>
                    <li class="icon2"><a href="{{ url()->current() }}">마이페이지</a></li>
                    <li class="icon3"><a href="{{ url()->current() }}">로그아웃</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Left Menu Active Logic from source
</script>