@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
@php
    $product = $shopProduct->product;
    $price = $shopProduct->selling_price ?: $shopProduct->product_price;
    $galleryImages = collect();
    if ($product) {
        if (!empty($product->product_image)) {
            $galleryImages->push($product->product_image);
        }
        if ($product->relationLoaded('images')) {
            foreach ($product->images as $image) {
                if (!empty($image->image)) {
                    $galleryImages->push($image->image);
                }
            }
        }
    }
    $galleryImages = $galleryImages->filter()->unique()->values();
    $mainImageName = $galleryImages->first();
    $mainImageUrl = $mainImageName ? asset('front/images/product_images/large/' . $mainImageName) : asset('front/images/product_images/small/no-image.png');
@endphp
<div style="background: #f6f7f9; min-height: 100vh; padding-bottom: 50px;">
    <div style="background: #111827; color: #fff; padding: 22px 32px;">
        <div style="max-width: 1180px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin: 0; font-size: 24px;">{{ $shop->channel_name }}</h1>
            <a href="{{ route('shop.products_list') }}" style="color: #fff; text-decoration: none; font-weight: 800;">상품 목록</a>
        </div>
    </div>

    <main style="max-width: 1180px; margin: 28px auto; padding: 0 20px;">
        @if(session('flash_message_success'))
            <div style="background: #dcfae6; color: #087443; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">{{ session('flash_message_success') }}</div>
        @endif

        <div style="display: grid; grid-template-columns: minmax(280px, 0.9fr) minmax(320px, 1.1fr); gap: 24px;">
            <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 24px;">
                <div style="height: 360px; background: #eef1f5; border-radius: 6px; overflow:hidden;">
                    <img src="{{ $mainImageUrl }}" alt="{{ $product->product_name ?? '상품 이미지' }}" style="display:block; width:100%; height:100%; object-fit:cover;">
                </div>
                @if($galleryImages->count() > 1)
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;">
                        @foreach($galleryImages as $imageName)
                            <div style="width:64px; height:64px; border:1px solid #d9dee7; border-radius:6px; overflow:hidden; background:#f2f4f7;">
                                <img src="{{ asset('front/images/product_images/small/' . $imageName) }}" alt="{{ $product->product_name ?? '상품 이미지' }}" style="display:block; width:100%; height:100%; object-fit:cover;">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 24px;">
                <div style="font-size: 13px; color: #667085; font-weight: 800;">{{ strtoupper($shopProduct->product_type) }} 상품</div>
                <h2 style="margin: 8px 0 12px; font-size: 30px;">{{ $product->product_name }}</h2>
                <p style="color: #667085;">{{ $product->description ?: '상품 상세 설명이 준비 중입니다.' }}</p>

                <div style="margin: 22px 0; padding: 18px; background: #f8fafc; border-radius: 6px;">
                    <div style="color: #667085; text-decoration: line-through;">공급가 {{ number_format($shopProduct->product_price) }}원</div>
                    <div style="font-size: 30px; font-weight: 900;">판매가 {{ number_format($price) }}원</div>
                    <div style="color: #667085; margin-top: 6px;">재고 {{ number_format($shopProduct->stock ?? 0) }}개 · 1회 구매제한 {{ $shopProduct->purchase_limit ?: '제한 없음' }}</div>
                </div>

                <form action="{{ route('front.shop.cart.add') }}" method="POST" style="display: grid; gap: 12px;">
                    @csrf
                    <input type="hidden" name="shop_product_id" value="{{ $shopProduct->id }}">
                    <label style="font-weight: 800;">옵션</label>
                    <select name="option" style="height: 44px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 10px;">
                        <option value="{{ $product->product_color }}/기본">{{ $product->product_color }} / 기본</option>
                        <option value="{{ $product->product_color }}/추가옵션">{{ $product->product_color }} / 추가옵션</option>
                    </select>
                    <label style="font-weight: 800;">수량</label>
                    <input type="number" name="qty" value="1" min="1" max="{{ $shopProduct->purchase_limit ?: 99 }}" style="height: 44px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 10px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px;">
                        <button type="submit" style="height: 48px; border: 1px solid #111827; border-radius: 6px; background: #fff; font-weight: 900; cursor: pointer;">장바구니 담기</button>
                        <button type="submit" name="buy_now" value="1" style="height: 48px; border: 0; border-radius: 6px; background: #111827; color: #fff; font-weight: 900; cursor: pointer;">바로 구매</button>
                    </div>
                </form>
            </div>
        </div>

        <section style="margin-top: 24px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 24px;">
            <h3 style="margin-top: 0;">배송/교환/반품 안내</h3>
            <p style="color: #475467;">채널관리자가 설정한 취소/환불 정책과 배송비 설정은 이 영역에 연결됩니다. 현재 주문 생성 시 배송비는 30,000원 미만 2,500원으로 계산됩니다.</p>
        </section>
    </main>
</div>
@endsection
