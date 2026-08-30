@extends('layouts.shop')

@section('page_type', 'main')

@section('content')
@php($showDemoCredentials = config('shop_channel.show_demo_credentials', false))
<div style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; background: #f6f7f9; padding: 32px;">
    <div style="width: 100%; max-width: 460px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 32px;">
        <h1 style="margin: 0; font-size: 28px;">Shop 채널 입장</h1>
        <p style="margin: 8px 0 24px; color: #667085;">비공개 채널은 등록된 휴대폰으로 SMS 인증 후 입장합니다.</p>

        @if(session('flash_message_error'))
            <div style="background: #fee4e2; color: #b42318; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                {{ session('flash_message_error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fee4e2; color: #b42318; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('shop.gate.submit') }}" method="POST">
            @csrf
            <label for="entry_code" style="display: block; font-weight: 700; margin-bottom: 8px;">채널 코드</label>
            <input type="text" name="entry_code" id="entry_code" value="{{ old('entry_code', $channelCode ?: ($showDemoCredentials ? 'me9' : '')) }}" required
                style="width: 100%; height: 46px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 12px; font-size: 15px;">
            <label for="phone" style="display: block; font-weight: 700; margin: 14px 0 8px;">휴대폰번호 <span style="font-weight: 400; color: #667085;">(비공개 채널)</span></label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="010-1234-5678"
                style="width: 100%; height: 46px; border: 1px solid #cfd4dc; border-radius: 6px; padding: 0 12px; font-size: 15px;">
            <button type="button" id="requestOtp" style="width: 100%; height: 42px; margin-top: 10px; border: 1px solid #111827; border-radius: 6px; background: #fff; color: #111827; font-weight: 700; cursor: pointer;">
                SMS 인증번호 받기
            </button>
            <label for="otp" style="display: block; font-weight: 700; margin: 14px 0 8px;">인증번호 <span style="font-weight: 400; color: #667085;">(비공개 채널)</span></label>
            <input type="text" name="otp" id="otp" value="{{ old('otp') }}" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="6자리 인증번호"
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
<script>
$(function () {
    $('#requestOtp').on('click', function () {
        var $button = $(this);
        var channelCode = $('#entry_code').val().trim();
        var phone = $('#phone').val().trim();
        if (!channelCode || !phone) {
            alert('채널 코드와 휴대폰번호를 입력해 주세요.');
            return;
        }
        $button.prop('disabled', true);
        $.ajax({
            url: @json(route('shop.otp.request')),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                entry_code: channelCode,
                phone: phone
            },
            success: function (response) {
                alert(response.message);
                $('#otp').focus();
            },
            error: function (xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) || '인증번호 발송에 실패했습니다.');
            },
            complete: function () {
                $button.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
