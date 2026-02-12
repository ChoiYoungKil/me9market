<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "00";
	$dep1_tit = "포인트관리";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">포인트 관리</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>포인트 관리</li>
                            </ul>
                        </div>
                        <div class="conbx">
                            <div class="con_w">
                                <div class="list_top1 btn">
                                    <div class="count">총 포인트 통화량 <strong>7,000,492</strong> P</div>
                                    <div class="btn_bx">
                                        <a href="#" class="btn01 col5 pop_btn" data-pop="pop2_1">포인트 구매</a>
                                    </div>
                                </div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="110px">
                                            <col width="">
                                            <col width="110px">
                                            <col width="">
                                            <col width="110px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>보유 포인트</span></th>
                                                <td>
                                                    1,000,492 P &nbsp;<a href="" class="btn02 pop_btn" data-pop="pop1_1">환급 요청</a>
                                                </td>
                                                <th class="w160"><span>분배 포인트</span></th>
                                                <td>6,000,000 P</td>
                                                <th class="w160"><span>환급 포인트</span></th>
                                                <td>1.000.000 P</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box box1">
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
                                                <th class="w160"><span>날짜</span></th>
                                                <td colspan="3">
                                                    <input class="datepicker w160" type="text" required="required" readonly="">
                                                    &nbsp; ~ &nbsp;
                                                    <input class="datepicker w160" type="text" required="required" readonly="">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>구분</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio1_1" id="radio1_1_1" checked="">
                                                            <label for="radio1_1_1">전체</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1_1" id="radio1_1_2">
                                                            <label for="radio1_1_2">구매</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1_1" id="radio1_1_3">
                                                            <label for="radio1_1_3">회입</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1_1" id="radio1_1_4">
                                                            <label for="radio1_1_4">분배</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1_1" id="radio1_1_5">
                                                            <label for="radio1_1_5">환급</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>상세</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required">
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
                                <div class="list_top1">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                </div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="80px">
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="300px">
                                            <col width="">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>등록일</th>
                                                <th>구매</th>
                                                <th>포인트</th>
                                                <th>내역</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>00</td>
                                                <td>2025-00-00</td>
                                                <td>구매</td>
                                                <td class="t_r"><span class="fcol5">+8,000.000</span></td>
                                                <td>포인트 구매</td>
                                            </tr>
                                            <tr>
                                                <td>00</td>
                                                <td>2025-00-00</td>
                                                <td>분배</td>
                                                <td class="t_r"><span class="fcol3">-6,000,000</span></td>
                                                <td>포인트 분배</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                
                                <div class="page_bx1">
                                    <a href="#" class="page_first">first</a>
                                    <a href="#" class="page_prev">prev</a>
                                    <a href="#" class="num on">1</a>
                                    <a href="#" class="num">2</a>
                                    <a href="#" class="num">3</a>
                                    <a href="#" class="num">4</a>
                                    <a href="#" class="num">5</a>
                                    <a href="#" class="page_next">next</a>
                                    <a href="#" class="page_last">last</a>
                                </div>
                                
                                <!-- 팝업 -->
                                <!-- 포인트 환급 팝업 -->
                                <div class="popup_bx" data-id="pop1_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w800">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">포인트 환급</div>
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
                                                                        <th class="w160"><span>환급 가이드</span></th>
                                                                        <td colspan="3">환급 가이드 내용</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>보유 포인트</span></th>
                                                                        <td colspan="3">2,000,492 P</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>환급 포인트<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                            <input type="text" value="" required="required">
                                                                            <p class="mt10 fcol3">(포인트는 1,000P 단위로 환급할 수 있습니다. )</p>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>메모</span></th>
                                                                        <td colspan="3">
                                                                            <textarea value="" required="required"></textarea>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="#">환급 요청</a>
                                                    <a href="#" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 포인트 구매 팝업 -->
                                <div class="popup_bx" data-id="pop2_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w800">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">포인트 구매</div>
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
                                                                        <th class="w160"><span>구매 가이드</span></th>
                                                                        <td colspan="3">구매 가이드 내용</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>포인트</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01 disb">
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio1" id="pop2_1_radio1_1" checked="">
                                                                                    <label for="pop2_1_radio1_1">
                                                                                        1,000,000 P ( \ 1,000,000 )
                                                                                    </label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio1" id="pop2_1_radio1_2">
                                                                                    <label for="pop2_1_radio1_2">
                                                                                        500,000 P ( \ 500,000 )
                                                                                    </label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio1" id="pop2_1_radio1_3">
                                                                                    <label for="pop2_1_radio1_3">
                                                                                        300,000 P ( \ 300,000 )
                                                                                    </label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio1" id="pop2_1_radio1_4">
                                                                                    <label for="pop2_1_radio1_4">
                                                                                        100,000 P ( \ 100,000 )
                                                                                    </label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio1" id="pop2_1_radio1_5">
                                                                                    <label for="pop2_1_radio1_5">
                                                                                        직접 입력 &nbsp;
                                                                                        <input class="w160" type="text" value="" required="required">
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>결제 비용</span></th>
                                                                        <td colspan="3">\ 8,000,000</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>결제 방법</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio2" id="pop2_1_radio2_1" checked="">
                                                                                    <label for="pop2_1_radio2_1">카드 결제</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop2_1_radio2" id="pop2_1_radio2_2">
                                                                                    <label for="pop2_1_radio2_2">실시간 계좌이체</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="#">결제 하기</a>
                                                    <a href="#" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            /* 팝업 */
            $(".pop_btn").click(function(){
                var popId = $(this).attr("data-pop");
                if(popId == "pop1") {
                    var thisImg = $(this).children("img").clone();
                    $(".popup_bx[data-id='"+popId+"']").find(".img_bx").html(thisImg);
                    $(".popup_bx[data-id='"+popId+"']").find(".img_bx").children("img").css({"max-width": "100%", "width": "auto", "margin": "0 auto", "display": "block"});
                }
                $(".popup_bx[data-id='"+popId+"']").stop().fadeIn(300);
                $(".popup_bx[data-id='"+popId+"']").scrollTop(0);

                return false;
            });
            $(".popup_bx .close_btn").click(function(){
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
<?php include __DIR__ ."/../inc/footer.php"; ?>