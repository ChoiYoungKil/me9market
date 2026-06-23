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
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; font-size: 12px; color: #aaa; text-align: left; line-height: 1.5; margin-bottom: 15px;">
                    <strong style="color: #fff;">💡 테스트 정보:</strong><br>
                    - 아이디: <code style="background: rgba(255,255,255,0.1); padding: 2px 4px; border-radius: 4px; font-weight: bold; color: #fff;">john@admin.com</code><br>
                    - 비밀번호: <code style="background: rgba(255,255,255,0.1); padding: 2px 4px; border-radius: 4px; font-weight: bold; color: #fff;">123456</code>
                </div>
                <form action="/channel/login" method="POST">
                    @csrf
                    <div class="f_bx">
                        <input class="mt0" type="text" name="email" value="john@admin.com" placeholder="아이디를 입력 해 주세요" required>
                        <input type="password" name="password" value="123456" placeholder="비밀번호를 입력 해 주세요" required>
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