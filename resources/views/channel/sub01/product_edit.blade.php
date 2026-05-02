@extends('layouts.channel')

@section('page_type', 'sub')

@php
    $dep1_id = "01";
    $dep1_tit = "Shop채널관리";
    
    $mainImage = $product->images->first();
    $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');
    $categoryPath = ($product->parentCategory ? $product->parentCategory->category_name . ' > ' : '') . ($product->category->category_name ?? '');
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">상품 상세 정보</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>Shop채널관리</li>
                        <li>상품 상세 정보</li>
                    </ul>
                </div>
                
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.shop_info', ['id' => $shopId]) }}"><span>Shop채널 정보</span></a></li>
                        <li><a href="{{ route('channel.shop_product01', ['shop_id' => $shopId]) }}" class="on"><span>판매상품</span></a></li>
                        <li><a href="{{ route('channel.shop_community', ['shop_id' => $shopId]) }}"><span>커뮤니티</span></a></li>
                    </ul>
                </div>

                <div class="conbx">
                    <form action="{{ route('channel.product.update', $shopProduct->id) }}" method="POST">
                        @csrf
                        <div class="con_w">
                            <div class="ttl01">상품 기본정보</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width=""><col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>상품코드</th>
                                            <td>{{ $product->product_code }}</td>
                                            <th>상품상태</th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="status" id="status_1" value="1" {{ $shopProduct->status == 1 ? 'checked' : '' }}>
                                                        <label for="status_1">판매</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="status" id="status_0" value="0" {{ $shopProduct->status == 0 ? 'checked' : '' }}>
                                                        <label for="status_0">중지</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>상품분류</th>
                                            <td colspan="3">{{ $categoryPath }}</td>
                                        </tr>
                                        <tr>
                                            <th>상품명</th>
                                            <td colspan="3">{{ $product->product_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>판매범위</th>
                                            <td>{{ ucfirst($shopProduct->product_type) }}</td>
                                            <th>기본금액</th>
                                            <td>{{ number_format($shopProduct->product_price) }} 원</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">판매 설정 정보</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup><col width="160px"><col width=""></colgroup>
                                    <tbody>
                                        <tr>
                                            <th>판매 가격</th>
                                            <td>
                                                <input type="text" name="selling_price" id="selling_price" value="{{ $shopProduct->selling_price }}" class="w160" required> 원
                                                <p class="mt5 fcol1" style="font-size: 12px;">* 원가: {{ number_format($shopProduct->product_price) }}원 / 예상수익: <span id="expected_profit">{{ number_format($shopProduct->profit) }}</span>원</p>
                                                <input type="hidden" id="product_price" value="{{ $shopProduct->product_price }}">
                                                <input type="hidden" id="settlement_rate" value="{{ $settlementRate }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>재고 수량</th>
                                            <td>
                                                <input type="text" name="stock" value="{{ $shopProduct->stock }}" class="w160"> 개
                                                <p class="mt5" style="font-size: 12px;">(공백 또는 0 일 경우 원본 상품 재고를 따릅니다)</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>구매 제한 수량</th>
                                            <td>
                                                <input type="text" name="purchase_limit" value="{{ $shopProduct->purchase_limit }}" class="w160"> 개
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">상품 이미지</div>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                        <div class="txt_bx">
                                            <strong>{{ $product->product_name }}</strong>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="btm_btn mt20">
                            <a href="{{ route('channel.shop_product01', ['shop_id' => $shopId]) }}" class="col5">목록으로</a>
                            <button type="submit" class="btn01 col2" style="border:none; cursor:pointer; height: 50px; padding: 0 40px; font-weight: bold; font-size: 16px;">저장하기</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $('#selling_price').on('input', function() {
            var sellingPrice = parseFloat($(this).val()) || 0;
            var productPrice = parseFloat($('#product_price').val()) || 0;
            var settlementRate = parseFloat($('#settlement_rate').val()) || 0;

            var commission = sellingPrice * (settlementRate / 100);
            var profit = sellingPrice - commission - productPrice;

            $('#expected_profit').text(Math.round(profit).toLocaleString());
        });
    });
</script>
@endpush
