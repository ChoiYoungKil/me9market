@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
@php
    $rate = $jointPurchase->min_quantity > 0 ? min(100, round(($jointPurchase->current_quantity / $jointPurchase->min_quantity) * 100)) : 0;
@endphp
<div style="background: #f6f7f9; min-height: 100vh; padding-bottom: 50px;">
    <div style="background: #111827; color: #fff; padding: 22px 32px;">
        <div style="max-width: 1180px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin: 0; font-size: 24px;">공동구매 상세</h1>
            <a href="{{ route('shop.joint_purchases_list') }}" style="color: #fff; text-decoration: none; font-weight: 800;">목록</a>
        </div>
    </div>

    <main style="max-width: 980px; margin: 28px auto; padding: 0 20px;">
        <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 28px;">
            <div style="color: #b54708; font-weight: 900;">{{ $rate }}% 달성 · {{ $jointPurchase->end_date }} 마감</div>
            <h2 style="font-size: 30px;">{{ $jointPurchase->product_name }}</h2>
            <div style="height: 12px; background: #eef1f5; border-radius: 999px; overflow: hidden; margin: 18px 0;">
                <div style="height: 100%; width: {{ $rate }}%; background: #111827;"></div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 22px;">
                <div style="background: #f8fafc; padding: 14px; border-radius: 6px;"><b>{{ $jointPurchase->min_quantity }}</b><br><span style="color:#667085;">목표수량</span></div>
                <div style="background: #f8fafc; padding: 14px; border-radius: 6px;"><b>{{ $jointPurchase->current_quantity }}</b><br><span style="color:#667085;">현재수량</span></div>
                <div style="background: #f8fafc; padding: 14px; border-radius: 6px;"><b>{{ number_format($jointPurchase->discount_price) }}원</b><br><span style="color:#667085;">공동구매가</span></div>
            </div>
            <a href="{{ route('shop.products_list') }}" style="display:inline-flex; height: 46px; align-items:center; padding: 0 18px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; font-weight:900;">채널 상품에서 구매하기</a>
        </div>
    </main>
</div>
@endsection
