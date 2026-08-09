<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "00";
	$dep1_tit = "발주담당관리";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">발주담당 관리</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>발주담당 관리</li>
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
                                                <th class="w160"><span>이메일</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="btm_btn right mt10">
                                    <a href="./">검색</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box box1">
                        <div class="conbx">
                            <div class="con_w">
                                <div class="list_top1 btn">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                    <div class="btn_bx">
                                        <a href="./" class="btn01 col5 pop_btn" data-pop="pop1_1">발주담당 등록</a>
                                    </div>
                                </div>
                                <div class="tb01 ovS">
                                    <table>
                                        <colgroup>
                                            <col width="80px">
                                            <col width="80px">
                                            <col width="">
                                            <col width="120px">
                                            <col width="230px">
                                            <col width="150px">
                                            <col width="180px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>상태</th>
                                                <th>이메일</th>
                                                <th>발주담당자</th>
                                                <th>운영기간</th>
                                                <th>관리상품수</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>00</td>
                                                <td>운영</td>
                                                <td>email_id001@email_domain.com</td>
                                                <td>홍길동</td>
                                                <td>2024.10.01 01시 ~ 2024.11.01 23시</td>
                                                <td>12</td>
                                                <td>
                                                    <a href="./" class="btn02 col5 pop_btn" data-pop="pop2_1">보기</a>
                                                    <a href="./" class="btn02 col7 pop_btn" data-pop="pop3_1">수정</a>
                                                    <a href="./" class="btn02">삭제</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                
                                <div class="page_bx1">
                                    <a href="./" class="page_first">first</a>
                                    <a href="./" class="page_prev">prev</a>
                                    <a href="./" class="num on">1</a>
                                    <a href="./" class="num">2</a>
                                    <a href="./" class="num">3</a>
                                    <a href="./" class="num">4</a>
                                    <a href="./" class="num">5</a>
                                    <a href="./" class="page_next">next</a>
                                    <a href="./" class="page_last">last</a>
                                </div>
                                
                                <!-- 팝업 -->
                                <!-- 발주담당자 등록 팝업 -->
                                <div class="popup_bx" data-id="pop1_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w800">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">발주담당자 등록</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="140px">
                                                                    <col width="">
                                                                    <col width="140px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_1" checked="">
                                                                                    <label for="pop1_1_radio1_1">사용</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_2">
                                                                                    <label for="pop1_1_radio1_2">중지</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>이메일<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                           <div class="r_btn_w">
                                                                               <div class="email_bx">
                                                                                    <input type="text" class="email1" required="required" value="">
                                                                                    <span>@</span>
                                                                                    <input type="text" class="email2" required="required" value="">
                                                                                    <select class="off" required="required">
                                                                                        <option value="" selected="">직접입력</option>
                                                                                        <option value="1">naver.com</option>
                                                                                    </select>
                                                                                </div>
                                                                                <a href="./" class="btn01 pop_btn" data-pop="pop4_1">중복확인</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>발주담당자<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                           <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>접속기간</span></th>
                                                                        <td colspan="3">
                                                                           <div class="date_bx w600">
                                                                                <input class="datepicker" type="text" required="required" readonly="">
                                                                                <select required="required">
                                                                                    <option value="" disabled="" selected="">시 선택</option>
                                                                                    <option value="1">00시</option>
                                                                                </select>
                                                                                <span>~</span>
                                                                                <input class="datepicker" type="text" required="required" readonly="">
                                                                                <select required="required">
                                                                                    <option value="" disabled="" selected="">시 선택</option>
                                                                                    <option value="1">00시</option>
                                                                                </select>
                                                                            </div>
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
                                                    <a href="./">발주담당 등록</a>
                                                    <a href="./" class="col5 close_btn">닫기</a>
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
                                                    <div class="ttl">발주담당 상세보기</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                       <div class="ttl01">발주담당상태</div>

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
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">운영</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>운영기간</span></th>
                                                                        <td colspan="3">2024.10.01 01시 ~ 2024.11.01 23시</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="con_w">
                                                       <div class="ttl01">발주담당정보</div>

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
                                                                        <th class="w160"><span>이메일</span></th>
                                                                        <td colspan="3">email_id001@email_domain.com</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>담당자 명</span></th>
                                                                        <td colspan="3">홍길동</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="con_w">
                                                       <div class="ttl01">발주상품정보 (12)</div>

                                                        <div class="tb01">
                                                            <table>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <td>
                                                                           <div class="list01 over_S s1">
                                                                                <ul>
                                                                                    <li>
                                                                                        <a href="./">
                                                                                            <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                                                            <div class="txt_bx">
                                                                                                <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                                                                <strong>상품명 111111</strong>
                                                                                            </div>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li>
                                                                                        <a href="./">
                                                                                            <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                                                            <div class="txt_bx">
                                                                                                <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                                                                <strong>상품명 111111</strong>
                                                                                            </div>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li>
                                                                                        <a href="./">
                                                                                            <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                                                            <div class="txt_bx">
                                                                                                <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                                                                <strong>상품명 111111</strong>
                                                                                            </div>
                                                                                        </a>
                                                                                    </li>
                                                                                </ul>
                                                                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="con_w">
                                                       <div class="ttl01">메모</div>

                                                        <div class="tb01">
                                                            <table>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <td>
                                                                           메모 입니다.
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="./" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 수정 팝업 -->
                                <div class="popup_bx" data-id="pop3_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w800">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">발주담당자 수정</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="140px">
                                                                    <col width="">
                                                                    <col width="140px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_1" checked="">
                                                                                    <label for="pop1_1_radio1_1">사용</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_2">
                                                                                    <label for="pop1_1_radio1_2">중지</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>이메일<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                           <div class="r_btn_w">
                                                                               <div class="email_bx">
                                                                                    <input type="text" class="email1" required="required" value="">
                                                                                    <span>@</span>
                                                                                    <input type="text" class="email2" required="required" value="">
                                                                                    <select class="off" required="required">
                                                                                        <option value="" selected="">직접입력</option>
                                                                                        <option value="1">naver.com</option>
                                                                                    </select>
                                                                                </div>
                                                                                <a href="./" class="btn01 pop_btn" data-pop="pop4_1">중복확인</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>발주담당자<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                           <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>접속기간</span></th>
                                                                        <td colspan="3">
                                                                           <div class="date_bx w600">
                                                                                <input class="datepicker" type="text" required="required" readonly="">
                                                                                <select required="required">
                                                                                    <option value="" disabled="" selected="">시 선택</option>
                                                                                    <option value="1">00시</option>
                                                                                </select>
                                                                                <span>~</span>
                                                                                <input class="datepicker" type="text" required="required" readonly="">
                                                                                <select required="required">
                                                                                    <option value="" disabled="" selected="">시 선택</option>
                                                                                    <option value="1">00시</option>
                                                                                </select>
                                                                            </div>
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
                                                    <a href="./">발주담당 수정</a>
                                                    <a href="./" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 중복확인 팝업 -->
                                <div class="popup_bx" data-id="pop4_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w560">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="imp_bx01 bN">
                                                            <div class="txt2 mt0">중복된 이메일을 가진 발주 담당이 있습니다.</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="./" class="col5 close_btn">확인</a>
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