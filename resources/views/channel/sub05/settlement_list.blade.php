@extends('layouts.channel')
@section('page_type','sub')
@push('styles')<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}"><link rel="stylesheet" href="/channel_assets/css/settlement-workspace.css?v={{ filemtime(public_path('channel_assets/css/settlement-workspace.css')) }}">@endpush
@section('content')
<div id="contents"><div class="order-workspace settlement-workspace">
<div class="order-page-heading"><div><p>정산관리</p><h1>정산 자료</h1></div></div>
<section class="order-panel">
<form id="settlement-search" method="GET" action="{{ route('channel.settlement.list') }}" class="order-search-row">
<label class="grow">정산 기간<select name="period" required><option value="">기간을 선택하여 주세요</option>@foreach($periodOptions as $key=>$label)<option value="{{ $key }}" @selected($hasPeriod && $period===$key)>{{ substr($key,0,4) }}년 {{ substr($key,5,2) }}월</option>@endforeach</select></label><button type="submit" class="primary">검색</button>
</form><p style="margin-top:16px;color:#667085">당월부터 최근 1년 이내, 한 번에 1개월 단위로 조회할 수 있습니다.</p>
@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
</section>
@if(!$hasPeriod)
<section class="order-panel"><p>정산 기간을 선택해 주십시오.</p></section>
@else
<section class="order-panel">
<div class="order-toolbar"><h2>{{ $period }} 정산 내역</h2><a class="order-button" href="{{ route('channel.settlement.export',['period'=>$period,'kind'=>'orders']) }}">정산 상세 엑셀</a></div>
<div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr><th>Shop 채널</th><th>정산역할</th><th>PG</th><th>구매확정 주문</th><th>채널매출</th><th>채널매입</th><th>제공포인트</th><th>포인트 결제</th><th>SMS 후불</th><th>정산비용</th><th>상세</th></tr></thead><tbody>
@forelse($settlements as $row)
<tr><td>{{ $row->shop_channel_name }}</td><td>{{ ['seller'=>'자사상품 판매자','shared_fixed_supplier'=>'공유상품 제공자(고정가)','shared_fixed_reseller'=>'위탁판매자(고정가)','shared_free_supplier'=>'공유상품 제공자(자유가)','shared_free_reseller'=>'위탁판매자(자유가)'][$row->settlement_role] ?? $row->settlement_role }}</td><td>{{ $row->payment_gateway_type==='own_pg'?'자사PG':'공용PG' }}</td><td>{{ $row->order_count }}건</td>@foreach(['invoice_sales_amount','invoice_purchase_amount','point_deposit_amount','point_used_amount','sms_postpaid_amount','payout_amount'] as $key)<td>{{ number_format($row->$key) }} {{ str_starts_with($key,'point')?'P':'원' }}</td>@endforeach<td><a class="order-button" href="{{ route('channel.settlement.view',['period'=>$period,'shop_channel_id'=>$row->shop_channel_id?:0,'role'=>$row->settlement_role,'pg'=>$row->payment_gateway_type]) }}">보기</a></td></tr>
@empty<tr><td colspan="11">정산 내역이 없습니다.</td></tr>@endforelse
</tbody><tfoot><tr><th colspan="4">합계</th>@foreach(['invoice_sales_amount','invoice_purchase_amount','point_deposit_amount','point_used_amount','sms_postpaid_amount','payout_amount'] as $key)<td>{{ number_format($totals[$key]) }}</td>@endforeach<td></td></tr></tfoot></table></div>
{{ $settlements->links() }}
</section>
<section class="order-panel"><div class="order-toolbar"><h2>반품·교환 추가 배송비</h2><a class="order-button" href="{{ route('channel.settlement.export',['period'=>$period,'kind'=>'shipping']) }}">추가 배송비 엑셀</a></div><p>공용PG 주문의 반품·교환 완료 월을 기준으로 조회합니다.</p><div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr>@foreach(\App\Services\SettlementReport::SHIPPING_HEADERS as $header)<th>{{ $header }}</th>@endforeach</tr></thead><tbody>@forelse($shippingRows as $row)<tr>@foreach($row as $value)<td>{{ is_numeric($value)?number_format($value):$value }}</td>@endforeach</tr>@empty<tr><td colspan="9">추가 배송비 내역이 없습니다.</td></tr>@endforelse</tbody></table></div></section>
@endif
<section class="order-panel"><h2 style="margin-bottom:20px">정산 집행 현황</h2><div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr><th>집행일</th><th>집행 내역</th><th>Shop 채널</th><th>금액</th><th>첨부자료</th><th>메모</th></tr></thead><tbody>
@forelse($hasPeriod?$executions:collect() as $execution)
<tr><td>{{ $execution->executed_at?->format('Y-m-d') }}</td><td>{{ $execution->title }}</td><td>{{ $execution->shopChannel?->channel_name }}</td><td>{{ number_format($execution->amount) }} 원</td><td>@if($execution->attachment_path)<a class="order-button" href="{{ route('channel.settlement.executions.download',$execution->id) }}">{{ $execution->attachment_name ?: '다운로드' }}</a>@else - @endif</td><td>{{ $execution->memo ?: '-' }}</td></tr>
@empty<tr><td colspan="6">정산 집행 내역이 없습니다.</td></tr>@endforelse
</tbody></table></div></section>
</div></div>
@endsection
