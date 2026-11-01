@extends('layouts.shop')
@push('styles')<link rel="stylesheet" href="/common/policy-rich-text.css?v={{ filemtime(public_path('common/policy-rich-text.css')) }}"><link rel="stylesheet" href="/channel_assets/vendor/quill/quill.snow.css">@endpush
@section('content')
@php
    $product = $shopProduct->product;
    $price = $shopProduct->selling_price ?: $shopProduct->product_price;
    if ($jointPurchase) $price = app(\App\Services\JointPurchasePricingService::class)->priceForQuantity($jointPurchase, max(1, (int)$jointPurchase->current_quantity))['unit_price'];
    $images = collect([$product->product_image])->merge($product->images->where('status', 1)->pluck('image'))->filter()->unique()->values();
    $imageUrl = $images->first() ? asset('front/images/product_images/large/'.$images->first()) : asset('front/images/product_images/small/no-image.png');
    $minimumQuantity = $product->purchase_limit_enabled ? max(1, (int) $product->purchase_min_qty) : 1;
    $maximumQuantity = min($shopProduct->purchase_limit ?: 999, $shopProduct->stock ?? 999, $product->purchase_limit_enabled ? ($product->purchase_max_qty ?: 999) : 999);
    if ($product->stock_usage === 'used') $maximumQuantity = min($maximumQuantity, $product->attributes->where('status', 1)->sum('stock'));
    if ($product->attributes->isNotEmpty() && $product->attributes->where('status', 1)->isEmpty()) $maximumQuantity = 0;
