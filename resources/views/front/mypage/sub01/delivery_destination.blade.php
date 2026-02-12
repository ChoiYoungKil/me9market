@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '1')
@section('dep3_id', '2')

@section('content')
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">배송지 설정</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>정보관리</li>
                            <li>배송지 설정</li>
                        </ul>
                    </div>

                    <div class="ttl01">기본 배송지</div>

                    <div class="tb01 type2">
                        <table class="two">
                            <tbody class="textL">
                                <tr>
                                    <th class="w160"><span>배송지 명</span></th>
                                    <td>
                                        <input type="text" value="" required="required">
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w160"><span>배송지 주소</span></th>
                                    <td>
                                        <div class="addr_bx">
                                            <input type="text" class="addr1 off" placeholder="우편번호" required="required">
                                            <a href="#" class="btn01">우편번호찾기</a>
                                            <input type="text" class="addr2 off" placeholder="주소" required="required">
                                            <input type="text" class="addr3 off" placeholder="상세주소" required="required">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt10 right">
                        <a href="#">기본 배송지 수정</a>
                    </div>
                </div>

                <div class="box box2">
                    <div class="ttl01">추가 배송지</div>

                    <div class="tb01 ovS size10">
                        <table>
                            <colgroup>
                                <col width="13%">
                                <col width="">
                                <col width="130px">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>배송지 명</th>
                                    <th>주소</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>회사</td>
                                    <td>(우편번호) 주소 및 상세 주소</td>
                                    <td>
                                        <a href="#" class="btn02 col2 pop_btn" data-pop="pop2_1">수정</a>
                                        <a href="#" class="btn02 col7">삭제</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="btm_btn right mt10">
                        <!-- 페이징 -->
                        <div class="page_bx1">
                            <a href="#" class="page_first">first</a>
                            <a href="#" class="page_prev">prev</a>
                            <a href="#" class="num on">1</a>
                            <a href="#" class="num">2</a>
                            <a href="#" class="num">3</a>
                            <a href="#" class="num">4</a>
                            <a href="#" class="num">5</a>
                            <a href="#" class="page_next">next</a>
                            <a href="#" class="page_last">last</a>
                        </div>

                        <!-- 하단버튼 -->
                        <a href="#" class="pop_btn" data-pop="pop1_1">배송지 추가하기</a>
                    </div>

                    <!-- 배송지 추가하기 팝업 -->
                    <div class="popup_bx" data-id="pop1_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w640">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">배송지 추가하기</div>
                                    </div>

                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160">배송지 명</th>
                                                            <td>
                                                                <input type="text" value="" required="required">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">주소</th>
                                                            <td>
                                                                <div class="addr_bx">
                                                                    <input type="text" class="addr1 off" placeholder="우편번호"
                                                                        required="required">
                                                                    <a href="#" class="btn01">우편번호찾기</a>
                                                                    <input type="text" class="addr2 off" placeholder="주소"
                                                                        required="required">
                                                                    <input type="text" class="addr3 off" placeholder="상세주소"
                                                                        required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">배송지 타입</th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="pop1_1_radio1"
                                                                            id="pop1_1_radio1_1" checked="">
                                                                        <label for="pop1_1_radio1_1">기본</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="pop1_1_radio1"
                                                                            id="pop1_1_radio1_2">
                                                                        <label for="pop1_1_radio1_2">추가</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 하단버튼 -->
                                    <div class="btm_btn mt10">
                                        <a href="#">배송지 추가하기</a>
                                        <a href="#" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 수정 팝업 -->
                    <div class="popup_bx" data-id="pop2_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w640">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">배송지 수정하기</div>
                                    </div>

                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160">배송지 명</th>
                                                            <td>
                                                                <input type="text" value="" required="required">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">주소</th>
                                                            <td>
                                                                <div class="addr_bx">
                                                                    <input type="text" class="addr1 off" placeholder="우편번호"
                                                                        required="required">
                                                                    <a href="#" class="btn01">우편번호찾기</a>
                                                                    <input type="text" class="addr2 off" placeholder="주소"
                                                                        required="required">
                                                                    <input type="text" class="addr3 off" placeholder="상세주소"
                                                                        required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">배송지 타입</th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="pop2_1_radio1"
                                                                            id="pop2_1_radio1_1" checked="">
                                                                        <label for="pop2_1_radio1_1">기본</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="pop2_1_radio1"
                                                                            id="pop2_1_radio1_2">
                                                                        <label for="pop2_1_radio1_2">추가</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 하단버튼 -->
                                    <div class="btm_btn mt10">
                                        <a href="#">배송지 수정하기</a>
                                        <a href="#" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 배송지 타입 => 기본 선택 시 팝업 -->
                    <div class="popup_bx" data-id="pop3_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w560">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="imp_bx01 bN">
                                                <div class="txt2 mt0">기본 배송지로 변경하시겠습니까? <br>기존 기본 배송지는 추가 배송지로 변경됩니다.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 하단버튼 -->
                                    <div class="btm_btn mt10">
                                        <a href="#">확인</a>
                                        <a href="#" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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