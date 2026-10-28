@extends('layouts.market-sub')
@section('page_title', '회원사 정보')
@section('content')
    @include('front.member.register.form', ['step' => 2])
@endsection
