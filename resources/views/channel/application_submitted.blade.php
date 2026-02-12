@extends('layouts.channel_login')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx" style="text-align: center; padding: 50px 0;">
                        <img src="{{ asset('channel_assets/images/common/logo1.png') }}" class="logo"
                            style="margin-bottom: 30px;">

                        <h2 style="font-size: 24px; margin-bottom: 20px;">가입 심사 중입니다</h2>
                        <p style="font-size: 16px; line-height: 1.6; color: #666; margin-bottom: 40px;">
                            판매자 가입 신청이 성공적으로 접수되었습니다.<br>
                            관리자 승인 후 Shop 채널 서비스를 이용하실 수 있습니다.<br>
                            심사 결과는 이메일로 안내해 드립니다.
                        </p>

                        <div class="btm_btn type2">
                            <a href="{{ route('channel.login') }}" class="col2"
                                style="display: inline-block; padding: 15px 40px; background: #333; color: #fff; text-decoration: none;">로그인
                                페이지로</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection