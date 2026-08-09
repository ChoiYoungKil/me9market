<!-- 공유상품 팝업 -->
<div class="popup_bx" data-id="pop1_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>

                <div class="tab_bx1">
                    <ul>
                        <li><a href="#" data-pop="pop1_1"><span>지사상품</span></a></li>
                        <li><a href="#" class="on"><span>공유상품</span></a></li>
                        <li><a href="#" data-pop="pop1_3"><span>부분공유상품</span></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $(".popup_bx[data-id='pop1_2'] .tab_bx1 li a").click(function () {
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
                        <form method="GET" action="{{ route(Route::currentRouteName(), ['shop_id' => $shopId]) }}">
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
                                            <input type="text" name="popup_public_q" value="{{ $popupFilters['public_q'] ?? '' }}" placeholder="상품명 또는 상품코드">
                                        </td>
                                    </tr>
                                </tbody>
                                </table>
                            </div>
                            <div class="btm_btn right mt10 search-actions">
                                <button type="submit" class="type2">검색</button>
                                <a href="{{ route(Route::currentRouteName(), ['shop_id' => $shopId]) }}" class="col5">초기화</a>
                            </div>
                        </form>
                    </div>

                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $publicProducts->total() }}</strong> 건</div>
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
                                    <col width="80px">
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
                                        <th>관심</th>
                                        <th>상품추가</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($publicProducts as $index => $product)
                                        <tr>
                                            <td>{{ $publicProducts->total() - ($publicProducts->currentPage() - 1) * $publicProducts->perPage() - $index }}</td>
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
                                                <a href="#" class="btn02 col2 pop_btn" data-pop="pop1_2_3">보기</a>
                                            </td>
                                            <td><input class="mr0" type="checkbox" checked></td>
                                            <td>
                                                <a href="#" class="btn02 col5"
                                                    onclick='openProductRegisterModal("pop1_2_2", @json($product)); return false;'>추가하기</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="t_c" style="padding: 50px 0;">
                                                추가 가능한 공유상품이 없습니다.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                        <div class="page_bx1">
                            {{ $publicProducts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 공유상품 팝업 ==> 추가하기 팝업 -->
<div class="popup_bx" data-id="pop1_2_2" id="modal_product_public_register">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w640">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">판매 상품 정보</div>
                </div>

                <form id="form_product_public_register">
                    <input type="hidden" name="product_id" id="public_product_id" value="">
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
                                            <td id="public_product_code">Me9-Shop-0032022</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <a href="#">
                                            <div class="img_bx" id="public_product_img"
                                                style="background-image:url(../images/sub/thum01.jpg)"></div>
                                            <div class="txt_bx">
                                                <p id="public_product_category">대분류 &gt; 중분류 &gt; 소분류</p>
                                                <strong id="public_product_name">상품명 111111</strong>
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
                                            <td id="public_price_constraint">1,500 원 ~ 5,000 원</td>
                                        </tr>
                                        <tr>
                                            <th>이익분배조건</th>
                                            <td id="public_profit_constraint">판매 개당 500 원</td>
                                        </tr>
                                        <tr>
                                            <th>재고</th>
                                            <td id="public_stock">20,000 개</td>
                                        </tr>
                                        <tr>
                                            <th>구매제한수량</th>
                                            <td id="public_purchase_limit">1회 구매시 100개 까지</td>
                                        </tr>
                                        <tr>
                                            <th>상품 판매 기간</th>
                                            <td id="public_sales_period">무기한</td>
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
                            onclick="submitProductForm('form_product_public_register', '{{ route('channel.product.public.store') }}'); return false;">상품추가하기</a>
                        <a href="#" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 공유상품 팝업 ==> 보기 팝업 -->
<div class="popup_bx" data-id="pop1_2_3">
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

                                    $(".popup_bx[data-id='pop1_2_3'] .info_bx01 .l_bx .img_slide > ul").slick({
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
                                    $(".pop_btn[data-pop='pop1_2_3']").click(function () {
                                        $(".popup_bx[data-id='pop1_2_3'] .info_bx01 .l_bx .img_slide > ul").slick('refresh');


                                        $(".popup_bx[data-id='pop1_2_3'] .info_bx01 .l_bx .img_slide .con").click(function () {
                                            $(".popup_bx[data-id='pop1_2_3'] .info_bx01 .l_bx .img_slide .con").removeClass("on");
                                            $(this).addClass("on");
                                            $(".popup_bx[data-id='pop1_2_3'] .info_bx01 .l_bx .img_bx").html($(this).find("img").clone());
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
                            $(".popup_bx[data-id='pop1_2_3'] .tab_bx2 a").click(function () {
                                if ($(this).parent(".tab_bx2").attr("data-tabId")) {
                                    var thisTabId = $(this).parent(".tab_bx2").attr("data-tabId");
                                    $(".popup_bx[data-id='pop1_2_3'] .tab_bx2[data-tabId='" + thisTabId + "'] a").removeClass("on");
                                    $(this).addClass("on");
                                    $(".popup_bx[data-id='pop1_2_3'] .tab_con[data-tabCon='" + thisTabId + "'] .tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop1_2_3'] .tab_con[data-tabCon='" + thisTabId + "'] .tab_w").eq($(this).index()).addClass("on");
                                }
                            });
                            $(".popup_bx[data-id='pop1_2_3'] .close_btn").click(function () {
                                setTimeout(function () {
                                    $(".popup_bx[data-id='pop1_2_3']").find("a").removeClass("on");
                                    $(".popup_bx[data-id='pop1_2_3']").find("a").eq(0).addClass("on");
                                    $(".popup_bx[data-id='pop1_2_3']").find(".tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop1_2_3']").find(".tab_w").eq(0).addClass("on");
                                }, 300);
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
