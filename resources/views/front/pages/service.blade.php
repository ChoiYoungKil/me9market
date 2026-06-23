@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
<div id="container">
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info" style="padding: 40px 0; border-bottom: 1px solid #eee; margin-bottom: 45px;">
                    <h2 class="ttl" style="font-size: 32px; font-weight: 700; color: #111;">서비스 안내</h2>
                    <ul class="dep" style="display: flex; gap: 8px; font-size: 14px; color: #777; list-style: none; padding: 0; margin: 10px 0 0 0;">
                        <li>HOME</li>
                        <li>&gt;</li>
                        <li>서비스 안내</li>
                    </ul>
                </div>

                <div class="conbx" style="text-align: center; padding: 60px 20px;">
                    <div style="max-width: 800px; margin: 0 auto;">
                        <span style="font-size: 16px; color: #6366f1; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">ABOUT ME9 MARKET</span>
                        <h3 style="font-size: 28px; font-weight: 700; color: #222; margin: 15px 0 25px 0; line-height: 1.4;">
                            폐쇄형 쇼핑몰 분양 및 통합 유통을 위한<br>
                            혁신적인 메가 마켓 플랫폼
                        </h3>
                        <p style="font-size: 16px; color: #555; line-height: 1.8; margin-bottom: 40px;">
                            미구마켓(Me9 Market)은 제조사와 판매자를 다이렉트로 연결하여 유통 마진을 최소화하고,<br>
                            누구나 자신만의 폐쇄형 몰(Shop 채널)을 간편하게 분양받아 수익을 창출할 수 있는<br>
                            차세대 멀티벤더 이커머스 허브입니다.
                        </p>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: left;">
                            <div>
                                <h4 style="font-size: 18px; font-weight: 700; color: #111; margin: 0 0 10px 0;">01. 입장코드 가드</h4>
                                <p style="font-size: 14px; color: #666; margin: 0; line-height: 1.5;">폐쇄형 VIP 고객만을 위한 전용 입장 코드로 충성 고객 관리가 극대화됩니다.</p>
                            </div>
                            <div>
                                <h4 style="font-size: 18px; font-weight: 700; color: #111; margin: 0 0 10px 0;">02. 간편 채널 분양</h4>
                                <p style="font-size: 14px; color: #666; margin: 0; line-height: 1.5;">어려운 개발 없이 클릭 몇 번으로 로고, 배너, 정산율이 정의된 샵 채널이 생성됩니다.</p>
                            </div>
                            <div>
                                <h4 style="font-size: 18px; font-weight: 700; color: #111; margin: 0 0 10px 0;">03. 직관적인 정산</h4>
                                <p style="font-size: 14px; color: #666; margin: 0; line-height: 1.5;">실시간 주문 및 정산 관리 시스템을 통해 판매 수수료와 이익 분배가 투명하게 이루어집니다.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
