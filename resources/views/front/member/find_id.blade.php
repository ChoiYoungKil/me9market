{{-- 아이디 찾기 페이지 (PPT Slide 32 기반) --}}
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
                            <li><a href="{{ route('front.member.find_id') }}" class="on">아이디 찾기</a></li>
                            <li><a href="{{ route('front.member.find_pw') }}">비밀번호 찾기</a></li>
                        </ul>

                        {{-- 검색 폼 --}}
                        <form method="POST" action="{{ route('front.member.find_id') }}">
                            @csrf
                            <div class="f_bx">
                                <input class="mt0" type="text" name="member_number" placeholder="회원번호 (예: M9-260401-0001)" value="{{ old('member_number') }}" required>
                                <input type="text" name="email" placeholder="대표이메일" value="{{ old('email') }}" required>
                                <button type="submit" class="btn col2" style="border:none; cursor:pointer; width:100%;">아이디 찾기</button>

                                {{-- 결과 영역 --}}
                                <div class="ans">
                                    @if(isset($result))
                                        @if($result['type'] === 'success')
                                            {{-- 아이디 찾기 성공 --}}
                                            <div class="txt" style="margin-top:20px; padding:20px; background:#f9f9f9; border-radius:5px; text-align:center;">
                                                <p>찾으시는 아이디는  <span class="col2" style="font-weight:bold; font-size:18px;">{{ $result['username'] }}</span> 입니다.</p>
                                            </div>
                                        @else
                                            {{-- 아이디 찾기 실패 --}}
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