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
                                <div class="count">총 <strong>{{ $notices->total() }}</strong> 건</div>
                                <div class="search_bx">
                                    <form method="GET" action="{{ route('cs.notice') }}">
                                        <input type="text" name="search" placeholder="검색어를 입력해주세요"
                                            value="{{ request('search') }}">
                                        <button type="submit" class="s_btn">검색</button>
                                    </form>
                                </div>
                            </div>

                            <div class="list01">
                                @if($notices->count() > 0)
                                    <ul>
                                        @foreach($notices as $index => $notice)
                                            <li @if($notice->is_important) class="on" @endif>
                                                <a href="{{ route('cs.notice.view', ['id' => $notice->id]) }}">
                                                    <div class="num">
                                                        @if($notice->is_important)
                                                            공지
                                                        @else
                                                            {{ $notices->total() - ($notices->currentPage() - 1) * $notices->perPage() - $index }}
                                                        @endif
                                                    </div>
                                                    <div class="subject">
                                                        {{ $notice->title }}
                                                        @if($notice->created_at->diffInHours(now()) < 24)
                                                            <span class="new_btn"
                                                                style="background-color: #ef4131; color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px; vertical-align: middle; font-weight: bold;">N</span>
                                                        @endif
                                                    </div>
                                                    <div class="date">{{ $notice->created_at->format('Y-m-d') }}</div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="no_data">등록된 데이터가 없습니다.</div>
                                @endif
                            </div>

                            @if($notices->hasPages())
                                <div class="page_bx">
                                    {{-- 첫 페이지 --}}
                                    @if($notices->onFirstPage())
                                        <a href="{{ url()->current() }}" class="page_first disabled"
                                            style="pointer-events: none; opacity: 0.5;">first</a>
                                    @else
                                        <a href="{{ $notices->url(1) }}" class="page_first">first</a>
                                    @endif

                                    {{-- 이전 페이지 --}}
                                    @if($notices->onFirstPage())
                                        <a href="{{ url()->current() }}" class="page_prev disabled" style="pointer-events: none; opacity: 0.5;">prev</a>
                                    @else
                                        <a href="{{ $notices->previousPageUrl() }}" class="page_prev">prev</a>
                                    @endif

                                    {{-- 페이지 번호 --}}
                                    @php
                                        $start = max($notices->currentPage() - 2, 1);
                                        $end = min($start + 4, $notices->lastPage());
                                        $start = max($end - 4, 1);
                                    @endphp
                                    @for($i = $start; $i <= $end; $i++)
                                        @if($i == $notices->currentPage())
                                            <a href="{{ url()->current() }}" class="num on">{{ $i }}</a>
                                        @else
                                            <a href="{{ $notices->url($i) }}" class="num">{{ $i }}</a>
                                        @endif
                                    @endfor

                                    {{-- 다음 페이지 --}}
                                    @if($notices->hasMorePages())
                                        <a href="{{ $notices->nextPageUrl() }}" class="page_next">next</a>
                                    @else
                                        <a href="{{ url()->current() }}" class="page_next disabled" style="pointer-events: none; opacity: 0.5;">next</a>
                                    @endif

                                    {{-- 마지막 페이지 --}}
                                    @if($notices->currentPage() == $notices->lastPage())
                                        <a href="{{ url()->current() }}" class="page_last disabled" style="pointer-events: none; opacity: 0.5;">last</a>
                                    @else
                                        <a href="{{ $notices->url($notices->lastPage()) }}" class="page_last">last</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection