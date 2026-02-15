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
                               <li><a href="{{ url('faq') }}" class="{{ !request('category') || request('category') == '전체' ? 'on' : '' }}">전체</a></li>
                               @foreach(['주문/배송', '결제', '회원', '상품', '기타'] as $cat)
                                   <li><a href="{{ url('faq') }}?category={{ $cat }}" class="{{ request('category') == $cat ? 'on' : '' }}">{{ $cat }}</a></li>
                               @endforeach
                            </ul>
                            <div class="search_bx">
                                <form action="{{ url('faq') }}" method="GET">
                                    @if(request('category'))
                                        <input type="hidden" name="category" value="{{ request('category') }}">
                                    @endif
                                    <!-- 프론트엔드에서는 기본적으로 질문+답변 검색 -->
                                    <input type="hidden" name="search_type" value="both">
                                    <input type="text" name="search_value" value="{{ request('search_value') }}" placeholder="검색어를 입력해주세요">
                                    <button type="submit" class="s_btn">검색</button>
                                </form>
                            </div>
                        </div>

                        <div class="list02">
                            <ul>
                                @if($faqs->count() > 0)
                                    @foreach($faqs as $faq)
                                        <li class="con_w">
                                            <div class="q_bx txt_bx">
                                                <div class="type">{{ $faq->category }}</div>
                                                <div class="subject">{{ $faq->question }}</div>
                                            </div>
                                            <div class="a_bx txt_bx" style="display: none;">
                                                <div class="txt_w">
                                                    {!! $faq->answer !!}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <li style="text-align: center; padding: 50px; border-bottom: 1px solid #eee;">검색 결과가 없습니다.</li>
                                @endif
                            </ul>
                        </div>

                        {{-- 페이지네이션 --}}
                        @if($faqs->hasPages())
                            <div class="page_bx">
                                {{-- 첫 페이지 --}}
                                @if($faqs->onFirstPage())
                                    <a href="#" class="page_first disabled" style="pointer-events: none; opacity: 0.5;">first</a>
                                @else
                                    <a href="{{ $faqs->appends(request()->query())->url(1) }}" class="page_first">first</a>
                                @endif

                                {{-- 이전 페이지 --}}
                                @if($faqs->onFirstPage())
                                    <a href="#" class="page_prev disabled" style="pointer-events: none; opacity: 0.5;">prev</a>
                                @else
                                    <a href="{{ $faqs->appends(request()->query())->previousPageUrl() }}" class="page_prev">prev</a>
                                @endif

                                {{-- 페이지 번호 --}}
                                @php
                                    $start = max($faqs->currentPage() - 2, 1);
                                    $end = min($start + 4, $faqs->lastPage());
                                    $start = max($end - 4, 1);
                                @endphp
                                @for($i = $start; $i <= $end; $i++)
                                    @if($i == $faqs->currentPage())
                                        <a href="#" class="num on">{{ $i }}</a>
                                    @else
                                        <a href="{{ $faqs->appends(request()->query())->url($i) }}" class="num">{{ $i }}</a>
                                    @endif
                                @endfor

                                {{-- 다음 페이지 --}}
                                @if($faqs->hasMorePages())
                                    <a href="{{ $faqs->appends(request()->query())->nextPageUrl() }}" class="page_next">next</a>
                                @else
                                    <a href="#" class="page_next disabled" style="pointer-events: none; opacity: 0.5;">next</a>
                                @endif

                                {{-- 마지막 페이지 --}}
                                @if($faqs->currentPage() == $faqs->lastPage())
                                    <a href="#" class="page_last disabled" style="pointer-events: none; opacity: 0.5;">last</a>
                                @else
                                    <a href="{{ $faqs->appends(request()->query())->url($faqs->lastPage()) }}" class="page_last">last</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->
</div><!-- //container -->

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
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
        });
    </script>
@endpush
@endsection