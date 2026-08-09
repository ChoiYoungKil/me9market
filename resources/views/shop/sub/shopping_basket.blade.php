@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join" class="shopping_basket">
                <div class="top_v">
                    <div class="title">주문 관리</div>
                    <div class="tab_bx">
                        <ul>
                            <li class="on"><a href="#">자사상품</a></li>
                            <li><a href="#">공개상품</a></li>
                        </ul>
                    </div>
                </div>
                <div class="box box1">
                    <div class="inner_bx">
                        <form>
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx fbx2">
                                        <div class="order_info">
                                            <div class="con_w con1">
                                                <div class="ttl">
                                                    <ul class="chk02 dipI">
                                                        <li>
                                                            <input type="checkbox" id="chk1_all">
                                                            <label for="chk1_all"></label>
                                                        </li>
                                                    </ul>
                                                    <label for="chk1_all">전체선택</label>
                                                    <span>|</span>
                                                    <button>선택삭제</button>
                                                </div>
                                            </div>
                                            <div class="con_w con2">
                                                <div class="ttl">
                                                    <ul class="chk02 dipI">
                                                        <li>
                                                            <input type="checkbox" id="chk1_1">
                                                            <label for="chk1_1"></label>
                                                        </li>
                                                    </ul>
                                                    <label for="chk1_1">판매자 <span>( txx2212 )</span></label>
                                                </div>
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
                                                                <div class="option">
                                                                    <a class="pop_btn" data-pop="pop1">옵션/수량변경</a>
                                                                </div>
                                                                <div class="txt">
                                                                    <strong>2,000</strong> 원
                                                                </div>
                                                                <div class="btn">
                                                                    <a class="col2">바로가기</a>
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
                                                                <div class="option">
                                                                    <a class="pop_btn" data-pop="pop1">옵션/수량변경</a>
                                                                </div>
                                                                <div class="txt">
                                                                    <strong>2,000</strong> 원
                                                                </div>
                                                                <div class="btn">
                                                                    <a class="col2">바로가기</a>
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
                                            <div class="con_w con3">
                                                <div class="ttl">
                                                    <ul class="chk02 dipI">
                                                        <li>
                                                            <input type="checkbox" id="chk1_2">
                                                            <label for="chk1_2"></label>
                                                        </li>
                                                    </ul>
                                                    <label for="chk1_2">판매자2222 <span>( kkttxx02 )</span></label>
                                                </div>
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
                                                                <div class="option">
                                                                    <a class="pop_btn" data-pop="pop1">옵션/수량변경</a>
                                                                </div>
                                                                <div class="txt">
                                                                    <strong>0,000,000</strong> 원
                                                                </div>
                                                                <div class="btn">
                                                                    <a class="col2">바로가기</a>
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
                                            <div class="con_w con4">
                                                <div class="ttl col2">최종 결제금액 <span>( 총 4 개 )</span></div>
                                                <div class="price_bx02">
                                                    <div class="l_bx">
                                                        <div class="txt_bx">
                                                            <div class="txt1">총 상품금액</div>
                                                            <div class="txt2"><strong>0,000,000</strong> 원</div>
                                                        </div>
                                                        <div class="txt_bx">
                                                            <div class="txt1">배송비</div>
                                                            <div class="txt2"><strong>2,500</strong> 원</div>
                                                        </div>
                                                    </div>
                                                    <div class="r_bx">
                                                        <div class="txt_bx">
                                                            <div class="txt_w">
                                                                <div class="txt1">최종 결제금액</div>
                                                                <div class="txt2"><strong>0,000,000</strong> 원</div>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('shop.order') }}" class="btn">구매하기</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 팝업 -->
                            <div class="popup_bx" data-id="pop1">
                                <div class="pop_w">
                                    <div class="pop_inner">
                                        <div class="pop_con w640">
                                            <div class="close_btn close1">닫기</div>
                                            <div class="ttl">옵션/수량변경</div>

                                            <div class="conbx">
                                                <select>
                                                    <option>옵션선택</option>
                                                </select>
                                                <ul class="option_list">
                                                    <li>
                                                        <div class="txt">옵션옵션옵션옵션옵션옵션옵션옵션</div>
                                                        <div class="counter01">
                                                            <div class="m">-</div>
                                                            <div class="num">1</div>
                                                            <div class="p">+</div>
                                                        </div>
                                                        <div class="price"><strong>0,000,000</strong> 원</div>
                                                        <div class="del">삭제</div>
                                                    </li>
                                                    <li>
                                                        <div class="txt">옵션옵션옵션옵션옵션옵션옵션옵션</div>
                                                        <div class="counter01">
                                                            <div class="m">-</div>
                                                            <div class="num">1</div>
                                                            <div class="p">+</div>
                                                        </div>
                                                        <div class="price"><strong>0,000,000</strong> 원</div>
                                                        <div class="del">삭제</div>
                                                    </li>
                                                    <li>
                                                        <div class="txt">옵션옵션옵션옵션옵션옵션옵션옵션</div>
                                                        <div class="counter01">
                                                            <div class="m">-</div>
                                                            <div class="num">1</div>
                                                            <div class="p">+</div>
                                                        </div>
                                                        <div class="price"><strong>0,000,000</strong> 원</div>
                                                        <div class="del">삭제</div>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- 하단버튼 -->
                                            <div class="btm_btn">
                                                <a href="#" class="col2">옵션변경</a>
                                                <a href="#" class="close_btn">닫기</a>
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
    </script>
@endsection