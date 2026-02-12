@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '1')
@section('dep3_id', '3')

@section('content')
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">회원 탈퇴 신청</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>정보관리</li>
                            <li>회원 탈퇴 신청</li>
                        </ul>
                    </div>

                    <div class="conbx">
                        <div class="con_w">
                            <div class="ttl01">탈퇴 정보 확인</div>

                            <div class="tb01">
                                <table class="two">
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>회원번호</span></th>
                                            <td>asd1234</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>아이디</span></th>
                                            <td>id1111</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>이름</span></th>
                                            <td>홍길동</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>보유 포인트</span></th>
                                            <td>0000point</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>판매 중 내역</span></th>
                                            <td>00건</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="con_w">
                            <div class="ttl01">탈퇴하시는 이유는 무엇인가요?</div>

                            <div class="tb01 type2">
                                <table class="two">
                                    <tbody class="textL">
                                        <tr>
                                            <td>
                                                <ul class="chk01 disb">
                                                    <li>
                                                        <input type="radio" name="radio1" id="radio1_1" checked="">
                                                        <label for="radio1_1">이유 1</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="radio1" id="radio1_2">
                                                        <label for="radio1_2">이유 2</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="radio1" id="radio1_3">
                                                        <label for="radio1_3">이유 3</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="radio1" id="radio1_4">
                                                        <label for="radio1_4">이유 4</label>
                                                    </li>
                                                    <li class="dotTop">
                                                        <input type="radio" name="radio1" id="radio1_5">
                                                        <label class="w100p" for="radio1_5">
                                                            기타
                                                            <textarea class="mt5" value="" required="required"></textarea>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="con_w">
                            <div class="ttl01">비밀번호 입력</div>

                            <div class="tb01 type2">
                                <table class="two">
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>비밀번호</span></th>
                                            <td>
                                                <input class="w200" type="password" value="" required="required">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>비밀번호 확인</span></th>
                                            <td>
                                                <input class="w200" type="password" value="" required="required">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 하단버튼 -->
            <div class="btm_btn">
                <a href="#" class="pop_btn" data-pop="pop1_1">회원 탈퇴 신청하기</a>
            </div>

            <!-- 회원 탈퇴 신청하기 팝업 -->
            <div class="popup_bx" data-id="pop1_1">
                <div class="pop_w">
                    <div class="pop_inner">
                        <div class="pop_con w560">
                            <div class="close_btn close1">닫기</div>
                            <div class="conbx">
                                <div class="con_w">
                                    <div class="imp_bx01 bN">
                                        <div class="txt2 mt0">탈퇴 처리 시 현재 보유 포인트는 모두 삭제 됩니다. <br>내 상품을 다른 회원사에서 판매 중일 때는
                                            탈퇴가
                                            거부 됩니다.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 하단버튼 -->
                            <div class="btm_btn mt10">
                                {{-- href="../sub01/withdraw.php" in source, we will link to a withdraw route later --}}
                                <a href="{{ url('/mypage/withdraw/success') }}">확인</a>
                                <a href="#" class="col5 close_btn">닫기</a>
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