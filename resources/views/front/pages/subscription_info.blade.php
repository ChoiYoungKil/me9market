@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
<div id="container">
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info" style="padding: 40px 0; border-bottom: 1px solid #eee; margin-bottom: 45px;">
                    <h2 class="ttl" style="font-size: 32px; font-weight: 700; color: #111;">가입 안내</h2>
                    <ul class="dep" style="display: flex; gap: 8px; font-size: 14px; color: #777; list-style: none; padding: 0; margin: 10px 0 0 0;">
                        <li>HOME</li>
                        <li>&gt;</li>
                        <li>가입 안내</li>
                    </ul>
                </div>

                <div class="conbx" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); padding: 30px; text-align: center; color: white;">
                            <h3 style="font-size: 24px; font-weight: 700; margin: 0 0 10px 0;">간편 채널 분양 파트너 모집</h3>
                            <p style="margin: 0; font-size: 15px; opacity: 0.9;">미구마켓과 함께 성장할 판매자를 모십니다.</p>
                        </div>
                        <div style="padding: 40px;">
                            <h4 style="font-size: 18px; font-weight: 700; color: #111; margin: 0 0 20px 0; border-left: 4px solid #6366f1; padding-left: 10px;">가입 절차 안내</h4>
                            <div style="display: flex; flex-direction: column; gap: 15px;">
                                <div style="display: flex; gap: 15px; align-items: flex-start;">
                                    <div style="background: #f1f5f9; color: #6366f1; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; margin-top: 3px;">1</div>
                                    <div>
                                        <h5 style="font-size: 16px; font-weight: 700; color: #333; margin: 0 0 5px 0;">회원가입 및 로그인</h5>
                                        <p style="font-size: 14px; color: #666; margin: 0;">기본 이메일 또는 휴대폰 번호 기반 가입을 진행합니다.</p>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 15px; align-items: flex-start;">
                                    <div style="background: #f1f5f9; color: #6366f1; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; margin-top: 3px;">2</div>
                                    <div>
                                        <h5 style="font-size: 16px; font-weight: 700; color: #333; margin: 0 0 5px 0;">판매자 정보 작성 및 심사 요청</h5>
                                        <p style="font-size: 14px; color: #666; margin: 0;">상호명, 사업자 번호, 정산용 통장 사본을 등록하여 어드민 승인을 요청합니다.</p>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 15px; align-items: flex-start;">
                                    <div style="background: #f1f5f9; color: #6366f1; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; margin-top: 3px;">3</div>
                                    <div>
                                        <h5 style="font-size: 16px; font-weight: 700; color: #333; margin: 0 0 5px 0;">채널 분양 완료 및 상품 판매 개시</h5>
                                        <p style="font-size: 14px; color: #666; margin: 0;">승인 완료와 함께 본인의 전용 입장코드 및 Shop채널이 분양되어 판매가 개시됩니다.</p>
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 40px; text-align: center;">
                                <a href="/seller/register" style="background: #6366f1; color: white; padding: 12px 30px; border-radius: 8px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block; transition: background 0.2s;">판매자 가입하러 가기</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
