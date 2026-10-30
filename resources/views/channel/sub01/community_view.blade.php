@extends('layouts.channel')
@section('page_type','sub')
@php $dep1_id='01'; @endphp
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}?v={{ filemtime(public_path('channel_assets/css/shop-configuration.css')) }}">@endpush
@section('content')
<div id="contents"><div class="channel-shop"><header><p>MY CHANNEL</p><h1>커뮤니티 상세</h1><p>{{ $shop->channel_name }}</p></header>@include('channel.sub01.inc.community_tabs')
@if(session('success_message'))<p class="shop-feedback" role="status">{{ session('success_message') }}</p>@endif
<section class="shop-panel"><h2>{{ $notice->title }}</h2><p class="shop-help">{{ $notice->type==='notice'?'공지':'일반' }} · {{ $notice->author }} · {{ $notice->created_at->format('Y-m-d H:i') }} · 조회 {{ $notice->view_count }}</p>
@if($notice->attachment)<p class="community-attachment">첨부파일 <a href="{{ route('channel.community.attachment',$notice->id) }}">{{ $notice->attachment_name?:'첨부파일 다운로드' }}</a></p>@endif
<div class="community-body">{!! app(\App\Services\ShopCommunity::class)->clean($notice->content) !!}</div>
<div class="shop-actions"><a class="secondary" href="{{ route('channel.shop_community',['shop_id'=>$shopId]) }}">목록</a><a href="{{ route('channel.community.update',$notice->id) }}">수정</a><form action="{{ route('channel.community.delete',$notice->id) }}" method="POST" data-delete-notice>@csrf<button type="submit" class="danger">삭제</button></form></div>
<dl class="shop-details">@if($nextNotice)<div><dt>다음글</dt><dd><a href="{{ route('channel.community.view',$nextNotice->id) }}">{{ $nextNotice->title }}</a></dd></div>@endif @if($prevNotice)<div><dt>이전글</dt><dd><a href="{{ route('channel.community.view',$prevNotice->id) }}">{{ $prevNotice->title }}</a></dd></div>@endif</dl></section></div></div>
@endsection
@push('scripts')<script src="{{ asset('channel_assets/js/shop-community.js') }}?v={{ filemtime(public_path('channel_assets/js/shop-community.js')) }}" defer></script>@endpush
