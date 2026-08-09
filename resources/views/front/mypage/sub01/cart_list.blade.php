@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '4')

@section('content')
    <style>
        .btn_black {
            background-color: #000 !important;
            border-color: #000 !important;
            color: #fff !important;
        }

        .btn_red {
            background-color: #ed1c24 !important;
            border-color: #ed1c24 !important;
            color: #fff !important;
        }

        .btn_green {
            background-color: #588f28 !important;
            border-color: #588f28 !important;
            color: #fff !important;
        }

        .w40 {
            width: 40px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* Product Info Style */
        .product-info {
            display: flex;
            align-items: center;
            text-align: left;
        }

        .product-info .img_bx {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            border: 1px solid #e1e1e1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }

        .product-info .img_bx img {
            max-width: 100%;
            max-height: 100%;
        }

        .product-info .info {
            flex: 1;
        }

        .product-info .code {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .product-info .name {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .product-info .option {
            font-size: 13px;
            color: #888;
        }

        /* Button Group in Table */
        .btn_group {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .btn_group .btn01 {
            padding: 0;
            line-height: 30px;
            height: 30px;
            font-size: 12px;
            width: 60px;
            border-radius: 0;
        }

        /* Search Table Button */
        .search_btn_cell {
            vertical-align: bottom;
        }

        .search_btn_cell .btn01 {
            height: 42px;
            line-height: 42px;
            width: 100%;
            font-size: 14px;
            border-radius: 0;
        }
    </style>

    <div id="contents">
        <div class="box_w">
            <div class="box box1">
                <!-- 페이지 정보 -->
                <div class="page_info">
                    <div class="ttl">장바구니 목록</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>장바구니 목록</li>
                    </ul>
                </div>

                <div class="conbx">
                    <div class="con_w">
                        <!-- 검색 -->
                        <form action="{{ route('mypage.cart') }}" method="get">
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                        <col width="120px">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th class="w160"><span>채널명</span></th>
                                            <td>
                                                <input type="text" name="channel_name" value="{{ $channelName ?? '' }}"
                                                    style="width: 100%; height: 42px; border: 1px solid #ddd; padding: 0 10px;">
                                            </td>
                                            <td class="search_btn_cell">
                                                <button type="submit" class="btn01 btn_black" style="border:0; cursor:pointer;">검색</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <div class="mt40">
                            <div class="txt_total mb10">총 <span class="fcol1 bold">{{ count($cartItems) }}</span>건</div>
                            <div class="tb01 type2">
                                <table>
                                    <colgroup>
                                        <col width="">
                                        <col width="80px">
                                        <col width="120px">
                                        <col width="100px">
                                        <col width="150px">
                                        <col width="160px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>상품 정보</th>
                                            <th>수량</th>
                                            <th>금액</th>
                                            <th>QR 코드</th>
                                            <th>Shop 채널명</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody class="t_c">
                                        @forelse($cartItems as $item)
                                            <tr>
                                                <td class="p15">
                                                    <div class="product-info">
                                                        <div class="img_bx">
                                                            {{-- Uses direct URL for placeholder --}}
                                                            <img src="{{ $item['image'] }}" alt="Product Image">
                                                        </div>
                                                        <div class="info">
                                                            <div class="code">[{{ $item['code'] }}]</div>
                                                            <div class="name">{{ $item['name'] }}</div>
                                                            <div class="option">{{ $item['option'] }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $item['quantity'] }}</td>
                                                <td class="bold">{{ number_format($item['price']) }}원</td>
                                                <td>
                                                    <img src="{{ $item['qr_code'] }}" class="w40 cursor-pointer pop_btn"
                                                        data-pop="pop_qr_zoom" data-img="{{ $item['qr_code'] }}" alt="QR">
                                                </td>
                                                <td>{{ $item['shop_channel'] }}</td>
                                                <td>
                                                    <div class="btn_group">
                                                        <a href="{{ $item['visit_url'] }}" class="btn01 btn_green">방문</a>
                                                        <form action="{{ route('mypage.cart.delete', ['id' => $item['id']]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn01 btn_red" style="border:0;">비우기</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="no_data" style="padding: 100px 0;">장바구니에 담긴 상품이 없습니다.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- 페이징 -->
                            <div class="page_bx1 text-center mt30">
                                <a href="#" class="page_prev dimmed">prev</a>
                                <a href="#" class="num on">1</a>
                                <a href="#" class="num">2</a>
                                <a href="#" class="num">3</a>
                                <a href="#" class="num">4</a>
                                <a href="#" class="num">5</a>
                                <a href="#" class="num">6</a>
                                <a href="#" class="num">7</a>
                                <a href="#" class="num">8</a>
                                <a href="#" class="num">9</a>
                                <a href="#" class="num">10</a>
                                <a href="#" class="page_next dimmed">next</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Zoom Popup -->
    <div class="popup_bx" data-id="pop_qr_zoom">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="width: 520px;">
                    <div class="close_btn close1">닫기</div>
                    <div class="conbx t_c" style="padding: 20px;">
                        <img id="qr_zoom_img" src="" alt="QR Zoom" style="width: 100%; display: block;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $(".pop_btn").click(function () {
                var popId = $(this).attr("data-pop");
                var imgUrl = $(this).attr("data-img");

                if (popId === 'pop_qr_zoom') {
                    // Use a larger size for the popup if possible
                    var largeImgUrl = imgUrl.replace('size=300x300', 'size=500x500');
                    $("#qr_zoom_img").attr("src", largeImgUrl);
                }

                $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                return false;
            });

            // Background click close
            $(document).on("click", ".popup_bx", function (e) {
                if ($(e.target).closest(".pop_con").length === 0) {
                    $(this).fadeOut(300);
                }
            });

            $(".popup_bx .close_btn").click(function () {
                $(this).parents(".popup_bx").stop().fadeOut(300);
                return false;
            });
        });
    </script>
@endsection
