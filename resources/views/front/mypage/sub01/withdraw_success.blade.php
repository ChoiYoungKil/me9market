@extends('layouts.market-sub')
@section('page_title', '회원 탈퇴 완료')
@section('content')
<section class="market-shell member-flow member-complete">
    <div class="member-complete-mark" aria-hidden="true">✓</div>
    <header class="market-register-head"><p class="home-eyebrow">ME9 MARKET</p>
    <h1>회원 탈퇴 신청이 완료되었습니다.</h1>
    <p>{{ $receipt['date'] }} {{ $receipt['name'] }}({{ $receipt['username'] }})님의<br>회원 탈퇴 처리가 완료되었습니다.</p>
    <p>그동안 Me9 market을 이용해주셔서 감사합니다.</p></header>
    <a href="{{ url('/') }}" class="market-button">메인으로 이동</a>
</section>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('me9market/css/member-registration.css') }}">@endpush
