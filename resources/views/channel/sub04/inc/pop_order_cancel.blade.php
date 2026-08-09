<div class="popup_bx" data-id="pop1_3">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">주문 상세보기</div>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="javascript:void(0);" data-pop="pop1_1"><span>주문 정보</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_2"><span>정상 주문</span></a></li>
                        <li><a href="javascript:void(0);" class="on"><span>취소 주문</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_4"><span>반품 주문</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_5"><span>교환 주문</span></a></li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01">취소 주문 정보 (1)</div>
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
                                        <th class="w160"><span>주문번호</span></th>
                                        <td id="pop_cancel_order_no">Me9-000939393</td>
                                        <th class="w160"><span>취소 주문번호</span></th>
                                        <td id="pop_cancel_claim_no">Me9-12222112121</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>취소 요청일시</span></th>
                                        <td id="pop_cancel_request_date">2024-10-01 10:02:12</td>
                                        <th class="w160"><span>취소 사유</span></th>
                                        <td id="pop_cancel_reason">동일 상품 재주문</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>환불 금액</span></th>
                                        <td id="pop_cancel_refund_amount">90,000 원</td>
                                        <th class="w160"><span>환불 계좌 정보</span></th>
                                        <td id="pop_cancel_refund_account">카카오뱅크 / 33333333301222 / 홍길동</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td colspan="3" id="pop_cancel_shop_name">Shop채널명</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="70px">
                                    <col width="100px">
                                    <col width="300px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"></th>
                                        <th>주문상태</th>
                                        <th>상품명</th>
                                        <th>상품코드</th>
                                        <th>옵션</th>
                                        <th>주문수량</th>
                                        <th>취소수량</th>
                                        <th>반품수량</th>
                                        <th>교환수량</th>
                                        <th>판매금액</th>
                                        <th>상품금액</th>
                                        <th>이익금</th>
                                        <th>판매이익</th>
                                        <th>배송비</th>
                                    </tr>
                                </thead>
                                <tbody id="pop_cancel_order_items_body">
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td>배송대기</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>2</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td class="t_r">45,000 원</td>
                                        <td class="t_r">25,000 원</td>
                                        <td class="t_r">2,500 원</td>
                                        <td class="t_r">17,500 원</td>
                                        <td class="t_r">0 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                        <div class="mt10">
                            <a href="javascript:void(0);" class="btn01 col3 pop_btn" data-pop="pop1_3_2">취소 거부</a>
                            <a href="javascript:void(0);" class="btn01 pop_btn" data-pop="pop1_3_3">취소 완료</a>
                        </div>
                    </div>
                </div>


                <div class="btm_btn mt20">
                    <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 취소 주문 팝업 ==> 취소 거부 팝업 -->
<div class="popup_bx" data-id="pop1_3_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">취소 거부 시 해당 주문은 취소 요청 이전 상태로 변경됩니다. <br>취소거부 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2">취소요청 상태에서만 취소거부 상태로 전환이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="javascript:void(0);" id="btn_cancel_reject_confirm">확인</a>
                    <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 취소 주문 팝업 ==> 취소 완료 팝업 -->
<div class="popup_bx" data-id="pop1_3_3">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">취소완료 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2 mt0">취소요청 상태에서만 취소완료 상태로 전환이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="javascript:void(0);" id="btn_cancel_approve_confirm">확인</a>
                    <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>
