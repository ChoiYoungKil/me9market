@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
@php($showDemoCredentials = config('shop_channel.show_demo_credentials', false))
<style>
    #container {
        padding-top: 100px;
    }

    .nonmember-check-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }

    @media all and (max-width: 1024px) {
        #container {
            padding-top: 70px;
        }
    }
</style>
<div id="container">
    <div id="contents">
        <div class="row" style="padding: 60px 0;">
            <div class="box box1" style="max-width: 500px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="font-size: 24px; font-weight: 700; color: #111; margin: 0 0 10px 0;">비회원 주문 조회</h2>
                    <p style="font-size: 14px; color: #666; margin: 0 0 15px 0;">주문 시 입력하신 주문번호와 연락처를 입력해 주세요.</p>
                    @if($showDemoCredentials)
                        <div style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 12px; color: #475569; text-align: left; line-height: 1.5;">
                            <strong>테스트 정보:</strong><br>
                            - 주문번호: <code style="background: #e2e8f0; padding: 2px 4px; border-radius: 4px; font-weight: bold; color: #000;">Me9-Shop-0032022</code><br>
                            - 연락처: <code style="background: #e2e8f0; padding: 2px 4px; border-radius: 4px; font-weight: bold; color: #000;">010-1234-5678</code>
                        </div>
                    @endif
                </div>

                @if(Session::has('flash_message_error'))
                    <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
                        {{ Session::get('flash_message_error') }}
                    </div>
                @endif

                <form action="{{ route('front.nonmember.order_check.submit') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label for="order_id" style="display: block; font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">주문번호</label>
                        <input type="text" id="order_id" name="order_id" value="{{ $showDemoCredentials ? 'Me9-Shop-0032022' : '' }}" placeholder="주문번호 입력" required class="nonmember-check-input">
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label for="phone" style="display: block; font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">연락처</label>
                        <input type="text" id="phone" name="phone" value="{{ $showDemoCredentials ? '010-1234-5678' : '' }}" placeholder="'-' 제외하고 입력" required class="nonmember-check-input">
                    </div>

                    <button type="submit" style="width: 100%; background: #6366f1; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s;">조회하기</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
