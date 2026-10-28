@extends('layouts.market-sub')
@section('page_title', '고객센터 · 공지사항')
@section('content')
@include('partials.market-support-heading')
<section class="market-shell market-support-content" aria-label="공지사항 목록">
    <div class="market-list-tools"><p>총 <strong>{{ $notices->total() }}</strong>건의 소식</p><form method="GET" action="{{ route('cs.notice') }}" class="market-search"><label class="sr-only" for="notice-search">공지사항 검색</label><input id="notice-search" type="search" name="search" placeholder="어떤 소식을 찾으세요?" value="{{ request('search') }}"><button type="submit" aria-label="공지사항 검색">검색 <span aria-hidden="true">↗</span></button></form></div>
    <div class="market-notice-table-head" aria-hidden="true"><span>번호</span><span>제목</span><span>작성일</span><span></span></div>
    <ul class="market-notice-list">
        @forelse($notices as $index => $notice)
            <li><a href="{{ route('cs.notice.view', ['id' => $notice->id]) }}"><span class="market-notice-index">{{ $notice->is_important ? '공지' : str_pad($notices->total() - ($notices->currentPage() - 1) * $notices->perPage() - $index, 2, '0', STR_PAD_LEFT) }}</span><span class="market-notice-subject">{{ $notice->title }}</span><time datetime="{{ $notice->created_at->toDateString() }}">{{ $notice->created_at->format('Y-m-d') }}</time><span aria-hidden="true">↗</span></a></li>
        @empty
            <li class="market-empty"><span aria-hidden="true">↗</span><h2>등록된 데이터가 없습니다.</h2><p>새로운 소식이 등록되면 이곳에서 안내해 드립니다.</p></li>
        @endforelse
    </ul>
    @include('partials.market-pagination', ['paginator' => $notices])
</section>
@endsection
