@extends('layouts.channel')
@section('page_type', 'sub')
@php
    $dep1_id = '05';
    $roleLabels = ['seller'=>'자사상품 판매자','shared_fixed_supplier'=>'공유상품 제공자(고정가)','shared_fixed_reseller'=>'위탁판매자(고정가)','shared_free_supplier'=>'공유상품 제공자(자유가)','shared_free_reseller'=>'위탁판매자(자유가)'];
    $pgLabels = ['own_pg'=>'자사PG','me9_pg'=>'공용PG'];
@endphp
@push('styles')
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/settlement-workspace.css?v={{ filemtime(public_path('channel_assets/css/settlement-workspace.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace settlement-workspace">
    <div class="order-page-heading"><div><p>{{ $period }}{{ $summary ? ' / '.$summary->shop_channel_name : '' }}</p><h1>정산 상세 내역</h1></div><a class="order-button" href="{{ route('channel.settlement.list',['period'=>$period]) }}">목록으로</a></div>
    @if($summary)
    <section class="order-panel">
        <h2>정산 요약</h2>
        <dl class="settlement-facts">
            <div><dt>Shop 채널</dt><dd>{{ $summary->shop_channel_name }}</dd></div>
            <div><dt>정산방식</dt><dd>{{ (int)$summary->settlement_type===2 ? '판매 개당 금액' : '판매금액 대비 %' }} / {{ number_format($summary->settlement_rate,2) }}{{ (int)$summary->settlement_type===2 ? '원' : '%' }}</dd></div>
            <div><dt>주문건수 / 품목수량</dt><dd>{{ number_format($summary->order_count) }} 건 / {{ number_format($summary->quantity) }} 개</dd></div>
            <div><dt>PG구분</dt><dd>{{ $pgLabels[$summary->payment_gateway_type ?? 'me9_pg'] ?? '공용PG' }}</dd></div>
            <div><dt>매출 / 매입</dt><dd>{{ number_format($summary->invoice_sales_amount ?? $summary->gross_sales_amount) }} 원 / {{ number_format($summary->invoice_purchase_amount ?? 0) }} 원</dd></div>
            <div><dt>포인트 예수금 / 사용</dt><dd>{{ number_format($summary->point_deposit_amount ?? 0) }} P / {{ number_format($summary->point_used_amount ?? 0) }} P</dd></div>
            <div><dt>SMS 후불</dt><dd>{{ number_format($summary->sms_postpaid_amount ?? 0) }} 원</dd></div>
            <div><dt>지급액</dt><dd><strong>{{ number_format($summary->payout_amount ?? $summary->settlement_amount) }} 원</strong></dd></div>
        </dl>
        <p class="settlement-note">지급 기준: @if(($summary->payment_gateway_type ?? 'me9_pg')==='own_pg') 자사PG 결제분은 채널이 직접 수납하므로 지급 대상이 아니며, 포인트 결제분에서 SMS 비용을 차감해 지급합니다. @else 공용PG 수납액에서 수수료·지급포인트·SMS 비용을 차감해 지급합니다. @endif</p>
    </section>
    @endif
    <section class="order-panel">
        <div class="order-toolbar"><h2>주문별 상세</h2><p>총 <strong>{{ $orders->total() }}</strong> 건</p></div>
        <div class="order-table-scroll" tabindex="0"><table class="order-table">
            <thead><tr><th>확정일자</th><th>주문번호</th><th>상품정보</th><th>수량</th><th>역할</th><th>PG구분</th><th>매출</th><th>매입</th><th>포인트 예수금 / 사용</th><th>SMS 후불</th><th>지급액</th></tr></thead>
            <tbody>
            @forelse($orders as $item)
                <tr><td>{{ optional($item->confirmed_at)->format('Y-m-d') }}</td><td>{{ $item->order_no }}</td><td class="order-product-name"><strong>{{ $item->product_name }}</strong><small>{{ $item->product_code }}</small></td><td>{{ $item->quantity }}</td><td>{{ $roleLabels[$item->settlement_role ?? 'seller'] ?? ($item->settlement_role ?? 'seller') }}</td><td>{{ $pgLabels[$item->payment_gateway_type ?? 'me9_pg'] ?? '공용PG' }}</td><td>{{ number_format($item->invoice_sales_amount ?? $item->gross_sales_amount) }} 원</td><td>{{ number_format($item->invoice_purchase_amount ?? 0) }} 원</td><td>{{ number_format($item->point_deposit_amount ?? 0) }} / {{ number_format($item->point_used_amount ?? 0) }} P</td><td>{{ number_format($item->sms_postpaid_amount ?? 0) }} 원</td><td><strong>{{ number_format($item->payout_amount ?? $item->settlement_amount) }} 원</strong>@if(($item->payment_gateway_type ?? 'me9_pg')==='own_pg')<small>자사PG 결제분 제외, 포인트분만 지급</small>@endif</td></tr>
            @empty
                <tr><td colspan="11" class="order-empty">내역이 없습니다.</td></tr>
            @endforelse
            </tbody>
            @if($orders->total()>0)
            <tfoot><tr><th colspan="3">상세 합계</th><td>{{ number_format($totals['quantity']) }}</td><td colspan="2"></td><td>{{ number_format($totals['invoice_sales_amount'] ?? $totals['gross_sales_amount']) }} 원</td><td>{{ number_format($totals['invoice_purchase_amount'] ?? 0) }} 원</td><td>{{ number_format($totals['point_deposit_amount'] ?? 0) }} / {{ number_format($totals['point_used_amount'] ?? 0) }} P</td><td>{{ number_format($totals['sms_postpaid_amount'] ?? 0) }} 원</td><td>{{ number_format($totals['payout_amount'] ?? $totals['settlement_amount']) }} 원</td></tr></tfoot>
            @endif
        </table></div>
        {{ $orders->links() }}
    </section>
</div></div>
@endsection
