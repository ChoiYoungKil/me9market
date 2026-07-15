@extends('layouts.channel')

@section('content')
    @php
        $page_type = "sub";
        $dep1_id = "02";
        $dep1_tit = "상품관리";
    @endphp
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">부분공개상품관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>상품관리</li>
                        <li>부분공개상품관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <form method="GET" action="{{ route('channel.product_partial') }}" id="productSearchForm">
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
                                                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="상품명 또는 상품코드를 입력해 주세요.">
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
                                                    <select name="category_id">
                                                        <option value="">전체 상품분류</option>
                                                        @foreach($categoryOptions ?? [] as $category)
                                                            <option value="{{ $category['id'] }}" {{ (int)($filters['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' }}>
                                                                {{ $category['name'] }}
                                                            </option>
                                                        @endforeach
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
                                                    <input type="checkbox" name="request_states[]" value="available" id="partial_request_available" {{ in_array('available', $filters['request_states'] ?? [], true) ? 'checked' : '' }}>
                                                    <label for="partial_request_available">판매요청</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="request_states[]" value="pending" id="partial_request_pending" {{ in_array('pending', $filters['request_states'] ?? [], true) ? 'checked' : '' }}>
                                                    <label for="partial_request_pending">판매요청중</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="request_states[]" value="approved" id="partial_request_approved" {{ in_array('approved', $filters['request_states'] ?? [], true) ? 'checked' : '' }}>
                                                    <label for="partial_request_approved">판매허용</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="request_states[]" value="rejected" id="partial_request_rejected" {{ in_array('rejected', $filters['request_states'] ?? [], true) ? 'checked' : '' }}>
                                                    <label for="partial_request_rejected">요청거부</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>판매자</span></th>
                                        <td>
                                            <input type="text" name="seller" value="{{ $filters['seller'] ?? '' }}" placeholder="판매자명 또는 이메일">
                                        </td>
                                        <th class="w160"><span>판매가격 범위</span></th>
                                        <td>
                                            <div class="scope01">
                                                <input type="text" name="price_min" value="{{ $filters['price_min'] ?? '' }}"><span>원</span>
                                                <span class="mid">~</span>
                                                <input type="text" name="price_max" value="{{ $filters['price_max'] ?? '' }}"><span>원</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>상품상태</span></th>
                                        <td colspan="3">
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="status" id="partial_status_all" value="" {{ ($filters['status'] ?? '') === '' ? 'checked' : '' }}>
                                                    <label for="partial_status_all">전체</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="status" id="partial_status_sale" value="1" {{ (string)($filters['status'] ?? '') === '1' ? 'checked' : '' }}>
                                                    <label for="partial_status_sale">판매</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="status" id="partial_status_stop" value="0" {{ (string)($filters['status'] ?? '') === '0' ? 'checked' : '' }}>
                                                    <label for="partial_status_stop">중지</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="status" id="partial_status_stop_notice" value="stop_notice" {{ ($filters['status'] ?? '') === 'stop_notice' ? 'checked' : '' }}>
                                                    <label for="partial_status_stop_notice">판매중지예고</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10">
                            <button type="submit" class="type2" style="border:none; cursor:pointer;">검색</button>
                            <a href="{{ route('channel.product_partial') }}" class="col5">초기화</a>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="box box2">
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $products->total() }}</strong> 건</div>
                            <div class="right_bx" style="display: flex; gap: 10px; align-items: center;">
                                <select id="perPageSelect" style="padding: 0 10px; border: 1px solid #ddd; height: 34px;">
                                    @foreach([20, 40, 60, 80, 100] as $perPageOption)
                                        <option value="{{ $perPageOption }}" {{ (int)($filters['per_page'] ?? 20) === $perPageOption ? 'selected' : '' }}>{{ $perPageOption }}개씩 보기</option>
                                    @endforeach
                                </select>
                            </div>
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
                                        <th>상품</th>
                                        <th>판매자</th>
                                        <th>재고</th>
                                        <th>판매가격</th>
                                        <th>Shop 채널 정보</th>
                                        <th>상세보기</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $index => $product)
                                        @php
                                            $mainImage = $product->images->first();
                                            $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');
                                            $categoryPath = $product->category_path;
                                        @endphp
                                        <tr>
                                            <td>{{ $products->total() - ($products->currentPage() - 1) * $products->perPage() - $index }}</td>
                                            <td>{{ $product->product_code }}</td>
                                            <td class="t_l">
                                                <div class="thum01">
                                                    <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                                    <div class="txt_bx">
                                                        <p>{{ $categoryPath }}</p>
                                                        <strong>{{ $product->product_name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $product->vendor->vendorbusinessdetails->shop_name ?? ($product->vendor->name ?? '-') }}</td>
                                            <td>수량제한없음</td>
                                            <td class="t_r">₩ {{ number_format($product->product_price) }}</td>
                                            <td class="t_l">
                                                -
                                            </td>
                                            <td>
                                                <a href="#" class="btn02 col2 pop_btn" data-pop="pop3_1" data-id="{{ $product->id }}">보기</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="t_c" style="padding: 100px 0;">
                                                등록된 부분공개 상품이 없습니다.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="page_bx1">
                            {{ $products->links() }}
                        </div>

                        <!-- 보기 팝업 -->
                        @include('channel.sub02.inc.pop_product_own_view')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(".btn01.arrow").click(function () {
            var thisId = $(this).attr("id");
            $(this).toggleClass("on");
            $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
        });

        $("#perPageSelect").change(function () {
            var url = new URL(window.location.href);
            url.searchParams.set("per_page", $(this).val());
            url.searchParams.delete("page");
            window.location.href = url.toString();
        });

        function numberFormat(value) {
            var number = parseFloat(value || 0);
            return number.toLocaleString();
        }

        function rebuildProductImages($pop, images) {
            var fallback = "{{ asset('channel_assets/images/sub/thum01.jpg') }}";
            var imageUrls = images && images.length ? images : [fallback];
            var $mainImage = $pop.find(".product-detail-main-image");
            var $slider = $pop.find(".product-detail-image-list");

            if ($slider.hasClass("slick-initialized")) {
                $slider.slick("unslick");
            }

            $slider.empty();
            imageUrls.forEach(function (url, index) {
                $slider.append(
                    '<li><div class="con ' + (index === 0 ? 'on' : '') + '"><img src="' + url + '"></div></li>'
                );
            });
            $mainImage.attr("src", imageUrls[0] || fallback);

            $slider.slick({
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

            $slider.find(".con").off("click").on("click", function () {
                $slider.find(".con").removeClass("on");
                $(this).addClass("on");
                $mainImage.attr("src", $(this).find("img").attr("src"));
            });
        }

        function renderProductOptions($pop, options) {
            var $select = $pop.find(".product-detail-option-select");
            var $list = $pop.find(".product-detail-option-list");
            $select.empty();
            $list.empty();

            if (!options || !options.length) {
                $select.append('<option>-선택-</option>');
                $list.append('<li style="padding:10px 0; border-bottom:1px solid #eee;">등록된 옵션이 없습니다.</li>');
                return;
            }

            $select.append('<option>-선택-</option>');
            options.forEach(function (option) {
                var label = (option.name ? option.name + ' : ' : '') + (option.value || '-');
                $select.append('<option>' + label + '</option>');
                $list.append(
                    '<li style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eee;">' +
                        '<div class="txt1" style="flex:1; font-weight:bold;">' + label + '</div>' +
                        '<div class="txt2" style="width:90px; text-align:right; font-weight:bold; margin-right:10px;">' + numberFormat(option.price) + '원</div>' +
                        '<div style="width:70px; text-align:right; color:#777;">재고 ' + numberFormat(option.stock) + '</div>' +
                    '</li>'
                );
            });
        }

        function applyProductDetail(response) {
            var p = response.product;
            var $pop = $(".popup_bx[data-id='pop3_1']");

            $pop.find(".product-detail-category").text(response.category_path || '카테고리 없음');
            $pop.find(".product-detail-name").text(p.product_name || '-');
            $pop.find(".product-detail-code").text("상품코드 : " + (p.product_code || '-'));
            $pop.find(".product-detail-seller").text("판매자 : " + (p.seller_name || '-'));
            $pop.find(".product-detail-reward").text(p.reward_points_label || '0 point');
            $pop.find(".product-detail-tax").text(p.tax_label || '-');
            $pop.find(".product-detail-price").text(p.price_condition_label || (numberFormat(p.product_price) + ' 원'));
            $pop.find(".product-detail-profit").text(p.profit_share_label || '-');
            $pop.find(".product-detail-stock").text(p.stock_label || '-');
            $pop.find(".product-detail-purchase-limit").text(p.purchase_limit_label || '-');
            $pop.find(".product-detail-html").html(p.detail_html || p.description || '등록된 상세 설명이 없습니다.');

            rebuildProductImages($pop, p.image_urls || []);
            renderProductOptions($pop, p.option_rows || []);

            $pop.stop().fadeIn(300);
            $pop.scrollTop(0);
        }

        /* 팝업 */
        $(".pop_btn").click(function () {
            var popId = $(this).attr("data-pop");
            var productId = $(this).attr("data-id");

            if (popId === 'pop3_1' && productId) {
                $.get("/channel/product/base/detail/" + productId, function(response) {
                    if (response.status) {
                        applyProductDetail(response);
                    } else {
                        alert(response.message || '데이터를 불러오지 못했습니다.');
                    }
                });
                return false;
            }

            $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
            $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

            return false;
        });
        $(".popup_bx .close_btn").click(function () {
            $(this).parents(".popup_bx").stop().fadeOut(300);

            return false;
        });
    </script>
@endsection
