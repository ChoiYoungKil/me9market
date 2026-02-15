<!-- 지사상품 팝업 -->
<div class="popup_bx" data-id="pop1_1">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>

                <div class="tab_bx1">
                    <ul>
                        <li><a href="#" class="on"><span>지사상품</span></a></li>
                        <li><a href="#" data-pop="pop1_2"><span>공유상품</span></a></li>
                        <li><a href="#" data-pop="pop1_3"><span>부분공유상품</span></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $(".popup_bx[data-id='pop1_1'] .tab_bx1 li a").click(function () {
                        if ($(this).attr("data-pop")) {
                            var popId = $(this).attr("data-pop");
                            $(this).parents(".popup_bx").stop().fadeOut(300);
                            $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                            $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

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
                            <a href="#">검색</a>
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
                                @php
                                    $ownProducts = [
                                        [
                                            'id' => 201,
                                            'code' => 'a20112',
                                            'name' => 'Mock Product 111111',
                                            'category' => '대분류 > 중분류 > 소분류',
                                            'img' => '../images/sub/thum01.jpg',
                                            'status' => '판매',
                                            'constraint_type' => '없음',
                                            'stock_text' => '수량제한없음',
                                            'stock' => '99999',
                                            'price' => '2,000',
                                            'price_constraint' => '1,500 원 ~ 5,000 원',
                                            'profit_constraint' => '판매 개당 500 원',
                                            'purchase_limit' => '제한 없음',
                                            'sales_period' => '무기한'
                                        ],
                                        [
                                            'id' => 202,
                                            'code' => 'a20393',
                                            'name' => 'Mock Product 222222',
                                            'category' => '대분류 > 중분류 > 소분류',
                                            'img' => '../images/sub/thum01.jpg',
                                            'status' => '판매',
                                            'constraint_type' => '범위형',
                                            'stock_text' => '10,000개',
                                            'stock' => '10000',
                                            'price' => '5,000',
                                            'price_constraint' => '4,000 원 ~ 8,000 원',
                                            'profit_constraint' => '판매 개당 1,000 원',
                                            'purchase_limit' => '1회 100개',
                                            'sales_period' => '2024-12-31 까지'
                                        ]
                                    ];
                                @endphp
                                <tbody>
                                    @foreach($ownProducts as $product)
                                        <tr>
                                            <td>00</td>
                                            <td>{{ $product['code'] }}</td>
                                            <td class="t_l">
                                                <div class="thum01">
                                                    <div class="img_bx" style="background-image:url({{ $product['img'] }})">
                                                    </div>
                                                    <div class="txt_bx">
                                                        <p>{{ $product['category'] }}</p>
                                                        <strong>{{ $product['name'] }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $product['status'] }}</td>
                                            <td>{{ $product['constraint_type'] }}</td>
                                            <td>{!! nl2br($product['stock_text']) !!}</td>
                                            <td class="t_r">{{ $product['price'] }}원</td>
                                            <td>
                                                <a href="#" class="btn02 col5"
                                                    onclick='openProductRegisterModal("pop1_1_2", @json($product)); return false;'>추가하기</a>
                                            </td>
                                        </tr>
                                    @endforeach
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 지사상품 팝업 ==> 추가하기 팝업 -->
<div class="popup_bx" data-id="pop1_1_2" id="modal_product_own_register">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w640">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">판매 상품 정보</div>
                </div>

                <form id="form_product_own_register">
                    <input type="hidden" name="product_id" id="own_product_id" value="">
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
                                            <td id="own_product_code">Me9-Shop-0032022</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <a href="#">
                                            <div class="img_bx" id="own_product_img"
                                                style="background-image:url(../images/sub/thum01.jpg)"></div>
                                            <div class="txt_bx">
                                                <p id="own_product_category">대분류 &gt; 중분류 &gt; 소분류</p>
                                                <strong id="own_product_name">상품명 111111</strong>
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
                                            <td id="own_price_constraint">1,500 원 ~ 5,000 원</td>
                                        </tr>
                                        <tr>
                                            <th>이익분배조건</th>
                                            <td id="own_profit_constraint">판매 개당 500 원</td>
                                        </tr>
                                        <tr>
                                            <th>재고</th>
                                            <td id="own_stock">20,000 개</td>
                                        </tr>
                                        <tr>
                                            <th>구매제한수량</th>
                                            <td id="own_purchase_limit">1회 구매시 100개 까지</td>
                                        </tr>
                                        <tr>
                                            <th>상품 판매 기간</th>
                                            <td id="own_sales_period">무기한</td>
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
                                                <input class="w160" type="text" name="selling_price"
                                                    required="required"> &nbsp;원
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt10">
                        <a href="#" class="btn_submit"
                            onclick="submitProductForm('form_product_own_register', '{{ route('channel.product.own.store') }}'); return false;">상품추가하기</a>
                        <a href="#" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>