@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div style="background:#f6f7f9; min-height:100vh; padding:28px 20px 50px;">
    <main style="max-width:980px; margin:0 auto;">
        <a href="{{ route('shop.notices') }}" style="display:inline-block; margin-bottom:14px; color:#344054; font-weight:800; text-decoration:none;">공지 목록</a>
        <article style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:28px;">
            <div style="color:#667085; font-size:13px; font-weight:800;">{{ $shop->channel_name }} 공지사항</div>
            <h1 style="margin:8px 0 10px; font-size:26px;">{{ $notice->title }}</h1>
            <div style="color:#667085; font-size:13px; padding-bottom:18px; border-bottom:1px solid #eef1f5;">{{ $notice->created_at?->format('Y-m-d H:i') }} · 조회 {{ $notice->view_count }}</div>
            <div style="white-space:pre-wrap; line-height:1.75; color:#344054; padding-top:22px;">{{ $notice->content }}</div>
            @if($notice->attachment)
                <div style="margin-top:22px; padding-top:16px; border-top:1px solid #eef1f5; color:#475467;">첨부파일: {{ basename($notice->attachment) }}</div>
            @endif
        </article>
    </main>
</div>
@endsection
