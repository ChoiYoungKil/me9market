@extends('layouts.master')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">대제목 #1</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>대분류</li>
                        <li>중분류</li>
                        <li>소분류</li>
                    </ul>
                </div>

                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01 brb">컨텐츠</div>
                        <div class="row_bx">
                            <div class="row" style="background:#ddd;">1</div>

                            <div class="row col2" style="background:#ddd;">2</div>
                            <div class="row col2 mr0" style="background:#ddd;">2</div>

                            <div class="row col3" style="background:#ddd;">3</div>
                            <div class="row col3" style="background:#ddd;">3</div>
                            <div class="row col3 mr0" style="background:#ddd;">3</div>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="ttl01">표</div>
                        <div class="row_bx">
                            @for ($i = 0; $i < 4; $i++)
                                <div
                                    class="row {{ $i > 0 && $i < 3 ? 'col2' : ($i >= 3 ? 'col3' : '') }} {{ $i == 2 ? 'mr0' : '' }}">
                                    <div class="tb01">
                                        <table>
                                            <colgroup>
                                                <col width="29%">
                                                <col width="">
                                                <col width="29%">
                                            </colgroup>
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>title1</th>
                                                    <th>title2</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>0000-00-00</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endfor
                            <div class="row col3">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="29%">
                                            <col width="">
                                            <col width="29%">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>title1</th>
                                                <th>title2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>0000-00-00</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row col3 mr0">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="29%">
                                            <col width="">
                                            <col width="29%">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>title1</th>
                                                <th>title2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>0000-00-00</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="box box2">
                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01">중제목 #1</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th>제목</th>
                                        <td>입력된 내용표시</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <div class="list01">
                            <ul>
                                <li>
                                    <a href="{{ url()->current() }}">
                                        <div class="img_bx"
                                            style="background-image:url({{ asset('master_assets/images/sub/thum01.jpg') }})">
                                        </div>
                                        <div class="txt_bx">
                                            <p>대분류 > 중분류 > 소분류</p>
                                            <strong>상품명 111111</strong>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                            <div class="no_data">등록된 데이터가 없습니다.</div>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="ttl01">중제목 #2</div>

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
                                        <th class="w160"><span>Input 텍스트</span></th>
                                        <td colspan="3">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>라디오버튼</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_1" checked="">
                                                    <label for="radio1_1">라디오 값1</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_2">
                                                    <label for="radio1_2">라디오 값2</label>
                                                </li>
                                            </ul>
                                        </td>
                                        <th class="w160"><span>체크박스</span></th>
                                        <td>
                                            <ul class="chk02">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_1" checked="">
                                                    <label for="chk1_1">체크 값1</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_2">
                                                    <label for="chk1_2">체크 값2</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>Textarea</span></th>
                                        <td colspan="3">
                                            <textarea value="" required="required"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>파일</span></th>
                                        <td colspan="3">
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly">
                                                <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn" class="uploadBtn" name="bbs_file1">
                                                <div class="del_btn">삭제</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="ttl01">중제목 #3</div>

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
                                        <th class="w160"><span>중복확인</span></th>
                                        <td colspan="3">
                                            <div class="r_btn_w w457">
                                                <input type="text">
                                                <a href="{{ url()->current() }}" class="btn01">중복확인</a>
                                            </div>
                                            <p class="mt10">‘ <span class="fcol2">abcd1234</span> ’ 는 사용할 수 있는 아이디 입니다.</p>
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
                                        <th class="w160"><span>주소</span></th>
                                        <td colspan="3">
                                            <div class="addr_bx">
                                                <input type="text" class="addr1 off" placeholder="우편번호" required="required">
                                                <a href="{{ url()->current() }}" class="btn01">우편번호찾기</a>
                                                <input type="text" class="addr2 off" placeholder="주소" required="required">
                                                <input type="text" class="addr3 off" placeholder="상세주소" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>날짜표시</span></th>
                                        <td colspan="3">
                                            <input class="datepicker w160" type="text" required="required" readonly>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <a href="{{ url()->current() }}" class="btn01">버튼1</a>
                        <a href="{{ url()->current() }}" class="btn01 col2">버튼2</a>
                        <a href="{{ url()->current() }}" class="btn01 col3">버튼3</a>
                    </div>
                    <div class="con_w">
                        <div class="ttl01 brb">새창/팝업</div>

                        <!-- 새창 -->
                        <div class="btm_btn left mt0">
                            <a href="javascript:void(0);"
                                onclick="window.open('#', '_blank', 'width=1220, height=700'); return false;">새창1</a>
                        </div>

                        <!-- 팝업버튼 -->
                        <div class="btm_btn left">
                            <a href="javascript:void(0);" class="pop_btn" data-pop="pop1">팝업버튼1</a>
                            <a href="javascript:void(0);" class="col2 pop_btn" data-pop="pop2">팝업버튼2</a>
                        </div>

                        <!-- 팝업 -->
                        <div class="popup_bx" data-id="pop1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <ul class="dep">
                                                <li>HOME</li>
                                                <li>대분류</li>
                                                <li>중분류</li>
                                                <li>소분류</li>
                                            </ul>
                                            <div class="ttl">대제목 #1</div>
                                        </div>

                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="ttl01">중제목 #1</div>

                                                <div class="tb01">
                                                    <table>
                                                        <colgroup>
                                                            <col width="160px">
                                                            <col width="">
                                                        </colgroup>
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th>제목</th>
                                                                <td>입력된 내용표시</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="{{ url()->current() }}">액션버튼</a>
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="popup_bx" data-id="pop2">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <ul class="dep">
                                                <li>HOME</li>
                                            </ul>
                                            <div class="ttl">상품제약조건</div>
                                        </div>

                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="ttl01">상품 제약 조건</div>
                                                <div class="tb01">
                                                    <table>
                                                        <colgroup>
                                                            <col width="160px">
                                                            <col width="">
                                                        </colgroup>
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th>가격제약조건</th>
                                                                <td>1,500 원 ~ 5,000 원</td>
                                                            </tr>
                                                            <tr>
                                                                <th>재고</th>
                                                                <td>20,000 개</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="{{ url()->current() }}">상품추가하기</a>
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
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

    @push('scripts')
        <script type="text/javascript">
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
    @endpush
@endsection