@extends('layouts.shop')
@section('shop_auth', '1')
@push('styles')<link rel="stylesheet" href="{{ asset('shop/css/auth.css') }}?v={{ filemtime(public_path('shop/css/auth.css')) }}">@endpush
@section('content')
<div class="shop-auth-wrap"><section class="shop-auth-card" aria-labelledby="shop-entry-title">
    @include('shop.partials.auth_brand')
    <div class="shop-auth-content">
        <p class="shop-auth-eyebrow">PRIVATE SHOP</p><h1 id="shop-entry-title">Shop 채널 입장</h1>
        @if($shop)
            <p class="shop-auth-description">{{ $shop->is_public ? '입장 확인 후 채널을 이용하실 수 있습니다.' : '안내받은 휴대폰번호와 입장코드를 입력해 주세요.' }}</p>
            <form action="{{ route('shop.gate.submit') }}" method="POST" class="shop-auth-form">
                @csrf
                <input type="hidden" name="channel" value="{{ $channelCode }}">
                <input type="hidden" name="product" value="{{ old('product', $productId ?? '') }}">
                @if(!$shop->is_public)
                    <label for="entry-phone">핸드폰번호</label><input id="entry-phone" name="phone" type="tel" inputmode="tel" value="{{ is_string(old('phone')) ? old('phone') : '' }}" placeholder="010-0000-0000" autocomplete="tel" maxlength="30" required>
                    <label for="entry-code">입장코드</label><input id="entry-code" name="entry_code" type="password" placeholder="안내받은 입장코드" autocomplete="off" maxlength="128" required>
                @endif
                <button type="submit" class="shop-auth-primary">입장 확인</button>
            </form>
        @else
            <p class="shop-auth-description" role="status">현재 입장할 수 있는 Shop 채널이 없습니다. 안내받은 Shop 채널 주소로 다시 접속해 주세요.</p>
        @endif
        <a class="shop-auth-market" href="{{ url('/') }}">Me9 Market 바로가기 <span aria-hidden="true">↗</span></a>
    </div>
</section></div>
@endsection
