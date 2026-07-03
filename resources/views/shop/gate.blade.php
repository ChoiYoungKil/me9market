@extends('layouts.shop')

@section('page_type', 'main')

@section('content')
<div style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; background: #f6f7f9; padding: 32px;">
    <div style="width: 100%; max-width: 460px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 32px;">
        <h1 style="margin: 0; font-size: 28px;">Shop 채널 입장</h1>
        <p style="margin: 8px 0 24px; color: #667085;">발급받은 채널 코드 또는 비공개 입장 코드를 입력합니다.</p>

        @if(session('flash_message_error'))
            <div style="background: #fee4e2; color: #b42318; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                {{ session('flash_message_error') }}
            </div>
        @endif

        <form action="{{ route('shop.gate.submit') }}" method="POST">
            @csrf
            <label for="entry_code" style="display: block; font-weight: 700; margin-bottom: 8px;">입장 코드</label>
            <input type="password" name="entry_code" id="entry_code" value="me9" required
                style="width: 100%; height: 46px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 12px; font-size: 15px;">
            <button type="submit" style="width: 100%; height: 48px; margin-top: 18px; border: 0; border-radius: 6px; background: #111827; color: #fff; font-weight: 800; cursor: pointer;">
                인증 후 입장
            </button>
        </form>

        <div style="margin-top: 18px; padding: 12px; background: #f2f4f7; border-radius: 6px; color: #475467; font-size: 13px;">
            개발 기본 채널 코드는 <strong>me9</strong> 입니다.
        </div>
    </div>
</div>
@endsection
