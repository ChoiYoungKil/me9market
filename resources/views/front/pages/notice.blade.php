@extends('layouts.frontend')

@section('page_type', 'sub')

@php
    $dep1_id = "04";
    $dep2_id = "01";
    $dep1_tit = "고객센터";
    $dep2_tit = "공지사항";
    $dep2_sub = "미구마켓의 다양한 소식을 안내해 드립니다.";
@endphp

@section('content')
    @include('layouts.inc.sub_header')
    <div id="container">
        <div id="contents">
            <div id="notice">
                <div class="box box1">
                    <div class="inner_bx">
                        <div id="board">
                            <div class="list_top">
                                <div class="count">총 <strong>24</strong> 건</div>
                                <div class="search_bx">
                                    <form>
                                        <input type="text" placeholder="검색어를 입력해주세요">
                                        <a href="#" class="s_btn">검색</a>
                                    </form>
                                </div>
                            </div>

                            <div class="list01">
                                <ul>
                                    <li class="on"><!-- 공지 -->
                                        <a href="{{ route('cs.notice.view', ['id' => 1]) }}">
                                            <div class="num">0000</div>
                                            <div class="subject">공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다.
                                            </div>
                                            <div class="date">2024-10-01</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('cs.notice.view', ['id' => 2]) }}">
                                            <div class="num">0000</div>
                                            <div class="subject">일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. </div>
                                            <div class="date">2024-10-01</div>
                                        </a>
                                    </li>
                                </ul>

                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                            </div>

                            <div class="page_bx">
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
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection