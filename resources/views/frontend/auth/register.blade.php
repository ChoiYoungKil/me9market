@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="step_bx">
                            <ul>
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 1</p>
                                            <strong>기본정보</strong>
                                        </div>
                                    </div>
                                    <div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>
                                </li>
                                <li>
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="round">
                                        <div>
                                            <p>Step 3</p>
                                            <strong>판매자권한</strong>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        @if(Session::has('error_message'))
                            <div class="alert alert-danger" style="color:red; margin-bottom:10px;">
                                {{ Session::get('error_message') }}
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" style="color:red; margin-bottom:10px;">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ url('/register') }}">
                            @csrf
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <div class="f_w">
                                            <div class="f_ttl">회원정보입력</div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>이름</span></th>
                                                            <td><input type="text" name="name" value="" required="required">
                                                            </td>
                                                            <th class="w160"><span>성별</span></th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="gender" id="gender_m"
                                                                            value="m" checked="">
                                                                        <label for="gender_m">남성</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="gender" id="gender_w"
                                                                            value="w">
                                                                        <label for="gender_w">여성</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td colspan="3">
                                                                <div class="r_btn_w w457">
                                                                    <div class="tel_bx">
                                                                        <select name="mobile[]" required="required">
                                                                            <option value="" disabled=""></option>
                                                                            <option value="010" selected="">010</option>
                                                                        </select>
                                                                        <span>-</span>
                                                                        <input type="text" name="mobile[]" class="tel1"
                                                                            required="required" value="">
                                                                        <span>-</span>
                                                                        <input type="text" name="mobile[]" class="tel2"
                                                                            required="required" value="">
                                                                    </div>
                                                                    <a href="{{ url()->current() }}" class="btn01 col2">본인인증</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>이메일</span></th>
                                                            <td colspan="3">
                                                                <div class="email_bx">
                                                                    <input type="text" name="email_prefix" class="email1"
                                                                        required="required" value="">
                                                                    <span>@</span>
                                                                    <input type="text" name="email_suffix" class="email2"
                                                                        required="required" value="">
                                                                    <select class="off" required="required"
                                                                        onchange="this.previousElementSibling.value=this.value">
                                                                        <option value="">직접입력</option>
                                                                        <option value="naver.com">naver.com</option>
                                                                        <option value="gmail.com">gmail.com</option>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>비밀번호</span></th>
                                                            <td colspan="3">
                                                                <input type="password" name="password"
                                                                    placeholder="비밀번호를 입력해주세요." required>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- Terms section omitted for brevity, can be added if needed -->
                                    </div>
                                    <div class="btm_btn type2">
                                        <a href="{{ url()->current() }}" class="col3">취소</a>
                                        <button type="submit" class="col2">저장</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection