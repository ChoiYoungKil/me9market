@extends('layouts.master')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">메인 타이틀</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>대분류</li>
                        <li>중분류</li>
                        <li>소분류</li>
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
                                        <th class="w160"><span>상품명</span></th>
                                        <td colspan="3">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>상품분류</span></th>
                                        <td colspan="3">
                                            <ul class="type_bx w600">
                                                <li>
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">대분류</option>
                                                        <option value="1">대분류1</option>
                                                    </select>
                                                </li>
                                                <li>
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">중분류</option>
                                                        <option value="1">중분류1</option>
                                                    </select>
                                                </li>
                                                <li>
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">세분류</option>
                                                        <option value="1">세분류1</option>
                                                    </select>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>상품상태</span></th>
                                        <td>
                                            <ul class="chk02">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_1" checked="">
                                                    <label for="chk1_1">판매</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_2">
                                                    <label for="chk1_2">중지</label>
                                                </li>
                                            </ul>
                                        </td>
                                        <th class="w160"><span>상품범위</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_1" checked="">
                                                    <label for="radio1_1">지사상품</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_2">
                                                    <label for="radio1_2">공개상품</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>날짜표시</span></th>
                                        <td colspan="3">
                                            <div class="date_bx w600">
                                                <input class="datepicker" type="text" required="required" readonly>
                                                <select required="required">
                                                    <option value="" disabled="" selected="">시 선택</option>
                                                    <option value="1">00시</option>
                                                </select>
                                                <span>~</span>
                                                <input class="datepicker" type="text" required="required" readonly>
                                                <select required="required">
                                                    <option value="" disabled="" selected="">시 선택</option>
                                                    <option value="1">00시</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10">
                            <a href="{{ url()->current() }}" class="col2 f_l">EXCEL</a>
                            <a href="{{ url()->current() }}" class="reset1">초기화</a>
                            <a href="{{ url()->current() }}">검색</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="box box2">
                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01 brb mb20">게시글 제목</div>

                        <div class="list_top1">
                            <div class="count">총 <strong>000,000,000</strong> 개</div>
                            <div class="searh_bx">
                                <select required="required">
                                    <option value="0">은행선택</option>
                                    <option value="1">국민은행</option>
                                </select>
                            </div>
                        </div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="50px">
                                    <col width="13%">
                                    <col width="">
                                    <col width="100px">
                                    <col width="">
                                    <col width="">
                                    <col width="">
                                    <col width="">
                                    <col width="190px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"></th>
                                        <th>Name #1</th>
                                        <th>Name #2</th>
                                        <th>Name #3</th>
                                        <th>Name #4</th>
                                        <th>Name #5</th>
                                        <th>Name #6</th>
                                        <th>Name #7</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="textL">
                                    @for($i = 0; $i < 3; $i++)
                                        <tr>
                                            <td class="t_c"><input type="checkbox"></td>
                                            <td class="ovH">테스트 내용입니다.</td>
                                            <td class="ovH">테스트 테스트</td>
                                            <td class="t_c">2025-01-01</td>
                                            <td class="ovH">테스트 내용</td>
                                            <td class="ovH">테스트 내용</td>
                                            <td class="ovH">테스트 내용</td>
                                            <td class="ovH">테스트 내용</td>
                                            <td class="t_c">
                                                <a href="{{ url()->current() }}" class="btn02 col6">보기</a>
                                                <a href="{{ url()->current() }}" class="btn02 col3">수정</a>
                                                <a href="{{ url()->current() }}" class="btn02 col4">삭제</a>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                        <div class="btm_btn right mt10">
                            <!-- 페이징 -->
                            <div class="page_bx1">
                                <a href="{{ url()->current() }}" class="page_first">first</a>
                                <a href="{{ url()->current() }}" class="page_prev">prev</a>
                                <a href="{{ url()->current() }}" class="num on">1</a>
                                <a href="{{ url()->current() }}" class="num">2</a>
                                <a href="{{ url()->current() }}" class="num">3</a>
                                <a href="{{ url()->current() }}" class="num">4</a>
                                <a href="{{ url()->current() }}" class="num">5</a>
                                <a href="{{ url()->current() }}" class="page_next">next</a>
                                <a href="{{ url()->current() }}" class="page_last">last</a>
                            </div>

                            <a href="{{ url()->current() }}" class="col3 f_l type2 mt10">일괄수정</a>
                            <a href="{{ url()->current() }}">등록</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="box box3">
                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01 brb mb20">갤러리 제목</div>

                        <div class="list_top1">
                            <div class="count">총 <strong>000,000,000</strong> 개</div>
                        </div>

                        <div class="list02">
                            <ul>
                                @for($i = 0; $i < 5; $i++)
                                    <li>
                                        <div class="img_bx"
                                            style="background-image:url({{ asset('master_assets/images/sub/thum02.jpg') }})">
                                        </div>
                                        <div class="txt_bx">
                                            <strong>제목제목제목제목...</strong>
                                            <p>2025-01-01</p>
                                        </div>
                                        <div class="btn_bx">
                                            <a href="{{ url()->current() }}" class="btn02 col6">보기</a>
                                            <a href="{{ url()->current() }}" class="btn02 col3">수정</a>
                                            <a href="{{ url()->current() }}" class="btn02 col4">삭제</a>
                                        </div>
                                    </li>
                                @endfor
                            </ul>
                        </div>

                        <div class="btm_btn right mt10">
                            <!-- 페이징 -->
                            <div class="page_bx1">
                                <a href="{{ url()->current() }}" class="page_first">first</a>
                                <a href="{{ url()->current() }}" class="page_prev">prev</a>
                                <a href="{{ url()->current() }}" class="num on">1</a>
                                <a href="{{ url()->current() }}" class="num">2</a>
                                <a href="{{ url()->current() }}" class="num">3</a>
                                <a href="{{ url()->current() }}" class="num">4</a>
                                <a href="{{ url()->current() }}" class="num">5</a>
                                <a href="{{ url()->current() }}" class="page_next">next</a>
                                <a href="{{ url()->current() }}" class="page_last">last</a>
                            </div>

                            <a href="{{ url()->current() }}">등록</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript">
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
@endsection