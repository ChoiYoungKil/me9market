@extends('layouts.admin')

@section('page_type', 'sub')

@section('content')
<div id="contents">
    <div class="row">
        <div class="box box1">
            <div class="page_info">
                <div class="ttl">디자인 패턴 - 레이어 패턴 1형</div>
                <ul class="dep">
                    <li>HOME</li>
                    <li>디자인 패턴</li>
                    <li>대형 레이어 팝업</li>
                </ul>
            </div>

            <div class="conbx">
                <div class="con_w" style="text-align: center; padding: 100px 0;">
                    <div style="font-size: 48px; margin-bottom: 20px;">🖼️</div>
                    <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 10px;">딤처리 전체화면 대형 모달 입력폼</h2>
                    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 30px;">기획서 Slide 258에 명시된 최고관리자 대형 레이어 팝업 패턴 실물 구현입니다.</p>
                    <button type="button" onclick="openLargeModal()" class="btn01 type2" style="padding: 10px 30px; font-size: 15px; font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <span>대형 레이어 팝업 열기</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 대형 레이어 팝업 (Slide 258) -->
    <div id="large-layer-popup" class="popup_bx" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #111827; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; width: 90%; max-width: 900px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5); animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
            
            <!-- 팝업 헤더 -->
            <div style="padding: 24px 30px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.01);">
                <div>
                    <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #fff; font-family: 'Outfit', sans-serif;">신규 채널 파트너 등록 (대형 레이어 패턴 1형)</h3>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #64748b;">시스템 관리자로 승인할 파트너사 및 계정 정보를 상세 입력합니다.</p>
                </div>
                <button type="button" onclick="closeLargeModal()" style="background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</button>
            </div>

            <!-- 팝업 본문 (스크롤) -->
            <div style="padding: 30px; overflow-y: auto; flex: 1;">
                <form id="largeModalForm" onsubmit="submitLargeModal(event)">
                    <div style="display: flex; flex-direction: column; gap: 30px;">
                        
                        <!-- 1구역: 파트너 기본 정보 -->
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: #3b82f6; border-left: 3px solid #3b82f6; padding-left: 10px; margin-bottom: 15px;">1. 파트너 기업 정보</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">파트너사 상호명 *</label>
                                    <input type="text" required placeholder="예: (주)글로벌네트웍스" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); color: #fff; outline: none; box-sizing: border-box;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">사업자등록번호 *</label>
                                    <input type="text" required placeholder="000-00-00000" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); color: #fff; outline: none; box-sizing: border-box;">
                                </div>
                            </div>
                        </div>

                        <!-- 2구역: 마스터 관리자 계정 -->
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: #3b82f6; border-left: 3px solid #3b82f6; padding-left: 10px; margin-bottom: 15px;">2. 마스터 관리자 계정</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">로그인 ID (이메일) *</label>
                                    <input type="email" required placeholder="manager@partner.com" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); color: #fff; outline: none; box-sizing: border-box;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">초기 비밀번호 *</label>
                                    <input type="password" required placeholder="••••••••" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); color: #fff; outline: none; box-sizing: border-box;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">담당자 성명 *</label>
                                    <input type="text" required placeholder="홍길동" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); color: #fff; outline: none; box-sizing: border-box;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">담당자 연락처 *</label>
                                    <input type="text" required placeholder="010-0000-0000" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); color: #fff; outline: none; box-sizing: border-box;">
                                </div>
                            </div>
                        </div>

                        <!-- 3구역: 권한 범위 설정 -->
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: #3b82f6; border-left: 3px solid #3b82f6; padding-left: 10px; margin-bottom: 15px;">3. 권한 범위 설정</div>
                            <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; padding: 20px;">
                                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; color: #cbd5e1;">
                                        <input type="checkbox" checked style="width: 16px; height: 16px; accent-color: #3b82f6;"> 상품 관리 권한
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; color: #cbd5e1;">
                                        <input type="checkbox" checked style="width: 16px; height: 16px; accent-color: #3b82f6;"> 주문/배송 관리 권한
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; color: #cbd5e1;">
                                        <input type="checkbox" style="width: 16px; height: 16px; accent-color: #3b82f6;"> 정산 대시보드 권한
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; color: #cbd5e1;">
                                        <input type="checkbox" style="width: 16px; height: 16px; accent-color: #3b82f6;"> 고객센터 및 공지관리 권한
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- 팝업 푸터 -->
                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" onclick="closeLargeModal()" class="btn01 close_btn" style="padding: 12px 24px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); color: #cbd5e1; font-weight: 600; cursor: pointer; border-radius: 8px;">취소</button>
                        <button type="submit" class="btn01 type2" style="padding: 12px 24px; border: none; background: #3b82f6; color: #fff; font-weight: 700; cursor: pointer; border-radius: 8px;">신규 파트너사 등록</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openLargeModal() {
        $('#large-layer-popup').css('display', 'flex');
    }

    function closeLargeModal() {
        $('#large-layer-popup').hide();
    }

    function submitLargeModal(e) {
        e.preventDefault();
        alert('신규 파트너사가 대형 레이어 팝업 폼을 통해 정상 등록되었습니다!');
        closeLargeModal();
    }

    // Auto open on load for demonstration
    $(document).ready(function() {
        openLargeModal();
    });
</script>

<style>
@keyframes scaleUp {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
#large-layer-popup input[type="text"], #large-layer-popup input[type="email"], #large-layer-popup input[type="password"] {
    transition: all 0.2s;
}
#large-layer-popup input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
}
</style>
@endsection
