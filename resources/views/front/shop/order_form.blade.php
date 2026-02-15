@extends('layouts.frontend')

@section('content')
    <!-- Include Channel CSS for styling to match Legacy Order Form -->
    <link href="/channel_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/channel_assets/css/board.css" rel="stylesheet" type="text/css" />

    <div id="contents" style="padding: 120px 0; min-height: 600px;">
        <div class="row">
            <div class="box box1" style="max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px;">
                <div class="page_info">
                    <div class="ttl" style="font-size: 28px; font-weight: bold; margin-bottom: 20px;">주문/결제</div>
                    <ul class="dep" style="display: flex; gap: 10px; color: #888; margin-bottom: 30px;">
                        <li>HOME</li>
                        <li>></li>
                        <li>Shop</li>
                        <li>></li>
                        <li>주문/결제</li>
                    </ul>
                </div>

                <div class="conbx">
                    <!-- 1. 주문자 정보 -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            주문자 정보</div>
                        <div class="tb01">
                            <table style="width: 100%;">
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">이름
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;"><input type="text"
                                                class="w300" placeholder="이름을 입력하세요" value="{{ Auth::user()->name ?? '' }}"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">연락처
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                                            <input type="text" class="w100"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;" value="010">
                                            -
                                            <input type="text" class="w100"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;"> -
                                            <input type="text" class="w100"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">이메일
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;"><input type="text"
                                                class="w300" placeholder="이메일을 입력하세요"
                                                value="{{ Auth::user()->email ?? '' }}"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. 배송지 정보 -->
                    <div class="con_w" style="margin-bottom: 40px;">
                        <div class="ttl01"
                            style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                            배송지 정보</div>
                        <div class="tb01">
                            <table style="width: 100%;">
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">배송지
                                            선택</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                                            <label><input type="radio" name="addr_type" checked> 회원 정보와 동일</label>
                                            <label style="margin-left: 20px;"><input type="radio" name="addr_type"> 새로운
                                                배송지</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">받는 사람
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;"><input type="text"
                                                class="w300" style="height: 35px; border: 1px solid #ccc; padding: 0 10px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">주소
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                                <input type="text" class="w150" placeholder="우편번호"
                                                    style="height: 35px; border: 1px solid #ccc; padding: 0 10px;">
                                                <button class="btn01"
                                                    style="height: 35px; padding: 0 15px; background: #666; color: #fff; border: none; cursor: pointer;">우편번호
                                                    찾기</button>
                                            </div>
                                            <input type="text" class="w100p" placeholder="기본 주소"
                                                style="width: 100%; height: 35px; border: 1px solid #ccc; padding: 0 10px; margin-bottom: 5px;">
                                            <input type="text" class="w100p" placeholder="상세 주소"
                                                style="width: 100%; height: 35px; border: 1px solid #ccc; padding: 0 10px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">연락처
                                        </th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                                            <input type="text" class="w100"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;"> -
                                            <input type="text" class="w100"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;"> -
                                            <input type="text" class="w100"
                                                style="height: 35px; border: 1px solid #ccc; padding: 0 10px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">배송
                                            요청사항</th>
                                        <td style="padding: 15px; border-bottom: 1px solid #ddd;"><textarea
                                                style="width: 100%; height: 80px; border: 1px solid #ccc; padding: 10px; resize: none;"></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. 결제 금액 및 결제 방법 -->
                    <div style="display: flex; gap: 40px; justify-content: space-between;">
                        <div style="width: 60%;">
                            <div class="ttl01"
                                style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px;">
                                결제 수단</div>
                            <div class="tb01">
                                <table style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td style="padding: 20px; border-bottom: 1px solid #ddd;">
                                                <label style="margin-right: 20px; font-size: 16px;"><input type="radio"
                                                        name="pay_method" checked> 신용카드</label>
                                                <label style="margin-right: 20px; font-size: 16px;"><input type="radio"
                                                        name="pay_method"> 가상계좌</label>
                                                <label style="margin-right: 20px; font-size: 16px;"><input type="radio"
                                                        name="pay_method"> 계좌이체</label>
                                                <label style="font-size: 16px;"><input type="radio" name="pay_method">
                                                    휴대폰결제</label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div style="width: 35%; background: #f8f8f8; padding: 20px; border: 1px solid #ddd;">
                            <div class="ttl01"
                                style="font-size: 20px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
                                결제 상세</div>
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px;">
                                <span>총 상품금액</span>
                                <span>54,000 원</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px;">
                                <span>배송비</span>
                                <span>2,500 원</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px;">
                                <span>할인 금액</span>
                                <span>-0 원</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 2px solid #ccc; font-weight: bold; font-size: 20px; color: #e91e63;">
                                <span>최종 결제 금액</span>
                                <span>56,500 원</span>
                            </div>

                            <a href="{{ route('front.shop.order.complete') }}" class="btn01"
                                style="width: 100%; height: 60px; line-height: 60px; display: block; background: #333; color: #fff; text-align: center; font-size: 18px; margin-top: 30px; border-radius: 5px;">56,500원
                                결제하기</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection