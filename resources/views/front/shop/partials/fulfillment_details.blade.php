<section class="shop-section"><h2>상품별 처리·배송 내역</h2>
@foreach($order->orders_products as $item)
    @php
        $image=$item->product?->product_image ?: $item->product?->images->where('status', 1)->first()?->image;
        $imageUrl=$image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png');
        $claim=$item->claims->sortByDesc('id')->first();
    @endphp
    <article class="shop-fulfillment-item"><h3>{{ $item->product_name }} <span>{{ $item->status_label }}</span></h3>
        <dl class="shop-meta">
        @if($item->recipient_name || $item->recipient_address)<dt>상품 수령인 / 배송지</dt><dd>{{ $item->recipient_name ?? $order->name }} / {{ $item->recipient_postcode }} {{ $item->recipient_address ?? $order->address }} {{ $item->recipient_address_detail }}</dd>@endif
        <dt>옵션 / 수량</dt><dd>{{ $item->product_size }} / {{ $item->product_qty }}개</dd><dt>택배사 / 송장번호</dt><dd>{{ $item->courier_name ?: '-' }} / {{ $item->tracking_number ?: '-' }}</dd><dt>발송일시</dt><dd>{{ $item->shipped_at?->format('Y-m-d H:i') ?: '-' }}</dd><dt>배송완료일시</dt><dd>{{ $item->delivered_at?->format('Y-m-d H:i') ?: '-' }}</dd><dt>구매확정일시</dt><dd>{{ $item->confirmed_at?->format('Y-m-d H:i') ?: '-' }}</dd>
        @if($item->is_exchange_replacement)<dt>교환 재발송</dt><dd>원상품 #{{ $item->replacement_for_order_product_id }}의 교환상품 #{{ $item->id }} · 추가 상품대금 없음</dd>@endif
        @if($item->exchangeReplacement)<dt>교환상품</dt><dd>#{{ $item->exchangeReplacement->id }} / {{ $item->exchangeReplacement->status_label }} / {{ $item->exchangeReplacement->courier_name }} {{ $item->exchangeReplacement->tracking_number }}</dd>@endif
        @if($claim)<dt>신청 사유</dt><dd>{{ $claim->reason }} {{ $claim->detail_reason }}</dd><dt>신청일시</dt><dd>{{ $claim->created_at?->format('Y-m-d H:i') }}</dd><dt>회수방법</dt><dd>{{ ['automatic'=>'자동회수','manual'=>'수동회수'][$claim->pickup_method] ?? '-' }}</dd><dt>판매자 처리내역</dt><dd class="shop-preserve-lines">{{ $claim->admin_comment ?: '처리 중입니다.' }}</dd>@endif
        @if($item->refund_status)<dt>환불상태</dt><dd>{{ $item->refund_status_label }} / {{ number_format($item->refund_cash_amount) }}원</dd>@endif
        </dl>
        <div class="shop-actions">
        @foreach(['cancel'=>'취소신청','return'=>'반품신청','exchange'=>'교환신청','confirm'=>'구매확정'] as $action=>$label)
            @if(\App\Support\OrderItemStatus::customerActionAllowed($action,$item->normalized_status))<button class="shop-btn small" type="button" data-dialog="{{ $action }}-{{ $item->id }}">{{ $label }}</button>@endif
        @endforeach
        <button class="shop-btn small" type="button" data-dialog="inquiry-{{ $item->id }}">판매자 문의하기</button></div>
        @include('front.shop.partials.order_popups')
    </article>
@endforeach
</section>
