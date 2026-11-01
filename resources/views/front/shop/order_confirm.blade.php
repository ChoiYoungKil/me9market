@extends('layouts.shop')
@section('content')
<div id="contents"><div id="login"><div class="box box1"><div class="inner_bx">
    <h1 class="ttl">주문조회</h1>
    <form action="{{ route('front.shop.order.confirm.submit') }}" method="POST"><div class="f_bx">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="주문자 이름" aria-label="주문자 이름" autocomplete="name" required maxlength="100">
        <label class="shop-lookup-label">주문자 연락처</label><div class="shop-phone-fields shop-lookup-phone"><select name="phone_prefix" aria-label="연락처 앞자리">@foreach(['010','011','016','017','018','019'] as $prefix)<option @selected(old('phone_prefix')===$prefix)>{{ $prefix }}</option>@endforeach</select><span>−</span><input name="phone_middle" aria-label="연락처 가운데자리" value="{{ old('phone_middle') }}" inputmode="numeric" maxlength="4" pattern="[0-9]{3,4}" required><span>−</span><input name="phone_last" aria-label="연락처 끝자리" value="{{ old('phone_last') }}" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" required></div>
        <button type="submit" class="btn">주문조회</button>
    </div></form>
    <div class="shop-member-links"><a href="{{ route('shop.login') }}">회원 로그인</a><a href="{{ route('shop.channel_main') }}">채널 홈</a></div>
</div></div></div></div>
@endsection
