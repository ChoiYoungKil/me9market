@extends('layouts.admin')
@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script src="{{ asset('common/admin-editor.js') }}?v={{ filemtime(public_path('common/admin-editor.js')) }}"></script>
@endpush
@section('content')
<div id="contents" class="admin-workspace admin-legacy admin-refund-settings"><div class="con_bx" style="padding:32px">
    <h1>취소/환불안내</h1>
    @if(session('success_message'))<p role="status">{{ session('success_message') }}</p>@endif
    <form class="admin-default-refund-form" method="POST" action="{{ route('admin.refund_policies.default') }}">
        @csrf<h2>Me9 기본 취소/환불 안내</h2>
        <p>상품등록에서 기본 안내를 선택한 상품에 표시됩니다.</p>
        <div class="refund-editor-field">
            <label for="default-refund-content">안내 내용</label>
            <textarea id="default-refund-content" name="content" data-admin-editor maxlength="30000" rows="10" style="width:100%;padding:12px;border:1px solid #bbb">{{ old('content',app(\App\Services\RefundPolicyContent::class)->editorHtml($defaultRefundPolicy)) }}</textarea>
            @error('content')<p role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="refund-editor-actions"><button type="submit" class="admin-btn primary">기본 안내 저장</button></div>
    </form>
    <form method="GET" class="admin-refund-filter"><label>정책명 <input name="search" value="{{ request('search') }}" maxlength="200"></label><button type="submit" class="admin-btn primary">검색</button></form>
    @forelse($policies as $policy)
        <details class="admin-refund-policy">
            <summary>{{ $policy->vendor?->name }} · {{ $policy->name }} · {{ $policy->status === 'active' ? '사용' : '미사용' }}</summary>
            <div style="white-space:pre-wrap;overflow-wrap:anywhere;padding:16px 0">{!! app(\App\Services\RefundPolicyContent::class)->render($policy->content) !!}</div>
        </details>
    @empty<p class="list-empty-state">등록된 자료가 없습니다.</p>@endforelse
    <div class="admin-pagination">{{ $policies->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div>
</div></div>
@endsection
