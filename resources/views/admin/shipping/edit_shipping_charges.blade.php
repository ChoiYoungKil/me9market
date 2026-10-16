@extends('layouts.admin')
@section('content')
<div id="contents"><div class="row"><div class="box">
    <div class="page_info"><h1 class="ttl">배송비 정책 수정</h1><ul class="dep"><li>홈</li><li>배송비 설정</li><li>정책 수정</li></ul></div>
    @include('partials.delivery_messages')
    <p class="portal-description">판매자: {{ $charge->vendor?->name }} · {{ $charge->name }}</p>
    <form class="delivery-editor" method="POST" action="{{ route('admin.shipping.edit', $charge->id) }}">@csrf
        @include('channel.sub00.inc.delivery_charge_form')
        <div class="btm_btn"><a class="btn01" href="{{ route('admin.shipping.index') }}">목록</a><button class="btn02" type="submit">저장</button></div>
    </form>
</div></div></div>
@endsection
