@extends('layouts.shop')
@section('page_type', 'main')
@section('content')
<div id="contents">
    <h1 class="shop-brand">{{ $shop->channel_name }}</h1>
    @if($shop->use_banner && count($shop->banner_images ?? []))
        <section id="sec01" aria-label="{{ $shop->channel_name }} 배너">
            <div class="shop-banner-slider" data-carousel>
                @foreach($shop->banner_images as $banner)
                    <img class="shop-banner" data-slide src="{{ asset($banner) }}" alt="{{ $shop->channel_name }} 배너 {{ $loop->iteration }}" @if(!$loop->first) hidden loading="lazy" @endif>
                @endforeach
                @if(count($shop->banner_images) > 1)
                    <button type="button" class="shop-slide-arrow prev" data-slide-prev aria-label="이전 배너" title="이전 배너"></button>
                    <button type="button" class="shop-slide-arrow next" data-slide-next aria-label="다음 배너" title="다음 배너"></button>
                    <div class="shop-slide-controls"><span data-slide-count aria-live="polite">1 / {{ count($shop->banner_images) }}</span><button type="button" data-slide-pause aria-label="자동 재생 일시정지" title="자동 재생 일시정지"></button></div>
                @endif
            </div>
        </section>
    @endif
    <section id="sec02">
        <div class="shop-inner">
            <h2 class="top_ttl">HOT TREND</h2>
            <div class="hashtag_bx"><ul>@foreach($shop->keywords ?? [] as $keyword)<li><a href="{{ route('shop.products_list', ['search'=>$keyword]) }}">#{{ $keyword }}</a></li>@endforeach</ul></div>
            <div class="shop-product-grid">
                @forelse($products as $shopProduct) @include('shop.partials.product_card') @empty<div class="shop-empty">판매중인 상품이 없습니다.</div>@endforelse
            </div>
            <div class="btm_btn"><a class="btn01" href="{{ route('shop.products_list') }}"><span>전체 상품 보기</span></a></div>
        </div>
    </section>
    @if($jointPurchases->isNotEmpty())
        <section class="shop-inner"><h2 class="ttl01">진행중인 공동구매</h2><div class="shop-product-grid">@foreach($jointPurchases as $joint) @include('shop.partials.joint_card') @endforeach</div></section>
    @endif
</div>
@endsection
