<div class="row"><div class="box box1">
    <div class="page_info"><h1 class="ttl">{{ $catalogTitle }}</h1><ul class="dep"><li>HOME</li><li>{{ $catalogSection ?? '상품관리' }}</li><li>{{ $catalogTitle }}</li></ul></div>
    @if(!empty($showCatalogTabs))
        <nav class="shop-tabs" aria-label="상품관리 메뉴">
            @foreach(['own'=>'자사상품', 'public'=>'공유상품', 'partial'=>'제휴상품'] as $key=>$label)
                <a href="{{ route('channel.product_'.$key) }}" @if($scope === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    @endif
</div></div>
