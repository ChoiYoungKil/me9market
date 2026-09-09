<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><title>주문 접수</title></head>
<body style="font-family:Arial,sans-serif;color:#111827;">
<table style="width:100%;max-width:700px;margin:0 auto;border-collapse:collapse;">
    <tr><td style="padding:24px 0;font-size:24px;font-weight:700;">{{ $shop->channel_name }} ({{ $shop->channel_code }})</td></tr>
    <tr><td style="padding-bottom:16px;">{{ $order->name }}님의 주문이 접수되었습니다.</td></tr>
    <tr><td style="padding-bottom:16px;"><strong>주문번호</strong> Me9-Shop-{{ str_pad($order->id, 7, '0', STR_PAD_LEFT) }}</td></tr>
    <tr><td>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#f3f4f6;"><th style="padding:10px;text-align:left;">상품</th><th>옵션</th><th>수량</th><th>금액</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:10px;">{{ $item->product_name }}</td>
                    <td style="text-align:center;">{{ $item->product_size }}</td>
                    <td style="text-align:center;">{{ number_format($item->product_qty) }}</td>
                    <td style="text-align:right;">{{ number_format($item->line_total) }}원</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </td></tr>
    <tr><td style="padding-top:18px;text-align:right;font-size:18px;"><strong>결제금액 {{ number_format($order->grand_total) }}원</strong></td></tr>
</table>
</body>
</html>
