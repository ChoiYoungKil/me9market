@extends('layouts.admin')
@section('page_type','sub')
@push('styles')<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}"><link rel="stylesheet" href="/channel_assets/css/settlement-workspace.css?v={{ filemtime(public_path('channel_assets/css/settlement-workspace.css')) }}">@endpush
@section('content')
<div id="contents"><div class="order-workspace settlement-workspace">
<div class="order-page-heading"><div><p>전체관리자</p><h1>정산관리</h1></div></div>
@if(session('success_message'))<section class="order-panel" role="status">{{ session('success_message') }}</section>@endif
@if($errors->any())<section class="order-panel" role="alert">{{ $errors->first() }}</section>@endif
<section class="order-panel"><form id="settlement-search" method="GET" action="{{ route('admin.settlements.index') }}" class="order-search-row">
<label class="grow">채널아이디<select name="shop_channel_id" required><option value="">채널 선택</option>@foreach($shops as $shop)<option value="{{ $shop->id }}" @selected($selectedShop?->id===$shop->id)>{{ $shop->channel_code }} / {{ $shop->channel_name }} / {{ $shop->vendor?->name }}</option>@endforeach</select></label>
<label>정산 년월<select name="period" required><option value="">정산 기간 선택</option>@foreach($periodOptions as $key=>$label)<option value="{{ $key }}" @selected(request()->filled('period') && $period===$key)>{{ $key }}</option>@endforeach</select></label><button type="submit" class="primary">검색</button></form></section>
@if(!$hasSelection)<section class="order-panel">채널 아이디와 정산 기간을 선택해 주십시오.</section>@else
<section class="order-panel"><div class="order-toolbar"><h2>{{ $selectedShop->channel_name }} · {{ $period }}</h2><form method="POST" action="{{ route('admin.settlements.generate') }}">@csrf<input type="hidden" name="period" value="{{ $period }}"><input type="hidden" name="shop_channel_id" value="{{ $selectedShop->id }}"><button type="submit">정산자료 생성/갱신</button></form></div>
<div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr><th>판매자</th><th>정산역할</th><th>주문건수</th><th>매출</th><th>매입</th><th>제공포인트</th><th>SMS 후불</th><th>정산비용</th><th>지급액 상세</th><th>청구액 상세</th><th>엑셀</th></tr></thead><tbody>
@forelse($rows as $row)
@php $query=['period'=>$period,'shop_channel_id'=>$selectedShop->id,'vendor_id'=>$row['vendor_id']]; @endphp
<tr><td>{{ $row['vendor_name'] }}</td><td>{{ ['seller'=>'자사상품 판매자','shared_fixed_supplier'=>'공유상품 제공자(고정가)','shared_fixed_reseller'=>'위탁판매자(고정가)','shared_free_supplier'=>'공유상품 제공자(자유가)','shared_free_reseller'=>'위탁판매자(자유가)'][$row['settlement_role']] ?? $row['settlement_role'] }}</td><td>{{ $row['order_count'] }}건</td>@foreach(['invoice_sales_amount','invoice_purchase_amount','point_deposit_amount','sms_postpaid_amount','payout_amount'] as $key)<td>{{ number_format($row[$key]) }}</td>@endforeach<td><a class="order-button" target="_blank" rel="noopener" href="{{ route('admin.settlements.workbook',$query+['kind'=>'payout']) }}">지급액 보기</a></td><td><a class="order-button" target="_blank" rel="noopener" href="{{ route('admin.settlements.workbook',$query+['kind'=>'billing']) }}">청구액 보기</a></td><td><a class="order-button" href="{{ route('admin.settlements.workbook',$query+['kind'=>'orders']) }}">상세 엑셀</a></td></tr>
@empty<tr><td colspan="11">정산 내역이 없습니다.</td></tr>@endforelse
</tbody></table></div>
</section>
<section class="order-panel"><h2>정산 집행 등록</h2><p style="margin:16px 0">대상: {{ $selectedShop->channel_code }} / 정산월: {{ $period }}</p><form method="POST" action="{{ route('admin.settlements.executions.store') }}" enctype="multipart/form-data" id="settlement-execution-form">@csrf
<input type="hidden" name="period" value="{{ $period }}"><input type="hidden" name="shop_channel_id" value="{{ $selectedShop->id }}">
<div class="order-fields"><label>집행 대상 판매자<select name="recipient_vendor_id"><option value="{{ $selectedShop->vendor_id }}">{{ $selectedShop->vendor?->name }}</option>@foreach($rows->where('vendor_id','!=',$selectedShop->vendor_id)->unique('vendor_id') as $recipient)<option value="{{ $recipient['vendor_id'] }}">{{ $recipient['vendor_name'] }}</option>@endforeach</select></label><label>집행 내역<input name="title" maxlength="255" value="{{ old('title') }}" required></label><label>금액 (원)<input type="number" name="amount" min="0" step="1" value="{{ old('amount') }}" required></label><label>집행일<input type="date" name="executed_at" value="{{ old('executed_at',now()->format('Y-m-d')) }}" required></label><label>첨부자료<input type="file" name="attachment" accept=".xls,.xlsx,.csv,.pdf"></label><label>메모<input name="memo" maxlength="255" value="{{ old('memo') }}"></label></div><div class="order-toolbar"><button type="submit" class="primary">집행 등록</button></div></form></section>
<section class="order-panel"><h2 style="margin-bottom:20px">정산 집행 현황</h2><div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr><th>채널아이디</th><th>정산년월</th><th>집행일</th><th>집행 내역</th><th>금액</th><th>첨부자료</th><th>메모</th></tr></thead><tbody>
@forelse($executions as $execution)<tr><td>{{ $execution->shopChannel?->channel_code }}</td><td>{{ $execution->period }}</td><td>{{ $execution->executed_at?->format('Y-m-d') }}</td><td>{{ $execution->title }}</td><td>{{ number_format($execution->amount) }} 원</td><td>@if($execution->attachment_path)<a class="order-button" href="{{ route('admin.settlements.executions.download',$execution->id) }}">{{ $execution->attachment_name }}</a>@else - @endif</td><td>{{ $execution->memo ?: '-' }}</td></tr>@empty<tr><td colspan="7">정산 집행 내역이 없습니다.</td></tr>@endforelse
</tbody></table></div></section>
@endif
</div></div>
@endsection
