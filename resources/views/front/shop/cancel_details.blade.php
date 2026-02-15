@extends('layouts.frontend')

@section('content')
    <link href="/channel_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/channel_assets/css/board.css" rel="stylesheet" type="text/css" />

    <div id="contents" style="padding: 120px 0; min-height: 600px;">
        <div class="row">
            <div class="box box1" style="max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px;">
                <div class="page_info">
                    <div class="ttl" style="font-size: 28px; font-weight: bold; margin-bottom: 20px;">취소 상세 내역</div>
                    <ul class="dep" style="display: flex; gap: 10px; color: #888; margin-bottom: 30px;">
                        <li>HOME</li>
                        <li>></li>
                        <li>Shop</li>
                        <li>></li>
                        <li>취소 상세</li>
                    </ul>
                </div>

                <div class="conbx">
                    <!-- Cancelled Items -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            취소 상품 <span style="font-size: 14px; color: #e91e63;">판매자 ( txx2212 )</span></div>
                        <div class="tb01">
                            <table style="width: 100%; border-top: 2px solid #333;">
                                <colgroup>
                                    <col width="100px">
                                    <col width="">
                                    <col width="150px">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd; text-align:center;">
                                            <span
                                                style="display:inline-block; padding: 5px 10px; background:#eee; font-size:12px;">취소완료</span>
                                        </td>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                                            <div style="display:flex; align-items:center;">
                                                <div
                                                    style="width:80px; height:80px; background:#f4f4f4; margin-right:20px; background-size:cover; background-position:center; background-image:url('/me9market/images/sub/thumbnail01.jpg');">
                                                </div>
                                                <div>
                                                    <strong style="display:block; font-size:16px; margin-bottom:5px;">상품명
                                                        111111</strong>
                                                    <p style="color:#888;">옵션 1 / 2개</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            style="padding: 15px; border-bottom: 1px solid #ddd; text-align:right; font-weight:bold;">
                                            2,000 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align:right; margin-top:10px; color:#888;">( 배송비 ) 무료</div>
                    </div>

                    <!-- Refund/Cancel Info -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            취소 정보</div>
                        <div class="tb01">
                            <table style="width: 100%;">
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">취소 사유
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">단순변심</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">결제 수단
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">카드취소 2,000 원</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">포인트
                                            환불</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">200 point</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="btn_area" style="text-align:center; margin-top:50px;">
                        <a href="{{ route('front.shop.order.details') }}" class="btn01"
                            style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #f4f4f4; color: #333; font-size: 16px; margin-right:10px; border:1px solid #ddd;">목록으로</a>
                        <a href="/" class="btn01"
                            style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #333; color: #fff; font-size: 16px;">쇼핑
                            계속하기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection