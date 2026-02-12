<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "00";
	$dep1_tit = "취소/환불안내";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">취소/환불안내 관리</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>취소/환불안내 관리</li>
                            </ul>
                        </div>
                        <div class="conbx">
                            <div class="con_w">
                                <div class="list_top1 btn">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                    <div class="btn_bx">
                                        <a href="#" class="btn01 col5 pop_btn" data-pop="pop1_1">취소/환불안내 등록</a>
                                    </div>
                                </div>
                                <div class="tb01 ovS">
                                    <table>
                                        <colgroup>
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="">
                                            <col width="120px">
                                            <col width="130px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>설정구분</th>
                                                <th>상태</th>
                                                <th>취소/환불안내 명</th>
                                                <th>상품수</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>기본</td>
                                                <td>사용</td>
                                                <td class="t_l">기본 취소/환불안내 명칭</td>
                                                <td>12</td>
                                                <td>
                                                    <a href="#" class="btn02 col5 pop_btn" data-pop="pop2_1">보기</a>
                                                    <a href="#" class="btn02 col2">복사</a>
                                                    <a href="#" class="btn02 col7 mt5 pop_btn" data-pop="pop3_1">수정</a>
                                                    <a href="#" class="btn02 mt5">삭제</a>
                                                </td>
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
                                <!-- 취소/환불안내 등록 팝업 -->
                                <div class="popup_bx" data-id="pop1_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">취소/환불안내 등록</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="170px">
                                                                    <col width="">
                                                                    <col width="170px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>설정구분</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_1" checked="">
                                                                                    <label for="pop1_1_radio1_1">기본</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_2">
                                                                                    <label for="pop1_1_radio1_2">사용자</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_1" checked="">
                                                                                    <label for="pop1_1_radio2_1">사용</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_2">
                                                                                    <label for="pop1_1_radio2_2">중지</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>취소/환불안내 명칭<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                            <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>취소/환불안내 내용</span></th>
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
                                                    <a href="#">취소/환불안내 등록</a>
                                                    <a href="#" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 보기 팝업 -->
                                <div class="popup_bx" data-id="pop2_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w800">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">취소/환불 안내 정보</div>
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
                                                                        <th class="w160"><span>설정구분</span></th>
                                                                        <td colspan="3">사용자</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">사용</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>취소/환불안내 명칭</span></th>
                                                                        <td colspan="3">사용자 취소/환불 안내 명칭</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160" rowspan="2"><span>취소/환불안내 내용</span></th>
                                                                        <td colspan="3">사용자 취소/환불 안내 내용</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="#" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 수정 팝업 -->
                                <div class="popup_bx" data-id="pop3_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">취소/환불안내 수정</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="170px">
                                                                    <col width="">
                                                                    <col width="170px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>설정구분</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_1" checked="">
                                                                                    <label for="pop1_1_radio1_1">기본</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_2">
                                                                                    <label for="pop1_1_radio1_2">사용자</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_1" checked="">
                                                                                    <label for="pop1_1_radio2_1">사용</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_2">
                                                                                    <label for="pop1_1_radio2_2">중지</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>취소/환불안내 명칭<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                            <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>취소/환불안내 내용</span></th>
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
                                                    <a href="#">취소/환불안내 수정</a>
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