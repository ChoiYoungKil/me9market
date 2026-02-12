<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "04";
	$dep1_tit = "주문관리";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">취소목록</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>주문관리</li>
                                <li>취소목록</li>
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
                                        <li><a href="../sub04/order_list.php"><span>주문목록</span></a></li>
                                        <li><a href="#" class="on"><span>취소목록</span></a></li>
                                        <li><a href="../sub04/order_return_request_list.php"><span>반품목록</span></a></li>
                                        <li><a href="../sub04/order_exchange_request_list.php"><span>교환목록</span></a></li>
                                    </ul>
                                </div>
                                <div class="list_top1">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                </div>
                                <div class="tb01 ovS">
                                    <table>
                                        <colgroup>
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="150px">
                                            <col width="170px">
                                            <col width="170px">
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="170px">
                                            <col width="120px">
                                            <col width="300px">
                                            <col width="100px">
                                            <col width="100px">
                                            <col width="80px">
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="120px">
                                            <col width="120px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>취소 주문번호</th>
                                                <th>주문번호</th>
                                                <th>Shop채널</th>
                                                <th>주문일시</th>
                                                <th>결제일시</th>
                                                <th>주문상태</th>
                                                <th>주문자</th>
                                                <th>취소 요청일시</th>
                                                <th>상품 유형</th>
                                                <th>상품명</th>
                                                <th>상품코드</th>
                                                <th>옵션</th>
                                                <th>수량</th>
                                                <th>상품금액</th>
                                                <th>배송비</th>
                                                <th>사용포인트</th>
                                                <th>결제금액</th>
                                                <th>적립포인트</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Me9-0002329</td>
                                                <td>Me9-0002329</td>
                                                <td>Shop 채널명</td>
                                                <td>2024-10-10 00:00:00</td>
                                                <td>2024-10-10 00:00:00</td>
                                                <td>취소완료</td>
                                                <td>홍길동</td>
                                                <td>2024-10-10 00:00:00</td>
                                                <td>자사</td>
                                                <td class="t_l"><a class="fcol2 link">BlueViolet a omnis</a></td>
                                                <td>a0029</td>
                                                <td>RD/S</td>
                                                <td>2</td>
                                                <td class="t_r">45,000 원</td>
                                                <td class="t_r">0 원</td>
                                                <td class="t_r">3,000 p</td>
                                                <td class="t_r"><span class="bold fcol4">42,000 원</span></td>
                                                <td class="t_r">4,200 p</td>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(".btn01.arrow").click(function(){
                var thisId = $(this).attr("id");
                $(this).toggleClass("on");
                $(".arrowbx[data-arrowbx='"+thisId+"']").stop().slideToggle(300);
            });
            
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