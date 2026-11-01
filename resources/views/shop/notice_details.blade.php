@extends('layouts.shop')
@section('content')
<div id="contents"><div class="notice"><div class="top_v"><h1 class="title">공지사항</h1></div><div class="shop-inner"><div id="board"><article class="view01 type2">
    <div class="subject">@if($notice->type==='notice')<div class="on">공지</div>@endif<strong>{{ $notice->title }}</strong><ul><li>작성자 {{ $shop->channel_name }}</li><li>작성일 {{ $notice->created_at?->format('Y-m-d') }}</li><li>조회 {{ $notice->view_count }}</li></ul></div>
    @if($notice->attachment)<div class="file"><span class="l_txt">첨부파일</span><ul><li><a href="{{ route('shop.notices.attachment', $notice->id) }}">{{ $notice->attachment_name?:basename($notice->attachment) }}</a></li></ul></div>@endif
    <div class="con shop-detail-copy">{!! app(\App\Services\ShopCommunity::class)->clean($notice->content) !!}</div>
    <div class="page">
        @foreach(['prev'=>$previousNotice, 'next'=>$nextNotice] as $direction=>$adjacent)<div class="page_w {{ $direction }}"><span class="l_txt">{{ $direction === 'prev' ? '이전글' : '다음글' }}</span><div class="sb">@if($adjacent)<a href="{{ route('shop.notices.show',array_merge(['id'=>$adjacent->id],$listQuery)) }}">{{ $adjacent->title }}</a>@else{{ $direction === 'prev' ? '이전글' : '다음글' }}이 없습니다.@endif</div></div>@endforeach
    </div>
</article><div class="shop-actions"><a class="shop-btn" href="{{ route('shop.notices',$listQuery) }}">목록</a></div></div></div></div></div>
@endsection
