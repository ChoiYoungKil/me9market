<!-- 지사상품 팝업 -->
<div class="popup_bx" data-id="pop1_1">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>
                
                <div class="tab_bx1">
                    <ul>
                        <li><a href="./" class="on"><span>지사상품</span></a></li>
                        <li><a href="./" data-pop="pop1_2"><span>공유상품</span></a></li>
                        <li><a href="./" data-pop="pop1_3"><span>부분공유상품</span></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $(".popup_bx[data-id='pop1_1'] .tab_bx1 li a").click(function(){
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
                                </tbody>
                            </table>
                        </div>
                        <div class="btm_btn right mt10">
                            <a href="./">검색</a>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="list_top1 btn">
                            <div class="count">총 <strong>00</strong> 건</div>
                            <div class="btn_bx">
                                <a href="../sub02/product_own.php" class="btn01 col2">상품 관리</a>
                            </div>
                        </div>
                        
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="70px">
                                    <col width="80px">
                                    <col width="">
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>상품코드</th>
                                        <th>상품정보</th>
                                        <th>판매상태</th>
                                        <th>제약조건</th>
                                        <th>재고</th>
                                        <th>상품가격</th>
                                        <th>상품추가</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>00</td>
                                        <td>a20112</td>
                                        <td class="t_l">
                                            <div class="thum01">
                                                <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                <div class="txt_bx">
                                                    <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                    <strong>상품명 111111</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>판매</td>
                                        <td>없음</td>
                                        <td>수량제한없음</td>
                                        <td class="t_r">2,000원</td>
                                        <td>
                                            <a href="./" class="btn02 col5 pop_btn" data-pop="pop1_1_2">추가하기</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>00</td>
                                        <td>a20393</td>
                                        <td class="t_l">
                                            <div class="thum01">
                                                <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                <div class="txt_bx">
                                                    <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                    <strong>상품명 222222</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>판매</td>
                                        <td>범위형</td>
                                        <td>10,000개</td>
                                        <td class="t_r">5,000원</td>
                                        <td>
                                            <a href="./" class="btn02 col5 pop_btn" data-pop="pop1_1_2">추가하기</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>00</td>
                                        <td>a22333</td>
                                        <td class="t_l">
                                            <div class="thum01">
                                                <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                                <div class="txt_bx">
                                                    <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                    <strong>상품명 333333</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>판매</td>
                                        <td>고정형</td>
                                        <td>500,000개<br>(1회 100개 제한)</td>
                                        <td class="t_r">4,000원</td>
                                        <td>
                                            <a href="./" class="btn02 col5 pop_btn" data-pop="pop1_1_2">추가하기</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

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

<!-- 지사상품 팝업 ==> 추가하기 팝업 -->
<div class="popup_bx" data-id="pop1_1_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w640">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">판매 상품 정보</div>
                </div>

                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01">판매 상품 코드</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th>판매 상품 코드</th>
                                        <td>Me9-Shop-0032022</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <div class="list01">
                            <ul>
                                <li>
                                    <a href="./">
                                        <div class="img_bx" style="background-image:url(../images/sub/thum01.jpg)"></div>
                                        <div class="txt_bx">
                                            <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                            <strong>상품명 111111</strong>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                            <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">상품 제약 조건</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th>가격제약조건</th>
                                        <td>1,500 원 ~ 5,000 원</td>
                                    </tr>
                                    <tr>
                                        <th>이익분배조건</th>
                                        <td>판매 개당 500 원</td>
                                    </tr>
                                    <tr>
                                        <th>재고</th>
                                        <td>20,000 개</td>
                                    </tr>
                                    <tr>
                                        <th>구매제한수량</th>
                                        <td>1회 구매시 100개 까지</td>
                                    </tr>
                                    <tr>
                                        <th>상품 판매 기간</th>
                                        <td>무기한</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">판매 설정 정보</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th>판매 설정 금액</th>
                                        <td>
                                            <input class="w160" type="text"> &nbsp;원
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="./">상품추가하기</a>
                    <a href="./" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>