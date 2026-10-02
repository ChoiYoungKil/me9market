@extends('shop.monitor.layout')
@section('content')
<h1>모니터링 관리자 로그인</h1>
<form class="login" method="POST" action="{{ route('shop.monitor.login.submit') }}">@csrf
<label>로그인 ID<input name="login_id" maxlength="50" autocomplete="username" value="{{ old('login_id') }}" required></label>
<label>비밀번호<input type="password" name="password" maxlength="200" autocomplete="current-password" required></label>
<button type="submit">로그인</button>
</form>
@endsection
