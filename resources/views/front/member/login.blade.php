@extends('layouts.frontend')

@section('page_type', 'sub')

@php
    $dep1_id = "05";
    $dep1_tit = "로그인";
@endphp

@section('content')
    @php($showDemoCredentials = config('shop_channel.show_demo_credentials', false))
    <div id="login">
        <div class="box box1">
            <div class="inner_bx">
                <div class="ttl">로그인</div>
                @if (Session::has('error_message'))
                    <div style="color: #ff4d4d; font-size: 0.85rem; text-align: center; margin-bottom: 15px;">
                        {{ Session::get('error_message') }}
                    </div>
                @endif
                @if (Session::has('success_message'))
                    <div style="color: #2ed573; font-size: 0.85rem; text-align: center; margin-bottom: 15px;">
                        {{ Session::get('success_message') }}
                    </div>
                @endif
                @if($showDemoCredentials)
                    <div style="background: rgba(0,0,0,0.03); border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 12px; color: #475569; text-align: left; line-height: 1.5; margin-bottom: 15px;">
                        <strong>테스트 정보:</strong><br>
                        - 아이디: <code style="background: #e2e8f0; padding: 2px 4px; border-radius: 4px; font-weight: bold; color: #000;">user@user.com</code><br>
                        - 비밀번호: <code style="background: #e2e8f0; padding: 2px 4px; border-radius: 4px; font-weight: bold; color: #000;">123456</code>
                    </div>
                @endif
                <form action="/member/login" method="POST">
                    @csrf
                    <div class="f_bx">
                        <input class="mt0" type="text" name="login_id" value="{{ $showDemoCredentials ? 'user@user.com' : '' }}" placeholder="아이디를 입력해 주세요" required>
                        <input type="password" name="password" value="{{ $showDemoCredentials ? '123456' : '' }}" placeholder="비밀번호를 입력해 주세요" required>
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
