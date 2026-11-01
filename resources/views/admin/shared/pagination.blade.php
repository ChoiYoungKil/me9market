@if($paginator->total() > 0)
<nav class="admin-circle-pagination" aria-label="페이지 이동">
    @foreach([['처음',$paginator->url(1),$paginator->onFirstPage(),'first'],['이전',$paginator->previousPageUrl(),$paginator->onFirstPage(),'prev']] as [$label,$url,$disabled,$kind])
        @if($disabled)<span class="page-arrow {{ $kind }} disabled" aria-disabled="true" aria-label="{{ $label }}">{{ $label }}</span>
        @else<a class="page-arrow {{ $kind }}" href="{{ $url }}" aria-label="{{ $label }}">{{ $label }}</a>
        @endif
    @endforeach
    @foreach($elements as $element)
        @if(is_string($element))<span class="page-gap">…</span>
        @else
            @foreach($element as $page=>$url)
                @if($page===$paginator->currentPage())<span class="current" aria-current="page">{{ $page }}</span>
                @else<a href="{{ $url }}" aria-label="{{ $page }}페이지">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach
    @foreach([['다음',$paginator->nextPageUrl(),!$paginator->hasMorePages(),'next'],['마지막',$paginator->url($paginator->lastPage()),!$paginator->hasMorePages(),'last']] as [$label,$url,$disabled,$kind])
        @if($disabled)<span class="page-arrow {{ $kind }} disabled" aria-disabled="true" aria-label="{{ $label }}">{{ $label }}</span>
        @else<a class="page-arrow {{ $kind }}" href="{{ $url }}" aria-label="{{ $label }}">{{ $label }}</a>
        @endif
    @endforeach
</nav>
@endif
