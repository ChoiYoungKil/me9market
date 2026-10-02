@extends('layouts.shop')
@section('content')
<div id="contents"><div id="join" class="order_details">
    <div class="top_v"><h1 class="title">교환 관리</h1></div>
    @include('front.shop.partials.order_tabs')
    <div class="shop-inner">
        @include('front.shop.partials.order_filters')
        @include('front.shop.partials.order_list')
    </div>
</div></div>
@endsection
