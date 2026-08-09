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
                                <div class="ttl01">상품 기본정보</div>
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
                                                <th class="w160"><span>상품코드</span></th>
                                                <td>Me9-2024-099833</td>
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
                                                    </ul>
                                                </td>
                                            </tr>
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
                                                <th class="w160"><span>상품명</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>판매범위</span></th>
                                                <td>
                                                    <ul class="chk02">
                                                        <li>
                                                            <input type="checkbox" name="chk1" id="chk1_1" checked="">
                                                            <label for="chk1_1">자사</label>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" name="chk1" id="chk1_2">
                                                            <label for="chk1_2">공개</label>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" name="chk1" id="chk1_3">
                                                            <label for="chk1_3">부분공개</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                                <th class="w160"><span>기본금액</span></th>
                                                <td>
                                                    <input class="w160" type="text" value="" required="required"> &nbsp;원
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- 대분류 선택시 -->
                            <div class="con_w">
                                <div class="ttl01">상품 유형별  필수 표시 사항 (식품 및 건강기능식품)</div>
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
                                                <th class="w160"><span>제품명</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                                <th class="w160"><span>식품의 유형</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>제조업소명</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                                <th class="w160"><span>소재지</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>원재료 명 및 함량</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                    <p class="mt10">※ 알레르기 유발 물질 표시 포함</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>유통기한 또는 <br>품질유지기한</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                                <th class="w160"><span>제조연월일</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>내용량<br>(중량, 부피 등)</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>보관방법</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>섭취 방법 및 섭취 시 주의 사항</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>건강기능식품의 경우 기능성 내용 및 ‘질병 예방,치료 목적이 아님’ 문구표시</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="con_w">
                                <div class="ttl01">상품 목록 이미지</div>
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
                                                <th class="w160"><span>목록 이미지 (Max 5)</span></th>
                                                <td colspan="3">
                                                    <div class="r_btn_w w560">
                                                        <a href="./" class="btn01 col5 bold">추가하기</a>
                                                        <div class="fileBox">
                                                            <input type="text" class="fileName" readonly="readonly">
                                                            <label for="uploadBtn1_1" class="btn_file">찾아보기</label>
                                                            <input type="file" id="uploadBtn1_1" class="uploadBtn" name="bbs_file1">
                                                            <div class="del_btn">삭제</div>
                                                        </div>
                                                        <!--<div class="fileBox">
                                                            <input type="text" class="fileName" readonly="readonly">
                                                            <label for="uploadBtn1_2" class="btn_file">찾아보기</label>
                                                            <input type="file" id="uploadBtn1_2" class="uploadBtn" name="bbs_file1">
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
                                <div class="ttl01">상품 상세 설명</div>
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
                                                <th class="w160"><span>상세정보 등록타입</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio2" id="radio2_1" checked="">
                                                            <label for="radio2_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio2" id="radio2_2">
                                                            <label for="radio2_2">이미지로 등록</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio2" id="radio2_3">
                                                            <label for="radio2_3">텍스트로 등록</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 이미지로 등록 선택시 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="260px">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th rowspan="2" class="w160"><span>상세정보</span></th>
                                                <td>* PC 최적화 이미지 (가로 000pixel)</td>
                                                <td colspan="2">
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly">
                                                        <label for="uploadBtn2_1_1" class="btn_file">찾아보기</label>
                                                        <input type="file" id="uploadBtn2_1_1" class="uploadBtn" name="bbs_file1">
                                                        <div class="del_btn">삭제</div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>* Mobile 최적화 이미지 (가로 000pixel)</td>
                                                <td colspan="2">
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly">
                                                        <label for="uploadBtn2_1_2" class="btn_file">찾아보기</label>
                                                        <input type="file" id="uploadBtn2_1_2" class="uploadBtn" name="bbs_file1">
                                                        <div class="del_btn">삭제</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 텍스트로 등록 선택시 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>상세정보</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                  
                            <div class="con_w">
                                <div class="ttl01">취소/환불(교환) 안내 설정</div>
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
                                                <th class="w160"><span>설정 정보 선택</span></th>
                                                <td colspan="3">
                                                    <select class="w310" required="required">
                                                        <option value="" disabled="" selected="">설정 정보 선택</option>
                                                        <option value="1">기본 정보</option>
                                                        <option value="1">취소/환불 정보</option>
                                                        <option value="1">쇼핑몰 취소 정보</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>취소/환불(교환)<br>안내 정보</span></th>
                                                <td colspan="3">
                                                    <textarea value="" required="required"></textarea>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                  
                            <div class="con_w">
                                <div class="ttl01">상품 옵션</div>
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
                                                <th class="w160"><span>재고사용 여부</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio3" id="radio3_1" checked="">
                                                            <label for="radio3_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio3" id="radio3_2">
                                                            <label for="radio3_2">사용</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>옵션선택</span></th>
                                                <td colspan="3">
                                                    <select class="w310" required="required">
                                                        <option value="" disabled="" selected="">옵션타입 선택</option>
                                                        <option value="1">비고형</option>
                                                        <option value="1">일반선택형</option>
                                                        <option value="1">금액선택형</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 옵션선택 선택시 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>옵션</span></th>
                                                <td colspan="3">
                                                    <ul class="typeC01">
                                                        <li>
                                                            <div class="typeC_w">
                                                                <select required="required" class="t01">
                                                                    <option value="" disabled="" selected="">비고형 선택</option>
                                                                </select>
                                                                <input type="text" value="" required="required" class="t02" placeholder="옵션명">
                                                                <input type="text" value="" required="required" class="t03" placeholder="비고내용">
                                                                <input type="text" value="" required="required" class="t02" placeholder="수량입력(개)">
                                                                <div class="del">삭제</div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="typeC_w">
                                                                <select required="required" class="t01">
                                                                    <option value="" disabled="" selected="">일반선택형 선택</option>
                                                                </select>
                                                                <select required="required" class="t02">
                                                                    <option value="" disabled="" selected="">선택개수</option>
                                                                </select>
                                                                <input type="text" value="" required="required" class="t02" placeholder="옵션명 ex)사이즈">
                                                                <input type="text" value="" required="required" class="t04" placeholder="선택옵션 내용">
                                                                <input type="text" value="" required="required" class="t02" placeholder="수량입력(개)">
                                                                <div class="del">삭제</div>
                                                            </div>
                                                            <div class="typeC_w">
                                                                <input type="text" value="" required="required" class="t04" placeholder="선택옵션 내용">
                                                                <input type="text" value="" required="required" class="t02" placeholder="수량입력(개)">
                                                                <div class="del">삭제</div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="typeC_w">
                                                                <select required="required" class="t01">
                                                                    <option value="" disabled="" selected="">금액선택형 선택</option>
                                                                </select>
                                                                <select required="required" class="t02">
                                                                    <option value="" disabled="" selected="">선택개수</option>
                                                                </select>
                                                                <input type="text" value="" required="required" class="t02" placeholder="옵션명 ex)사이즈">
                                                                <input type="text" value="" required="required" class="t02" placeholder="선택옵션 내용">
                                                                <input type="text" value="" required="required" class="t02" placeholder="+, - 금액입력(원)">
                                                                <input type="text" value="" required="required" class="t02" placeholder="수량입력(개)">
                                                                <div class="del">삭제</div>
                                                            </div>
                                                            <div class="typeC_w">
                                                                <input type="text" value="" required="required" class="t02" placeholder="선택옵션 내용">
                                                                <input type="text" value="" required="required" class="t02" placeholder="+, - 금액입력(원)">
                                                                <input type="text" value="" required="required" class="t02" placeholder="수량입력(개)">
                                                                <div class="del">삭제</div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                  
                            <div class="con_w">
                                <div class="ttl01">상품 제약 조건</div>
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
                                                <th class="w160"><span>지급포인트 </span></th>
                                                <td colspan="3">
                                                    <input class="w310" type="text" value="" required="required"> &nbsp;point  
                                                    <p class="mt10">※ 포인트를 기재하지 않으면 포인트 지급은 반영되지 않습니다.</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>과세구분</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio4_1" id="radio4_1_1" checked="">
                                                            <label for="radio4_1_1">과세</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4_1" id="radio4_1_2">
                                                            <label for="radio4_1_2">면세</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4_1" id="radio4_1_3">
                                                            <label for="radio4_1_3">영세</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>가격 제약 조건</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio4_2" id="radio4_2_1" checked="">
                                                            <label for="radio4_2_1">제약없음</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4_2" id="radio4_2_2">
                                                            <label for="radio4_2_2">제약있음</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 가격제약을 선택하고 판매금액의 범위가 범위형일 경우 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>판매금액 설정범위</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio4_2_1" id="radio4_2_1_1" checked="">
                                                            <label for="radio4_2_1_1">범위형</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4_2_1" id="radio4_2_1_2">
                                                            <label for="radio4_2_1_2">고정형</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>이익 배분 조건</span></th>
                                                <td colspan="3">
                                                    <p>공유 제공자 이익 비용 <input class="w160" type="text" value="" required="required"> 원</p>
                                                    <p class="mt5">판매비용 <input class="w160" type="text" value="" required="required"> 부터 ~ <input class="w160" type="text" value="" required="required"> 원 사이 설정가능</p>
                                                    <p class="mt5">(판매비용 = 기본금액 + Me9분담금 + 이익배분비용)</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 가격제약을 선택하고 판매금액의 범위가 고정형일 경우 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>판매금액 설정범위</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio4_2_2" id="radio4_2_2_1">
                                                            <label for="radio4_2_2_1">범위형</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4_2_2" id="radio4_2_2_2" checked="">
                                                            <label for="radio4_2_2_2">고정형</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>이익 배분 조건</span></th>
                                                <td colspan="3">
                                                    <div>
                                                        <ul class="chk01">
                                                            <li>
                                                                <input type="radio" name="radio4_2_3" id="radio4_2_3_1" checked="">
                                                                <label for="radio4_2_3_1">고정비용</label>
                                                            </li>
                                                            <li class="mr5">
                                                                <input type="radio" name="radio4_2_3" id="radio4_2_3_2">
                                                                <label for="radio4_2_3_2">비율비용 (%)&nbsp;&nbsp;/&nbsp;&nbsp;개당&nbsp;&nbsp;<input class="w160" type="text" value="" required="required">&nbsp;원</label>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="mt5">
                                                        <ul class="chk01">
                                                            <li>
                                                                <input type="radio" name="radio4_2_4" id="radio4_2_4_1">
                                                                <label for="radio4_2_4_1">고정비용</label>
                                                            </li>
                                                            <li class="mr5">
                                                                <input type="radio" name="radio4_2_4" id="radio4_2_4_2" checked="">
                                                                <label for="radio4_2_4_2">비율비용 (%)&nbsp;&nbsp;/&nbsp;&nbsp;개당&nbsp;&nbsp;<input class="w160" type="text" value="" required="required">&nbsp;%</label>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <p class="mt5">판매비용 <input class="w160" type="text" value="" required="required"> 원</p>
                                                    <p class="mt5">(판매비용 = 기본금액 + Me9분담금 + 이익배분비용)</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>구매제한수량</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio4_3" id="radio4_3_1" checked="">
                                                            <label for="radio4_3_1">미사용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio4_3" id="radio4_3_2">
                                                            <label for="radio4_3_2">사용</label>
                                                        </li>
                                                    </ul>
                                                    <p class="mt10">※ 구매시 최소 또는 최대 구매 수량을 제한 할 수 있습니다.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 구매제한수량을 사용으로 변경했을 경우 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>구매제한수량 범위</span></th>
                                                <td colspan="3">
                                                   최소 <input class="w160" type="text" value="" required="required"> 개 부터 최대 <input class="w160" type="text" value="" required="required"> 개 까지 구매가능
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                  
                            <div class="con_w">
                                <div class="ttl01">배송비 선택</div>
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
                                                <th class="w160"><span>배송비 선택</span></th>
                                                <td colspan="3">
                                                    <select class="w310" required="required">
                                                        <option value="" disabled="" selected="">무료배송(조건식)</option>
                                                        <option value="1">무료배송(조건식)1</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                  
                            <div class="con_w">
                                <div class="ttl01">발주 담당자 정보</div>
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
                                                <th class="w160"><span>발주 담당자 설정</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio5" id="radio5_1" checked="">
                                                            <label for="radio5_1">설정안함</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio5" id="radio5_2">
                                                            <label for="radio5_2">설정</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- 발주 담당자 설정 선택시 -->
                                <div class="tb01 bN">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>발주 담당자 선택</span></th>
                                                <td colspan="3">
                                                    <div class="r_btn_w w457">
                                                        <input type="text" value="" required="required">
                                                        <a href="./" class="btn01 bold pop_btn" data-pop="pop1">담당찾기</a>
                                                    </div>
                                                    
                                                    <div class="popup_bx" data-id="pop1">
                                                        <div class="pop_w">
                                                            <div class="pop_inner">
                                                                <div class="pop_con w640">
                                                                    <div class="close_btn close1">닫기</div>
                                                                    <div class="page_info type2">
                                                                        <div class="ttl">발주담당자 찾기</div>
                                                                    </div>

                                                                    <div class="conbx">
                                                                        <div class="con_w">
                                                                            <div class="tb01 bN">
                                                                                <table>
                                                                                    <tbody class="textL">
                                                                                        <tr>
                                                                                            <td class="pt0 pr0 pl0">
                                                                                                <div class="r_btn_w">
                                                                                                    <input type="text" value="" required="required">
                                                                                                    <a href="./" class="btn01 bold pop_btn" data-pop="pop1">검색</a>
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>
                                                                                                <ul class="list_st01">
                                                                                                    <li>
                                                                                                        홍길동 ( abc@abc.co.kr )
                                                                                                        <button class="btn">선택</button>
                                                                                                    </li>
                                                                                                    <li>
                                                                                                        성춘향 ( test1234@naver.com )
                                                                                                        <button class="btn">선택</button>
                                                                                                    </li>
                                                                                                    <li>
                                                                                                        이목룡 ( dsdsfds@gmail.com )
                                                                                                        <button class="btn">선택</button>
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
                                                                        <a href="./" class="col5 close_btn">닫기</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="btm_btn mt10">
                                    <a href="../sub02/product_own.php" class="col5">목록</a>
                                    <a href="./">등록</a>
                                </div>
                                
                                <!-- 팝업 -->
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
        </script>
<?php include __DIR__ ."/../inc/footer.php"; ?>