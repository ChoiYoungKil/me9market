@extends('layouts.shop')
@section('content')
@php
    $product = $shopProduct->product;
    $price = $shopProduct->selling_price ?: $shopProduct->product_price;
    $images = collect([$product->product_image])->merge($product->images->pluck('image'))->filter()->unique()->values();
    $imageUrl = $images->first() ? asset('front/images/product_images/large/'.$images->first()) : asset('front/images/product_images/small/no-image.png');
    $minimumQuantity = $product->purchase_limit_enabled ? max(1, (int) $product->purchase_min_qty) : 1;
    $maximumQuantity = min($shopProduct->purchase_limit ?: 999, $shopProduct->stock ?? 999, $product->purchase_limit_enabled ? ($product->purchase_max_qty ?: 999) : 999);
@endphp
<div id="contents"><div class="product_details"><div class="shop-inner">
    <div class="top_bx">
        <div class="l_bx"><div class="img_bx"><div><img id="product-main-image" src="{{ $imageUrl }}" alt="{{ $product->product_name }}"></div></div>
            <div class="shop-thumbnails">@foreach($images as $image)<button type="button" data-gallery-image="{{ asset('front/images/product_images/large/'.$image) }}" title="상품 이미지 {{ $loop->iteration }}"><img src="{{ asset('front/images/product_images/small/'.$image) }}" alt="상품 이미지 {{ $loop->iteration }}"></button>@endforeach</div>
        </div>
        <div class="r_bx"><div class="txt1">{{ $product->category_path }}<span>상품코드: {{ $product->product_code }}</span></div><h1 class="txt2">{{ $product->product_name }}</h1>
            @include('shop.partials.rating')
            <form action="{{ route('front.shop.cart.add') }}" method="POST" class="shop-purchase" data-price="{{ $price }}">
                @csrf<input type="hidden" name="shop_product_id" value="{{ $shopProduct->id }}">
                <label>옵션선택<select class="shop-control" name="option">
                    @forelse($product->attributes->where('status', 1) as $attribute)<option value="{{ $attribute->size }}" data-price-adjustment="{{ $attribute->price_delta }}">{{ $attribute->size }}@if($attribute->price_delta != 0) ({{ $attribute->price_delta > 0 ? '+' : '' }}{{ number_format($attribute->price_delta) }}원)@endif</option>@empty<option value="기본옵션">기본옵션</option>@endforelse
                </select></label>
                @if($product->attributes->where('status', 1)->count() > 1)<button type="button" class="shop-btn small shop-option-add" data-add-option>선택 옵션 추가</button>@endif
                <div class="shop-option-selections" data-option-selections></div>
                <div class="shop-option-row"><div><label for="product-quantity">수량</label>
                    <div class="shop-stepper" data-stepper><button type="button" data-step="-1" aria-label="수량 줄이기" title="수량 줄이기">−</button><input id="product-quantity" type="number" name="qty" value="{{ $minimumQuantity }}" min="{{ $minimumQuantity }}" max="{{ max($minimumQuantity, $maximumQuantity) }}" @disabled($maximumQuantity < $minimumQuantity) required><button type="button" data-step="1" aria-label="수량 늘리기" title="수량 늘리기">+</button></div>
                </div><strong data-option-unit-price>{{ number_format($price) }}원</strong></div>
                <p class="shop-stock">{{ $maximumQuantity < $minimumQuantity ? '주문 가능 수량 부족' : '주문 가능' }} / 최소 {{ $minimumQuantity }}개 ~ 최대 {{ $maximumQuantity }}개</p>
                <div class="shop-purchase-total"><strong>총 상품 금액</strong><output data-price-total>{{ number_format($price * $minimumQuantity) }}원</output></div>
                <div class="shop-actions"><button type="submit" class="shop-btn" @disabled($maximumQuantity < $minimumQuantity)>장바구니</button><button class="shop-btn primary" type="submit" name="buy_now" value="1" @disabled($maximumQuantity < $minimumQuantity)>바로 구매</button></div>
            </form>
        </div>
    </div>
    <div class="shop-product-tabs" data-tabs>
    <div class="shop-tab-buttons" role="tablist" aria-label="상품 정보">
        <button id="detail-tab" role="tab" type="button" aria-controls="detail-panel" aria-selected="true">상세 정보</button>
        <button id="sales-tab" role="tab" type="button" aria-controls="sales-panel" aria-selected="false" tabindex="-1">판매 정보</button>
        <button id="inquiry-tab" role="tab" type="button" aria-controls="inquiry-panel" aria-selected="false" tabindex="-1">상품 Q&amp;A</button>
    </div>
    <section id="detail-panel" role="tabpanel" aria-labelledby="detail-tab" class="shop-section"><h2>상품 내용</h2>
        @if($product->detail_display_type === 'image' && ($product->detail_pc_image || $product->detail_mobile_image))
            <picture>@if($product->detail_mobile_image)<source media="(max-width:768px)" srcset="{{ asset('front/images/product_detail_images/'.$product->detail_mobile_image) }}">@endif<img src="{{ asset('front/images/product_detail_images/'.($product->detail_pc_image ?: $product->detail_mobile_image)) }}" alt="{{ $product->product_name }} 상세" style="max-width:100%; height:auto;" loading="lazy"></picture>
        @else<div class="shop-detail-copy">{{ strip_tags($product->detail_text ?: $product->description ?: '상품 상세 설명이 준비 중입니다.') }}</div>@endif
        @if(($reviews ?? collect())->isNotEmpty())
            <h2>구매 후기</h2>
            @foreach($reviews as $review)<article class="shop-review"><strong>{{ $review->rating }} / 5</strong><time>{{ $review->created_at?->format('Y-m-d') }}</time><p class="shop-detail-copy">{{ $review->review }}</p></article>@endforeach
        @endif
    </section>
    <section id="sales-panel" role="tabpanel" aria-labelledby="sales-tab" class="shop-section" hidden>
        @if($product->product_notice_items)<h2>상품정보 제공고시</h2><dl class="shop-meta">@foreach($product->product_notice_items as $key=>$value)<dt>{{ $key }}</dt><dd>{{ is_scalar($value) ? $value : implode(' / ', array_filter($value, 'is_scalar')) }}</dd>@endforeach</dl>@endif
        <h2>배송 / 교환 / 반품 안내</h2><p>배송비: {{ app(\App\Services\ShopOrderTotals::class)->shippingLabel($product) }}</p>
        @if($policy)<div class="shop-detail-copy">{{ strip_tags($policy->content) }}</div>@endif
    </section>
    <section id="inquiry-panel" role="tabpanel" aria-labelledby="inquiry-tab" class="shop-section" hidden>
        <h2>내 상품 문의</h2>
        <div class="shop-inquiries">
            @forelse($inquiries ?? collect() as $inquiry)<details><summary><span>{{ $inquiry->admin_reply ? '답변완료' : '답변대기' }}</span><strong>{{ $inquiry->subject }}</strong><time>{{ $inquiry->created_at?->format('Y-m-d') }}</time></summary><div class="shop-detail-copy">{{ $inquiry->message }}</div>@if($inquiry->admin_reply)<div class="shop-inquiry-reply shop-detail-copy">{{ $inquiry->admin_reply }}</div>@endif</details>@empty<p class="shop-empty">등록한 문의가 없습니다.</p>@endforelse
        </div>
        <h2>상품 문의하기</h2><form action="{{ route('front.shop.order.inquiry') }}" method="POST" class="shop-form-grid">
        @csrf<input type="hidden" name="shop_product_id" value="{{ $shopProduct->id }}">
        <label>문의 분류<select name="inquiry_category" required class="shop-control"><option value="">선택</option><option value="delivery">배송문의</option><option value="claim">교환·반품</option><option value="product">상품관련</option><option value="payment">결제문의</option><option value="other">기타</option></select></label>
        <label>제목<input name="subject" class="shop-control" maxlength="255" required></label>
        <label class="wide">문의 내용<textarea name="message" class="shop-control" maxlength="3000" required></textarea></label>
        <div class="shop-actions wide"><button type="submit" class="shop-btn primary">문의하기</button></div>
    </form></section>
    </div>
    <div class="shop-actions"><a href="{{ route('shop.products_list') }}" class="shop-btn">상품 목록</a></div>
</div></div></div>
@endsection
