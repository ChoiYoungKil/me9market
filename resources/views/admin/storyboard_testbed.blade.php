@php
    $areas = [
        [
            'code' => 'RF-02',
            'title' => '채널 관리자',
            'account' => 'channel',
            'items' => [
                ['065 이후', '로그인 / 정보관리', '/channel/login', '판매자 인증과 기본정보 관리'],
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
        .accounts { display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 10px; }
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
        .review-summary { padding:18px; border:1px solid #d5dbe1; background:#fff; margin:20px 0; line-height:1.7; }
        .review-summary p { margin:6px 0; }
        .review-tools { display:flex; flex-wrap:wrap; gap:10px; margin:16px 0; align-items:center; }
        .review-tools input, .review-tools select { min-height:40px; padding:8px 12px; border:1px solid #bcc6d0; border-radius:5px; font:inherit; background:#fff; }
        .review-tools input { flex:1; min-width:200px; }
        .review-table th:nth-child(1) { width:75px; }
        .review-table th:nth-child(2) { width:210px; }
        .review-table th:nth-child(4) { width:140px; }
        .review-table th:nth-child(5) { width:110px; }
        .review-table td { line-height:1.65; vertical-align:top; }
        .review-note { display:block; margin-top:6px; color:#7b5920; font-size:12px; }
        .badge { display:inline-block; padding:3px 7px; border-radius:4px; font-size:12px; background:#edf3f7; color:#425669; }
        .badge.reviewed { background:#e9f6ef; color:#087443; }
        .badge.deferred, .badge.partial { background:#fff2db; color:#8b5500; }
        .account p { font-size:12px; line-height:1.6; color:#55616e; }
        .no-matches { padding:20px; background:white; }
        [hidden] { display:none !important; }
    </style>
</head>
<body>
<header>
    <h1>Me9 스토리보드 통합 검수</h1>
    <p>PPT Ver3.0.0의 실제 슬라이드 번호 기준으로 1~64번 확인 기록과 화면 진입 경로를 정리했습니다.</p>
</header>
<main>
    <div class="notice">전체 관리자 전용 검수 화면입니다. 링크를 열어도 현재 로그인 계정은 자동 전환되지 않습니다. 계정 생성·클레임 접수·탈퇴는 각 화면에서 직접 진행합니다.</div>

    <h2>현재 서버 DB에 등록된 검수 계정</h2>
    <p>활성 계정을 가입 방식과 회원·관리자 연결 정보로 구분합니다. 비밀번호는 기존 검수 서버 설정값이 선택한 DB 계정과 일치할 때 표시합니다. 계정의 비밀번호를 바꾸지 않습니다.</p>
    <form method="get" action="{{ route('admin.storyboard_testbed') }}">
        <div class="review-tools"><label for="account-search">계정 검색</label><input type="search" id="account-search" name="account_search" value="{{ request('account_search') }}" placeholder="등록된 이름 또는 이메일"><button class="button" type="submit" style="border:0; cursor:pointer;">검색 / 선택 적용</button></div>
        <p>각 목록은 검색 조건에 맞는 계정을 최대 100개 표시합니다. 선택한 계정은 로그인 세션을 바꾸지 않습니다.</p>
        <div class="accounts">
            @foreach($accounts as $account)
                <section class="account" data-account-role="{{ $account['key'] }}">
                    <h3>{{ $account['title'] }}</h3>
                    <label for="account-{{ $account['key'] }}">등록 계정 선택</label>
                    <select id="account-{{ $account['key'] }}" name="{{ $account['parameter'] }}" style="width:100%; min-height:38px; margin:8px 0 14px;" @disabled($account['options']->isEmpty())>
                        @forelse($account['options'] as $option)
                            <option value="{{ $option->id }}" @selected($option->id === $account['id'])>#{{ $option->id }} {{ $option->name }} · {{ $option->username ?: $option->email }}</option>
                        @empty<option value="">등록된 계정 없음</option>@endforelse
                    </select>
                    <dl>
                        <dt>DB 번호</dt><dd>{{ $account['id'] ?? '-' }}</dd>
                        <dt>이름</dt><dd>{{ $account['name'] ?: '-' }}</dd>
                        <dt>아이디</dt><dd>{{ $account['login_id'] }}</dd>
                        <dt>비밀번호</dt><dd>{{ $account['key'] === 'social' ? '소셜 인증 사용' : ($account['password'] !== '' ? $account['password'] : '기존 비밀번호 / 서버 설정 확인') }}</dd>
                        @if($account['key'] !== 'social')<dt>인증정보</dt><dd class="{{ $account['password_matches'] ? 'ok' : ($account['password_configured'] ? 'bad' : '') }}">{{ $account['password_matches'] ? '일치' : ($account['password_configured'] ? '선택 계정과 설정 비밀번호 불일치' : '검수 비밀번호 미설정') }}</dd>@endif
                        <dt>이메일</dt><dd>{{ $account['email'] ?: '-' }}</dd>
                        @if($account['member_number'])<dt>회원번호</dt><dd>{{ $account['member_number'] }}</dd>@endif
                        @if($account['providers'])<dt>소셜 연결</dt><dd>{{ $account['providers'] }}</dd>@endif
                        @if($account['vendor_id'])
                            <dt>회원사 번호</dt><dd>{{ $account['vendor_id'] }}</dd>
                            <dt>회원사</dt><dd>{{ $account['business_name'] ?: '-' }}</dd>
                            <dt>연결 관리자</dt><dd>{{ $account['owner_email'] ?: '-' }}</dd>
                        @endif
                        <dt>로그인 방식</dt><dd>{{ $account['auth_method'] }}</dd>
                        <dt>상태</dt><dd class="{{ $account['active'] ? 'ok' : 'bad' }}">{{ $account['exists'] ? ($account['active'] ? '활성' : '접근 기간 확인 필요') : '해당 서버에 등록된 계정 없음' }}</dd>
                    </dl>
                    <p>{{ $account['hint'] }}</p>
                    <a class="button" href="{{ $account['login_url'] }}" target="_blank" rel="noopener noreferrer">{{ $account['key'] === 'channel' ? '회원 화면에서 관리자 진입' : '로그인 화면 열기' }}</a>
                </section>
            @endforeach
        </div>
    </form>

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

    <section class="review-summary" aria-label="검수 범위와 다음 진행">
        <h2 style="margin-top:0;">PPT 1~64번 확인 기록 · {{ $reviewedAt }}</h2>
        <p>아래 상태는 해당 날짜의 로컬 수동 시연 기록입니다. 이 화면은 기능을 자동 실행하거나 현재 서버의 합격 여부를 판정하지 않습니다.</p>
        <p><strong>39번:</strong> 사용자 요청으로 실제 비밀번호 변경 보류. <strong>60번:</strong> 4.5점 선택·해제·저장 확인, 마우스 이동만으로 미리보기는 추가 확인 필요.</p>
        <p>취소·반품 환불은 모의 결제 검수 결과입니다. 실 PG 환불·택배사 회수/발송·문자/이메일 실전송은 별도 확인 범위입니다.</p>
        <p><strong>다음 진행: PPT 65번 채널관리자.</strong> 각 번호를 먼저 안내하고 화면 동작을 하나씩 확인합니다.</p>
    </section>
    <h2>1~64번 페이지별 화면과 확인 순서</h2>
    <div class="review-tools">
        <label for="review-search">페이지 검색</label><input id="review-search" type="search" placeholder="예: 58, 반품, 배송지">
        <label for="review-status">확인 상태</label><select id="review-status"><option value="all">전체</option><option value="reviewed">시연 확인 기록</option><option value="deferred">실제 변경 보류</option><option value="partial">추가 확인 있음</option><option value="document">문서/구분</option></select>
        <output id="review-count" aria-live="polite">{{ count($pages) }}개 페이지</output>
    </div>
    <section class="section" aria-label="PPT 1~64번 페이지별 검수">
        <table class="review-table">
            <thead><tr><th>PPT 번호</th><th>화면</th><th>진입 후 확인 순서</th><th>확인 기록</th><th>이동</th></tr></thead>
            <tbody>
            @foreach($pages as $page)
                <tr id="ppt-{{ $page['number'] }}" data-review-page="{{ $page['number'] }}" data-status="{{ $page['status'] }}">
                    <td><strong>{{ $page['number'] }}번</strong></td>
                    <td>{{ $page['title'] }}</td>
                    <td>{{ $page['steps'] }}@if($page['note'])<small class="review-note">{{ $page['note'] }}</small>@endif</td>
                    <td><span class="badge {{ $page['status'] }}">{{ $page['status_label'] }}</span></td>
                    <td><a class="open" href="{{ $frontUrl.$page['path'] }}" target="_blank" rel="noopener noreferrer" aria-label="PPT {{ $page['number'] }}번 {{ $page['title'] }} 진입 화면 열기">진입 화면 열기</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p id="review-empty" class="no-matches" hidden>검색 조건에 맞는 페이지가 없습니다.</p>
    </section>
    <h2>65번 이후 기존 기능 진입 링크</h2>
    <p>아래 영역은 다음 검수 대상입니다. 기존 링크와 번호는 후속 PPT 검수에서 다시 대조하며, 완료 상태를 의미하지 않습니다.</p>

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
<script>
const reviewSearch = document.getElementById('review-search');
const reviewStatus = document.getElementById('review-status');
const reviewRows = Array.from(document.querySelectorAll('[data-review-page]'));
function filterReviewPages() {
    const query = reviewSearch.value.trim().toLocaleLowerCase();
    let count = 0;
    reviewRows.forEach(row => {
        const matches = (!query || row.textContent.toLocaleLowerCase().includes(query)) && (reviewStatus.value === 'all' || row.dataset.status === reviewStatus.value);
        row.hidden = !matches;
        if (matches) count++;
    });
    document.getElementById('review-count').textContent = count + '개 페이지';
    document.getElementById('review-empty').hidden = count !== 0;
}
reviewSearch.addEventListener('input', filterReviewPages);
reviewStatus.addEventListener('change', filterReviewPages);
</script>
</body>
</html>
