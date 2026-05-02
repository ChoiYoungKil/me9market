<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="ttl">주문관리</div>
            <ul>
                <li>
                    <a href="{{ route('channel.order.list') }}">
                        <div class="icon icon1">
                            <span class="num">99+</span>
                        </div>
                        <div class="txt">금일주문</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('channel.order.list') }}">
                        <div class="icon icon2">
                            <span class="num">20</span>
                        </div>
                        <div class="txt">결제완료</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('channel.order.list') }}">
                        <div class="icon icon3">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">발주대기건</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('channel.order.cancel_list') }}">
                        <div class="icon icon4">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">취소 요청건</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('channel.order.return_list') }}">
                        <div class="icon icon5">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">반품 요청건</div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 상품등록 -->
        <div class="con2">
            <div class="ttl">상품등록</div>
            <div class="list_bx">
                <div class="list_w">
                    <ul>
                        <li><a href="{{ route('channel.product_own') }}">- 자사상품 수<span>({{ $sidebar_counts['own'] ?? 0 }})</span></a></li>
                        <li><a href="{{ route('channel.product_public') }}">- 공유상품 수<span>({{ $sidebar_counts['public'] ?? 0 }})</span></a></li>
                        <li><a href="{{ route('channel.product_own') }}">- 판매 상품 수<span>({{ $sidebar_counts['selling'] ?? 0 }})</span></a></li>
                        <li><a href="{{ route('channel.shop_list') }}">- 판매채널 수<span>({{ $sidebar_counts['channels'] ?? 0 }})</span></a></li>
                    </ul>
                    <a href="{{ route('channel.product_request') }}" class="add_btn"><span>상품등록</span></a>
                </div>
            </div>
        </div>

        <div class="con3">
            <!-- 메인 관리 메뉴 -->
            <div class="con_w">
                <ul class="dep1_wrap type2">
                    <li class="dep1 {{ request()->routeIs('channel.shop_*') ? 'on' : '' }}">
                        <a href="javascript:void(0);" class="toggle-btn">Shop 채널관리 <span class="arrow">▼</span></a>
                        <ul class="dep2_wrap"
                            style="{{ request()->routeIs('channel.shop_*') ? 'display: block;' : 'display: none;' }}">
                            <li><a href="{{ route('channel.shop_list') }}"
                                    class="{{ (request()->routeIs('channel.shop_list') || request()->routeIs('channel.shop_info') || request()->routeIs('channel.info_update')) ? 'active-link' : '' }}">-
                                    Shop
                                    채널목록</a></li>
                            <li><a href="{{ route('channel.shop_register') }}"
                                    class="{{ request()->routeIs('channel.shop_register') ? 'active-link' : '' }}">-
                                    Shop 채널등록</a></li>
                        </ul>
                    </li>
                    <li class="dep1 {{ request()->routeIs('channel.product_*') ? 'on' : '' }}">
                        <a href="javascript:void(0);" class="toggle-btn">상품관리 <span class="arrow">▼</span></a>
                        <ul class="dep2_wrap"
                            style="{{ request()->routeIs('channel.product_*') ? 'display: block;' : 'display: none;' }}">
                            <li><a href="{{ route('channel.product_own') }}"
                                    class="{{ request()->routeIs('channel.product_own') ? 'active-link' : '' }}">-
                                    자사상품목록</a></li>
                            <li><a href="{{ route('channel.product_public') }}"
                                    class="{{ request()->routeIs('channel.product_public') ? 'active-link' : '' }}">-
                                    공개상품목록</a></li>
                            <li><a href="{{ route('channel.product_partial') }}"
                                    class="{{ request()->routeIs('channel.product_partial') ? 'active-link' : '' }}">-
                                    부분공개상품목록</a></li>
                            <li><a href="{{ route('channel.product_request') }}"
                                    class="{{ request()->routeIs('channel.product_request') ? 'active-link' : '' }}">-
                                    상품등록</a></li>
                        </ul>
                    </li>
                    <li class="dep1">
                        <a href="javascript:void(0);" class="toggle-btn">공동구매 관리 <span class="arrow">▼</span></a>
                        <ul class="dep2_wrap" style="display: none;">
                            <li><a href="#">- 공동구매 상품목록</a></li>
                            <li><a href="#">- 공동구매 상품등록</a></li>
                        </ul>
                    </li>
                    <li class="dep1 {{ request()->routeIs('channel.order.*') ? 'on' : '' }}">
                        <a href="javascript:void(0);" class="toggle-btn">주문관리 <span class="arrow">▼</span></a>
                        <ul class="dep2_wrap"
                            style="{{ request()->routeIs('channel.order.*') ? 'display: block;' : 'display: none;' }}">
                            <li><a href="{{ route('channel.order.list') }}"
                                    class="{{ request()->routeIs('channel.order.list') ? 'active-link' : '' }}">- 일반
                                    주문목록</a></li>
                            <li><a href="#">- 공동구매 주문목록</a></li>
                        </ul>
                    </li>
                    <li class="dep1">
                        <a href="{{ route('channel.settlement.list') }}">정산관리</a>
                    </li>
                </ul>
            </div>

            <!-- 환경설정 -->
            <div class="con_w">
                <div class="c_ttl">환경설정</div>
                <ul class="dep1_wrap type2">
                    <li class="dep1">
                        <a href="{{ route('channel.info.management') }}">정보관리</a>
                    </li>
                    <li class="dep1">
                        <a href="{{ route('channel.sub_accounts.list') }}">서브관리자 관리</a>
                    </li>
                    <li class="dep1">
                        <a href="{{ route('channel.order.manager') }}">발주담당자 관리</a>
                    </li>
                    <li class="dep1">
                        <a href="{{ route('channel.point.list') }}">포인트 관리</a>
                    </li>
                    <li class="dep1">
                        <a href="{{ route('channel.delivery.list') }}">배송비 설정</a>
                    </li>
                    <li class="dep1">
                        <a href="{{ route('channel.refund.list') }}">취소/환불 안내</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    #l_menu .con3 .con_w .dep1>a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-right: 15px;
    }

    #l_menu .con3 .con_w .dep1 .arrow {
        font-size: 10px;
        transition: transform 0.3s;
        color: #999;
    }

    #l_menu .con3 .con_w .dep1.on .arrow {
        transform: rotate(180deg);
    }

    #l_menu .con3 .con_w .dep2_wrap {
        margin-bottom: 20px;
    }

    #l_menu .active-link {
        color: #3470f7 !important;
        font-weight: bold !important;
    }
</style>

<script>
    $(function () {
        $("#l_menu .toggle-btn").click(function (e) {
            e.preventDefault();
            var $li = $(this).parent(".dep1");
            var $submenu = $(this).next(".dep2_wrap");

            if ($submenu.length > 0) {
                $li.toggleClass("on");
                $submenu.stop().slideToggle(300);
            }
        });
    });
</script>