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
                                    @if($notice->is_important)
                                        <div class="on">공지</div>
                                    @endif
                                    <strong>{{ $notice->title }}</strong>
                                    <ul>
                                        <li>{{ $notice->created_at->format('Y-m-d') }}</li>
                                    </ul>
                                </div>

                                @if($notice->attachment)
                                    <div class="file">
                                        <span class="l_txt">첨부파일</span>
                                        <ul>
                                            <li>
                                                <a href="{{ url('admin/attachments/notices/' . $notice->attachment) }}"
                                                    download>
                                                    {{ $notice->attachment }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @endif

                                <div class="con">
                                    {!! $notice->content !!}
                                </div>

                                <div class="page">
                                    @if($prevNotice)
                                        <div class="page_w prev">
                                            <span class="l_txt">이전글</span>
                                            <div class="sb">
                                                <a href="{{ route('cs.notice.view', ['id' => $prevNotice->id]) }}">
                                                    {{ $prevNotice->title }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if($nextNotice)
                                        <div class="page_w next">
                                            <span class="l_txt">다음글</span>
                                            <div class="sb">
                                                <a href="{{ route('cs.notice.view', ['id' => $nextNotice->id]) }}">
                                                    {{ $nextNotice->title }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
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