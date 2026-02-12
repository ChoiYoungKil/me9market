<div id="l_menu">
    <div class="con_bx">
        <div class="con1">
            <div class="ttl">주문관리</div>
            <ul>
                <li>
                    <a href="#">
                        <div class="icon icon1">
                            <span class="num">99+</span>
                        </div>
                        <div class="txt">금일주문</div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="icon icon2">
                            <span class="num">20</span>
                        </div>
                        <div class="txt">결제완료</div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="icon icon3">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">발주대기건</div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="icon icon4">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">취소 요청건</div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="icon icon5">
                            <!--<span class="num">99+</span>-->
                        </div>
                        <div class="txt">반품 요청건</div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 상품등록 버튼만 -->
        <!--<div class="con2_btn">
            <a href="#" class="btn"><span>상품등록</span></a>
        </div>-->

        <!-- 상품등록 -->
        <div class="con2">
            <div class="ttl">상품등록</div>
            <div class="list_bx">
                <div class="list_w">
                    <ul>
                        <li><a href="#">- 자사상품 수<span>(5)</span></a></li>
                        <li><a href="#">- 공유상품 수<span>(5)</span></a></li>
                        <li><a href="#">- 판매 상품 수<span>(1)</span></a></li>
                        <li><a href="#">- 판매채널 수<span>(2)</span></a></li>
                    </ul>
                    <a href="#" class="add_btn"><span>상품등록</span></a>
                </div>
            </div>
        </div>

        <div class="con3">
            <?php if($dep1_id != '00'){ ?>
            <div class="con_w">
                <ul class="dep1_wrap">
                    <?php include __DIR__ . "/../inc/snb".$dep1_id.".php"; ?>
                </ul>
            </div>
            <?php } ?>
            
            <div class="con_w">
                <div class="c_ttl">바로가기 메뉴</div>
                <ul class="dep1_wrap type2">
                    <li class="dep1 icon2_1">
                        <a href="../sub00/info_management.php">정보관리</a>
                    </li>
                    <li class="dep1">
                        <a href="../sub00/sub_list.php">서브관리자관리</a>
                    </li>
                    <li class="dep1">
                        <a href="../sub00/order_manager_list.php">발주담당관리</a>
                    </li>
                    <li class="dep1">
                        <a href="../sub00/point_list.php">포인트관리</a>
                    </li>
                    <li class="dep1">
                        <a href="../sub00/delivery_charge_list.php">배송비설정</a>
                    </li>
                    <li class="dep1">
                        <a href="../sub00/cancel_refund_list.php">취소/환불안내</a>
                    </li>
                    
                    <!--<li class="dep1 icon2_1">
                        <a href="#">주문목록</a>
                    </li>
                    <li class="dep1">
                        <a href="#">회원정보수정</a>
                    </li>
                    <li class="dep1">
                        <a href="#">포인트</a>
                    </li>
                    <li class="dep1">
                        <a href="#">방문한 채널</a>
                    </li>-->
                </ul>
            </div>
        </div>
    </div>
</div>