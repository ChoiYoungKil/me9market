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
                            <div class="ttl">공개상품관리</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>상품관리</li>
                                <li>공개상품관리</li>
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
                                                <th class="w160"><span>상품명</span></th>
                                                <td colspan="3">
                                                    <div class="r_btn_w">
                                                        <input type="text" value="" required="required">
                                                        <a id="arrow1" class="btn01 arrow"><span>상세</span></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tb01 bN arrowbx" data-arrowbx="arrow1">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
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
                                                <th class="w160"><span>판매자</span></th>
                                                <td>
                                                    <input type="text" value="" required="required">
                                                </td>
                                                <th class="w160"><span>판매가격 범위</span></th>
                                                <td>
                                                    <div class="scope01">
                                                        <input type="text" value="" required="required"><span>원</span>
                                                        <span class="mid">~</span> 
                                                        <input type="text" value="" required="required"><span>원</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="btm_btn right mt10">
                                    <a href="#">검색</a>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="list_top1">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                </div>
                                
                                <div class="tb01 ovS">
                                    <table>
                                        <colgroup>
                                            <col width="70px">
                                            <col width="80px">
                                            <col width="">
                                            <col width="120px">
                                            <col width="150px">
                                            <col width="130px">
                                            <col width="130px">
                                            <col width="110px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>번호</th>
                                                <th>상품코드</th>
                                                <th>상품명</th>
                                                <th>판매자</th>
                                                <th>재고</th>
                                                <th>판매가격</th>
                                                <th>Shop 채널 정보</th>
                                                <th>상세보기</th>
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
                                                <td>Test123</td>
                                                <td>수량제한없음</td>
                                                <td class="t_r">2,000원 ~ 5,000원</td>
                                                <td class="t_l">채널 명<br>채널 코드</td>
                                                <td>
                                                    <a href="#" class="btn02 col2 pop_btn" data-pop="pop3_1">보기</a>
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
                                
                                <!-- 보기 팝업 -->
                                <?php include __DIR__ . "/../sub02/pop.product_own_view.php"; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(".btn01.arrow").click(function(){
                var thisId = $(this).attr("id");
                $(this).toggleClass("on");
                $(".arrowbx[data-arrowbx='"+thisId+"']").stop().slideToggle(300);
            });
            
            /* 팝업 */
            $(".pop_btn").click(function(){
                var popId = $(this).attr("data-pop");
                $(".popup_bx[data-id='"+popId+"']").stop().fadeIn(300);
                $(".popup_bx[data-id='"+popId+"']").scrollTop(0);

                return false;
            });
            $(".popup_bx .close_btn").click(function(){
                $(this).parents(".popup_bx").stop().fadeOut(300);

                return false;
            });
        </script>
<?php include __DIR__ ."/../inc/footer.php"; ?>