@extends('layouts.channel_login')

@section('content')
    <div id="login">
        <div class="box box1">
            <div class="inner_bx">
                <img src="{{ asset('channel_assets/images/common/logo1.png') }}" class="logo">
                @if (Session::has('error_message'))
                    <div style="color: #ff4d4d; font-size: 0.85rem; text-align: center; margin-bottom: 15px;">
                        {{ Session::get('error_message') }}
                    </div>
                @endif
                <form action="/channel/login" method="POST">
                    @csrf
                    <div class="f_bx">
                        <input class="mt0" type="text" name="email" placeholder="아이디를 입력 해 주세요" required>
                        <input type="password" name="password" placeholder="비밀번호를 입력 해 주세요" required>
                        <ul class="chk01">
                            <li>
                                <input type="checkbox" id="idSave">
                                <label for="idSave">아이디 저장</label>
                            </li>
                        </ul>
                        <button type="submit" class="btn" style="border:none; cursor:pointer;">LOGIN</button>
                    </div>
                </form>
                <div class="sns_bx">
                    <div class="txt"><span>간편회원 로그인</span></div>
                    <ul>
                        <li class="icon1"><a href="#">kakaotalk</a></li>
                        <li class="icon2"><a href="#">naver</a></li>
                        <li class="icon3"><a href="#">gmail</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#login").css("height", $(window).height());
            $(window).resize(function () {
                $("#login").css("height", $(window).height());
            });
        });
    </script>
@endsection