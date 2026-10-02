@extends('layouts.shop')
@section('content')
<div id="contents"><div class="product_list">
    <div class="top_v"><h1 class="title">{{ $shop->channel_name }} 상품</h1>
        <nav class="shop-categories" aria-label="상품 카테고리">
            <a href="{{ route('shop.products_list', request()->except('category', 'page')) }}" @if(!request('category')) aria-current="page" @endif>전체</a>
            @foreach($categories as $category)
                <a href="{{ route('shop.products_list', array_merge(request()->except('page'), ['category' => $category->id])) }}" @if((int) request('category') === $category->id) aria-current="page" @endif>{{ $category->category_name }}</a>
            @endforeach
        </nav>
    </div>
    <div class="box box1"><div class="inner_bx"><div id="board">
        <div class="list_top">
            <div class="count">총 <strong>{{ number_format($products->total()) }}</strong> 개</div>
            <form method="GET" action="{{ route('shop.products_list') }}" class="search_bx type2">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">
                <input type="search" name="search" value="{{ request('search') }}" maxlength="100" aria-label="상품 검색" placeholder="상품 검색">
                <button class="s_btn" type="submit" aria-label="검색" title="검색"></button>
            </form>
        </div>
        <nav class="shop-sort" aria-label="상품 정렬">
            @foreach(['rating'=>'별점순', 'latest'=>'신상품순', 'price_asc'=>'낮은가격순', 'price_desc'=>'높은가격순'] as $key=>$label)
                <a href="{{ route('shop.products_list', array_merge(request()->except('page'), ['sort'=>$key])) }}" @if(request('sort', 'latest') === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="shop-product-grid">@forelse($products as $shopProduct) @include('shop.partials.product_card') @empty<div class="shop-empty">조회된 상품이 없습니다.</div>@endforelse</div>
        @include('shop.partials.pagination', ['paginator' => $products])
    </div></div></div>
</div></div>
@endsection
