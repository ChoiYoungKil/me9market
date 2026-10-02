@php($rating = min(5, max(0, (float) ($product->approved_ratings_avg_rating ?? 0))))
<div class="shop-rating" aria-label="평점 {{ number_format($rating, 1) }}점, 후기 {{ (int) $product->approved_ratings_count }}건">
    <span class="shop-stars" aria-hidden="true">@for($star = 1; $star <= 5; $star++)<span @class(['on' => $star <= round($rating)])></span>@endfor</span>
    <span>({{ number_format($product->approved_ratings_count ?? 0) }})</span>
</div>
