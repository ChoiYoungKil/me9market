@extends('layouts.frontend')

@section('page_type', 'sub')

@php
    $dep1_id = "04";
    $dep1_tit = "고객센터";
@endphp

@section('content')
@include('layouts.inc.sub_header', [
    'dep1_id' => $dep1_id,
    'dep2_id' => '02',
    'dep1_tit' => $dep1_tit,
    'dep2_tit' => '자주묻는질문',
    'dep2_sub' => '미구마켓의 다양한 소식을 안내해 드립니다.'
])

<div id="container">
    <div id="contents">
        <div id="faq">
             <div class="box box1">
                <div class="inner_bx">
                    <div id="board">
                        <div class="list_top">
                            <ul class="tab_bx">
                               <li><a href="#" class="on">전체</a></li>
                               <li><a href="#">회원관련</a></li>
                               <li><a href="#">포인트관련</a></li>
                               <li><a href="#">상품관련</a></li>
                            </ul>
                            <div class="search_bx">
                                <form action="#" method="GET">
                                    <input type="text" name="keyword" placeholder="검색어를 입력해주세요">
                                    <button type="submit" class="s_btn">검색</button>
                                </form>
                            </div>
                        </div>

                        <div class="list02">
                            <ul>
                                <li class="con_w">
                                    <div class="q_bx txt_bx">
                                        <div class="type">회원관련</div>
                                        <div class="subject">제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목</div>
                                    </div>
                                    <div class="a_bx txt_bx">
                                        <div class="txt_w">
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출
                                            내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출<br>
                                            <br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출
                                        </div>
                                    </div>
                                </li>
                                <li class="con_w">
                                    <div class="q_bx txt_bx">
                                        <div class="type">포인트관련</div>
                                        <div class="subject">제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목</div>
                                    </div>
                                    <div class="a_bx txt_bx">
                                        <div class="txt_w">
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출
                                            내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출<br>
                                            <br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출
                                        </div>
                                    </div>
                                </li>
                                <li class="con_w">
                                    <div class="q_bx txt_bx">
                                        <div class="type">상품관련</div>
                                        <div class="subject">제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목노출제목</div>
                                    </div>
                                    <div class="a_bx txt_bx">
                                        <div class="txt_w">
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출
                                            내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출<br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출<br>
                                            <br>
                                            내용추출내용추출내용추출내용추출내용추출내용추출내용추출내용추출
                                        </div>
                                    </div>
                                </li>
                            </ul>
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

@push('scripts')
    <script type="text/javascript">
        $('.q_bx').click(function () {
            var $data = $(this).parent();
            if ($data.is('.on')) {
                $data.removeClass('on');
                $data.find('.a_bx').stop().slideUp(300);
            }else {
                $('.con_w').removeClass('on');
                $('.con_w').find('.a_bx').stop().slideUp(300);

                $data.addClass('on');
                $data.find('.a_bx').stop().slideDown(300);
            }
        });
    </script>
@endpush