@extends('layouts.frontend')

@section('content')
<div id="contents" style="padding: 100px 0; min-height: 600px; background:#f6f7f9;">
    <div style="max-width:760px; margin:0 auto; background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:36px; text-align:center;">
        <div style="font-size:42px; font-weight:900; color:#087443;">완료</div>
        <h1 style="margin:12px 0;">주문이 접수되었습니다</h1>
        <p style="color:#667085;">주문상품은 채널관리자 주문관리와 발주사 발주대기 목록에 함께 표시됩니다.</p>

        @if($order)
            <div style="text-align:left; background:#f8fafc; border-radius:8px; padding:18px; margin:28px 0;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>주문번호</span><strong>Me9-{{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}</strong></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Shop 채널</span><strong>{{ $shop->channel_name }}</strong></div>
                <div style="display:flex; justify-content:space-between;"><span>결제금액</span><strong>{{ number_format($order->grand_total) }}원</strong></div>
            </div>
        @endif

        <div style="display:flex; justify-content:center; gap:10px;">
            <a href="{{ route('shop.channel_main') }}" style="height:46px; display:inline-flex; align-items:center; padding:0 18px; border:1px solid #111827; border-radius:6px; color:#111827; text-decoration:none; font-weight:900;">채널 홈</a>
            <a href="{{ route('distributor.orders.pending') }}" style="height:46px; display:inline-flex; align-items:center; padding:0 18px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; font-weight:900;">발주대기 확인</a>
        </div>
    </div>
</div>
@endsection
