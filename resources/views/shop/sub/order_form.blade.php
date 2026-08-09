@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join" class="order_form">
                <div class="top_v">
                    <div class="title">주문서 작성</div>
                </div>
                <div class="box box1">
                    <div class="inner_bx">
                        <form>
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx fbx1">
                                        <div class="f_w">
                                            <div class="f_ttl">주문자 정보</div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>주문자 ID</span></th>
                                                            <td>test1234</td>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td>M9-09909</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>주문자 이름<em class="imp">필수</em></span></th>
                                                            <td>
                                                                <input type="text" class="w300" required="required">
                                                            </td>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td>000-0000-****</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>이메일</span></th>
                                                            <td colspan="3">abcde1234@naver.com</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">
                                                배송지 정보
                                                <ul class="chk02 dipI ml10">
                                                    <li>
                                                        <input type="checkbox" id="chk1_1" name="chk1">
                                                        <label for="chk1_1">주문자 정보와 동일</label>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>수신자 명</span></th>
                                                            <td>
                                                                <input type="text" class="w300" required="required">
                                                            </td>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td>
                                                                <div class="tel_bx">
                                                                    <select required="required">
                                                                        <option value="" disabled="" selected=""></option>
                                                                        <option value="1">010</option>
                                                                    </select>
                                                                    <span>-</span>
                                                                    <input type="text" class="tel1" required="required">
                                                                    <span>-</span>
                                                                    <input type="text" class="tel2" required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>이메일</span></th>
                                                            <td colspan="3">
                                                                <div class="email_bx">
                                                                    <input type="text" class="email1" required="required">
                                                                    <span>@</span>
                                                                    <input type="text" class="email2" required="required">
                                                                    <select class="off" required="required">
                                                                        <option value="" selected="">직접입력</option>
                                                                        <option value="1">naver.com</option>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>배송지 선택</span></th>
                                                            <td colspan="3">
                                                                <select class="w170" required="required">
                                                                    <option value="" disabled="" selected="">기본 배송지</option>
                                                                    <option value="1">배송지명1</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>배송주소</span></th>
                                                            <td colspan="3">
                                                                <div class="addr_bx">
                                                                    <input type="text" class="addr1 off" placeholder="우편번호"
                                                                        required="required">
                                                                    <a href="javascript:void(0);" class="btn01">우편번호찾기</a>
                                                                    <input type="text" class="addr2 off" placeholder="주소"
                                                                        required="required">
                                                                    <input type="text" class="addr3 off" placeholder="상세주소"
                                                                        required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>배송 메모</span></th>
                                                            <td colspan="3">
                                                                <textarea required="required"
                                                                    placeholder="배송 메모"></textarea>
                                                                <p class="mt5">** 택배기사님께 전달 말씀을 남겨주세요</p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="f_bx fbx2">
                                        <div class="ttl01">주문 상품 정보</div>
                                        <div class="order_info">
                                            <div class="con_w">
                                                <div class="ttl">판매자 <span>( txx2212 )</span></div>
                                                <ul class="pd_list">
                                                    <li>
                                                        <div class="product01">
                                                            <div class="img_bx"
                                                                style="background-image:url({{ asset('shop/images/sub/thum01.jpg') }})">
                                                            </div>
                                                            <div class="txt_bx">
                                                                <div class="txt_w">
                                                                    <strong class="txt2">상품명 111111</strong>
                                                                    <div class="txt3">
                                                                        <p>옵션옵션옵션옵션옵션옵션옵션옵션 1 / 2개</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="r_bx">
                                                            <div class="txt_w">
                                                                <div class="txt">
                                                                    <strong>2,000</strong> 원
                                                                </div>
                                                                <div class="btn">
                                                                    <a>삭제</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="product01">
                                                            <div class="img_bx"
                                                                style="background-image:url({{ asset('shop/images/sub/thum01.jpg') }})">
                                                            </div>
                                                            <div class="txt_bx">
                                                                <div class="txt_w">
                                                                    <div class="txt1">상품명상품명</div>
                                                                    <strong
                                                                        class="txt2">상품명상품명상품명상품명상품명상품명상품명상품명상품명상품명상품명상품명
                                                                        111111</strong>
                                                                    <div class="txt3">
                                                                        <p>옵션옵션옵션옵션옵션옵션옵션옵션 1 / 2개</p>
                                                                        <p>옵션옵션옵션옵션옵션옵션옵션옵션 1 / 2개</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="r_bx">
                                                            <div class="txt_w">
                                                                <div class="txt">
                                                                    <strong>2,000</strong> 원
                                                                </div>
                                                                <div class="btn">
                                                                    <a>삭제</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="all">
                                                    <div class="txt1">
                                                        총 상품금액 <strong>2,000</strong> 원 + 배송비 <strong>2,500</strong> 원 =
                                                        <span class="fs2"><strong>0,000,000</strong> 원</span>
                                                    </div>
                                                    <div class="txt2">30,000 원 이상 무료배송 ( 개별 상품 기준 )</div>
                                                </div>
                                            </div>
                                            <div class="con_w">
                                                <div class="ttl">판매자2222 <span>( kkttxx02 )</span></div>
                                                <ul class="pd_list">
                                                    <li>
                                                        <div class="product01">
                                                            <div class="img_bx"
                                                                style="background-image:url({{ asset('shop/images/sub/thum01.jpg') }})">
                                                            </div>
                                                            <div class="txt_bx">
                                                                <div class="txt_w">
                                                                    <strong class="txt2">상품명 111111</strong>
                                                                    <div class="txt3">
                                                                        <p>옵션옵션옵션옵션옵션옵션옵션옵션 1 / 2개</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="r_bx">
                                                            <div class="txt_w">
                                                                <div class="txt">
                                                                    <strong>0,000,000</strong> 원
                                                                </div>
                                                                <div class="btn">
                                                                    <a>삭제</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="all">
                                                    <div class="txt1">
                                                        총 상품금액 <strong>0,000,000</strong> 원 + 배송비 <strong>0</strong> 원 =
                                                        <span class="fs2"><strong>0,000,000</strong> 원</span>
                                                    </div>
                                                    <div class="txt2">30,000 원 이상 무료배송 ( 개별 상품 기준 )</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="f_bx fbx3">
                                        <div class="ttl01">주문 결제</div>
                                        <div class="f_inner two">
                                            <div class="f_w">
                                                <div class="f_ttl">결제수단</div>
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <tbody>
                                                            <tr>
                                                                <th class="w160"><span>결제수단</span></th>
                                                                <td>
                                                                    <ul class="chk01">
                                                                        <li>
                                                                            <input type="radio" name="chk2" id="chk2_1"
                                                                                checked="">
                                                                            <label for="chk2_1">신용카드</label>
                                                                        </li>
                                                                        <li>
                                                                            <input type="radio" name="chk2" id="chk2_2">
                                                                            <label for="chk2_2">실시간계좌이체</label>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="f_ttl">포인트 사용 <span class="col2">( 사용가능 보유 포인트 : 10,090 p
                                                        )</span></div>
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <tbody>
                                                            <tr>
                                                                <th class="w160"><span>사용포인트</span></th>
                                                                <td>
                                                                    <div class="r_txt_bx type1">
                                                                        <input type="text" required="required" class="l_f">
                                                                        <span>Point</span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="f_ttl">개인정보 수집, 이용 및 제공 등에 관한 고지</div>
                                                <div class="agree_bx">
                                                    <ul class="con_bx">
                                                        <li>
                                                            <div class="s_txt">
                                                                <input type="checkbox" id="agree1">
                                                                <label for="agree1">고지 내용에 동의합니다 <span
                                                                        class="col2">(필수)</span></label>
                                                                <div class="btn">전문보기</div>
                                                            </div>
                                                            <div class="h_txt">
                                                                고지 내용입니다.<br>
                                                                고지 내용입니다. 고지 내용입니다.<br><br>
                                                                고지 내용입니다. 고지 내용입니다. 고지 내용입니다.<br>
                                                                고지 내용입니다.<br>
                                                                고지 내용입니다.<br>
                                                                고지 내용입니다. 고지 내용입니다.<br><br>
                                                                고지 내용입니다. 고지 내용입니다. 고지 내용입니다.<br>
                                                                고지 내용입니다.
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="f_w all_price">
                                                <div class="price_bx">
                                                    <ul>
                                                        <li><span>총 상품금액</span> 0,000,000 원</li>
                                                        <li><span>배송비</span> + 2,500 원</li>
                                                        <li><span>사용포인트</span> - 2,000 p</li>
                                                    </ul>
                                                    <div class="all">
                                                        <span>최종 결제금액</span>
                                                        <strong>0,000,000</strong> 원
                                                    </div>
                                                </div>
                                                <div class="btm_btn type2 pt0">
                                                    <a href="javascript:void(0);">취소</a>
                                                    <a href="{{ route('shop.order.complete') }}" class="col2">결제하기</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
    <script type="text/javascript">
        $(".agree_bx .s_txt .btn").click(function () {
            $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
        });
    </script>
@endsection