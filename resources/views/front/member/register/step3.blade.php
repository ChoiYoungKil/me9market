@extends('layouts.market-sub')
@section('page_title', '판매자 정보')
@section('content')
    @include('front.member.register.form', ['step' => 3])
@endsection
