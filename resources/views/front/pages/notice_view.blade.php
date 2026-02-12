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
                            <div class="view01">
                                <div class="subject">
                                    <div class="on">공지</div>
                                    <strong>공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다.</strong>
                                    <ul>
                                        <li>2024-10-01</li>
                                    </ul>
                                </div>
                                <div class="file">
                                    <span class="l_txt">첨부파일</span>
                                    <ul>
                                        <li><a href="#">첨부파일명.확장자</a></li>
                                        <li><a href="#">첨부파일명.확장자</a></li>
                                        <li><a href="#">첨부파일명.확장자</a></li>
                                    </ul>
                                </div>
                                <div class="con">
                                    <img src="{{ asset('public/images/sub/view_sample01.jpg') }}"><br>
                                    <br>
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 <br>
                                    <br>
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 <br>
                                    <br>
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                </div>
                                <div class="page">
                                    <div class="page_w prev">
                                        <span class="l_txt">이전글</span>
                                        <div class="sb"><a href="#">이전글 제목 추출영역입니다. 이전글 제목 추출영역입니다. 이전글 제목 추출영역입니다. 이전글 제목
                                                추출영역입니다. 이전글 제목 추출영역입니다. 이전글 제목 추출영역입니다. 이전글 제목 추출영역입니다. 이전글 제목 추출영역입니다.</a>
                                        </div>
                                    </div>
                                    <div class="page_w next">
                                        <span class="l_txt">다음글</span>
                                        <div class="sb"><a href="#">다음글 제목 추출영역입니다.</a></div>
                                    </div>
                                </div>
                            </div>

                            <div class="btm_btn">
                                <a href="{{ route('cs.notice') }}">목록</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection