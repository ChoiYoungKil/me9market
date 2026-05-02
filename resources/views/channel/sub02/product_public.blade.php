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
                </div>
            </div>
        </div>
        <div class="row">
            <div class="box box2">
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $products->total() }}</strong> 건</div>
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
                                    @forelse($products as $index => $product)
                                        @php
                                            $mainImage = $product->images->first();
                                            $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');
                                            $categoryPath = ($product->parentCategory ? $product->parentCategory->category_name . ' > ' : '') . ($product->category->category_name ?? '');
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
                                                <!-- Shop channels using this product -->
                                                -
                                            </td>
                                            <td>
                                                <a href="#" class="btn02 col2 pop_btn" data-pop="pop3_1" data-id="{{ $product->id }}">보기</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="t_c" style="padding: 100px 0;">
                                                등록된 공개 상품이 없습니다.
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

        /* 팝업 */
        $(".pop_btn").click(function () {
            var popId = $(this).attr("data-pop");
            var productId = $(this).attr("data-id");

            if (popId === 'pop3_1' && productId) {
                $.get("/channel/product/base/detail/" + productId, function(response) {
                    if (response.status) {
                        var p = response.product;
                        var $pop = $(".popup_bx[data-id='pop3_1']");
                        
                        $pop.find(".txt_bx p").text(response.category_path);
                        $pop.find(".txt_bx strong").text(p.product_name);
                        $pop.find(".txt_bx ul li:eq(0)").text("상품코드 : " + p.product_code);
                        $pop.find(".tab_w.tab1 table tr:nth-child(3) td").text(parseFloat(p.product_price).toLocaleString() + " 원");
                        
                        if (p.images && p.images.length > 0) {
                            var mainImgUrl = "/front/images/product_images/small/" + p.images[0].image;
                            $pop.find(".l_bx .img_bx img").attr("src", mainImgUrl);
                        }

                        $pop.stop().fadeIn(300);
                        $pop.scrollTop(0);
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