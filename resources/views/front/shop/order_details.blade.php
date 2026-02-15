@extends('layouts.frontend')

@section('content')
    <!-- Include Channel CSS for styling to match Legacy Order Form -->
    <link href="/channel_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/channel_assets/css/board.css" rel="stylesheet" type="text/css" />

    <div id="contents" style="padding: 120px 0; min-height: 600px;">
        <div class="row">
            <div class="box box1" style="max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px;">
                <!-- Page Info -->
                <div class="page_info">
                    <div class="ttl" style="font-size: 28px; font-weight: bold; margin-bottom: 20px;">주문 상세 내역</div>
                    <ul class="dep" style="display: flex; gap: 10px; color: #888; margin-bottom: 30px;">
                        <li>HOME</li>
                        <li>></li>
                        <li>Shop</li>
                        <li>></li>
                        <li>주문 상세</li>
                    </ul>
                </div>

                <div class="conbx">
                    <!-- Order Information -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            주문자 정보 <span style="font-size: 14px; color: #666; margin-left:10px;">2024.10.14 ( 주문번호:
                                Me9-00929423 )</span></div>
                        <div class="tb01">
                            <table style="width: 100%;">
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">주문자
                                            이름</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">홍길동</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">휴대폰
                                            번호</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">010-0000-0000</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">이메일
                                            주소</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">test1234@naver.com</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            배송 정보</div>
                        <div class="tb01">
                            <table style="width: 100%;">
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">받는 사람
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">홍길동</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">휴대폰
                                            번호</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">010-0000-0000</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">주소
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">22012 서울특별시 광진구 가나다동
                                            119-12</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">배송메모
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">문 앞에 놓아주세요</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            구매 상품 <span style="font-size: 14px; color: #e91e63;">판매자 ( txx2212 )</span></div>
                        <div class="tb01">
                            <table style="width: 100%; border-top: 2px solid #333;">
                                <colgroup>
                                    <col width="100px">
                                    <col width="">
                                    <col width="150px">
                                    <col width="150px">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd; text-align:center;">
                                            <span
                                                style="display:inline-block; padding: 5px 10px; background:#eee; font-size:12px;">구매확정</span>
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
                                        <td
                                            style="padding: 15px; border-bottom: 1px solid #ddd; text-align:center; color:#888; font-size:14px;">
                                            구매확정일<br> 2024.10.16</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            결제 정보</div>
                        <div style="display: flex; gap: 40px; justify-content: space-between;">
                            <div style="width: 60%;">
                                <div class="tb01">
                                    <table style="width: 100%;">
                                        <tbody class="textL">
                                            <tr>
                                                <th
                                                    style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd; width: 160px;">
                                                    결제수단</th>
                                                <td style="padding: 15px; border-bottom: 1px solid #ddd;">카드결제</td>
                                            </tr>
                                            <tr>
                                                <th
                                                    style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd; width: 160px;">
                                                    카드종류</th>
                                                <td style="padding: 15px; border-bottom: 1px solid #ddd;">현대카드</td>
                                            </tr>
                                            <tr>
                                                <th
                                                    style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd; width: 160px;">
                                                    카드번호</th>
                                                <td style="padding: 15px; border-bottom: 1px solid #ddd;">22030222-******
                                                </td>
                                            </tr>
                                            <tr>
                                                <th
                                                    style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd; width: 160px;">
                                                    적립포인트</th>
                                                <td style="padding: 15px; border-bottom: 1px solid #ddd;">+ 1000 point</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div style="width: 35%; background: #f8f8f8; padding: 20px; border: 1px solid #ddd;">
                                <div
                                    style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; border-bottom: 1px solid #ccc; padding-bottom:10px;">
                                    <span>총 상품금액</span>
                                    <span>3,000 원</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; border-bottom: 1px solid #ccc; padding-bottom:10px;">
                                    <span>배송비</span>
                                    <span>+ 2,500 원</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; border-bottom: 1px solid #ccc; padding-bottom:10px;">
                                    <span>포인트 사용</span>
                                    <span>- 2,000 p</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; margin-top: 10px; font-weight: bold; font-size: 20px; color: #e91e63;">
                                    <span>최종 결제금액</span>
                                    <span>4,500 원</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn_area" style="text-align:center; margin-top:50px;">
                        <a href="/" class="btn01"
                            style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #333; color: #fff; font-size: 16px;">쇼핑
                            계속하기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection