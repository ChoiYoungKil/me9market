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
                                                    <input type="radio" name="pop1_1_radio1" id="pop1_1_radio1_1"
                                                        checked="">
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
                                                    <input type="radio" name="pop1_1_radio2" id="pop1_1_radio2_1"
                                                        checked="">
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
                                            기본배송비 &nbsp;&nbsp;&nbsp;<input class="w160" type="text" value=""
                                                required="required"> 원
                                            <ul class="chk02 mt5 disb">
                                                <li>
                                                    <input type="checkbox" name="pop1_1_chk2" id="pop1_1_chk2_1"
                                                        checked="">
                                                    <label for="pop1_1_chk2_1">
                                                        조건 1 &nbsp;<input class="w160" type="text" value=""
                                                            required="required"> 원 이상 구매시 배송비 무료
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="pop1_1_chk2" id="pop1_1_chk2_2">
                                                    <label for="pop1_1_chk2_2">
                                                        조건 2 &nbsp;<input class="w160" type="text" value=""
                                                            required="required"> 개 이상 구매시 배송비 무료
                                                    </label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>배송비 유형 - <br> 고정 배송비</span></th>
                                        <td colspan="3">
                                            구매 금액에 상관없이 <input class="w160" type="text" value="" required="required"> 원을
                                            고정적으로 부과
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>배송비 유형 - <br> 변동 배송비</span></th>
                                        <td colspan="3">
                                            기본배송비 &nbsp;&nbsp;&nbsp;<input class="w160" type="text" value=""
                                                required="required"> 원 &nbsp;&nbsp;&nbsp;<span class="fcol6 fs">ⓘ 기본배송비가
                                                0 일 경우 반복 부과 금액만 계산됩니다.</span>
                                            <ul class="chk01 mt10 disb">
                                                <li>
                                                    <input type="radio" name="pop1_1_radio2_4_1"
                                                        id="pop1_1_radio2_4_1_1" checked="">
                                                    <label for="pop1_1_radio2_4_1_1">
                                                        조건 1 &nbsp;<input class="w160" type="text" value=""
                                                            required="required"> 개 마다 기본 배송비에 <input class="w160"
                                                            type="text" value="" required="required"> 원 반복 부과
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
                                                    <input type="radio" name="pop1_1_radio2_4_1"
                                                        id="pop1_1_radio2_4_1_2">
                                                    <label for="pop1_1_radio2_4_1_2">
                                                        조건 2 &nbsp;<input class="w160" type="text" value=""
                                                            required="required"> 원 마다 기본 배송비에 <input class="w160"
                                                            type="text" value="" required="required"> 원 반복 부과
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
                                                    <input type="radio" name="pop1_1_radio2_5_1"
                                                        id="pop1_1_radio2_5_1_1" checked="">
                                                    <label for="pop1_1_radio2_5_1_1">금액별</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="pop1_1_radio2_5_1"
                                                        id="pop1_1_radio2_5_1_2">
                                                    <label for="pop1_1_radio2_5_1_2">수량별</label>
                                                </li>
                                            </ul>

                                            <!-- 금액별 -->
                                            <p class="mt5">
                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 원 이상 ~ <input class="w160" type="text" value=""
                                                    required="required"> 원 미만, 배송비 <input class="w160" type="text"
                                                    value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#"
                                                    class="btn02 col2">추가</a>
                                            </p>
                                            <p class="mt5">
                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 원 이상 ~ <input class="w160" type="text" value=""
                                                    required="required"> 원 미만, 배송비 <input class="w160" type="text"
                                                    value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#"
                                                    class="btn02 col7">삭제</a>
                                            </p>
                                            <p class="mt5">
                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 원 이상, 배송비 <input class="w160" type="text"
                                                    value="" required="required"> 원 부과 &nbsp;&nbsp;&nbsp;<span
                                                    class="fcol6 fs">ⓘ 마지막 금액, 자동 입력</span>
                                            </p>

                                            <!-- 수량별 -->
                                            <p class="mt5">
                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 개 이상 ~ <input class="w160" type="text" value=""
                                                    required="required"> 개 미만, 배송비 <input class="w160" type="text"
                                                    value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#"
                                                    class="btn02 col2">추가</a>
                                            </p>
                                            <p class="mt5">
                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 개 이상 ~ <input class="w160" type="text" value=""
                                                    required="required"> 개 미만, 배송비 <input class="w160" type="text"
                                                    value="" required="required"> 원 부과 &nbsp;&nbsp;<a href="#"
                                                    class="btn02 col7">삭제</a>
                                            </p>
                                            <p class="mt5">
                                                · 상품 금액 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 개 이상, 배송비 <input class="w160" type="text"
                                                    value="" required="required"> 개 부과 &nbsp;&nbsp;&nbsp;<span
                                                    class="fcol6 fs">ⓘ 마지막 금액, 자동 입력</span>
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
                                                    <input type="radio" name="pop1_1_radio3" id="pop1_1_radio3_1"
                                                        checked="">
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
                                                    <input type="radio" name="pop1_1_radio4_1" id="pop1_1_radio4_1_1"
                                                        checked="">
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
                                                    <input type="radio" name="pop1_1_radio4_2" id="pop1_1_radio4_2_1"
                                                        checked="">
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
                                                    <input type="radio" name="pop1_1_radio5" id="pop1_1_radio5_1"
                                                        checked="">
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
                                            &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 2권역 : 제주 + 도서산간지역 통합 / 3권역 : 제주,
                                                도서산간지역 분리</span>
                                            <p class="mt5">
                                                · 제주/도서산간지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 원
                                            </p>
                                            <p class="mt5">
                                                · 제주/도서산간지역을 제외한 지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text"
                                                    value="" required="required"> 원
                                                &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 제주도에서 배송되는 상품일 경우에 설정하실 수
                                                    있습니다.</span>
                                            </p>

                                            <!-- 3권역 -->
                                            <p class="mt5">
                                                · 제주지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 원
                                            </p>
                                            <p class="mt5">
                                                · 도서산간지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text" value=""
                                                    required="required"> 원
                                            </p>
                                            <p class="mt5">
                                                · 제주/도서산간지역을 제외한 지역 추가 배송비 &nbsp;&nbsp;<input class="w160" type="text"
                                                    value="" required="required"> 원
                                                &nbsp;&nbsp; <span class="fcol6 fs">ⓘ 제주도에서 배송되는 상품일 경우에 설정하실 수
                                                    있습니다.</span>
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