{{-- 비밀번호 찾기 페이지 (PPT Slide 32~33 기반) --}}
@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="login">
                <div class="box box1">
                    <div class="inner_bx">
                        {{-- 탭 메뉴: 아이디 찾기 / 비밀번호 찾기 --}}
                        <ul class="tab_bx">
                            <li><a href="{{ route('front.member.find_id') }}">아이디 찾기</a></li>
                            <li><a href="{{ route('front.member.find_pw') }}" class="on">비밀번호 찾기</a></li>
                        </ul>

                        {{-- 재설정 링크 요청 폼 --}}
                        <form method="POST" action="{{ route('front.member.find_pw') }}">
                            @csrf
                            <div class="f_bx">
                                <input class="mt0" type="text" name="username" placeholder="아이디" value="{{ old('username') }}" required>
                                <input type="text" name="email" placeholder="대표이메일" value="{{ old('email') }}" required>
                                <button type="submit" class="btn col2" style="border:none; cursor:pointer; width:100%;">비밀번호 찾기</button>

                                <div class="ans">
                                    @if($errors->any())
                                        <div class="txt" style="margin-top:20px; padding:20px; background:#fff5f5; border-radius:5px; text-align:center; color:#e00;">
                                            <p>{{ $errors->first() }}</p>
                                        </div>
                                    @endif
                                    @if(isset($result))
                                        @if($result['type'] === 'success')
                                            <div class="txt" style="margin-top:20px; padding:20px; background:#f0f7ff; border:1px solid #d0e3f7; border-radius:5px; text-align:center;">
                                                <p style="margin-bottom:10px; font-weight:bold;">{{ $result['message'] }}</p>
                                                <p style="font-size:13px; color:#666;">메일의 링크는 일정 시간이 지나면 만료됩니다.</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </form>

                        {{-- 하단 링크 --}}
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
