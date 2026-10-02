<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Me9 주문명세서 {{ $order->id }}</title>
<style>
@font-face{font-family:Me9Invoice;src:url("{{ $pdf ? 'file://'.public_path('master_assets/css/font/NanumGothic-Regular.ttf') : asset('master_assets/css/font/NanumGothic-Regular.ttf') }}") format("truetype");font-weight:normal}
@font-face{font-family:Me9Invoice;src:url("{{ $pdf ? 'file://'.public_path('mypage_assets/css/font/NanumGothic-Bold.ttf') : asset('mypage_assets/css/font/NanumGothic-Bold.ttf') }}") format("truetype");font-weight:bold}
@page{margin:30px}
body{font-family:Me9Invoice,sans-serif;color:#222;font-size:12px;line-height:1.6;margin:0;padding:24px}
h1{font-size:24px;margin:18px 0}h2{font-size:15px;margin:20px 0 10px}.brand{font-size:18px;color:#087f76}
table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{border-bottom:1px solid #ddd;padding:10px 6px;text-align:left;word-wrap:break-word;vertical-align:top}th{background:#f2f5f4}
.number{text-align:right;white-space:nowrap}.totals{margin-top:24px;width:100%}.totals td:first-child{width:70%}.total{font-size:16px}.meta{color:#555}.address{margin:0 0 16px;word-wrap:break-word}thead{display:table-header-group}tr{page-break-inside:avoid}
@media(max-width:600px){body{padding:16px;font-size:11px}th,td{padding:8px 4px}.number{white-space:normal}h1{font-size:22px}}
</style>
</head>
<body>
<div class="brand">Me9 market</div>
<h1>주문명세서</h1>
<div class="meta">주문번호 Me9-{{ str_pad((string) $order->id, 8, '0', STR_PAD_LEFT) }} · {{ $order->created_at?->format('Y-m-d H:i') }}</div>
<h2>주문자</h2>
<p class="address">{{ $order->buyer_name ?: $order->name }}<br>{{ $order->buyer_mobile ?: $order->mobile }}<br>{{ $order->email }}</p>
<h2>배송정보</h2>
<p class="address">{{ $order->name }} · {{ $order->mobile }}<br>({{ $order->pincode }}) {{ $order->address }} {{ $order->city }} {{ $order->state }}<br>{{ $order->delivery_memo }}</p>
<table>
<thead><tr><th style="width:42%">상품 / 옵션</th><th style="width:10%">수량</th><th style="width:23%">상품금액</th><th style="width:25%">상태</th></tr></thead>
<tbody>
@foreach($items as $row)
<tr><td>{{ $row['item']->product_name }}<br>{{ $row['item']->product_code }} · {{ $row['item']->product_size }}</td><td class="number">{{ number_format($row['item']->product_qty) }}</td><td class="number">{{ number_format($row['amounts']['paid_line_total']) }}원</td><td>{{ $row['item']->status_label }}</td></tr>
@endforeach
</tbody>
</table>
<table class="totals"><tbody>
<tr><td>상품 합계</td><td class="number">{{ number_format($totals['paid_line_total']) }}원</td></tr>
<tr><td>배송비</td><td class="number">{{ number_format($totals['shipping']) }}원</td></tr>
<tr><td>쿠폰 할인</td><td class="number">{{ number_format($totals['coupon']) }}원</td></tr>
<tr><td>사용 포인트</td><td class="number">{{ number_format($totals['points']) }}P</td></tr>
<tr class="total"><td>주문 시 결제금액</td><td class="number">{{ number_format($totals['cash']) }}원</td></tr>
</tbody></table>
<p class="meta">결제수단: {{ $order->payment_method }} · {{ $order->payment_gateway }}</p>
</body>
</html>
