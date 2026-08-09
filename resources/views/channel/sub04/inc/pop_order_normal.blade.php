<div class="popup_bx" data-id="pop1_2">
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
                        <li><a href="javascript:void(0);" class="on"><span>정상 주문</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_3"><span>취소 주문</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_4"><span>반품 주문</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_5"><span>교환 주문</span></a></li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01">정상주문정보</div>
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
                                        <td id="pop_normal_order_no">Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td id="pop_normal_order_date">2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td id="pop_normal_shop_name">Shop채널명</td>
                                        <th class="w160"><span>결제일시</span></th>
                                        <td id="pop_normal_payment_date">2024-10-01 10:02:12</td>
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
                                <tbody id="pop_normal_order_items_body">
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
                            <a href="javascript:void(0);" class="btn01 col3 pop_btn" data-pop="pop1_2_2">결제완료</a>
                            <a href="javascript:void(0);" class="btn01 col3 pop_btn" data-pop="pop1_2_3">배송대기</a>
                            <a href="javascript:void(0);" class="btn01 col3 pop_btn" data-pop="pop1_2_3">배송중</a>
                            <a href="javascript:void(0);" class="btn01 pop_btn" data-pop="pop1_2_4">반품 요청</a>
                            <a href="javascript:void(0);" class="btn01 pop_btn" data-pop="pop1_2_5">교환 요청</a>
                            <a href="javascript:void(0);" class="btn01 pop_btn" data-pop="pop1_2_6">취소 요청</a>
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

<!-- 정상 주문 팝업 ==> 결제완료 팝업 -->
<div class="popup_bx" data-id="pop1_2_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">결제완료 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2 mt0">입금대기 상태에서만 결제완료 상태로 전환이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="javascript:void(0);">확인</a>
                    <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 배송대기, 배송중 팝업 -->
<div class="popup_bx" data-id="pop1_2_3" id="modal_shipping_update">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">주문 배송정보 관리</div>
                </div>
                <form id="form_shipping_update">
                    <input type="hidden" name="order_id" id="shipping_order_id" value="">
                    <input type="hidden" name="status" value="shipping">
                    <div class="conbx">
                        <div class="con_w">
                            <div class="imp_bx01">
                                <div class="txt2 mt0">
                                    배송대기 / 배송중 상품만 배송정보를 관리 할 수 있습니다. <br>
                                    변경하기가 완료되면 배송중으로 상태가 변경됩니다.
                                </div>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">주문상품목록</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="70px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="100px">
                                        <col width="100px">
                                        <col width="80px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox"></th>
                                            <th>주문상태</th>
                                            <th>상품명</th>
                                            <th>상품코드</th>
                                            <th>옵션</th>
                                            <th>주문수량</th>
                                        </tr>
                                    </thead>
                                    <tbody id="shipping_order_items">
                                        <!-- JS를 통해 항목이 채워짐 -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">배송정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>택배사</span></th>
                                            <td>
                                                <select class="w200" name="courier_name" required="required">
                                                    <option value="" disabled="" selected="">택배사 선택</option>
                                                    <option value="CJ대한통운">CJ대한통운</option>
                                                    <option value="우체국택배">우체국택배</option>
                                                    <option value="한진택배">한진택배</option>
                                                    <option value="로젠택배">로젠택배</option>
                                                    <option value="롯데택배">롯데택배</option>
                                                    <option value="직접배송">직접배송</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>송장번호</span></th>
                                            <td>
                                                <input type="text" name="tracking_number" value="" required="required">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt10">
                        <a href="javascript:void(0);" class="btn_submit"
                            onclick="submitOrderForm('form_shipping_update', '{{ route('channel.order.status.update') }}'); return false;">변경하기</a>
                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 반품요청 팝업 -->
