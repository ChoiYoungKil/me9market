
@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><div class="admin-toolbar"><h1>주문 상세 · Me9-Shop-{{ str_pad($order->id,7,'0',STR_PAD_LEFT) }}</h1><div class="admin-actions"><a class="admin-btn" href="{{ route('admin.orders') }}">목록</a><a class="admin-btn" href="{{ url('admin/orders/invoice/'.$order->id) }}" target="_blank" rel="noopener">거래명세서</a><a class="admin-btn" href="{{ url('admin/orders/invoice/pdf/'.$order->id) }}" target="_blank" rel="noopener">PDF 다운로드</a></div></div>
@include('admin.shared.messages')
<nav class="admin-tabs" role="tablist" aria-label="주문 상세 구분">
@foreach(['info'=>'주문·결제 정보','products'=>'상품·배송','claims'=>'취소·반품·교환','history'=>'처리 이력'] as $code=>$label)<a href="{{ route('admin.order.workspace.panel',[$order->id,$code]) }}" role="tab" aria-selected="{{ $tab===$code?'true':'false' }}" class="{{ $tab===$code?'active':'' }}" data-panel-url="{{ route('admin.order.workspace.panel',[$order->id,$code]) }}" data-panel-target="order-panel">{{ $label }}</a>
@endforeach</nav>
<div id="order-panel" role="tabpanel">
@include('admin.workspace.order_panel')</div>
</div>
@endsection
