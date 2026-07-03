@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div style="background: #f6f7f9; min-height: 100vh; padding-bottom: 50px;">
    <div style="background: #111827; color: #fff; padding: 22px 32px;">
        <div style="max-width: 1180px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin: 0; font-size: 24px;">{{ $shop->channel_name }} 공지사항</h1>
            <a href="{{ route('shop.channel_main') }}" style="color: #fff; text-decoration: none; font-weight: 800;">채널 홈</a>
        </div>
    </div>

    <main style="max-width: 980px; margin: 28px auto; padding: 0 20px;">
        <div style="background:#fff; border:1px solid #d9dee7; border-radius:8px; overflow:hidden;">
            @forelse($notices as $notice)
                <article style="padding:18px; border-bottom:1px solid #eef1f5;">
                    <h2 style="margin:0 0 6px; font-size:18px;">{{ $notice->title }}</h2>
                    <div style="color:#667085; font-size:13px;">{{ $notice->created_at?->format('Y-m-d') }} · 조회 {{ $notice->view_count }}</div>
                    <p style="color:#475467;">{{ $notice->content }}</p>
                </article>
            @empty
                <div style="padding:18px;">등록된 공지사항이 없습니다.</div>
            @endforelse
        </div>
    </main>
</div>
@endsection
