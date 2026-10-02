<div class="shop-summary">
    <div><span>상품금액</span><strong>{{ number_format($order->orders_products->sum(fn ($item) => $item->line_total ?? $item->product_price * $item->product_qty)) }}원</strong></div>
    <div><span>배송비</span><strong>{{ number_format($order->shipping_charges) }}원</strong></div>
    @if($order->used_point)<div><span>사용 포인트</span><strong>{{ number_format($order->used_point) }}P</strong></div>@endif
    <div><span>결제수단</span><strong>{{ $order->payment_method }}</strong></div>
    <div class="total"><span>결제금액</span><strong>{{ number_format($order->grand_total) }}원</strong></div>
</div>
