@if($paginator->hasPages())
    <nav class="market-pagination" aria-label="페이지 이동">
        @if($paginator->onFirstPage())<span aria-disabled="true">←</span>@else<a href="{{ $paginator->withQueryString()->previousPageUrl() }}" aria-label="이전 페이지">←</a>@endif
        @for($page = max(1, $paginator->currentPage() - 2); $page <= min($paginator->lastPage(), $paginator->currentPage() + 2); $page++)
            <a href="{{ $paginator->withQueryString()->url($page) }}" @if($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
        @endfor
        @if($paginator->hasMorePages())<a href="{{ $paginator->withQueryString()->nextPageUrl() }}" aria-label="다음 페이지">→</a>@else<span aria-disabled="true">→</span>@endif
    </nav>
@endif
