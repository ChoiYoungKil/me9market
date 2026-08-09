<div class="popup_bx" data-id="pop1_1">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">주문 상세보기</div>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ url()->current() }}" class="on"><span>주문 정보</span></a></li>
                        <li><a href="{{ url()->current() }}" data-pop="pop1_2"><span>정상 주문</span></a></li>
                        <li><a href="{{ url()->current() }}" data-pop="pop1_3"><span>취소 주문</span></a></li>
                        <li><a href="{{ url()->current() }}" data-pop="pop1_4"><span>반품 주문</span></a></li>
                        <li><a href="{{ url()->current() }}" data-pop="pop1_5"><span>교환 주문</span></a></li>
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
                                        <th class="w160"><span>주문번호</span></th>
                                        <td id="pop_info_order_no">Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td id="pop_info_order_date">2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td id="pop_info_shop_name">Shop채널명</td>
                                        <th class="w160"><span>결제일시</span></th>
                                        <td id="pop_info_payment_date">2024-10-01 10:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>원 주문번호</span></th>
                                        <td colspan="3"><span class="bold fcol4" id="pop_info_orig_order_no">Me9-088776768</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">주문상품목록</div>

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="120px">
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
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>상품 유형</th>
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
                                        <th>사용포인트</th>
                                        <th>결제금액</th>
                                        <th>적립포인트</th>
                                        <th>주문상태</th>
                                        <th>택배사</th>
                                        <th>송장번호</th>
                                    </tr>
                                </thead>
                                <tbody id="pop_info_order_items_body">
                                    <tr>
                                        <td>자사</td>
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
                                        <td class="t_r">3,000 p</td>
                                        <td class="t_r"><span class="bold fcol4">42,000 원</span></td>
                                        <td class="t_r">4,200 p</td>
                                        <td>구매확정</td>
                                        <td>CJ 대한통운</td>
                                        <td>11827321</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">주문자 / 수취인 정보</div>
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
                                        <th class="w160"><span>회원정보</span></th>
                                        <td id="pop_info_user_email"><a class="fcol2 link" href="mailto:user@domain.com">user@domain.com</a>
                                            (A992029202)</td>
                                        <th class="w160"><span>주문자명</span></th>
                                        <td id="pop_info_user_name">홍길동</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주문자 연락처</span></th>
                                        <td id="pop_info_user_mobile">010-1122-3344</td>
                                        <th class="w160"><span>주문자 이메일</span></th>
                                        <td id="pop_info_user_email2"><a class="fcol2 link" href="mailto:user@domain.com">user@domain.com</a></td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>수취인</span></th>
                                        <td id="pop_info_recipient_name">홍길동</td>
                                        <th class="w160"><span>수취인 연락처</span></th>
                                        <td id="pop_info_recipient_mobile">010-1122-3344</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>배송지 주소</span></th>
                                        <td colspan="3" id="pop_info_recipient_address">[06151] 서울특별시 강남구 테헤란로 112233 (역삼동) 132435</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">주문 결제 정보</div>
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
                                        <th class="w160"><span>총 상품 판매가</span></th>
                                        <td id="pop_info_total_sale_price">90,000 원</td>
                                        <th class="w160"><span>총 상품 기본가</span></th>
                                        <td id="pop_info_total_product_price">50,000 원</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>총 이익금</span></th>
                                        <td id="pop_info_total_profit">5,000 원</td>
                                        <th class="w160"><span>총 판매이익금</span></th>
                                        <td id="pop_info_total_selling_profit">35,000 원</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>총 배송비</span></th>
                                        <td colspan="3" id="pop_info_delivery_fee">0 원</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>총 사용 포인트</span></th>
                                        <td id="pop_info_used_point">3,000 point</td>
                                        <th class="w160"><span>총 적립 포인트</span></th>
                                        <td id="pop_info_earned_point">90 point</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>최종 결제가</span></th>
                                        <td id="pop_info_total_payment_price">87,000 원</td>
                                        <th class="w160"><span>결제 수단</span></th>
                                        <td>
                                            <span id="pop_info_payment_method">카드</span> <a class="btn02 col5 ml10 pop_btn" data-pop="pop1_1_2">연동 로그</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">주문 상태 변경 정보</div>
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
                                        <th class="w160"><span>변경 내역</span></th>
                                        <td colspan="3">
                                            2024-09-28 12:17:16 반품요청 / 브롬톤런던 티셔츠 / admin(최고관리자)<br>
                                            2024-09-28 12:16:18 배송완료 / 브롬톤런던 티셔츠 / admin(최고관리자)<br>
                                            2024-09-28 12:16:14 배송중 / 브롬톤런던 티셔츠 / admin(최고관리자)<br>
                                            2024-09-28 12:16:04 배송준비중 / 브롬톤런던 티셔츠 / admin(최고관리자)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="btm_btn mt10">
                    <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 주문 정보 팝업 ==> 연동 로그 팝업 -->
<div class="popup_bx" data-id="pop1_1_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">PG 연동 로그</div>
                </div>

                <div class="conbx">
                    <div class="con_w">

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="150px">
                                    <col width="140px">
                                    <col width="">
                                    <col width="170px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>결제 수단</th>
                                        <th>결제 타입</th>
                                        <th>결과 로그</th>
                                        <th>연동일시</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>신용/체크 카드</td>
                                        <td>결제 (승인)</td>
                                        <td class="t_l">
                                            결과 코드 : 0000<br>
                                            결과 메세지 : 정상처리<br>
                                            주문번호 : 2308281213370046142<br>
                                            거래번호 : 23870922232121<br>
                                            승인 번호 : 309878887<br>
                                            결제 금액 : 10,000<br>
                                            공급가 : 9,090<br>
                                            면세 : 0<br>
                                            부가세 : 910<br>
                                            카드 결제 금액 : 10,000<br>
                                            카드사 코드 : CCKM<br>
                                            카드사명 : 국민카드<br>
                                            할부 개월 수 : 00<br>
                                            무이자 여부 : N<br>
                                            부분 취소 가능 여부 : Y<br>
                                            카드 구분 정보1 (개인: 0, 법인: 1) : 0<br>
                                            카드 구분 정보2 (일반: 0, 체크: 1) : 0<br>
                                            에스크로 결제 여부 : N<br>
                                            승인 일시 : 20241001121413
                                        </td>
                                        <td>2024-10-01 10:02:12</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>
