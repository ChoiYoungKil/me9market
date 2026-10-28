@extends('layouts.market-sub')
@section('page_title', '주문 상세')
@push('styles')
<link rel="stylesheet" href="{{ asset('me9market/css/order-details.css').'?v='.filemtime(public_path('me9market/css/order-details.css')) }}">
@endpush
@section('content')
@php
    $actions = ['cancel'=>'취소신청', 'return'=>'반품신청', 'exchange'=>'교환신청', 'confirm'=>'구매확정', 'inquiry'=>'판매자 문의하기'];
    $retryItem = is_scalar(old('order_product_id')) ? $order->orders_products->firstWhere('id', old('order_product_id')) : null;
    $retryAction = is_string(old('type')) && isset($actions[old('type')]) && $retryItem ? old('type') : '';
    if ($retryAction && $retryAction !== 'inquiry' && !\App\Support\OrderItemStatus::customerActionAllowed($retryAction, $retryItem->normalized_status)) $retryAction = '';
    $retryValues = [];
    foreach (['reason','detail_reason','recovery_method','customer_courier_name','customer_tracking_number','rating','review','inquiry_category','subject','message'] as $key) {
        if (is_string(old($key))) $retryValues[$key] = old($key);
    }
@endphp
<div class="market-shell market-order-view" data-order-view data-retry-action="{{ $retryAction }}" data-retry-item="{{ $retryItem?->id }}">
    <nav class="market-breadcrumb" aria-label="현재 위치"><a href="{{ route('home') }}">HOME</a><span>/</span><a href="{{ route('front.nonmember.order_check') }}">주문조회</a><span>/</span><span>주문 상세</span></nav>
    <header class="market-order-heading"><div><p class="home-eyebrow">YOUR ORDER</p><h1>주문 상세</h1></div><p><time>{{ $order->created_at->format('Y.m.d') }}</time><span>주문번호 <strong>Me9-{{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}</strong></span></p></header>
    @if(session('flash_message_success'))<p class="market-alert" role="status">{{ session('flash_message_success') }}</p>@endif
    @if(session('flash_message_error'))<p class="market-alert market-alert-error" role="alert">{{ session('flash_message_error') }}</p>@endif
    @if($errors->any())<div class="market-alert market-alert-error" role="alert" data-order-errors>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="market-order-section" aria-labelledby="purchased-title"><h2 id="purchased-title">구매 상품</h2>
        @foreach($order->orders_products->groupBy('shop_channel_id') as $items)
        <div class="market-order-shop"><strong>{{ $items->first()->shopChannel?->channel_name ?: 'Me9 market' }}</strong><span>판매자 {{ $items->first()->shopChannel?->vendor?->name ?: $items->first()->product?->vendor?->name ?: '판매자' }}</span></div>
        @foreach($items as $item)
        @php
            $image = $item->product?->product_image ?: $item->product?->images->first()?->image ?: 'no-image.png';
            $claim = $item->claims->sortByDesc('id')->first();
        @endphp
        <article class="market-order-card" id="order-item-{{ $item->id }}" data-order-item="{{ $item->id }}" data-return-address="{{ $item->manual_return_address }}">
            <div class="market-order-card-top"><span class="market-order-status" data-status="{{ $item->normalized_status }}">{{ $item->status_label }}</span>
                <div class="market-order-dates">
                    @if($item->confirmed_at)<span>구매확정일 {{ $item->confirmed_at->format('Y.m.d H:i') }}</span>@endif
                    @foreach($item->claims->where('status','completed') as $completed)<span>{{ ['cancel'=>'취소완료일','return'=>'반품완료일','exchange'=>'교환완료일'][$completed->type] ?? '완료일' }} {{ $completed->updated_at->format('Y.m.d H:i') }}</span>@endforeach
                </div>
            </div>
            <div class="market-order-product">
                <img class="market-order-image" src="{{ asset('front/images/product_images/small/'.$image) }}" alt="" width="88" height="88" loading="lazy">
                <div class="market-order-product-copy"><h3>{{ $item->product_name }}</h3><p class="market-order-option">{{ $item->product_size ?: '기본옵션' }} / {{ $item->product_qty }}개</p>
                    @if($item->product?->category)<p class="market-order-category">{{ $item->product->category->category_name }}</p>@endif
                    @if($item->courier_name || $item->tracking_number)<p>택배사 {{ $item->courier_name }} · 송장번호 {{ $item->tracking_number }}</p>@endif
                    @if($item->is_exchange_replacement)<p class="market-order-link">교환 재발송 상품 #{{ $item->id }} · <a href="#order-item-{{ $item->replacement_for_order_product_id }}">원상품 보기 ↑</a></p>@endif
                    @if($item->exchangeReplacement)<p class="market-order-link">교환 재발송 상품 #{{ $item->exchangeReplacement->id }} · <a href="#order-item-{{ $item->exchangeReplacement->id }}">{{ $item->exchangeReplacement->status_label }} 확인 ↓</a></p>@endif
                    @if($item->refund_status)<p>{{ $item->refund_status_label }} @if($item->refund_status !== 'reversed_original')· {{ number_format($item->refund_cash_amount) }}원@endif</p>@endif
                </div>
                <strong class="market-order-price">{{ number_format($item->line_total ?? $item->product_price * $item->product_qty) }}<small>원</small></strong>
            </div>
            @if($claim)
                <details class="market-order-claim"><summary>{{ ['cancel'=>'취소','return'=>'반품','exchange'=>'교환'][$claim->type] ?? '' }} 신청 내역</summary>
                    <dl><dt>신청 사유</dt><dd>{{ $claim->reason }}@if($claim->detail_reason)<p>{{ $claim->detail_reason }}</p>@endif</dd>
                    @if($claim->pickup_method)<dt>회수 방법</dt><dd>{{ $claim->pickup_method === 'manual' ? '수동회수' : '자동회수' }}</dd>@endif
                    @if($claim->return_address)<dt>보내실 주소</dt><dd>{{ $claim->return_address }}</dd>@endif
                    @if($claim->customer_tracking_number)<dt>회수 송장</dt><dd>{{ $claim->customer_courier_name }} {{ $claim->customer_tracking_number }}</dd>@endif
                    @if($claim->admin_comment)<dt>판매자 처리 내역</dt><dd>{{ $claim->admin_comment }}</dd>@endif</dl>
                </details>
            @endif
            <div class="market-order-actions">
                @foreach($actions as $action=>$label)
                    @if($action === 'inquiry' || (\App\Support\OrderItemStatus::customerActionAllowed($action,$item->normalized_status) && ($action !== 'exchange' || $item->normalized_status === 'delivered')))
                        <button type="button" data-order-action="{{ $action }}" data-order-product="{{ $item->id }}" @if($action==='cancel') data-cancel-item="{{ $item->id }}" @endif>{{ $label }}</button>
                    @endif
                @endforeach
            </div>
        </article>
        @endforeach
        @endforeach
    </section>
    <div class="market-order-information">
        <section class="market-order-section"><h2>주문자 정보</h2><dl class="market-order-meta"><dt>주문자 이름</dt><dd>{{ $order->buyer_name ?: $order->name }}</dd><dt>휴대폰 번호</dt><dd>{{ $order->buyer_mobile ?: $order->mobile }}</dd><dt>이메일 주소</dt><dd>{{ $order->email }}</dd></dl></section>
        <section class="market-order-section"><h2>배송 정보</h2><dl class="market-order-meta"><dt>받는 사람</dt><dd>{{ $order->name }}</dd><dt>휴대폰 번호</dt><dd>{{ $order->mobile }}</dd><dt>주소</dt><dd>{{ $order->pincode }} {{ $order->address }} {{ $order->city }} {{ $order->state }}</dd><dt>배송메모</dt><dd>{{ $order->delivery_memo ?: '-' }}</dd></dl></section>
    </div>
    <section class="market-order-section"><h2>결제 정보</h2><dl class="market-order-payment"><dt>총 상품금액</dt><dd>{{ number_format($order->orders_products->sum(fn($item)=>$item->line_total ?? $item->product_price*$item->product_qty)) }}원</dd><dt>배송비</dt><dd>+ {{ number_format($order->shipping_charges) }}원</dd><dt>포인트 사용</dt><dd>- {{ number_format($order->used_point) }}P</dd>@if($order->coupon_amount)<dt>쿠폰 할인</dt><dd>- {{ number_format($order->coupon_amount) }}원</dd>@endif<dt>적립포인트</dt><dd>{{ number_format($earnedPoints) }}P</dd><dt>결제수단</dt><dd>{{ $order->payment_method }}</dd><dt class="order-payment-total">최종 결제금액</dt><dd class="order-payment-total">{{ number_format($order->grand_total) }}원</dd></dl></section>
    <section class="market-order-section" id="order-inquiries"><h2>판매자 문의 내역</h2>
        @forelse($inquiries as $inquiry)<details class="market-order-inquiry" data-inquiry="{{ $inquiry->id }}"><summary><span>{{ $inquiry->status === 'completed' ? '답변완료' : '접수완료' }}</span><strong>{{ $inquiry->subject }}</strong><time>{{ $inquiry->created_at->format('Y.m.d') }}</time></summary><div><p>{{ $inquiry->message }}</p>@if($inquiry->admin_reply)<h3>판매자 답변</h3><p>{{ $inquiry->admin_reply }}</p><small>{{ $inquiry->replied_at?->format('Y.m.d H:i') }}</small>@else<p class="market-order-help">판매자가 확인 후 답변합니다.</p>@endif</div></details>@empty<p class="market-order-help">등록된 판매자 문의가 없습니다.</p>@endforelse
    </section>
    <div class="market-order-back"><a class="market-button market-button-outline" href="{{ route('home') }}">메인으로 <span>↗</span></a><a class="market-button market-button-outline" href="{{ route('front.nonmember.order_check') }}">다른 주문 조회 <span>↗</span></a></div>
</div>
@include('front.pages.partials.nonmember_order_popups')
<script type="application/json" id="order-retry-values">@json($retryValues)</script>
@endsection
@push('scripts')
<script src="{{ asset('me9market/js/order-details.js').'?v='.filemtime(public_path('me9market/js/order-details.js')) }}" defer></script>
@endpush
