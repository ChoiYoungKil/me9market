@extends('layouts.frontend')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="top_ttl">회원가입</div>
                        <form>
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <div class="f_w">
                                            <div class="f_ttl">회원정보입력</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>가입경로</span></th>
                                                            <td>NAVER</td>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td>M9-09909</td>
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
                                            <div class="f_ttl">회원정보입력</div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>아이디</span></th>
                                                            <td colspan="3">
                                                                <div class="r_btn_w w430">
                                                                    <input type="text" required="required">
                                                                    <a href="#" class="btn01 col2">본인인증</a>
                                                                </div>
                                                                <span class="imp_txt">해당 아이디는 사용가능 합니다.</span>
                                                                <!--<span class="imp_txt fcol2">해당 아이디는 사용중 입니다.</span>-->
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
                                                            <th class="w160"><span>비밀번호</span></th>
                                                            <td colspan="3">
                                                                <input class="w300" type="password" required="required">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>비밀번호확인</span></th>
                                                            <td colspan="3">
                                                                <input class="w300" type="password" required="required">
                                                                <span class="imp_txt">비밀번호가 일치합니다.</span>
                                                                <!--<span class="imp_txt fcol2">비밀번호가 일치하지 않습니다.</span>-->
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">약관동의</div>
                                            <div class="agree_bx">

                                                <ul class="con_bx">
                                                    <li class="all_chk">
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree_all">
                                                            <label for="agree_all">전체 약관 동의</label>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree1">
                                                            <label for="agree1">이용약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            이용약관 내용입니다.<br>
                                                            이용약관 내용입니다. 이용약관 내용입니다.<br><br>
                                                            이용약관 내용입니다. 이용약관 내용입니다. 이용약관 내용입니다.<br>
                                                            이용약관 내용입니다.<br>
                                                            이용약관 내용입니다.<br>
                                                            이용약관 내용입니다. 이용약관 내용입니다.<br><br>
                                                            이용약관 내용입니다. 이용약관 내용입니다. 이용약관 내용입니다.<br>
                                                            이용약관 내용입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree2">
                                                            <label for="agree2">개인정보 수집 및 이용에 관한 안내 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            개인정보 수집 및 이용에 관한 안내입니다.<br>
                                                            개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다.<br><br>
                                                            개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한
                                                            안내입니다.<br>
                                                            개인정보 수집 및 이용에 관한 안내입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree3">
                                                            <label for="agree3">제3자 정보제공 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            제3자 정보제공 동의에 관한 안내입니다.<br>
                                                            제3자 정보제공 동의에 관한 안내입니다. 제3자 정보제공 동의에 관한 안내입니다.<br><br>
                                                            제3자 정보제공 동의에 관한 안내입니다. 제3자 정보제공 동의에 관한 안내입니다. 제3자 정보제공 동의에 관한
                                                            안내입니다.<br>
                                                            제3자 정보제공 동의에 관한 안내입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree4">
                                                            <label for="agree4">마케팅활용동의 <span
                                                                    class="col3">(선택)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            마케팅활용동의 내용입니다.<br>
                                                            마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다.<br><br>
                                                            마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다.<br>
                                                            마케팅활용동의 내용입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree5">
                                                            <label for="agree5">알림정보수신동의 <span
                                                                    class="col3">(선택)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            알림정보수신동의 내용입니다.<br>
                                                            알림정보수신동의 내용입니다. 알림정보수신동의 내용입니다.<br><br>
                                                            알림정보수신동의 내용입니다. 알림정보수신동의 내용입니다. 알림정보수신동의 내용입니다.<br>
                                                            알림정보수신동의 내용입니다.
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="btm_btn type2">
                                    <a href="#" class="col2">가입완료</a>
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