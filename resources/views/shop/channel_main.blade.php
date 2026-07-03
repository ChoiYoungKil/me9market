@extends('layouts.shop')

@section('page_type', 'main')

@section('content')
<div style="background: #f6f7f9; min-height: 100vh;">
    <div style="background: #111827; color: #fff; padding: 22px 32px;">
        <div style="max-width: 1180px; margin: 0 auto; display: flex; justify-content: space-between; gap: 20px; align-items: center;">
            <div>
                <div style="font-size: 13px; color: #cbd5e1;">{{ $shop->channel_code }}</div>
                <h1 style="margin: 4px 0 0; font-size: 28px;">{{ $shop->channel_name }}</h1>
            </div>
            <nav style="display: flex; gap: 16px; font-weight: 700;">
                <a href="{{ route('shop.channel_main') }}" style="color: #fff; text-decoration: none;">홈</a>
                <a href="{{ route('shop.products_list') }}" style="color: #cbd5e1; text-decoration: none;">상품</a>
                <a href="{{ route('shop.joint_purchases_list') }}" style="color: #cbd5e1; text-decoration: none;">공동구매</a>
                <a href="{{ route('shop.notices') }}" style="color: #cbd5e1; text-decoration: none;">공지</a>
                <a href="{{ route('front.shop.cart.index') }}" style="color: #cbd5e1; text-decoration: none;">장바구니</a>
            </nav>
        </div>
    </div>

    @if(session('flash_message_success'))
        <div style="max-width: 1180px; margin: 18px auto 0; background: #dcfae6; color: #087443; padding: 12px 16px; border-radius: 6px;">
            {{ session('flash_message_success') }}
        </div>
    @endif

    <section style="max-width: 1180px; margin: 28px auto; padding: 0 20px;">
        <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 28px; margin-bottom: 24px;">
            <h2 style="margin: 0 0 8px; font-size: 26px;">채널 전용 상품과 공동구매를 한 곳에서 확인하세요</h2>
            <p style="margin: 0; color: #667085;">이 화면은 실제 `shop_channels`, `shop_channel_products`, `products`, `joint_purchases` 데이터를 읽습니다.</p>
        </div>

        <h3 style="font-size: 20px;">추천 상품</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
            @foreach($products as $shopProduct)
                @include('shop.partials.product_card', ['shopProduct' => $shopProduct])
            @endforeach
        </div>

        <h3 style="font-size: 20px; margin-top: 34px;">진행중인 공동구매</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            @forelse($jointPurchases as $joint)
                @include('shop.partials.joint_card', ['joint' => $joint])
            @empty
                <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 18px;">진행중인 공동구매가 없습니다.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
