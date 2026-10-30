@extends('layouts.channel')
@section('page_type','sub')
@php $dep1_id='01'; @endphp
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}?v={{ filemtime(public_path('channel_assets/css/shop-configuration.css')) }}">@endpush
@section('content')
<div id="contents"><div class="channel-shop"><header><p>MY CHANNEL</p><h1>판매상품 관리</h1><p>{{ $product->product_name }}</p></header>
<nav class="shop-tabs"><a href="{{ route('channel.shop_info',['id'=>$shopId]) }}">Shop채널 정보</a><a href="{{ route('channel.shop_product01',['shop_id'=>$shopId]) }}" aria-current="page">판매상품</a><a href="{{ route('channel.shop_community',['shop_id'=>$shopId]) }}">커뮤니티</a></nav>
@if($errors->any())<div class="shop-feedback error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="shop-panel"><h2>판매 설정 정보</h2><p>{{ $product->category_path }} · {{ $product->product_code }}</p><form method="POST" action="{{ route('channel.product.update',$shopProduct->id) }}">@csrf<div class="shop-fields">
<label>판매 가격 (원)<input type="number" min="0" step="1" name="selling_price" required value="{{ old('selling_price',(int)$shopProduct->selling_price) }}" @readonly($shopProduct->product_type!=='own'&&$product->price_constraint_enabled&&$product->price_constraint_type==='fixed')></label>
<label>지급 포인트<input name="reward_points" type="number" min="0" step="1" value="{{ old('reward_points',$shopProduct->reward_points??$product->reward_points??0) }}" @readonly($shopProduct->shopChannel->use_own_pg)></label>
<label>채널 재고 수량<input type="number" name="stock" min="0" step="1" value="{{ old('stock',$shopProduct->stock) }}"><small>비워두면 원상품의 재고 설정을 따릅니다.</small></label><label>1회 구매제한수량<input type="number" name="purchase_limit" min="0" step="1" value="{{ old('purchase_limit',$shopProduct->purchase_limit) }}"></label>
<fieldset><legend>판매상태</legend><label><input type="radio" name="status" value="1" @checked(old('status',$shopProduct->status)==1)>판매</label><label><input type="radio" name="status" value="0" @checked(old('status',$shopProduct->status)==0)>중지</label></fieldset></div><div class="shop-actions"><button type="submit">수정</button><a href="{{ route('channel.shop_product01',['shop_id'=>$shopId]) }}" class="secondary">목록</a></div></form></section></div></div>
@endsection
