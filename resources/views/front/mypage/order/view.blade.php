@extends('layouts.mypage')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div id="order">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">주문 상세</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>주문 상세</li>
                        </ul>
                    </div>

                    <div class="ttl01">주문자 정보 <span class="col2">2024.10.14 ( 주문번호: Me9-00929423 )</span></div>

                    <div class="tb01">
                        <table>
                            <tbody class="textL">
                                <tr>
                                    <th class="w160">주문자 이름</th>
                                    <td>홍길동</td>
                                </tr>
                                <tr>
                                    <th class="w160">휴대폰 번호</th>
                                    <td>010-0000-0000</td>
                                </tr>
                                <tr>
                                    <th class="w160">이메일 주소</th>
                                    <td>test1234@naver.com</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box2">
                    <div class="ttl01">배송 정보</div>

                    <div class="tb01">
                        <table>
                            <tbody class="textL">
                                <tr>
                                    <th class="w160">받는 사람</th>
                                    <td>홍길동</td>
                                </tr>
                                <tr>
                                    <th class="w160">휴대폰 번호</th>
                                    <td>010-0000-0000</td>
                                </tr>
                                <tr>
                                    <th class="w160">주소</th>
                                    <td>22012 서울특별시 광진구 가나다동 119-12</td>
                                </tr>
                                <tr>
                                    <th class="w160">배송메모</th>
                                    <td>배송메모 <br>1122</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box3">
                    <div class="ttl01">구매 상품 <span class="col3">판매자 ( txx2212 )</span></div>

                    <div class="tb02">
                        <table>
                            <colgroup>
                                <col width="96px">
                                <col width="">
                                <col width="150px">
                                <col width="111px">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="status col1">구매확정</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">2,000 원</td>
                                    <td class="t_r">구매확정일<br> 2024.10.16</td>
                                </tr>
                                <tr>
                                    <td class="status col2">취소완료</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">2,000 원</td>
                                    <td class="t_r">취소완료일<br> 2024.10.16</td>
                                </tr>
                                <tr>
                                    <td class="status col3">반품완료</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">2,000 원</td>
                                    <td class="t_r">반품완료일<br> 2024.10.16</td>
                                </tr>
                                <tr>
                                    <td class="status col4">교환완료</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <p class="col2">교환 주문번호: Me9-RE-00929111</p>
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">1,000 원</td>
                                    <td class="t_r">교환완료일<br> 2024.10.16</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box4">
                    <div class="ttl01">결제 정보</div>

                    <div class="tb03">
                        <div class="l_bx">
                            <table>
                                <tbody>
                                    <tr>
                                        <th>결제수단</th>
                                        <td>카드결제</td>
                                    </tr>
                                    <tr>
                                        <th>카드종류</th>
                                        <td>현대카드</td>
                                    </tr>
                                    <tr>
                                        <th>카드번호</th>
                                        <td>22030222-******</td>
                                    </tr>
                                    <tr>
                                        <th>적립포인트</th>
                                        <td>+ 1000 point</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="r_bx">
                            <table>
                                <tbody>
                                    <tr>
                                        <th>총 상품금액</th>
                                        <td>3,000 원</td>
                                    </tr>
                                    <tr>
                                        <th>배송비</th>
                                        <td>+ 2,500 원</td>
                                    </tr>
                                    <tr>
                                        <th>포인트 사용</th>
                                        <td>- 2,000 p</td>
                                    </tr>
                                    <tr class="last">
                                        <th>최종 결제금액</th>
                                        <td><strong>4,500</strong> 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="txt01 t_r">( 배송비 ) 30,000원 미만 : 2,500 원</div>
                </div>

                <div class="box box5">
                    <div class="ttl01">취소 상품 <span class="col3">판매자 ( txx2212 )</span></div>

                    <div class="tb02">
                        <table>
                            <colgroup>
                                <col width="96px">
                                <col width="">
                                <col width="150px">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="status col2">취소완료</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">2,000 원</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="txt01 t_r">( 배송비 ) 무료</div>

                    <div class="tb04">
                        <table>
                            <tbody>
                                <tr>
                                    <th>취소 사유</th>
                                    <td>단순변심</td>
                                </tr>
                                <tr>
                                    <th>결제 수단</th>
                                    <td>카드취소 2,000 원</td>
                                </tr>
                                <tr>
                                    <th>포인트 환불</th>
                                    <td>200 point </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box6">
                    <div class="ttl01">반품 상품 <span class="col3">판매자 ( txx2212 )</span></div>

                    <div class="tb02">
                        <table>
                            <colgroup>
                                <col width="96px">
                                <col width="">
                                <col width="150px">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="status col3">반품완료</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">2,000 원</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="txt01 t_r">( 배송비 ) 무료</div>

                    <div class="tb04">
                        <table>
                            <tbody>
                                <tr>
                                    <th>반품 사유</th>
                                    <td>처음 사려고 했던 상품이 상품표기 이상으로 구매를 잘 못 하였습니다. <br>그래서 반품을 신청합니다.</td>
                                </tr>
                                <tr>
                                    <th>결제 수단</th>
                                    <td>무통장입금 환불 2,000 원</td>
                                </tr>
                                <tr>
                                    <th>포인트 환불</th>
                                    <td>200 point </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box7">
                    <div class="ttl01">교환 상품 <span class="col3">판매자 ( txx2212 )</span></div>

                    <div class="tb02">
                        <table>
                            <colgroup>
                                <col width="96px">
                                <col width="">
                                <col width="150px">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="status col4">교환완료</td>
                                    <td class="info">
                                        <div class="con_w">
                                            <div class="img_bx"
                                                style="background:url({{ asset('mypage/images/sub/thumbnail01.jpg') }})">
                                            </div>
                                            <div class="txt_w">
                                                <p class="col2">교환 주문번호: Me9-RE-00929111</p>
                                                <strong class="subject">상품명 111111</strong>
                                                <p>옵션 1 / 2개</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price t_r">2,000 원</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="txt01 t_r">( 배송비 ) 무료</div>

                    <div class="tb04">
                        <table>
                            <tbody>
                                <tr>
                                    <th>교환 사유</th>
                                    <td>다른 상품이 배송되었습니다.</td>
                                </tr>
                                <tr>
                                    <th>결제 수단</th>
                                    <td>2,500 원</td>
                                </tr>
                                <tr>
                                    <th>포인트 환불</th>
                                    <td>200 point </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection