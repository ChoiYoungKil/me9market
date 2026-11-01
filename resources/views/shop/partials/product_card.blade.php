@php
    $product = $shopProduct->product;
    $image = $product?->product_image ?: $product?->images->where('status', 1)->first()?->image;
    $imageUrl = $image ? asset('front/images/product_images/small/'.$image) : asset('front/images/product_images/small/no-image.png');
    $activeJoint = ($jointByProduct ?? collect())->get($product->id);
    $cardPrice = $activeJoint ? app(\App\Services\JointPurchasePricingService::class)->priceForQuantity($activeJoint, max(1, (int)$activeJoint->current_quantity))['unit_price'] : ($shopProduct->selling_price ?: $shopProduct->product_price);
@endphp
<a class="shop-product-card" href="{{ $activeJoint ? route('shop.joint_purchase_details', $activeJoint->id) : route('shop.product_details', $shopProduct->id) }}">
    <img src="{{ $imageUrl }}" alt="{{ $product?->product_name }}" loading="lazy">
    <h2>{{ $product?->product_name }}</h2><div class="price">{{ number_format($cardPrice) }}원</div>
    @if($activeJoint)<p>{{ $activeJoint->sale_name }}</p><p>{{ $activeJoint->purchase_code }}</p><p>{{ $activeJoint->start_date }} ~ {{ $activeJoint->end_date }}</p><span>상세보기</span>@endif
    @include('shop.partials.rating', ['product' => $product])
</a>
