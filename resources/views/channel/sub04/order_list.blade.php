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
                    <div class="ttl">주문목록</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>주문관리</li>
                        <li>주문목록</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
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
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">검색구분 선택</option>
                                                        <option value="1">주문번호</option>
                                                    </select>
                                                    <input type="text" value="" required="required">
                                                </div>
                                                <a id="arrow1" class="btn01 arrow"><span>상세</span></a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tb01 bN arrowbx" data-arrowbx="arrow1">
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
                                            <input class="datepicker w160" type="text" required="required" readonly="">
                                            &nbsp; ~ &nbsp;
                                            <input class="datepicker w160" type="text" required="required" readonly="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주문상태</span></th>
                                        <td colspan="3">
                                            <ul class="chk02">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_1" checked="">
                                                    <label for="chk1_1">결제대기중</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_2">
                                                    <label for="chk1_2">결제완료</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_3">
                                                    <label for="chk1_3">배송대기</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_4">
                                                    <label for="chk1_4">배송중</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_5">
                                                    <label for="chk1_5">구매확정</label>
                                                </li>
                                            </ul>
                                            <ul class="chk02 mt5">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_6">
                                                    <label for="chk1_6">취소요청</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_7">
                                                    <label for="chk1_7">취소완료</label>
                                                </li>
                                            </ul>
                                            <ul class="chk02 mt5">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_8">
                                                    <label for="chk1_8">반품신청</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_9">
                                                    <label for="chk1_9">반품완료</label>
                                                </li>
                                            </ul>
                                            <ul class="chk02 mt5">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_10">
                                                    <label for="chk1_10">교환신청</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_11">
                                                    <label for="chk1_11">교환완료</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주문자</span></th>
                                        <td>
                                            <input type="text" value="" required="required">
                                        </td>
                                        <th class="w160"><span>판매가격 범위</span></th>
                                        <td>
                                            <div class="scope01">
                                                <input type="text" value="" required="required"><span>원</span>
                                                <span class="mid">~</span>
                                                <input type="text" value="" required="required"><span>원</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10">
                            <a href="#">검색</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box1">
                <div class="conbx">
                    <div class="con_w">
                        <div class="tab_bx1">
                            <ul>
                                <li><a href="{{ route('channel.order.list') }}" class="on"><span>주문목록</span></a></li>
                                <li><a href="{{ route('channel.order.cancel_list') }}"><span>취소목록</span></a></li>
                                <li><a href="{{ route('channel.order.return_list') }}"><span>반품목록</span></a></li>
                                <li><a href="{{ route('channel.order.exchange_list') }}"><span>교환목록</span></a></li>
                            </ul>
                        </div>
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $orders->total() }}</strong> 건</div>
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
                                                <td><a href="#" onclick='openOrderModal("pop1_1", @json($order)); return false;' class="fcol4 link">{{ $order->order_no }}</a>
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $order->shop_name }}</td>
                                                <td>{{ $order->status }}</td>
                                                <td>{{ $order->user_name }}</td>
                                                <td>{{ $order->items->first()['product_type'] ?? '자사' }}</td>
                                                <td class="t_l">
                                                    @foreach($order->items as $item)
                                                        <a class="fcol2 link">{{ $item['product_name'] }}</a><br>
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
                                                <td colspan="22" style="text-align: left; padding: 5px 10px; background: #f9f9f9;">
                                                    <strong>[관리]: </strong>
                                                    <a href="#" onclick='openOrderModal("pop1_2_3", @json($order)); return false;'
                                                        class="btn02">배송관리</a>
                                                    <a href="#" onclick='openOrderModal("pop1_2_6", @json($order)); return false;'
                                                        class="btn02">취소요청</a>
                                                    <a href="#" onclick='openOrderModal("pop1_2_4", @json($order)); return false;'
                                                        class="btn02">반품요청</a>
                                                    <a href="#" onclick='openOrderModal("pop1_2_5", @json($order)); return false;'
                                                        class="btn02">교환요청</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="22" class="no_data">등록된 데이터가 없습니다.</td>
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
        <script src="/channel_assets/js/order_management.js"></script>
        <script type="text/javascript">
            $(".btn01.arrow").click(function () {
                var thisId = $(this).attr("id");
                $(this).toggleClass("on");
                $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
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