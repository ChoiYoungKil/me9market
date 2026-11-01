<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>발주사 로그인 | Me9 Market</title><link rel="stylesheet" href="{{ asset('common/distributor.css') }}"></head>
<body class="login-page"><main class="login-card"><img class="brand-logo" src="{{ asset('me9market/images/common/logo.png') }}" alt="Me9 Market"><p class="eyebrow">Me9 Market · PARTNER PORTAL</p><h1>발주사 로그인</h1><p class="muted">등록된 발주담당자 정보로 로그인해 주세요.</p>
@include('distributor.partials.messages')
<form method="post" action="{{ route('distributor.login.submit') }}">@csrf
<label for="name">발주담당자 성명</label><input id="name" name="name" required maxlength="100" autocomplete="name" value="{{ old('name') }}">
<label for="email">발주담당자 Email</label><input id="email" name="email" type="email" required maxlength="255" autocomplete="username" value="{{ old('email') }}">
<label for="password">비밀번호</label><input id="password" name="password" type="password" required maxlength="255" autocomplete="current-password">
<button class="btn primary" type="submit">로그인</button></form></main></body></html>
