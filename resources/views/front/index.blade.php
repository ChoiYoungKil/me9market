@extends('layouts.frontend')

@section('page_type', 'main')

@section('content')
    <main id="container" class="market-home">
        <section class="market-entry">
            <img src="{{ asset('me9market/images/common/logo.png') }}" alt="Me9 market" width="189" height="30">
            <h1>Me9 market</h1>
            <form action="{{ route('shop.gate') }}" method="GET">
                <label for="home-channel-code">Shop 입장코드</label>
                <div class="entry-fields"><input id="home-channel-code" name="channel" maxlength="80" required autocomplete="off"><button type="submit">Shop 입장</button></div>
            </form>
            <nav aria-label="서비스 바로가기">
                <a href="{{ route('front.shop.order.confirm') }}">주문조회</a>
                <a href="{{ route('channel.login') }}">채널관리자</a>
                <a href="{{ route('shop.monitor.login') }}">Shop 모니터링</a>
            </nav>
        </section>
        <section class="market-notices">
            <div class="section-title"><h2>공지사항</h2><a href="{{ route('cs.notice') }}">전체보기</a></div>
            <ul>@forelse($notices as $notice)<li><a href="{{ route('cs.notice.view', $notice->id) }}">{{ $notice->title }}</a><time>{{ $notice->created_at?->format('Y-m-d') }}</time></li>@empty<li>등록된 공지사항이 없습니다.</li>@endforelse</ul>
        </section>
    </main>
    <style>
    .market-home{max-width:960px;margin:auto;padding:70px 24px 56px;min-height:60vh;letter-spacing:0}.market-home .market-entry{padding:32px 0 48px;border-bottom:1px solid #dce3e7}.market-entry>img{object-fit:contain;object-position:left}.market-entry h1{font-size:36px;line-height:1.25;margin:24px 0;color:#222}.market-entry form{max-width:520px}.market-entry label{display:block;font-size:16px;margin-bottom:10px}.entry-fields{display:flex;gap:10px}.entry-fields input{min-width:0;flex:1;height:48px;border:1px solid #929ba3;padding:0 12px;font-size:18px;border-radius:4px}.entry-fields button{border:0;background:#087f76;color:#fff;padding:0 20px;border-radius:4px;white-space:nowrap;font-size:16px}.market-entry nav{display:flex;gap:24px;flex-wrap:wrap;margin-top:24px}.market-entry nav a{font-size:15px;text-decoration:underline;text-underline-offset:4px}.market-notices{padding-top:32px}.market-notices .section-title{display:flex;align-items:center;justify-content:space-between;gap:16px}.market-notices h2{font-size:22px}.market-notices li{display:flex;gap:20px;justify-content:space-between;padding:18px 0;border-bottom:1px solid #e7ebee;font-size:15px}.market-notices li a{overflow-wrap:anywhere}.market-notices time{white-space:nowrap;color:#666}@media(max-width:600px){.market-home{padding:35px 20px}.market-entry h1{font-size:30px}.market-entry nav{gap:16px}.entry-fields button{padding:0 12px}.market-notices li{flex-direction:column;gap:6px}}
    </style>
@endsection
