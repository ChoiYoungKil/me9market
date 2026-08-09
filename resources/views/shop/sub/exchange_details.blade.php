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
                            <li><a href="{{ route('shop.order.details') }}">주문</a></li>
                            <li><a href="{{ route('shop.cancel.details') }}">취소</a></li>
                            <li><a href="{{ route('shop.return.details') }}">반품</a></li>
                            <li class="on"><a href="javascript:void(0);">교환</a></li>
                        </ul>
                    </div>
                </div>
                <div class="box box1">
                    <div class="inner_bx">
                        <div id="board">
                            <div class="write02">
                                <div class="f_bx fbx2">
                                    <div class="order_info">
                                        <div class="con_w con1">
                                            <div class="search01">
                                                <form>
                                                    <div class="tb_bx type2">
                                                        <div class="tr">
                                                            <div class="th">주문기간</div>
                                                            <div class="td">
                                                                <ul class="chk03">
                                                                    <li>
                                                                        <input type="radio" name="radio1" id="radio1_1"
                                                                            checked>
                                                                        <label for="radio1_1">최근 1개월</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="radio1" id="radio1_2">
                                                                        <label for="radio1_2">최근 3개월</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="radio1" id="radio1_3">
                                                                        <label for="radio1_3">최근 6개월</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="radio1" id="radio1_4">
                                                                        <label for="radio1_4">최근 1년</label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <div class="tr">
                                                            <div class="th">기간선택</div>
                                                            <div class="td">
                                                                <div class="datepicker_bx01">
                                                                    <input type="test" class="datepicker datepicker01">
                                                                    <span>~</span>
                                                                    <input type="test" class="datepicker datepicker01">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tb_bx">
                                                        <div class="tr w100 btn_w">
                                                            <div class="th">검색명</div>
                                                            <div class="td">
                                                                <div class="txt_search01">
                                                                    <select>
                                                                        <option>전체</option>
                                                                    </select>
                                                                    <input type="text" placeholder="검색어를 입력해주세요">
                                                                </div>
                                                            </div>
                                                            <a href="javascript:void(0);" class="s_btn">조회</a>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="con_w con2">
                                            <div class="ttl">
                                                판매자 <span>( txx2212 )</span>
                                                <div class="r_bx">
                                                    2024-10-14 <span>&nbsp;|&nbsp;</span> 주문번호 : Me9-00929423 <a href="javascript:void(0);"
                                                        class="btn">주문상세</a>
                                                </div>
                                            </div>
                                            <ul class="pd_list type2">
                                                <li>
                                                    <div class="l_bx">
                                                        <p class="txt1">교환신청중</p>
                                                    </div>
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
                                                                <p>교환신청일<br>2024.10.16</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="all">
                                                <div class="txt2">30,000 원 미만 : 2,500 원 <span
                                                        class="bar">&nbsp;|&nbsp;</span> <a href="javascript:void(0);" class="btn pop_btn"
                                                        data-pop="pop1">판매자 문의하기</a></div>
                                            </div>
                                        </div>
                                        <div class="con_w con2">
                                            <div class="ttl">
                                                판매자 <span>( txx2212 )</span>
                                                <div class="r_bx">
                                                    2024-10-14 <span>&nbsp;|&nbsp;</span> 주문번호 : Me9-00929423 <a href="javascript:void(0);"
                                                        class="btn">주문상세</a>
                                                </div>
                                            </div>
                                            <ul class="pd_list type2">
                                                <li>
                                                    <div class="l_bx">
                                                        <p class="txt1 col4">교환완료</p>
                                                    </div>
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
                                                                <p>교환완료일<br>2024.10.16</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="all">
                                                <div class="txt2">30,000 원 미만 : 2,500 원 <span
                                                        class="bar">&nbsp;|&nbsp;</span> <a href="javascript:void(0);" class="btn pop_btn"
                                                        data-pop="pop1">판매자 문의하기</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 페이징 -->
                            <div class="page_bx">
                                <a href="javascript:void(0);" class="page_first">first</a>
                                <a href="javascript:void(0);" class="page_prev">prev</a>
                                <a href="javascript:void(0);" class="num on">1</a>
                                <a href="javascript:void(0);" class="num">2</a>
                                <a href="javascript:void(0);" class="num">3</a>
                                <a href="javascript:void(0);" class="num">4</a>
                                <a href="javascript:void(0);" class="num">5</a>
                                <a href="javascript:void(0);" class="page_next">next</a>
                                <a href="javascript:void(0);" class="page_last">last</a>
                            </div>
                        </div>

                        <!-- 팝업 -->
                        <div class="popup_bx" data-id="pop1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="ttl">상품 문의하기</div>

                                        <div class="conbx">
                                            <div class="con">
                                                <div class="product01">
                                                    <div class="img_bx"
                                                        style="background-image:url({{ asset('shop/images/sub/thum01.jpg') }})">
                                                    </div>
                                                    <div class="txt_bx">
                                                        <div class="txt_w">
                                                            <div class="txt1">대분류 > 중분류 > 소분류</div>
                                                            <strong class="txt2">상품명 111111</strong>
                                                            <!--<div class="txt3">
                                                                        <p>옵션옵션옵션옵션옵션옵션옵션옵션 1 / 2개</p>
                                                                    </div>-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="con">
                                                <div class="c_ttl">문의내용</div>
                                                <div class="f_con">
                                                    <div class="f_bx">
                                                        <div class="f_w w100">
                                                            <input type="text" placeholder="질문 제목">
                                                        </div>
                                                    </div>
                                                    <div class="f_bx">
                                                        <div class="f_w w100">
                                                            <textarea
                                                                placeholder="판매자에게 상품, 배송, 취소, 교환, 반품 등 궁금한 내용을 문의하세요."></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn">
                                            <a href="javascript:void(0);" class="col2">문의하기</a>
                                            <a href="javascript:void(0);" class="close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

        $(".datepicker").datepicker({
            dateFormat: 'yy-mm-dd', //달력 날짜 형태
            showOtherMonths: true, //빈 공간에 현재월의 앞뒤월의 날짜를 표시
            showMonthAfterYear: true, // 월- 년 순서가아닌 년도 - 월 순서
            changeYear: true, //option값 년 선택 가능
            changeMonth: true, //option값  월 선택 가능                      
            yearSuffix: "년", //달력의 년도 부분 뒤 텍스트
            monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 텍스트
            monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 Tooltip
            dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'], //달력의 요일 텍스트
            dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'], //달력의 요일 Tooltip
            minDate: "-5y", //최소 선택일자(-1D:하루전, -1M:한달전, -1Y:일년전)
            maxDate: "+5y", //최대 선택일자(+1D:하루후, -1M:한달후, -1Y:일년후)  
        });
    </script>
@endsection