<div class="popup_bx" data-id="pop1_2_4" id="modal_return_request">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">반품요청</div>
                </div>
                <form id="form_return_request">
                    <input type="hidden" name="order_id" id="return_order_id" value="">
                    <div class="conbx">
                        <div class="con_w">
                            <div class="imp_bx01">
                                <div class="txt2 mt0">
                                    배송중 상품만 반품요청으로 관리 할 수 있습니다. <br>
                                    반품요청을 진행하면 새로운 주문번호가 생성되면서 반품이 진행됩니다.
                                </div>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">주문정보</div>
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
                                            <td id="return_order_no">Me9-000939393</td>
                                            <th class="w160"><span>주문일시</span></th>
                                            <td id="return_order_date">2024-10-01 09:02:12</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>판매처</span></th>
                                            <td id="return_shop_name">Shop채널명</td>
                                            <th class="w160"><span>결제일시</span></th>
                                            <td id="return_payment_date">2024-10-01 09:02:12</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tb01 mt10">
                                <table>
                                    <colgroup>
                                        <col width="70px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="100px">
                                        <col width="100px">
                                        <col width="80px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox"></th>
                                            <th>주문상태</th>
                                            <th>상품명</th>
                                            <th>상품코드</th>
                                            <th>옵션</th>
                                            <th>주문수량</th>
                                        </tr>
                                    </thead>
                                    <tbody id="return_order_items">
                                        <!-- JS를 통해 항목이 채워짐 -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">반품정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>반품지</span></th>
                                            <td colspan="3">(06236) 서울특별시 강남구 테헤란로 152(역삼동) test</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>반품 사유</span></th>
                                            <td colspan="3">
                                                <select name="reason" required="required">
                                                    <option value="" disabled="" selected="">반품사유 선택</option>
                                                    <option value="mind_change">마음이 변했어요</option>
                                                    <option value="product_defect">상품에 하자가 있어요(상세 사유 필요)</option>
                                                    <option value="wrong_delivery">다른 상품이 배송됐어요(상세 사유 필요)</option>
                                                </select>
                                                <textarea class="mt5" name="detail_reason" required="required"
                                                    placeholder="상세 사유를 입력해 주세요 (필수)"></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>회수 방법</span></th>
                                            <td colspan="3">
                                                <select name="return_method" required="required">
                                                    <option value="" disabled="" selected="">회수 방법 선택</option>
                                                    <option value="self_return">고객 직접 회수</option>
                                                    <option value="pickup">업체 회수</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <!-- 회수 방법 => 고객 직접 회수 -->
                                        <tr>
                                            <th class="w160"><span>회수 정보</span></th>
                                            <td class="pr0">택배사 정보</td>
                                            <td colspan="2">
                                                <div class="search_w01">
                                                    <select name="courier_name" required="required">
                                                        <option value="" disabled="" selected="">택배사 선택</option>
                                                        <option value="CJ대한통운">CJ대한통운</option>
                                                        <option value="우체국택배">우체국택배</option>
                                                    </select>
                                                    <input type="text" name="tracking_number" value=""
                                                        required="required">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt10">
                        <a href="javascript:void(0);" class="btn_submit"
                            onclick="submitOrderForm('form_return_request', '{{ route('channel.order.return.request') }}'); return false;">반품요청하기</a>
                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 교환신청 팝업 -->
