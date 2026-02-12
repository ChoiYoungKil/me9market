<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "00";
	$dep1_tit = "배송비설정";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
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
                                        <a href="#" class="btn01 col5 pop_btn" data-pop="pop1_1">배송비 등록</a>
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
                                <!-- 배송비 등록 팝업 -->
                                <div class="popup_bx" data-id="pop1_1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">배송비 등록</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="160px">
                                                                    <col width="120px">
                                                                    <col width="160px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>배송구분</span></th>
                                                                        <td colspan="3">사용자</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_1" checked="">
                                                                                    <label for="pop1_1_radio1">사용</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_2">
                                                                                    <label for="pop1_1_radio1_2">중지</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 명칭<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                            <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지정택배사</span></th>
                                                                        <td colspan="3">
                                                                            <select class="w310" required="required">
                                                                                <option value="" disabled="" selected="">지정택배사 선택</option>
                                                                                <option value="1">자체배송</option>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_1" checked="">
                                                                                    <label for="pop1_1_radio2_1">무료배송</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_2">
                                                                                    <label for="pop1_1_radio2_2">무료배송 (조건부)</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_3">
                                                                                    <label for="pop1_1_radio2_3">고정 배송비</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_4">
                                                                                    <label for="pop1_1_radio2_4">변동 배송비</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_5">
                                                                                    <label for="pop1_1_radio2_5">구간별 배송비</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 무료배송 (조건부)</span></th>
                                                                        <td colspan="3">
                                                                            기본배송비 &nbsp;&nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            <ul class="chk02 mt5 disb">
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2" id="pop1_1_chk2_1" checked="">
                                                                                    <label for="pop1_1_chk2_1">
                                                                                        조건 1 &nbsp;<input class="w160" type="text" value="" required="required"> 원 이상 구매시 배송비 무료
                                                                                    </label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2" id="pop1_1_chk2_2">
                                                                                    <label for="pop1_1_chk2_2">
                                                                                        조건 2 &nbsp;<input class="w160" type="text" value="" required="required"> 개 이상 구매시 배송비 무료
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 고정 배송비</span></th>
                                                                        <td colspan="3">
                                                                            구매 금액에 상관없이 <input class="w160" type="text" value="" required="required"> 원을 고정적으로 부과
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 변동 배송비</span></th>
                                                                        <td colspan="3">
                                                                            기본배송비 &nbsp;&nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 기본배송비가 0 일 경우 반복 부과 금액만 계산됩니다.</span>
                                                                            <ul class="chk01 mt10 disb">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_4_1" id="pop1_1_radio2_4_1_1" checked="">
                                                                                    <label for="pop1_1_radio2_4_1_1">
                                                                                        조건 1 &nbsp;<input class="w160" type="text" value="" required="required"> 개 마다 기본 배송비에 <input class="w160" type="text" value="" required="required"> 원 반복 부과
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                            <ul class="chk02 mt10 disb pl70">
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2_4_1" id="pop1_1_chk2_4_1">
                                                                                    <label for="pop1_1_chk2_4_1">반복 부과 기준을 1부터 시작</label>
                                                                                </li>
                                                                            </ul>
                                                                            <ul class="chk01 mt10 disb">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_4_1" id="pop1_1_radio2_4_1_2">
                                                                                    <label for="pop1_1_radio2_4_1_2">
                                                                                        조건 2 &nbsp;<input class="w160" type="text" value="" required="required"> 원 마다 기본 배송비에 <input class="w160" type="text" value="" required="required"> 원 반복 부과
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                            <ul class="chk02 mt10 disb">
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2_4_2" id="pop1_1_chk2_4_2">
                                                                                    <label for="pop1_1_chk2_4_2">지역별 배송비를 동일한 조건으로 반복 부과</label>
                                                                                </li>
                                                                            </ul>
                                                                            <p class="fcol6 fs mt15">
                                                                                - 반복해서 부과되는 금액은 기본 배송비와 합산됩니다.<br>
                                                                                - 지역별 배송비는 기본 1회 자동 부과되며 이후 반복 조건에 따라 부과됩니다.<br>
                                                                                - 기본배송비를 0원으로 입력할 경우 설정하신 조건에 따라 반복 부과 금액만 처리됩니다.
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 구간별 배송비</span></th>
                                                                        <td colspan="3">
                                                                            · 구매조건선택 &nbsp;&nbsp;
                                                                            <ul class="chk01 disi">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_5_1" id="pop1_1_radio2_5_1_1" checked="">
                                                                                    <label for="pop1_1_radio2_5_1_1">금액별</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_5_1" id="pop1_1_radio2_5_1_2">
                                                                                    <label for="pop1_1_radio2_5_1_2">수량별</label>
                                                                                </li>
                                                                            </ul>
                                                                            
                                                                            <!-- 금액별 -->
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 이상 ~ <input class="w160" type="text" value="" required="required"> 원 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col2">추가</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 이상 ~ <input class="w160" type="text" value="" required="required"> 원 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col7">삭제</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 이상, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 마지막 금액, 자동 입력</span>
                                                                            </p>
                                                                            
                                                                            <!-- 수량별 -->
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 개 이상 ~ <input class="w160" type="text" value="" required="required"> 개 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col2">추가</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 개 이상 ~ <input class="w160" type="text" value="" required="required"> 개 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col7">삭제</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 개 이상, 배송비 <input class="w160" type="text" value="" required="required"> 개 부과 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 마지막 금액, 자동 입력</span>
                                                                            </p>
                                                                            
                                                                            
                                                                            <p class="fcol6 fs mt15">
                                                                                - 구간별 배송비는 해당 구간만 적용되며, 다른 구간의 배송비 금액은 합산되지 않습니다.<br>
                                                                                - 지역별 배송비는 지역별 배송비 조건에 따라 별도 부과될 수 있습니다.<br>
                                                                                - 배송비 금액을 0원으로 입력할 경우 무료배송 처리됩니다.
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 결제</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio3" id="pop1_1_radio3_1" checked="">
                                                                                    <label for="pop1_1_radio3_1">선결제</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio3" id="pop1_1_radio3_2">
                                                                                    <label for="pop1_1_radio3_2">착불</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160" rowspan="2"><span>지역별 배송</span></th>
                                                                        <td class="pr0">- 제주 지역</td>
                                                                        <td colspan="2">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_1" id="pop1_1_radio4_1_1" checked="">
                                                                                    <label for="pop1_1_radio4_1_1">배송함</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_1" id="pop1_1_radio4_1_2">
                                                                                    <label for="pop1_1_radio4_1_2">배송안함</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="pr0">- 도서산간 지역</td>
                                                                        <td colspan="2">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_2" id="pop1_1_radio4_2_1" checked="">
                                                                                    <label for="pop1_1_radio4_2_1">배송함</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_2" id="pop1_1_radio4_2_2">
                                                                                    <label for="pop1_1_radio4_2_2">배송안함</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지역별 배송비</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio5" id="pop1_1_radio5_1" checked="">
                                                                                    <label for="pop1_1_radio5_1">사용안함</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio5" id="pop1_1_radio5_2">
                                                                                    <label for="pop1_1_radio5_2">배송권역 방식</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지역별 배송비 - <br>배송권역 방식</span></th>
                                                                        <td colspan="3">
                                                                            <select class="w160" required="required">
                                                                                <option value="" disabled="" selected="">배송권역 선택</option>
                                                                                <option value="1">2권역</option>
                                                                                <option value="1">3권역</option>
                                                                            </select>
                                                                            
                                                                            <!-- 2권역 -->
                                                                            &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 2권역 : 제주 + 도서산간지역 통합 / 3권역 : 제주, 도서산간지역 분리</span>
                                                                            <p class="mt5">
                                                                                · 제주/도서산간지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 제주/도서산간지역을 제외한 지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                                &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 제주도에서 배송되는 상품일 경우에 설정하실 수 있습니다.</span>
                                                                            </p>
                                                                            
                                                                            <!-- 3권역 -->
                                                                            <p class="mt5">
                                                                                · 제주지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 도서산간지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 제주/도서산간지역을 제외한 지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                                &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 제주도에서 배송되는 상품일 경우에 설정하실 수 있습니다.</span>
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="#">배송비 등록</a>
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
                                                    <div class="ttl">배송비 정보</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="ttl01">조건부 배송비 명칭</div>
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
                                                                        <th class="w160"><span>배송구분</span></th>
                                                                        <td colspan="3">사용자</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td colspan="3">사용</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지정택배사</span></th>
                                                                        <td colspan="3">한진 택배사</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160" rowspan="2"><span>배송비 유형</span></th>
                                                                        <td colspan="3">무료배송 ( 조건부 )</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3">
                                                                            · 기본 배송비 : 3,000 원<br>
                                                                            · 조건 1 : 30,000 원 이상 구매 시 배송비 무료
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 결제</span></th>
                                                                        <td colspan="3">선결제</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지역별 배송</span></th>
                                                                        <td colspan="3">
                                                                            · 제주지역 / 배송함<br>
                                                                            · 도서산간지역 / 배송함
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지역별 배송비</span></th>
                                                                        <td colspan="3">배송권역 방식</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송권역</span></th>
                                                                        <td colspan="3">
                                                                            2권역<br>
                                                                            · 제주/도서산간지역 추가 배송비 / 5,000 원<br>
                                                                            · 제주/도서산간지역을 제외한 지역 추가 배송비 / 3,000 원
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>연결 상품수</span></th>
                                                                        <td colspan="3">22</td>
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
                                                    <div class="ttl">배송비 수정</div>
                                                </div>
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="160px">
                                                                    <col width="120px">
                                                                    <col width="160px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>배송구분</span></th>
                                                                        <td colspan="3">사용자</td>
                                                                    </tr>
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
                                                                        <th class="w160"><span>배송비 명칭<em>필수</em></span></th>
                                                                        <td colspan="3">
                                                                            <input type="text" value="" required="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지정택배사</span></th>
                                                                        <td colspan="3">
                                                                            <select class="w310" required="required">
                                                                                <option value="" disabled="" selected="">지정택배사 선택</option>
                                                                                <option value="1">자체배송</option>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_1" checked="">
                                                                                    <label for="pop1_1_radio2_1">무료배송</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_2">
                                                                                    <label for="pop1_1_radio2_2">무료배송 (조건부)</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_3">
                                                                                    <label for="pop1_1_radio2_3">고정 배송비</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_4">
                                                                                    <label for="pop1_1_radio2_4">변동 배송비</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_5">
                                                                                    <label for="pop1_1_radio2_5">구간별 배송비</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 무료배송 (조건부)</span></th>
                                                                        <td colspan="3">
                                                                            기본배송비 &nbsp;&nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            <ul class="chk02 mt5 disb">
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2" id="pop1_1_chk2_1" checked="">
                                                                                    <label for="pop1_1_chk2_1">
                                                                                        조건 1 &nbsp;<input class="w160" type="text" value="" required="required"> 원 이상 구매시 배송비 무료
                                                                                    </label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2" id="pop1_1_chk2_2">
                                                                                    <label for="pop1_1_chk2_2">
                                                                                        조건 2 &nbsp;<input class="w160" type="text" value="" required="required"> 개 이상 구매시 배송비 무료
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 고정 배송비</span></th>
                                                                        <td colspan="3">
                                                                            구매 금액에 상관없이 <input class="w160" type="text" value="" required="required"> 원을 고정적으로 부과
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 변동 배송비</span></th>
                                                                        <td colspan="3">
                                                                            기본배송비 &nbsp;&nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 기본배송비가 0 일 경우 반복 부과 금액만 계산됩니다.</span>
                                                                            <ul class="chk01 mt10 disb">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_4_1" id="pop1_1_radio2_4_1_1" checked="">
                                                                                    <label for="pop1_1_radio2_4_1_1">
                                                                                        조건 1 &nbsp;<input class="w160" type="text" value="" required="required"> 개 마다 기본 배송비에 <input class="w160" type="text" value="" required="required"> 원 반복 부과
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                            <ul class="chk02 mt10 disb pl70">
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2_4_1" id="pop1_1_chk2_4_1">
                                                                                    <label for="pop1_1_chk2_4_1">반복 부과 기준을 1부터 시작</label>
                                                                                </li>
                                                                            </ul>
                                                                            <ul class="chk01 mt10 disb">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_4_1" id="pop1_1_radio2_4_1_2">
                                                                                    <label for="pop1_1_radio2_4_1_2">
                                                                                        조건 2 &nbsp;<input class="w160" type="text" value="" required="required"> 원 마다 기본 배송비에 <input class="w160" type="text" value="" required="required"> 원 반복 부과
                                                                                    </label>
                                                                                </li>
                                                                            </ul>
                                                                            <ul class="chk02 mt10 disb">
                                                                                <li>
                                                                                    <input type="checkbox" name="pop1_1_chk2_4_2" id="pop1_1_chk2_4_2">
                                                                                    <label for="pop1_1_chk2_4_2">지역별 배송비를 동일한 조건으로 반복 부과</label>
                                                                                </li>
                                                                            </ul>
                                                                            <p class="fcol6 fs mt15">
                                                                                - 반복해서 부과되는 금액은 기본 배송비와 합산됩니다.<br>
                                                                                - 지역별 배송비는 기본 1회 자동 부과되며 이후 반복 조건에 따라 부과됩니다.<br>
                                                                                - 기본배송비를 0원으로 입력할 경우 설정하신 조건에 따라 반복 부과 금액만 처리됩니다.
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 유형 - <br> 구간별 배송비</span></th>
                                                                        <td colspan="3">
                                                                            · 구매조건선택 &nbsp;&nbsp;
                                                                            <ul class="chk01 disi">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_5_1" id="pop1_1_radio2_5_1_1" checked="">
                                                                                    <label for="pop1_1_radio2_5_1_1">금액별</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio2_5_1" id="pop1_1_radio2_5_1_2">
                                                                                    <label for="pop1_1_radio2_5_1_2">수량별</label>
                                                                                </li>
                                                                            </ul>
                                                                            
                                                                            <!-- 금액별 -->
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 이상 ~ <input class="w160" type="text" value="" required="required"> 원 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col2">추가</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 이상 ~ <input class="w160" type="text" value="" required="required"> 원 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col7">삭제</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원 이상, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 마지막 금액, 자동 입력</span>
                                                                            </p>
                                                                            
                                                                            <!-- 수량별 -->
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 개 이상 ~ <input class="w160" type="text" value="" required="required"> 개 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col2">추가</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 개 이상 ~ <input class="w160" type="text" value="" required="required"> 개 미만, 배송비 <input class="w160" type="text" value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#" class="btn02 col7">삭제</a>
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 개 이상, 배송비 <input class="w160" type="text" value="" required="required"> 개 부과 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 마지막 금액, 자동 입력</span>
                                                                            </p>
                                                                            
                                                                            
                                                                            <p class="fcol6 fs mt15">
                                                                                - 구간별 배송비는 해당 구간만 적용되며, 다른 구간의 배송비 금액은 합산되지 않습니다.<br>
                                                                                - 지역별 배송비는 지역별 배송비 조건에 따라 별도 부과될 수 있습니다.<br>
                                                                                - 배송비 금액을 0원으로 입력할 경우 무료배송 처리됩니다.
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>배송비 결제</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio3" id="pop1_1_radio3_1" checked="">
                                                                                    <label for="pop1_1_radio3_1">선결제</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio3" id="pop1_1_radio3_2">
                                                                                    <label for="pop1_1_radio3_2">착불</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160" rowspan="2"><span>지역별 배송</span></th>
                                                                        <td class="pr0">- 제주 지역</td>
                                                                        <td colspan="2">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_1" id="pop1_1_radio4_1_1" checked="">
                                                                                    <label for="pop1_1_radio4_1_1">배송함</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_1" id="pop1_1_radio4_1_2">
                                                                                    <label for="pop1_1_radio4_1_2">배송안함</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="pr0">- 도서산간 지역</td>
                                                                        <td colspan="2">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_2" id="pop1_1_radio4_2_1" checked="">
                                                                                    <label for="pop1_1_radio4_2_1">배송함</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio4_2" id="pop1_1_radio4_2_2">
                                                                                    <label for="pop1_1_radio4_2_2">배송안함</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지역별 배송비</span></th>
                                                                        <td colspan="3">
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio5" id="pop1_1_radio5_1" checked="">
                                                                                    <label for="pop1_1_radio5_1">사용안함</label>
                                                                                </li>
                                                                                <li>
                                                                                    <input type="radio" name="pop1_1_radio5" id="pop1_1_radio5_2">
                                                                                    <label for="pop1_1_radio5_2">배송권역 방식</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>지역별 배송비 - <br>배송권역 방식</span></th>
                                                                        <td colspan="3">
                                                                            <select class="w160" required="required">
                                                                                <option value="" disabled="" selected="">배송권역 선택</option>
                                                                                <option value="1">2권역</option>
                                                                                <option value="1">3권역</option>
                                                                            </select>
                                                                            
                                                                            <!-- 2권역 -->
                                                                            &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 2권역 : 제주 + 도서산간지역 통합 / 3권역 : 제주, 도서산간지역 분리</span>
                                                                            <p class="mt5">
                                                                                · 제주/도서산간지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 제주/도서산간지역을 제외한 지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                                &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 제주도에서 배송되는 상품일 경우에 설정하실 수 있습니다.</span>
                                                                            </p>
                                                                            
                                                                            <!-- 3권역 -->
                                                                            <p class="mt5">
                                                                                · 제주지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 도서산간지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                            </p>
                                                                            <p class="mt5">
                                                                                · 제주/도서산간지역을 제외한 지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value="" required="required"> 원
                                                                                &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 제주도에서 배송되는 상품일 경우에 설정하실 수 있습니다.</span>
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 하단버튼 -->
                                                <div class="btm_btn mt10">
                                                    <a href="#">배송비 수정</a>
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