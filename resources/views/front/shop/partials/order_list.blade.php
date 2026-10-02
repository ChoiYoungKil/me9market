@forelse($orders as $order)
<article class="shop-order">
    <div class="shop-order-heading"><div><strong>{{ $shop->channel_name }}</strong> <span>{{ $order->buyer_name ?: $order->name }}</span></div><div><span>{{ $order->created_at?->format('Y-m-d') }} | Me9-Shop-{{ str_pad($order->id, 7, '0', STR_PAD_LEFT) }}</span> <a class="shop-btn small" href="{{ route('front.shop.order.view', $order->id) }}">주문상세</a></div></div>
    @foreach($order->orders_products as $item)
        @include('front.shop.partials.order_item')
    @endforeach
</article>
@empty
    <div class="shop-empty">조회된 내역이 없습니다.<div class="shop-actions"><a class="shop-btn" href="{{ route('front.shop.order.confirm') }}">주문조회</a></div></div>
@endforelse
@include('shop.partials.pagination', ['paginator' => $orders])