<div class="popup_bx" data-id="pop1_2_5" id="modal_exchange_request">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">교환신청</div>
                </div>
                <form id="form_exchange_request">
                    <input type="hidden" name="order_id" id="exchange_order_id" value="">
                    <div class="conbx">
                        <div class="con_w">
                            <div class="imp_bx01">
                                <div class="txt2 mt0">
                                    배송중 상품만 교환요청으로 관리 할 수 있습니다. <br>
                                    교환요청을 진행하면 새로운 주문번호가 생성되면서 교환이 진행됩니다.
                                </div>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">주문정보</div>
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
                                            <td id="exchange_order_no">Me9-000939393</td>
                                            <th class="w160"><span>주문일시</span></th>
                                            <td id="exchange_order_date">2024-10-01 09:02:12</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>판매처</span></th>
                                            <td id="exchange_shop_name">Shop채널명</td>
                                            <th class="w160"><span>결제일시</span></th>
                                            <td id="exchange_payment_date">2024-10-01 09:02:12</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tb01 mt10">
                                <table>
                                    <colgroup>
                                        <col width="70px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="100px">
                                        <col width="100px">
                                        <col width="80px">
                                        <col width="130px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox"></th>
                                            <th>주문상태</th>
                                            <th>상품명</th>
                                            <th>상품코드</th>
                                            <th>옵션</th>
                                            <th>주문수량</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="exchange_order_items">
                                        <!-- JS를 통해 항목이 채워짐 -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">반품정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>교환지</span></th>
                                            <td colspan="3">(06236) 서울특별시 강남구 테헤란로 152(역삼동) test</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>교환 사유</span></th>
                                            <td colspan="3">
                                                <select name="reason" required="required">
                                                    <option value="" disabled="" selected="">교환사유 선택</option>
                                                    <option value="mind_change">마음이 변했어요</option>
                                                    <option value="product_defect">상품에 하자가 있어요(상세 사유 필요)</option>
                                                    <option value="wrong_delivery">다른 상품이 배송됐어요(상세 사유 필요)</option>
                                                </select>
                                                <textarea class="mt5" name="detail_reason" required="required"
                                                    placeholder="상세 사유를 입력해 주세요 (필수)"></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>회수 방법</span></th>
                                            <td colspan="3">
                                                <select name="return_method" required="required">
                                                    <option value="" disabled="" selected="">회수 방법 선택</option>
                                                    <option value="self_return">고객 직접 회수</option>
                                                    <option value="pickup">업체 회수</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <!-- 회수 방법 => 고객 직접 회수 -->
                                        <tr>
                                            <th class="w160"><span>회수 정보</span></th>
                                            <td class="pr0">택배사 정보</td>
                                            <td colspan="2">
                                                <div class="search_w01">
                                                    <select name="courier_name" required="required">
                                                        <option value="" disabled="" selected="">택배사 선택</option>
                                                        <option value="CJ대한통운">CJ대한통운</option>
                                                        <option value="우체국택배">우체국택배</option>
                                                    </select>
                                                    <input type="text" name="tracking_number" value=""
                                                        required="required">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt10">
                        <a href="javascript:void(0);" class="btn_submit"
                            onclick="submitOrderForm('form_exchange_request', '{{ route('channel.order.exchange.request') }}'); return false;">교환요청하기</a>
                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 취소요청 팝업 -->
<div class="popup_bx" data-id="pop1_2_6" id="modal_cancel_request">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">취소요청</div>
                </div>
                <form id="form_cancel_request">
                    <input type="hidden" name="order_id" id="cancel_order_id" value="">
                    <div class="conbx">
                        <div class="con_w">
                            <div class="imp_bx01">
                                <div class="txt2 mt0">
                                    입금대기,결제완료,배송대기 상품만 취소요청 상태로 변경 할 수 있습니다. <br>
                                    주문 취소를 요청하면 새로운 주문번호가 생성되면서 주문취소가 진행됩니다.
                                </div>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">주문정보</div>
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
                                            <td id="cancel_order_no">Me9-000939393</td>
                                            <th class="w160"><span>주문일시</span></th>
                                            <td id="cancel_order_date">2024-10-01 09:02:12</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>판매처</span></th>
                                            <td id="cancel_shop_name">Shop채널명</td>
                                            <th class="w160"><span>결제일시</span></th>
                                            <td id="cancel_payment_date">2024-10-01 09:02:12</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tb01 mt10">
                                <table>
                                    <colgroup>
                                        <col width="70px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="100px">
                                        <col width="100px">
                                        <col width="80px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox"></th>
                                            <th>주문상태</th>
                                            <th>상품명</th>
                                            <th>상품코드</th>
                                            <th>옵션</th>
                                            <th>주문수량</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cancel_order_items">
                                        <!-- Items populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">취소정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>취소 사유</span></th>
                                            <td colspan="3">
                                                <select name="reason" required="required">
                                                    <option value="" disabled="" selected="">취소사유 선택</option>
                                                    <option value="mind_change">마음이 변했어요</option>
                                                    <option value="product_defect">상품에 하자가 있어요(상세 사유 필요)</option>
                                                    <option value="wrong_delivery">다른 상품이 배송됐어요(상세 사유 필요)</option>
                                                </select>
                                                <textarea class="mt5" name="detail_reason" required="required"
                                                    placeholder="상세 사유를 입력해 주세요 (필수)"></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>환불금액</span></th>
                                            <td colspan="3"><span id="cancel_refund_amount">0</span> 원</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt10">
                        <a href="javascript:void(0);" class="btn_submit"
                            onclick="submitOrderForm('form_cancel_request', '{{ route('channel.order.cancel.request') }}'); return false;">주문취소
                            요청하기</a>
                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
