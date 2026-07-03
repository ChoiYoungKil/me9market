@extends('layouts.frontend')

@section('content')
<div id="contents" style="padding: 100px 0; min-height: 600px; background:#f6f7f9;">
    <div style="max-width: 1180px; margin: 0 auto; background: #fff; border:1px solid #d9dee7; border-radius:8px; padding: 28px;">
        <h1 style="margin:0 0 8px;">장바구니</h1>
        <p style="margin:0 0 24px; color:#667085;">{{ $shop->channel_name }}에서 담은 상품입니다.</p>

        @if(session('flash_message_success'))
            <div style="background:#dcfae6; color:#087443; padding:12px 16px; border-radius:6px; margin-bottom:16px;">{{ session('flash_message_success') }}</div>
        @endif

        @if(empty($cartItems))
            <div style="padding:40px; text-align:center; border:1px dashed #d9dee7; border-radius:8px;">
                장바구니가 비어 있습니다.
                <div style="margin-top:18px;"><a href="{{ route('shop.products_list') }}" style="font-weight:900;">상품 보러가기</a></div>
            </div>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px; text-align:left;">상품</th>
                        <th style="padding:12px;">옵션</th>
                        <th style="padding:12px;">수량</th>
                        <th style="padding:12px;">금액</th>
                        <th style="padding:12px;">삭제</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                        <tr style="border-bottom:1px solid #eef1f5;">
                            <td style="padding:12px;">
                                <strong>{{ $item['product']->product_name }}</strong>
                                <div style="color:#667085; font-size:13px;">{{ $item['product']->product_code }}</div>
                            </td>
                            <td style="padding:12px; text-align:center;">{{ $item['option'] }}</td>
                            <td style="padding:12px; text-align:center;">{{ number_format($item['qty']) }}</td>
                            <td style="padding:12px; text-align:right; font-weight:900;">{{ number_format($item['line_total']) }}원</td>
                            <td style="padding:12px; text-align:center;">
                                <form action="{{ route('front.shop.cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="shop_product_id" value="{{ $item['id'] }}">
                                    <button type="submit" style="border:1px solid #d9dee7; background:#fff; border-radius:6px; padding:6px 10px; cursor:pointer;">삭제</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:24px; display:grid; justify-content:end;">
                <div style="min-width:320px; background:#f8fafc; border-radius:8px; padding:18px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><span>상품금액</span><strong>{{ number_format($totals['subtotal']) }}원</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><span>배송비</span><strong>{{ number_format($totals['shipping']) }}원</strong></div>
                    <div style="display:flex; justify-content:space-between; border-top:1px solid #d9dee7; padding-top:12px; font-size:20px;"><span>결제예정</span><strong>{{ number_format($totals['total']) }}원</strong></div>
                </div>
            </div>

            <div style="display:flex; justify-content:center; gap:10px; margin-top:28px;">
                <a href="{{ route('shop.products_list') }}" style="height:48px; display:inline-flex; align-items:center; padding:0 22px; border:1px solid #111827; border-radius:6px; color:#111827; text-decoration:none; font-weight:900;">쇼핑 계속하기</a>
                <a href="{{ route('front.shop.order.form') }}" style="height:48px; display:inline-flex; align-items:center; padding:0 22px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; font-weight:900;">주문하기</a>
            </div>
        @endif
    </div>
</div>
@endsection
