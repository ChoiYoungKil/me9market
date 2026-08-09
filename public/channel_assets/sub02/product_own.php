<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "02";
	$dep1_tit = "상품관리";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">자사상품관리</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>상품관리</li>
                                <li>자사상품관리</li>
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
                                                <th class="w160"><span>상품명</span></th>
                                                <td colspan="3">
                                                    <div class="r_btn_w">
                                                        <input type="text" value="" required="required">
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
                                                <th class="w160"><span>상품분류</span></th>
                                                <td colspan="3">
                                                    <ul class="type_bx w600">
                                                        <li>
                                                            <select required="required">
                                                                <option value="" disabled="" selected="">대분류</option>
                                                                <option value="1">대분류1</option>
                                                            </select>
                                                        </li>
                                                        <li>
                                                            <select required="required">
                                                                <option value="" disabled="" selected="">중분류</option>
                                                                <option value="1">중분류1</option>
                                                            </select>
                                                        </li>
                                                        <li>
                                                            <select required="required">
                                                                <option value="" disabled="" selected="">세분류</option>
                                                                <option value="1">세분류1</option>
                                                            </select>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>상품상태</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_1" checked="">
                                                            <label for="radio1_1">판매</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_2">
                                                            <label for="radio1_2">중지</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_3">
                                                            <label for="radio1_3">판매중지예고</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                                <th class="w160"><span>판매범위</span></th>
                                                <td>
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio2" id="radio2_1" checked="">
                                                            <label for="radio2_1">자사상품</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio2" id="radio2_2">
                                                            <label for="radio2_2">공개상품</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio2" id="radio2_3">
                                                            <label for="radio2_3">부분공개상품</label>
                                                        </li>
                                                    </ul>
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
                </div>
                <div class="row">
                    <div class="box box2">
                        <div class="conbx">
                            <div class="con_w">
                                <div class="list_top1 btn">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                    <div class="btn_bx">
                                        <a href="./" class="btn01 col2">EXCEL</a>
                                        <a href="../sub02/product_request.php" class="btn01 col5">상품등록</a>
                                    </div>
                                </div>
                                
                                <div class="tb01 ovS">
                                    <table>
                                        <colgroup>
                                            <col width="80px">
                                            <col width="80px">
                                            <col width="">
                                            <col width="100px">
                                            <col width="100px">
                                            <col width="80px">
                                            <col width="120px">
                                            <col width="130px">
                                            <col width="120px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>상품코드</th>
                                                <th>상품상태</th>
                                                <th>상품명</th>
                                                <th>금액</th>
                                                <th>채널범위</th>
                                                <th>게시채널</th>
                                                <th>판매요청목록</th>
                                                <th>판매중지신청</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody class="textL">
                                            <tr>
                                                <td class="t_c">a20392</td>
                                                <td class="t_c">판매</td>
                                                <td>
                                                    <div class="thum01">
                                                        <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                        <div class="txt_bx">
                                                            <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                            <strong>상품명 111111</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="t_c">₩ 5,000</td>
                                                <td class="t_c">공개, 회원용</td>
                                                <td class="t_c"><a href="./" class="btn02 col3 pop_btn" data-pop="pop4_1">03</a></td>
                                                <td class="t_c"><a href="./" class="btn02 col5 pop_btn" data-pop="pop1_1">판매요청목록</a></td>
                                                <td class="t_c"><a href="./" class="btn02 col7 pop_btn" data-pop="pop2_1">판매중지 예고신청</a></td>
                                                <td class="t_c">
                                                    <a href="./" class="btn02 col5 pop_btn" data-pop="pop3_1">보기</a>
                                                    <a href="./" class="btn02 col2">복사</a>
                                                    <a href="./" class="btn02 col4 mt5">수정</a>
                                                    <a href="./" class="btn02 mt5">삭제</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="t_c">a20392</td>
                                                <td class="t_c">중지</td>
                                                <td>
                                                    <div class="thum01">
                                                        <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                        <div class="txt_bx">
                                                            <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                            <strong>상품명 111111</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="t_c">₩ 1,000</td>
                                                <td class="t_c">비공개, 회원용</td>
                                                <td class="t_c"><a href="./" class="btn02 col3 pop_btn" data-pop="pop4_1">--</a></td>
                                                <td class="t_c"><a href="./" class="btn02 col4 pop_btn" data-pop="pop1_1">판매요청목록</a></td>
                                                <td class="t_c"><a href="./" class="btn02 col7 pop_btn" data-pop="pop2_1">판매중지 예고신청</a></td>
                                                <td class="t_c">
                                                    <a href="./" class="btn02 col5 pop_btn" data-pop="pop3_1">보기</a>
                                                    <a href="./" class="btn02 col2">복사</a>
                                                    <a href="./" class="btn02 col7 mt5">수정</a>
                                                    <a href="./" class="btn02 mt5">삭제</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="t_c">a20392</td>
                                                <td class="t_c">판매중지예고</td>
                                                <td>
                                                    <div class="thum01">
                                                        <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                        <div class="txt_bx">
                                                            <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                            <strong>상품명 111111</strong>
                                                        </div>
                                                    </div>
                                                    <div class="mt10 fcol3">판매중지예고 &nbsp;&nbsp; <strong>0000.00.00</strong></div>
                                                </td>
                                                <td class="t_c">₩ 1,000</td>
                                                <td class="t_c">공개, 일반용</td>
                                                <td class="t_c"><a href="./" class="btn02 col3 pop_btn" data-pop="pop4_1">13</a></td>
                                                <td class="t_c"><a href="./" class="btn02 col5 pop_btn" data-pop="pop1_1">판매요청목록</a></td>
                                                <td class="t_c"><a href="./" class="btn02 col4 pop_btn" data-pop="pop2_1">판매중지 예고신청</a></td>
                                                <td class="t_c">
                                                    <a href="./" class="btn02 col5 pop_btn" data-pop="pop3_1">보기</a>
                                                    <a href="./" class="btn02 col2">복사</a>
                                                    <a href="./" class="btn02 col4 mt5">수정</a>
                                                    <a href="./" class="btn02 mt5">삭제</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                
                                    <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                </div>
                                
                                <!-- 페이징 -->
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
                                <!-- 게시채널 팝업 -->
                                <div class="popup_bx" data-id="pop4_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">상품게시한 채널목록</div>
                                                </div>

                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="thum01">
                                                            <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                            <div class="txt_bx">
                                                                <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                                <strong>상품명 111111</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="con_w">
                                                        <div class="list_top1">
                                                            <div class="count">총 <strong>00</strong> 건</div>
                                                        </div>
                                                        <div class="tb01 ovS">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="80px">
                                                                    <col width="80px">
                                                                    <col width="">
                                                                    <col width="100px">
                                                                    <col width="80px">
                                                                    <col width="10%">
                                                                    <col width="10%">
                                                                </colgroup>
                                                                <thead>
                                                                    <tr>
                                                                        <th>채널코드</th>
                                                                        <th>채널상태</th>
                                                                        <th>채널명</th>
                                                                        <th>채널범위</th>
                                                                        <th>상품수</th>
                                                                        <th>QR 코드</th>
                                                                        <th>단축주소</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <td class="t_c">a20392</td>
                                                                        <td class="t_c">운영</td>
                                                                        <td>
                                                                            채널명 123
                                                                            <ul class="tag_list">
                                                                                <li>#그룹 키워드 #1</li>
                                                                                <li>#키워드 #2</li>
                                                                            </ul>
                                                                        </td>
                                                                        <td class="t_c">공개, 회원용</td>
                                                                        <td class="t_c">03</td>
                                                                        <td class="t_c">
                                                                            <div class="pop_btn" data-pop="pop4_1_1">
                                                                                <img src="../images/sub/qr_sample1.jpg" style="max-width: 60px; width:100%;">
                                                                            </div>
                                                                        </td>
                                                                        <td class="t_c">//qcc112ko</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="t_c">a20392</td>
                                                                        <td class="t_c">중지</td>
                                                                        <td>
                                                                            비공개 채널명 123
                                                                            <ul class="tag_list">
                                                                                <li>#그룹 키워드 #1</li>
                                                                            </ul>
                                                                        </td>
                                                                        <td class="t_c">비공개, 회원용</td>
                                                                        <td class="t_c">--</td>
                                                                        <td class="t_c">
                                                                            <div class="pop_btn" data-pop="pop4_1_1">
                                                                                <img src="../images/sub/qr_sample1.jpg" style="max-width: 60px; width:100%;">
                                                                            </div>
                                                                        </td>
                                                                        <td class="t_c">//qcc112ko</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="t_c">a20392</td>
                                                                        <td class="t_c">운영</td>
                                                                        <td>
                                                                            일반용 채널명 123
                                                                            <ul class="tag_list">
                                                                                <li>#그룹 키워드 #1</li>
                                                                                <li>#키워드 #2</li>
                                                                            </ul>
                                                                        </td>
                                                                        <td class="t_c">공개, 일반용</td>
                                                                        <td class="t_c">13</td>
                                                                        <td class="t_c">
                                                                            <div class="pop_btn" data-pop="pop4_1_1">
                                                                                <img src="../images/sub/qr_sample1.jpg" style="max-width: 60px; width:100%;">
                                                                            </div>
                                                                        </td>
                                                                        <td class="t_c">//qcc112ko</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                            <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                                            
                                                            <!-- 페이징 -->
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
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 게시채널 팝업 ==> RQ 팝업 -->
                                <div class="popup_bx" data-id="pop4_1_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w457">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="ttl01">QR 코드</div>
                                                        <div class="img_bx"></div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt20">
                                                    <a href="./" class="col5 close_btn">닫기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    $(".pop_btn[data-pop='pop4_1_1']").click(function(){
                                        var popId = $(this).attr("data-pop");
                                        if(popId == "pop4_1_1") {
                                            var thisImg = $(this).children("img").clone();
                                            $(".popup_bx[data-id='"+popId+"']").find(".img_bx").html(thisImg);
                                            $(".popup_bx[data-id='"+popId+"']").find(".img_bx").children("img").css({"max-width": "100%", "width": "auto", "margin": "0 auto", "display": "block"});
                                        }

                                        return false;
                                    });
                                </script>
                                
                                <!-- 판매요청목록 팝업 -->
                                <div class="popup_bx" data-id="pop1_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">판매요청목록</div>
                                                </div>

                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="160px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>상품명</span></th>
                                                                        <td>
                                                                            <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>상품분류</span></th>
                                                                        <td>
                                                                            <ul class="type_bx w600">
                                                                                <li>
                                                                                    <select required="required">
                                                                                        <option value="" disabled="" selected="">대분류</option>
                                                                                        <option value="1">대분류1</option>
                                                                                    </select>
                                                                                </li>
                                                                                <li>
                                                                                    <select required="required">
                                                                                        <option value="" disabled="" selected="">중분류</option>
                                                                                        <option value="1">중분류1</option>
                                                                                    </select>
                                                                                </li>
                                                                                <li>
                                                                                    <select required="required">
                                                                                        <option value="" disabled="" selected="">세분류</option>
                                                                                        <option value="1">세분류1</option>
                                                                                    </select>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>신청자</span></th>
                                                                        <td>
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

                                                    <div class="con_w">
                                                        <div class="list_top1">
                                                            <div class="count">총 <strong>00</strong> 건</div>
                                                        </div>

                                                        <div class="tb01 ovS">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="70px">
                                                                    <col width="70px">
                                                                    <col width="150">
                                                                    <col width="17%">
                                                                    <col width="100px">
                                                                    <col width="">
                                                                    <col width="100px">
                                                                </colgroup>
                                                                <thead>
                                                                    <tr>
                                                                        <th><input type="checkbox"></th>
                                                                        <th>번호</th>
                                                                        <th>신청일지</th>
                                                                        <th>상품명</th>
                                                                        <th>신청자</th>
                                                                        <th>신청사유</th>
                                                                        <th>상태</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><input type="checkbox"></td>
                                                                        <td>00</td>
                                                                        <td>0000-00-00 00:00</td>
                                                                        <td class="t_l">나이키 운동화</td>
                                                                        <td>아무개</td>
                                                                        <td class="t_l">신청사유 신청사유 신청사유 신청사유</td>
                                                                        <td>미승인</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><input type="checkbox"></td>
                                                                        <td>00</td>
                                                                        <td>0000-00-00 00:00</td>
                                                                        <td class="t_l">고추 비료 DDT 3호</td>
                                                                        <td>아무개</td>
                                                                        <td class="t_l">신청사유 신청사유 <br>신청사유 신청사유 신청사유 </td>
                                                                        <td>승인</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><input type="checkbox"></td>
                                                                        <td>00</td>
                                                                        <td>0000-00-00 00:00</td>
                                                                        <td class="t_l">나이키 운동화</td>
                                                                        <td>홍길동</td>
                                                                        <td class="t_l">신청사유</td>
                                                                        <td>승인거절</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                                        
                                                        <div class="btm_btn right mt10">
                                                            <!-- 페이징 -->
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

                                                            <a href="./" class="col5 close_btn">승인거절</a>
                                                            <a href="./">판매승인</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 판매중지 예고신청 팝업 -->
                                <div class="popup_bx" data-id="pop2_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w640">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">판매중지예고 설정</div>
                                                </div>

                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="imp_bx01">
                                                            <div class="txt1"><span>유의사항</span></div>
                                                            <div class="txt2">
                                                                판매중지 예고설정 시 <br>해당 상품이 게시 된 상세페이지에 중지 예정일이 표기되지만 <br>상품이 자동으로 판매중지 되지는 않습니다.
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="tb01 mt10">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="180px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>판매중지 예고일자 설정</span></th>
                                                                        <td>
                                                                            <input class="datepicker w160" type="text" required="required" readonly="">
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="./" class="col5 close_btn">취소</a>
                                                    <a href="./">확인</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 보기 팝업 -->
                                <?php include __DIR__ . "/../sub02/pop.product_own_view.php"; ?>
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