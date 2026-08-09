@extends('layouts.channel')

@php
    $dep1_id = "00";
    $dep1_tit = "서브관리자관리";
@endphp

@section('page_type', 'sub')

@section('content')
<div id="contents">
    <div class="row">
        <div class="box box1">
            <div class="page_info">
                <div class="ttl">서브관리자 관리</div>
                <ul class="dep">
                    <li>HOME</li>
                    <li>서브관리자 관리</li>
                </ul>
            </div>
            <div class="conbx">
                <div class="con_w">
                    <div class="tb01">
                        <table>
                            <colgroup>
                                <col width="160px">
                                <col width="">
                                <col width="160px">
                                <col width="">
                            </colgroup>
                            <tbody class="textL">
                                <tr>
                                    <th class="w160"><span>회원번호</span></th>
                                    <td colspan="3">
                                        <input type="text" value="" required="required">
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w160"><span>회원아이디</span></th>
                                    <td colspan="3">
                                        <input type="text" value="" required="required">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="btm_btn right mt10 search-actions">
                        <a href="javascript:void(0);" class="type2">검색</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="box box1">
            <div class="conbx">
                <div class="con_w">
                    <div class="list_top1 btn">
                        <div class="count">총 <strong>00</strong> 건</div>
                        <div class="btn_bx">
                            <a href="javascript:void(0);" class="btn01 col5 pop_btn" data-pop="pop1_1">서브관리자 등록</a>
                        </div>
                    </div>
                    <div class="tb01 ovS">
                        <table>
                            <colgroup>
                                <col width="80px">
                                <col width="80px">
                                <col width="120px">
                                <col width="">
                                <col width="120px">
                                <col width="230px">
                                <col width="150px">
                                <col width="180px">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>상태</th>
                                    <th>회원번호</th>
                                    <th>이메일</th>
                                    <th>발주담당자</th>
                                    <th>운영기간</th>
                                    <th>권한</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>00</td>
                                    <td>운영</td>
                                    <td>m0020292</td>
                                    <td>email_id001@email_domain.com</td>
                                    <td>홍길동</td>
                                    <td>2024.10.01 01시 ~ 2024.11.01 23시</td>
                                    <td>
                                        Shop채널관리<br>
                                        상품관리<br>
                                        공동구매관리<br>
                                        주문관리
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="btn02 col5 pop_btn" data-pop="pop2_1">보기</a>
                                        <a href="javascript:void(0);" class="btn02 col7 pop_btn" data-pop="pop3_1">수정</a>
                                        <a href="javascript:void(0);" class="btn02">삭제</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                    <div class="page_bx1">
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

                    <!-- 팝업 -->
                    <!-- 서브관리자 등록 팝업 -->
                    <div class="popup_bx" data-id="pop1_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w800">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">서브관리자 등록</div>
                                    </div>
                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="ttl01">회원찾기</div>
                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="180px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <td class="pr0">
                                                                <select required="required">
                                                                    <option value="" disabled="" selected="">회원번호 선택
                                                                    </option>
                                                                    <option value="1">회원번호1</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <div class="r_btn_w">
                                                                    <input type="text" required="required">
                                                                    <a href="javascript:void(0);" class="btn01">회원찾기</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="con_w">
                                            <div class="ttl01">회원정보</div>

                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td colspan="3">I8878</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원아이디</span></th>
                                                            <td colspan="3"><a class="fcol2 link"
                                                                    href="mailto:</a>">test@test.com</a></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원명</span></th>
                                                            <td colspan="3">홍길동</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!--<div class="no_data">일치하는 회원이 없습니다.</div>-->
                                            </div>
                                        </div>

                                        <div class="con_w">
                                            <div class="ttl01">서브관리정보</div>

                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>이용기간</span></th>
                                                            <td colspan="3">
                                                                <input class="datepicker w160" type="text"
                                                                    required="required" readonly="">
                                                                &nbsp;&nbsp;~&nbsp;&nbsp;
                                                                <input class="datepicker w160" type="text"
                                                                    required="required" readonly="">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>권한 선택</span></th>
                                                            <td colspan="3">
                                                                <ul class="chk02">
                                                                    <li>
                                                                        <input type="checkbox" name="pop1_1_chk1"
                                                                            id="pop1_1_chk1_1" checked="">
                                                                        <label for="pop1_1_chk1_1">Shop 채널</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="checkbox" name="pop1_1_chk1"
                                                                            id="pop1_1_chk1_2">
                                                                        <label for="pop1_1_chk1_2">상품관리</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="checkbox" name="pop1_1_chk1"
                                                                            id="pop1_1_chk1_3">
                                                                        <label for="pop1_1_chk1_3">공동상품관리</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="checkbox" name="pop1_1_chk1"
                                                                            id="pop1_1_chk1_4">
                                                                        <label for="pop1_1_chk1_4">주문관리</label>
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
                                        <a href="javascript:void(0);">서브관리자 등록</a>
                                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 보기 팝업 -->
                    <div class="popup_bx" data-id="pop2_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w800">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">서브관리자 보기</div>
                                    </div>
                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="ttl01">회원정보</div>

                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td colspan="3">I8878</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원아이디</span></th>
                                                            <td colspan="3"><a class="fcol2 link"
                                                                    href="mailto:</a>">test@test.com</a></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원명</span></th>
                                                            <td colspan="3">홍길동</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!--<div class="no_data">일치하는 회원이 없습니다.</div>-->
                                            </div>
                                        </div>

                                        <div class="con_w">
                                            <div class="ttl01">서브관리정보</div>

                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>이용기간</span></th>
                                                            <td colspan="3">2024.10.01 01시 ~ 2024.11.01 23시</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>권한 선택</span></th>
                                                            <td colspan="3">
                                                                Shop 채널<br>상품관리
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 하단버튼 -->
                                    <div class="btm_btn mt10">
                                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 수정 팝업 -->
                    <div class="popup_bx" data-id="pop3_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w800">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">서브관리자 수정</div>
                                    </div>
                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="ttl01">회원찾기</div>
                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="180px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <td class="pr0">
                                                                <select required="required">
                                                                    <option value="" disabled="" selected="">회원번호 선택
                                                                    </option>
                                                                    <option value="1">회원번호1</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <div class="r_btn_w">
                                                                    <input type="text" required="required">
                                                                    <a href="javascript:void(0);" class="btn01">회원찾기</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="con_w">
                                            <div class="ttl01">회원정보</div>

                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td colspan="3">I8878</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원아이디</span></th>
                                                            <td colspan="3"><a class="fcol2 link"
                                                                    href="mailto:</a>">test@test.com</a></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원명</span></th>
                                                            <td colspan="3">홍길동</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!--<div class="no_data">일치하는 회원이 없습니다.</div>-->
                                            </div>
                                        </div>

                                        <div class="con_w">
                                            <div class="ttl01">서브관리정보</div>

                                            <div class="tb01">
                                                <table>
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>이용기간</span></th>
                                                            <td colspan="3">
                                                                <input class="datepicker w160" type="text"
                                                                    required="required" readonly="">
                                                                &nbsp;&nbsp;~&nbsp;&nbsp;
                                                                <input class="datepicker w160" type="text"
                                                                    required="required" readonly="">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>권한 선택</span></th>
                                                            <td colspan="3">
                                                                <ul class="chk02">
                                                                    <li>
                                                                        <input type="checkbox" name="pop3_1_chk1"
                                                                            id="pop3_1_chk1_1" checked="">
                                                                        <label for="pop3_1_chk1_1">Shop 채널</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="checkbox" name="pop3_1_chk1"
                                                                            id="pop3_1_chk1_2">
                                                                        <label for="pop3_1_chk1_2">상품관리</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="checkbox" name="pop3_1_chk1"
                                                                            id="pop3_1_chk1_3">
                                                                        <label for="pop3_1_chk1_3">공동상품관리</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="checkbox" name="pop3_1_chk1"
                                                                            id="pop3_1_chk1_4">
                                                                        <label for="pop3_1_chk1_4">주문관리</label>
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
                                        <a href="javascript:void(0);">서브관리자 수정</a>
                                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        if (popId == "pop1") {
            var thisImg = $(this).children("img").clone();
            $(".popup_bx[data-id='" + popId + "']").find(".img_bx").html(thisImg);
            $(".popup_bx[data-id='" + popId + "']").find(".img_bx").children("img").css({ "max-width": "100%", "width": "auto", "margin": "0 auto", "display": "block" });
        }
        $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
        $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

        return false;
    });
    $(".popup_bx .close_btn").click(function () {
        $(this).parents(".popup_bx").stop().fadeOut(300);

        return false;
    });

    /* 달력 */
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

@endpush
