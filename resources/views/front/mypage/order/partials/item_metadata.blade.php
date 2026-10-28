@php
    $category = $item->product?->category?->category_name;
    $productType = data_get($item->settlement_policy_snapshot, 'product_type');
@endphp
@if($category)<p style="font-size:12px; color:#666;">상품 분류: {{ $category }}</p>@endif
@if($productType)<p style="font-size:12px; color:#666;">상품 유형: {{ ['own'=>'자사', 'shared'=>'공유', 'public'=>'공유'][$productType] ?? $productType }}</p>@endif
@if($item->is_exchange_replacement)
    <p style="color:#6954a6; font-weight:700;">교환 재발송 상품 #{{ $item->id }} · 추가 상품 결제 없음</p>
@endif
@if($item->courier_name || $item->tracking_number)
    <p style="margin-top:6px;">택배사: {{ $item->courier_name ?: '-' }} / 송장번호: {{ $item->tracking_number ?: '-' }}</p>
@endif
@if($item->shipped_at)<p style="font-size:12px; color:#666;">발송일: {{ $item->shipped_at->format('Y.m.d H:i') }}</p>@endif
@if($item->delivered_at)<p style="font-size:12px; color:#666;">배송완료일: {{ $item->delivered_at->format('Y.m.d H:i') }}</p>@endif
