@php
    $mypageUser = Auth::user();
    $statusCounts = collect();

    if ($mypageUser) {
        $statusCounts = \App\Models\OrdersProduct::where('user_id', $mypageUser->id)
            ->get()
            ->map(fn ($item) => $item->normalized_status)
            ->countBy();
    }

    $countFor = function (array $statuses) use ($statusCounts) {
        return collect($statuses)->sum(fn ($status) => (int) ($statusCounts[$status] ?? 0));
    };

    $formatCount = fn ($count) => $count > 99 ? '99+' : (string) $count;

    $paidCount = $countFor([\App\Support\OrderItemStatus::PAID]);
    $readyToShipCount = $countFor([\App\Support\OrderItemStatus::READY_TO_SHIP]);
    $shippingCount = $countFor([\App\Support\OrderItemStatus::SHIPPING, \App\Support\OrderItemStatus::DELIVERED]);
    $confirmedCount = $countFor([\App\Support\OrderItemStatus::CONFIRMED]);
    $cancelRequestCount = $countFor([\App\Support\OrderItemStatus::CANCEL_REQUESTED]);
    $cancelCompletedCount = $countFor([\App\Support\OrderItemStatus::CANCELLED]);
    $returnRequestCount = $countFor([\App\Support\OrderItemStatus::RETURN_REQUESTED]);
    $returnCompletedCount = $countFor([\App\Support\OrderItemStatus::RETURNED]);
    $exchangeRequestCount = $countFor([\App\Support\OrderItemStatus::EXCHANGE_REQUESTED]);
    $exchangeCompletedCount = $countFor([\App\Support\OrderItemStatus::EXCHANGED]);
    $orderTotalCount = $paidCount + $readyToShipCount + $shippingCount + $confirmedCount;
@endphp

