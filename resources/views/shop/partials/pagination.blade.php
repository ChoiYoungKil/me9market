@if($paginator->hasPages())
<nav class="shop-pages" aria-label="페이지 이동">
    @if($paginator->onFirstPage())<span aria-disabled="true">&lt;</span>@else<a href="{{ $paginator->previousPageUrl() }}" aria-label="이전 페이지">&lt;</a>@endif
    @for($page = max(1, $paginator->currentPage() - 2); $page <= min($paginator->lastPage(), $paginator->currentPage() + 2); $page++)
        @if($page === $paginator->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $paginator->url($page) }}">{{ $page }}</a>@endif
    @endfor
    @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" aria-label="다음 페이지">&gt;</a>@else<span aria-disabled="true">&gt;</span>@endif
</nav>
@endif
