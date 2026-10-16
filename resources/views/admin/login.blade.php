@extends('layouts.channel_login')
@section('title', 'Me9 market - 관리자 로그인')
@section('content')
<div id="login"><div class="box box1"><div class="inner_bx">
    <img src="{{ asset('master_assets/images/common/logo1.png') }}" class="logo" alt="Me9 market">
    @if(session('error_message'))<p class="portal-message error" role="alert">{{ session('error_message') }}</p>@endif
    @if($errors->any())<div class="portal-message error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form action="{{ route('admin.login.submit') }}" method="POST">@csrf
        <div class="f_bx">
            <input class="mt0" type="text" name="email" placeholder="아이디를 입력해 주세요" aria-label="아이디" value="{{ old('email') }}" required autofocus autocomplete="username">
            <input type="password" name="password" placeholder="비밀번호를 입력해 주세요" aria-label="비밀번호" required autocomplete="current-password">
            <ul class="chk01"><li><input type="checkbox" id="idSave" name="remember"><label for="idSave">아이디 저장</label></li></ul>
            <button type="submit" class="btn">LOGIN</button>
        </div>
    </form>
</div></div></div>
@endsection
