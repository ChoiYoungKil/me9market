@extends('layouts.admin')
@section('content')
<div id="container"><div class="con_bx" style="padding:32px">
    <h1 style="font-size:24px;margin-bottom:24px">취소/환불안내</h1>
    <form method="GET" style="display:flex;gap:8px;margin-bottom:24px"><label>정책명 <input name="search" value="{{ request('search') }}" maxlength="200"></label><button type="submit">검색</button></form>
    @forelse($policies as $policy)
        <details style="padding:16px 0;border-bottom:1px solid #ddd">
            <summary>{{ $policy->vendor?->name }} · {{ $policy->name }} · {{ (int) $policy->status === 1 ? '사용' : '미사용' }}</summary>
            <div style="white-space:pre-wrap;overflow-wrap:anywhere;padding:16px 0">{{ strip_tags($policy->content ?? '') }}</div>
        </details>
    @empty<p>등록된 정책이 없습니다.</p>@endforelse
    <nav aria-label="페이지 이동" style="margin-top:24px">@if($policies->previousPageUrl())<a href="{{ $policies->previousPageUrl() }}">이전</a>@endif <span>{{ $policies->currentPage() }} / {{ $policies->lastPage() }}</span> @if($policies->nextPageUrl())<a href="{{ $policies->nextPageUrl() }}">다음</a>@endif</nav>
</div></div>
@endsection
