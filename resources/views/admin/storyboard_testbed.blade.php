@php
    $areas = [
        [
            'code' => 'RF-03',
            'title' => 'Shop 채널 페이지',
            'account' => null,
            'items' => [
                ['209-215', '채널 입장', '/shop-channel/gate', '공개·비공개 채널 입장'],
                ['216-217', '채널 메인', '/shop-channel/main', '배너, 소개, 상품과 공지'],
                ['218-227', '상품 / 공동구매', '/shop-channel/products', '상품 목록, 상세와 공동구매'],
                ['228-231', '장바구니 / 주문', '/shop/cart', '장바구니부터 주문 완료'],
                ['232-243', '주문관리', '/shop/order/details', '주문조회와 클레임 처리'],
                ['244-246', '채널 공지', '/shop-channel/notices', '채널별 공지 목록과 상세'],
            ],
        ],
        [
            'code' => 'RF-04',
            'title' => '발주사',
            'account' => 'distributor',
            'items' => [
                ['247-249', '발주사 로그인', '/distributor/login', '발주 담당자 인증'],
                ['250-253', '발주 대기', '/distributor/orders/pending', '대기 주문과 송장 엑셀 처리'],
                ['254-255', '발주 완료', '/distributor/orders/completed', '송장 등록 이후 주문 확인'],
            ],
        ],
        [
            'code' => 'RF-05',
            'title' => '전체 관리자',
            'account' => 'superadmin',
            'admin' => true,
            'items' => [
                ['256-260', '로그인 / 대시보드', '/admin/dashboard', '최고관리자 인증과 현황'],
                ['173-176', '전체 정산관리', '/admin/settlements', '정산 생성, 상세와 집행 현황'],
                ['POINTS', '채널 포인트 원장', '/admin/channel-points', '포인트 분배와 소진 확인'],
                ['261-265', '목록 / 상세 패턴', '/admin/sub03', '관리자 공통 화면 패턴'],
                ['266-270', '입력 / 레이어 / 로딩', '/admin/sub02', '입력과 처리 상태 패턴'],
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
        .review-table th:first-child { white-space:nowrap; }
        .review-table th:nth-child(2) { width:210px; }
        .review-table th:nth-child(4) { width:140px; }
        .review-table th:nth-child(5) { width:110px; }
        .review-table td { line-height:1.65; vertical-align:top; }
        .review-note { display:block; margin-top:6px; color:#7b5920; font-size:12px; }
        .badge { display:inline-block; padding:3px 7px; border-radius:4px; font-size:12px; background:#edf3f7; color:#425669; }
        .badge.reviewed { background:#e9f6ef; color:#087443; }
        .badge.excluded { background:#f0f0f3; color:#66717d; }
        .badge.deferred, .badge.partial { background:#fff2db; color:#8b5500; }
        .account p { font-size:12px; line-height:1.6; color:#55616e; }
        .review-tools button { min-height:40px; padding:8px 12px; border:1px solid #bcc6d0; border-radius:5px; background:#fff; color:#18202a; font:inherit; cursor:pointer; }
        .review-tools button:hover { background:#edf3f7; }
        .review-tools button:focus-visible, .review-log summary:focus-visible { outline:3px solid #1769aa; outline-offset:3px; }
        .review-date { display:block; margin-top:6px; color:#66717d; font-size:12px; }
        .review-log { margin-top:12px; border:1px solid #d5dbe1; border-radius:6px; background:#fafcfe; }
        .review-log summary { padding:10px 12px; color:#1769aa; font-weight:700; cursor:pointer; }
        .review-log[open] summary { border-bottom:1px solid #d5dbe1; }
        .review-log-body { padding:12px; }
        .review-log-body h4 { margin:16px 0 7px; color:#283b4d; font-size:13px; }
        .review-log-body p { margin:6px 0; }
        .review-log-body dl { margin:0; }
        .review-log-body dt { margin-top:8px; color:#66717d; font-weight:700; }
        .review-log-body dd { margin:2px 0 0; }
        .review-log-body ol, .review-log-body ul { padding-left:21px; margin:6px 0; }
        .review-log-body li { margin:8px 0; }
        .review-outcome { display:block; margin-top:4px; color:#24533d; }
        .review-limits { border-left:3px solid #c08a2c; padding-left:10px; }
        .review-sources { color:#66717d; font-size:12px; overflow-wrap:anywhere; }
        .review-scope { padding:8px 10px; background:#edf3f7; }
        .review-table tr:target { outline:2px solid #1769aa; outline-offset:-2px; }
        .no-matches { padding:20px; background:white; }
        [hidden] { display:none !important; }
    </style>
</head>
<body>
<header>
    <h1>Me9 스토리보드 통합 검수</h1>
    <p>PPT Ver3.0.0의 실제 슬라이드 번호 기준으로 1~208번 확인 기록과 화면 진입 경로를 정리했습니다.</p>
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
        <h2 style="margin-top:0;">PPT 1~208번 확인 기록 · {{ $reviewedAt }}</h2>
        <p>아래 상태는 해당 날짜의 로컬 수동 시연 기록입니다. 이 화면은 기능을 자동 실행하거나 현재 서버의 합격 여부를 판정하지 않습니다.</p>
        <p><strong>39번:</strong> 사용자 요청으로 실제 비밀번호 변경 보류. <strong>60번:</strong> 4.5점 선택·해제·저장 확인, 마우스 이동만으로 미리보기는 추가 확인 필요.</p>
        <p>취소·반품 환불은 모의 결제 검수 결과입니다. 실 PG 환불·택배사 회수/발송·문자/이메일 실전송은 별도 확인 범위입니다.</p>
        <p><strong>제외:</strong> 81번은 사용자 지시, 188번은 재설계, 190·191번은 삭제 화면으로 제외했습니다. <strong>추가 확인:</strong> 67번 카카오 연동·외부 계정 권한, 72번 운영 PG/수수료, 73번 실제 비밀번호 변경, 87~89번 PG·별도 계정 인증을 완료로 표시하지 않았습니다.</p>
        <p>65~208번의 검수일은 2026-10-31이며 1~64번의 이전 기록은 유지했습니다. 운영 DB 계정·상품·주문이 로컬 검수 데이터와 다를 수 있으므로 해당 서버의 등록 자료를 사용합니다.</p>
        <p><strong>시연 과정·결과 보기</strong>를 펼치면 당시의 사전 조건, 실제 조작 순서와 확인 결과, 수정 내용, 자동 테스트 및 미시연 범위를 확인할 수 있습니다. 상세 기록 정리일은 2026-11-01이며 새로운 시연을 수행한 날짜는 아닙니다.</p>
        <p>상세에 적힌 금액·건수·상태는 당시 로컬 검수 사례입니다. 위 계정 선택은 현재 서버의 진입 계정에만 적용되며 과거 시연 기록의 대상이나 결과를 바꾸지 않습니다. 원기록이 여러 번호를 묶은 경우 해당 범위를 그대로 표시했습니다.</p>
        <p><strong>다음 진행: PPT 209번 Shop 채널.</strong> 각 번호를 먼저 안내하고 화면 동작을 하나씩 확인합니다.</p>
    </section>
    <h2>1~208번 페이지별 화면과 시연 기록</h2>
    <div class="review-tools">
        <label for="review-search">페이지 검색</label><input id="review-search" type="search" placeholder="예: 58, 반품, 배송지">
        <label for="review-status">확인 상태</label><select id="review-status"><option value="all">전체</option><option value="reviewed">시연 확인 기록</option><option value="deferred">실제 변경 보류</option><option value="partial">추가 확인 있음</option><option value="document">문서/구분</option><option value="excluded">검수 대상 제외</option></select>
        <output id="review-count" aria-live="polite">{{ count($pages) }}개 페이지</output>
        <button type="button" id="review-expand">검색된 기록 모두 펼치기</button>
        <button type="button" id="review-collapse">기록 모두 접기</button>
    </div>
    <section class="section" aria-label="PPT 1~208번 페이지별 검수">
        <table class="review-table">
            <thead><tr><th>PPT 번호</th><th>화면</th><th>화면 안내와 시연 상세</th><th>확인 기록</th><th>이동</th></tr></thead>
            <tbody>
            @foreach($pages as $page)
                <tr id="ppt-{{ $page['number'] }}" data-review-page="{{ $page['number'] }}" data-status="{{ $page['status'] }}">
                    <td><strong>{{ $page['number'] }}번</strong></td>
                    <td>{{ $page['title'] }}</td>
                    <td>
                        {{ $page['steps'] }}@if($page['note'])<small class="review-note">{{ $page['note'] }}</small>@endif
                        @php($history = $page['history'])
                        <details class="review-log" data-review-history="{{ $page['number'] }}">
                            <summary aria-label="PPT {{ $page['number'] }}번 시연 상세 기록">{{ in_array($page['status'], ['document', 'excluded'], true) ? '구분·제외 근거 보기' : '시연 과정·결과 보기' }}</summary>
                            <div class="review-log-body">
                                <p class="review-scope"><strong>{{ $history['scope'] }}</strong> · 당시 기록을 정리한 내역입니다.</p>
                                <dl>
                                    <dt>원기록 날짜</dt><dd>{{ $history['date'] }}</dd>
                                    <dt>확인 환경</dt><dd>{{ $history['environment'] }}</dd>
                                    @if($history['cases'])<dt>사전 조건·사용 자료</dt><dd>{{ $history['precondition'] }}</dd>@endif
                                </dl>
                                @if($history['cases'])
                                    <h4>실제 시연 순서와 확인 결과</h4>
                                    <ol>@foreach($history['cases'] as $case)<li><strong>{{ $case['action'] }}</strong><span class="review-outcome">확인 결과: {{ $case['outcome'] }}</span></li>@endforeach</ol>
                                @else
                                    <p>이 번호에는 별도 기능 시연 결과를 기록하지 않았습니다. 아래 구분·제외 근거를 확인해 주세요.</p>
                                @endif
                                @if($history['corrections'])
                                    <h4>시연 중 발견·수정한 내용</h4>
                                    <ul>@foreach($history['corrections'] as $correction)<li>{{ $correction }}</li>@endforeach</ul>
                                @endif
                                @if($history['cases'])
                                    <h4>자동 테스트 기록 · {{ $history['automation_scope'] }} 묶음</h4>
                                    <p>{{ $history['automation'] }}</p>
                                    <p class="review-note">해당 묶음의 당시 검사 기록이며 이 페이지의 모든 항목을 브라우저에서 수행했다는 뜻은 아닙니다.</p>
                                @endif
                                <div class="review-limits">
                                    <h4>{{ in_array($page['status'], ['document', 'excluded'], true) ? '구분·제외 근거와 범위' : '미시연·보류·추가 확인' }}</h4>
                                    <ul>@foreach($history['limitations'] as $limitation)<li>{{ $limitation }}</li>@endforeach</ul>
                                </div>
                                <h4>원기록 근거</h4>
                                <ul class="review-sources">@foreach($history['sources'] as $source)<li>{{ $source }}</li>@endforeach</ul>
                                <p class="review-note">과거 기록입니다. 화면 열기·검색·펼치기만으로 계정이나 주문을 만들거나 같은 처리를 재실행하지 않습니다.</p>
                            </div>
                        </details>
                    </td>
                    <td><span class="badge {{ $page['status'] }}">{{ $page['status_label'] }}</span><small class="review-date">원기록 {{ $page['history']['date'] }}</small></td>
                    <td>@if($page['status']==='excluded')<span>대상 제외</span>@else<a class="open" href="{{ ($page['admin'] ? $adminUrl : $frontUrl).$page['path'] }}" target="_blank" rel="noopener noreferrer" aria-label="PPT {{ $page['number'] }}번 {{ $page['title'] }} 진입 화면 열기">진입 화면 열기</a>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p id="review-empty" class="no-matches" hidden>검색 조건에 맞는 페이지가 없습니다.</p>
    </section>
    <h2>209번 이후 다음 검수 대상 진입 링크</h2>
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
    const numberRange = query.match(/^(\d+)(?:\s*[-~]\s*(\d+))?$/);
    let count = 0;
    reviewRows.forEach(row => {
        const number = Number(row.dataset.reviewPage);
        const textMatches = numberRange ? number >= Number(numberRange[1]) && number <= Number(numberRange[2] || numberRange[1]) : !query || row.textContent.toLocaleLowerCase().includes(query);
        const matches = textMatches && (reviewStatus.value === 'all' || row.dataset.status === reviewStatus.value);
        row.hidden = !matches;
        if (matches) count++;
    });
    document.getElementById('review-count').textContent = count + '개 페이지';
    document.getElementById('review-empty').hidden = count !== 0;
}
reviewSearch.addEventListener('input', filterReviewPages);
reviewStatus.addEventListener('change', filterReviewPages);
document.getElementById('review-expand').addEventListener('click', () => {
    reviewRows.filter(row => !row.hidden).forEach(row => { row.querySelector('details').open = true; });
});
document.getElementById('review-collapse').addEventListener('click', () => {
    reviewRows.forEach(row => { row.querySelector('details').open = false; });
});
function revealReviewAnchor() {
    if (!/^#ppt-\d+$/.test(window.location.hash)) return;
    const row = document.getElementById(window.location.hash.slice(1));
    if (row) row.querySelector('details').open = true;
}
window.addEventListener('hashchange', revealReviewAnchor);
revealReviewAnchor();
</script>
</body>
</html>
