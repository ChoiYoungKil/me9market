@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div style="background: #f6f7f9; min-height: 100vh; padding-bottom: 50px;">
    <div style="background: #111827; color: #fff; padding: 22px 32px;">
        <div style="max-width: 1180px; margin: 0 auto; display: flex; justify-content: space-between; gap: 20px; align-items: center;">
            <h1 style="margin: 0; font-size: 24px;">{{ $shop->channel_name }} 상품</h1>
            <a href="{{ route('front.shop.cart.index') }}" style="color: #fff; font-weight: 800; text-decoration: none;">장바구니</a>
        </div>
    </div>

    <main style="max-width: 1180px; margin: 28px auto; padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: end; gap: 16px; margin-bottom: 18px;">
            <div>
                <h2 style="margin: 0; font-size: 26px;">전체 판매상품</h2>
                <p style="margin: 6px 0 0; color: #667085;">자사상품, 공유상품, 제휴/부분공개 상품이 승인 상태에 따라 노출됩니다.</p>
            </div>
            <a href="{{ route('shop.channel_main') }}" style="color: #475467; font-weight: 800;">채널 홈</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
            @forelse($products as $shopProduct)
                @include('shop.partials.product_card', ['shopProduct' => $shopProduct])
            @empty
                <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 18px;">판매중인 상품이 없습니다.</div>
            @endforelse
        </div>
    </main>
</div>
@endsection
