
@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>회원 관리</h1>
@include('admin.shared.messages')
<section class="admin-panel"><form action="{{ route('admin.users') }}" method="get" class="admin-filter"><label>검색 구분<select name="search_type"><option value="">전체</option>
@foreach(['id'=>'회원번호','name'=>'이름','email'=>'이메일','mobile'=>'연락처'] as $code=>$label)<option value="{{ $code }}"
@selected(request('search_type')===$code)>{{ $label }}</option>
@endforeach</select></label><label>검색어<input type="search" name="search_value" value="{{ request('search_value') }}" maxlength="100"></label><label>가입 시작일<input type="date" name="start_date" value="{{ request('start_date') }}"></label><label>가입 종료일<input type="date" name="end_date" value="{{ request('end_date') }}"></label><label>판매자 신청<select name="seller_status"><option value="">전체</option><option value="pending"
@selected(request('seller_status')==='pending')>승인 대기</option></select></label><label>목록 개수<select name="per_page">
@foreach([10,20,50,100] as $count)<option value="{{ $count }}"
@selected(request('per_page',10)==$count)>{{ $count }}개</option>
@endforeach</select></label><button class="admin-btn primary">검색</button><a class="admin-btn admin-button-reset" href="{{ route('admin.users') }}">초기화</a></form></section>
<div class="admin-toolbar"><p>총 <strong>{{ $users->total() }}</strong>명</p><div class="admin-actions"><a class="admin-btn admin-button-export" href="{{ request()->fullUrlWithQuery(['export'=>1,'page'=>null]) }}">엑셀 다운로드</a><a class="admin-btn primary" href="{{ url('admin/add-edit-user') }}">회원 추가</a></div></div>
<form action="{{ url('admin/lists/users/bulk') }}" method="post">
@csrf
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th><input type="checkbox" data-select-all=".admin-row-check" aria-label="목록 전체 선택"></th><th>회원번호</th><th>이름 / 아이디</th><th>구분</th><th>이메일</th><th>연락처</th><th>상태</th><th>가입일</th><th>관리</th></tr></thead><tbody>
@forelse($users as $user)<tr><td><input type="checkbox" name="ids[]" value="{{ $user->id }}" class="admin-row-check" aria-label="회원 {{ $user->id }} 선택"></td><td>{{ $user->id }}</td><td>{{ $user->name }}<br><small>{{ $user->username }}</small></td><td>{{ $user->vendor_id?'판매자':'일반회원' }}</td><td>{{ $user->email }}</td><td>{{ $user->mobile }}</td><td><span class="admin-status">{{ $user->status?'활성':'비활성' }}</span></td><td>{{ $user->created_at?->format('Y-m-d') }}</td><td class="nowrap">
@if($user->vendor_id && $sellerApprovals->get($user->vendor_id)?->seller_requested_at)<a class="admin-btn small" href="{{ route('admin.users.seller_review',$user->id) }}">{{ $sellerApprovals->get($user->vendor_id)?->seller_approved_at?'승인완료 확인':'판매자 승인 검토' }}</a>
@endif <a class="admin-btn small" href="{{ url('admin/add-edit-user/'.$user->id) }}">수정</a> <button type="button" class="admin-btn small danger" data-admin-delete="{{ url('admin/delete-user/'.$user->id) }}">삭제</button></td></tr>
@empty
@include('components.table-empty-row', ['columns' => 9])
@endforelse</tbody></table></div><div class="admin-toolbar admin-bulk-bar"><div class="admin-actions"><label>선택 회원 상태 <select name="status" required><option value="">선택</option><option value="1">활성</option><option value="0">비활성</option></select></label><button class="admin-btn primary" type="submit">선택 회원 일괄 적용</button></div></div></form><div class="admin-pagination">{{ $users->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div></div>
@endsection
