@if($claim)
    <tr><th>신청일</th><td>{{ $claim->created_at->format('Y.m.d H:i') }}</td></tr>
    @if($claim->pickup_method)
        <tr><th>회수 방법</th><td>{{ ['manual'=>'수동회수', 'automatic'=>'자동회수', 'auto'=>'자동회수'][$claim->pickup_method] ?? $claim->pickup_method }}</td></tr>
    @endif
    @if($claim->return_address)<tr><th>상품 회수 주소</th><td>{{ $claim->return_address }}</td></tr>@endif
    @if($claim->customer_courier_name || $claim->customer_tracking_number)
        <tr><th>회수 택배 정보</th><td>{{ $claim->customer_courier_name }} {{ $claim->customer_tracking_number }}</td></tr>
    @endif
@endif
@if($item->financial_reversed_at)
    <tr><th>환불 처리</th><td>{{ $item->refund_status_label }} · {{ number_format($item->refund_cash_amount) }} 원</td></tr>
@endif
