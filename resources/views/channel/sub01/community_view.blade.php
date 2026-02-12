@extends('layouts.channel')

@section('content')
    @php
        $page_type = "sub";
        $dep1_id = "01";
        $dep1_tit = "Shop채널관리";
    @endphp
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">Shop채널 상세페이지</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>Shop채널관리</li>
                        <li>Shop채널 상세페이지</li>
                    </ul>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.shop_info') }}"><span>Shop채널 정보</span></a></li>
                        <li><a href="{{ route('channel.product_own') }}"><span>판매상품</span></a></li>
                        <li><a href="{{ route('channel.community') }}" class="on"><span>커뮤니티</span></a></li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="view01">
                            <div class="subject">
                                <div class="on"><span>공지</span></div>
                                <strong>공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다.</strong>
                                <ul>
                                    <li><span>작성일</span> 0000-00-00</li>
                                </ul>
                            </div>
                            <div class="file">
                                <span class="l_txt">첨부파일</span>
                                <ul>
                                    <li><a href="#">첨부파일명.확장자 <span>(00,000 byte)</span></a></li>
                                    <li><a href="#">첨부파일명.확장자 <span>(00,000 byte)</span></a></li>
                                </ul>
                            </div>
                            <div class="con">
                                <img src="/images/channel/sub/thum03.jpg"><br>
                                <br>
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 <br>
                                <br>
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 <br>
                                <br>
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                                내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출 내용노출
                            </div>
                            <div class="page">
                                <div class="prev">
                                    <div class="l_txt">이전글</div>
                                    <div class="sb"><a href="#">이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글
                                            제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글 제목입니다. 이전글
                                            제목입니다. 이전글 제목입니다.</a></div>
                                </div>
                                <div class="next">
                                    <div class="l_txt">다음글</div>
                                    <div class="sb"><a href="#">다음글 제목입니다.</a></div>
                                </div>
                            </div>
                        </div>

                        <div class="btm_btn mt10">
                            <a href="{{ route('channel.community.update') }}" class="col3">수정</a>
                            <a href="#" class="col4">삭제</a>
                            <a href="{{ route('channel.community') }}" class="col5">목록</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection