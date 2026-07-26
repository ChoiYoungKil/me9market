@php
    $sections = [
        [
            'id' => 'rf01',
            'code' => 'RF-01',
            'title' => '홈페이지 / 회원 / 마이페이지',
            'intent' => 'Me9 Market을 소개하고, 일반 회원이 가입부터 주문조회, 마이페이지 관리까지 수행하는 소비자 접점입니다.',
            'summary' => ['전체 서비스 소개', '고객센터', '회원가입/로그인', '비회원 주문조회', '마이페이지 주문/포인트/배송지'],
            'status' => '부분 구현',
            'accent' => '#2563eb',
            'items' => [
                ['slide' => '004', 'screen' => 'RF-01-01', 'name' => '메인', 'intent' => '대표 이미지, 회사 소개, 이용방법, 공지/회사정보를 한 화면 전환 방식으로 전달', 'url' => '/', 'state' => 'partial', 'note' => '메인 경로는 있으나 PPT의 한 화면 전환형 스크롤 연출은 별도 확인 필요'],
                ['slide' => '006', 'screen' => 'RF-01-02', 'name' => '서비스안내', 'intent' => '서비스 개념을 소개하는 정적 안내 페이지', 'url' => '/service', 'state' => 'mock', 'note' => '소개 화면 확인용'],
                ['slide' => '008', 'screen' => 'RF-01-03', 'name' => '주요기능', 'intent' => '플랫폼 핵심 기능 소개', 'url' => '/features', 'state' => 'mock', 'note' => '소개 화면 확인용'],
                ['slide' => '010', 'screen' => 'RF-01-04', 'name' => '가입안내', 'intent' => '가입 절차와 권한 단계 안내', 'url' => '/subscription-information', 'state' => 'mock', 'note' => '소개 화면 확인용'],
                ['slide' => '012-015', 'screen' => 'RF-01-05', 'name' => '고객센터', 'intent' => '공지사항, FAQ, 제휴/문의 등록 및 관리자 답변 흐름', 'url' => '/notice', 'state' => 'done', 'note' => '공지/FAQ/문의 라우트 구현됨'],
                ['slide' => '017-023', 'screen' => 'RF-01-06', 'name' => '비회원 주문조회', 'intent' => '주문코드와 연락처로 주문 상세, 취소/반품/교환/구매확정/문의 처리', 'url' => '/nonmember/order/check', 'state' => 'done', 'note' => '주문 상세와 상품별 취소/반품/교환/구매확정 흐름 구현됨'],
                ['slide' => '025-033', 'screen' => 'RF-01-07-01~08', 'name' => '로그인/회원가입/아이디·비밀번호 찾기', 'intent' => '일반 회원과 간편 회원의 가입 및 계정 복구', 'url' => '/member/login', 'state' => 'done', 'note' => '일반/소셜 보완가입 화면과 단계별 입력 흐름 구현됨'],
                ['slide' => '034-061', 'screen' => 'RF-01-07-09~22', 'name' => '마이페이지', 'intent' => '대시보드, 회원정보, 배송지, 탈퇴, 방문채널, 포인트, 장바구니, 찜, 주문/클레임 관리', 'url' => '/mypage/main', 'state' => 'done', 'note' => '주문/클레임/포인트/장바구니/찜 실제 데이터 연동 구현됨'],
            ],
            'flows' => [
                ['label' => '비회원 주문조회', 'steps' => ['주문조회 입력', '주문 상세 확인', '상품별 취소/반품/교환/구매확정', '판매자 문의 등록']],
                ['label' => '회원 주문관리', 'steps' => ['회원 로그인', '마이페이지 주문 목록', '주문 상세', '클레임/구매확정 처리']],
            ],
        ],
        [
            'id' => 'rf02',
            'code' => 'RF-02',
            'title' => '채널 관리자',
            'intent' => '판매자가 Shop 채널을 만들고 상품, 주문, 정산, 운영정책을 관리하는 백오피스입니다.',
            'summary' => ['채널 관리자 로그인', 'Shop 채널 등록/수정', '상품 추가/요청', '공동구매', '주문관리', '정산/정책/포인트'],
            'status' => '구현',
            'accent' => '#059669',
            'items' => [
                ['slide' => '064-067', 'screen' => 'RF-02-01~02', 'name' => '로그인 / 대시보드', 'intent' => '판매자 인증 후 주문상태와 매출 현황을 확인', 'url' => '/channel/login', 'state' => 'done', 'note' => 'admin guard 기반 판매자 로그인'],
                ['slide' => '069-070', 'screen' => 'RF-02-03', 'name' => '정보 관리', 'intent' => '채널 관리자 정보와 비밀번호 관리', 'url' => '/channel/settings/info', 'state' => 'done', 'note' => '기본 정보/비밀번호 변경 구현됨'],
                ['slide' => '072-087', 'screen' => 'RF-02-04-01~04', 'name' => 'Shop 채널 목록/등록/상세/수정', 'intent' => '공개여부, 입장코드, 키워드, 기간, 로고, 배너, OG, 관리자 정보 설정', 'url' => '/channel/shop/list', 'state' => 'done', 'note' => 'Shop 채널 목록/등록/상세/수정과 주요 DB 저장 경로 구현됨'],
                ['slide' => '088-104', 'screen' => 'RF-02-04-05~09', 'name' => 'Shop 채널 판매상품/공지', 'intent' => '자사/공유/제휴 상품을 채널에 붙이고 채널 공지 운영', 'url' => '/channel/shop/product01', 'state' => 'done', 'note' => 'ShopChannelProduct 연결과 채널 공지 CRUD 구현됨'],
                ['slide' => '106-123', 'screen' => 'RF-02-05', 'name' => '상품관리', 'intent' => '자사상품 등록, 공개/부분공개, 제약조건, 판매요청 관리', 'url' => '/channel/product/own', 'state' => 'done', 'note' => '자사/공유/제휴 상품 관리와 삭제 제한 로직 구현됨'],
                ['slide' => '124-132', 'screen' => 'RF-02-06', 'name' => '공동구매관리', 'intent' => '공동구매 상품 등록, 기간, 혜택, 발주 담당자 설정', 'url' => '/channel/joint-purchase/list', 'state' => 'done', 'note' => '공동구매 수량별 가격 구간 등록/수정, 누적 수량 도달 시 기존 주문 재가격 산정 구현됨'],
                ['slide' => '134-162', 'screen' => 'RF-02-07', 'name' => '주문관리', 'intent' => '주문 상세, 정상/취소/반품/교환 주문 처리와 송장 입력', 'url' => '/channel/order/list', 'state' => 'done', 'note' => 'Shop 주문 상태 동기화, 클레임/송장 처리, 배송 안내 SMS 포인트 차감, 공동구매 재결제 예정 차액 표시 구현됨'],
                ['slide' => '164-166', 'screen' => 'RF-02-08', 'name' => '정산관리', 'intent' => '기간별 정산 집행 현황과 상세 정산 확인', 'url' => '/channel/settlement/list', 'state' => 'done', 'note' => '구매확정 상품과 Shop 채널 요율 기준 정산 조회, 공동구매 재가격 line_total 기준 정산 반영 구현됨'],
                ['slide' => '168-197', 'screen' => 'RF-02-09~13', 'name' => '서브관리자/발주담당/포인트/배송비/환불정책', 'intent' => '운영 보조 계정과 정책성 데이터를 관리', 'url' => '/channel/settings/points', 'state' => 'done', 'note' => '판매자 포인트 구매/사용/SMS차감/환급 요청 원장과 정책 화면 데이터 흐름 구현됨'],
            ],
            'flows' => [
                ['label' => 'Shop 채널 생성', 'steps' => ['채널 로그인', 'Shop 채널 등록', '채널 정보 확인', '판매상품 추가', '채널 공지 등록']],
                ['label' => '판매자 주문 처리', 'steps' => ['주문 목록 확인', '주문 상세 팝업', '상태 변경', '송장 입력', '취소/반품/교환 목록 확인']],
            ],
        ],
        [
            'id' => 'rf03',
            'code' => 'RF-03',
            'title' => 'Shop 채널 프론트',
            'intent' => '구매자가 특정 Shop 채널에 입장해 상품을 보고 장바구니, 주문, 주문조회, 공지 확인을 수행하는 분양몰 화면입니다.',
            'summary' => ['입장코드', '간편회원 가입', '채널 메인', '상품 목록/상세', '장바구니/주문', '주문조회', '채널 공지'],
            'status' => '구현',
            'accent' => '#d97706',
            'items' => [
                ['slide' => '200-202', 'screen' => 'RF-03-01~02', 'name' => '입장코드 / 로그인 / 간편가입', 'intent' => '공개/비공개 채널 입장 및 간편 회원 가입', 'url' => '/shop-channel/gate', 'state' => 'done', 'note' => '입장코드가 ShopChannel DB와 세션에 연결됨'],
                ['slide' => '206', 'screen' => 'RF-03-03-01', 'name' => 'Shop 채널 메인', 'intent' => '배너, 상품 진입, 채널 소개, 공지 노출', 'url' => '/shop-channel/main', 'state' => 'done', 'note' => '채널/상품/공지/공동구매 데이터 노출 구현됨'],
                ['slide' => '208-216', 'screen' => 'RF-03-04', 'name' => '상품 목록/상세/공동구매', 'intent' => '일반상품과 공동구매상품 탐색, 옵션 선택, Q&A, 판매정보 확인', 'url' => '/shop-channel/products', 'state' => 'done', 'note' => 'ShopChannelProduct, 공동구매 수량별 현재 적용가/다음 구매 예상가 표시 구현됨'],
                ['slide' => '217-220', 'screen' => 'RF-03-04-03~06', 'name' => '장바구니 / 주문서 / 결제완료 / 주문내역', 'intent' => '채널 상품 구매 전환 흐름', 'url' => '/shop/cart', 'state' => 'done', 'note' => '세션 장바구니에서 orders/orders_products 생성, 공동구매 누적 수량 기준 가격 적용과 발주사 대기 목록 연동'],
                ['slide' => '222-232', 'screen' => 'RF-03-05', 'name' => '주문조회/주문관리', 'intent' => '채널 구매 주문의 취소/반품/교환/구매확정/문의', 'url' => '/shop/order/details', 'state' => 'done', 'note' => '실제 주문 상세와 취소/반품/교환/확정 상태 변경 구현됨'],
                ['slide' => '234-235', 'screen' => 'RF-03-06', 'name' => '채널 공지사항', 'intent' => 'Shop 채널 관리자가 등록한 공지 목록/상세', 'url' => '/shop-channel/notices', 'state' => 'done', 'note' => '채널별 공지 목록/상세 DB 연동 구현됨'],
            ],
            'flows' => [
                ['label' => '구매자 채널 이용', 'steps' => ['입장코드 입력', '채널 메인', '상품 상세', '장바구니', '주문서', '결제완료']],
                ['label' => '채널 주문 사후관리', 'steps' => ['주문조회', '주문 상세', '취소/반품/교환/확정', '상품 문의']],
            ],
        ],
        [
            'id' => 'rf04',
            'code' => 'RF-04',
            'title' => '발주사 페이지',
            'intent' => '공급업체/발주 담당자가 배송 대기 주문을 확인하고 송장 정보를 입력해 발주 완료로 넘기는 운영 화면입니다.',
            'summary' => ['발주 담당자 로그인', '발주 대기 목록', '엑셀 송장 업로드', '발주 상세/수정', '발주 완료 목록'],
            'status' => '구현',
            'accent' => '#7c3aed',
            'items' => [
                ['slide' => '238', 'screen' => 'RF-04-01-01', 'name' => '발주사 로그인', 'intent' => '발주 담당자 이메일/비밀번호 로그인', 'url' => '/distributor/login', 'state' => 'done', 'note' => 'Distributor DB 계정 인증'],
                ['slide' => '240-242', 'screen' => 'RF-04-02-01', 'name' => '발주 대기', 'intent' => '배송대기 주문 조회, 엑셀 다운로드/업로드, 발주 상세 수정', 'url' => '/distributor/orders/pending', 'state' => 'done', 'note' => 'orders_products.distributor_id 기준 실제 주문상품 조회'],
                ['slide' => '243-244', 'screen' => 'RF-04-02-02', 'name' => '발주 완료', 'intent' => '송장 입력 후 배송중/완료 주문 확인 및 배송 전 수정', 'url' => '/distributor/orders/completed', 'state' => 'done', 'note' => '송장 저장 시 배송중 상태와 발송일 반영'],
            ],
            'flows' => [
                ['label' => '발주 처리', 'steps' => ['발주사 로그인', '발주 대기 목록', '엑셀 다운로드', '송장 입력 업로드', '발주 완료 확인']],
            ],
        ],
        [
            'id' => 'rf05',
            'code' => 'RF-05',
            'title' => '전체관리자 / 디자인 패턴',
            'intent' => 'Me9 운영자가 전체 플랫폼을 관리하고, 관리자 화면 전반에 쓰일 리스트/갤러리/보기/입력/레이어/로딩 패턴을 정의합니다.',
            'summary' => ['관리자 로그인', '대시보드', '전체 정산관리', '리스트/갤러리/보기 패턴', '입력폼', '팝업/레이어', '상태 진행창'],
            'status' => '부분 구현',
            'accent' => '#dc2626',
            'items' => [
                ['slide' => '247', 'screen' => 'RF-05-01', 'name' => '전체관리자 로그인', 'intent' => '별도 관리자 주소로 로그인', 'url' => '/admin/login', 'state' => 'done', 'note' => '기존 admin guard 로그인'],
                ['slide' => '249', 'screen' => 'RF-05-02-01', 'name' => '대시보드', 'intent' => '3분할 블록형 대시보드와 메뉴 구조', 'url' => '/admin/dashboard', 'state' => 'partial', 'note' => '기존 관리자 대시보드와 혼재'],
                ['slide' => '164-166 연계', 'screen' => 'RF-05-SETTLEMENT', 'name' => '전체관리자 정산관리', 'intent' => '구매확정 주문을 기준으로 채널별 요율 정산자료를 생성하고 완료 처리', 'url' => '/admin/settlements', 'state' => 'done', 'note' => 'settlement_runs, settlement_items 별도 테이블과 Shop 채널 정산 요율, 공동구매 재가격 금액 연동'],
                ['slide' => '포인트 연계', 'screen' => 'RF-05-POINTS', 'name' => '판매자 포인트 판매/사용 내역', 'intent' => '판매자 포인트 구매 승인, 환급 승인, 고객 페이백/SMS 사용 차감 원장 관리', 'url' => '/admin/channel-points', 'state' => 'done', 'note' => '판매자 포인트 구매/환급 승인과 사용 내역 전체관리자 원장 구현됨'],
                ['slide' => '250-254', 'screen' => 'RF-05-02-02~06', 'name' => '리스트/갤러리/보기 패턴', 'intent' => '관리자 공통 화면 패턴', 'url' => '/admin/sub03', 'state' => 'partial', 'note' => '패턴 확인용 sub 화면 존재'],
                ['slide' => '255-258', 'screen' => 'RF-05-02-07~10', 'name' => '입력/팝업/레이어 패턴', 'intent' => '폼, 작은 레이어, 별도 팝업, 대형 딤 레이어', 'url' => '/admin/sub02', 'state' => 'partial', 'note' => '여러 샘플 페이지로 분산'],
                ['slide' => '259', 'screen' => 'RF-05-02-11', 'name' => '상태 진행창', 'intent' => 'Ajax/대용량 처리 중 전체 화면 클릭 방지 로딩', 'url' => '/admin/loading', 'state' => 'partial', 'note' => '로딩 샘플 화면'],
            ],
            'flows' => [
                ['label' => '관리자 패턴 검증', 'steps' => ['로그인', '대시보드', '리스트 패턴', '보기 패턴', '입력/레이어', '로딩 상태']],
            ],
        ],
    ];

    $stateLabels = [
        'done' => '구현',
        'partial' => '부분구현',
        'mock' => '목업',
        'missing' => '미구현',
    ];

    $stateDescriptions = [
        'done' => '현재 라우트와 화면이 실제 데이터 흐름에 연결되어 있습니다.',
        'partial' => '라우트/화면/일부 DB 처리는 있으나 기획서 세부 흐름과 추가 정합성 검증이 필요합니다.',
        'mock' => '화면 확인 또는 시나리오 테스트용 구현입니다. 운영 데이터 연동은 부족합니다.',
        'missing' => '기획서 항목은 있으나 현재 직접 연결할 화면이 없습니다.',
    ];

    $accounts = [
        ['title' => '비회원 주문조회', 'lines' => ['주문번호: Me9-Shop-0032022', '연락처: 010-1234-5678'], 'url' => '/nonmember/order/check'],
        ['title' => '일반 회원', 'lines' => ['아이디: user@user.com', '비밀번호: 123456'], 'url' => '/member/login'],
        ['title' => '채널 관리자', 'lines' => ['아이디: john@admin.com', '비밀번호: 123456'], 'url' => '/channel/login'],
        ['title' => '발주사', 'lines' => ['아이디: partner@main.com', '비밀번호: 123456'], 'url' => '/distributor/login'],
        ['title' => '전체 관리자', 'lines' => ['아이디: admin@admin.com', '비밀번호: 123456'], 'url' => '/admin/login'],
    ];

    $quickMenus = [
        ['title' => '전체관리자 정산관리', 'desc' => '정산자료 생성, 상세 품목, 완료 처리', 'url' => '/admin/settlements'],
        ['title' => '전체관리자 포인트내역', 'desc' => '판매자 포인트 구매 승인, 환급, 사용 차감', 'url' => '/admin/channel-points'],
        ['title' => '채널 정산관리', 'desc' => 'Shop 채널별 기간 정산 조회', 'url' => '/channel/settlement/list'],
        ['title' => '채널 주문목록', 'desc' => '주문/취소/반품/교환 상태 검색', 'url' => '/channel/order/list'],
        ['title' => '공동구매 주문목록', 'desc' => '공동구매 주문만 분리 조회', 'url' => '/channel/order/joint/list'],
    ];
