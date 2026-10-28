@extends('layouts.market-sub')
@section('page_title', '고객센터 · 공지사항 상세')
@section('content')
@include('partials.market-support-heading')
<article class="market-shell market-notice-detail" aria-labelledby="notice-title" id="notice-article">
    <header class="market-notice-detail-head">
        <p class="home-eyebrow">{{ $notice->is_important ? 'IMPORTANT NOTICE' : 'NOTICE' }}</p>
        <h2 id="notice-title">{{ $notice->title }}</h2>
        <p><span>작성일</span> <time datetime="{{ $notice->created_at->toDateString() }}">{{ $notice->created_at->format('Y-m-d') }}</time></p>
    </header>
    @if($notice->attachment)
        <div class="market-notice-attachment"><span>첨부파일</span><a href="{{ url('admin/attachments/notices/'.$notice->attachment) }}" download><span class="attachment-format">{{ strtoupper(pathinfo($notice->attachment, PATHINFO_EXTENSION)) }}</span> 첨부파일 다운로드 <span aria-hidden="true">↓</span></a></div>
    @endif
    <div class="market-notice-body">{!! $notice->content !!}</div>
    @if($prevNotice || $nextNotice)
        <nav class="market-notice-neighbors" aria-label="이전글 다음글">
            @if($prevNotice)<a href="{{ route('cs.notice.view', ['id' => $prevNotice->id]) }}"><span>이전글</span><strong>{{ $prevNotice->title }}</strong><span aria-hidden="true">↑</span></a>@endif
            @if($nextNotice)<a href="{{ route('cs.notice.view', ['id' => $nextNotice->id]) }}"><span>다음글</span><strong>{{ $nextNotice->title }}</strong><span aria-hidden="true">↓</span></a>@endif
        </nav>
    @endif
    <div class="market-notice-back"><a class="market-button market-button-outline" href="{{ route('cs.notice') }}">목록 <span aria-hidden="true">↗</span></a></div>
</article>
@endsection
