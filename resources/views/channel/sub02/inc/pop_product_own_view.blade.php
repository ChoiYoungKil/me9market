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
	                                    <img src="{{ asset('channel_assets/images/sub/thum01.jpg') }}" class="product-detail-main-image">
	                                </div>
	                                <div class="img_slide">
	                                    <ul class="product-detail-image-list">
	                                        <li>
	                                            <div class="con on">
	                                                <img src="{{ asset('channel_assets/images/sub/thum01.jpg') }}">
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
                                    $(".pop_btn[data-pop='pop3_1']").click(function () {
                                        $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide > ul").slick('refresh');


                                        $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide .con").click(function () {
                                            $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_slide .con").removeClass("on");
                                            $(this).addClass("on");
                                            $(".popup_bx[data-id='pop3_1'] .info_bx01 .l_bx .img_bx").html($(this).find("img").clone());
                                        });
                                    });  
                                </script>
                            </div>
                            <div class="r_bx">
	                                <div class="txt_bx">
	                                    <p class="product-detail-category">대분류 &gt; 중분류 &gt; 소분류</p>
	                                    <strong class="product-detail-name">상품명</strong>
	                                    <ul>
	                                        <li class="product-detail-code">상품코드 : -</li>
	                                        <li class="product-detail-seller">판매자 : -</li>
	                                    </ul>
	                                </div>
	                                <div class="option_bx">
	                                    <div class="ttl">옵션</div>
	                                    <select class="product-detail-option-select">
	                                        <option>-선택-</option>
	                                    </select>
	                                    <div class="option_w"
	                                        style="max-height: 150px; overflow-y: auto; margin-top: 10px; border-top: 1px solid #ddd;">
	                                        <ul class="product-detail-option-list">
	                                            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">등록된 옵션이 없습니다.</li>
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
	                                                <td class="product-detail-reward">-</td>
	                                            </tr>
	                                            <tr>
	                                                <th>과세구분</th>
	                                                <td class="product-detail-tax">-</td>
	                                            </tr>
	                                            <tr>
	                                                <th>판매가격</th>
	                                                <td class="product-detail-price">-</td>
	                                            </tr>
	                                            <tr>
	                                                <th>이익분배</th>
	                                                <td class="product-detail-profit">-</td>
	                                            </tr>
	                                            <tr>
	                                                <th>재고</th>
	                                                <td class="product-detail-stock">-</td>
	                                            </tr>
	                                            <tr>
	                                                <th>구매제한수량</th>
	                                                <td class="product-detail-purchase-limit">-</td>
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
	                                                <td class="product-detail-html">등록된 상세 설명이 없습니다.</td>
	                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <script type="text/javascript">
                            $(".popup_bx[data-id='pop3_1'] .tab_bx2 a").click(function () {
                                if ($(this).parent(".tab_bx2").attr("data-tabId")) {
                                    var thisTabId = $(this).parent(".tab_bx2").attr("data-tabId");
                                    $(".popup_bx[data-id='pop3_1'] .tab_bx2[data-tabId='" + thisTabId + "'] a").removeClass("on");
                                    $(this).addClass("on");
                                    $(".popup_bx[data-id='pop3_1'] .tab_con[data-tabCon='" + thisTabId + "'] .tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop3_1'] .tab_con[data-tabCon='" + thisTabId + "'] .tab_w").eq($(this).index()).addClass("on");
                                }
                            });
                            $(".popup_bx[data-id='pop3_1'] .close_btn").click(function () {
                                setTimeout(function () {
                                    $(".popup_bx[data-id='pop3_1']").find("a").removeClass("on");
                                    $(".popup_bx[data-id='pop3_1']").find("a").eq(0).addClass("on");
                                    $(".popup_bx[data-id='pop3_1']").find(".tab_w").removeClass("on");
                                    $(".popup_bx[data-id='pop3_1']").find(".tab_w").eq(0).addClass("on");
                                }, 300);
                            });
                        </script>
                    </div>
                </div>

                <!-- 하단버튼 -->
                <div class="btm_btn mt20" style="text-align: center;">
                    <a href="{{ url()->current() }}" class="close_btn"
                        style="display: inline-block; width: 120px; height: 40px; line-height: 40px; background: #000; color: #fff; text-align: center; font-size: 14px; font-weight: bold;">창닫기</a>
                </div>
            </div>
        </div>
    </div>
</div>
