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
                        <li><a href="./" data-pop="pop1_1"><span>주문 정보</span></a></li>
                        <li><a href="./" class="on"><span>정상 주문</span></a></li>
                        <li><a href="./" data-pop="pop1_3"><span>취소 주문</span></a></li>
                        <li><a href="./" data-pop="pop1_4"><span>반품 주문</span></a></li>
                        <li><a href="./" data-pop="pop1_5"><span>교환 주문</span></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $(".popup_bx[data-id='pop1_2'] .tab_bx1 li a").click(function(){
                        if($(this).attr("data-pop")) {
                            var popId = $(this).attr("data-pop");
                            $(this).parents(".popup_bx").stop().fadeOut(300);
                            $(".popup_bx[data-id='"+popId+"']").stop().fadeIn(300);
                            $(".popup_bx[data-id='"+popId+"']").scrollTop(0);
                
                            return false;
                        }
                    });
                </script>

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
                                        <td>Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td>Shop채널명</td>
                                        <th class="w160"><span>결제일시</span></th>
                                        <td>2024-10-01 10:02:12</td>
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
                                <tbody>
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
                            <a href="./" class="btn01 col3 pop_btn" data-pop="pop1_2_2">결제완료</a>
                            <a href="./" class="btn01 col3 pop_btn" data-pop="pop1_2_3">배송대기</a>
                            <a href="./" class="btn01 col3 pop_btn" data-pop="pop1_2_3">배송중</a>
                            <a href="./" class="btn01 pop_btn" data-pop="pop1_2_4">반품 요청</a>
                            <a href="./" class="btn01 pop_btn" data-pop="pop1_2_5">교환 요청</a>
                            <a href="./" class="btn01 pop_btn" data-pop="pop1_2_6">취소 요청</a>
                        </div>
                    </div>
                </div>
                

                <div class="btm_btn mt20">
                    <a href="./" class="col5 close_btn">닫기</a>
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
                    <a href="./">확인</a>
                    <a href="./" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 배송대기, 배송중 팝업 -->
<div class="popup_bx" data-id="pop1_2_3">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">주문 배송정보 관리</div>
                </div>
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
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td>배송대기</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>2</td>
                                    </tr>
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
                                            <select class="w200" required="required">
                                                <option value="" disabled="" selected="">택배사 선택</option>
                                                <option value="1">직접배송</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>송장번호</span></th>
                                        <td>
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="./">변경하기</a>
                    <a href="./" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 반품요청 팝업 -->
<div class="popup_bx" data-id="pop1_2_4">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">반품요청</div>
                </div>
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
                                        <td>Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td>Shop채널명</td>
                                        <th class="w160"><span>결제일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
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
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td>배송대기</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>2</td>
                                    </tr>
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
                                            <select required="required">
                                                <option value="" disabled="" selected="">반품사유 선택</option>
                                                <option value="1">마음이 변했어요</option>
                                                <option value="2">상품에 하자가 있어요(상세 사유 필요)</option>
                                                <option value="3">다른 상품이 배송됐어요(상세 사유 필요)</option>
                                            </select>
                                            <textarea class="mt5" required="required" placeholder="상세 사유를 입력해 주세요 (필수)"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>회수 방법</span></th>
                                        <td colspan="3">
                                            <select required="required">
                                                <option value="" disabled="" selected="">회수 방법 선택</option>
                                                <option value="1">고객 직접 회수</option>
                                                <option value="2">업체 회수</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <!-- 회수 방법 => 고객 직접 회수 -->
                                    <tr>
                                        <th class="w160"><span>회수 정보</span></th>
                                        <td class="pr0">택배사 정보</td>
                                        <td colspan="2">
                                            <div class="search_w01">
                                                <select required="required">
                                                    <option value="" disabled="" selected="">택배사 선택</option>
                                                    <option value="1">택배사1</option>
                                                </select>
                                                <input type="text" value="" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- 회수 방법 => 업체 회수 -->
                                    <tr>
                                        <th class="w160" rowspan="3"><span>회수 정보</span></th>
                                        <td class="pr0">주문자</td>
                                        <td colspan="2">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr0">연락처</td>
                                        <td colspan="2">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr0">교환 수거지</td>
                                        <td colspan="2">
                                            <div class="addr_bx">
                                                <input type="text" class="addr1 off" placeholder="우편번호" required="required">
                                                <a href="./" class="btn01">우편번호찾기</a>
                                                <input type="text" class="addr2 off" placeholder="주소" required="required">
                                                <input type="text" class="addr3 off" placeholder="상세주소" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th class="w160"><span>반품상품 결제금액</span></th>
                                        <td colspan="3">90,000 원</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>차감 내역</span></th>
                                        <td colspan="3">
                                            0 원<br>
                                            반품 배송비 <input class="w160" type="text" value="" required="required"> 원 &nbsp;&nbsp;<a href="./" class="btn02 col7">적용</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>환불 예상 금액</span></th>
                                        <td colspan="3">90,000 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="./">반품요청하기</a>
                    <a href="./" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 교환신청 팝업 -->
