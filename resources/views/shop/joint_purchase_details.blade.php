@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
@php
    $rate = $jointPurchase->min_quantity > 0 ? min(100, round(($jointPurchase->current_quantity / $jointPurchase->min_quantity) * 100)) : 0;
    $pricing = app(\App\Services\JointPurchasePricingService::class);
    $currentPrice = $pricing->priceForQuantity($jointPurchase, max(1, (int) $jointPurchase->current_quantity));
    $nextPrice = $pricing->priceForQuantity($jointPurchase, ((int) $jointPurchase->current_quantity) + 1);
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
                <div style="background: #f8fafc; padding: 14px; border-radius: 6px;"><b>{{ number_format($currentPrice['unit_price']) }}원</b><br><span style="color:#667085;">현재 적용가</span></div>
            </div>
            <div style="border:1px solid #d9dee7; border-radius:6px; overflow:hidden; margin-bottom:18px;">
                <div style="background:#f8fafc; padding:10px 12px; font-weight:900;">수량별 공동구매가</div>
                @foreach($pricing->tiers((int) $jointPurchase->id) as $tier)
                    <div style="display:flex; justify-content:space-between; padding:10px 12px; border-top:1px solid #eef1f5;">
                        <span>{{ number_format($tier->min_quantity) }}~{{ $tier->max_quantity ? number_format($tier->max_quantity) : '이상' }}개</span>
                        <strong>{{ number_format($tier->unit_price) }}원</strong>
                    </div>
                @endforeach
                <div style="padding:10px 12px; border-top:1px solid #eef1f5; color:#667085;">다음 구매 기준 예상가: {{ number_format($nextPrice['unit_price']) }}원</div>
            </div>
            <a href="{{ route('shop.products_list') }}" style="display:inline-flex; height: 46px; align-items:center; padding: 0 18px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; font-weight:900;">채널 상품에서 구매하기</a>
        </div>
    </main>
</div>
@endsection
