@extends('layouts.admin')
@section('content')
<div id="contents"><div class="con_bx" style="padding:32px">
    <h1 style="font-size:24px;margin-bottom:24px">취소/환불안내</h1>
    @if(session('success_message'))<p role="status">{{ session('success_message') }}</p>@endif
    <form method="POST" action="{{ route('admin.refund_policies.default') }}" style="padding:24px;border:1px solid #ddd;border-radius:8px;margin-bottom:24px">
        @csrf<h2 style="font-size:18px;margin-bottom:16px">Me9 기본 취소/환불 안내</h2>
        <p>상품등록에서 기본 안내를 선택한 상품에 표시됩니다.</p>
        <label for="default-refund-content">안내 내용</label><textarea id="default-refund-content" name="content" required maxlength="30000" rows="10" style="width:100%;padding:12px;border:1px solid #bbb;margin:12px 0">{{ old('content',$defaultRefundPolicy) }}</textarea>
        @error('content')<p role="alert">{{ $message }}</p>@enderror
        <button type="submit" style="padding:12px 24px;color:#fff;background:#51489c;border-radius:6px">기본 안내 저장</button>
    </form>
    <form method="GET" style="display:flex;gap:8px;margin-bottom:24px"><label>정책명 <input name="search" value="{{ request('search') }}" maxlength="200"></label><button type="submit">검색</button></form>
    @forelse($policies as $policy)
        <details style="padding:16px 0;border-bottom:1px solid #ddd">
            <summary>{{ $policy->vendor?->name }} · {{ $policy->name }} · {{ $policy->status === 'active' ? '사용' : '미사용' }}</summary>
            <div style="white-space:pre-wrap;overflow-wrap:anywhere;padding:16px 0">{{ strip_tags($policy->content ?? '') }}</div>
        </details>
    @empty<p>등록된 정책이 없습니다.</p>@endforelse
    <nav aria-label="페이지 이동" style="margin-top:24px">@if($policies->previousPageUrl())<a href="{{ $policies->previousPageUrl() }}">이전</a>@endif <span>{{ $policies->currentPage() }} / {{ $policies->lastPage() }}</span> @if($policies->nextPageUrl())<a href="{{ $policies->nextPageUrl() }}">다음</a>@endif</nav>
</div></div>
@endsection
