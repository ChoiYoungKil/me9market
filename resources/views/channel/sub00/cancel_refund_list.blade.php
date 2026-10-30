@extends('layouts.channel')
@section('page_type','sub')
@php($dep1_id='00')
@push('styles')
<link rel="stylesheet" href="/admin/vendors/quill/quill.snow.css">
<link rel="stylesheet" href="/common/policy-rich-text.css?v={{ filemtime(public_path('common/policy-rich-text.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/refund-policies.css?v={{ filemtime(public_path('channel_assets/css/refund-policies.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace refund-workspace" data-refund-active-form="{{ $activeForm }}">
<div class="order-page-heading"><div><p>환경설정</p><h1>취소/환불안내 관리</h1></div><button type="button" class="primary" data-refund-open="refund-create">취소/환불안내 등록</button></div>
@if(session('flash_message_success'))<section class="order-panel" role="status">{{ session('flash_message_success') }}</section>@endif
@if($errors->any())<section class="order-panel refund-error" role="alert">{{ $errors->first() }}</section>@endif
<section class="order-panel"><form method="GET" action="{{ route('channel.refund.list') }}" class="order-search-row"><label class="grow">취소/환불안내 명칭<input name="keyword" value="{{ $keyword }}" maxlength="100" placeholder="안내 명칭 검색"></label><button type="submit" class="primary">검색</button><a class="order-button" href="{{ route('channel.refund.list') }}">초기화</a></form></section>
<section class="order-panel"><div class="order-toolbar"><h2>취소/환불안내 목록</h2><p>총 <strong>{{ $policies->total() }}</strong> 건</p></div>
<div class="order-table-scroll" role="region" aria-label="취소/환불안내 목록" tabindex="0"><table class="order-table"><thead><tr><th>설정구분</th><th>상태</th><th>취소/환불안내 명칭</th><th>연결 상품수</th><th>관리</th></tr></thead><tbody>
@forelse($policies as $policy)<tr><td>{{ $policy->type==='default' ? '기본' : '사용자' }}</td><td><span class="order-state">{{ $policy->status==='active' ? '사용' : '중지' }}</span></td><td>{{ $policy->name }}</td><td>{{ number_format($policy->linked_product_count) }}</td><td><div class="refund-actions"><a class="order-button" href="{{ route('channel.refund.get',$policy->id) }}" data-refund-view>보기</a><button type="button" data-refund-open="refund-edit-{{ $policy->id }}">수정</button><form method="POST" action="{{ route('channel.refund.copy',$policy->id) }}">@csrf<button type="submit">복사</button></form><form method="POST" action="{{ route('channel.refund.delete',$policy->id) }}" data-refund-delete>@csrf<button type="submit" @disabled($policy->linked_product_count>0) title="{{ $policy->linked_product_count>0 ? '상품에서 사용 중인 안내는 삭제할 수 없습니다.' : '취소/환불안내 삭제' }}">삭제</button></form></div></td></tr>
@empty<tr><td colspan="5" class="order-empty">검색 조건에 맞는 취소/환불안내가 없습니다.</td></tr>@endforelse
</tbody></table></div>{{ $policies->links() }}</section></div></div>
<dialog id="refund-create" class="order-dialog refund-dialog" aria-labelledby="refund-create-title"><div class="order-dialog-heading"><h2 id="refund-create-title">취소/환불안내 등록</h2><button type="button" data-refund-close>창닫기</button></div><form method="POST" action="{{ route('channel.refund.store') }}">@csrf @include('channel.sub00.inc.refund_policy_form',['policy'=>null])</form></dialog>
@foreach($policies as $policy)<dialog id="refund-edit-{{ $policy->id }}" class="order-dialog refund-dialog" aria-labelledby="refund-edit-title-{{ $policy->id }}"><div class="order-dialog-heading"><h2 id="refund-edit-title-{{ $policy->id }}">취소/환불안내 수정</h2><button type="button" data-refund-close>창닫기</button></div><form method="POST" action="{{ route('channel.refund.update',$policy->id) }}">@csrf @include('channel.sub00.inc.refund_policy_form',['policy'=>$policy])</form></dialog>@endforeach
<dialog id="refund-view" class="order-dialog refund-dialog" aria-labelledby="refund-view-title"><div class="order-dialog-heading"><h2 id="refund-view-title">취소/환불안내 정보</h2><button type="button" data-refund-close>창닫기</button></div><div data-refund-details aria-live="polite"></div></dialog>
@endsection
@push('scripts')<script src="/admin/vendors/quill/quill.min.js"></script><script src="/channel_assets/js/refund-policies.js?v={{ filemtime(public_path('channel_assets/js/refund-policies.js')) }}" defer></script>@endpush
