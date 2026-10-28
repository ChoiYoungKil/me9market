@extends('layouts.market-sub')
@section('page_title', '기본정보')
@section('content')
    @include('front.member.register.form', ['step' => 1])
@endsection
