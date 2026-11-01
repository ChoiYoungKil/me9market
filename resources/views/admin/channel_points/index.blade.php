@extends('layouts.admin')
@section('content')
@php
$typeLabels=['purchase'=>'구매','customer_payback'=>'고객 페이백','first_visit'=>'첫 방문 포인트','sms'=>'문자 차감','refund'=>'환급'];
$statusLabels=['all'=>'전체','pending'=>'승인대기','approved'=>'승인완료','rejected'=>'반려'];
@endphp
<div id="contents" class="admin-workspace"><h1>판매자 포인트 판매/사용 내역</h1>
@include('admin.shared.messages')
<div class="admin-point-metrics">
@foreach(['approved_purchase'=>'포인트 판매완료','pending_purchase'=>'구매 승인대기','pending_refund'=>'환급 승인대기','approved_payback'=>'고객 페이백 차감','approved_first_visit'=>'첫 방문 포인트 차감','approved_sms'=>'문자 차감','approved_balance'=>'전체 승인 잔액'] as $key=>$label)
<section class="admin-panel"><p class="admin-help">{{ $label }}</p><strong>{{ number_format($totals[$key]??0) }} P</strong></section>
@endforeach
</div>
<section class="admin-panel"><form method="get" class="admin-filter admin-point-filter"><label>처리 상태<select name="status">
@foreach($statusLabels as $value=>$label)
<option value="{{ $value }}" @selected($status===$value)>{{ $label }}</option>
@endforeach
</select></label><label>포인트 구분<select name="type"><option value="all">전체</option>
@foreach($typeLabels as $value=>$label)
<option value="{{ $value }}" @selected($type===$value)>{{ $label }}</option>
@endforeach
</select></label><button class="admin-btn primary">검색</button><a class="admin-btn" href="{{ route('admin.channel_points.index') }}">초기화</a></form></section>
<div class="admin-toolbar"><p>총 <strong>{{ number_format($transactions->total()) }}</strong>건</p></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>번호</th><th>등록일</th><th>판매자 / 채널</th><th style="width:110px">구분</th><th style="width:100px">상태</th><th>포인트</th><th>처리 내역</th><th>관리</th></tr></thead><tbody>
@forelse($transactions as $transaction)
<tr><td>{{ $transaction->id }}</td><td class="nowrap">{{ $transaction->created_at?->format('Y-m-d H:i') }}</td><td>{{ $transaction->vendor?->name??'판매자 #'.$transaction->vendor_id }}<br><small>{{ $transaction->shopChannel?->channel_name??'-' }}</small></td><td>{{ $typeLabels[$transaction->type]??$transaction->type }}</td><td><span class="admin-status">{{ $statusLabels[$transaction->status]??$transaction->status }}</span></td><td class="nowrap">{{ $transaction->points>=0?'+':'' }}{{ number_format($transaction->points) }} P</td><td class="text-left admin-preserve-lines">{{ $transaction->memo?:'-' }}</td><td class="nowrap">
@if($transaction->status==='pending')
<div class="admin-row-actions"><form method="post" action="{{ route('admin.channel_points.approve',$transaction->id) }}">@csrf<button type="submit" class="admin-btn primary">승인</button></form><button type="button" class="admin-btn" data-dialog-open="point-reject-{{ $transaction->id }}">반려</button></div>
@else
처리 완료
@endif
</td></tr>
@empty
<tr><td colspan="8" class="admin-empty">포인트 내역이 없습니다.</td></tr>
@endforelse
</tbody></table></div><div class="admin-pagination">{{ $transactions->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div>
@foreach($transactions->where('status','pending') as $transaction)
<dialog class="admin-modal" id="point-reject-{{ $transaction->id }}"><div class="admin-modal-header"><h2>포인트 요청 반려 · #{{ $transaction->id }}</h2><button type="button" class="admin-btn" data-dialog-close aria-label="반려 창닫기">×</button></div><form method="post" action="{{ route('admin.channel_points.reject',$transaction->id) }}">@csrf<label class="admin-field">반려 사유<textarea name="memo" maxlength="255" required rows="3"></textarea></label><div class="admin-modal-footer"><button type="button" class="admin-btn" data-dialog-close>취소</button><button type="submit" class="admin-btn primary">반려 저장</button></div></form></dialog>
@endforeach
</div>
@endsection
