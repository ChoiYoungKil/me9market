@php
    $product = $shopProduct->product;
    $imageName = $product->product_image ?? null;
    if (!$imageName && $product && $product->relationLoaded('images')) {
        $imageName = optional($product->images->first())->image;
    }
    $imageUrl = $imageName ? asset('front/images/product_images/small/' . $imageName) : asset('front/images/product_images/small/no-image.png');
@endphp

<a href="{{ route('shop.product_details', $shopProduct->id) }}" style="display: block; background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 16px; text-decoration: none; color: #151922;">
    <div style="height: 150px; background: #eef1f5; border-radius: 6px; overflow:hidden;">
        <img src="{{ $imageUrl }}" alt="{{ $product->product_name ?? '상품 이미지' }}" style="display:block; width:100%; height:100%; object-fit:cover;">
    </div>
    <div style="margin-top: 12px;">
        <div style="font-size: 12px; color: #667085;">{{ strtoupper($shopProduct->product_type) }}</div>
        <strong style="display: block; min-height: 44px;">{{ $product->product_name ?? '상품명 없음' }}</strong>
        <div style="margin-top: 8px;">
            <span style="color: #667085; text-decoration: line-through;">{{ number_format($shopProduct->product_price) }}원</span>
            <span style="font-size: 18px; font-weight: 900; margin-left: 6px;">{{ number_format($shopProduct->selling_price) }}원</span>
        </div>
    </div>
</a>
