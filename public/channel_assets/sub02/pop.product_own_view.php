<div class="popup_bx" data-id="pop3_1">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>

                <div class="conbx">
                    <div class="con_w">
                       <div class="info_bx01">
                           <div class="l_bx">
                               <div class="img_bx">
                                   <img src="../images/sub/thum03.jpg">
                               </div>
                               <div class="img_slide">
                                   <ul>
                                       <li>
                                           <div class="con on">
                                               <img src="../images/sub/thum01.jpg">
                                           </div>
                                       </li>
                                       <li>
                                           <div class="con">
                                               <img src="../images/sub/thum02.jpg">
                                           </div>
                                       </li>
                                       <li>
                                           <div class="con">
                                               <img src="../images/sub/thum03.jpg">
                                           </div>
                                       </li>
                                       <li>
                                           <div class="con">
                                               <img src="../images/sub/thum01.jpg">
                                           </div>
                                       </li>
                                       <li>
                                           <div class="con">
                                               <img src="../images/sub/thum02.jpg">
                                           </div>
                                       </li>
                                   </ul>
                               </div>
                               <script type="text/javascript">
                                    
                                    $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide > ul").slick({
                                        dots: false,
                                        arrows: true,
                                        autoplay: false,
                                        infinite: false,
                                        autoplaySpeed: 4000,
                                        slidesToShow: 4,
                                        slidesToScroll: 1,
                                        draggable: true,
                                        focusOnSelect: false,
                                        pauseOnFocus: false,
                                        pauseOnHover: false,
                                        swipe: false,
                                    });
                                    $(".pop_btn[data-pop='pop3_1']").click(function(){
                                        $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide > ul").slick('refresh');
                                        
                                        
                                        $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide .con").click(function() {
                                            $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide .con").removeClass("on");
                                            $(this).addClass("on");
                                            $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_bx").html($(this).find("img").clone());
                                        });
                                    });  
                               </script>
                           </div>
                           <div class="r_bx">
                               <div class="txt_bx">
                                    <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                    <strong>상품명 333333</strong>
                                    <ul>
                                        <li>상품코드 :  a200392</li>
                                        <li>판매자 : aabbds</li>
                                    </ul>
                                </div>
                                <div class="option_bx">
                                    <div class="ttl">옵션</div>
                                    <select>
                                        <option>-선택-</option>
                                    </select>
                                    <div class="option_w">
                                        <ul>
                                            <li>
                                                <div class="txt1">085</div>
                                                <div class="count">
                                                    <div class="plus">+</div>
                                                    <input type="text" readonly value="1">
                                                    <div class="minus">-</div>
                                                </div>
                                                <div class="txt2"><strong>100</strong>원</div>
                                                <div class="del">삭제</div>
                                            </li>
                                            <li>
                                                <div class="txt1">090</div>
                                                <div class="count">
                                                    <div class="plus">+</div>
                                                    <input type="text" readonly value="1">
                                                    <div class="minus">-</div>
                                                </div>
                                                <div class="txt2"><strong>100</strong>원</div>
                                                <div class="del">삭제</div>
                                            </li>
                                            <li>
                                                <div class="txt1">100</div>
                                                <div class="count">
                                                    <div class="plus">+</div>
                                                    <input type="text" readonly value="1">
                                                    <div class="minus">-</div>
                                                </div>
                                                <div class="txt2"><strong>100</strong>원</div>
                                                <div class="del">삭제</div>
                                            </li>
                                            <li>
                                                <div class="txt1">110</div>
                                                <div class="count">
                                                    <div class="plus">+</div>
                                                    <input type="text" readonly value="1">
                                                    <div class="minus">-</div>
                                                </div>
                                                <div class="txt2"><strong>100</strong>원</div>
                                                <div class="del">삭제</div>
                                            </li>
                                            <li>
                                                <div class="txt1">120</div>
                                                <div class="count">
                                                    <div class="plus">+</div>
                                                    <input type="text" readonly value="1">
                                                    <div class="minus">-</div>
                                                </div>
                                                <div class="txt2"><strong>100</strong>원</div>
                                                <div class="del">삭제</div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                           </div>
                       </div>
                    </div>

                    <div class="con_w">
                        <div class="tab_bx2" data-tabId="tab1">
                            <a class="on"><span>상품 판매 조건</span></a>
                            <a><span>상품 상세 정보</span></a>
                        </div>
                        <div class="tab_con" data-tabCon="tab1">
                            <div class="tab_w tab1 on">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th>지급포인트</th>
                                                <td>1,000 point</td>
                                            </tr>
                                            <tr>
                                                <th>과세구분</th>
                                                <td>과세</td>
                                            </tr>
                                            <tr>
                                                <th>판매가격</th>
                                                <td>5,000 원 ~ 11,000 원</td>
                                            </tr>
                                            <tr>
                                                <th>이익분배</th>
                                                <td>판매개당 4,000 원</td>
                                            </tr>
                                            <tr>
                                                <th>재고</th>
                                                <td>3,223 개</td>
                                            </tr>
                                            <tr>
                                                <th>구매제한수량</th>
                                                <td>1회 구매에 100 개까지 구매가능</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab_w tab2">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th>상품 상세 정보1</th>
                                                <td>내용입니다.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <script type="text/javascript">
                            $(".popup_bx[data-id='pop3_1'] .tab_bx2 a").click(function(){
                                if($(this).parent(".tab_bx2").attr("data-tabId")) {
                                    var thisTabId = $(this).parent(".tab_bx2").attr("data-tabId");
                                    $(".popup_bx[data-id='pop3_1'] .tab_bx2[data-tabId='"+thisTabId+"'] a").removeClass("on");
                                    $(this).addClass("on");
                                    $(".popup_bx[data-id='pop3_1'] .tab_con[data-tabCon='"+thisTabId+"'] .tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop3_1'] .tab_con[data-tabCon='"+thisTabId+"'] .tab_w").eq($(this).index()).addClass("on");
                                }
                            });
                            $(".popup_bx[data-id='pop3_1'] .close_btn").click(function(){
                                setTimeout(function(){
                                    $(".popup_bx[data-id='pop3_1']").find("a").removeClass("on");
                                    $(".popup_bx[data-id='pop3_1']").find("a").eq(0).addClass("on");
                                    $(".popup_bx[data-id='pop3_1']").find(".tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop3_1']").find(".tab_w").eq(0).addClass("on");
                                },300);
                            });
                        </script>
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