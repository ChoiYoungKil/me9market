<nav class="shop-tabs" aria-label="주문 내역">
    @foreach(['order' => '주문', 'cancel' => '취소', 'return' => '반품', 'exchange' => '교환'] as $type => $label)
        <a href="{{ route('front.shop.'.$type.'.details') }}" @if(request()->routeIs('front.shop.'.$type.'.details')) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