@endphp
<div id="contents"><div class="product_details"><div class="shop-inner">
    <div class="top_bx">
        <div class="l_bx"><div class="img_bx"><div><img id="product-main-image" src="{{ $imageUrl }}" alt="{{ $product->product_name }}"></div></div>
            <div class="shop-thumbnails">@foreach($images as $image)<button type="button" data-gallery-image="{{ asset('front/images/product_images/large/'.$image) }}" title="상품 이미지 {{ $loop->iteration }}"><img src="{{ asset('front/images/product_images/small/'.$image) }}" alt="상품 이미지 {{ $loop->iteration }}"></button>@endforeach</div>
        </div>
        <div class="r_bx"><div class="txt1">{{ $product->category_path }}<span>상품코드: {{ $product->product_code }}</span></div><h1 class="txt2">{{ $product->product_name }}</h1>
            @if($jointPurchase)<p class="shop-stock">{{ $jointPurchase->sale_name }} · {{ $jointPurchase->purchase_code }}</p><p>{{ $jointPurchase->start_date }} ~ {{ $jointPurchase->end_date }}</p>@endif
            @include('shop.partials.rating')
            <form id="shop-purchase-form" action="{{ route('front.shop.cart.add') }}" method="POST" class="shop-purchase" data-price="{{ $price }}" data-minimum="{{ $minimumQuantity }}" data-maximum="{{ $maximumQuantity }}" @if($jointPurchase) data-joint-purchase data-joint-quantity="{{ $jointPurchase->current_quantity }}" data-joint-tiers="{{ json_encode(app(\App\Services\JointPurchasePricingService::class)->tiers($jointPurchase->id)) }}" @endif>
                @csrf<input type="hidden" name="shop_product_id" value="{{ $shopProduct->id }}">
                @if($jointPurchase)<input type="hidden" name="option" value="기본옵션">@else
                <label>옵션<select class="shop-control" name="option" required data-purchase-picker>
                    <option value="">- 선택 -</option>
                    @forelse($product->attributes->where('status', 1) as $attribute)<option value="{{ $attribute->size }}" data-price-adjustment="{{ $attribute->price_delta }}" data-stock="{{ $product->stock_usage === 'used' ? $attribute->stock : $maximumQuantity }}">{{ $attribute->size }}@if($attribute->price_delta != 0) ({{ $attribute->price_delta > 0 ? '+' : '' }}{{ number_format($attribute->price_delta) }}원)@endif</option>@empty<option value="기본옵션" data-stock="{{ $maximumQuantity }}">기본옵션</option>@endforelse
                </select></label>
                @endif
                <div class="shop-option-selections" data-option-selections="top"></div><div data-purchase-fields></div>
                <div class="shop-option-row"><div><label for="product-quantity">수량</label>
                    <div class="shop-stepper" data-stepper><button type="button" data-step="-1" aria-label="수량 줄이기" title="수량 줄이기">−</button><input id="product-quantity" type="number" name="qty" value="{{ $minimumQuantity }}" min="{{ $minimumQuantity }}" max="{{ max($minimumQuantity, $maximumQuantity) }}" @disabled($maximumQuantity < $minimumQuantity) required><button type="button" data-step="1" aria-label="수량 늘리기" title="수량 늘리기">+</button></div>
                </div><strong data-option-unit-price>{{ number_format($price) }}원</strong></div>
                <p class="shop-stock">{{ $maximumQuantity < $minimumQuantity ? '주문 가능 수량 부족' : '주문 가능' }} / 최소 {{ $minimumQuantity }}개 ~ 최대 {{ $maximumQuantity }}개</p>
                <p data-purchase-error class="shop-purchase-error" role="status" hidden></p>
                <div class="shop-purchase-total"><strong>총 상품 금액</strong><output data-price-total>{{ number_format($price * $minimumQuantity) }}원</output></div>
                <div class="shop-actions"><button type="submit" class="shop-btn" @disabled($maximumQuantity < $minimumQuantity)>장바구니</button><button class="shop-btn primary" type="submit" name="buy_now" value="1" @disabled($maximumQuantity < $minimumQuantity)>바로 구매</button></div>
            </form>
            @auth
                @php
                    $isWishlisted = \App\Models\Wishlist::where('user_id', auth()->id())->where('shop_channel_product_id', $shopProduct->id)->exists();
                @endphp
                <form class="shop-actions" method="post" action="{{ route('shop.wishlist.toggle', $shopProduct->id) }}">
                    @csrf
                    <button type="submit" class="shop-btn" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}">{{ $isWishlisted ? '찜 해제' : '찜하기' }}</button>
                </form>
            @else
                <div class="shop-actions"><a href="{{ route('shop.login') }}" class="shop-btn">로그인 후 찜하기</a></div>
            @endauth
        </div>
    </div>
    <aside class="shop-purchase-dock" data-purchase-dock aria-label="구매 옵션 및 주문">
        <div class="shop-purchase-dock-inner">
            @unless($jointPurchase)<button type="button" class="shop-dock-toggle" aria-expanded="false" aria-controls="purchase-options-dock" data-option-toggle>구매옵션선택 <span aria-hidden="true">⌃</span></button>@endunless
            <div class="shop-dock-summary"><div class="shop-dock-product"><img src="{{ $imageUrl }}" alt="{{ $product->product_name }}"><div><strong>{{ $product->product_name }}</strong><p>{{ $product->product_code }}</p></div></div><div class="shop-dock-total"><span>총 상품금액</span><output data-dock-total>0원</output></div><div class="shop-actions"><button type="submit" form="shop-purchase-form" class="shop-btn" aria-label="하단 장바구니" @disabled($maximumQuantity < $minimumQuantity)>장바구니</button><button type="submit" form="shop-purchase-form" name="buy_now" value="1" class="shop-btn primary" aria-label="하단 구매하기" @disabled($maximumQuantity < $minimumQuantity)>구매하기</button></div></div>
            @unless($jointPurchase)<div id="purchase-options-dock" class="shop-options-collapse collapsed" inert><div class="shop-dock-options"><label>옵션<select class="shop-control" data-purchase-picker aria-label="하단 구매옵션"></select></label><div class="shop-option-selections" data-option-selections="dock"></div></div></div>@endunless
        </div>
    </aside>
    <div class="shop-product-tabs" data-tabs>
    <div class="shop-tab-buttons" role="tablist" aria-label="상품 정보">
        <button id="detail-tab" role="tab" type="button" aria-controls="detail-panel" aria-selected="true">상세 정보</button>
        <button id="sales-tab" role="tab" type="button" aria-controls="sales-panel" aria-selected="false" tabindex="-1">판매 정보</button>
        @if($jointPurchase)<button id="joint-tab" role="tab" type="button" aria-controls="joint-panel" aria-selected="false" tabindex="-1">공동구매현황</button>@endif
        <button id="inquiry-tab" role="tab" type="button" aria-controls="inquiry-panel" aria-selected="false" tabindex="-1">상품 Q&amp;A</button>
    </div>
    <section id="detail-panel" role="tabpanel" aria-labelledby="detail-tab" class="shop-section"><h2>상품 내용</h2>
        @if($product->detail_display_type === 'image' && ($product->detail_pc_image || $product->detail_mobile_image))
            <picture>@if($product->detail_mobile_image)<source media="(max-width:768px)" srcset="{{ asset('front/images/product_detail_images/'.$product->detail_mobile_image) }}">@endif<img src="{{ asset('front/images/product_detail_images/'.($product->detail_pc_image ?: $product->detail_mobile_image)) }}" alt="{{ $product->product_name }} 상세" style="max-width:100%; height:auto;" loading="lazy"></picture>
        @else<div class="shop-detail-copy">{!! app(\App\Services\ShopCommunity::class)->clean($product->detail_text ?: $product->description ?: '상품 상세 설명이 준비 중입니다.') !!}</div>@endif
        @if(($reviews ?? collect())->isNotEmpty())
            <h2>구매 후기</h2>
            @foreach($reviews as $review)<article class="shop-review"><strong>{{ $review->rating }} / 5</strong><time>{{ $review->created_at?->format('Y-m-d') }}</time><p class="shop-detail-copy">{{ $review->review }}</p></article>@endforeach
        @endif
    </section>
    <section id="sales-panel" role="tabpanel" aria-labelledby="sales-tab" class="shop-section" hidden>
        @if($product->product_notice_items)<h2>상품정보 제공고시</h2><dl class="shop-meta">@foreach($product->product_notice_items as $key=>$value)<dt>{{ $key }}</dt><dd>{{ is_scalar($value) ? $value : implode(' / ', array_filter($value, 'is_scalar')) }}</dd>@endforeach</dl>@endif
        <h2>배송 / 교환 / 반품 안내</h2><p>배송비: {{ app(\App\Services\ShopOrderTotals::class)->shippingLabel($product, $shopProduct) }}</p>
        @if($refundContent??'')<div class="shop-detail-copy policy-rich-text">{!! app(\App\Services\RefundPolicyContent::class)->render($refundContent) !!}</div>@endif
    </section>
    @if($jointPurchase)<section id="joint-panel" role="tabpanel" aria-labelledby="joint-tab" class="shop-section" hidden>
        <h2>공동구매 기본정보</h2><dl class="shop-meta"><dt>공동구매명</dt><dd>{{ $jointPurchase->sale_name }}</dd><dt>공동구매코드</dt><dd>{{ $jointPurchase->purchase_code }}</dd><dt>구매기간</dt><dd>{{ $jointPurchase->start_date }} ~ {{ $jointPurchase->end_date }}</dd><dt>현재 신청수량</dt><dd>{{ number_format($jointPurchase->current_quantity) }}개</dd><dt>구매확정수량</dt><dd>{{ number_format($confirmedQuantity) }}개</dd></dl>
        <h2>공동구매 판매혜택</h2><div class="shop-table-scroll"><table class="shop-data-table"><thead><tr><th>공동구매수량</th><th>구매혜택 (상품 1개당)</th></tr></thead><tbody>@foreach(app(\App\Services\JointPurchasePricingService::class)->tiers($jointPurchase->id)->filter(fn($row) => $row->unit_price < $jointPurchase->discount_price) as $tier)<tr><td>{{ number_format($tier->min_quantity) }}개 이상</td><td>{{ number_format(max(0, $jointPurchase->discount_price - $tier->unit_price)) }}원 할인</td></tr>@endforeach</tbody></table></div>
        <h2>공동구매 판매현황</h2><p>구매확정·신청중·배송중 내역만 표시합니다. 구매자 정보는 일부 가려집니다.</p><div class="shop-table-scroll"><table class="shop-data-table"><thead><tr><th>No</th><th>구매자 아이디</th><th>구매자명</th><th>상태</th><th>구매일자</th><th>구매확정일자</th></tr></thead><tbody>@forelse($jointParticipants as $participant)<tr><td>{{ $loop->iteration }}</td><td>{{ $participant['username'] }}</td><td>{{ $participant['name'] }}</td><td>{{ $participant['status'] }}</td><td>{{ $participant['created_at']?->format('Y-m-d H:i') }}</td><td>{{ $participant['confirmed_at']?->format('Y-m-d H:i') ?: '--' }}</td></tr>@empty
