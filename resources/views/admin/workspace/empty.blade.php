
@extends('layouts.admin')
@section('content')<div id="contents" class="admin-workspace"><h1>관리 자료</h1><section class="admin-panel"><p>등록된 자료가 없습니다.</p><a class="admin-btn primary" href="{{ url('admin/notices') }}">공지 관리</a><a class="admin-btn" href="{{ route('admin.products') }}">상품 관리</a></section></div>
@endsection
