@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="order_form">
    <div class="top_v"><h1 class="title">주문서 작성</h1></div>
    <div class="shop-inner">
        @if(empty($cartItems))
            <div class="shop-empty">장바구니가 비어 있습니다.<div class="shop-actions"><a class="shop-btn primary" href="{{ route('shop.products_list') }}">상품 보러가기</a></div></div>
        @else
        <form action="{{ route('front.shop.order.checkout') }}" method="POST" data-checkout-total="{{ $totals['total'] }}">
            @csrf
            <section class="shop-form-section"><h2>주문자 정보</h2><div class="shop-form-grid shop-table-form">
                <label>주문자 이름<input class="shop-control" name="buyer_name" value="{{ old('buyer_name', auth()->user()?->name) }}" required maxlength="100" autocomplete="billing name"></label>
                <label>연락처<input class="shop-control" type="tel" name="buyer_mobile" value="{{ old('buyer_mobile', auth()->user()?->mobile) }}" required maxlength="30" autocomplete="billing tel"></label>
                <label class="wide">이메일<input class="shop-control" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="150" autocomplete="email"></label>
            </div></section>
            <section class="shop-form-section"><div class="shop-order-heading"><h2>배송지 정보</h2><label class="shop-check"><input type="checkbox" data-copy-buyer>주문자 정보와 동일</label></div><div class="shop-form-grid shop-table-form">
                @if($deliveryAddresses->isNotEmpty())
                    <label class="wide">배송지 선택<select class="shop-control" data-delivery-address><option value="">직접 입력</option>@foreach($deliveryAddresses as $deliveryAddress)<option value="{{ $deliveryAddress->id }}" data-address="{{ json_encode($deliveryAddress->only(['name','mobile','pincode','address','city','state'])) }}">{{ $deliveryAddress->is_default ? '기본 배송지: ' : '' }}{{ $deliveryAddress->name }} / {{ $deliveryAddress->address }}</option>@endforeach</select></label>
                @endif
                <label>수신자 이름<input class="shop-control" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="100" autocomplete="shipping name"></label>
                <label>연락처<input class="shop-control" type="tel" name="mobile" value="{{ old('mobile', auth()->user()?->mobile) }}" required maxlength="30" autocomplete="shipping tel"></label>
                <label class="wide">우편번호<div class="shop-postcode"><input class="shop-control" name="pincode" value="{{ old('pincode', auth()->user()?->pincode) }}" required maxlength="20" autocomplete="postal-code"><button class="shop-btn dark" type="button" data-postcode>우편번호 찾기</button></div></label>
                <label class="wide">배송주소<textarea class="shop-control" name="address" required maxlength="255" autocomplete="street-address" placeholder="기본주소와 상세주소">{{ old('address', auth()->user()?->address) }}</textarea></label>
                <label>시/도<input class="shop-control" name="state" value="{{ old('state', auth()->user()?->state) }}" maxlength="100" autocomplete="address-level1"></label>
                <label>시/군/구<input class="shop-control" name="city" value="{{ old('city', auth()->user()?->city) }}" maxlength="100" autocomplete="address-level2"></label>
                <label class="wide">배송 메모<textarea class="shop-control" name="delivery_memo" maxlength="500">{{ old('delivery_memo') }}</textarea></label>
            </div></section>
            <h2 class="shop-block-title">주문 상품 정보</h2>
            <section class="shop-order"><div class="shop-order-heading"><strong>{{ $shop->channel_name }} ({{ $shop->channel_code }})</strong></div>
                @foreach($cartItems as $item)
                    @php($image = $item['product']->product_image ?: $item['product']->images->first()?->image)
                    <div class="shop-item">
                        <img src="{{ $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png') }}" alt="{{ $item['product']->product_name }}">
                        <div><strong>{{ $item['product']->product_name }}</strong><p>{{ $item['option'] }} / {{ $item['qty'] }}개</p><p>배송비: {{ $item['product']->shipping_payment_type === 'collect' ? '착불' : number_format($totals['shipping_by_item'][$item['key']]).'원' }}</p></div>
                        <div class="shop-item-side"><strong>{{ number_format($item['line_total']) }}원</strong></div>
                    </div>
                @endforeach
            </section>
            <h2 class="shop-block-title">주문 결제</h2>
            <div class="shop-payment-grid">
            <section class="shop-form-section"><h2>결제수단</h2>
                <input type="hidden" name="payment_method" value="Card">
                <label class="shop-check"><input type="radio" checked disabled>신용카드</label>
                @auth
                    <div class="shop-form-grid">
                        <label>채널 포인트 ({{ number_format($pointBalances['channel'] ?? 0) }}P)<input class="shop-control" type="number" name="channel_points" data-point-use min="0" max="{{ max(0, min($pointBalances['channel'] ?? 0, floor($totals['total']))) }}" step="1" value="{{ old('channel_points', 0) }}"></label>
                        <label>Me9 포인트 ({{ number_format($pointBalances['me9'] ?? 0) }}P)<input class="shop-control" type="number" name="me9_points" data-point-use min="0" max="{{ max(0, min($pointBalances['me9'] ?? 0, floor($totals['total']))) }}" step="1" value="{{ old('me9_points', 0) }}"></label>
                    </div>
                @endauth
                <div class="shop-order-consent"><label class="shop-check"><input type="checkbox" name="order_confirmed" value="1" required @checked(old('order_confirmed'))>주문 상품과 최종 결제금액을 확인했습니다. (필수)</label></div>
            </section>
            <section class="shop-form-section">
                <div class="shop-summary"><div><span>상품금액</span><strong>{{ number_format($totals['subtotal']) }}원</strong></div><div><span>배송비</span><strong>{{ number_format($totals['shipping']) }}원</strong></div><div><span>사용 포인트</span><strong data-used-points>0P</strong></div><div class="total"><span>최종 결제금액</span><strong data-checkout-payable>{{ number_format($totals['total']) }}원</strong></div></div>
                @unless($canCheckout)<p class="shop-payment-unavailable" role="status">현재 결제를 이용할 수 없습니다. 판매자에게 문의해 주세요.</p>@endunless
                <div class="shop-actions"><a class="shop-btn dark" href="{{ route('front.shop.cart.index') }}">취소</a><button class="shop-btn primary" type="submit" @disabled(!$canCheckout)>주문하기</button></div>
            </section></div>
        </form>
        <dialog id="postcode-dialog" class="shop-dialog" aria-labelledby="postcode-title"><div class="shop-dialog-head"><h2 id="postcode-title">우편번호 찾기</h2><button type="button" data-close class="shop-dialog-close" aria-label="닫기" title="닫기">&times;</button></div><div data-postcode-container style="min-height:420px;"><p class="shop-empty" role="status">주소 검색을 불러오는 중입니다.</p></div></dialog>
        @endif
    </div>
</div></div>
@endsection