<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="ttl on">주문목록 <span class="num"
                    style="background-color: #ef4131; color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px; vertical-align: middle;"
                    aria-label="주문목록 {{ $orderTotalCount }}건">{{ $formatCount($orderTotalCount) }}</span>
            </div> <!-- 알림: on -->
            <ul>
                <li>
                    <a href="{{ route('mypage.order.list', ['status' => 'payment_completed']) }}"
                        class="{{ request('status') == 'payment_completed' ? 'on' : '' }}">
                        <div class="icon icon1">
                            <span class="num" aria-label="결제완료 {{ $paidCount }}건">{{ $formatCount($paidCount) }}</span>
                        </div>
                        <div class="txt">결제완료</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('mypage.order.list', ['status' => 'preparing_shipment']) }}"
                        class="{{ request('status') == 'preparing_shipment' ? 'on' : '' }}">
                        <div class="icon icon2">
                            <span class="num" aria-label="배송대기중 {{ $readyToShipCount }}건">{{ $formatCount($readyToShipCount) }}</span>
                        </div>
                        <div class="txt">배송대기중</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('mypage.order.list', ['status' => 'shipping']) }}"
                        class="{{ request('status') == 'shipping' ? 'on' : '' }}">
                        <div class="icon icon3">
                            <span class="num" aria-label="배송중 {{ $shippingCount }}건">{{ $formatCount($shippingCount) }}</span>
                        </div>
                        <div class="txt">배송중</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('mypage.order.list', ['status' => 'purchase_confirmed']) }}"
                        class="{{ request('status') == 'purchase_confirmed' ? 'on' : '' }}">
                        <div class="icon icon4">
                            <span class="num" aria-label="구매확정 {{ $confirmedCount }}건">{{ $formatCount($confirmedCount) }}</span>
                        </div>
                        <div class="txt">구매확정</div>
                    </a>
                </li>
            </ul>
        </div>

        <div class="con1" style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
            <div class="ttl on cancel">취소목록
            </div>
            <ul>
                <li>
                    <a href="{{ route('mypage.order.list', ['tab' => 'cancel', 'status' => 'cancel_request']) }}"
                        class="{{ request('status') == 'cancel_request' ? 'on' : '' }}">
                        <div class="icon icon5">
                            <span class="num" aria-label="취소요청 {{ $cancelRequestCount }}건">{{ $formatCount($cancelRequestCount) }}</span>
                        </div>
                        <div class="txt">취소요청</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('mypage.order.list', ['tab' => 'cancel', 'status' => 'cancel_completed']) }}"
                        class="{{ request('status') == 'cancel_completed' ? 'on' : '' }}">
                        <div class="icon icon4"> <!-- Reusing purchase confirm icon (Check) for completion -->
                            <span class="num" aria-label="취소완료 {{ $cancelCompletedCount }}건">{{ $formatCount($cancelCompletedCount) }}</span>
                        </div>
                        <div class="txt">취소완료</div>
                    </a>
                </li>
            </ul>
        </div>

        <div class="con1" style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
            <div class="ttl on return">반품목록
            </div>
            <ul>
                <li>
                    <a href="{{ route('mypage.order.list', ['tab' => 'return', 'status' => 'return_request']) }}"
                        class="{{ request('status') == 'return_request' ? 'on' : '' }}">
                        <div class="icon icon5">
                            <span class="num" aria-label="반품신청 {{ $returnRequestCount }}건">{{ $formatCount($returnRequestCount) }}</span>
                        </div>
                        <div class="txt">반품신청</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('mypage.order.list', ['tab' => 'return', 'status' => 'return_completed']) }}"
                        class="{{ request('status') == 'return_completed' ? 'on' : '' }}">
                        <div class="icon icon4"> <!-- Reusing purchase confirm icon (Check) for completion -->
                            <span class="num" aria-label="반품완료 {{ $returnCompletedCount }}건">{{ $formatCount($returnCompletedCount) }}</span>
                        </div>
                        <div class="txt">반품완료</div>
                    </a>
                </li>
            </ul>
        </div>

        <div class="con1" style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
            <div class="ttl on exchange">교환목록
            </div>
            <ul>
                <li>
                    <a href="{{ route('mypage.order.list', ['tab' => 'exchange', 'status' => 'exchange_request']) }}"
                        class="{{ request('status') == 'exchange_request' ? 'on' : '' }}">
                        <div class="icon icon2"> <!-- Reusing 'Waiting Delivery' (Box) icon for Exchange Request -->
                            <span class="num" aria-label="교환신청 {{ $exchangeRequestCount }}건">{{ $formatCount($exchangeRequestCount) }}</span>
                        </div>
                        <div class="txt">교환신청</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('mypage.order.list', ['tab' => 'exchange', 'status' => 'exchange_completed']) }}"
                        class="{{ request('status') == 'exchange_completed' ? 'on' : '' }}">
                        <div class="icon icon4"> <!-- Reusing purchase confirm icon (Check) for completion -->
                            <span class="num" aria-label="교환완료 {{ $exchangeCompletedCount }}건">{{ $formatCount($exchangeCompletedCount) }}</span>
                        </div>
                        <div class="txt">교환완료</div>
                    </a>
                </li>
            </ul>
        </div>

        <div class="con2">
            <div class="con_w dep02">
                <div class="c_ttl">주요메뉴</div>
                <ul class="dep1_wrap">
                    <li class="dep1 icon1_5 {{ request()->routeIs('mypage.dashboard') ? 'on' : '' }}">
                        <a href="{{ route('mypage.dashboard') }}">마이페이지 홈 (대시보드)</a>
                    </li>
                    <li
                        class="dep1 icon1_1 arrow {{ request()->routeIs('mypage.profile', 'mypage.delivery', 'mypage.withdraw') ? 'on' : '' }}">
                        <!-- on -->
                        <a href="#">정보관리</a>
                        <ul class="dep2_wrap">
                            <li class="{{ request()->routeIs('mypage.profile') ? 'on' : '' }}"><a
                                    href="{{ route('mypage.profile') }}">회원 정보 수정</a></li> <!-- on -->
                            <li class="{{ request()->routeIs('mypage.delivery') ? 'on' : '' }}"><a
                                    href="{{ route('mypage.delivery') }}">배송지 설정</a></li>
                            <li class="{{ request()->routeIs('mypage.withdraw') ? 'on' : '' }}"><a
                                    href="{{ route('mypage.withdraw') }}">회원 탈퇴 신청</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_2 {{ request()->routeIs('mypage.visited_channels') ? 'on' : '' }}">
                        <a href="{{ route('mypage.visited_channels') }}">방문한 채널</a>
                    </li>
                    <li class="dep1 icon1_3 arrow {{ request()->routeIs('mypage.point.*') ? 'on' : '' }}">
                        <a href="#">포인트관리</a>
                        <ul class="dep2_wrap">
                            <li class="{{ request()->routeIs('mypage.point.status') ? 'on' : '' }}"><a
                                    href="{{ route('mypage.point.status') }}">포인트 현황</a></li>
                            <li class="{{ request()->routeIs('mypage.point.history') ? 'on' : '' }}"><a
                                    href="{{ route('mypage.point.history') }}">포인트 이력</a></li>
                        </ul>
                    </li>
                    <li class="dep1 icon1_4 {{ request()->routeIs('mypage.cart') ? 'on' : '' }}">
                        <a href="{{ route('mypage.cart') }}">장바구니 목록</a>
                    </li>
                    <li class="dep1 icon1_5 {{ request()->routeIs('mypage.wishlist') ? 'on' : '' }}">
                        <a href="{{ route('mypage.wishlist') }}">찜한 상품 목록</a>
                    </li>
                </ul>
            </div>
            <div class="con_w dep03">
                <div class="c_ttl">Me9 Market</div>
                <ul class="dep1_wrap">
                    <li class="dep1 icon2_1">
                        <a href="#">서비스 안내</a>
                    </li>
                    <li class="dep1 icon2_2">
                        <a href="#">주요기능</a>
                    </li>
                    <li class="dep1 icon2_3">
                        <a href="#">가입안내</a>
                    </li>
                    <li class="dep1 icon2_4 arrow">
                        <a href="#">고객센터</a>
                        <ul class="dep2_wrap">
                            <li><a href="{{ route('cs.notice') }}">공지사항</a></li>
                            <li><a href="{{ route('cs.faq') }}">자주묻는질문</a></li>
                            <li><a href="{{ route('cs.contact') }}">제휴문의</a></li>
                            <li><a href="#">주문조회</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="con_w m_show">
                <div class="c_ttl">{{ Auth::user()->name ?? 'User' }}</div>
                <ul>
                    <li class="icon1"><a href="{{ route('channel.index') }}">채널관리자</a></li>
                    <li class="icon2"><a href="{{ route('mypage.dashboard') }}">마이페이지</a></li>
                    <li class="icon3">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">로그아웃</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
