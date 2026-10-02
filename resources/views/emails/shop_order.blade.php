<!DOCTYPE html>
<html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>주문 접수</title></head>
<body style="margin:0; padding:0; background:#fafafa; font-family:Arial,sans-serif; color:#333;">
<table role="presentation" style="width:100%; max-width:720px; margin:40px auto; border-collapse:collapse; background:#fff;"><tr><td style="padding:32px 24px;">
    <table role="presentation" style="width:100%; margin-bottom:40px;"><tr><td><h1 style="font-size:22px; line-height:1.5; margin:0;">{{ $shop->channel_name }}에서 주문하신 내역입니다.</h1></td><td style="width:120px; padding-left:16px;"><img src="{{ asset($shop->use_logo && $shop->logo_image ? $shop->logo_image : 'shop/images/common/logo.png') }}" alt="{{ $shop->channel_name }}" style="display:block; width:100%; max-width:120px; height:auto;"></td></tr></table>
    <table style="width:100%; border-top:1px solid #111; border-collapse:collapse; text-align:left; font-size:14px;">
        <tr><th style="padding:12px 0; width:100px;">고객명</th><td>{{ $order->buyer_name ?: $order->name }}</td></tr>
        <tr><th style="padding:12px 0;">주문번호</th><td>Me9-Shop-{{ str_pad($order->id, 7, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><th style="padding:12px 0;">주문일시</th><td>{{ $order->created_at?->format('Y-m-d H:i') }}</td></tr>
    </table>
    <h2 style="font-size:18px; margin-top:28px;">배송정보</h2>
    <table style="width:100%; border-top:1px solid #111; border-collapse:collapse; text-align:left; font-size:14px;">
        <tr><th style="padding:12px 0; width:100px;">수신자명</th><td>{{ $order->name }}</td></tr>
        <tr><th style="padding:12px 0;">휴대전화</th><td>{{ $order->mobile }}</td></tr>
        <tr><th style="padding:12px 0;">이메일</th><td style="word-break:break-all;">{{ $order->email }}</td></tr>
        <tr><th style="padding:12px 0;">배송지주소</th><td>{{ $order->pincode }} {{ $order->address }} {{ $order->city }} {{ $order->state }}</td></tr>
        @if($order->delivery_memo)<tr><th style="padding:12px 0;">배송 메모</th><td>{{ $order->delivery_memo }}</td></tr>@endif
    </table>
    <h2 style="font-size:18px; margin-top:28px;">주문상품</h2>
    <p style="background:#fafafa; padding:16px; font-weight:bold;">{{ $shop->channel_name }} ({{ $shop->channel_code }})</p>
    <table style="width:100%; border-collapse:collapse; font-size:14px;">
        @foreach($items as $item)
            @php($image = $item->product?->product_image)
            <tr><td style="width:64px; padding:16px 12px 16px 0; border-bottom:1px solid #eee;"><img src="{{ $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png') }}" alt="" width="64" style="display:block; width:64px; height:auto;"></td><td style="padding:16px 0; border-bottom:1px solid #eee; overflow-wrap:anywhere;"><strong>{{ $item->product_name }}</strong><br><span style="color:#777; line-height:2;">{{ $item->product_size ?: '기본옵션' }} / {{ $item->product_qty }}개</span></td><td style="padding:16px 0 16px 12px; text-align:right; border-bottom:1px solid #eee; white-space:nowrap;">{{ number_format($item->line_total ?? $item->product_price * $item->product_qty) }}원</td></tr>
        @endforeach
    </table>
    <table style="width:100%; border-top:1px solid #111; border-collapse:collapse; text-align:left; margin-top:24px; font-size:14px;">
        <tr><th style="padding:16px 0;">상품금액</th><td style="text-align:right;">{{ number_format($items->sum(fn ($item) => $item->line_total ?? $item->product_price * $item->product_qty)) }}원</td></tr>
        <tr><th style="padding:16px 0;">배송비</th><td style="text-align:right;">{{ number_format($order->shipping_charges) }}원</td></tr>
        @if($order->used_point)<tr><th style="padding:16px 0;">사용 포인트</th><td style="text-align:right;">{{ number_format($order->used_point) }}P</td></tr>@endif
        <tr><th style="padding:16px 0;">결제수단</th><td style="text-align:right;">{{ $order->payment_method }}</td></tr>
        <tr style="color:#3470f7; border-top:1px solid #eee;"><th style="padding:24px 0;">최종 주문금액</th><td style="text-align:right; font-size:22px; font-weight:bold;">{{ number_format($order->grand_total) }}원</td></tr>
    </table>
    <p style="margin:30px 0 0; text-align:center;"><a href="{{ route('shop.enter', $shop->channel_code) }}" style="display:inline-block; padding:18px 32px; background:#3470f7; border-radius:30px; color:#fff; font-weight:bold; text-decoration:none;">Shop 채널 바로가기</a></p>
</td></tr></table>
</body></html>
