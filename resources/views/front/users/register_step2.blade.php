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
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                    <div class="txt">자사상품을 등록할 수 있으며, <br class="pc_show2">Shop 채널에서 판매를 할 수 있는 단계</div>
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
                                            <div class="f_ttl">회원사 정보</div>
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
                                                            <th class="w160"><span>회원사 명</span></th>
                                                            <td colspan="3"><input type="text" class="w300"
                                                                    required="required"></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>사업자구분</span></th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="type2_1" id="type2_1_1"
                                                                            checked="">
                                                                        <label for="type2_1_1">개인판매</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="type2_1" id="type2_1_2">
                                                                        <label for="type2_1_2">개인사업자</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="type2_1" id="type2_1_3">
                                                                        <label for="type2_1_3">법인사업자</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                            <th class="w160"><span>사업자등록번호</span></th>
                                                            <td>
                                                                <div class="tel_bx2">
                                                                    <input type="text" class="tel1" required="required">
                                                                    <span>-</span>
                                                                    <input type="text" class="tel2" required="required">
                                                                    <span>-</span>
                                                                    <input type="text" class="tel3" required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td colspan="3">
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
                                                            <th class="w160"><span>회원사 주소지</span></th>
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
                                                            <th class="w160"><span>정산용 계좌번호</span></th>
                                                            <td colspan="3">
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
                                                            <th class="w160"><span>정산용 통장사본</span></th>
                                                            <td colspan="3">
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly"
                                                                        placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                                    <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtn" class="uploadBtn"
                                                                        name="bbs_file1">
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
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree1">
                                                            <label for="agree1">상품 판매시 약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            상품 판매시 약관 동의 내용입니다.<br>
                                                            상품 판매시 약관 동의 내용입니다. 상품 판매시 약관 동의 내용입니다.<br><br>
                                                            상품 판매시 약관 동의 내용입니다. 상품 판매시 약관 동의 내용입니다. 상품 판매시 약관 동의 내용입니다.<br>
                                                            상품 판매시 약관 동의 내용입니다.<br>
                                                            상품 판매시 약관 동의 내용입니다.<br>
                                                            상품 판매시 약관 동의 내용입니다. 상품 판매시 약관 동의 내용입니다.<br><br>
                                                            상품 판매시 약관 동의 내용입니다. 상품 판매시 약관 동의 내용입니다. 상품 판매시 약관 동의 내용입니다.<br>
                                                            상품 판매시 약관 동의 내용입니다.
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="btm_btn type2">
                                    <a href="{{ route('register.step1') }}" class="col3">Step1 기본정보 신청</a>
                                    <a href="javascript:void(0);" class="col2">저장</a>
                                    <a href="{{ route('register.step3') }}" class="col4">Step3 판매권한 신청</a>
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