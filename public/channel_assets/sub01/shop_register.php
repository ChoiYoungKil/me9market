<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "01";
	$dep1_tit = "Shop채널관리";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">Shop채널등록</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>Shop채널관리</li>
                                <li>Shop채널등록</li>
                            </ul>
                        </div>
                        <div class="conbx">
                            <div class="con_w">
                                <div class="ttl01">Shop 채널 기본정보</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>Shop 채널코드</span></th>
                                                <td>Me9-2024-099833</td>
                                                <th class="w160"><span>채널상태</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_1" checked="">
                                                            <label for="radio1_1">운영</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_2">
                                                            <label for="radio1_2">중지</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>공개여부</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li class="w100p">
                                                            <input type="radio" name="radio2" id="radio2_1" checked="">
                                                            <label for="radio2_1">공개</label>
                                                        </li>
                                                        <li class="w100p">
                                                            <input type="radio" name="radio2" id="radio2_2">
                                                            <label for="radio2_2">비공개</label>
                                                            <input class="w200 ml5" type="password" value="" required="required" placeholder="비밀번호 입력">
                                                        </li>
                                                    </ul>
                                                </td>
                                                <th class="w160"><span>구매권한</span></th>
                                                <td>
                                                    <ul class="chk02">
                                                        <li>
                                                            <input type="checkbox" name="chk1" id="chk1_1">
                                                            <label for="chk1_1">회원전용</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>* 채널명</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>카피라이트</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>그룹 keyword</span></th>
                                                <td colspan="3">
                                                    <div class="r_btn_w w457">
                                                        <input type="text" value="" required="required" placeholder="Keyword 입력">
                                                        <a href="#" class="btn01">등록</a>
                                                    </div>
                                                    <ul class="tag_list mt5">
                                                        <li>그룹명 #2<button class="del">삭제</button></li>
                                                        <li>그룹명 키워드 #1<button class="del">삭제</button></li>
                                                        <li>그룹명 키워드 #2<button class="del">삭제</button></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">Shop 채널 사용주기</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>사용기간 여부</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio3" id="radio3_1" checked="">
                                                            <label for="radio3_1">무기한</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio3" id="radio3_2" class="arrow">
                                                            <label for="radio3_2">기간제</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tb01 bN arrowbx" data-arrowbx="radio3">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>사용기간</span></th>
                                                <td>
                                                    <div class="date_bx w600">
                                                        <input class="datepicker" type="text" required="required" readonly>
                                                        <select required="required">
                                                            <option value="" disabled="" selected="">시 선택</option>
                                                            <option value="1">00시</option>
                                                        </select>
                                                        <span>~</span>
                                                        <input class="datepicker" type="text" required="required" readonly>
                                                        <select required="required">
                                                            <option value="" disabled="" selected="">시 선택</option>
                                                            <option value="1">00시</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">Shop 채널 로고 이미지 <span class="col2 fs2">(Shop 채널 로고를 생략 할 경우 채널명 노출)</span></div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>로고 사용여부</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio4" id="radio4_1" checked="">
                                                            <label for="radio4_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4" id="radio4_2" class="arrow">
                                                            <label for="radio4_2">이미지 사용</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tb01 bN arrowbx" data-arrowbx="radio4">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>로고 이미지</span></th>
                                                <td>
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly">
                                                        <label for="uploadBtn1" class="btn_file">찾아보기</label>
                                                        <input type="file" id="uploadBtn1" class="uploadBtn" name="bbs_file1">
                                                        <div class="del_btn">삭제</div>
                                                    </div>
                                                    <p class="mt10">※ 최적사이즈 000px * 000px</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">메인 배너 이미지</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>메인 배너 사용여부</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio5" id="radio5_1" checked="">
                                                            <label for="radio5_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio5" id="radio5_2" class="arrow">
                                                            <label for="radio5_2">이미지로 등록</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tb01 bN arrowbx" data-arrowbx="radio5">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>배너 이미지 (Max 5)</span></th>
                                                <td>
                                                    <div class="r_btn_w w560">
                                                        <a href="#" class="btn01 col5 bold">추가하기</a>
                                                        <div class="fileBox">
                                                            <input type="text" class="fileName" readonly="readonly">
                                                            <label for="uploadBtn2_1" class="btn_file">찾아보기</label>
                                                            <input type="file" id="uploadBtn2_1" class="uploadBtn" name="bbs_file1">
                                                            <div class="del_btn">삭제</div>
                                                        </div>
                                                        <!--<div class="fileBox">
                                                            <input type="text" class="fileName" readonly="readonly">
                                                            <label for="uploadBtn2_2" class="btn_file">찾아보기</label>
                                                            <input type="file" id="uploadBtn2_2" class="uploadBtn" name="bbs_file1">
                                                            <div class="del_btn">삭제</div>
                                                        </div>-->
                                                    </div>
                                                    <p class="mt5">※ 최적사이즈 000px * 000px</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">OG (오픈 그래프) TAG <span class="col2 fs2">(SNS에 게시되는데 최적화된 데이터) </span></div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>OG 사용여부</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio6" id="radio6_1" checked="">
                                                            <label for="radio6_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio6" id="radio6_2" class="arrow">
                                                            <label for="radio6_2">사용</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tb01 bN arrowbx" data-arrowbx="radio6">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="100px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160" rowspan="3"><span>OG TAG</span></th>
                                                <td>Title</td>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Description</td>
                                                <td><input type="text" value="" required="required"></td>
                                            </tr>
                                            <tr>
                                                <td>Image</td>
                                                <td>
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly">
                                                        <label for="uploadBtn3" class="btn_file">찾아보기</label>
                                                        <input type="file" id="uploadBtn3" class="uploadBtn" name="bbs_file1">
                                                        <div class="del_btn">삭제</div>
                                                    </div>
                                                    <p class="mt10">※ 최적사이즈 000px * 000px</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">Shop 채널 (모니터링) 관리자 정보</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>Shop 채널 관리자 여부</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio7" id="radio7_1" checked="">
                                                            <label for="radio7_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio7" id="radio7_2" class="arrow">
                                                            <label for="radio7_2">사용</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tb01 bN arrowbx" data-arrowbx="radio7">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>Shop 채널 관리자 성명</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>로그인 ID</span></th>
                                                <td><input type="text" value="" required="required"></td>
                                                <th class="w160"><span>로그인 PW</span></th>
                                                <td><input type="password" value="" required="required"></td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>정산(지급)방법</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio8" id="radio8_1" checked="">
                                                            <label for="radio8_1">판매금액 대비 % 지급</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio8" id="radio8_2">
                                                            <label for="radio8_2">판매 개당 금액</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160" rowspan="2"><span>정산요율</span></th>
                                                <td colspan="3">
                                                    <input class="w200" type="text" value="" required="required"> % (총 판매금액에서 지급되는 %)
                                                </td>
                                            </tr>
                                            <tr>
                                                <!--<th class="w160"><span></span></th>-->
                                                <td colspan="3">
                                                    <input class="w200" type="text" value="" required="required"> 원 (판매당 지급되는 비용)
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                                
                    <div class="btm_btn">
                        <a href="#">등록</a>
                        <a href="../sub01/shop_list.php" class="col5">목록</a>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(".chk01 input").click(function(){
                var thisId = $(this).attr("name");
                if($(this).hasClass("arrow")) {
                    $(".arrowbx[data-arrowbx='"+thisId+"']").stop().slideDown(300);
                }else {
                    $(".arrowbx[data-arrowbx='"+thisId+"']").stop().slideUp(300);
                }
            });
            
            /* 파일 */
            var uploadFile = $('.fileBox .uploadBtn');
            uploadFile.on('change', function(){
                if(window.FileReader){
                    var filename = $(this)[0].files[0].name;
                } else {
                    var filename = $(this).val().split('/').pop().split('\\').pop();
                }
                $(this).parents('.fileBox').find('.fileName').val(filename);
                $(this).parents('.fileBox').find('.fileName').addClass("on");
            });
            $(".fileBox .del_btn").click(function(){
                $(this).siblings("input").val("");
                $(this).siblings(".fileName").removeClass("on");
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