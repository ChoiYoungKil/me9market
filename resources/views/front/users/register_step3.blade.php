@extends('layouts.frontend')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="step_bx">
                            <ul>
                                <li class="on">
                                    <div class="round">
                                        <div>
                                            <p>Step 1</p>
                                            <strong>기본정보</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>-->
                                </li>
                                <li class="on">
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">자사상품을 등록할 수 있으며, <br class="pc_show2">Shop 채널에서 판매를 할 수 있는 단계</div>-->
                                </li>
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 3</p>
                                            <strong>판매자권한</strong>
                                        </div>
                                    </div>
                                    <div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>
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
                                                            <td><a href="{{ url()->current() }}" class="btn01">비밀번호 변경</a></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 공유 및
                                                    공동구매 상품을 판매하기 위해서는 판매인증이 필요합니다. )</span></div>
                                            <div class="tb01 type2">
                                                <table>
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160">판매인증</th>
                                                            <td><strong class="fcol3">미인증</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">사업자등록증</th>
                                                            <td>
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly"
                                                                        placeholder="">
                                                                    <label for="uploadBtn2" class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtn2" class="uploadBtn"
                                                                        name="bbs_file1">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                                            <td>
                                                                <div class="bank_bx">
                                                                    <select required="required">
                                                                        <option value="" disabled="" selected="">은행선택
                                                                        </option>
                                                                        <option value="1">국민은행</option>
                                                                    </select>
                                                                    <input type="text" class="bank1"
                                                                        placeholder="계좌번호 (‘-’없이 숫자만 입력)"
                                                                        required="required">
                                                                    <input type="text" class="bank2" placeholder="예금주"
                                                                        required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">사업자 명의 통장사본</th>
                                                            <td>
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly"
                                                                        placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                                    <label for="uploadBtn3" class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtn3" class="uploadBtn"
                                                                        name="bbs_file1">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div class="btm_btn t_r pt10 type2">
                                                    <a href="{{ url()->current() }}" class="col2">인증요청</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">약관동의</div>
                                            <div class="agree_bx">
                                                <ul class="con_bx">
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree1">
                                                            <label for="agree1">판매권한 약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            판매권한 약관 동의 내용입니다.<br>
                                                            판매권한 약관 동의 내용입니다. 판매권한 약관 동의 내용입니다.<br><br>
                                                            판매권한 약관 동의 내용입니다. 판매권한 약관 동의 내용입니다. 판매권한 약관 동의 내용입니다.<br>
                                                            판매권한 약관 동의 내용입니다.<br>
                                                            판매권한 약관 동의 내용입니다.<br>
                                                            판매권한 약관 동의 내용입니다. 판매권한 약관 동의 내용입니다.<br><br>
                                                            판매권한 약관 동의 내용입니다. 판매권한 약관 동의 내용입니다. 판매권한 약관 동의 내용입니다.<br>
                                                            판매권한 약관 동의 내용입니다.
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="btm_btn type2">
                                    <a href="{{ route('register.step1') }}" class="col3">Step1 기본정보 신청</a>
                                    <a href="{{ route('register.step2') }}" class="col3">Step2 회원사권한 신청</a>
                                    <a href="{{ url()->current() }}" class="col2">저장</a>
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

        var uploadFile = $('.fileBox .uploadBtn');
        uploadFile.on('change', function () {
            if (window.FileReader) {
                var filename = $(this)[0].files[0].name;
            } else {
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }
            $(this).parents('.fileBox').find('.fileName').val(filename);
        });
    </script>
@endsection