@php
    $productSubtotal = $rows->sum('line_total');
    $productShipping = $totals['shipping_by_product'][$rows->first()['id']] ?? 0;
@endphp
<div class="shop-shipping-equation" data-shipping-group="{{ $rows->first()['id'] }}" data-group-subtotal="{{ $productSubtotal }}">
    <strong>{{ number_format($productSubtotal) }}원 + 배송비 <span data-group-shipping>{{ number_format($productShipping) }}원</span> = <span data-group-total>{{ number_format($productSubtotal + $productShipping) }}원</span></strong>
    <small>{{ app(\App\Services\ShopOrderTotals::class)->shippingLabel($rows->first()['product'], $rows->first()['shop_product'] ?? null) }} (개별 상품 기준)</small>
</div>
