<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Me9 Market - 스토리보드 통합 테스트베드</title>
    <!-- Outfit & Inter Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: rgba(255, 255, 255, 0.08);
            --gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        header {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        header .subtitle {
            font-size: 14px;
            color: var(--text-muted);
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .hero {
            background: var(--gradient);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            filter: blur(50px);
            border-radius: 50%;
        }

        .hero h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .hero p {
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
            overflow-x: auto;
        }

        .tab-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 12px 24px;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .tab-btn:hover {
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .tab-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .section-panel {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .section-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-badge {
            background: rgba(99, 102, 241, 0.2);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .tb-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        th {
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .slide-badge {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
        }

        .id-badge {
            font-family: monospace;
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.completed {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .status-badge.mocked {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }

        .test-btn {
            background: var(--primary);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .test-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5);
        }

        .test-btn.secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-color);
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .test-btn.secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        footer {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-size: 13px;
            border-top: 1px solid var(--border);
            margin-top: 80px;
        }
    </style>
</head>
<body>

    <header>
        <div>
            <h1>Me9 Market Storyboard Portal</h1>
            <div class="subtitle">스토리보드 통합 테스트베드 (Unified Testbed)</div>
        </div>
        <a href="/" class="test-btn secondary">메인 홈으로 이동</a>
    </header>

    <div class="container">
        
        <div class="hero">
            <h2>스토리보드(Ver 2.0.2) 시뮬레이터</h2>
            <p>
                본 페이지는 기획서 스토리보드 기준 총 5개 대분류 영역의 각 화면(기개발 완료 화면 및 신규 모의 구현 화면)을 순차적으로 테스트하고 검증하기 위한 포털입니다.<br>
                <strong>미개발 화면군(모의 구현)</strong>은 실물 레이아웃 가이드를 기반으로 모의(Mock Up) 구현되어 흐름 시나리오 확인이 가능합니다.
            </p>
        </div>

        <!-- 테스트용 계정 정보 박스 -->
        <div class="card" style="background: rgba(30, 41, 59, 0.9); border: 2px solid var(--primary); padding: 25px; margin-bottom: 40px; border-radius: 20px;">
            <div style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <span>🔑 테스트 실행용 계정 및 조회 정보</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div style="background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 8px; font-size: 14px;">1. 비회원 주문조회</div>
                    <div style="font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                        - 주문번호: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">Me9-Shop-0032022</strong><br>
                        - 연락처: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">010-1234-5678</strong>
                    </div>
                </div>
                <div style="background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 8px; font-size: 14px;">2. 회원 영역 (마이페이지)</div>
                    <div style="font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                        - 아이디: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">user@user.com</strong><br>
                        - 비밀번호: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">123456</strong>
                    </div>
                </div>
                <div style="background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 8px; font-size: 14px;">3. 채널관리자</div>
                    <div style="font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                        - 아이디: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">john@admin.com</strong><br>
                        - 비밀번호: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">123456</strong>
                    </div>
                </div>
                <div style="background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 8px; font-size: 14px;">4. 발주사 페이지</div>
                    <div style="font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                        - 아이디: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">partner@main.com</strong><br>
                        - 비밀번호: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">123456</strong>
                    </div>
                </div>
                <div style="background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 8px; font-size: 14px;">5. 전체관리자</div>
                    <div style="font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                        - 아이디: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">admin@admin.com</strong><br>
                        - 비밀번호: <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">123456</strong>
                    </div>
                </div>
                <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3); grid-column: span 3;">
                    <div style="font-weight: 700; color: #10b981; margin-bottom: 8px; font-size: 14px;">📝 제휴/문의 등록 & 답변 확인 테스트 시나리오</div>
                    <div style="font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                        1. <strong>문의 등록</strong>: <u>1. 홈페이지 & 마이페이지</u> 탭에서 <strong>고객센터 > 제휴/문의 작성 (Slide 15)</strong> <a href="/contact" target="_blank" style="color: #6366f1; text-decoration: underline; font-weight: bold;">[테스트 실행]</a>으로 문의를 작성합니다.<br>
                        2. <strong>답변 작성 (전체관리자)</strong>: 최고관리자로 로그인하시거나 직접 <a href="/admin/contacts" target="_blank" style="color: #6366f1; text-decoration: underline; font-weight: bold;">[전체관리자 문의관리]</a> 페이지로 이동하여 로그인(admin@admin.com / 123456) 후 답변을 달아줍니다.<br>
                        3. <strong>내역 & 답변 확인</strong>: 로그인 상태에서 마이페이지의 <a href="/mypage/main" target="_blank" style="color: #6366f1; text-decoration: underline; font-weight: bold;">[마이페이지 대시보드]</a> 하단 '나의 제휴 및 상품 문의 내역'에서 등록한 문의와 관리자의 답변 내용을 확인합니다.
                    </div>
                </div>
            </div>
        </div>

        <!-- 5대 탭 분류 -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('sec1')">1. 홈페이지 & 마이페이지</button>
            <button class="tab-btn" onclick="switchTab('sec2')">2. 채널관리자</button>
            <button class="tab-btn" onclick="switchTab('sec3')">3. shop 채널</button>
            <button class="tab-btn" onclick="switchTab('sec4')">4. 발주사 페이지</button>
            <button class="tab-btn" onclick="switchTab('sec5')">5. 전체관리자 패턴</button>
        </div>

        <!-- 1. 홈페이지 & 마이페이지 -->
        <div id="sec1" class="section-panel active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">홈페이지 및 회원 마이페이지 영역</h3>
                    <span class="card-badge">일반 소비자 화면군</span>
                </div>
                <div class="tb-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>슬라이드</th>
                                <th>화면 ID</th>
                                <th>화면명</th>
                                <th>상태</th>
                                <th>테스트 링크</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="slide-badge">Slide 5</span></td>
                                <td><span class="id-badge">RF-01-01</span></td>
                                <td>소비자 공식 홈페이지 메인</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 6</span></td>
                                <td><span class="id-badge">RF-01-02</span></td>
                                <td>서비스안내 (소개)</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/service" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 8</span></td>
                                <td><span class="id-badge">RF-01-03</span></td>
                                <td>주요기능 (소개)</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/features" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 10</span></td>
                                <td><span class="id-badge">RF-01-04</span></td>
                                <td>가입안내 (소개)</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/subscription-information" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 12</span></td>
                                <td><span class="id-badge">RF-01-05-01</span></td>
                                <td>고객센터 > 공지사항 목록</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/notice" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 13</span></td>
                                <td><span class="id-badge">RF-01-05-02</span></td>
                                <td>고객센터 > 공지사항 상세 보기</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/notice" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 14</span></td>
                                <td><span class="id-badge">RF-01-05-03</span></td>
                                <td>고객센터 > 자주묻는질문(FAQ)</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/faq" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 15</span></td>
                                <td><span class="id-badge">RF-01-05-04</span></td>
                                <td>고객센터 > 제휴/문의 작성</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/contact" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 17</span></td>
                                <td><span class="id-badge">RF-01-06-01</span></td>
                                <td>비회원 주문조회 입력 페이지</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/nonmember/order/check" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 18-23</span></td>
                                <td><span class="id-badge">RF-01-06-02</span></td>
                                <td>비회원 주문 상세조회 & 취소/반품/교환 액션 모달</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/nonmember/order/details" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 26</span></td>
                                <td><span class="id-badge">RF-01-07-02</span></td>
                                <td>간편 약관 동의 및 소셜보완가입</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/member/social-join" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 34</span></td>
                                <td><span class="id-badge">RF-01-07-09</span></td>
                                <td>마이페이지 - 대시보드 및 문의사항 리스트</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/mypage/main" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 53</span></td>
                                <td><span class="id-badge">RF-01-07-18-X</span></td>
                                <td>마이페이지 주문 상세 액션 팝업군 (취소/반품/교환/확정/문의)</td>
                                <td><span class="status-badge completed">개발 완료 (통합)</span></td>
                                <td><a href="/mypage/order/list" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 58-60</span></td>
                                <td><span class="id-badge">RF-01-07-19~21</span></td>
                                <td>마이페이지 취소/반품/교환 처리이력 리스트</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/mypage/order/cancel-return-list" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. 채널관리자 -->
        <div id="sec2" class="section-panel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">채널 관리자 백오피스 영역</h3>
                    <span class="card-badge">판매자 전용 어드민</span>
                </div>
                <div class="tb-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>슬라이드</th>
                                <th>화면 ID</th>
                                <th>화면명</th>
                                <th>상태</th>
                                <th>테스트 링크</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="slide-badge">Slide 61</span></td>
                                <td><span class="id-badge">RF-02-01</span></td>
                                <td>채널관리자 로그인</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/channel/login" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 63</span></td>
                                <td><span class="id-badge">RF-02-02</span></td>
                                <td>대시보드 메인</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/channel" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 72-74</span></td>
                                <td><span class="id-badge">RF-02-04-01</span></td>
                                <td>Shop채널 목록 (상세검색, QR모달 팝업 적용됨)</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/channel/shop/list" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 124</span></td>
                                <td><span class="id-badge">RF-02-06-01</span></td>
                                <td>공동구매 상품 관리 목록 페이지</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/channel/joint-purchase/list" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 126</span></td>
                                <td><span class="id-badge">RF-02-06-02</span></td>
                                <td>공동구매 상품 등록 신청</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/channel/joint-purchase/create" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. shop 채널 -->
        <div id="sec3" class="section-panel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Shop 채널 분양몰 영역</h3>
                    <span class="card-badge">폐쇄형/분양몰 프론트</span>
                </div>
                <div class="tb-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>슬라이드</th>
                                <th>화면 ID</th>
                                <th>화면명</th>
                                <th>상태</th>
                                <th>테스트 링크</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="slide-badge">Slide 200</span></td>
                                <td><span class="id-badge">RF-03-01-01</span></td>
                                <td>입장코드(접속 비밀번호) 입력 게이트</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/gate" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 204</span></td>
                                <td><span class="id-badge">RF-03-02-01</span></td>
                                <td>간편 가입 절차 약관 동의 폼</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/register" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 206</span></td>
                                <td><span class="id-badge">RF-03-03-01</span></td>
                                <td>Shop 채널 분양몰 메인 대시보드</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/main" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 208</span></td>
                                <td><span class="id-badge">RF-03-04-01</span></td>
                                <td>일반 상품 목록 조회</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/products" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 210</span></td>
                                <td><span class="id-badge">RF-03-04-02-1</span></td>
                                <td>일반 상품 상세 정보 페이지 & 옵션/QA/판매고시 탭</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/products/1" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 215</span></td>
                                <td><span class="id-badge">RF-03-04-02-6</span></td>
                                <td>공동구매 진행 중인 상품 목록</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/joint-purchases" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 216</span></td>
                                <td><span class="id-badge">RF-03-04-02-7</span></td>
                                <td>공동구매 상품 상세 페이지 (달성 게이지 적용)</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/joint-purchases/1" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 234</span></td>
                                <td><span class="id-badge">RF-03-06-01</span></td>
                                <td>분양몰 내부 공지사항 목록</td>
                                <td><span class="status-badge mocked">모의 구현</span></td>
                                <td><a href="/shop-channel/notices" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. 발주사 페이지 -->
        <div id="sec4" class="section-panel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">발주사(제조사/공급업체) 전용 포털</h3>
                    <span class="card-badge">송장 입력 및 주문 발주 관리</span>
                </div>
                <div class="tb-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>슬라이드</th>
                                <th>화면 ID</th>
                                <th>화면명</th>
                                <th>상태</th>
                                <th>테스트 링크</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="slide-badge">Slide 238</span></td>
                                <td><span class="id-badge">RF-04-01-01</span></td>
                                <td>발주사 성명 및 이메일 로그인</td>
                                <td><span class="status-badge mocked">모의 구현 (완료)</span></td>
                                <td><a href="/distributor/login" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 240-242</span></td>
                                <td><span class="id-badge">RF-04-02-01</span></td>
                                <td>발주 대기 목록 (발주 처리 및 대기 상세정보 수정 모달 포함)</td>
                                <td><span class="status-badge mocked">모의 구현 (완료)</span></td>
                                <td><a href="/distributor/orders/pending" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 243-244</span></td>
                                <td><span class="id-badge">RF-04-02-02</span></td>
                                <td>발주 완료 목록 (배송정보 및 완료 상세 페이지 포함)</td>
                                <td><span class="status-badge mocked">모의 구현 (완료)</span></td>
                                <td><a href="/distributor/orders/completed" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. 전체관리자 패턴 -->
        <div id="sec5" class="section-panel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">전체 관리자(Super Admin) 디자인 패턴 프레임셋</h3>
                    <span class="card-badge">시스템 레이아웃 테마 가이드</span>
                </div>
                <div class="tb-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>슬라이드</th>
                                <th>화면 ID</th>
                                <th>패턴 가이드 화면명</th>
                                <th>상태</th>
                                <th>테스트 링크</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="slide-badge">Slide 247</span></td>
                                <td><span class="id-badge">RF-05-01</span></td>
                                <td>최고관리자 로그인 프레임</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/login" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 249</span></td>
                                <td><span class="id-badge">RF-05-02-01</span></td>
                                <td>관리자 전체 대시보드 3분할 레이아웃</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/dashboard" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 250</span></td>
                                <td><span class="id-badge">RF-05-02-02</span></td>
                                <td>디자인 패턴 - 일반 리스트 화면 (검색/일괄수정 적용)</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/sub03" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 251</span></td>
                                <td><span class="id-badge">RF-05-02-03</span></td>
                                <td>디자인 패턴 - 갤러리 카드 리스트 화면</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/sub03#gallery-section" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 252-253</span></td>
                                <td><span class="id-badge">RF-05-02-04~05</span></td>
                                <td>디자인 패턴 - 게시글 뷰어 + 댓글 및 댓글수정 모달</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/view" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 254</span></td>
                                <td><span class="id-badge">RF-05-02-06</span></td>
                                <td>디자인 패턴 - 2분할 레이아웃 및 Ajax 탭메뉴 프레임</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/sub01" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 255-256</span></td>
                                <td><span class="id-badge">RF-05-02-07~08</span></td>
                                <td>디자인 패턴 - 정보 입력폼 & 우측 소규모 레이어 팝업</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/sub02" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 257</span></td>
                                <td><span class="id-badge">RF-05-02-09</span></td>
                                <td>디자인 패턴 - 별도 팝업 윈도우(헤더포함) 형태 입력폼</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/newpage" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 258</span></td>
                                <td><span class="id-badge">RF-05-02-10</span></td>
                                <td>디자인 패턴 - 레이어 패턴 1형 (딤처리 전체화면 대형 모달 입력폼)</td>
                                <td><span class="status-badge mocked">모의 구현 (신규)</span></td>
                                <td><a href="/admin/sub/layer-large" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                            <tr>
                                <td><span class="slide-badge">Slide 259</span></td>
                                <td><span class="id-badge">RF-05-02-11</span></td>
                                <td>디자인 패턴 - 상태 진행창 (Ajax 처리중 딤처리 로딩)</td>
                                <td><span class="status-badge completed">개발 완료</span></td>
                                <td><a href="/admin/loading" class="test-btn" target="_blank">테스트 실행</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <footer>
        &copy; 2026 Me9 Market Development Team. All rights reserved.
    </footer>

    <script>
        function switchTab(tabId) {
            // Hide all panels
            document.querySelectorAll('.section-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            // Deactivate all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            // Show selected panel
            document.getElementById(tabId).classList.add('active');
            // Find the button and activate it
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
