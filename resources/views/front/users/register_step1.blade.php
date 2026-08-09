@extends('layouts.frontend')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="step_bx">
                            <ul>
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 1</p>
                                            <strong>기본정보</strong>
                                        </div>
                                    </div>
                                    <div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>
                                </li>
                                <li>
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">자사상품을 등록할 수 있으며, <br class="pc_show2">Shop 채널에서 판매를 할 수 있는 단계</div>-->
                                </li>
                                <li>
                                    <div class="round">
                                        <div>
                                            <p>Step 3</p>
                                            <strong>판매자권한</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>-->
                                </li>
                            </ul>
                        </div>
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
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td>M9-09909</td>
                                                            <th class="w160"><span>가입일</span></th>
                                                            <td>2024-10-01 12:21:12</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>아이디</span></th>
                                                            <td>abcde1234</td>
                                                            <th class="w160"><span>비밀번호</span></th>
                                                            <td><a href="javascript:void(0);" class="btn01">비밀번호 변경</a></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">회원정보입력</div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>이름</span></th>
                                                            <td><input type="text" value="홍길동" required="required"></td>
                                                            <th class="w160"><span>성별</span></th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="gender" id="gender_m"
                                                                            checked="">
                                                                        <label for="gender_m">남성</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="gender" id="gender_w">
                                                                        <label for="gender_w">여성</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td colspan="3">
                                                                <div class="r_btn_w w457">
                                                                    <div class="tel_bx">
                                                                        <select required="required">
                                                                            <option value="" disabled=""></option>
                                                                            <option value="1" selected="">010</option>
                                                                        </select>
                                                                        <span>-</span>
                                                                        <input type="text" class="tel1" required="required"
                                                                            value="0000">
                                                                        <span>-</span>
                                                                        <input type="text" class="tel2" required="required"
                                                                            value="0000">
                                                                    </div>
                                                                    <a href="javascript:void(0);" class="btn01 col2">본인인증</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>이메일</span></th>
                                                            <td colspan="3">
                                                                <div class="email_bx">
                                                                    <input type="text" class="email1" required="required"
                                                                        value="abcde1234">
                                                                    <span>@</span>
                                                                    <input type="text" class="email2" required="required"
                                                                        value="naver.com">
                                                                    <select class="off" required="required">
                                                                        <option value="">직접입력</option>
                                                                        <option value="1" selected="">naver.com</option>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>주소<br class="pc_show">(배송지)</span></th>
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
                                    <a href="javascript:void(0);" class="col3">취소</a>
                                    <a href="javascript:void(0);" class="col2">저장</a>
                                    <a href="{{ route('register.step2') }}" class="col4">Step2 회원사 등록</a>
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