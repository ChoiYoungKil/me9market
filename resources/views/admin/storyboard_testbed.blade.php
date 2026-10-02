@php
    $frontUrl = rtrim(config('storyboard.front_url'), '/');
    $adminUrl = rtrim(config('storyboard.admin_url'), '/');
    $areas = [
        [
            'code' => 'RF-01',
            'title' => '사용자 / 마이페이지',
            'account' => 'member',
            'items' => [
                ['004', '메인', '/', '사용자 메인과 서비스 진입'],
                ['012-015', '고객센터', '/notice', '공지사항, FAQ, 제휴문의'],
                ['025-033', '로그인 / 회원가입', '/member/login', '로그인, 가입, 계정 찾기'],
                ['034-061', '마이페이지', '/mypage/main', '회원정보, 배송지, 포인트, 주문과 클레임'],
                ['017-023', '비회원 주문조회', '/nonmember/order/check', '비회원 주문과 사후 처리'],
            ],
        ],
        [
            'code' => 'RF-02',
            'title' => '채널 관리자',
            'account' => 'channel',
            'items' => [
                ['064-070', '로그인 / 정보관리', '/channel/login', '판매자 인증과 기본정보 관리'],
                ['072-104', 'Shop 채널 관리', '/channel/shop/list', '채널 등록, 설정, 상품과 공지'],
                ['106-123', '상품관리', '/channel/product/own', '자사, 공유, 제휴 상품 관리'],
                ['124-132', '공동구매관리', '/channel/joint-purchase/list', '구간 가격과 발주 담당자 설정'],
                ['134-162', '주문관리', '/channel/order/list', '주문, 송장, 취소, 반품, 교환'],
                ['168-172', '정산관리', '/channel/settlement/list', '채널 정산 집행내역 확인'],
                ['188-191', '포인트관리', '/channel/settings/points', '포인트 분배와 소진 원장'],
            ],
        ],
        [
            'code' => 'RF-03',
            'title' => 'Shop 채널 페이지',
            'account' => null,
            'items' => [
                ['200-202', '채널 입장', '/shop-channel/gate', '공개·비공개 채널 입장'],
                ['206', '채널 메인', '/shop-channel/main', '배너, 소개, 상품과 공지'],
                ['208-216', '상품 / 공동구매', '/shop-channel/products', '상품 목록, 상세와 공동구매'],
                ['217-220', '장바구니 / 주문', '/shop/cart', '장바구니부터 주문 완료'],
                ['222-232', '주문관리', '/shop/order/details', '주문조회와 클레임 처리'],
                ['234-235', '채널 공지', '/shop-channel/notices', '채널별 공지 목록과 상세'],
            ],
        ],
        [
            'code' => 'RF-04',
            'title' => '발주사',
            'account' => 'distributor',
            'items' => [
                ['238', '발주사 로그인', '/distributor/login', '발주 담당자 인증'],
                ['240-242', '발주 대기', '/distributor/orders/pending', '대기 주문과 송장 엑셀 처리'],
                ['243-244', '발주 완료', '/distributor/orders/completed', '송장 등록 이후 주문 확인'],
            ],
        ],
        [
            'code' => 'RF-05',
            'title' => '전체 관리자',
            'account' => 'superadmin',
            'admin' => true,
            'items' => [
                ['247-249', '로그인 / 대시보드', '/admin/dashboard', '최고관리자 인증과 현황'],
                ['173-176', '전체 정산관리', '/admin/settlements', '정산 생성, 상세와 집행 현황'],
                ['POINTS', '채널 포인트 원장', '/admin/channel-points', '포인트 분배와 소진 확인'],
                ['250-254', '목록 / 상세 패턴', '/admin/sub03', '관리자 공통 화면 패턴'],
                ['255-259', '입력 / 레이어 / 로딩', '/admin/sub02', '입력과 처리 상태 패턴'],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Me9 스토리보드 통합 검수</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #18202a; background: #f3f5f7; font-family: Arial, "Noto Sans KR", sans-serif; letter-spacing: 0; }
        header { background: #17212b; color: #fff; padding: 24px max(24px, calc((100% - 1440px) / 2)); }
        header h1 { margin: 0 0 8px; font-size: 26px; }
        header p { margin: 0; color: #cbd3da; font-size: 14px; }
        main { max-width: 1440px; margin: 0 auto; padding: 24px; }
        .notice { padding: 14px 16px; margin-bottom: 20px; border-left: 4px solid #c33b2f; background: #fff; color: #5a2520; }
        h2 { margin: 28px 0 10px; font-size: 19px; }
        .accounts { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .account { min-width: 0; padding: 14px; border: 1px solid #d5dbe1; border-radius: 6px; background: #fff; }
        .account h3 { margin: 0 0 12px; font-size: 16px; }
        .account dl { display: grid; grid-template-columns: 70px minmax(0, 1fr); gap: 7px; margin: 0 0 14px; font-size: 13px; }
        .account dt { color: #66717d; }
        .account dd { margin: 0; overflow-wrap: anywhere; }
        .ok { color: #087443; font-weight: 700; }
        .bad { color: #b42318; font-weight: 700; }
        .button { display: inline-block; min-height: 36px; padding: 9px 13px; border-radius: 5px; background: #1769aa; color: #fff; text-decoration: none; font-size: 13px; font-weight: 700; }
        .shop { display: flex; gap: 18px; align-items: center; justify-content: space-between; padding: 14px 16px; border: 1px solid #d5dbe1; background: #fff; }
        .shop p { margin: 4px 0; font-size: 13px; }
        .section { margin-bottom: 18px; border-top: 3px solid #283b4d; background: #fff; }
        .section-head { display: flex; align-items: baseline; gap: 12px; padding: 14px 16px; border-bottom: 1px solid #d5dbe1; }
        .section-head strong { color: #1769aa; }
        .section-head h3 { margin: 0; font-size: 17px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 11px 12px; border-bottom: 1px solid #e4e8ec; text-align: left; vertical-align: middle; font-size: 13px; overflow-wrap: anywhere; }
        th { background: #f7f8fa; color: #4b5661; }
        th:nth-child(1) { width: 100px; }
        th:nth-child(2) { width: 190px; }
        th:nth-child(4) { width: 100px; }
        tr:last-child td { border-bottom: 0; }
        .open { color: #1769aa; font-weight: 700; text-decoration: none; }
        @media (max-width: 900px) {
            .accounts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .shop { align-items: flex-start; flex-direction: column; }
            table, thead, tbody, tr, th, td { display: block; }
            thead { display: none; }
            tr { padding: 10px 12px; border-bottom: 1px solid #d5dbe1; }
            td { width: auto; padding: 4px 0; border: 0; }
        }
        @media (max-width: 560px) { .accounts { grid-template-columns: 1fr; } main { padding: 16px; } }
    </style>
</head>
<body>
<header>
    <h1>Me9 스토리보드 통합 검수</h1>
    <p>RF-01부터 RF-05까지 역할별 실제 로그인과 구현 화면을 확인합니다.</p>
</header>
<main>
    <div class="notice">이 페이지는 최고관리자 전용입니다. 검수 계정은 운영 기능 확인 목적으로만 사용하고 완료 후 비밀번호를 교체하십시오.</div>

    <h2>역할별 로그인 상태</h2>
    <div class="accounts">
        @foreach($accounts as $account)
            <section class="account">
                <h3>{{ $account['title'] }}</h3>
                <dl>
                    <dt>아이디</dt><dd>{{ $account['login_id'] }}</dd>
                    <dt>비밀번호</dt><dd>{{ $account['password'] !== '' ? $account['password'] : '서버 설정 필요' }}</dd>
                    <dt>계정</dt><dd class="{{ $account['exists'] ? 'ok' : 'bad' }}">{{ $account['exists'] ? '존재' : '없음' }}</dd>
                    <dt>활성</dt><dd class="{{ $account['active'] ? 'ok' : 'bad' }}">{{ $account['active'] ? '정상' : '확인 필요' }}</dd>
                    <dt>인증정보</dt><dd class="{{ $account['password_matches'] ? 'ok' : 'bad' }}">{{ $account['password_matches'] ? '일치' : '확인 필요' }}</dd>
                </dl>
                <a class="button" href="{{ $account['login_url'] }}" target="_blank" rel="noopener noreferrer">로그인 화면 열기</a>
            </section>
        @endforeach
    </div>

    <h2>Shop 채널 진입</h2>
    <section class="shop">
        @if($shopChannel)
            <div>
                <p><strong>{{ $shopChannel->channel_name }}</strong></p>
                <p>채널코드: {{ $shopChannel->channel_code }} · {{ $shopChannel->is_public ? '공개' : '비공개' }} · {{ $shopChannel->is_member_only ? '회원 전용' : '회원/비회원' }}</p>
            </div>
            <a class="button" href="{{ $frontUrl }}/shop-channel/enter/{{ urlencode($shopChannel->channel_code) }}" target="_blank" rel="noopener noreferrer">활성 채널 열기</a>
        @else
            <p class="bad">현재 활성 Shop 채널이 없습니다.</p>
        @endif
    </section>

    <h2>스토리보드 기능 점검</h2>
    @foreach($areas as $area)
        @php($baseUrl = !empty($area['admin']) ? $adminUrl : $frontUrl)
        <section class="section">
            <div class="section-head"><strong>{{ $area['code'] }}</strong><h3>{{ $area['title'] }}</h3></div>
            <table>
                <thead><tr><th>스토리보드</th><th>화면</th><th>검수 내용</th><th>이동</th></tr></thead>
                <tbody>
                @foreach($area['items'] as $item)
                    <tr>
                        <td>{{ $item[0] }}</td>
                        <td>{{ $item[1] }}</td>
                        <td>{{ $item[3] }}</td>
                        <td><a class="open" href="{{ $baseUrl . $item[2] }}" target="_blank" rel="noopener noreferrer">열기</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endforeach
</main>
</body>
</html>
