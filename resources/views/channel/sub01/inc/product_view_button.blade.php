@php
    $productViewData = [
        'id' => $shopProduct->id,
        'edit_url' => route('channel.product.edit', $shopProduct->id),
        'type_label' => $productTypeLabel,
        'code' => $product->product_code,
        'name' => $product->product_name,
        'category' => $categoryPath,
        'img' => $imageUrl,
        'price_constraint' => $constraintLabel,
        'profit_constraint' => number_format($shopProduct->profit).'원',
        'stock_text' => $shopProduct->stock === null ? '수량제한없음' : number_format($shopProduct->stock).'개',
        'purchase_limit' => $shopProduct->purchase_limit ? number_format($shopProduct->purchase_limit).'개' : '제한없음',
        'sales_period' => $product->stop_notice_at?->format('Y-m-d') ?: '없음',
        'selling_price' => number_format($shopProduct->selling_price),
    ];
@endphp
<button type="button" class="btn02 col5 product-view-button" data-product="{{ json_encode($productViewData) }}">보기</button>
