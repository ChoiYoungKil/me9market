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
                <div class="ttl">로그인</div>
                <form action="{{ route('front.member.login.submit') }}" method="POST">
                    @csrf
                    <div class="f_bx">
                        <input class="mt0" type="text" name="login_id" placeholder="아이디 또는 이메일을 입력 해 주세요" required>
                        <input type="password" name="password" placeholder="비밀번호를 입력 해 주세요" required>
                        <ul class="chk01">
                            <li>
                                <input type="checkbox" id="idSave" name="remember">
                                <label for="idSave">아이디 저장</label>
                            </li>
                        </ul>
                        <button type="submit" class="btn">LOGIN</button>
                    </div>
                </form>
                <ul class="link_bx">
                    <li><a href="{{ route('front.member.find_id') }}">아이디 찾기</a></li>
                    <li><a href="{{ route('front.member.find_pw') }}">비밀번호 찾기</a></li>
                    <li><a href="{{ route('front.member.register.member') }}">회원가입</a></li>
                </ul>
                <div class="sns_bx">
                    <div class="txt"><span>간편회원 로그인</span></div>
                    <ul>
                        <li class="icon1"><a href="#">kakaotalk</a></li>
                        <li class="icon2"><a href="#">naver</a></li>
                        <li class="icon3"><a href="#">gmail</a></li>
                    </ul>
                </div>
                <a href="#" class="btn2">비회원 구매조회</a>
            </div>
        </div>
    </div>

@endsection