@extends('layouts.channel')
@section('page_type','sub')
@php($dep1_id='00')
@push('styles')
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/order-managers.css?v={{ filemtime(public_path('channel_assets/css/order-managers.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace manager-workspace" data-manager-active-form="{{ $activeForm }}">
<div class="order-page-heading"><div><p>환경설정</p><h1>발주담당 관리</h1></div><button type="button" class="primary" data-manager-open="manager-create">발주담당 등록</button></div>
@if(session('flash_message_success'))<section class="order-panel" role="status">{{ session('flash_message_success') }}</section>@endif
@if($errors->any())<section class="order-panel manager-error" role="alert">{{ $errors->first() }}</section>@endif
<section class="order-panel"><form action="{{ route('channel.order.manager') }}" method="GET" class="order-search-row"><label class="grow">검색어<input name="keyword" value="{{ $keyword }}" placeholder="이메일, 담당자명, 연락처"></label><button type="submit" class="primary">검색</button><a class="order-button" href="{{ route('channel.order.manager') }}">초기화</a></form></section>
<section class="order-panel"><div class="order-toolbar"><h2>발주담당자 목록</h2><p>총 <strong>{{ $managers->total() }}</strong> 건</p></div><div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr><th>No</th><th>상태</th><th>이메일</th><th>발주담당자</th><th>접속기간</th><th>관리상품수</th><th>관리</th></tr></thead><tbody>
@forelse($managers as $manager)
<tr><td>{{ $managers->firstItem()+$loop->index }}</td><td><span class="order-state">{{ $manager->access_status }}</span></td><td>{{ $manager->email }}</td><td>{{ $manager->name }}</td><td>{{ $manager->access_started_at?->format('Y.m.d H:i') ?: '시작 제한 없음' }}<br>~ {{ $manager->access_ended_at?->format('Y.m.d H:i') ?: '종료 제한 없음' }}</td><td>{{ number_format($manager->products_count) }}</td><td><div class="manager-actions"><a class="order-button" href="{{ route('channel.order.manager.show',$manager->id) }}" data-manager-view>보기</a><button type="button" data-manager-open="manager-edit-{{ $manager->id }}">수정</button><form method="POST" action="{{ route('channel.order.manager.portal',$manager->id) }}" target="_blank">@csrf<button type="submit" @disabled(!$manager->canAccess())>발주확인</button></form></div></td></tr>
@empty<tr><td class="order-empty" colspan="7">등록된 발주담당자가 없습니다.</td></tr>
@endforelse
</tbody></table></div>{{ $managers->links() }}</section></div></div>
<dialog class="order-dialog manager-dialog" id="manager-create" aria-labelledby="manager-create-title"><div class="order-dialog-heading"><h2 id="manager-create-title">발주담당자 등록</h2><button type="button" data-manager-close>창닫기</button></div><form method="POST" action="{{ route('channel.order.manager.store') }}" data-manager-form data-original-email="">@csrf
@include('channel.sub00.inc.order_manager_form',['manager'=>null])
</form></dialog>
@foreach($managers as $manager)
<dialog class="order-dialog manager-dialog" id="manager-edit-{{ $manager->id }}" aria-labelledby="manager-edit-title-{{ $manager->id }}"><div class="order-dialog-heading"><h2 id="manager-edit-title-{{ $manager->id }}">발주담당자 수정</h2><button type="button" data-manager-close>창닫기</button></div><form method="POST" action="{{ route('channel.order.manager.update',$manager->id) }}" data-manager-form data-original-email="{{ strtolower($manager->email) }}">@csrf
@include('channel.sub00.inc.order_manager_form',['manager'=>$manager])
</form></dialog>
@endforeach
<dialog class="order-dialog manager-dialog" id="manager-view" aria-labelledby="manager-view-title"><div class="order-dialog-heading"><h2 id="manager-view-title">발주담당 상세보기</h2><button type="button" data-manager-close>창닫기</button></div><div data-manager-detail-body aria-live="polite"></div></dialog>
<dialog class="order-dialog manager-postcode-dialog" id="manager-postcode" aria-labelledby="manager-postcode-title"><div class="order-dialog-heading"><h2 id="manager-postcode-title">출고지 우편번호찾기</h2><button type="button" data-manager-close>창닫기</button></div><div id="manager-postcode-widget"></div></dialog>
@endsection
@push('scripts')
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script src="/channel_assets/js/order-managers.js?v={{ filemtime(public_path('channel_assets/js/order-managers.js')) }}" defer></script>
@endpush
