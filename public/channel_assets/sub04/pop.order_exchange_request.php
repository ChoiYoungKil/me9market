<div class="popup_bx" data-id="pop1_5">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">주문 상세보기</div>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="#" data-pop="pop1_1"><span>주문 정보</span></a></li>
                        <li><a href="#" data-pop="pop1_2"><span>정상 주문</span></a></li>
                        <li><a href="#" data-pop="pop1_3"><span>취소 주문</span></a></li>
                        <li><a href="#" data-pop="pop1_4"><span>반품 주문</span></a></li>
                        <li><a href="#" class="on"><span>교환 주문</span></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $(".popup_bx[data-id='pop1_5'] .tab_bx1 li a").click(function(){
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
                        <div class="ttl01">교환 주문 정보 (1)</div>
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
                                        <th class="w160"><span>교환 주문번호</span></th>
                                        <td>Me9-12222112121</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>신규 교환 주문번호</span></th>
                                        <td colspan="3">* 교환 확정 처리 시 신규 교환 주문이 [배송 대기] 상태로 생성됩니다.</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td colspan="3">Shop채널명</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>교환 요청일시</span></th>
                                        <td colspan="3">2024-10-01 10:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>교환 사유</span></th>
                                        <td colspan="3">마음이 변했어요</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>교환 수거지</span></th>
                                        <td colspan="3">(13487)경기도 성남시 분당구 판교로 242(삼평동) 401호</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>교환 상품 수취인</span></th>
                                        <td>홍길동</td>
                                        <th class="w160"><span>수취인 연락처</span></th>
                                        <td>01011223344</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>교환 배송지</span></th>
                                        <td colspan="3">(13487)경기도 성남시 분당구 판교로 242(삼평동) 401호</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>추가 결제 금액</span></th>
                                        <td>8,000 원 (교환 배송비)</td>
                                        <th class="w160"><span>결제 방식</span></th>
                                        <td>판매처 협의</td>
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
                                    <col width="300px">
                                    <col width="100px">
                                    <col width="300px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                    <col width="120px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"></th>
                                        <th>택배 정보</th>
                                        <th>주문상태</th>
                                        <th>상품명</th>
                                        <th>상품코드</th>
                                        <th>상품옵션</th>
                                        <th>변경옵션</th>
                                        <th>교환수량</th>
                                        <th>판매금액</th>
                                        <th>상품금액</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td>
                                            한진택배 &nbsp;<span class="fcol3">22332323</span>&nbsp; <a href="#" class="btn02">삭제</a>
                                        </td>
                                        <td>배송대기</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>BK/M</td>
                                        <td>RD/S</td>
                                        <td>1</td>
                                        <td class="t_r">45,000 원</td>
                                        <td class="t_r">25,000 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                        
                        <div class="mt10">
                            <a href="#" class="btn01 col3 pop_btn" data-pop="pop1_5_2">교환 승인</a>
                            <a href="#" class="btn01 pop_btn" data-pop="pop1_5_3">교환 회수 전 보류</a>
                            <a href="#" class="btn01 pop_btn" data-pop="pop1_5_4">교환 철회</a>
                            <a href="#" class="btn01 col3 pop_btn" data-pop="pop1_5_5">교환 회수 완료</a>
                            <a href="#" class="btn01 col3 pop_btn" data-pop="pop1_5_6">교환 확정</a>
                            <a href="#" class="btn01 pop_btn" data-pop="pop1_5_7">교환 회수 후 보류</a>
                            <a href="#" class="btn01 pop_btn" data-pop="pop1_5_8">반품 전환</a>
                            <a href="#" class="btn01 col6 pop_btn" data-pop="pop1_5_9">옵션 변경</a>
                            <a href="#" class="btn01 col3 pop_btn" data-pop="pop1_5_10">송장수정</a>
                        </div>
                        
                        <div class="imp_bx01 mt10">
                            <div class="txt2 mt0 t_l">
                                - 송장번호가 입력되지 않은 건만 교환 철회가 가능합니다.<br>
                                - 교환 철회 시 해당 주문은 배송 완료 상태로 변경됩니다.<br>
                                - 반품 전환 시 해당 주문은 반품 완료 상태로 변경되어 환불이 진행됩니다.
                            </div>
                        </div>
                    </div>
                </div>
                

                <div class="btm_btn mt20">
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 교환 승인 팝업 -->
<div class="popup_bx" data-id="pop1_5_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">교환승인 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2">교환요청 상태에서만 교환승인 상태로 전환이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">확인</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 교환 회수 전 보류 팝업 -->
<div class="popup_bx" data-id="pop1_5_3">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">교환 보류</div>
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
                                        <th class="w160"><span>교환 주문번호</span></th>
                                        <td>Me9-12222112121</td>
                                        <th class="w160"><span>교환 요청일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td colspan="3">Shop채널명</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tb01 mt10">
                            <table>
                                <colgroup>
                                    <col width="100px">
                                    <col width="">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>주문상태</th>
                                        <th>상품명</th>
                                        <th>상품코드</th>
                                        <th>상품 옵션</th>
                                        <th>변경 옵션</th>
                                        <th>주문수량</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>교환요청</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>Black/M</td>
                                        <td>2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="tb01 mt10">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>보류 사유</span></th>
                                        <td colspan="3">
                                            <textarea required="required" placeholder="보류 사유를 입력해 주세요."></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">교환보류</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 교환 철회 팝업 -->
<div class="popup_bx" data-id="pop1_5_4">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">교환 철회 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2">교환 회수 전 보류, 교환 회수 완료 상태의 상품만 <br>교환 철회 상태로 변경이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">확인</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 교환 회수 완료 팝업 -->
<div class="popup_bx" data-id="pop1_5_5">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">교환 회수 완료 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2">교환 승인 상태에서 교환 회수 완료 상태로 변경이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">확인</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 교환 확정 팝업 -->
<div class="popup_bx" data-id="pop1_5_6">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">교환 확정 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2">교환 회수 완료 상태에서 교환 확정 상태로 변경이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">확인</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 교환 회수 후 보류 팝업 -->
<div class="popup_bx" data-id="pop1_5_7">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">교환 보류</div>
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
                                        <th class="w160"><span>교환 주문번호</span></th>
                                        <td>Me9-12222112121</td>
                                        <th class="w160"><span>교환 요청일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td colspan="3">Shop채널명</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tb01 mt10">
                            <table>
                                <colgroup>
                                    <col width="100px">
                                    <col width="">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>주문상태</th>
                                        <th>상품명</th>
                                        <th>상품코드</th>
                                        <th>상품 옵션</th>
                                        <th>변경 옵션</th>
                                        <th>주문수량</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>교환요청</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>Black/M</td>
                                        <td>2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="tb01 mt10">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>보류 사유</span></th>
                                        <td colspan="3">
                                            <textarea required="required" placeholder="보류 사유를 입력해 주세요."></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">교환보류</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>
<? /* 
<div class="popup_bx" data-id="pop1_5_7">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">교환회수완료 상태에서만 교환 회수 후 보류의 상태로 전환이 가능합니다.</div>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">확인</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>
*/ ?>

<!-- 교환 주문 팝업 ==> 반품 전환 팝업 -->
<div class="popup_bx" data-id="pop1_5_8">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w560">
                <div class="close_btn close1">닫기</div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="imp_bx01 bN">
                            <div class="txt2 mt0">교환 확정 상태로 전환하시겠습니까?</div>
                            <!--<div class="txt2 mt0">교환 회수 후 보류 상태의 상품만 <br>반품 전환 상태로 변경이 가능합니다.</div>-->
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">확인</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 옵션 변경 팝업 -->
<div class="popup_bx" data-id="pop1_5_9">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">교환 옵션 변경</div>
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
                                        <td>Me9-000939393</td>
                                        <th class="w160"><span>주문일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>교환 주문번호</span></th>
                                        <td>Me9-12222112121</td>
                                        <th class="w160"><span>교환 요청일시</span></th>
                                        <td>2024-10-01 09:02:12</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매처</span></th>
                                        <td colspan="3">Shop채널명</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tb01 mt10">
                            <table>
                                <colgroup>
                                    <col width="100px">
                                    <col width="">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                    <col width="">
                                </colgroup>
                                <thead>
                                    <tr>
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
                                        <td>배송중</td>
                                        <td class="t_l"><span class="fcol2">BlueViolet a omnis</span></td>
                                        <td>a0029</td>
                                        <td>RD/S</td>
                                        <td>2</td>
                                        <td>
                                            <select required="required">
                                                <option value="" disabled="" selected="">변경 옵션 선택</option>
                                                <option value="1">변경 옵션1</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="#">변경하기</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 교환 주문 팝업 ==> 송장수정 팝업 -->
<div class="popup_bx" data-id="pop1_5_10">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">송장정보 변경</div>
                </div>
                <div class="conbx">
                    <div class="con_w">
                       <div class="ttl01">기존배송정보</div>
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
                                        <th class="w160"><span>택배사</span></th>
                                        <td>로진택배</td>
                                        <th class="w160"><span>송장번호</span></th>
                                        <td>11122122231</td>
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
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>택배사</span></th>
                                        <td colspan="3">
                                            <select class="w200" required="required">
                                                <option value="" disabled="" selected="">택배사 선택</option>
                                                <option value="1">직접배송</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>송장번호</span></th>
                                        <td colspan="3">
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
                    <a href="#">변경하기</a>
                    <a href="#" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>