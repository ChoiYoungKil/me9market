@extends('layouts.market-sub')
@section('page_title', '회원가입 완료')
@section('content')
<div class="market-shell member-flow member-complete" data-member-id="{{ $user->id }}">
    <div class="member-complete-mark" aria-hidden="true">✓</div>
    <header class="market-register-head"><p class="home-eyebrow">WELCOME TO ME9 MARKET</p><h1>함께할 준비,<br><em>가입 완료.</em></h1><p>{{ $isSocialMember ? '소셜회원' : '일반회원' }} 가입과 입력 정보가 저장되었습니다.</p></header>
    <dl class="member-facts"><div><dt>회원번호</dt><dd>{{ $memberNumber }}</dd></div><div><dt>기본정보</dt><dd>{{ $user->profile_completed_at ? '저장 완료' : '미입력' }}</dd></div><div><dt>회원사 정보</dt><dd>{{ $vendorDetails?->company_terms_accepted_at ? '저장 완료' : '미입력' }}</dd></div><div><dt>판매인증</dt><dd>{{ $vendorDetails?->status == 1 ? '인증완료' : ($vendorDetails?->seller_requested_at ? '인증 요청 접수 · 심사 대기' : '인증 미요청') }}</dd></div></dl>
    @if($vendorDetails?->seller_requested_at && $vendorDetails->status != 1)<p class="member-help">판매 권한 인증 요청이 접수되었습니다. 서류 심사와 승인 후 판매 권한이 부여됩니다.</p>@endif
    @if($vendorDetails?->status == 1 && $vendorDetails->seller_approved_at)<p class="member-help">판매자 승인 완료일: {{ $vendorDetails->seller_approved_at->format('Y-m-d H:i') }}</p>@endif
    <div class="member-actions"><a href="{{ route('front.member.register.step1') }}">기본정보 확인</a><a href="{{ route('front.member.register.step2') }}">회원사 정보 확인</a><a href="{{ route('front.member.register.step3') }}">판매자 정보 확인</a><a href="{{ route('mypage.dashboard') }}" class="member-primary">마이페이지</a><a href="{{ route('home') }}">메인으로</a></div>
</div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('me9market/css/member-registration.css').'?v='.filemtime(public_path('me9market/css/member-registration.css')) }}">@endpush
