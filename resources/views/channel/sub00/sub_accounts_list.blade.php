@extends('layouts.channel')
@section('page_type','sub')
@php($dep1_id='00')
@push('styles')
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/sub-accounts.css?v={{ filemtime(public_path('channel_assets/css/sub-accounts.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace subaccount-workspace" data-active-form="{{ $activeForm }}">
<div class="order-page-heading"><div><p>환경설정</p><h1>서브관리자 관리</h1></div><button class="primary" type="button" data-dialog-open="subaccount-create">서브관리자 등록</button></div>
@if(session('flash_message_success'))<section class="order-panel" role="status">{{ session('flash_message_success') }}</section>@endif
@if($errors->any())<section class="order-panel subaccount-error" role="alert">{{ $errors->first() }}</section>@endif
<section class="order-panel"><form method="GET" action="{{ route('channel.sub_accounts.list') }}" class="order-search-row"><label class="grow">검색어<input name="keyword" value="{{ $keyword }}" placeholder="회원번호, 아이디, 이메일, 이름, 연락처"></label><button type="submit" class="primary">검색</button><a class="order-button" href="{{ route('channel.sub_accounts.list') }}">초기화</a></form></section>
<section class="order-panel"><div class="order-toolbar"><h2>서브관리자 목록</h2><p>총 <strong>{{ $accounts->total() }}</strong> 건</p></div>
<div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr><th>No</th><th>상태</th><th>회원번호</th><th>회원아이디</th><th>회원명</th><th>이용기간</th><th>권한</th><th>관리</th></tr></thead><tbody>
@forelse($accounts as $account)
<tr><td>{{ $accounts->firstItem()+$loop->index }}</td><td><span class="order-state">{{ $account->access_status }}</span></td><td>{{ $account->member?->member_number ?: $account->member_no ?: '-' }}</td><td>{{ $account->member?->username ?: $account->member?->email ?: $account->admin?->email }}</td><td>{{ $account->member?->name ?: $account->admin?->name }}</td><td>{{ $account->started_at?->format('Y.m.d H:i') ?: '-' }}<br>~ {{ $account->ended_at?->format('Y.m.d H:i') ?: '-' }}</td><td>@forelse($account->permissions ?? [] as $permission)<span class="subaccount-permission">{{ $permissionLabels[$permission] ?? $permission }}</span>@empty - @endforelse</td><td><div class="subaccount-actions"><button type="button" data-dialog-open="subaccount-view-{{ $account->id }}">보기</button><button type="button" data-dialog-open="subaccount-edit-{{ $account->id }}">수정</button></div></td></tr>
@empty<tr><td colspan="8" class="order-empty">등록된 서브관리자가 없습니다.</td></tr>
@endforelse
</tbody></table></div>{{ $accounts->links() }}</section>
</div></div>
<dialog class="order-dialog subaccount-dialog" id="subaccount-create" aria-labelledby="subaccount-create-title"><div class="order-dialog-heading"><h2 id="subaccount-create-title">서브관리자 등록</h2><button type="button" data-dialog-close>창닫기</button></div>
<form method="POST" action="{{ route('channel.sub_accounts.store') }}" data-subaccount-form>@csrf
@include('channel.sub00.inc.sub_account_form',['account'=>null])
@if(old('form_key')==='create' && $errors->any())<p role="alert" class="subaccount-error">{{ $errors->first() }}</p>@endif
<div class="order-actions"><button type="button" data-dialog-close>취소</button><button type="submit" class="primary">서브관리자 등록</button></div></form></dialog>
@foreach($accounts as $account)
<dialog class="order-dialog subaccount-dialog" id="subaccount-view-{{ $account->id }}" aria-labelledby="subaccount-view-title-{{ $account->id }}"><div class="order-dialog-heading"><h2 id="subaccount-view-title-{{ $account->id }}">서브관리자 보기</h2><button type="button" data-dialog-close>창닫기</button></div><h3>회원정보</h3><dl class="subaccount-facts"><div><dt>회원번호</dt><dd>{{ $account->member?->member_number ?: $account->member_no ?: '-' }}</dd></div><div><dt>회원아이디</dt><dd>{{ $account->member?->username ?: $account->member?->email ?: $account->admin?->email }}</dd></div><div><dt>회원명</dt><dd>{{ $account->member?->name ?: $account->admin?->name }}</dd></div></dl><h3>서브관리정보</h3><dl class="subaccount-facts"><div><dt>이용 상태</dt><dd>{{ $account->access_status }}</dd></div><div><dt>이용기간</dt><dd>{{ $account->started_at?->format('Y.m.d H:i') ?: '-' }} ~ {{ $account->ended_at?->format('Y.m.d H:i') ?: '-' }}</dd></div><div><dt>서브관리 권한</dt><dd>@forelse($account->permissions ?? [] as $permission)<span class="subaccount-permission">{{ $permissionLabels[$permission] ?? $permission }}</span>@empty - @endforelse</dd></div></dl></dialog>
<dialog class="order-dialog subaccount-dialog" id="subaccount-edit-{{ $account->id }}" aria-labelledby="subaccount-edit-title-{{ $account->id }}"><div class="order-dialog-heading"><h2 id="subaccount-edit-title-{{ $account->id }}">서브관리자 수정</h2><button type="button" data-dialog-close>창닫기</button></div><form method="POST" action="{{ route('channel.sub_accounts.update',$account->id) }}" data-subaccount-form>@csrf
@include('channel.sub00.inc.sub_account_form',['account'=>$account])
@if(old('form_key')==='edit-'.$account->id && $errors->any())<p role="alert" class="subaccount-error">{{ $errors->first() }}</p>@endif
<div class="order-actions"><button type="button" data-dialog-close>취소</button><button type="submit" class="primary">서브관리자 수정</button></div></form></dialog>
@endforeach
@endsection
@push('scripts')<script src="/channel_assets/js/sub-accounts.js?v={{ filemtime(public_path('channel_assets/js/sub-accounts.js')) }}" defer></script>@endpush
