@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '1')

@section('content')
    <style>
        /* 기본 sub.css 스타일을 보완하는 커스텀 스타일 */

        /* 필터 박스 (sub.css 스타일 톤앤매너 유지) */
        .filter_bx {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 40px;
            border: 1px solid #eee;
        }

        .filter_bx .inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter_bx select {
            height: 40px;
            border: 1px solid #ddd;
            padding: 0 15px;
            min-width: 150px;
            background-color: #fff;
            color: #444;
        }

        .filter_bx .period_btn_wrap {
            display: inline-block;
            vertical-align: middle;
            font-size: 0;
            margin-right: 10px;
        }

        /* sub.css의 btn02 스타일 활용하되 높이 조정 */
        .filter_bx .period_btn_wrap .btn02 {
            height: 40px;
            line-height: 38px;
            background-color: #fff;
            color: #666;
            border-color: #ddd;
            border-radius: 0;
            margin-left: -1px;
            font-size: 13px;
        }

        .filter_bx .period_btn_wrap .btn02:first-child {
            margin-left: 0;
            border-radius: 5px 0 0 5px;
        }

        .filter_bx .period_btn_wrap .btn02:last-child {
            border-radius: 0 5px 5px 0;
        }

        .filter_bx .period_btn_wrap .btn02.on {
            background-color: #333;
            color: #fff;
            border-color: #333;
            z-index: 1;
            position: relative;
        }

        .filter_bx .date_wrap {
            display: inline-block;
            vertical-align: middle;
        }

        .filter_bx .date_wrap input {
            height: 40px;
            border: 1px solid #ddd;
            width: 120px;
            text-align: center;
            padding: 0 10px;
            background-color: #fff;
            color: #444;
        }

        .filter_bx .btn_search {
            height: 40px;
            padding: 0 20px;
            background-color: #111;
            color: #fff;
            border: 1px solid #111;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 3px;
            vertical-align: middle;
            margin-left: 5px;
        }

        /* 주문 리스트 - 테이블 느낌의 카드형 리스트 */
        .order_list_wrap {
            border-top: 2px solid #111;
        }

        .order_item {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .order_head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 10px;
            background-color: #fff;
            border-bottom: 1px solid #f5f5f5;
            margin-bottom: 15px;
        }

        .order_head .info {
            font-size: 14px;
            font-weight: 700;
            color: #333;
        }

        .order_head .info .date {
            margin-right: 15px;
        }

        .order_head .info .no {
            color: #666;
            font-weight: 400;
        }

        .order_head .link {
            font-size: 13px;
            color: #333;
            text-decoration: none;
            border: 1px solid #ccc;
            padding: 5px 15px;
            border-radius: 3px;
            background: #fff;
            font-weight: 500;
        }

        .prd_group {
            padding: 0 10px;
        }

        .prd_box {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f5f5f5;
        }

        .prd_box:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .prd_box .cell {
            display: table-cell;
            vertical-align: top;
        }

        .prd_box .status {
            width: 100px;
            font-size: 15px;
            font-weight: 700;
            color: #111;
            padding-top: 10px;
        }

        .prd_box .img {
            width: 90px;
            padding-right: 20px;
        }

        .prd_box .img .inner {
            width: 70px;
            height: 70px;
            background-size: cover;
            background-position: center;
            border: 1px solid #eee;
        }

        .prd_box .info {
            vertical-align: top;
        }

        .prd_box .info .shop {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }

        .prd_box .info .name {
            font-size: 15px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .prd_box .info .opt {
            font-size: 13px;
            color: #888;
        }

        .prd_box .price {
            width: 120px;
            text-align: right;
            padding-right: 30px;
            font-weight: 700;
            color: #111;
            font-size: 16px;
            padding-top: 10px;
        }

        .prd_box .btns {
            width: 90px;
            text-align: right;
        }

        .prd_box .btns .btn02 {
            display: block;
            width: 100%;
            margin-bottom: 5px;
        }

        /* 풋터 (배송비 등) */
        .ord_footer {
            margin-top: 10px;
            padding: 15px 20px;
            background-color: #f9f9f9;
            text-align: right;
            border-radius: 5px;
            font-size: 13px;
            color: #666;
        }

        .ord_footer strong {
            color: #111;
            font-weight: 700;
        }

        @media all and (max-width: 768px) {
            .filter_bx .inner {
                display: block;
            }

            .filter_bx .left_bx {
                margin-bottom: 10px;
            }

            .filter_bx select {
                width: 100%;
            }

            .filter_bx .right_bx {
                text-align: left;
            }

            .filter_bx .period_btn_wrap {
                display: flex;
                width: 100%;
                margin-bottom: 10px;
                margin-right: 0;
            }

            .filter_bx .period_btn_wrap .btn02 {
                flex: 1;
                text-align: center;
                padding: 0;
            }

            .filter_bx .date_wrap {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .filter_bx .date_wrap input {
                flex: 1;
                width: auto;
            }

            .filter_bx .btn_search {
                width: 100%;
                margin: 10px 0 0 0;
            }

            .prd_box,
            .prd_box .cell {
                display: block;
                width: 100%;
            }

            .prd_box .status {
                margin-bottom: 10px;
            }

            .prd_box .img {
                float: left;
                width: 80px;
                padding-right: 0;
                margin-right: 15px;
            }

            .prd_box .info {
                overflow: hidden;
                min-height: 70px;
            }

            .prd_box .price {
                text-align: left;
                padding: 10px 0 0 95px;
                width: auto;
            }

            .prd_box .btns {
                width: 100%;
                margin-top: 15px;
                display: flex;
                gap: 5px;
            }

            .prd_box .btns .btn02 {
                margin-bottom: 0;
                flex: 1;
            }
        }
    </style>

    <div id="contents">
        <div id="order">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">주문목록</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>주문목록</li>
                        </ul>
                    </div>

                    <div class="con_w">
                        <!-- 탭 (sub.css 기본 클래스 활용 - 둥근 박스형 버튼) -->
                        <div class="tab_bx1">
                            <ul>
                                <li><a href="{{ route('mypage.order.list', ['tab' => 'order']) }}"
                                        class="{{ $tab == 'order' ? 'on' : '' }}">주문</a></li>
                                <li><a href="{{ route('mypage.order.list', ['tab' => 'cancel']) }}"
                                        class="{{ $tab == 'cancel' ? 'on' : '' }}">취소</a></li>
                                <li><a href="{{ route('mypage.order.list', ['tab' => 'return']) }}"
                                        class="{{ $tab == 'return' ? 'on' : '' }}">반품</a></li>
                                <li><a href="{{ route('mypage.order.list', ['tab' => 'exchange']) }}"
                                        class="{{ $tab == 'exchange' ? 'on' : '' }}">교환</a></li>
                            </ul>
                        </div>

                        <!-- 필터 -->
                        <div class="filter_bx">
                            <div class="inner">
                                <div class="left_bx">
                                    <select
                                        onchange="location.href='{{ route('mypage.order.list', ['tab' => $tab]) }}&status=' + this.value">
                                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>전체</option>
                                        @if($tab == 'order')
                                            <option value="payment_completed" {{ $status == 'payment_completed' ? 'selected' : '' }}>결제완료</option>
                                            <option value="preparing_shipment" {{ $status == 'preparing_shipment' ? 'selected' : '' }}>배송대기중</option>
                                            <option value="shipping" {{ $status == 'shipping' ? 'selected' : '' }}>배송중</option>
                                            <option value="purchase_confirmed" {{ $status == 'purchase_confirmed' ? 'selected' : '' }}>구매확정</option>
                                        @elseif($tab == 'cancel')
                                            <option value="cancel_request" {{ $status == 'cancel_request' ? 'selected' : '' }}>
                                                취소신청</option>
                                            <option value="cancel_completed" {{ $status == 'cancel_completed' ? 'selected' : '' }}>취소완료</option>
                                        @elseif($tab == 'return')
                                            <option value="return_request" {{ $status == 'return_request' ? 'selected' : '' }}>
                                                반품신청</option>
                                            <option value="return_completed" {{ $status == 'return_completed' ? 'selected' : '' }}>반품완료</option>
                                        @elseif($tab == 'exchange')
                                            <option value="exchange_request" {{ $status == 'exchange_request' ? 'selected' : '' }}>교환신청</option>
                                            <option value="exchange_completed" {{ $status == 'exchange_completed' ? 'selected' : '' }}>교환완료</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="right_bx">
                                    <div class="period_btn_wrap">
                                        <a href="javascript:void(0);" class="btn02 on" data-period="1">1개월</a>
                                        <a href="javascript:void(0);" class="btn02" data-period="3">3개월</a>
                                        <a href="javascript:void(0);" class="btn02" data-period="6">6개월</a>
                                        <a href="javascript:void(0);" class="btn02" data-period="12">1년</a>
                                    </div>
                                    <div class="date_wrap">
                                        <input type="text" class="datepicker" id="start_date" value="{{ $startDate ?? '' }}">
                                        <span>~</span>
                                        <input type="text" class="datepicker" id="end_date" value="{{ $endDate ?? '' }}">
                                    </div>
                                    <button type="button" class="btn_search">조회</button>
                                </div>
                            </div>
                        </div>

                        <!-- 리스트 -->
                        <div class="order_list_wrap">
                            @forelse($orders as $order)
                                <div class="order_item">
                                    <div class="order_head">
                                        <div class="info">
                                            @if($tab == 'cancel')
                                                <span class="date">취소날짜: {{ $order['created_at'] }}</span>
                                                <!-- Mock logic: assuming order date is relevant or mock has distinct logic -->
                                            @elseif($tab == 'return')
                                                <span class="date">반품날짜: {{ $order['created_at'] }}</span>
                                            @elseif($tab == 'exchange')
                                                <span class="date">교환날짜: {{ $order['created_at'] }}</span>
                                            @else
                                                <span class="date">{{ $order['created_at'] }}</span>
                                            @endif
                                            <span class="no">주문번호 {{ $order['order_no'] }}</span>
                                        </div>
                                        <a href="{{ route('mypage.order.view', ['id' => $order['id']]) }}" class="link">주문상세 ></a>
                                    </div>

                                    <div class="prd_group">
                                        @foreach($order['items'] as $item)
                                            <div class="prd_box">
                                                <div class="cell status">{{ $item['status'] }}</div>
                                                <div class="cell img">
                                                    <div class="inner"
                                                        style="background-image: url('{{ $item['product_image'] }}');">
                                                    </div>
                                                </div>
                                                <div class="cell info">
                                                    <div class="shop">{{ $item['shop_name'] }}</div>
                                                    <div class="name">{{ $item['product_name'] }}</div>
                                                    <div class="opt">{{ $item['option'] }}</div>
                                                    @if(isset($item['exchange_order_no']))
                                                        <div class="exchange_info"
                                                            style="margin-top: 10px; font-size: 13px; color: #444;">
                                                            주문번호: {{ $item['original_order_no'] ?? '-' }} <br>
                                                            <span style="color:#e00; font-weight:bold;">→</span> 교환 주문번호:
                                                            {{ $item['exchange_order_no'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="cell price">
                                                    <div>{{ number_format($item['price']) }}원</div>
                                                    @if(isset($item['cancel_request_date']))
                                                        <div style="font-size: 11px; color: #888; margin-top: 5px;">
                                                            취소신청일<br>{{ $item['cancel_request_date'] }}</div>
                                                    @endif
                                                    @if(isset($item['cancel_complete_date']))
                                                        <div style="font-size: 11px; color: #888; margin-top: 5px;">
                                                            취소완료일<br>{{ $item['cancel_complete_date'] }}</div>
                                                    @endif
                                                    @if(isset($item['return_request_date']))
                                                        <div style="font-size: 11px; color: #888; margin-top: 5px;">
                                                            반품신청일<br>{{ $item['return_request_date'] }}</div>
                                                    @endif
                                                    @if(isset($item['exchange_request_date']))
                                                        <div style="font-size: 11px; color: #888; margin-top: 5px;">
                                                            교환신청일<br>{{ $item['exchange_request_date'] }}</div>
                                                    @endif
                                                </div>
                                                <div class="cell btns">
                                                    @if(in_array('cancel', $item['buttons']))
                                                        <a href="javascript:void(0);" class="btn02 col7 js-cancel-popup"
                                                            data-id="{{ $item['order_item_id'] ?? $item['id'] }}"
                                                            data-name="{{ $item['product_name'] }}"
                                                            data-image="{{ $item['product_image'] }}"
                                                            data-shop="{{ $item['shop_name'] }}"
                                                            data-option="{{ $item['option'] }}">취소신청</a>
                                                    @endif
                                                    @if(in_array('return', $item['buttons']))
                                                        <a href="javascript:void(0);" class="btn02 col7 js-return-popup"
                                                            data-id="{{ $item['order_item_id'] ?? $item['id'] }}"
                                                            data-name="{{ $item['product_name'] }}"
                                                            data-image="{{ $item['product_image'] }}"
                                                            data-shop="{{ $item['shop_name'] }}"
                                                            data-option="{{ $item['option'] }}">반품신청</a>
                                                    @endif
                                                    @if(in_array('exchange', $item['buttons']))
                                                        <a href="javascript:void(0);" class="btn02 col5 js-exchange-popup"
                                                            data-id="{{ $item['order_item_id'] ?? $item['id'] }}"
                                                            data-name="{{ $item['product_name'] }}"
                                                            data-image="{{ $item['product_image'] }}"
                                                            data-shop="{{ $item['shop_name'] }}"
                                                            data-option="{{ $item['option'] }}">교환신청</a>
                                                    @endif
                                                    @if(in_array('confirm', $item['buttons']))
                                                        <a href="javascript:void(0);" class="btn02 col2 js-confirm-popup"
                                                            data-id="{{ $item['order_item_id'] ?? $item['id'] }}"
                                                            data-name="{{ $item['product_name'] }}"
                                                            data-image="{{ $item['product_image'] }}"
                                                            data-shop="{{ $item['shop_name'] }}"
                                                            data-option="{{ $item['option'] }}">구매확정</a>
                                                    @endif
                                                    @if(in_array('review', $item['buttons']))
                                                        <a href="javascript:void(0);" class="btn02 col3 js-review-popup"
                                                            data-product-id="{{ $item['product_id'] }}"
                                                            data-name="{{ $item['product_name'] }}"
                                                            data-image="{{ $item['product_image'] }}">리뷰작성</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(count($order['items']) > 0)
                                        <div class="ord_footer">
                                            <strong>{{ $order['items'][0]['shipping_fee'] ?? '무료배송' }}</strong>
                                            <span style="color:#ddd; margin:0 10px;">|</span>
                                            <a href="javascript:void(0);"
                                                onclick="openQnaPopup('{{ $order['items'][0]['product_name'] }}', '{{ $order['items'][0]['product_image'] }}')"
                                                style="font-weight: 700; color: #444; text-decoration: none;">판매자 문의하기 ></a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="no_data"
                                    style="padding: 100px 0; text-align: center; border-bottom: 1px solid #ddd;">
                                    주문 내역이 없습니다.
                                </div>
                            @endforelse
                        </div>

                        <!-- 페이지네이션 (common footer style) -->
                        <div class="btm_btn">
                            <div class="page_bx1 text-center">
                                <a href="#" class="page_prev dimmed">prev</a>
                                <a href="#" class="num on">1</a>
                                <a href="#" class="num">2</a>
                                <a href="#" class="num">3</a>
                                <a href="#" class="num">4</a>
                                <a href="#" class="num">5</a>
                                <a href="#" class="page_next dimmed">next</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 문의하기 팝업 -->
    <div id="pop_qna" class="popup_bx">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="max-width: 600px;">
                    <a href="javascript:void(0);" onclick="closeQnaPopup()" class="close1">닫기</a>

                    <div class="ttl01 brb">
                        <strong>상품 문의하기</strong>
                    </div>

                    <div class="prd_info_summary"
                        style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                        <div class="img"
                            style="width: 80px; height: 80px; background: #f5f5f5; border: 1px solid #eee; background-size: cover; background-position: center;"
                            id="qna_prd_img"></div>
                        <div class="txt">
                            <div style="font-size: 13px; color: #888; margin-bottom: 5px;">대분류 > 중분류 > 소분류</div>
                            <div style="font-size: 15px; font-weight: 700; color: #333;" id="qna_prd_name">상품명</div>
                        </div>
                    </div>

                    <div style="font-size: 14px; font-weight: 700; margin-bottom: 10px;">■ 문의내용</div>
                    <div style="font-size: 13px; color: #666; margin-bottom: 15px;">판매자에게 상품, 배송, 취소, 교환, 반품 등 궁금한 내용을
                        문의하세요.</div>

                    <div class="qna_form"
                        style="border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <colgroup>
                                <col style="width: 100px;">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <th
                                        style="background: #f9f9f9; padding: 10px; text-align: left; font-weight: 400; color: #333; font-size: 13px;">
                                        질문 제목</th>
                                    <td style="padding: 10px;">
                                        <input type="text"
                                            style="width: 100%; height: 34px; border: 1px solid #ddd; padding: 0 10px; font-size: 13px;"
                                            placeholder="질문 제목입니다">
                                    </td>
                                </tr>
                                <tr>
                                    <th
                                        style="background: #f9f9f9; padding: 10px; text-align: left; font-weight: 400; color: #333; font-size: 13px; vertical-align: top;">
                                        질문 내용</th>
                                    <td style="padding: 10px;">
                                        <textarea
                                            style="width: 100%; height: 200px; border: 1px solid #ddd; padding: 10px; font-size: 13px; resize: none;"
                                            placeholder="내용입니다"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="btm_btn">
                        <a href="javascript:void(0);" class="col2" style="width: 120px;">문의하기</a>
                        <a href="javascript:void(0);" onclick="closeQnaPopup()" class="col5" style="width: 120px;">창닫기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 취소신청 팝업 -->
    <div id="pop_cancel" class="popup_bx">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="max-width: 600px;">
                    <a href="javascript:void(0);" onclick="closeCancelPopup()" class="close1">닫기</a>

                    <div class="ttl01 brb">
                        <strong>취소신청하기</strong>
                    </div>

                    <div class="conbx">
                        <div class="con" style="margin-bottom: 20px;">
                            <div class="product01" style="display: flex; align-items: center;">
                                <div class="img_bx" id="cancel_prd_img"
                                    style="width: 80px; height: 80px; background: #f5f5f5; border: 1px solid #eee; background-size: cover; background-position: center; border-radius: 5px; margin-right: 15px;">
                                </div>
                                <div class="txt_bx">
                                    <div class="txt_w">
                                        <div class="txt1" id="cancel_prd_shop" style="font-size: 13px; color: #888;"></div>
                                        <strong class="txt2" id="cancel_prd_name"
                                            style="font-size: 16px; color: #333; display: block; margin: 5px 0;"></strong>
                                        <div class="txt3" id="cancel_prd_option" style="font-size: 13px; color: #666;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="cancel_form">
                            <input type="hidden" name="order_item_id" id="cancel_order_item_id">

                            <div class="con">
                                <div class="c_ttl" style="font-size: 15px; font-weight: 700; margin-bottom: 10px;">취소사유
                                </div>
                                <div class="f_con">
                                    <div class="f_bx" style="margin-bottom: 10px;">
                                        <div class="f_w w100">
                                            <select name="reason" id="cancel_reason_select"
                                                style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #ddd; border-radius: 5px;">
                                                <option value="">취소사유 선택</option>
                                                @foreach(config('array.order_cancel_reasons') as $key => $reason)
                                                    <option value="{{ $key }}">{{ $reason }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="f_bx" id="cancel_detail_reason_bx" style="display: none;">
                                        <div class="f_w w100">
                                            <textarea name="detail_reason" id="cancel_detail_reason"
                                                placeholder="기타 내용을 입력해 주세요"
                                                style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="btm_btn" style="margin-top: 20px; text-align: center;">
                                <a href="javascript:void(0);" class="col5"
                                    style="display: inline-block; width: 120px; text-align: center;">취소신청</a>
                                <a href="javascript:void(0);" onclick="closeCancelPopup()" class="close_btn"
                                    style="display: inline-block; width: 120px; text-align: center;">창닫기</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- 반품신청 팝업 -->
    <div id="pop_return" class="popup_bx">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="max-width: 600px;">
                    <a href="javascript:void(0);" onclick="closeReturnPopup()" class="close1">닫기</a>

                    <div class="ttl01 brb">
                        <strong>반품신청하기</strong>
                    </div>

                    <div class="conbx">
                        <div class="con" style="margin-bottom: 20px;">
                            <div class="product01" style="display: flex; align-items: center;">
                                <div class="img_bx" id="return_prd_img"
                                    style="width: 80px; height: 80px; background: #f5f5f5; border: 1px solid #eee; background-size: cover; background-position: center; border-radius: 5px; margin-right: 15px;">
                                </div>
                                <div class="txt_bx">
                                    <div class="txt_w">
                                        <div class="txt1" id="return_prd_shop" style="font-size: 13px; color: #888;"></div>
                                        <strong class="txt2" id="return_prd_name"
                                            style="font-size: 16px; color: #333; display: block; margin: 5px 0;"></strong>
                                        <div class="txt3" id="return_prd_option" style="font-size: 13px; color: #666;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="return_form">
                            <input type="hidden" name="order_item_id" id="return_order_item_id">

                            <div class="con">
                                <div class="c_ttl" style="font-size: 15px; font-weight: 700; margin-bottom: 10px;">반품사유
                                </div>
                                <div class="f_con">
                                    <div class="f_bx" style="margin-bottom: 10px;">
                                        <div class="f_w w100">
                                            <select name="reason" id="return_reason_select"
                                                style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #ddd; border-radius: 5px;">
                                                <option value="">반품사유 선택</option>
                                                @foreach(config('array.order_return_reasons') as $key => $reason)
                                                    <option value="{{ $key }}">{{ $reason }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="f_bx" id="return_detail_reason_bx" style="display: none;">
                                        <div class="f_w w100">
                                            <textarea name="detail_reason" id="return_detail_reason"
                                                placeholder="기타 내용을 입력해 주세요"
                                                style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="con">
                                <div class="c_ttl" style="font-size: 15px; font-weight: 700; margin-bottom: 10px;">상품회수방법
                                </div>
                                <div class="f_con">
                                    <div class="f_bx" style="margin-bottom: 15px;">
                                        <div class="f_w w100">
                                            <ul class="chk01 mdipi" style="display: flex; gap: 20px;">
                                                <li>
                                                    <input type="radio" id="return_method_auto" name="return_method"
                                                        value="auto" checked>
                                                    <label for="return_method_auto" style="cursor: pointer;">자동회수</label>
                                                </li>
                                                <li>
                                                    <input type="radio" id="return_method_manual" name="return_method"
                                                        value="manual">
                                                    <label for="return_method_manual" style="cursor: pointer;">수동회수</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="f_bx">
                                        <div class="f_w w100">
                                            <!-- 자동회수 선택시 표시 -->
                                            <div id="return_method_text_auto" class="imp_txt"
                                                style="background: #f9f9f9; padding: 15px; border-radius: 5px; font-size: 13px; line-height: 1.5; color: #666;">
                                                <p class="txt1">※ 반품이라고 표기해서 문 앞에 두시면 “한진택배”에서 회수합니다. <br>물품에 문제가 없을 경우 택배비가
                                                    발생합니다.</p>
                                            </div>

                                            <!-- 수동회수 선택시 표시 -->
                                            <div id="return_method_text_manual" class="addr_bx"
                                                style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                                                <div style="font-size: 13px; font-weight: 700; margin-bottom: 5px;">상품회수주소
                                                </div>
                                                <div style="font-size: 13px; color: #444; line-height: 1.5;">
                                                    {{ $user->pincode ?? '00000' }}<br>
                                                    {{ $user->address ?? '주소 정보 없음' }} {{ $user->city ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="btm_btn" style="margin-top: 20px; text-align: center;">
                                <a href="javascript:void(0);" class="col4"
                                    style="display: inline-block; width: 120px; text-align: center;">반품신청</a>
                                <a href="javascript:void(0);" onclick="closeReturnPopup()" class="close_btn"
                                    style="display: inline-block; width: 120px; text-align: center;">창닫기</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- 교환신청 팝업 -->
    <div id="pop_exchange" class="popup_bx">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="max-width: 600px;">
                    <a href="javascript:void(0);" onclick="closeExchangePopup()" class="close1">닫기</a>

                    <div class="ttl01 brb">
                        <strong>교환신청하기</strong>
                    </div>

                    <div class="conbx">
                        <div class="con" style="margin-bottom: 20px;">
                            <div class="product01" style="display: flex; align-items: center;">
                                <div class="img_bx" id="exchange_prd_img"
                                    style="width: 80px; height: 80px; background: #f5f5f5; border: 1px solid #eee; background-size: cover; background-position: center; border-radius: 5px; margin-right: 15px;">
                                </div>
                                <div class="txt_bx">
                                    <div class="txt_w">
                                        <div class="txt1" id="exchange_prd_shop" style="font-size: 13px; color: #888;">
                                        </div>
                                        <strong class="txt2" id="exchange_prd_name"
                                            style="font-size: 16px; color: #333; display: block; margin: 5px 0;"></strong>
                                        <div class="txt3" id="exchange_prd_option" style="font-size: 13px; color: #666;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="exchange_form">
                            <input type="hidden" name="order_item_id" id="exchange_order_item_id">

                            <div class="con">
                                <div class="c_ttl" style="font-size: 15px; font-weight: 700; margin-bottom: 10px;">교환사유
                                </div>
                                <div class="f_con">
                                    <div class="f_bx" style="margin-bottom: 10px;">
                                        <div class="f_w w100">
                                            <select name="reason" id="exchange_reason_select"
                                                style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #ddd; border-radius: 5px;">
                                                <option value="">교환사유 선택</option>
                                                @foreach(config('array.order_exchange_reasons') as $key => $reason)
                                                    <option value="{{ $key }}">{{ $reason }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="f_bx" id="exchange_detail_reason_bx" style="display: none;">
                                        <div class="f_w w100">
                                            <textarea name="detail_reason" id="exchange_detail_reason"
                                                placeholder="기타 내용을 입력해 주세요"
                                                style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="con">
                                <div class="c_ttl" style="font-size: 15px; font-weight: 700; margin-bottom: 10px;">상품회수방법
                                </div>
                                <div class="f_con">
                                    <div class="f_bx" style="margin-bottom: 15px;">
                                        <div class="f_w w100">
                                            <ul class="chk01 mdipi" style="display: flex; gap: 20px;">
                                                <li>
                                                    <input type="radio" id="exchange_method_auto" name="exchange_method"
                                                        value="auto" checked>
                                                    <label for="exchange_method_auto" style="cursor: pointer;">자동회수</label>
                                                </li>
                                                <li>
                                                    <input type="radio" id="exchange_method_manual" name="exchange_method"
                                                        value="manual">
                                                    <label for="exchange_method_manual"
                                                        style="cursor: pointer;">수동회수</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="f_bx">
                                        <div class="f_w w100">
                                            <!-- 자동회수 선택시 표시 -->
                                            <div id="exchange_method_text_auto" class="imp_txt"
                                                style="background: #f9f9f9; padding: 15px; border-radius: 5px; font-size: 13px; line-height: 1.5; color: #666;">
                                                <p class="txt1">※ 교환이라고 표기해서 문 앞에 두시면 “한진택배”에서 회수합니다. <br>물품에 문제가 없을 경우 택배비가
                                                    발생합니다.</p>
                                            </div>

                                            <!-- 수동회수 선택시 표시 -->
                                            <div id="exchange_method_text_manual" class="addr_bx"
                                                style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                                                <div style="font-size: 13px; font-weight: 700; margin-bottom: 5px;">상품회수주소
                                                </div>
                                                <div style="font-size: 13px; color: #444; line-height: 1.5;">
                                                    {{ $user->pincode ?? '00000' }}<br>
                                                    {{ $user->address ?? '주소 정보 없음' }} {{ $user->city ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="btm_btn" style="margin-top: 20px; text-align: center;">
                                <a href="javascript:void(0);" class="col3"
                                    style="display: inline-block; width: 120px; text-align: center; background-color: #ff0000; color: #fff; border: 1px solid #ff0000;">교환신청</a>
                                <a href="javascript:void(0);" onclick="closeExchangePopup()" class="close_btn"
                                    style="display: inline-block; width: 120px; text-align: center;">창닫기</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 구매확정 팝업 -->
    <div id="pop_confirm" class="popup_bx">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="max-width: 600px;">
                    <a href="javascript:void(0);" onclick="closeConfirmPopup()" class="close1">닫기</a>

                    <div class="ttl01 brb">
                        <strong>구매확정하기</strong>
                    </div>

                    <div class="conbx">
                        <div class="con" style="margin-bottom: 20px;">
                            <div class="product01" style="display: flex; align-items: center;">
                                <div class="img_bx" id="confirm_prd_img"
                                    style="width: 80px; height: 80px; background: #f5f5f5; border: 1px solid #eee; background-size: cover; background-position: center; border-radius: 5px; margin-right: 15px;">
                                </div>
                                <div class="txt_bx">
                                    <div class="txt_w">
                                        <div class="txt1" id="confirm_prd_shop" style="font-size: 13px; color: #888;"></div>
                                        <strong class="txt2" id="confirm_prd_name"
                                            style="font-size: 16px; color: #333; display: block; margin: 5px 0;"></strong>
                                        <div class="txt3" id="confirm_prd_option" style="font-size: 13px; color: #666;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="confirm_form">
                            <input type="hidden" name="order_item_id" id="confirm_order_item_id">
                            <input type="hidden" name="rating" id="confirm_rating_value" value="5">

                            <div class="con"
                                style="padding: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div class="c_ttl" style="font-size: 15px; font-weight: 700;">별점주기</div>
                                    <div class="star_rating_bx" style="display: flex; gap: 5px;">
                                        <a href="javascript:void(0);" onclick="setRating(1)" class="star on" data-idx="1"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setRating(2)" class="star on" data-idx="2"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setRating(3)" class="star on" data-idx="3"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setRating(4)" class="star on" data-idx="4"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setRating(5)" class="star on" data-idx="5"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                    </div>
                                </div>
                            </div>

                            <div class="con" style="text-align: center; margin-top: 30px; margin-bottom: 10px;">
                                <p style="font-size: 16px; color: #333;">이 상품을 구매하겠습니다.</p>
                            </div>

                            <div class="btm_btn" style="margin-top: 20px; text-align: center;">
                                <a href="javascript:void(0);" class="col2"
                                    style="display: inline-block; width: 150px; text-align: center; background-color: #588f3a; color: #fff; border: 1px solid #588f3a;">구매확정하기</a>
                                <a href="javascript:void(0);" onclick="closeConfirmPopup()" class="close_btn"
                                    style="display: inline-block; width: 120px; text-align: center;">창닫기</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 리뷰작성 팝업 -->
    <div id="pop_review" class="popup_bx">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con" style="max-width: 600px;">
                    <a href="javascript:void(0);" onclick="closeReviewPopup()" class="close1">닫기</a>

                    <div class="ttl01 brb">
                        <strong>리뷰작성</strong>
                    </div>

                    <div class="conbx">
                        <div class="con" style="margin-bottom: 20px;">
                            <div class="product01" style="display: flex; align-items: center;">
                                <div class="img_bx" id="review_prd_img"
                                    style="width: 80px; height: 80px; background: #f5f5f5; border: 1px solid #eee; background-size: cover; background-position: center; border-radius: 5px; margin-right: 15px;">
                                </div>
                                <strong class="txt2" id="review_prd_name" style="font-size: 16px; color: #333;"></strong>
                            </div>
                        </div>

                        <form id="review_form" action="{{ route('front.rating.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" id="review_product_id">
                            <input type="hidden" name="rating" id="review_rating_value" value="5">

                            <div class="con"
                                style="padding: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div class="c_ttl" style="font-size: 15px; font-weight: 700;">별점주기</div>
                                    <div class="review_star_rating_bx" style="display: flex; gap: 5px;">
                                        <a href="javascript:void(0);" onclick="setReviewRating(1)" class="star on" data-idx="1"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setReviewRating(2)" class="star on" data-idx="2"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setReviewRating(3)" class="star on" data-idx="3"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setReviewRating(4)" class="star on" data-idx="4"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                        <a href="javascript:void(0);" onclick="setReviewRating(5)" class="star on" data-idx="5"
                                            style="font-size: 24px; color: #ddd; text-decoration: none;">★</a>
                                    </div>
                                </div>
                            </div>

                            <div class="con" style="margin-top: 20px;">
                                <textarea name="review" id="review_text" required
                                    style="width: 100%; min-height: 120px; border: 1px solid #ddd; padding: 10px;"
                                    placeholder="리뷰 내용을 입력해 주세요."></textarea>
                            </div>

                            <div class="btm_btn" style="margin-top: 20px; text-align: center;">
                                <button type="submit" class="col2"
                                    style="display: inline-block; width: 150px; text-align: center; background-color: #588f3a; color: #fff; border: 1px solid #588f3a; cursor:pointer;">리뷰등록</button>
                                <a href="javascript:void(0);" onclick="closeReviewPopup()" class="close_btn"
                                    style="display: inline-block; width: 120px; text-align: center;">창닫기</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .star_rating_bx .star {
        color: #ddd;
        transition: color 0.2s;
    }

    .star_rating_bx .star.on {
        color: #ffbf00 !important;
    }

    .review_star_rating_bx .star.on {
        color: #ffbf00 !important;
    }
</style>

@push('scripts')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            // Popup Event Listeners (Delegation) - Moved to top to ensure binding
            $(document).on('click', '.js-cancel-popup', function () {
                var d = $(this).data();
                console.log('Cancel Popup Data:', d);
                openCancelPopup(d.id, d.name, d.image, d.shop, d.option);
            });
            $(document).on('click', '.js-return-popup', function () {
                var d = $(this).data();
                console.log('Return Popup Data:', d);
                openReturnPopup(d.id, d.name, d.image, d.shop, d.option);
            });
            $(document).on('click', '.js-exchange-popup', function () {
                var d = $(this).data();
                console.log('Exchange Popup Data:', d);
                openExchangePopup(d.id, d.name, d.image, d.shop, d.option);
            });
            $(document).on('click', '.js-confirm-popup', function () {
                var d = $(this).data();
                console.log('Confirm Popup Data:', d);
                openConfirmPopup(d.id, d.name, d.image, d.shop, d.option);
            });
            $(document).on('click', '.js-review-popup', function () {
                var d = $(this).data();
                openReviewPopup(d.productId, d.name, d.image);
            });

            // Cancel Submit
            $("#cancel_form .col5").on("click", function (e) {
                e.preventDefault();
                var reason = $("#cancel_reason_select").val();
                if (!reason) {
                    alert("취소 사유를 선택해 주세요.");
                    return;
                }

                var data = {
                    _token: "{{ csrf_token() }}",
                    order_item_id: $("#cancel_order_item_id").val(),
                    type: 'cancel',
                    reason: reason,
                    detail_reason: $("#cancel_detail_reason").val()
                };

                $.ajax({
                    url: "{{ route('mypage.order.claim.submit') }}",
                    method: "POST",
                    data: data,
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "오류가 발생했습니다.");
                    }
                });
            });

            // Return Submit
            $("#return_form .col4").on("click", function (e) {
                e.preventDefault();
                var reason = $("#return_reason_select").val();
                if (!reason) {
                    alert("반품 사유를 선택해 주세요.");
                    return;
                }

                var data = {
                    _token: "{{ csrf_token() }}",
                    order_item_id: $("#return_order_item_id").val(),
                    type: 'return',
                    reason: reason,
                    detail_reason: $("#return_detail_reason").val(),
                    recovery_method: $("input[name='return_method']:checked").val() === 'auto' ? '자동회수' : '수동회수',
                    recovery_address: "{{ $user->address ?? '' }} {{ $user->city ?? '' }}"
                };

                $.ajax({
                    url: "{{ route('mypage.order.claim.submit') }}",
                    method: "POST",
                    data: data,
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "오류가 발생했습니다.");
                    }
                });
            });

            // Exchange Submit
            $("#exchange_form .col3").on("click", function (e) {
                e.preventDefault();
                var reason = $("#exchange_reason_select").val();
                if (!reason) {
                    alert("교환 사유를 선택해 주세요.");
                    return;
                }

                var data = {
                    _token: "{{ csrf_token() }}",
                    order_item_id: $("#exchange_order_item_id").val(),
                    type: 'exchange',
                    reason: reason,
                    detail_reason: $("#exchange_detail_reason").val(),
                    recovery_method: $("input[name='exchange_method']:checked").val() === 'auto' ? '자동회수' : '수동회수',
                    recovery_address: "{{ $user->address ?? '' }} {{ $user->city ?? '' }}"
                };

                $.ajax({
                    url: "{{ route('mypage.order.claim.submit') }}",
                    method: "POST",
                    data: data,
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "오류가 발생했습니다.");
                    }
                });
            });

            // Confirm Submit
            $("#confirm_form .col2").on("click", function (e) {
                e.preventDefault();
                var data = {
                    _token: "{{ csrf_token() }}",
                    order_item_id: $("#confirm_order_item_id").val(),
                    type: 'confirm',
                    rating: $("#confirm_rating_value").val(),
                    review: '이 상품을 구매하겠습니다.'
                };

                $.ajax({
                    url: "{{ route('mypage.order.claim.submit') }}",
                    method: "POST",
                    data: data,
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "오류가 발생했습니다.");
                    }
                });
            });

            // Datepicker (safe check)
            if ($.fn.datepicker) {
                $(".datepicker").datepicker({
                    dateFormat: 'yy-mm-dd',
                    showOtherMonths: true,
                    showMonthAfterYear: true,
                    changeYear: true,
                    changeMonth: true,
                    yearSuffix: "년",
                    monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                    monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                    dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
                    dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
                    maxDate: "+0D"
                });
            }

            // Function to format date as YYYY-MM-DD
            function formatDate(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            }

            // Function to set dates based on months
            function setDateRange(months) {
                var endDate = new Date();
                var startDate = new Date();
                startDate.setMonth(startDate.getMonth() - months);

                $("#start_date").datepicker("setDate", formatDate(startDate));
                $("#end_date").datepicker("setDate", formatDate(endDate));
            }

            // Initialize with 1 month default
            setDateRange(1);

            $(".period_btn_wrap .btn02").click(function (e) {
                e.preventDefault();
                $(this).siblings().removeClass("on");
                $(this).addClass("on");

                var period = $(this).data('period');
                setDateRange(period);
            });

            $(".btn_search").click(function () {
                var url = new URL("{{ route('mypage.order.list') }}", window.location.origin);
                url.searchParams.set("tab", "{{ $tab }}");
                url.searchParams.set("status", "{{ $status }}");

                var startDate = $("#start_date").val();
                var endDate = $("#end_date").val();
                if (startDate) {
                    url.searchParams.set("start_date", startDate);
                }
                if (endDate) {
                    url.searchParams.set("end_date", endDate);
                }

                window.location.href = url.toString();
            });

            $(".filter_bx .btn02").not('.period_btn_wrap .btn02').click(function (e) {
                e.preventDefault();
                $(this).siblings().removeClass("on");
                $(this).addClass("on");
            });

            // Cancel Reason Logic
            $("#cancel_reason_select").on("change", function () {
                var selectedValue = $(this).val();
                if (selectedValue === 'other') {
                    $("#cancel_detail_reason_bx").show();
                } else {
                    $("#cancel_detail_reason_bx").hide();
                }
            });

            // Return Reason Logic
            $("#return_reason_select").on("change", function () {
                var selectedValue = $(this).val();
                if (selectedValue === 'other') {
                    $("#return_detail_reason_bx").show();
                } else {
                    $("#return_detail_reason_bx").hide();
                }
            });

            // Return Method Logic
            $("input[name='return_method']").on("change", function () {
                var selectedValue = $(this).val();
                if (selectedValue === 'auto') {
                    $("#return_method_text_auto").show();
                    $("#return_method_text_manual").hide();
                } else {
                    $("#return_method_text_auto").hide();
                    $("#return_method_text_manual").show();
                }
            });

            // Exchange Reason Logic
            $("#exchange_reason_select").on("change", function () {
                var selectedValue = $(this).val();
                if (selectedValue === 'other') {
                    $("#exchange_detail_reason_bx").show();
                } else {
                    $("#exchange_detail_reason_bx").hide();
                }
            });

            // Exchange Method Logic
            $("input[name='exchange_method']").on("change", function () {
                var selectedValue = $(this).val();
                if (selectedValue === 'auto') {
                    $("#exchange_method_text_auto").show();
                    $("#exchange_method_text_manual").hide();
                } else {
                    $("#exchange_method_text_auto").hide();
                    $("#exchange_method_text_manual").show();
                }
            });
        });

        // Popup Functions
        function openQnaPopup(name, image) {
            $("#qna_prd_name").text(name);
            $("#qna_prd_img").css("background-image", "url('" + image + "')");
            $("#pop_qna").fadeIn();
        }

        function closeQnaPopup() {
            $("#pop_qna").fadeOut();
        }

        function openCancelPopup(id, name, image, shop, option) {
            $("#cancel_prd_name").text(name);
            $("#cancel_prd_img").css("background-image", "url('" + image + "')");
            $("#cancel_prd_shop").text(shop);
            $("#cancel_prd_option").text(option);
            $("#cancel_order_item_id").val(id);

            // 초기화
            $("#cancel_reason_select").val("");
            $("#cancel_detail_reason").val("");
            $("#cancel_detail_reason_bx").hide();

            $("#pop_cancel").fadeIn();
        }

        function closeCancelPopup() {
            $("#pop_cancel").fadeOut();
        }

        function openReturnPopup(id, name, image, shop, option) {
            $("#return_prd_name").text(name);
            $("#return_prd_img").css("background-image", "url('" + image + "')");
            $("#return_prd_shop").text(shop);
            $("#return_prd_option").text(option);
            $("#return_order_item_id").val(id);

            // 초기화
            $("#return_reason_select").val("");
            $("#return_detail_reason").val("");
            $("#return_detail_reason_bx").hide();

            // Method Reset
            $("input[name='return_method'][value='auto']").prop('checked', true);
            $("#return_method_text_auto").show();
            $("#return_method_text_manual").hide();

            $("#pop_return").fadeIn();
        }

        function closeReturnPopup() {
            $("#pop_return").fadeOut();
        }

        function openExchangePopup(id, name, image, shop, option) {
            $("#exchange_prd_name").text(name);
            $("#exchange_prd_img").css("background-image", "url('" + image + "')");
            $("#exchange_prd_shop").text(shop);
            $("#exchange_prd_option").text(option);
            $("#exchange_order_item_id").val(id);

            // 초기화
            $("#exchange_reason_select").val("");
            $("#exchange_detail_reason").val("");
            $("#exchange_detail_reason_bx").hide();

            // Method Reset
            $("input[name='exchange_method'][value='auto']").prop('checked', true);
            $("#exchange_method_text_auto").show();
            $("#exchange_method_text_manual").hide();

            $("#pop_exchange").fadeIn();
        }

        function closeExchangePopup() {
            $("#pop_exchange").fadeOut();
        }

        function openConfirmPopup(id, name, image, shop, option) {
            $("#confirm_prd_name").text(name);
            $("#confirm_prd_img").css("background-image", "url('" + image + "')");
            $("#confirm_prd_shop").text(shop);
            $("#confirm_prd_option").text(option);
            $("#confirm_order_item_id").val(id);

            // 초기화 (별점 5점 기본)
            setRating(5);

            $("#pop_confirm").fadeIn();
        }

        function closeConfirmPopup() {
            $("#pop_confirm").fadeOut();
        }

        function setRating(rating) {
            $("#confirm_rating_value").val(rating);
            $(".star_rating_bx .star").each(function (index) {
                if (index < rating) {
                    $(this).addClass("on");
                } else {
                    $(this).removeClass("on");
                }
            });
        }

        function openReviewPopup(productId, name, image) {
            $("#review_product_id").val(productId);
            $("#review_prd_name").text(name);
            $("#review_prd_img").css("background-image", "url('" + image + "')");
            $("#review_text").val("");
            setReviewRating(5);
            $("#pop_review").fadeIn();
        }

        function closeReviewPopup() {
            $("#pop_review").fadeOut();
        }

        function setReviewRating(rating) {
            $("#review_rating_value").val(rating);
            $(".review_star_rating_bx .star").each(function (index) {
                if (index < rating) {
                    $(this).addClass("on");
                } else {
                    $(this).removeClass("on");
                }
            });
        }
    </script>
@endpush
