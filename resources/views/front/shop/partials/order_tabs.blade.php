<nav class="shop-tabs shop-order-tabs" aria-label="주문 내역">
    @foreach(['order' => '주문', 'cancel' => '취소', 'return' => '반품', 'exchange' => '교환'] as $type => $label)
        <a href="{{ route('front.shop.'.$type.'.details') }}" @if(request()->routeIs('front.shop.'.$type.'.details')) aria-current="page" @endif><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">@if($type==='order')<path d="M8 4H5v17h14V4h-3M9 3h6v4H9zM8 12h8M8 16h5"/>@elseif($type==='cancel')<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/>@elseif($type==='return')<path d="m8 4-5 5 5 5M3 9h10a7 7 0 0 1 0 14" transform="translate(0 -2)"/>@else<path d="M4 7h15m-4-4 4 4-4 4M20 17H5m4-4-4 4 4 4"/>@endif</svg><span>{{ $label }}</span></a>
    @endforeach
</nav>
