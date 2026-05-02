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

                        {{-- 검색 폼 --}}
                        <form method="POST" action="{{ route('front.member.find_pw') }}">
                            @csrf
                            <div class="f_bx">
                                <input class="mt0" type="text" name="username" placeholder="아이디" value="{{ old('username') }}" required>
                                <input type="text" name="email" placeholder="대표이메일" value="{{ old('email') }}" required>
                                <button type="submit" class="btn col2" style="border:none; cursor:pointer; width:100%;">비밀번호 찾기</button>

                                {{-- 결과 영역 --}}
                                <div class="ans">
                                    @if(isset($result))
                                        @if($result['type'] === 'success')
                                            {{-- 임시비밀번호 발급 성공 --}}
                                            <div class="txt" style="margin-top:20px; padding:20px; background:#f0f7ff; border:1px solid #d0e3f7; border-radius:5px; text-align:center;">
                                                <p style="margin-bottom:10px; font-weight:bold;">임시비밀번호가 발급되었습니다.</p>
                                                <p style="font-size:20px; font-weight:bold; color:#2563eb; letter-spacing:2px; margin-bottom:10px;">{{ $result['temp_password'] }}</p>
                                                <p style="font-size:13px; color:#666;">로그인 후 반드시 비밀번호를 변경해 주세요.</p>
                                            </div>
                                        @else
                                            {{-- 비밀번호 찾기 실패 --}}
                                            <div class="txt" style="margin-top:20px; padding:20px; background:#fff5f5; border-radius:5px; text-align:center; color:#e00;">
                                                <p>{{ $result['message'] }}</p>
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