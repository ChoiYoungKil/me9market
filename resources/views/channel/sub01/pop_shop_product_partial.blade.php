<!-- 부분공유상품 팝업 -->
<div class="popup_bx" data-id="pop1_3">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>

                <div class="tab_bx1">
                    <ul>
                        <li><a href="javascript:void(0);" data-pop="pop1_1"><span>지사상품</span></a></li>
                        <li><a href="javascript:void(0);" data-pop="pop1_2"><span>공유상품</span></a></li>
                        <li><a href="javascript:void(0);" class="on"><span>부분공유상품</span></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $(".popup_bx[data-id='pop1_3'] .tab_bx1 li a").click(function () {
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
                                        <th class="w160"><span>판매요청</span></th>
                                        <td colspan="3">
                                            <ul class="chk02">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_1" checked="">
                                                    <label for="chk1_1">판매요청</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_2">
                                                    <label for="chk1_2">판매요청중</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_3">
                                                    <label for="chk1_3">판매허용</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_4">
                                                    <label for="chk1_4">요청거부</label>
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
                        <div class="btm_btn right mt10 search-actions">
                            <a href="javascript:void(0);" class="type2">검색</a>
                        </div>

                        <script type="text/javascript">
                            $(".btn01.arrow").click(function () {
                                var thisId = $(this).attr("id");
                                $(this).toggleClass("on");
                                $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
                            });
                        </script>
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
                                    <col width="80px">
                                    <col width="110px">
                                    <col width="110px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>상품코드</th>
                                        <th>상품정보</th>
                                        <th>판매자</th>
                                        <th>재고</th>
                                        <th>판매가격</th>
                                        <th>상세보기</th>
                                        <th>판매요청</th>
                                        <th>상품추가</th>
                                    </tr>
                                </thead>
                                @php
                                    $partialProducts = [
                                        [
                                            'id' => 401,
                                            'code' => 'a20112',
                                            'name' => 'Partial Product 111111',
                                            'category' => '대분류 > 중분류 > 소분류',
                                            'img' => '../images/sub/thum01.jpg',
                                            'seller' => 'Test123',
                                            'stock_text' => '수량제한없음',
                                            'stock' => '99999',
                                            'price_range' => '2,000원 ~ 5,000원',
                                            'price_constraint' => '1,500 원 ~ 5,000 원',
                                            'profit_constraint' => '판매 개당 500 원',
                                            'purchase_limit' => '제한 없음',
                                            'sales_period' => '무기한',
                                            'request_status' => 'approved',
                                            'request_status_text' => '판매허용',
                                            'request_btn_class' => 'btn02 col2'
                                        ],
                                        [
                                            'id' => 402,
                                            'code' => 'a20393',
                                            'name' => 'Partial Product 222222',
                                            'category' => '대분류 > 중분류 > 소분류',
                                            'img' => '../images/sub/thum01.jpg',
                                            'seller' => 'Abc111',
                                            'stock_text' => '10,000개',
                                            'stock' => '10000',
                                            'price_range' => '5,000원',
                                            'price_constraint' => '4,000 원 ~ 8,000 원',
                                            'profit_constraint' => '판매 개당 1,000 원',
                                            'purchase_limit' => '1회 100개',
                                            'sales_period' => '2024-12-31 까지',
                                            'request_status' => 'pending',
                                            'request_status_text' => '판매요청중',
                                            'request_btn_class' => 'btn02'
                                        ],
                                        [
                                            'id' => 403,
                                            'code' => 'a22333',
                                            'name' => 'Partial Product 333333',
                                            'category' => '대분류 > 중분류 > 소분류',
                                            'img' => '../images/sub/thum01.jpg',
                                            'seller' => 'nttes0922',
                                            'stock_text' => '500,000개 <br>(1회 100개 제한)',
                                            'stock' => '500000',
                                            'price_range' => '4,000원',
                                            'price_constraint' => '3,500 원 ~ 4,500 원',
                                            'profit_constraint' => '판매 개당 300 원',
                                            'purchase_limit' => '1회 100개',
                                            'sales_period' => '무기한',
                                            'request_status' => 'new',
                                            'request_status_text' => '판매요청',
                                            'request_btn_class' => 'btn02 col5'
                                        ],
                                        [
                                            'id' => 404,
                                            'code' => 'a22334',
                                            'name' => 'Partial Product 444444',
                                            'category' => '대분류 > 중분류 > 소분류',
                                            'img' => '../images/sub/thum01.jpg',
                                            'seller' => 'nttes0922',
                                            'stock_text' => '500,000개',
                                            'stock' => '500000',
                                            'price_range' => '4,000원',
                                            'price_constraint' => '3,500 원 ~ 4,500 원',
                                            'profit_constraint' => '판매 개당 300 원',
                                            'purchase_limit' => '1회 100개',
                                            'sales_period' => '무기한',
                                            'request_status' => 'rejected',
                                            'request_status_text' => '요청거부',
                                            'request_btn_class' => 'btn02 col4'
                                        ]
                                    ];
                                @endphp
                                <tbody>
                                    @foreach($partialProducts as $product)
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
                                            <td>{{ $product['seller'] }}</td>
                                            <td>{!! nl2br($product['stock_text']) !!}</td>
                                            <td class="t_r">{{ $product['price_range'] }}</td>
                                            <td>
                                                <a href="javascript:void(0);" class="btn02 col2 pop_btn" data-pop="pop1_3_3">보기</a>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="{{ $product['request_btn_class'] }}"
                                                    onclick='openProductRegisterModal("pop1_3_4", @json($product)); return false;'>{{ $product['request_status_text'] }}</a>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="btn02 col5"
                                                    onclick='openProductRegisterModal("pop1_3_2", @json($product)); return false;'>추가하기</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                        <div class="page_bx1">
                            <a href="javascript:void(0);" class="page_first">first</a>
                            <a href="javascript:void(0);" class="page_prev">prev</a>
                            <a href="javascript:void(0);" class="num on">1</a>
                            <a href="javascript:void(0);" class="num">2</a>
                            <a href="javascript:void(0);" class="num">3</a>
                            <a href="javascript:void(0);" class="num">4</a>
                            <a href="javascript:void(0);" class="num">5</a>
                            <a href="javascript:void(0);" class="page_next">next</a>
                            <a href="javascript:void(0);" class="page_last">last</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 부분공유상품 팝업 ==> 추가하기 팝업 -->
<div class="popup_bx" data-id="pop1_3_2" id="modal_product_partial_register">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w640">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">판매 상품 정보</div>
                </div>

                <form id="form_product_partial_register">
                    <input type="hidden" name="product_id" id="partial_product_id" value="">
                    <input type="hidden" name="shop_id" value="{{ $shopId }}">
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
                                            <td id="partial_product_code">Me9-Shop-0032022</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="img_bx" id="partial_product_img"
                                                style="background-image:url(../images/sub/thum01.jpg)"></div>
                                            <div class="txt_bx">
                                                <p id="partial_product_category">대분류 &gt; 중분류 &gt; 소분류</p>
                                                <strong id="partial_product_name">상품명 111111</strong>
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
                                            <td id="partial_price_constraint">1,500 원 ~ 5,000 원</td>
                                        </tr>
                                        <tr>
                                            <th>이익분배조건</th>
                                            <td id="partial_profit_constraint">판매 개당 500 원</td>
                                        </tr>
                                        <tr>
                                            <th>재고</th>
                                            <td id="partial_stock">20,000 개</td>
                                        </tr>
                                        <tr>
                                            <th>구매제한수량</th>
                                            <td id="partial_purchase_limit">1회 구매시 100개 까지</td>
                                        </tr>
                                        <tr>
                                            <th>상품 판매 기간</th>
                                            <td id="partial_sales_period">무기한</td>
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
                        <a href="javascript:void(0);" class="btn_submit"
                            onclick="submitProductForm('form_product_partial_register', '{{ route('channel.product.partial.store') }}'); return false;">상품추가하기</a>
                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 부분공유상품 팝업 ==> 보기 팝업 -->
<div class="popup_bx" data-id="pop1_3_3">
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

                                    $(".popup_bx[data-id='pop1_3_3'] .info_bx01 .l_bx .img_slide > ul").slick({
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
                                    $(".pop_btn[data-pop='pop1_3_3']").click(function () {
                                        $(".popup_bx[data-id='pop1_3_3'] .info_bx01 .l_bx .img_slide > ul").slick('refresh');


                                        $(".popup_bx[data-id='pop1_3_3'] .info_bx01 .l_bx .img_slide .con").click(function () {
                                            $(".popup_bx[data-id='pop1_3_3'] .info_bx01 .l_bx .img_slide .con").removeClass("on");
                                            $(this).addClass("on");
                                            $(".popup_bx[data-id='pop1_3_3'] .info_bx01 .l_bx .img_bx").html($(this).find("img").clone());
                                        });
                                    });  
                                </script>
                            </div>
                            <div class="r_bx">
                                <div class="txt_bx">
                                    <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                    <strong>상품명 333333</strong>
                                    <ul>
                                        <li>상품코드 : a200392</li>
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
                            $(".popup_bx[data-id='pop1_3_3'] .tab_bx2 a").click(function () {
                                if ($(this).parent(".tab_bx2").attr("data-tabId")) {
                                    var thisTabId = $(this).parent(".tab_bx2").attr("data-tabId");
                                    $(".tab_bx2[data-tabId='" + thisTabId + "'] a").removeClass("on");
                                    $(this).addClass("on");
                                    $(".popup_bx[data-id='pop1_3_3'] .tab_con[data-tabCon='" + thisTabId + "'] .tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop1_3_3'] .tab_con[data-tabCon='" + thisTabId + "'] .tab_w").eq($(this).index()).addClass("on");
                                }
                            });
                            $(".popup_bx[data-id='pop1_3_3'] .close_btn").click(function () {
                                setTimeout(function () {
                                    $(".popup_bx[data-id='pop1_3_3']").find("a").removeClass("on");
                                    $(".popup_bx[data-id='pop1_3_3']").find("a").eq(0).addClass("on");
                                    $(".popup_bx[data-id='pop1_3_3']").find(".tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop1_3_3']").find(".tab_w").eq(0).addClass("on");
                                }, 300);
                            });
                        </script>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt10">
                    <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 부분공유상품 팝업 ==> 추가하기 팝업 -->
<div class="popup_bx" data-id="pop1_3_4" id="modal_product_partial_request">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w640">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">판매 요청 신청하기</div>
                </div>

                <form id="form_product_partial_request">
                    <input type="hidden" name="product_id" id="partial_req_product_id" value="">
                    <input type="hidden" name="shop_id" value="{{ $shopId }}">
                    <div class="conbx">
                        <div class="con_w">
                            <div class="ttl01">판매 요청 상태</div>

                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th>판매 요청 상태</th>
                                            <td>요청거부</td>
                                            <!-- 신규 요청시 상태가 동적일 필요 없음? 아니면 현재 상태 표시? 현재 상태 가정 -->
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                            <td id="partial_req_product_code">Me9-Shop-0032022</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="img_bx" id="partial_req_product_img"
                                                style="background-image:url(../images/sub/thum01.jpg)"></div>
                                            <div class="txt_bx">
                                                <p id="partial_req_product_category">대분류 &gt; 중분류 &gt; 소분류</p>
                                                <strong id="partial_req_product_name">상품명 111111</strong>
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
                                            <td id="partial_req_price_constraint">1,500 원 ~ 5,000 원</td>
                                        </tr>
                                        <tr>
                                            <th>이익분배조건</th>
                                            <td id="partial_req_profit_constraint">판매 개당 500 원</td>
                                        </tr>
                                        <tr>
                                            <th>재고</th>
                                            <td id="partial_req_stock">20,000 개</td>
                                        </tr>
                                        <tr>
                                            <th>구매제한수량</th>
                                            <td id="partial_req_purchase_limit">1회 구매시 100개 까지</td>
                                        </tr>
                                        <tr>
                                            <th>상품 판매 기간</th>
                                            <td id="partial_req_sales_period">무기한</td>
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
                                                <input class="w160" type="text" name="selling_price"> &nbsp;원
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">신청사유</div>

                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th>신청사유</th>
                                            <td>
                                                <textarea name="request_reason" required="required"></textarea>
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
                            onclick="submitProductForm('form_product_partial_request', '{{ route('channel.product.partial.request') }}'); return false;">재요청하기</a>
                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
