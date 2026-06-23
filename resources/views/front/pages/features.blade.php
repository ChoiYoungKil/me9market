@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
<div id="container">
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info" style="padding: 40px 0; border-bottom: 1px solid #eee; margin-bottom: 45px;">
                    <h2 class="ttl" style="font-size: 32px; font-weight: 700; color: #111;">주요 기능</h2>
                    <ul class="dep" style="display: flex; gap: 8px; font-size: 14px; color: #777; list-style: none; padding: 0; margin: 10px 0 0 0;">
                        <li>HOME</li>
                        <li>&gt;</li>
                        <li>주요 기능</li>
                    </ul>
                </div>

                <div class="conbx" style="padding: 40px 20px;">
                    <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                        <div style="background: #f8fafc; padding: 30px; border-radius: 16px; border: 1px solid #eee;">
                            <h3 style="font-size: 22px; font-weight: 700; color: #111; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
                                <span style="background: #6366f1; color: white; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">1</span>
                                회원 및 채널 매니저 기능
                            </h3>
                            <p style="font-size: 15px; color: #666; line-height: 1.7; margin: 0;">
                                간편 회원가입 및 소셜 로그인 연동, 포인트 적립 제어 및 관리, 회원사별 방문 채널 로그 추적과 장바구니 QR코드 동적 매핑 기능을 기본 탑재하고 있습니다.
                            </p>
                        </div>

                        <div style="background: #f8fafc; padding: 30px; border-radius: 16px; border: 1px solid #eee;">
                            <h3 style="font-size: 22px; font-weight: 700; color: #111; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
                                <span style="background: #a855f7; color: white; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">2</span>
                                공동구매 관리 시스템
                            </h3>
                            <p style="font-size: 15px; color: #666; line-height: 1.7; margin: 0;">
                                채널 관리자 화면에서 특정 상품을 공동구매 전용으로 등록하고 최소 달성 수량, 기간을 지정하면 분양몰 프론트에 실시간 타이머 및 달성 현황 게이지가 생성되어 구매 전환율을 높입니다.
                            </p>
                        </div>

                        <div style="background: #f8fafc; padding: 30px; border-radius: 16px; border: 1px solid #eee;">
                            <h3 style="font-size: 22px; font-weight: 700; color: #111; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
                                <span style="background: #10b981; color: white; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">3</span>
                                발주사(공급망) 원스톱 포털
                            </h3>
                            <p style="font-size: 15px; color: #666; line-height: 1.7; margin: 0;">
                                입점 제조 및 배송담당자를 위한 전용 로그인 가드와 엑셀 기반 송장 일괄 업로드 처리 모듈을 지원하여 수많은 배송 정보를 실시간으로 관리자 패널과 동기화시킵니다.
                            </p>
                        </div>

                        <div style="background: #f8fafc; padding: 30px; border-radius: 16px; border: 1px solid #eee;">
                            <h3 style="font-size: 22px; font-weight: 700; color: #111; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
                                <span style="background: #f59e0b; color: white; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">4</span>
                                환경설정 및 정산 모듈
                            </h3>
                            <p style="font-size: 15px; color: #666; line-height: 1.7; margin: 0;">
                                채널별 배송 조건 및 정산율(비율/정액 방식) 커스터마이징, 취소/환불 복사본 정책 관리 기능을 세부적으로 지원하여 개별 소호몰 운영과 백오피스 업무를 대폭 축소해 줍니다.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
