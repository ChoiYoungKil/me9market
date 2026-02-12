@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="login">
                <div class="box box1">
                    <div class="inner_bx">
                        <ul class="tab_bx">
                            <li><a href="{{ route('front.member.find_id') }}" class="on">아이디 찾기</a></li>
                            <!-- We will update this route when we implement find_pw -->
                            <li><a href="{{ route('front.member.find_pw') }}">비밀번호 찾기</a></li>
                        </ul>
                        <form>
                            <div class="f_bx">
                                <input class="mt0" type="text" placeholder="회원번호">
                                <input type="text" placeholder="대표이메일">
                                <a href="#" class="btn col2">아이디 찾기</a>
                                <div class="ans">
                                    <!--<div class="txt"><p>찾으시는 아이디는  <span class="col2">id1234</span> 입니다.</p></div>-->
                                    <!--<div class="txt"><p>일치하는 정보가 없습니다.</p></div>-->
                                </div>
                            </div>
                        </form>
                        <ul class="link_bx">
                            <li><a href="{{ route('front.member.login') }}">로그인</a></li>
                            <li><a href="{{ route('front.member.register.member') }}">회원가입</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection