<!DOCTYPE html>
<html lang="ko">

<head>
    <title>Me9 market</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="{{ asset('master/css/base.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/css/common.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/css/main.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/css/sub.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/css/board.css') }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{ asset('master/images/icon/favicon.ico') }}" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="{{ asset('master/images/common/url_img_logo.jpg') }}">

    <script src="{{ asset('master/js/jquery-3.7.0.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="{{ asset('master/js/slick.min.js') }}"></script>
    <script src="{{ asset('master/js/common.js') }}"></script>
</head>

<body class="type2">
    <div id="skipNavi">
        <ul>
            <li>
                <a href="#container">본문 바로가기</a>
                <a href="#gnb">주메뉴 바로가기</a>
            </li>
        </ul>
    </div>

    <div id="wrap">
        <div id="container">
            <div id="container_w">
                <div id="contents">
                    <div class="row">
                        <div class="box">
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
                                    <br>
                                    <div class="list01">
                                        <ul>
                                            <li>
                                                <a href="javascript:void(0);">
                                                    <div class="img_bx"
                                                        style="background-image:url({{ asset('master/images/sub/thum01.jpg') }})">
                                                    </div>
                                                    <div class="txt_bx">
                                                        <p>대분류 > 중분류 > 소분류</p>
                                                        <strong>상품명 111111</strong>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
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
                                                                <input type="radio" name="radio1" id="radio1_1"
                                                                    checked="">
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
                                                                <input type="checkbox" name="chk1" id="chk1_1"
                                                                    checked="">
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
                                                            <label for="uploadBtn3" class="btn_file">찾아보기</label>
                                                            <input type="file" id="uploadBtn3" class="uploadBtn"
                                                                name="bbs_file1">
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
                                                            <a href="javascript:void(0);" class="btn01">중복확인</a>
                                                        </div>
                                                        <p class="mt10">‘ <span class="fcol2">abcd1234</span> ’ 는 사용할 수
                                                            있는 아이디 입니다.</p>
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
                                                            <input type="text" class="email1" required="required"
                                                                value="">
                                                            <span>@</span>
                                                            <input type="text" class="email2" required="required"
                                                                value="">
                                                            <select class="off" required="required">
                                                                <option value="" selected="">직접입력</option>
                                                                <option value="1">naver.com</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="w160"><span>주소</span></th>
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
                                                    <th class="w160"><span>파일표시 및 삭제</span></th>
                                                    <td colspan="3">
                                                        <div class="f_down2">
                                                            <div class="f_name">
                                                                <p>File_name.txt ( 4,33 kb )</p>
                                                            </div>
                                                            <a href="javascript:void(0);" class="down_btn">내려받기</a>
                                                            <a class="del_btn">파일삭제</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="w160"><span>날짜표시</span></th>
                                                    <td colspan="3">
                                                        <input class="datepicker w160" type="text" required="required"
                                                            readonly>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 하단버튼 -->
                                <div class="btm_btn mt10">
                                    <a href="javascript:void(0);">액션버튼</a>
                                    <a href="javascript:void(0);" class="col5">닫기</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- //wrap -->
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
    </script>
</body>

</html>