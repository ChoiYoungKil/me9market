@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="login">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="ttl">로그인</div>
                        <p id="login-error" style="color:red; text-align:center; margin-bottom:10px;"></p>

                        <form id="loginForm" action="javascript:;" method="post">
                            @csrf
                            <div class="f_bx">
                                <input class="mt0" type="email" name="email" id="users-email" placeholder="아이디를 입력 해 주세요"
                                    required autofocus>
                                <p id="login-email" style="color:red;"></p>

                                <input type="password" name="password" id="users-password" placeholder="비밀번호를 입력 해 주세요"
                                    required>
                                <p id="login-password" style="color:red;"></p>

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
                            <li><a href="{{ url('/find/id') }}">아이디 찾기</a></li>
                            <li><a href="{{ url('/find/pw') }}">비밀번호 찾기</a></li>
                            <li><a href="{{ url('/register') }}">회원가입</a></li>
                        </ul>

                        <div class="sns_bx">
                            <div class="txt"><span>간편회원 로그인</span></div>
                            <ul>
                                <li class="icon1"><a href="{{ url()->current() }}">kakaotalk</a></li>
                                <li class="icon2"><a href="{{ url()->current() }}">naver</a></li>
                                <li class="icon3"><a href="{{ url()->current() }}">gmail</a></li>
                            </ul>
                        </div>
                        <a href="{{ url()->current() }}" class="btn2">비회원 구매조회</a>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection