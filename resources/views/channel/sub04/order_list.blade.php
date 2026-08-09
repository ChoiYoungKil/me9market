@extends('layouts.channel')

@php
    $dep1_id = "04";
@endphp

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">{{ $orderPageTitle ?? '주문목록' }}</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>주문관리</li>
                        <li>{{ $orderPageTitle ?? '주문목록' }}</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @php
                            $orderTab = $orderPageType ?? match(true) {
                                request()->routeIs('channel.order.joint_list') => 'joint',
                                request()->routeIs('channel.order.cancel_list') => 'cancel',
                                request()->routeIs('channel.order.return_list') => 'return',
                                request()->routeIs('channel.order.exchange_list') => 'exchange',
                                default => 'list',
                            };
                            $searchAction = match($orderTab) {
                                'joint' => route('channel.order.joint_list'),
                                'cancel' => route('channel.order.cancel_list'),
                                'return' => route('channel.order.return_list'),
                                'exchange' => route('channel.order.exchange_list'),
                                default => route('channel.order.list'),
                            };
                            $isDetailSearchOpen = request()->input('detail_open') === '1'
                                || !empty($filters['date_from'] ?? '')
                                || !empty($filters['date_to'] ?? '')
                                || !empty($filters['order_statuses'] ?? [])
                                || !empty($filters['buyer'] ?? '')
                                || !empty($filters['price_min'] ?? '')
                                || !empty($filters['price_max'] ?? '')
                                || !empty($filters['order_type'] ?? '');
                        @endphp
                        <form method="GET" action="{{ $searchAction }}" id="orderSearchForm">
                        <input type="hidden" name="detail_open" id="detail_open" value="{{ $isDetailSearchOpen ? '1' : '0' }}">
                        <input type="hidden" name="status_filter" id="status_filter" value="{{ implode(',', $filters['order_statuses'] ?? []) }}">
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>검색구분</span></th>
                                        <td colspan="3">
                                            <div class="r_btn_w">
                                                <div class="search_w01">
                                                    <select name="search_type" class="w160">
                                                        <option value="all" {{ ($filters['search_type'] ?? 'all') === 'all' ? 'selected' : '' }}>전체</option>
                                                        <option value="order_no" {{ ($filters['search_type'] ?? '') === 'order_no' ? 'selected' : '' }}>주문번호</option>
                                                        <option value="product_name" {{ ($filters['search_type'] ?? '') === 'product_name' ? 'selected' : '' }}>상품명</option>
                                                        <option value="product_code" {{ ($filters['search_type'] ?? '') === 'product_code' ? 'selected' : '' }}>상품코드</option>
                                                        <option value="tracking_number" {{ ($filters['search_type'] ?? '') === 'tracking_number' ? 'selected' : '' }}>송장번호</option>
                                                    </select>
                                                    <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="검색어를 입력해 주세요.">
                                                </div>
                                                <a id="arrow1" class="btn01 arrow {{ $isDetailSearchOpen ? 'on' : '' }}"><span>상세</span></a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tb01 bN arrowbx" data-arrowbx="arrow1" style="{{ $isDetailSearchOpen ? 'display:block;' : '' }}">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>주문기간</span></th>
                                        <td colspan="3">
                                            <input class="datepicker w160" type="text" name="date_from" value="{{ $filters['date_from'] ?? '' }}" readonly="">
                                            &nbsp; ~ &nbsp;
                                            <input class="datepicker w160" type="text" name="date_to" value="{{ $filters['date_to'] ?? '' }}" readonly="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주문상태</span></th>
                                        <td colspan="3">
                                            <ul class="chk02">
                                                @foreach($orderStatusOptions ?? [] as $statusValue => $statusLabel)
                                                    <li>
                                                        <input type="checkbox" name="order_statuses[]" class="order-status-check" value="{{ $statusValue }}" id="order_status_{{ $statusValue }}" {{ in_array($statusValue, $filters['order_statuses'] ?? [], true) ? 'checked' : '' }}>
                                                        <label for="order_status_{{ $statusValue }}">{{ $statusLabel }}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주문자</span></th>
                                        <td>
                                            <input type="text" name="buyer" value="{{ $filters['buyer'] ?? '' }}" placeholder="주문자명, 이메일, 연락처">
                                        </td>
                                        <th class="w160"><span>판매가격 범위</span></th>
                                        <td>
                                            <div class="scope01">
                                                <input type="text" name="price_min" value="{{ $filters['price_min'] ?? '' }}"><span>원</span>
                                                <span class="mid">~</span>
                                                <input type="text" name="price_max" value="{{ $filters['price_max'] ?? '' }}"><span>원</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주문 유형</span></th>
                                        <td colspan="3">
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="order_type" id="order_type_all" value="all" {{ ($filters['order_type'] ?? '') === '' ? 'checked' : '' }}>
                                                    <label for="order_type_all">전체</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="order_type" id="order_type_normal" value="normal" {{ ($filters['order_type'] ?? '') === 'normal' ? 'checked' : '' }}>
                                                    <label for="order_type_normal">일반</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="order_type" id="order_type_joint" value="joint" {{ ($filters['order_type'] ?? '') === 'joint' ? 'checked' : '' }}>
                                                    <label for="order_type_joint">공동구매</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10 order-search-actions search-actions">
                            <button type="submit" class="type2 order-search-submit">검색</button>
                            <a href="{{ $searchAction }}" class="col5">초기화</a>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="box box1">
                <div class="conbx">
                    <div class="con_w">
                        <div class="tab_bx1 order-list-tabs">
                            <ul>
                                <li><a href="{{ route('channel.order.list') }}" class="{{ in_array($orderTab, ['list', 'joint'], true) ? 'on active' : '' }}" @if(in_array($orderTab, ['list', 'joint'], true)) aria-current="page" @endif><span>주문목록</span></a></li>
                                <li><a href="{{ route('channel.order.cancel_list') }}" class="{{ $orderTab === 'cancel' ? 'on active' : '' }}" @if($orderTab === 'cancel') aria-current="page" @endif><span>취소목록</span></a></li>
                                <li><a href="{{ route('channel.order.return_list') }}" class="{{ $orderTab === 'return' ? 'on active' : '' }}" @if($orderTab === 'return') aria-current="page" @endif><span>반품목록</span></a></li>
                                <li><a href="{{ route('channel.order.exchange_list') }}" class="{{ $orderTab === 'exchange' ? 'on active' : '' }}" @if($orderTab === 'exchange') aria-current="page" @endif><span>교환목록</span></a></li>
                            </ul>
                        </div>
                        <div class="list_top1 channel-list-top">
                            <div class="count">총 <strong>{{ $orders->total() }}</strong> 건</div>
                            <div class="right_bx list-top-actions">
                                <select id="perPageSelect" class="w160">
                                    @foreach([20, 40, 60, 80, 100] as $perPageOption)
                                        <option value="{{ $perPageOption }}" {{ (int)($filters['per_page'] ?? 20) === $perPageOption ? 'selected' : '' }}>{{ $perPageOption }}개씩 보기</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="170px">
                                    <col width="120px">
                                    <col width="80px">
                                    <col width="150px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="300px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>주문일시</th>
                                        <th>주문번호</th>
                                        <th>순번</th>
                                        <th>Shop채널</th>
                                        <th>주문상태</th>
                                        <th>주문자</th>
                                        <th>주문 유형</th>
                                        <th>상품 유형</th>
                                        <th>상품명</th>
                                        <th>상품코드</th>
                                        <th>옵션</th>
                                        <th>주문수량</th>
                                        <th>취소수량</th>
                                        <th>반품수량</th>
                                        <th>교환수량</th>
                                        <th>판매금액</th>
                                        <th>상품금액</th>
                                        <th>이익금</th>
                                        <th>판매이익</th>
                                        <th>배송비</th>
                                        <th>사용포인트</th>
                                        <th>결제금액</th>
                                        <th>적립포인트</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($orders->count() > 0)
                                        @foreach($orders as $order)
                                            <tr>
                                                <td>{{ $order->created_at }}</td>
                                                <td><a href="{{ url()->current() }}" onclick='openOrderModal("pop1_1", @json($order)); return false;' class="fcol4 link">{{ $order->order_no }}</a>
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $order->shop_name }}</td>
                                                <td>{{ $order->status }}</td>
                                                <td>{{ $order->user_name }}</td>
                                                <td>
                                                    @foreach($order->items as $item)
                                                        {{ $item['order_type_label'] }}<br>
                                                    @endforeach
                                                </td>
                                                <td>{{ $order->items->first()['product_type'] ?? '자사' }}</td>
                                                <td class="t_l">
                                                    @foreach($order->items as $item)
                                                        <a class="fcol2 link">{{ $item['product_name'] }}</a><br>
                                                        @if(($item['order_type'] ?? '') === 'joint' && !empty($item['reprice_status']) && ($item['reprice_adjustment_amount'] ?? 0) > 0)
                                                            <span class="fs2 fcol3">공동구매 재결제 예정: {{ number_format($item['original_line_total'] ?? 0) }}원 → {{ number_format($item['repriced_line_total'] ?? $item['line_total']) }}원 / 차액 {{ number_format($item['reprice_adjustment_amount']) }}원</span><br>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach($order->items as $item)
                                                        {{ $item['product_code'] }}<br>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach($order->items as $item)
                                                        {{ $item['option_name'] }}<br>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach($order->items as $item)
                                                        {{ $item['qty'] }}<br>
                                                    @endforeach
                                                </td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td class="t_r">{{ number_format($order->total_sale_price) }} 원</td>
                                                <td class="t_r">{{ number_format($order->total_product_price) }} 원</td>
                                                <td class="t_r">{{ number_format($order->total_profit) }} 원</td>
                                                <td class="t_r">{{ number_format($order->total_selling_profit) }} 원</td>
                                                <td class="t_r">{{ number_format($order->delivery_fee) }} 원</td>
                                                <td class="t_r">{{ number_format($order->used_point) }} p</td>
                                                <td class="t_r"><span
                                                        class="bold fcol4">{{ number_format($order->total_payment_price) }} 원</span>
                                                </td>
                                                <td class="t_r">{{ number_format($order->earned_point) }} p</td>
                                            </tr>
                                            <!-- 액션 버튼 행 -->
                                            <tr>
                                                <td colspan="23" style="text-align: left; padding: 5px 10px; background: #f9f9f9;">
                                                    <strong>[관리]: </strong>
                                                    <a href="{{ url()->current() }}" onclick='openOrderModal("pop1_2_3", @json($order)); return false;'
                                                        class="btn02">배송관리</a>
                                                    <a href="{{ url()->current() }}" onclick='openOrderModal("pop1_2_6", @json($order)); return false;'
                                                        class="btn02">취소요청</a>
                                                    <a href="{{ url()->current() }}" onclick='openOrderModal("pop1_2_4", @json($order)); return false;'
                                                        class="btn02">반품요청</a>
                                                    <a href="{{ url()->current() }}" onclick='openOrderModal("pop1_2_5", @json($order)); return false;'
                                                        class="btn02">교환요청</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="23" class="no_data">등록된 데이터가 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                        <div class="page_bx1">
                            {{ $orders->links() }}
                        </div>

                        <!-- 팝업 -->
                        <!-- 주문번호 클릭시 주문 상세 보기 -->
                        <!-- 주문 정보 팝업 -->
                        @include('channel.sub04.inc.pop_order_info')
                        <!-- 정상 주문 팝업 -->
                        @include('channel.sub04.inc.pop_order_normal')
                        <!-- 취소 주문 팝업 -->
                        @include('channel.sub04.inc.pop_order_cancel')
                        <!-- 반품 주문 팝업 -->
                        @include('channel.sub04.inc.pop_order_return')
                        <!-- 교환 주문 팝업 -->
                        @include('channel.sub04.inc.pop_order_exchange')
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('scripts')
        <script src="/channel_assets/js/order_management.js?v={{ filemtime(public_path('channel_assets/js/order_management.js')) }}"></script>
        <script type="text/javascript">
            $(".btn01.arrow").click(function () {
                var thisId = $(this).attr("id");
                $(this).toggleClass("on");
                $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
                $("#detail_open").val($(this).hasClass("on") ? "1" : "0");
            });

            $("#perPageSelect").change(function () {
                var url = new URL(window.location.href);
                url.searchParams.set("per_page", $(this).val());
                url.searchParams.delete("page");
                window.location.href = url.toString();
            });

            function syncOrderStatusFilter() {
                var statuses = $(".order-status-check:checked").map(function () {
                    return this.value;
                }).get();
                $("#status_filter").val(statuses.join(","));
            }

            $(".order-status-check").on("change", syncOrderStatusFilter);
            syncOrderStatusFilter();

            $("#orderSearchForm").on("submit", function () {
                syncOrderStatusFilter();
                if ($("#detail_open").val() !== "1") {
                    $("#detail_open").prop("disabled", true);
                }
                $(this).find(":input").filter(function () {
                    return !this.value;
                }).prop("disabled", true);
            });

            $(".order-list-tabs a").each(function () {
                var linkPath = this.pathname.replace(/\/$/, "");
                var currentPath = window.location.pathname.replace(/\/$/, "");
                var isCurrent = linkPath === currentPath;
                $(this).toggleClass("on active", isCurrent);
                if (isCurrent) {
                    $(this).attr("aria-current", "page");
                } else {
                    $(this).removeAttr("aria-current");
                }
            });

            /* 팝업 */
            $(".pop_btn").click(function () {
                var popId = $(this).attr("data-pop");
                if (popId == "pop1") {
                    var thisImg = $(this).children("img").clone();
                    $(".popup_bx[data-id='" + popId + "']").find(".img_bx").html(thisImg);
                    $(".popup_bx[data-id='" + popId + "']").find(".img_bx").children("img").css({ "max-width": "100%", "width": "auto", "margin": "0 auto", "display": "block" });
                }
                $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

                return false;
            });
            $(".popup_bx .close_btn").click(function () {
                $(this).parents(".popup_bx").stop().fadeOut(300);

                return false;
            });

            /* 달력 */
            $(".datepicker").datepicker({
                dateFormat: 'yy-mm-dd', //달력 날짜 형태
                showOtherMonths: true, //빈 공간에 현재월의 앞뒤월의 날짜를 표시
                showMonthAfterYear: true, // 월- 년 순서가아닌 년도 - 월 순서
                changeYear: true, //option값 년 선택 가능
                changeMonth: true, //option값  월 선택 가능                      
                yearSuffix: "년", //달력의 년도 부분 뒤 텍스트
                monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 텍스트
                monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 Tooltip
                dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'], //달력의 요일 텍스트
                dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'], //달력의 요일 Tooltip
                minDate: "-5y", //최소 선택일자(-1D:하루전, -1M:한달전, -1Y:일년전)
                maxDate: "+5y", //최대 선택일자(+1D:하루후, -1M:한달후, -1Y:일년후)  
            });
        </script>
    @endpush
