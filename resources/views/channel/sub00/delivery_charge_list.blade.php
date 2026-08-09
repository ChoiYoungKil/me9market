@extends('layouts.channel')

@php
    $dep1_id = "00";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">배송비설정 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>배송비설정 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1 btn">
                            <div class="count">총 <strong>00</strong> 건</div>
                            <div class="btn_bx">
                                <a href="javascript:void(0);" class="btn01 col5 pop_btn" data-pop="pop1_1">배송비 등록</a>
                            </div>
                        </div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="">
                                    <col width="150px">
                                    <col width="150px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="130px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>배송구분</th>
                                        <th>상태</th>
                                        <th>배송명</th>
                                        <th>지정택배사</th>
                                        <th>배송비 유형</th>
                                        <th>배송비 결제</th>
                                        <th>상품수</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>사용자</td>
                                        <td>사용</td>
                                        <td class="t_l">기본배송비</td>
                                        <td class="t_l">자체배송</td>
                                        <td class="t_l">무료 배송(조건부)</td>
                                        <td>선결제</td>
                                        <td>12</td>
                                        <td>
                                            <a href="javascript:void(0);" class="btn02 col5 pop_btn" data-pop="pop2_1">보기</a>
                                            <a href="javascript:void(0);" class="btn02 col2">복사</a>
                                            <a href="javascript:void(0);" class="btn02 col7 mt5 pop_btn" data-pop="pop3_1">수정</a>
                                            <a href="javascript:void(0);" class="btn02 mt5">삭제</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                        <div class="page_bx1">
                            <a href="javascript:void(0);" class="page_first">first</a>
                            <a href="javascript:void(0);" class="page_prev">prev</a>
                            <a href="javascript:void(0);" class="num on">1</a>
                            <a href="javascript:void(0);" class="num">2</a>
                            <a href="javascript:void(0);" class="num">3</a>
                            <a href="javascript:void(0);" class="num">4</a>
                            <a href="javascript:void(0);" class="num">5</a>
                            <a href="javascript:void(0);" class="page_next">next</a>
                            <a href="javascript:void(0);" class="page_last">last</a>
                        </div>

                        <!-- 팝업 -->
                        <!-- 배송비 등록 팝업 -->
                        @include('channel.sub00.inc.pop_delivery_add')

                        <!-- 보기 팝업 -->
                        @include('channel.sub00.inc.pop_delivery_view')

                        <!-- 수정 팝업 -->
                        @include('channel.sub00.inc.pop_delivery_edit')
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('scripts')
        <script type="text/javascript">
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