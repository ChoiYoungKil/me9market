<div class="shop-auth-brand">
    @if(isset($shop) && $shop->use_logo && $shop->logo_image)
        <img src="{{ asset($shop->logo_image) }}" alt="{{ $shop->channel_name }}">
        <p>{{ $shop->channel_name }}</p>
    @elseif(isset($shop))
        <strong>{{ $shop->channel_name }}</strong>
    @else
        <img src="{{ asset('shop/images/common/logo.png') }}" alt="Me9 Market">
    @endif
</div>