@include('components.table-empty-row', ['columns' => 6])@endforelse</tbody></table></div>
    </section>@endif
    <section id="inquiry-panel" role="tabpanel" aria-labelledby="inquiry-tab" class="shop-section" hidden>
        <h2>내 상품 문의</h2>
        <div class="shop-inquiry-invite"><p>판매자에게 상품, 배송, 취소, 교환, 반품 등<br>궁금한 내용을 문의하세요.</p><button type="button" class="shop-btn" data-dialog="product-inquiry-dialog">상품 문의하기</button></div>
        <div class="shop-inquiries">
            @forelse($inquiries ?? collect() as $inquiry)<article class="shop-inquiry"><header><span>{{ $loop->iteration }} · {{ $inquiry->admin_reply ? '답변완료' : '답변대기' }}</span><strong>{{ $inquiry->subject }}</strong><span>{{ mb_substr($inquiry->name,0,1) }}**</span><time>{{ $inquiry->created_at?->format('Y-m-d') }}</time></header><div class="shop-detail-copy policy-rich-text">{!! app(\App\Services\RefundPolicyContent::class)->render($inquiry->message) !!}</div>@if($inquiry->admin_reply)<div class="shop-inquiry-reply shop-detail-copy">{{ $inquiry->admin_reply }}</div>@endif</article>@empty<p class="shop-empty list-empty-state">등록된 자료가 없습니다.</p>@endforelse
        </div>
    </section>
    </div>
    <dialog id="product-inquiry-dialog" class="shop-dialog" aria-labelledby="product-inquiry-title"><div class="shop-dialog-head"><h2 id="product-inquiry-title">상품 문의하기</h2><button type="button" data-close class="shop-dialog-close" aria-label="닫기">×</button></div><div class="shop-dialog-body">
        <div class="shop-inquiry-product"><img src="{{ $imageUrl }}" alt="{{ $product->product_name }}"><div><p>{{ $product->category_path }}</p><h3>{{ $product->product_name }}</h3></div></div>
        <h3 class="shop-inquiry-section-title">문의내용</h3><p>판매자에게 상품, 배송, 취소, 교환, 반품 등 궁금한 내용을 문의하세요.</p><form action="{{ route('front.shop.order.inquiry') }}" method="POST" class="shop-inquiry-form" data-product-inquiry-form>
        @csrf<input type="hidden" name="shop_product_id" value="{{ $shopProduct->id }}"><input type="hidden" name="inquiry_category" value="product">
        <label for="product-inquiry-subject">제목</label><div class="shop-inquiry-title-field"><input id="product-inquiry-subject" name="subject" class="shop-control" maxlength="255" value="{{ old('subject') }}" required></div>
        <label id="product-inquiry-message-label" for="product-inquiry-message">질문</label>
        <div data-product-inquiry-editor>
            <textarea id="product-inquiry-message" name="message" class="shop-control" maxlength="20000" required>{{ old('message') }}</textarea>
            <div data-inquiry-toolbar hidden><span class="ql-formats"><button type="button" class="ql-bold" aria-label="굵게" title="굵게"></button><button type="button" class="ql-italic" aria-label="기울임" title="기울임"></button><button type="button" class="ql-underline" aria-label="밑줄" title="밑줄"></button></span><span class="ql-formats"><button type="button" class="ql-list" value="ordered" aria-label="번호 목록" title="번호 목록"></button><button type="button" class="ql-list" value="bullet" aria-label="글머리 목록" title="글머리 목록"></button><button type="button" class="ql-link" aria-label="링크" title="링크"></button><button type="button" class="ql-clean" aria-label="서식 지우기" title="서식 지우기"></button></span></div>
            <div data-inquiry-editor-body hidden></div><template data-inquiry-editor-initial>{!! app(\App\Services\RefundPolicyContent::class)->editorHtml(old('message','')) !!}</template>
            <p class="shop-inquiry-editor-error" data-inquiry-editor-error role="alert" hidden></p>
        </div>
        <div class="shop-actions"><button type="submit" class="shop-btn primary">문의하기</button><button type="button" class="shop-btn" data-close>창닫기</button></div>
    </form></div></dialog>
    <div class="shop-actions"><a href="{{ route('shop.products_list') }}" class="shop-btn">상품 목록</a></div>
</div></div></div>
@endsection
@push('scripts')<script src="{{ asset('shop/js/product-purchase.js') }}?v={{ filemtime(public_path('shop/js/product-purchase.js')) }}" defer></script><script src="/channel_assets/vendor/quill/quill.min.js"></script><script src="{{ asset('shop/js/product-inquiry.js') }}?v={{ filemtime(public_path('shop/js/product-inquiry.js')) }}" defer></script>@endpush
