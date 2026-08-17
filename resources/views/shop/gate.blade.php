@extends('layouts.shop')

@section('page_type', 'main')

@section('content')
@php($showDemoCredentials = config('shop_channel.show_demo_credentials', false))
<div style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; background: #f6f7f9; padding: 32px;">
    <div style="width: 100%; max-width: 460px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 32px;">
        <h1 style="margin: 0; font-size: 28px;">Shop 채널 입장</h1>
        <p style="margin: 8px 0 24px; color: #667085;">공개 채널은 채널 코드만, 비공개 채널은 휴대폰번호와 입장 코드를 입력합니다.</p>

        @if(session('flash_message_error'))
            <div style="background: #fee4e2; color: #b42318; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                {{ session('flash_message_error') }}
            </div>
        @endif

        <form action="{{ route('shop.gate.submit') }}" method="POST">
            @csrf
            <label for="entry_code" style="display: block; font-weight: 700; margin-bottom: 8px;">입장 코드</label>
            <input type="password" name="entry_code" id="entry_code" value="{{ old('entry_code', $showDemoCredentials ? 'me9' : '') }}" required
                style="width: 100%; height: 46px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 12px; font-size: 15px;">
            <label for="phone" style="display: block; font-weight: 700; margin: 14px 0 8px;">휴대폰번호 <span style="font-weight: 400; color: #667085;">(비공개 채널)</span></label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="010-1234-5678"
                style="width: 100%; height: 46px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 12px; font-size: 15px;">
            <button type="submit" style="width: 100%; height: 48px; margin-top: 18px; border: 0; border-radius: 6px; background: #111827; color: #fff; font-weight: 800; cursor: pointer;">
                인증 후 입장
            </button>
        </form>

        @if($showDemoCredentials)
        <div style="margin-top: 18px; padding: 12px; background: #f2f4f7; border-radius: 6px; color: #475467; font-size: 13px;">
            개발 기본 채널 코드는 <strong>me9</strong> 입니다.
        </div>
        @endif
    </div>
</div>
@endsection
