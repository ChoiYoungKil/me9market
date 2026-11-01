
@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>주문 관리</h1>
@include('admin.shared.messages')
<section class="admin-panel"><form method="get" action="{{ route('admin.orders') }}" class="admin-filter"><label>검색어<input type="search" name="keyword" maxlength="100" value="{{ request('keyword') }}" placeholder="주문번호·주문자·상품·송장"></label><label>상품 상태<select name="item_status"><option value="">전체</option>
@foreach(\App\Support\OrderItemStatus::labels() as $code=>$label)<option value="{{ $code }}"
@selected(request('item_status')===$code)>{{ $label }}</option>
@endforeach</select></label><label>시작일<input type="date" name="from" value="{{ request('from') }}"></label><label>종료일<input type="date" name="to" value="{{ request('to') }}"></label><label>목록 개수<select name="per_page">
@foreach([10,20,50,100] as $count)<option value="{{ $count }}"
@selected(request('per_page',20)==$count)>{{ $count }}개</option>
@endforeach</select></label><button class="admin-btn primary">검색</button><a class="admin-btn" href="{{ route('admin.orders') }}">초기화</a></form></section>
<div class="admin-toolbar"><p>총 <strong>{{ $orders->total() }}</strong>건의 주문</p><a class="admin-btn" href="{{ request()->fullUrlWithQuery(['export'=>1,'page'=>null]) }}">엑셀 다운로드</a></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>주문번호</th><th>주문일</th><th>주문자</th><th>상품 / 수량</th><th>상품 금액</th><th>상품별 상태</th><th>관리</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td class="nowrap">Me9-Shop-{{ str_pad($order->id,7,'0',STR_PAD_LEFT) }}</td><td>{{ $order->created_at?->format('Y-m-d H:i') }}</td><td>{{ $order->buyer_name ?: $order->name }}</td><td class="text-left">
@foreach($order->orders_products as $item)<p>{{ $item->product_name }} / {{ $item->product_qty }}개</p>
@endforeach</td><td>{{ number_format($order->orders_products->sum('line_total')) }}원</td><td>
@foreach($order->orders_products->pluck('status_label')->unique() as $label)<span class="admin-status">{{ $label }}</span>
@endforeach</td><td class="nowrap"><a class="admin-btn small primary" href="{{ url('admin/orders/'.$order->id) }}">상세 / 처리</a><a class="admin-btn small" href="{{ url('admin/orders/invoice/'.$order->id) }}" target="_blank" rel="noopener">거래명세서</a></td></tr>
@empty<tr><td colspan="7" class="admin-empty">검색된 주문이 없습니다.</td></tr>
@endforelse</tbody></table></div><div class="admin-pagination">{{ $orders->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div></div>
@endsection
