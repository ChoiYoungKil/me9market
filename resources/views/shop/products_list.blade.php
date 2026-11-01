@extends('layouts.shop')
@section('content')
<div id="contents"><div class="product_list">
    <div class="top_v"><h1 class="title">{{ $joint ? '공동구매 상품' : '일반상품' }}</h1>
        @if($hasJointPurchases)<nav class="shop-categories" aria-label="상품 유형"><a href="{{ route('shop.products_list') }}" @if(!$joint) aria-current="page" @endif>일반상품</a><a href="{{ route('shop.joint_purchases_list') }}" @if($joint) aria-current="page" @endif>공동구매</a></nav>@endif
    </div>
    <div class="box box1"><div class="inner_bx"><div id="board">
        <div class="list_top">
            <div class="count">총 <strong>{{ number_format($products->total()) }}</strong> 개</div>
            <form method="GET" action="{{ $joint ? route('shop.joint_purchases_list') : route('shop.products_list') }}" class="shop-catalog-search" data-category-filter>
                <input type="hidden" name="sort" value="{{ request('sort', $joint ? 'deadline' : 'latest') }}">
                @foreach([1=>'대분류',2=>'중분류',3=>'세분류'] as $level=>$label)<label>{{ $label }}<select class="shop-control" name="category{{ $level }}" data-category-level="{{ $level }}"><option value="">전체</option>@foreach($categoryTree as $category)@if(($level === 1 || filled(request('category'.($level-1)))) && (int)$category->parent_id === (int)($level === 1 ? 0 : request('category'.($level-1))))<option value="{{ $category->id }}" @selected((int)request('category'.$level)===$category->id)>{{ $category->category_name }}</option>@endif @endforeach</select></label>@endforeach
                <label>상품코드·상품제목<input class="shop-control" type="search" name="search" value="{{ request('search') }}" maxlength="100" aria-label="상품 검색" placeholder="상품코드, 상품제목"></label>
                <button class="shop-btn primary" type="submit">검색</button><a class="shop-btn" href="{{ $joint ? route('shop.joint_purchases_list') : route('shop.products_list') }}">초기화</a>
                <script type="application/json" data-category-tree>@json($categoryTree)</script>
            </form>
        </div>
        <nav class="shop-sort" aria-label="상품 정렬">
            @foreach(['rating'=>'별점순', ($joint?'deadline':'latest')=>($joint?'마감일순':'신상품순'), 'price_asc'=>'낮은가격순', 'price_desc'=>'높은가격순'] as $key=>$label)
                <a href="{{ route($joint ? 'shop.joint_purchases_list' : 'shop.products_list', array_merge(request()->except('page'), ['sort'=>$key])) }}" @if(request('sort', $joint ? 'deadline' : 'latest') === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="shop-product-grid">@forelse($products as $shopProduct) @include('shop.partials.product_card') @empty<div class="shop-empty list-empty-state">등록된 상품이 없습니다.</div>@endforelse</div>
        @include('shop.partials.pagination', ['paginator' => $products])
    </div></div></div>
</div></div>
@endsection
