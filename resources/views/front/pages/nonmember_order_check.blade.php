@extends('layouts.market-sub')
@section('page_title', '비회원 주문조회')
@section('content')
@php
    $lookupValue = static fn ($key, $default = '') => is_string(old($key, $default)) ? old($key, $default) : $default;
@endphp
<div class="market-shell market-auth-layout market-order-lookup">
    <section class="market-auth-intro" aria-labelledby="order-lookup-title">
        <p class="home-eyebrow">CHECK YOUR ORDER</p>
        <h1 id="order-lookup-title">주문 내역을<br><em>확인하세요.</em></h1>
        <p>주문코드와 주문자 연락처로<br>비회원 구매 내역을 확인하실 수 있습니다.</p>
        <div class="auth-brand-mark" aria-hidden="true">Me9<span>market</span></div>
    </section>
    <section class="market-auth-form" aria-labelledby="order-form-title">
        <div class="auth-form-heading"><h2 id="order-form-title">주문조회</h2><p>주문할 때 입력한 정보를 입력해 주세요.</p></div>
        @if(Session::has('flash_message_error'))<p class="market-alert market-alert-error" role="alert">{{ Session::get('flash_message_error') }}</p>@endif
        @if($errors->any())
            <div class="market-alert market-alert-error" role="alert" id="order-lookup-errors">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <form action="{{ route('front.nonmember.order_check.submit') }}" method="POST" id="order-lookup-form">
            @csrf
            <div class="market-form-field">
                <label for="order_id">주문코드</label>
                <input type="text" id="order_id" name="order_id" value="{{ $lookupValue('order_id') }}" placeholder="예: Me9-009098" required maxlength="27" pattern="(?:[Mm][Ee]9-(?:[Ss][Hh][Oo][Pp]-)?)?[0-9]{1,18}" title="주문 완료 시 안내받은 주문코드를 입력해 주세요." autocomplete="off" autocapitalize="off" spellcheck="false" aria-describedby="order-code-help" @error('order_id') aria-invalid="true" @enderror>
                <p id="order-code-help" class="market-order-help">주문 완료 시 안내받은 코드를 입력해 주세요.</p>
            </div>
            <fieldset class="market-form-field market-order-phone">
                <legend>주문자 연락처</legend>
                <div class="market-order-phone-fields">
                    <label class="sr-only" for="phone_prefix">연락처 앞자리</label>
                    <select name="phone_prefix" id="phone_prefix" required autocomplete="tel-area-code">
                        @foreach(['010', '011', '016', '017', '018', '019'] as $prefix)<option value="{{ $prefix }}" @selected($lookupValue('phone_prefix', '010') === $prefix)>{{ $prefix }}</option>@endforeach
                    </select>
                    <span aria-hidden="true">-</span>
                    <label class="sr-only" for="phone_middle">연락처 중간자리</label>
                    <input type="text" id="phone_middle" name="phone_middle" value="{{ $lookupValue('phone_middle') }}" required inputmode="numeric" pattern="{{ $lookupValue('phone_prefix', '010') === '010' ? '[0-9]{4}' : '[0-9]{3,4}' }}" maxlength="4" placeholder="0000" autocomplete="tel-local-prefix" title="연락처 중간자리를 숫자로 입력해 주세요." @error('phone_middle') aria-invalid="true" @enderror>
                    <span aria-hidden="true">-</span>
                    <label class="sr-only" for="phone_last">연락처 끝자리</label>
                    <input type="text" id="phone_last" name="phone_last" value="{{ $lookupValue('phone_last') }}" required inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="0000" autocomplete="tel-local-suffix" title="연락처 끝자리 숫자 4자리를 입력해 주세요." @error('phone_last') aria-invalid="true" @enderror>
                </div>
            </fieldset>
            <button type="submit" class="market-button market-submit">주문조회 <span aria-hidden="true">↗</span></button>
        </form>
        <p class="market-order-help market-order-privacy">주문코드와 주문자 연락처가 모두 일치해야 조회할 수 있습니다.</p>
        <a class="market-nonmember" href="{{ route('front.member.login') }}">회원 로그인 <span aria-hidden="true">↗</span></a>
    </section>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('phone_prefix').addEventListener('change', function () {
    document.getElementById('phone_middle').pattern = this.value === '010' ? '[0-9]{4}' : '[0-9]{3,4}';
});
</script>
@endpush
