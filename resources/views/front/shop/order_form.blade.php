@extends('layouts.frontend')

@section('content')
<div id="contents" style="padding: 100px 0; min-height: 600px; background:#f6f7f9;">
    <form action="{{ route('front.shop.order.checkout') }}" method="POST" style="max-width:1180px; margin:0 auto; display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:20px;">
        @csrf
        <div style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:28px;">
            <h1 style="margin:0 0 8px;">주문/결제</h1>
            <p style="margin:0 0 24px; color:#667085;">{{ $shop->channel_name }} 상품 주문서입니다.</p>

            @if ($errors->any())
                <div style="background:#fee4e2; color:#b42318; padding:12px; border-radius:6px; margin-bottom:16px;">{{ $errors->first() }}</div>
            @endif

            <h2 style="font-size:18px;">주문 상품</h2>
            @forelse($cartItems as $item)
                <div style="display:flex; justify-content:space-between; gap:12px; border-bottom:1px solid #eef1f5; padding:12px 0;">
                    <div>
                        <strong>{{ $item['product']->product_name }}</strong>
                        <div style="color:#667085; font-size:13px;">{{ $item['option'] }} / {{ $item['qty'] }}개</div>
                    </div>
                    <strong>{{ number_format($item['line_total']) }}원</strong>
                </div>
            @empty
                <div style="padding:20px; background:#f8fafc; border-radius:6px;">장바구니가 비어 있습니다.</div>
            @endforelse

            <h2 style="font-size:18px; margin-top:28px;">주문자/배송 정보</h2>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <label>이름<input name="name" value="{{ old('name', Auth::user()->name ?? '홍길동') }}" required style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
                <label>연락처<input name="mobile" value="{{ old('mobile', Auth::user()->mobile ?? '010-1234-5678') }}" required style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
                <label>이메일<input name="email" value="{{ old('email', Auth::user()->email ?? 'guest@me9.local') }}" required style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
                <label>우편번호<input name="pincode" value="{{ old('pincode', '04524') }}" required style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
                <label style="grid-column:1 / -1;">주소<input name="address" value="{{ old('address', '서울특별시 중구 세종대로 110') }}" required style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
                <label>시/도<input name="city" value="{{ old('city', '서울특별시') }}" style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
                <label>구/군<input name="state" value="{{ old('state', '중구') }}" style="width:100%; height:42px; border:1px solid #cfd4dc; border-radius:6px; padding:0 10px;"></label>
            </div>
        </div>

        <aside style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:24px; align-self:start;">
            <h2 style="font-size:18px; margin-top:0;">결제 상세</h2>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><span>상품금액</span><strong>{{ number_format($totals['subtotal']) }}원</strong></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><span>배송비</span><strong>{{ number_format($totals['shipping']) }}원</strong></div>
            <div style="display:flex; justify-content:space-between; border-top:1px solid #d9dee7; padding-top:12px; font-size:20px;"><span>최종 결제</span><strong>{{ number_format($totals['total']) }}원</strong></div>
            <input type="hidden" name="payment_method" value="Card">
            <button type="submit" style="width:100%; height:52px; margin-top:22px; border:0; border-radius:6px; background:#111827; color:#fff; font-weight:900; cursor:pointer;" {{ empty($cartItems) ? 'disabled' : '' }}>
                {{ number_format($totals['total']) }}원 결제하기
            </button>
        </aside>
    </form>
</div>
@endsection