<div class="popup_bx" data-id="pop1_2_5">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">교환신청</div>
                </div>
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
                                        <td>Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td>Shop채널명</td>
                                        <th class="w160"><span>결제일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
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
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td>배송대기</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>2</td>
                                        <td>
                                            <select required="required">
                                                <option value="" disabled="" selected="">변경 옵션</option>
                                                <option value="1">변경 옵션1</option>
                                            </select>
                                        </td>
                                    </tr>
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
                                            <select required="required">
                                                <option value="" disabled="" selected="">교환사유 선택</option>
                                                <option value="1">마음이 변했어요</option>
                                                <option value="2">상품에 하자가 있어요(상세 사유 필요)</option>
                                                <option value="3">다른 상품이 배송됐어요(상세 사유 필요)</option>
                                            </select>
                                            <textarea class="mt5" required="required" placeholder="상세 사유를 입력해 주세요 (필수)"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>회수 방법</span></th>
                                        <td colspan="3">
                                            <select required="required">
                                                <option value="" disabled="" selected="">회수 방법 선택</option>
                                                <option value="1">고객 직접 회수</option>
                                                <option value="2">업체 회수</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <!-- 회수 방법 => 고객 직접 회수 -->
                                    <tr>
                                        <th class="w160"><span>회수 정보</span></th>
                                        <td class="pr0">택배사 정보</td>
                                        <td colspan="2">
                                            <div class="search_w01">
                                                <select required="required">
                                                    <option value="" disabled="" selected="">택배사 선택</option>
                                                    <option value="1">택배사1</option>
                                                </select>
                                                <input type="text" value="" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- 회수 방법 => 업체 회수 -->
                                    <tr>
                                        <th class="w160" rowspan="3"><span>회수 정보</span></th>
                                        <td class="pr0">주문자</td>
                                        <td colspan="2">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr0">연락처</td>
                                        <td colspan="2">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr0">교환 수거지</td>
                                        <td colspan="2">
                                            <div class="addr_bx">
                                                <input type="text" class="addr1 off" placeholder="우편번호" required="required">
                                                <a href="./" class="btn01">우편번호찾기</a>
                                                <input type="text" class="addr2 off" placeholder="주소" required="required">
                                                <input type="text" class="addr3 off" placeholder="상세주소" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th class="w160"><span>추가 결제 금액</span></th>
                                        <td colspan="3">
                                            0 원<br>
                                            추가 결제비용 <input class="w160" type="text" value="" required="required"> 원 &nbsp;&nbsp;<a href="./" class="btn02 col7">적용</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160" rowspan="3"><span>교환 배송지</span></th>
                                        <td class="pr0">주문자</td>
                                        <td colspan="2">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr0">연락처</td>
                                        <td colspan="2">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr0">주소</td>
                                        <td colspan="2">
                                            <div class="addr_bx">
                                                <input type="text" class="addr1 off" placeholder="우편번호" required="required">
                                                <a href="./" class="btn01">우편번호찾기</a>
                                                <input type="text" class="addr2 off" placeholder="주소" required="required">
                                                <input type="text" class="addr3 off" placeholder="상세주소" required="required">
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
                    <a href="./">교환요청하기</a>
                    <a href="./" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 정상 주문 팝업 ==> 취소요청 팝업 -->
<div class="popup_bx" data-id="pop1_2_6">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">취소요청</div>
                </div>
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
                                        <td>Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td>Shop채널명</td>
                                        <th class="w160"><span>결제일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
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
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td>배송대기</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>2</td>
                                    </tr>
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
                                            <select required="required">
                                                <option value="" disabled="" selected="">취소사유 선택</option>
                                                <option value="1">마음이 변했어요</option>
                                                <option value="2">상품에 하자가 있어요(상세 사유 필요)</option>
                                                <option value="3">다른 상품이 배송됐어요(상세 사유 필요)</option>
                                            </select>
                                            <textarea class="mt5" required="required" placeholder="상세 사유를 입력해 주세요 (필수)"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>환불금액</span></th>
                                        <td colspan="3">90,000 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="./">주문취소 요청하기</a>
                    <a href="./" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>