@endphp
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Me9 Market RF-01~RF-05 테스트보드</title>
    <style>
        :root {
            --bg: #f6f7f9;
            --surface: #ffffff;
            --surface-muted: #eef1f5;
            --ink: #151922;
            --muted: #667085;
            --line: #d9dee7;
            --done: #087443;
            --partial: #b54708;
            --mock: #475467;
            --missing: #b42318;
            --radius: 8px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        a { color: inherit; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(10px);
        }

        .topbar-inner {
            max-width: 1440px;
            margin: 0 auto;
            padding: 18px 24px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 18px;
            align-items: center;
        }

        .brand h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0;
        }

        .brand p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .home-link,
        .open-link,
        .copy-button {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 6px;
            border: 1px solid var(--line);
            background: var(--surface);
            padding: 8px 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
            cursor: pointer;
        }

        .open-link.primary {
            background: #111827;
            border-color: #111827;
            color: white;
        }

        .wrap {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px;
        }

        .intro {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(320px, 0.9fr);
            gap: 16px;
            margin-bottom: 16px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px;
        }

        .panel h2,
        .panel h3 {
            margin: 0 0 10px;
            letter-spacing: 0;
        }

        .panel h2 { font-size: 24px; }
        .panel h3 { font-size: 17px; }

        .lead {
            margin: 0;
            color: #344054;
            font-size: 15px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .metric {
            background: var(--surface-muted);
            border-radius: 6px;
            padding: 12px;
        }

        .metric b {
            display: block;
            font-size: 22px;
        }

        .metric span {
            color: var(--muted);
            font-size: 12px;
        }

        .account-list {
            display: grid;
            gap: 8px;
        }

        .account {
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 10px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .account strong {
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .account span {
            display: block;
            color: var(--muted);
            font-size: 12px;
        }

        .quick-menu-title {
            margin: 16px 0 8px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            font-weight: 800;
            font-size: 13px;
        }

        .quick-menu-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .quick-menu {
            min-height: 72px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 10px;
            display: grid;
            align-content: start;
            gap: 4px;
            text-decoration: none;
            background: #fff;
        }

        .quick-menu strong {
            font-size: 13px;
        }

        .quick-menu span {
            color: var(--muted);
            font-size: 12px;
        }

        .quick-menu:hover {
            border-color: #111827;
        }

        .rf-nav {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin: 16px 0;
        }

        .rf-tab {
            border: 1px solid var(--line);
            border-left: 5px solid var(--accent);
            background: var(--surface);
            border-radius: var(--radius);
            padding: 12px;
            text-align: left;
            cursor: pointer;
            min-height: 88px;
        }

        .rf-tab.active {
            box-shadow: 0 0 0 2px var(--accent) inset;
        }

        .rf-tab b {
            display: block;
            font-size: 15px;
        }

        .rf-tab span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .toolbar {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) auto auto auto auto auto;
            gap: 10px;
            margin: 16px 0;
        }

        .toolbar input,
        .toolbar select {
            height: 40px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--surface);
            padding: 0 12px;
            font-size: 14px;
        }

        .rf-panel {
            display: none;
        }

        .rf-panel.active {
            display: block;
        }

        .rf-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: start;
            border-left: 5px solid var(--accent);
        }

        .rf-code {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 3px 10px;
            background: var(--surface-muted);
            font-weight: 800;
            font-size: 12px;
        }

        .rf-head h2 {
            margin-top: 8px;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 12px;
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px 9px;
            color: #344054;
            background: #fff;
            font-size: 12px;
        }

        .state-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 76px;
            border-radius: 999px;
            padding: 4px 10px;
            font-weight: 800;
            font-size: 12px;
        }

        .state-done { background: #dcfae6; color: var(--done); }
        .state-partial { background: #fef0c7; color: var(--partial); }
        .state-mock { background: #eaecf0; color: var(--mock); }
        .state-missing { background: #fee4e2; color: var(--missing); }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 16px;
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border-bottom: 1px solid var(--line);
            padding: 12px;
            vertical-align: top;
            text-align: left;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            color: #475467;
            font-size: 12px;
        }

        th:nth-child(1) { width: 76px; }
        th:nth-child(2) { width: 128px; }
        th:nth-child(4) { width: 94px; }
        th:nth-child(5) { width: 112px; }

        .screen-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 800;
        }

        .screen-name {
            display: block;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .screen-intent,
        .note {
            color: var(--muted);
        }

        .side-stack {
            display: grid;
            gap: 16px;
            align-content: start;
        }

        .flow {
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px;
            margin-top: 8px;
        }

        .flow strong {
            display: block;
            margin-bottom: 8px;
        }

        .flow ol {
            margin: 0;
            padding-left: 20px;
            color: #344054;
            font-size: 13px;
        }

        .legend {
            display: grid;
            gap: 8px;
        }

        .legend-row {
            display: grid;
            grid-template-columns: 88px 1fr;
            gap: 8px;
            align-items: start;
            font-size: 12px;
            color: var(--muted);
        }

        .empty {
            display: none;
            padding: 18px;
            color: var(--muted);
            text-align: center;
        }

        footer {
            color: var(--muted);
            font-size: 12px;
            padding: 20px 0 8px;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .intro,
            .content-grid {
                grid-template-columns: 1fr;
            }

            .rf-nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .topbar-inner,
            .rf-head,
            .toolbar {
                grid-template-columns: 1fr;
            }

            .wrap {
                padding: 14px;
            }

            .metric-grid,
            .rf-nav,
            .quick-menu-list {
                grid-template-columns: 1fr;
            }

            table,
            thead,
            tbody,
            tr,
            th,
            td {
                display: block;
                width: 100% !important;
            }

            thead { display: none; }

            tr {
                border: 1px solid var(--line);
                border-radius: 6px;
                margin-bottom: 10px;
                overflow: hidden;
                background: white;
            }

            td {
                border-bottom: 1px solid #eef1f5;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <h1>Me9 Market 스토리보드 검증보드</h1>
                <p>M9-SB-Ver2.0.2 기준 RF-01~RF-05 기획 의도와 현재 구현 화면을 한 곳에서 점검합니다.</p>
            </div>
            <a class="home-link" href="/">메인 홈</a>
        </div>
    </header>

    <main class="wrap">
        <section class="intro">
            <div class="panel">
                <h2>기획서 기준 테스트 목적</h2>
                <p class="lead">
                    이 페이지는 단순 링크 모음이 아니라, 기획서의 큰 흐름대로 각 RF 영역의 의도와 현재 구현 상태를 대조하기 위한 검증 허브입니다.
                    구현된 화면은 바로 열고, 부분 구현이나 목업 화면은 어떤 업무 로직이 더 필요한지 함께 확인할 수 있게 구성했습니다.
                </p>
                <div class="metric-grid">
                    <div class="metric"><b>5</b><span>대분류 RF 영역</span></div>
                    <div class="metric"><b>260</b><span>PPTX 슬라이드</span></div>
                    <div class="metric"><b>32</b><span>대표 검증 항목</span></div>
                    <div class="metric"><b>330</b><span>현재 Laravel 라우트</span></div>
                </div>
            </div>

            <div class="panel">
                <h3>테스트 계정 / 입력값</h3>
                <div class="account-list">
                    @foreach ($accounts as $account)
                        <div class="account">
                            <div>
                                <strong>{{ $account['title'] }}</strong>
                                @foreach ($account['lines'] as $line)
                                    <span>{{ $line }}</span>
                                @endforeach
                            </div>
                            <a class="open-link" href="{{ $account['url'] }}" target="_blank" rel="noreferrer">열기</a>
                        </div>
                    @endforeach
                </div>
                <div class="quick-menu-title">운영 메뉴 바로가기</div>
                <div class="quick-menu-list">
                    @foreach ($quickMenus as $menu)
                        <a class="quick-menu" href="{{ $menu['url'] }}" target="_blank" rel="noreferrer">
                            <strong>{{ $menu['title'] }}</strong>
                            <span>{{ $menu['desc'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <nav class="rf-nav" aria-label="RF 영역 선택">
            @foreach ($sections as $section)
                <button class="rf-tab {{ $loop->first ? 'active' : '' }}" style="--accent: {{ $section['accent'] }}" data-tab="{{ $section['id'] }}">
                    <b>{{ $section['code'] }} {{ $section['title'] }}</b>
                    <span>{{ $section['status'] }}</span>
                </button>
            @endforeach
        </nav>

        <section class="toolbar" aria-label="필터">
            <input id="searchInput" type="search" placeholder="화면 ID, 화면명, 의도, 비고 검색">
            <select id="stateFilter">
                <option value="all">전체 상태</option>
                <option value="done">구현</option>
                <option value="partial">부분구현</option>
                <option value="mock">목업</option>
                <option value="missing">미구현</option>
            </select>
            <button class="copy-button" type="button" data-copy="/storyboard-test">현재 URL 복사</button>
            <a class="open-link primary" href="/admin/dashboard" target="_blank" rel="noreferrer">관리자 바로가기</a>
            <a class="open-link primary" href="/admin/settlements" target="_blank" rel="noreferrer">전체관리자 정산</a>
            <a class="open-link" href="/channel/settlement/list" target="_blank" rel="noreferrer">채널 정산</a>
        </section>

        @foreach ($sections as $section)
            <section id="{{ $section['id'] }}" class="rf-panel {{ $loop->first ? 'active' : '' }}" data-panel="{{ $section['id'] }}">
                <div class="panel rf-head" style="--accent: {{ $section['accent'] }}">
                    <div>
                        <span class="rf-code">{{ $section['code'] }}</span>
                        <h2>{{ $section['title'] }}</h2>
                        <p class="lead">{{ $section['intent'] }}</p>
                        <div class="chips">
                            @foreach ($section['summary'] as $summary)
                                <span class="chip">{{ $summary }}</span>
                            @endforeach
                        </div>
                    </div>
                    <span class="state-pill state-{{ in_array($section['status'], ['부분 구현', '기존 관리자와 혼재']) ? 'partial' : ($section['status'] === '목업 중심' ? 'mock' : 'done') }}">{{ $section['status'] }}</span>
                </div>

                <div class="content-grid">
                    <div class="panel">
                        <h3>{{ $section['code'] }} 화면 검증 목록</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Slide</th>
                                    <th>화면 ID</th>
                                    <th>기획 의도 / 현재 확인 포인트</th>
                                    <th>상태</th>
                                    <th>실행</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($section['items'] as $item)
                                    <tr class="screen-row" data-state="{{ $item['state'] }}" data-search="{{ strtolower($item['slide'] . ' ' . $item['screen'] . ' ' . $item['name'] . ' ' . $item['intent'] . ' ' . $item['note']) }}">
                                        <td>{{ $item['slide'] }}</td>
                                        <td><span class="screen-code">{{ $item['screen'] }}</span></td>
                                        <td>
                                            <span class="screen-name">{{ $item['name'] }}</span>
                                            <div class="screen-intent">{{ $item['intent'] }}</div>
                                            <div class="note">현재: {{ $item['note'] }}</div>
                                        </td>
                                        <td>
                                            <span class="state-pill state-{{ $item['state'] }}">{{ $stateLabels[$item['state']] }}</span>
                                        </td>
                                        <td>
                                            @if (!empty($item['url']))
                                                <a class="open-link primary" href="{{ $item['url'] }}" target="_blank" rel="noreferrer">열기</a>
                                            @else
                                                <span class="open-link">없음</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="empty">현재 필터 조건에 맞는 화면이 없습니다.</div>
                    </div>

                    <aside class="side-stack">
                        <div class="panel">
                            <h3>상태 기준</h3>
                            <div class="legend">
                                @foreach ($stateLabels as $key => $label)
                                    <div class="legend-row">
                                        <span class="state-pill state-{{ $key }}">{{ $label }}</span>
                                        <span>{{ $stateDescriptions[$key] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="panel">
                            <h3>우선 검증 플로우</h3>
                            @foreach ($section['flows'] as $flow)
                                <div class="flow">
                                    <strong>{{ $flow['label'] }}</strong>
                                    <ol>
                                        @foreach ($flow['steps'] as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                </div>
            </section>
        @endforeach

        <footer>
            M9-SB-Ver2.0.2 기준으로 정리된 개발 검증용 페이지입니다. 운영 구현 여부는 각 화면의 상태와 현재 확인 포인트를 기준으로 판단합니다.
        </footer>
    </main>

    <script>
        const tabs = document.querySelectorAll('.rf-tab');
        const panels = document.querySelectorAll('.rf-panel');
        const searchInput = document.getElementById('searchInput');
        const stateFilter = document.getElementById('stateFilter');

        function setActiveTab(tabId) {
            tabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.tab === tabId));
            panels.forEach((panel) => panel.classList.toggle('active', panel.dataset.panel === tabId));
            applyFilters();
        }

        function applyFilters() {
            const query = searchInput.value.trim().toLowerCase();
            const state = stateFilter.value;
            const activePanel = document.querySelector('.rf-panel.active');
            if (!activePanel) return;

            const rows = activePanel.querySelectorAll('.screen-row');
            let visibleCount = 0;
            rows.forEach((row) => {
                const matchesQuery = !query || row.dataset.search.includes(query);
                const matchesState = state === 'all' || row.dataset.state === state;
                const visible = matchesQuery && matchesState;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount += 1;
            });

            const empty = activePanel.querySelector('.empty');
            if (empty) empty.style.display = visibleCount ? 'none' : 'block';
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
        });

        searchInput.addEventListener('input', applyFilters);
        stateFilter.addEventListener('change', applyFilters);

        document.querySelectorAll('[data-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                const url = `${window.location.origin}${button.dataset.copy}`;
                try {
                    await navigator.clipboard.writeText(url);
                    const original = button.textContent;
                    button.textContent = '복사됨';
                    setTimeout(() => { button.textContent = original; }, 1200);
                } catch (error) {
                    window.prompt('URL 복사', url);
                }
            });
        });
    </script>
</body>
</html>
