@extends('layouts.frontend')

@section('content')
    <!-- Include Channel CSS for styling -->
    <link href="/channel_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/channel_assets/css/board.css" rel="stylesheet" type="text/css" />

    <div id="contents" style="padding: 120px 0; min-height: 600px;">
        <div class="row">
            <div class="box box1" style="max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px;">
                <div class="page_info">
                    <div class="ttl" style="font-size: 28px; font-weight: bold; margin-bottom: 20px;">장바구니</div>
                    <ul class="dep" style="display: flex; gap: 10px; color: #888; margin-bottom: 30px;">
                        <li>HOME</li>
                        <li>></li>
                        <li>Shop</li>
                        <li>></li>
                        <li>장바구니</li>
                    </ul>
                </div>

                <div class="conbx">
                    <div class="con_w">
                        <div class="tb01">
                            <table style="width: 100%;">
                                <colgroup>
                                    <col width="50px">
                                    <col width="100px">
                                    <col width="">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="150px">
                                    <col width="100px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"></th>
                                        <th>이미지</th>
                                        <th>상품정보</th>
                                        <th>수량</th>
                                        <th>적립금</th>
                                        <th>가격</th>
                                        <th>삭제</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example Data -->
                                    <tr>
                                        <td><input type="checkbox" checked></td>
                                        <td class="t_c">
                                            <div class="img_bx"
                                                style="width: 80px; height: 80px; background: #f0f0f0; margin: 0 auto;">
                                            </div>
                                        </td>
                                        <td class="t_l" style="padding-left: 20px;">
                                            <strong style="font-size: 16px;">[예시] 편안한 코튼 티셔츠</strong>
                                            <p style="color: #888; font-size: 13px; margin-top: 5px;">옵션: 화이트 / L</p>
                                        </td>
                                        <td>
                                            <div class="qty_bx"
                                                style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                                                <button
                                                    style="width: 25px; height: 25px; border: 1px solid #ddd; background: #fff;">-</button>
                                                <input type="text" value="1"
                                                    style="width: 40px; text-align: center; border: 1px solid #ddd; height: 25px;">
                                                <button
                                                    style="width: 25px; height: 25px; border: 1px solid #ddd; background: #fff;">+</button>
                                            </div>
                                        </td>
                                        <td>150 p</td>
                                        <td style="font-weight: bold; color: #333;">15,000 원</td>
                                        <td><button class="btn02" style="padding: 5px 10px; font-size: 12px;">삭제</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" checked></td>
                                        <td class="t_c">
                                            <div class="img_bx"
                                                style="width: 80px; height: 80px; background: #f0f0f0; margin: 0 auto;">
                                            </div>
                                        </td>
                                        <td class="t_l" style="padding-left: 20px;">
                                            <strong style="font-size: 16px;">[예시] 슬림핏 청바지</strong>
                                            <p style="color: #888; font-size: 13px; margin-top: 5px;">옵션: 진청 / 30</p>
                                        </td>
                                        <td>
                                            <div class="qty_bx"
                                                style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                                                <button
                                                    style="width: 25px; height: 25px; border: 1px solid #ddd; background: #fff;">-</button>
                                                <input type="text" value="1"
                                                    style="width: 40px; text-align: center; border: 1px solid #ddd; height: 25px;">
                                                <button
                                                    style="width: 25px; height: 25px; border: 1px solid #ddd; background: #fff;">+</button>
                                            </div>
                                        </td>
                                        <td>390 p</td>
                                        <td style="font-weight: bold; color: #333;">39,000 원</td>
                                        <td><button class="btn02" style="padding: 5px 10px; font-size: 12px;">삭제</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 20px; align-items: center; border-top: 2px solid #333; padding-top: 20px;">
                            <div style="text-align: right;">
                                <p style="font-size: 16px; margin-bottom: 10px;">총 상품금액: <span
                                        style="font-weight: bold; font-size: 20px;">54,000</span> 원</p>
                                <p style="font-size: 16px; margin-bottom: 10px;">배송비: <span
                                        style="font-weight: bold;">2,500</span> 원</p>
                                <p style="font-size: 20px; font-weight: bold; color: #e91e63;">결제 예상 금액: 56,500 원</p>
                            </div>
                        </div>

                        <div class="btm_btn" style="text-align: center; margin-top: 40px;">
                            <a href="/" class="btn02"
                                style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #fff; border: 1px solid #ccc; color: #333; font-size: 16px; margin-right: 10px;">쇼핑
                                계속하기</a>
                            <a href="{{ route('front.checkout.index') }}" class="btn01"
                                style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #333; color: #fff; font-size: 16px;">주문하기</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection