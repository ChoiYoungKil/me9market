@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="order_form">
    <div class="top_v"><h1 class="title">주문서 작성</h1></div>
    <div class="shop-inner">
        @if(empty($cartItems))
            <div class="shop-empty">장바구니가 비어 있습니다.<div class="shop-actions"><a class="shop-btn primary" href="{{ route('shop.products_list') }}">상품 보러가기</a></div></div>
        @else
        <form action="{{ route('front.shop.order.checkout') }}" method="POST" data-shipping-quote="{{ route('front.shop.order.shipping_quote') }}" data-checkout-total="{{ $totals['total'] }}" @if($canCheckout) data-mock-checkout @endif>
            @csrf
            @if($canCheckout)<p class="shop-message" role="status">로컬 검수용 모의결제입니다. 실제 금액이 청구되거나 상품이 배송되지 않습니다.</p>@endif
            <input type="hidden" name="expected_total" value="{{ $totals['total'] }}">
            <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
            <p data-shipping-message role="status">배송주소를 입력하면 지역 배송비가 반영됩니다.</p>
            <section class="shop-form-section"><h2>주문자 정보</h2><div class="shop-form-grid shop-table-form">
                @auth<label>주문자 ID<input class="shop-control shop-order-identity" value="{{ auth()->user()->username }}" readonly></label><label>회원번호<input class="shop-control shop-order-identity" value="{{ auth()->user()->member_number ?: '-' }}" readonly></label>@endauth
                <label>주문자 이름<input class="shop-control" name="buyer_name" value="{{ old('buyer_name', auth()->user()?->name) }}" required maxlength="100" autocomplete="billing name"></label>
                <label>연락처<input class="shop-control" type="tel" name="buyer_mobile" value="{{ auth()->check() ? auth()->user()->mobile : old('buyer_mobile') }}" @readonly(auth()->check()) required maxlength="30" autocomplete="billing tel"></label>
                <label class="wide">이메일<input class="shop-control" type="email" name="email" value="{{ auth()->check() ? auth()->user()->email : old('email') }}" @readonly(auth()->check()) required maxlength="150" autocomplete="email"></label>
            </div></section>
            <section class="shop-form-section"><div class="shop-order-heading"><h2>배송지 정보</h2><label class="shop-check"><input type="checkbox" data-copy-buyer>주문자 정보와 동일</label></div><div class="shop-form-grid shop-table-form">
                <label>수신자 이름<input class="shop-control" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="100" autocomplete="shipping name"></label>
                @php
                    $recipientPhone = preg_replace('/\D/', '', old('mobile', auth()->user()?->mobile ?: ''));
                    $recipientEmail = old('recipient_email', auth()->user()?->email ?: '');
                    $emailParts = explode('@', $recipientEmail, 2);
                @endphp
                <label>휴대전화<div class="shop-phone-fields" data-recipient-phone><select class="shop-control" name="recipient_phone_prefix" aria-label="휴대전화 앞자리">@foreach(['010','011','016','017','018','019'] as $prefix)<option @selected(old('recipient_phone_prefix', substr($recipientPhone,0,3))===$prefix)>{{ $prefix }}</option>@endforeach</select><span>−</span><input class="shop-control" name="recipient_phone_middle" aria-label="휴대전화 가운데자리" value="{{ old('recipient_phone_middle',substr($recipientPhone,3,-4)) }}" inputmode="numeric" pattern="[0-9]{3,4}" maxlength="4" required><span>−</span><input class="shop-control" name="recipient_phone_last" aria-label="휴대전화 끝자리" value="{{ old('recipient_phone_last',substr($recipientPhone,-4)) }}" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required><input type="hidden" name="mobile" value="{{ old('mobile',auth()->user()?->mobile) }}"></div></label>
                <label class="wide">이메일<div class="shop-email-fields" data-recipient-email><input class="shop-control" name="recipient_email_local" aria-label="수신자 이메일 아이디" value="{{ old('recipient_email_local',$emailParts[0]) }}" maxlength="64" required><span>@</span><input class="shop-control" name="recipient_email_domain" aria-label="수신자 이메일 도메인" value="{{ old('recipient_email_domain',$emailParts[1] ?? '') }}" maxlength="85" required><select class="shop-control" aria-label="이메일 도메인 선택" data-email-domain><option value="">직접입력</option>@foreach(['naver.com','gmail.com','daum.net','hanmail.net','nate.com'] as $domain)<option value="{{ $domain }}">{{ $domain }}</option>@endforeach</select></div></label>
                @if($deliveryAddresses->isNotEmpty())
                    <label class="wide">배송지 선택<select class="shop-control" data-delivery-address><option value="">직접 입력</option>@foreach($deliveryAddresses as $deliveryAddress)<option value="{{ $deliveryAddress->id }}" data-address="{{ json_encode($deliveryAddress->only(['name','mobile','pincode','address','city','state'])) }}">{{ $deliveryAddress->is_default ? '기본 배송지: ' : '' }}{{ $deliveryAddress->name }} / {{ $deliveryAddress->address }}</option>@endforeach</select></label>
                @endif
                <label class="wide">우편번호<div class="shop-postcode"><input class="shop-control" name="pincode" value="{{ old('pincode', auth()->user()?->pincode) }}" required maxlength="20" autocomplete="postal-code"><button class="shop-btn dark" type="button" data-postcode>우편번호 찾기</button></div></label>
                <label class="wide">배송주소<textarea class="shop-control" name="address" required maxlength="255" autocomplete="street-address" placeholder="기본주소와 상세주소">{{ old('address', auth()->user()?->address) }}</textarea></label>
                <label class="wide">상세주소<input class="shop-control" name="address_detail" value="{{ old('address_detail') }}" maxlength="150" autocomplete="address-line2"></label>
                <input type="hidden" name="state" value="{{ old('state', auth()->user()?->state) }}">
                <input type="hidden" name="city" value="{{ old('city', auth()->user()?->city) }}">
                <label class="wide">배송 메모<textarea class="shop-control" name="delivery_memo" maxlength="500">{{ old('delivery_memo') }}</textarea><small>택배기사님께 전달할 말씀을 남겨주세요.</small></label>
            </div></section>
            <h2 class="shop-block-title" id="shop-order-products">주문 상품 정보</h2>
            @foreach(collect($cartItems)->groupBy(fn($item)=>$item['product']->vendor_id) as $sellerItems)
            <section class="shop-order"><div class="shop-order-heading"><strong>판매자: {{ $sellerItems->first()['product']->vendor?->name ?: $shop->channel_name }}</strong></div>
                @foreach($sellerItems->groupBy('id') as $rows)
                @foreach($rows as $item)
                    @php($image = $item['product']->product_image ?: $item['product']->images->first()?->image)
                    <div class="shop-item">
                        <img src="{{ $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png') }}" alt="{{ $item['product']->product_name }}">
                        <div><strong>{{ $item['product']->product_name }}</strong><p>{{ $item['option'] }} / {{ $item['qty'] }}개</p></div>
                        <div class="shop-item-side"><strong>{{ number_format($item['line_total']) }}원</strong><button type="submit" class="shop-btn small" name="cart_key" value="{{ $item['key'] }}" formaction="{{ route('front.shop.order.remove_item') }}" formnovalidate>삭제</button></div>
                    </div>
                @endforeach
                @include('front.shop.partials.shipping_equation')
                @endforeach
            </section>
            @endforeach
            <h2 class="shop-block-title">주문 결제 금액</h2>
            @include('front.shop.partials.amount_equation', ['live'=>true])
            @php($availablePoints = $shop->use_own_pg ? 0 : max(0,($pointBalances['channel'] ?? 0) + ($pointBalances['me9'] ?? 0)))
            <section class="shop-checkout-points">
                <h2 class="shop-block-title">포인트 사용 <small>(사용가능 보유 포인트: {{ number_format($availablePoints) }}P)</small></h2>
                <div class="shop-summary shop-cart-summary shop-point-equation" aria-label="포인트 차감 후 최종 결제금액">
                    <div><span>총 결제금액</span><strong data-gross-total>{{ number_format($totals['total']) }}원</strong></div><span class="shop-amount-operator" aria-hidden="true">−</span>
                    <div><label for="shop-use-points">사용포인트</label><div class="shop-point-input"><input id="shop-use-points" class="shop-control" name="point_use" type="number" data-point-use min="0" max="{{ min($availablePoints, floor($totals['total'])) }}" data-point-balance="{{ $availablePoints }}" step="1" value="{{ old('point_use',0) }}" @readonly(!$availablePoints)><span>Point</span></div></div><span class="shop-amount-operator" aria-hidden="true">=</span>
                    <div class="total"><span>최종 결제금액</span><strong data-checkout-payable>{{ number_format($totals['total']) }}원</strong></div>
                </div>
                @auth<p class="shop-point-help">채널 포인트 {{ number_format($pointBalances['channel'] ?? 0) }}P / Me9 포인트 {{ number_format($pointBalances['me9'] ?? 0) }}P · 채널 포인트부터 사용합니다.@if($shop->use_own_pg) 자사 PG 채널은 포인트를 사용할 수 없습니다.@endif</p>@else<p class="shop-point-help">포인트는 회원 로그인 후 사용할 수 있습니다.</p>@endauth
            </section>
            <section class="shop-form-section shop-checkout-payment" aria-label="결제금액 및 결제수단">
                <div class="shop-payment-row"><h2>결제금액</h2><strong data-checkout-payable>{{ number_format($totals['total']) }}원</strong></div>
                <div class="shop-payment-row"><h2>결제수단</h2><div class="shop-payment-methods"><label class="shop-check"><input type="radio" name="payment_method" value="Card" required @checked(old('payment_method','Card')==='Card')>신용카드</label><label class="shop-check"><input type="radio" name="payment_method" value="BankTransfer" @checked(old('payment_method')==='BankTransfer')>실시간계좌이체</label></div></div>
            </section>
            @include('front.shop.partials.checkout_privacy')
            @unless($canCheckout)<p class="shop-payment-unavailable" role="status">현재 결제를 이용할 수 없습니다. 판매자에게 문의해 주세요.</p>@endunless
            <div class="shop-actions shop-checkout-actions"><a class="shop-btn dark" href="{{ route('front.shop.cart.index') }}">취소</a><button class="shop-btn primary" type="submit" data-checkout-submit @disabled(!$canCheckout)>결제하기</button></div>
        </form>
        @if($canCheckout)<dialog id="shop-mock-payment" class="shop-dialog" aria-labelledby="shop-mock-payment-title"><div class="shop-dialog-head"><h2 id="shop-mock-payment-title">검수용 모의결제</h2><button type="button" class="shop-dialog-close" data-close aria-label="닫기">×</button></div><div class="shop-dialog-body"><p>실제 PG 승인이나 금액 청구 없이 주문 저장을 검수합니다.</p><dl class="shop-meta"><dt>결제수단</dt><dd data-mock-method></dd><dt>최종 결제금액</dt><dd data-mock-amount></dd></dl><div class="shop-actions"><button type="button" class="shop-btn" data-close>결제 취소</button><button type="button" class="shop-btn primary" data-mock-confirm>모의결제 승인</button></div></div></dialog>@endif
        <dialog id="postcode-dialog" class="shop-dialog" aria-labelledby="postcode-title"><div class="shop-dialog-head"><h2 id="postcode-title">우편번호 찾기</h2><button type="button" data-close class="shop-dialog-close" aria-label="닫기" title="닫기">&times;</button></div><div data-postcode-container style="min-height:420px;"><p class="shop-empty" role="status">주소 검색을 불러오는 중입니다.</p></div></dialog>
        @endif
    </div>
</div></div>
@endsection
