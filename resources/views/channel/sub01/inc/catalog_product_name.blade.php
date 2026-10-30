@php
    $imageName=basename($catalogProduct->images->first()?->image ?: ($catalogProduct->product_image??''));
    $imagePath='front/images/product_images/small/'.$imageName;
@endphp
<div class="product-identity">
@if($imageName&&is_file(public_path($imagePath)))<img class="product-thumbnail" src="{{ asset($imagePath) }}" alt="{{ $catalogProduct->product_name }}" loading="lazy">@else<span class="product-thumbnail no-image">이미지 없음</span>@endif
<div><small>{{ $catalogProduct->category_path }}</small><strong>{{ $catalogProduct->product_name }}</strong>@if($showCode??false)<small>{{ $catalogProduct->product_code }}</small>@endif</div>
</div>
