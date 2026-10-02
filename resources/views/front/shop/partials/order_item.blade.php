@php
    $image = $item->product?->product_image ?: $item->product?->images->first()?->image;
    $imageUrl = $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png');
    $latestClaim = $item->claims->sortByDesc('id')->first();
@endphp
<div class="shop-item shop-order-item">
    <div class="shop-item-status">{{ $item->status_label }}</div>
    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" loading="lazy">
    <div><h3>{{ $item->product_name }}</h3><p>{{ $item->product_size ?: '기본옵션' }} / {{ $item->product_qty }}개</p>
        @if($item->courier_name || $item->tracking_number)<p>{{ $item->courier_name }} {{ $item->tracking_number }}</p>@endif
        @if($item->refund_status)<p>{{ $item->refund_status_label }} @if($item->refund_status !== 'reversed_original')/ {{ number_format($item->refund_cash_amount) }}원@endif</p>@endif
        @if($item->exchangeReplacement)<p>교환 상품: {{ $item->exchangeReplacement->product_name }} / {{ $item->exchangeReplacement->status_label }}</p>@endif
    </div>
    <div class="shop-item-side"><strong>{{ number_format($item->line_total ?? $item->product_price * $item->product_qty) }}원</strong>
        <div class="shop-item-actions">
            @if(isset($claimType))
                @if($latestClaim)<button class="shop-btn small" type="button" data-dialog="claim-{{ $latestClaim->id }}">{{ ['cancel'=>'취소', 'return'=>'반품', 'exchange'=>'교환'][$claimType] }} 상세</button>@endif
            @else
                @foreach(['cancel'=>'취소신청', 'return'=>'반품신청', 'exchange'=>'교환신청', 'confirm'=>'구매확정'] as $action=>$label)
                    @if(\App\Support\OrderItemStatus::customerActionAllowed($action, $item->normalized_status))<button type="button" class="shop-btn small" data-dialog="{{ $action }}-{{ $item->id }}">{{ $label }}</button>@endif
                @endforeach
                <button type="button" class="shop-btn small" data-dialog="inquiry-{{ $item->id }}">문의하기</button>
                @if($latestClaim && $latestClaim->pickup_method === 'manual')<button type="button" class="shop-btn small" data-dialog="claim-{{ $latestClaim->id }}">송장정보</button>@endif
            @endif
        </div>
    </div>
</div>
@if(!isset($claimType))
    @include('front.shop.partials.order_popups')
@endif
@if($latestClaim && (isset($claimType) || $latestClaim->pickup_method === 'manual'))
    @include('front.shop.partials.claim_popup', ['claim' => $latestClaim])
@endif
