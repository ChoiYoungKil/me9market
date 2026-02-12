@extends('layouts.channel')

@php
    $dep1_id = "00";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">정보 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>정보 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01">채널 정보</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>채널 코드</span></th>
                                        <td></td>
                                        <th class="w160"><span>대표 관리자 아이디</span></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>비밀 번호</span></th>
                                        <td><a href="#" class="btn01 pop_btn" data-pop="pop1_1">비밀번호변경</a></td>
                                        <th class="w160"><span>판매 권한</span></th>
                                        <td>획득 ( 2025. 10. 10 )$$</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="popup_bx" data-id="pop1_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">비밀번호 변경</div>
                                        </div>

                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="tb01">
                                                    <table>
                                                        <colgroup>
                                                            <col width="160px">
                                                            <col width="">
                                                        </colgroup>
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th>현재 비밀번호</th>
                                                                <td>
                                                                    <input class="w200" type="password" required="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>새로운 비밀번호</th>
                                                                <td>
                                                                    <input class="w200" type="password" required="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>비밀번호 확인</th>
                                                                <td>
                                                                    <input class="w200" type="password" required="required">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="#">변경하기</a>
                                            <a href="#" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="ttl01">회원사 정보</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>회원사 명</span></th>
                                        <td colspan="3">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>사업자구분</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_1" checked="">
                                                    <label for="radio1_1">개인판매</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_2">
                                                    <label for="radio1_2">개인사업자</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_3">
                                                    <label for="radio1_3">법인사업자</label>
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
                                        <th class="w160"><span>대표연락처</span></th>
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
                                        <th class="w160"><span>대표이메일</span></th>
                                        <td colspan="3">
                                            <div class="email_bx">
                                                <input type="text" class="email1" required="required" value="">
                                                <span>@</span>
                                                <input type="text" class="email2" required="required" value="">
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
                                                <input type="text" class="addr1 off" placeholder="우편번호" required="required">
                                                <a href="#" class="btn01">우편번호찾기</a>
                                                <input type="text" class="addr2 off" placeholder="주소" required="required">
                                                <input type="text" class="addr3 off" placeholder="상세주소" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>정산용 계좌번호</span></th>
                                        <td colspan="3">
                                            <div class="bank_bx">
                                                <select required="required">
                                                    <option value="" disabled="" selected="">은행선택</option>
                                                    <option value="1">국민은행</option>
                                                </select>
                                                <input type="text" class="bank1" placeholder="계좌번호 (‘-’없이 숫자만 입력)"
                                                    required="required">
                                                <input type="text" class="bank2" placeholder="예금주" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>정산용 통장사본</span></th>
                                        <td colspan="3">
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly">
                                                <label for="uploadBtn1" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn1" class="uploadBtn" name="bbs_file1">
                                                <div class="del_btn">삭제</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 공유 및 공동구매 상품을 판매하기
                                위해서는 판매인증이 필요합니다. )</span></div>
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>판매인증</span></th>
                                        <td colspan="3"><span class="fcol3">미인증</span></td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자등록증</th>
                                        <td colspan="3">
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly" placeholder="">
                                                <label for="uploadBtn2_1" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn2_1" class="uploadBtn" name="bbs_file1">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                        <td colspan="3">
                                            <div class="bank_bx">
                                                <select required="required">
                                                    <option value="" disabled="" selected="">은행선택</option>
                                                    <option value="1">국민은행</option>
                                                </select>
                                                <input type="text" class="bank1" placeholder="계좌번호 (‘-’없이 숫자만 입력)"
                                                    required="required">
                                                <input type="text" class="bank2" placeholder="예금주" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자 명의 통장사본</th>
                                        <td colspan="3">
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly"
                                                    placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                <label for="uploadBtn2_2" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn2_2" class="uploadBtn" name="bbs_file1">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 판매회원 인증되기전 권한 정보 영역 -->
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>판매인증</span></th>
                                        <td colspan="3"><span class="fcol3">인증대기</span></td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>사업자등록증</span></th>
                                        <td colspan="3">
                                            <div class="f_img">
                                                <div class="img_w"
                                                    style="background-image:url({{ asset('channel_assets/images/sub/thumbnail02.jpg') }})">
                                                </div>
                                            </div>
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly" placeholder="">
                                                <label for="uploadBtn3_1" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn3_1" class="uploadBtn" name="bbs_file1">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                        <td colspan="3">
                                            <div class="bank_bx">
                                                <select required="required">
                                                    <option value="" disabled="" selected="">은행선택</option>
                                                    <option value="1">국민은행</option>
                                                </select>
                                                <input type="text" class="bank1" placeholder="계좌번호 (‘-’없이 숫자만 입력)"
                                                    required="required">
                                                <input type="text" class="bank2" placeholder="예금주" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자 명의 통장사본</th>
                                        <td colspan="3">
                                            <div class="f_img">
                                                <div class="img_w"
                                                    style="background-image:url({{ asset('channel_assets/images/sub/thumbnail02.jpg') }})">
                                                </div>
                                            </div>
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly" placeholder="">
                                                <label for="uploadBtn5" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn5" class="uploadBtn" name="bbs_file1">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 판매회원 인증 후의 권한 정보 영역 -->
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>판매인증</span></th>
                                        <td colspan="3"><span class="fcol2">인증완료 ( 인증완료일 : 2024-01-01 )</span></td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자등록증</th>
                                        <td colspan="3">
                                            <div class="f_img">
                                                <div class="img_w"
                                                    style="background-image:url({{ asset('channel_assets/images/sub/thumbnail02.jpg') }})">
                                                </div>
                                                <span class="f_txt">사업자등록번호 / 000-01-00000</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                        <td colspan="3">
                                            <div class="f_down">
                                                <div class="f_name">국민은행 / 000000000-02-000000 / 통장주 </div>
                                                <a href="#" class="btn01">통장사본 내려받기</a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="ttl01 brb">약관동의</div>
                        <div class="agree01">
                            <div class="c_w">
                                <div class="c_ttl">회원사 권한 약관</div>
                                <ul class="con_bx">
                                    <li>
                                        <div class="s_txt">
                                            <input type="checkbox" id="agree1_1">
                                            <label for="agree1_1">상품 판매시 약관 동의 <span class="col2">(필수)</span></label>
                                            <div class="btn">전문보기</div>
                                        </div>
                                        <div class="h_txt">
                                            상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br><br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br><br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다.
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="c_w">
                                <div class="c_ttl">판매 권한 약관</div>
                                <ul class="con_bx">
                                    <li>
                                        <div class="s_txt">
                                            <input type="checkbox" id="agree2_1">
                                            <label for="agree2_1">판매권한 약관 동의 <span class="col2">(필수)</span></label>
                                            <div class="btn">전문보기</div>
                                        </div>
                                        <div class="h_txt">
                                            판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br><br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br><br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="btm_btn mt10">
                            <a href="#">정보 수정</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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

        /* 파일 */
        var uploadFile = $('.fileBox .uploadBtn');
        uploadFile.on('change', function () {
            if (window.FileReader) {
                var filename = $(this)[0].files[0].name;
            } else {
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }
            $(this).parents('.fileBox').find('.fileName').val(filename);
            $(this).parents('.fileBox').find('.fileName').addClass("on");
        });
        $(".fileBox .del_btn").click(function () {
            $(this).siblings("input").val("");
            $(this).siblings(".fileName").removeClass("on");
        });

        /* 약관 */
        $(".agree01 .s_txt .btn").click(function () {
            $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
        });
    </script>
@endpush