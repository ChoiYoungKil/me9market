@php
    $product = $shopProduct->product;
    $image = $product?->product_image ?: $product?->images->first()?->image;
    $imageUrl = $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png');
@endphp
<a class="shop-product-card" href="{{ route('shop.product_details', $shopProduct->id) }}">
    <img src="{{ $imageUrl }}" alt="{{ $product?->product_name }}" loading="lazy">
    <h2>{{ $product?->product_name }}</h2><div class="price">{{ number_format($shopProduct->selling_price ?: $shopProduct->product_price) }}원</div>
    @include('shop.partials.rating', ['product' => $product])
</a>
