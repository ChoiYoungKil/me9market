@extends('layouts.frontend')

@section('page_type', 'sub')

@php
    $dep1_id = "05";
    $dep1_tit = "로그인";
@endphp

@section('content')
    <div id="login">
        <div class="box box1">
            <div class="inner_bx">
                <ul class="tab_bx">
                    <li><a href="{{ route('front.member.find_id') }}">아이디 찾기</a></li>
                    <li><a href="{{ route('front.member.find_pw') }}" class="on">비밀번호 찾기</a></li>
                </ul>
                <form>
                    <div class="f_bx">
                        <input class="mt0" type="text" placeholder="아이디">
                        <input type="text" placeholder="대표이메일">
                        <a href="#" class="btn col2">비밀번호 찾기</a>
                        <div class="ans"></div>
                    </div>
                </form>
                <ul class="link_bx">
                    <li><a href="{{ route('front.member.login') }}">로그인</a></li>
                    <li><a href="{{ route('front.member.register.step1') }}">회원가입</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection