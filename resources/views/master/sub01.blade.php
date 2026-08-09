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
                        <div class="ttl01">중제목 #1</div>
                        <div class="row_bx">
                            <div class="row col2">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="27%">
                                            <col width="27%">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th rowspan="3">주문자 정보</th>
                                                <td>주문자 이름</td>
                                                <td>홍길동</td>
                                            </tr>
                                            <tr>
                                                <td>휴대폰 번호</td>
                                                <td>010-0000-0000</td>
                                            </tr>
                                            <tr>
                                                <td>이메일 주소</td>
                                                <td><a class="fcol2" href="#">text1234@naver.com</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row col2 mr0">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="27%">
                                            <col width="27%">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th rowspan="3">주문자 정보</th>
                                                <td>주문자 이름</td>
                                                <td>홍길동</td>
                                            </tr>
                                            <tr>
                                                <td>휴대폰 번호</td>
                                                <td>010-0000-0000</td>
                                            </tr>
                                            <tr>
                                                <td>이메일 주소</td>
                                                <td><a class="fcol2" href="#">text1234@naver.com</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="ttl01">중제목 #1</div>
                        <div class="row_bx">
                            <div class="row">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="15%">
                                            <col width="30%">
                                            <col width="10%">
                                            <col width="">
                                            <col width="">
                                            <col width="">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>제목 #1</th>
                                                <th>제목 #2</th>
                                                <th>제목 #3</th>
                                                <th>제목 #4</th>
                                                <th>제목 #5</th>
                                                <th>제목 #6</th>
                                            </tr>
                                        </thead>
                                        <tbody class="textL">
                                            <tr>
                                                <td>받는사람</td>
                                                <td>홍길동</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>휴대폰 번호</td>
                                                <td>010-0000-0000</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>주소</td>
                                                <td>22012 서울특별시 광진구 가나다동 119-12</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
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
                        <div class="row_bx">
                            <div class="row">
                                <div class="tab_bx1">
                                    <ul>
                                        <li><a href="#" class="on"><span>탭메뉴 #1</span></a></li>
                                        <li><a href="#"><span>탭메뉴 #2</span></a></li>
                                        <li><a href="#"><span>탭메뉴 #3</span></a></li>
                                        <li><a href="#"><span>탭메뉴 #4</span></a></li>
                                    </ul>
                                </div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="80px">
                                            <col width="">
                                            <col width="100px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>번호</th>
                                                <th>제목</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="textL">
                                            <tr>
                                                <td class="t_c">00</td>
                                                <td class="ovH">
                                                    <a href="#" class="subject on fcol1">공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항
                                                        입니다. 공지사항 입니다. 공지사항 입니다.</a>
                                                </td>
                                                <td class="t_c">2025-10-01</td>
                                            </tr>
                                            <tr>
                                                <td class="t_c">00</td>
                                                <td class="ovH">
                                                    <a href="#" class="subject fcol1">일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판
                                                        제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반
                                                        게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판
                                                        제목입니다.</a>
                                                </td>
                                                <td class="t_c">2025-10-01</td>
                                            </tr>
                                            <tr>
                                                <td class="t_c">00</td>
                                                <td class="ovH">
                                                    <a href="#" class="subject fcol1">일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판
                                                        제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다.</a>
                                                </td>
                                                <td class="t_c">2025-10-01</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                                <!-- 페이징 -->
                                <div class="page_bx1">
                                    <a href="#" class="page_first">first</a>
                                    <a href="#" class="page_prev">prev</a>
                                    <a href="#" class="num on">1</a>
                                    <a href="#" class="num">2</a>
                                    <a href="#" class="num">3</a>
                                    <a href="#" class="num">4</a>
                                    <a href="#" class="num">5</a>
                                    <a href="#" class="page_next">next</a>
                                    <a href="#" class="page_last">last</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection