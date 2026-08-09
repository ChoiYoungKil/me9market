@extends('layouts.admin')

@section('page_type', 'sub')

@section('content')
    <!-- 오른쪽 박스 -->
    <div id="container_w">
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
                            <div class="view01">
                                <div class="subject">
                                    <div class="on"><span>공지</span></div>
                                    <strong>공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다.</strong>
                                    <ul>
                                        <li><span>Date.</span> 0000-00-00</li>
                                        <li><span>Hit.</span> 000,000,000</li>
                                        <li><span>Writer.</span> 최고관리자 (admin)</li>
                                    </ul>
                                </div>
                                <div class="file">
                                    <span class="l_txt">첨부파일</span>
                                    <ul>
                                        <li><a href="javascript:void(0);">첨부파일명.확장자 <span>(00,000 byte)</span></a></li>
                                        <li><a href="javascript:void(0);">첨부파일명.확장자 <span>(00,000 byte)</span></a></li>
                                    </ul>
                                </div>
                                <div class="con">
                                    <img src="{{ asset('master_assets/images/sub/thum03.jpg') }}"><br>
                                    <br>
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 <br>
                                    <br>
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 <br>
                                    <br>
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                    내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                </div>
                            </div>

                            <div class="btm_btn right mt10">
                                <a href="javascript:void(0);" class="col2 f_l">EXCEL</a>
                                <a href="javascript:void(0);" class="col3">수정</a>
                                <a href="javascript:void(0);" class="col4">삭제</a>
                                <a href="javascript:void(0);" class="col5">목록</a>
                            </div>
                        </div>
                        <div class="con_w">
                            <!-- 댓글 -->
                            <div class="comt01">
                                <div class="count">댓글 <strong>24</strong> 건</div>
                                <div class="f_bx">
                                    <form>
                                        <textarea></textarea>
                                        <a href="javascript:void(0);" class="btn">댓글 입력</a>
                                    </form>
                                </div>
                                <ul class="c_list">
                                    <li class="list_w">
                                        <div class="num">000.</div>
                                        <div class="top_bx">
                                            <div class="name"><strong>아무개</strong> (abc123@gmail.com)</div>
                                            <div class="r_bx">
                                                <span>0000-00-00 00:00:00</span>
                                                <a href="javascript:void(0);" class="btn icon1 pop_btn" data-pop="pop1">수정</a>
                                                <a href="javascript:void(0);" class="btn icon2">삭제</a>
                                            </div>
                                        </div>
                                        <div class="btm_bx">
                                            관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력
                                            관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력
                                        </div>
                                    </li>
                                    <li class="list_w">
                                        <div class="num">000.</div>
                                        <div class="top_bx">
                                            <div class="name"><strong>아무개</strong> (abc123@gmail.com)</div>
                                            <div class="r_bx">
                                                <span>0000-00-00 00:00:00</span>
                                                <a href="javascript:void(0);" class="btn icon1 pop_btn" data-pop="pop1">수정</a>
                                                <a href="javascript:void(0);" class="btn icon2">삭제</a>
                                            </div>
                                        </div>
                                        <div class="btm_bx">
                                            관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력
                                            관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력 관리자 내용 입력
                                        </div>
                                    </li>
                                </ul>

                                <!-- 댓글 수정 팝업 -->
                                <div class="popup_bx" data-id="pop1">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="top_bx">
                                                    <div class="name"><strong>아무개</strong> &nbsp;(abc123@gmail.com)
                                                        &nbsp;<span>0000-00-00 00:00:00</span></div>
                                                </div>
                                                <div class="f_bx">
                                                    <form>
                                                        <textarea></textarea>
                                                        <a href="javascript:void(0);" class="btn">댓글 수정</a>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--<div class="no_data">등록된 댓글이 없습니다.</div>-->
                                <!-- 페이징 -->
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container_w -->
@endsection

@section('scripts')
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
    </script>
@endsection