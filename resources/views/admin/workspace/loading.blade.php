
@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>운영 현황 새로고침</h1><section class="admin-panel"><p>주문상품의 현재 건수를 다시 조회합니다.</p><div class="admin-toolbar"><button class="admin-btn primary" type="button" data-loading-demo="{{ route('admin.workspace.summary') }}" data-output="summary-result">현재 주문현황 조회</button><a class="admin-btn" href="{{ route('admin.dashboard') }}">대시보드</a></div><p id="summary-result" role="status" class="admin-alert">조회 버튼을 눌러 주세요.</p></section></div>
@endsection
