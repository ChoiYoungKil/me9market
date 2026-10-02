@extends('shop.monitor.layout')
@section('content')
<h1>{{ $shop->channel_name }} 주문 현황</h1>
<form method="GET"><label>조회월 <select name="period">@foreach($periods as $value => $label)<option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>@endforeach</select></label> <button>조회</button></form>
<div class="metrics">@foreach(['paid','ready_to_ship','shipping','delivered','confirmed','cancelled','returned'] as $status)<div>{{ \App\Support\OrderItemStatus::label($status) }}<strong>{{ number_format($counts[$status] ?? 0) }}</strong></div>@endforeach</div>
<div class="table-scroll"><table><thead><tr><th>주문일</th><th>주문번호</th><th>상품</th><th>옵션</th><th>수량</th><th>상품금액</th><th>상태</th></tr></thead><tbody>
@forelse($items as $item)<tr><td>{{ $item->created_at?->format('Y-m-d') }}</td><td>Me9-{{ $item->order_id }}</td><td>{{ $item->product_name }}</td><td>{{ $item->product_size }}</td><td>{{ number_format($item->product_qty) }}</td><td>{{ number_format($item->paid_line_total_snapshot ?? $item->line_total) }}원</td><td>{{ $item->status_label }}</td></tr>@empty<tr><td colspan="7">주문 내역이 없습니다.</td></tr>@endforelse
</tbody></table></div>
<nav class="pagination" aria-label="페이지 이동">@if($items->previousPageUrl())<a href="{{ $items->previousPageUrl() }}">이전</a>@endif<span>{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>@if($items->nextPageUrl())<a href="{{ $items->nextPageUrl() }}">다음</a>@endif</nav>
@endsection
