@extends('layouts.channel')

@php
    $dep1_id = "01";
    $dep1_tit = "Shop채널관리";
@endphp

@section('page_type', 'sub')

@section('content')
    <div id="container_w">
        <div id="contents">
            <div class="row">
                <div class="box box1">
                    <div class="page_info">
                        <div class="ttl">Shop채널 상세페이지</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>Shop채널관리</li>
                            <li>Shop채널 상세페이지</li>
                        </ul>
                    </div>
                    <div class="tab_bx1">
                        <ul>
                            <li><a href="{{ route('channel.shop_info') }}"><span>Shop채널 정보</span></a></li>
                            <li><a href="#" class="on"><span>판매상품</span></a></li>
                            <li><a href="{{ route('channel.shop_community') }}"><span>커뮤니티</span></a></li>
                        </ul>
                    </div>
                    <div class="conbx">
                        <div class="con_w">
                            <div class="list_top1 btn">
                                <div class="count">총 <strong>{{ $products->total() }}</strong> 건</div>
                                <div class="btn_bx">
                                    <a href="{{ route('channel.shop_product01', ['shop_id' => $shopId]) }}"
                                        class="btn01 col2">판매상품</a>
                                    <a href="{{ route('channel.shop_product02', ['shop_id' => $shopId]) }}"
                                        class="btn01 col4">판매중지상품</a>
                                    <a href="#" class="btn01 col5 pop_btn" data-pop="pop1_1">판매상품 추가</a>
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
                                        <col width="100px">
                                        <col width="100px">
                                        <col width="150px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>번호</th>
                                            <th>상품구분</th>
                                            <th>상품정보</th>
                                            <th>판매상태</th>
                                            <th>제약조건</th>
                                            <th>재고</th>
                                            <th>상품가격</th>
                                            <th>판매가</th>
                                            <th>판매이익</th>
                                            <th>수정/삭제</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($products as $index => $shopProduct)
                                            @php
                                                $product = $shopProduct->product;
                                                $productTypeLabel = match ($shopProduct->product_type) {
                                                    'own' => '지사',
                                                    'public' => '공개',
                                                    'partial' => '부분공개',
                                                    default => '-'
                                                };
                                                $constraintLabel = match ($shopProduct->constraint_type) {
                                                    'none' => '없음',
                                                    'range' => '범위형',
                                                    'fixed' => '고정형',
                                                    default => '-'
                                                };
                                                $mainImage = $product->images->first();
                                                $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');

                                                // 카테고리 경로 생성
                                                $categoryPath = '';
                                                if ($product->category) {
                                                    $categoryPath = $product->category->category_name;
                                                }

                                                // 재고 표시
                                                $stockDisplay = '수량제한없음';
                                                if ($shopProduct->stock) {
                                                    $stockDisplay = number_format($shopProduct->stock) . '개';
                                                    if ($shopProduct->purchase_limit) {
                                                        $stockDisplay .= '<br>(1회 ' . number_format($shopProduct->purchase_limit) . '개 제한)';
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $products->total() - ($products->currentPage() - 1) * $products->perPage() - $index }}
                                                </td>
                                                <td>{{ $productTypeLabel }}</td>
                                                <td class="t_l">
                                                    <div class="thum01">
                                                        <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                                        <div class="txt_bx">
                                                            <p>{{ $categoryPath }}</p>
                                                            <strong>{{ $product->product_name }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>중지</td>
                                                <td>{{ $constraintLabel }}</td>
                                                <td>{!! $stockDisplay !!}</td>
                                                <td class="t_r">{{ number_format($shopProduct->product_price) }}원</td>
                                                <td class="t_r">{{ number_format($shopProduct->selling_price) }}원</td>
                                                <td class="t_r">{{ number_format($shopProduct->profit) }}원</td>
                                                <td>
                                                    <a href="#" class="btn02 col5" onclick='openProductViewModal({
                                                        "id": {{ $shopProduct->id }},
                                                        "type_label": "{{ $productTypeLabel }}",
                                                        "code": "{{ $product->product_code }}",
                                                        "name": "{{ addslashes($product->product_name) }}",
                                                        "category": "{{ addslashes($categoryPath) }}",
                                                        "img": "{{ $imageUrl }}",
                                                        "price_constraint": "{{ $constraintLabel }}",
                                                        "profit_constraint": "{{ number_format($shopProduct->profit) }}원",
                                                        "stock_text": "{!! strip_tags($stockDisplay) !!}",
                                                        "purchase_limit": "{{ $shopProduct->purchase_limit ? number_format($shopProduct->purchase_limit).'개' : '제한없음' }}",
                                                        "sales_period": "무기한",
                                                        "selling_price": "{{ number_format($shopProduct->selling_price) }}"
                                                    }); return false;'>보기</a>
                                                    <a href="#" class="btn02 col1" onclick='updateProductStatus("{{ route("channel.product.status.update") }}", {{ $shopProduct->id }}, 1, "판매재개"); return false;'>판매재개</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="t_c" style="padding: 50px 0;">
                                                    판매중지된 상품이 없습니다.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                            <div class="page_bx1">
                                {{ $products->links() }}
                            </div>

                            <!-- 판매상품 추가 팝업 -->
                            <!-- 지사상품 -->
                            @include('channel.sub01.pop_shop_product_own')
                            <!-- 공유상품 -->
                            @include('channel.sub01.pop_shop_product_public')
                            <!-- 부분고유상품 -->
                            @include('channel.sub01.pop_shop_product_partial')

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
                                                                    <td>Me9-Shop-0032022</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <br>
                                                    <div class="list01">
                                                        <ul>
                                                            <li>
                                                                <a href="#">
                                                                    <div class="img_bx"
                                                                        style="background-image:url(../images/sub/thum01.jpg)">
                                                                    </div>
                                                                    <div class="txt_bx">
                                                                        <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                                        <strong>상품명 111111</strong>
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
                                                                    <td>1,500 원 ~ 5,000 원</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>이익분배조건</th>
                                                                    <td>판매 개당 500 원</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>재고</th>
                                                                    <td>20,000 개</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>구매제한수량</th>
                                                                    <td>1회 구매시 100개 까지</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>상품 판매 기간</th>
                                                                    <td>무기한</td>
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
                                                                    <td>3,500원</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/channel_assets/js/product_management.js"></script>
    <script type="text/javascript">
        $(function () {
            /* 팝업 */
            $(".pop_btn").click(function () {
                var popId = $(this).attr("data-pop");
                $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

                return false;
            });
            $(".popup_bx .close_btn").click(function () {
                $(this).parents(".popup_bx").stop().fadeOut(300);

                return false;
            });
        });
    </script>
@endpush