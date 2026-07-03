@php
    $rate = $joint->min_quantity > 0 ? min(100, round(($joint->current_quantity / $joint->min_quantity) * 100)) : 0;
@endphp
<a href="{{ route('shop.joint_purchase_details', $joint->id) }}" style="display: block; background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 16px; text-decoration: none; color: #151922;">
    <div style="font-size: 12px; color: #b54708; font-weight: 800;">공동구매 {{ $rate }}% 달성</div>
    <strong style="display: block; margin-top: 6px;">{{ $joint->product_name }}</strong>
    <div style="height: 8px; background: #eef1f5; border-radius: 999px; overflow: hidden; margin: 14px 0 8px;">
        <div style="height: 100%; width: {{ $rate }}%; background: #111827;"></div>
    </div>
    <div style="display: flex; justify-content: space-between; color: #667085; font-size: 13px;">
        <span>{{ $joint->current_quantity }} / {{ $joint->min_quantity }}개</span>
        <span>{{ $joint->end_date }} 마감</span>
    </div>
    <div style="margin-top: 10px; font-size: 18px; font-weight: 900;">{{ number_format($joint->discount_price) }}원</div>
</a>
