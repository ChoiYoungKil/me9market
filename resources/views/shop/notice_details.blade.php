@extends('layouts.shop')
@section('content')
<div id="contents"><div class="notice"><div class="top_v"><h1 class="title">공지사항</h1></div><div class="shop-inner"><div id="board"><article class="view01 type2">
    <div class="subject"><div class="on">공지</div><strong>{{ $notice->title }}</strong><ul><li>{{ $notice->created_at?->format('Y-m-d') }}</li><li>조회 {{ $notice->view_count }}</li></ul></div>
    @if($notice->attachment)<div class="file"><span class="l_txt">첨부파일</span><ul><li><a href="{{ route('shop.notices.attachment', $notice->id) }}">{{ basename($notice->attachment) }}</a></li></ul></div>@endif
    <div class="con shop-detail-copy">{{ $notice->content }}</div>
    <div class="page">
        @foreach(['prev'=>$previousNotice, 'next'=>$nextNotice] as $direction=>$adjacent)<div class="page_w {{ $direction }}"><span class="l_txt">{{ $direction === 'prev' ? '이전글' : '다음글' }}</span><div class="sb">@if($adjacent)<a href="{{ route('shop.notices.show', $adjacent->id) }}">{{ $adjacent->title }}</a>@else{{ $direction === 'prev' ? '이전글' : '다음글' }}이 없습니다.@endif</div></div>@endforeach
    </div>
</article><div class="shop-actions"><a class="shop-btn" href="{{ route('shop.notices') }}">목록</a></div></div></div></div></div>
@endsection
