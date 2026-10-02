<!-- 공유상품 팝업 -->
<div class="popup_bx" data-id="pop1_2">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con">
                <div class="close_btn close1">닫기</div>

                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ url()->current() }}" data-pop="pop1_1"><span>지사상품</span></a></li>
                        <li><a href="{{ url()->current() }}" class="on"><span>공유상품</span></a></li>
                        <li><a href="{{ url()->current() }}" data-pop="pop1_3"><span>부분공유상품</span></a></li>
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
                                                <button type="button" class="btn02 col2 base-product-view-button" data-url="{{ route('channel.product.base.detail', $product['id']) }}">보기</button>
                                            </td>
                                            <td><input class="mr0" type="checkbox" checked></td>
                                            <td>
                                                <a href="{{ url()->current() }}" class="btn02 col5"
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
                                            <td id="public_product_code"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <a href="{{ url()->current() }}">
                                            <div class="img_bx" id="public_product_img"
                                                style="background-image:url(../images/sub/thum01.jpg)"></div>
                                            <div class="txt_bx">
                                                <p id="public_product_category"></p>
                                                <strong id="public_product_name"></strong>
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
                                            <td id="public_price_constraint"></td>
                                        </tr>
                                        <tr>
                                            <th>이익분배조건</th>
                                            <td id="public_profit_constraint"></td>
                                        </tr>
                                        <tr>
                                            <th>재고</th>
                                            <td id="public_stock"></td>
                                        </tr>
                                        <tr>
                                            <th>구매제한수량</th>
                                            <td id="public_purchase_limit"></td>
                                        </tr>
                                        <tr>
                                            <th>상품 판매 기간</th>
                                            <td id="public_sales_period"></td>
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
                        <a href="{{ url()->current() }}" class="btn_submit"
                            onclick="submitProductForm('form_product_public_register', '{{ route('channel.product.public.store') }}'); return false;">상품추가하기</a>
                        <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 공유상품 팝업 ==> 보기 팝업 -->
