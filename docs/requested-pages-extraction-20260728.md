# Requested Pages Extraction

점검 기준일: 2026-08-09  
검토 파일:

- `Me9_플랫폼_웹페이지_작업요청서_20260728.xlsx`
- `Me9_FileMap.xlsx`

## 결론

작업요청서 기준 실제 요청 항목은 45개입니다. 현재 소스와 대조하면 “완전히 없는 페이지”보다는 다음 세 유형이 핵심입니다.

- 이미 구현되어 있으나 FileMap에는 아직 `파일요청중`으로 남아 있는 항목
- Blade는 있으나 정적 샘플/임시 데이터라 운영 페이지로 보기 어려운 항목
- 레이어 UI는 있으나 실제 action/상태 처리까지 완결되지 않은 항목

## 최우선 작업 대상

| 우선순위 | 요구 항목 | 현재 상태 | 필요한 작업 |
| --- | --- | --- | --- |
| 1 | `PaypalController` 결제 실패/세션 없음 복귀 | `app/Http/Controllers/Front/PaypalController.php:71`에서 `view('cart')` 호출. `resources/views/cart.blade.php` 없음 | 기존 실제 장바구니 화면으로 redirect 처리 필요. 신규 Blade보다 `front.products.cart` 또는 `front.shop.cart` 흐름으로 정리 권장 |
| 2 | Shop 취소/반품/교환 상세 페이지 | `/shop/cancel/details`, `/shop/return/details`, `/shop/exchange/details`는 라우트가 있으나 식별자 없음. Blade는 정적 샘플 데이터 | `{claim}` 또는 `{orderItem}` 식별자 기반 라우트/컨트롤러/Blade 재작업 필요 |
| 3 | Shop 구매내역/주문조회 계열 | FileMap RF-03-05-01~06 요구. 현재 `/shop/order/details`는 최근 주문 1건 중심 | 주문조회 입력, 구매내역 목록, 주문 상세 보기의 역할 분리 필요 |
| 4 | 채널 주문관리 보류/송장/옵션 변경 액션 | 레이어 UI는 존재하나 일부 버튼이 `url()->current()` 또는 단순 확인 UI | 반품보류, 송장정보 변경, 교환 보류, 옵션 변경, 송장수정 등 후속 상태 변경 route/action 보강 필요 |
| 5 | 비공개 Shop 채널 접속 가능 인원관리 | 현재 비공개 비밀번호/회원전용 설정은 있으나 “접속가능 인원관리” 팝업은 별도 확인 안 됨 | 별도 레이어 또는 설정 내 인원 관리 CRUD 필요 |
| 6 | Shop 채널 로그인/간편회원가입 | FileMap RF-03-01-02, RF-03-02-01. 현재 Shop 채널은 입장코드/일반 회원 로그인 흐름 중심 | 스토리보드가 Shop 전용 로그인/소셜가입 화면을 요구하면 Blade/route 추가 필요 |

## 작업요청서 45개 항목 대조

| No | SB No | 영역 | 요청 화면 | 종류 | 현재 매칭 | 판단 |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | RF-01-06-01 | 홈페이지 주문조회 | 입력페이지 | Web Page | `front.pages.nonmember_order_check` | 구현 있음 |
| 2 | RF-01-06-02 | 홈페이지 주문조회 | 주문 상세 페이지 | Web Page | `front.pages.nonmember_order_details` | 구현 있음. 팝업 포함 여부 재검수 필요 |
| 3 | RF-01-06-02-1 | 홈페이지 주문조회 | 취소신청 | Layer popup | `front.pages.nonmember_order_details` 내 claim form | 구현 있음 |
| 4 | RF-01-06-02-2 | 홈페이지 주문조회 | 반품신청 | Layer popup | `front.pages.nonmember_order_details` 내 claim form | 구현 있음 |
| 5 | RF-01-06-02-3 | 홈페이지 주문조회 | 교환신청 | Layer popup | `front.pages.nonmember_order_details` 내 claim form | 구현 있음 |
| 6 | RF-01-06-02-4 | 홈페이지 주문조회 | 구매확정하기 | Layer popup | `front.pages.nonmember_order_details` | 기능/표시 재검수 필요 |
| 7 | RF-01-06-02-5 | 홈페이지 주문조회 | 상품 문의하기 | Layer popup | `front.pages.nonmember_order_details` / 문의 form | 구현 있음 |
| 8 | RF-01-07-02 | 홈페이지 회원영역 | 간편 회원 약관 동의 | Web Page | `front.member.social_join` | 구현 있음 |
| 9 | RF-01-07-11-1 | 마이페이지 | 배송지 설정/추가 | Layer popup | `front.mypage.sub01.delivery_destination` | 구현 있음 |
| 10 | RF-01-07-11-3 | 마이페이지 | 배송지 수정 | Layer popup | `front.mypage.sub01.delivery_destination` | 구현 있음 |
| 11 | RF-01-07-14-1 | 마이페이지 | 방문한 채널 QR 확대 | Layer popup | `front.mypage.sub01.visited_channels` | 구현 있음 |
| 12 | RF-01-07-15-1 | 마이페이지 | 사용가능한 Shop 채널 보기/QR 확대 | Layer popup | `front.mypage.sub01.point_status` | 부분 구현. “보기 클릭 후 채널목록” UX 재검수 필요 |
| 13 | RF-01-07-16-1 | 마이페이지 | 장바구니 QR 확대 | Layer popup | `front.mypage.sub01.cart_list` | 구현 있음 |
| 14 | RF-02-04-02-1 | 채널관리자 Shop채널 | 비공개시 인원관리 | Layer popup | 비밀번호/회원전용 필드는 있음 | 별도 인원관리 CRUD 미확인. 보강 필요 |
| 15 | RF-02-04-02-2 | 채널관리자 Shop채널 | 그룹 keyword 추가 화면 | Web Page | `channel.sub01.shop_register`, `channel.sub01.info_update` | 구현 있음 |
| 16 | RF-02-04-02-3 | 채널관리자 Shop채널 | 기간제 사용기간 화면 | Web Page | `channel.sub01.shop_register`, `channel.sub01.info_update` | 구현 있음 |
| 17 | RF-02-04-02-5-2 | 채널관리자 Shop채널 | 구매현황 문자알림 사용 화면 | Web Page | 명확한 설정 UI 확인 안 됨 | 보강 후보 |
| 18 | RF-02-04-02-6 | 채널관리자 Shop채널 | OG 태그 사용 화면 | Web Page | `channel.sub01.shop_register`, `channel.sub01.info_update` | 구현 있음 |
| 19 | RF-02-04-02-6-1 | 채널관리자 Shop채널 | Shop 채널 PG 정보 사용 화면 | Web Page | `channel.sub01.shop_register`, `channel.sub01.info_update`, `shop_info` | 구현 있음 |
| 20 | RF-02-04-02-7 | 채널관리자 Shop채널 | 모니터링 관리자 정보 사용 화면 | Web Page | `channel.sub01.shop_register`, `channel.sub01.info_update`, `shop_info` | 구현 있음 |
| 21 | RF-02-04-05-3 | 채널관리자 Shop채널 | 판매상품 추가시 2중 팝업 | Layer popup | `channel.sub01.inc.pop_shop_product_*` | UI 있음. 2중 팝업 UX 재검수 필요 |
| 22 | RF-02-04-05-5 | 채널관리자 Shop채널 | 상품 확인용 popup | Layer popup | `channel.sub01.inc.pop_shop_product_public` 등 | 구현 있음 |
| 23 | RF-02-04-05-6 | 채널관리자 Shop채널 | 상품 추가용 popup/위탁판매 | Layer popup | `channel.sub01.inc.pop_shop_product_public` | 부분 구현. 위탁판매 패턴 재검수 필요 |
| 24 | RF-02-04-05-8 | 채널관리자 Shop채널 | 상품 판매 신청 popup | Layer popup | `channel.sub01.inc.pop_shop_product_partial` | 구현 있음 |
| 25 | RF-02-05-02-1 | 채널관리자 상품관리 | 대분류별 필수표시 변경 | Web Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 26 | RF-02-05-02-2 | 채널관리자 상품관리 | 상품정보 등록타입별 변화 | Web Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 27 | RF-02-05-02-4 | 채널관리자 상품관리 | 옵션 사용/추가/삭제 변화 | Web Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 28 | RF-02-05-02-5 | 채널관리자 상품관리 | 상품 제약 조건 변화 | Web Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 29 | RF-02-06-01 | 채널관리자 공동구매 | 공동구매 목록 | Web Page | `channel.sub03.joint_purchase_list` | 구현 있음 |
| 30 | RF-02-06-02 | 채널관리자 공동구매 | 공동구매 등록 | Web Page | `channel.sub03.joint_purchase_create` | 구현 있음 |
| 31 | RF-02-06-03 | 채널관리자 공동구매 | 상품등록과 동일한 필수표시 퍼포먼스 | Web Page | `channel.sub03.joint_purchase_create/edit` | 부분 구현 가능. 상품등록 동일 수준 재검수 필요 |
| 32 | RF-02-07-03-2 | 채널관리자 주문관리 | 배송정보 popup | Layer popup | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 33 | RF-02-07-03-3 | 채널관리자 주문관리 | 반품요청 처리 popup | Layer popup | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 34 | RF-02-07-03-4 | 채널관리자 주문관리 | 교환요청 처리 popup | Layer popup | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 35 | RF-02-07-03-5 | 채널관리자 주문관리 | 취소요청 처리 popup | Layer popup | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 36 | RF-02-07-05-2 | 채널관리자 주문관리 | 반품보류 popup | Layer popup | `channel.sub04.inc.pop_order_return` | UI 있음. 실제 action 보강 필요 |
| 37 | RF-02-07-05-3 | 채널관리자 주문관리 | 송장정보 변경 popup | Layer popup | `channel.sub04.inc.pop_order_return` | UI 있음. 실제 action 보강 필요 |
| 38 | RF-02-07-06-2 | 채널관리자 주문관리 | 교환회수 전 보류 popup | Layer popup | `channel.sub04.inc.pop_order_exchange` | UI 있음. 실제 action 보강 필요 |
| 39 | RF-02-07-06-6 | 채널관리자 주문관리 | 교환회수 후 보류 popup | Layer popup | `channel.sub04.inc.pop_order_exchange` | UI 있음. 실제 action 보강 필요 |
| 40 | RF-02-07-06-8 | 채널관리자 주문관리 | 교환 옵션 변경 popup | Layer popup | `channel.sub04.inc.pop_order_exchange` | UI 있음. 실제 action 보강 필요 |
| 41 | RF-02-07-06-9 | 채널관리자 주문관리 | 송장수정 popup | Layer popup | `channel.sub04.inc.pop_order_exchange` | UI 있음. 실제 action 보강 필요 |
| 42 | RF-02-08-01 | 채널관리자 정산관리 | 정산월 선택 전 기본화면 | Web Page | `channel.sub05.settlement_list` | 구현 있음 |
| 43 | RF-02-08-01-2 | 채널관리자 정산관리 | 정산월 선택 후 기본화면 | Web Page | `channel.sub05.settlement_list/view` | 구현 있음 |
| 44 | RF-03-01-01~RF-03-06-02 | Shop 채널 | 전체 웹페이지 | Web Page | `shop.*`, `front.shop.*` 혼재 | 부분 구현. 아래 Shop 항목 참고 |
| 45 | RF-04-01-01~RF-04-02-02-1 | 발주사 | 전체 웹페이지 | Web Page | `distributor.*` | 구현 있음. 스타일/팝업 일관성 재검수 필요 |

## FileMap 기준 추가 확인 필요 항목

### 홈페이지/마이페이지

| FileMap 항목 | 현재 구현 | 판단 |
| --- | --- | --- |
| RF-01-01 메인, RF-01-02 서비스, RF-01-03 주요기능, RF-01-04 가입안내 | `front.index`, `front.pages.service`, `features`, `subscription_info` | 구현 있음. FileMap의 `파일요청중`은 현 상태와 불일치 |
| RF-01-06-01~02 주문조회/상세 및 팝업 | `front.pages.nonmember_order_check/details` | 구현 있음 |
| RF-01-07-09 마이페이지 대시보드 | `front.mypage.index` | 구현 있음 |
| RF-01-07-10 회원정보 수정 | `front.mypage.profile`가 실제 사용. FileMap은 `member_update.html`/`modify.php` 기준 | 구현 있음. FileMap의 “해당 파일 없음”은 현재 구조와 불일치 |
| RF-01-07-19~21 취소/반품/교환 목록 | `front.mypage.cancel_return_list`와 `order/list` 탭 | 구현 있음. 별도 3개 Blade가 필요한 설계인지 확인 필요 |
| RF-01-07-22 주문 상세 | `front.mypage.order.view` | 구현 있음 |
| RF-01-07-14~17 방문채널/포인트/장바구니/찜 | 각각 Blade 존재 | 구현 있음. QR/보기 팝업은 부분 재검수 |

### 채널관리자

| FileMap 항목 | 현재 구현 | 판단 |
| --- | --- | --- |
| RF-02-03-01 정보관리 | `channel.sub00.info_management` | 구현 있음 |
| RF-02-04-01~09 Shop 채널관리 | `channel.sub01.*` | 구현 있음. 단 비공개 인원관리/2중 팝업은 재검수 |
| RF-02-05-01~05 상품관리 | `channel.sub02.*` | 구현 있음 |
| RF-02-05-02 발주 담당자 찾기 | 상품 등록/수정에서 발주 담당자 select는 있음 | 별도 “찾기 popup” 요구이면 보강 필요 |
| RF-02-06 공동구매 | `channel.sub03.*` | 구현 있음 |
| RF-02-07 주문관리 | `channel.sub04.*` | 목록/기본 상태처리 구현. 보류/송장/옵션 변경 후속 action 보강 필요 |
| RF-02-08 정산 | `channel.sub05.*` | 구현 있음 |
| RF-02-09 서브 관리자 | `channel.sub00.sub_accounts_list` | 구현 있음 |
| RF-02-10 발주 담당 관리 | `channel.sub00.order_manager_list` | 구현 있음 |
| RF-02-11 포인트 | `channel.sub00.point_list` | 구매/환급 요청 구현 있음 |

### Shop 채널

| FileMap 항목 | 현재 구현 | 판단 |
| --- | --- | --- |
| RF-03-01-01 입장 코드 확인 | `shop.gate` | 구현 있음 |
| RF-03-01-02 로그인 | Shop 전용 로그인 Blade 없음. 일반 회원 로그인/채널 gate 중심 | 별도 화면 요구 시 신규 필요 |
| RF-03-02-01 간편 회원가입 | Shop 전용 가입 Blade 없음. `front.member.social_join` 존재 | 별도 화면 요구 시 신규 필요 |
| RF-03-03-01 메인 | `shop.channel_main` | 구현 있음 |
| RF-03-04-01~02 일반상품 목록/상세 | `shop.products_list`, `shop.product_details` | 구현 있음 |
| RF-03-04-03 장바구니/옵션 변경 | `front.shop.cart` | 장바구니 구현. 옵션 변경 팝업은 보강 후보 |
| RF-03-04-04 주문서 | `front.shop.order_form` | 구현 있음 |
| RF-03-04-05 주문 완료 | `front.shop.order_complete` | 구현 있음 |
| RF-03-04-06 구매내역 확인 | 전용 구매내역 목록 없음 | 신규 또는 `/shop/order/details` 확장 필요 |
| RF-03-05-01 주문 조회 | 전용 입력 페이지 없음. `/shop/order/details`는 최근 주문 조회 중심 | 신규 또는 비회원 주문조회 공통화 필요 |
| RF-03-05-02 주문 관리 | `front.shop.order_details` | 구현 있음 |
| RF-03-05-02-1~5 문의/취소/반품/교환/구매확정 팝업 | 현재 `front.shop.order_details`는 즉시 POST 버튼 방식 | 스토리보드가 Layer popup이면 팝업 UI 보강 필요 |
| RF-03-05-06 주문 상세 | `front.shop.order_details` | 구현 있음. 상세/목록 분리 필요 가능 |
| RF-03-05-03~05 취소/반품/교환 목록 | `front.shop.cancel_details`, `return_details`, `exchange_details` | 파일은 있으나 정적 샘플. 실제 데이터 연결 필요 |
| RF-03-06-01~02 공지사항 목록/상세 | `shop.notices`만 존재 | 상세 페이지가 별도 필요하면 추가 필요 |

### 발주사

| FileMap 항목 | 현재 구현 | 판단 |
| --- | --- | --- |
| RF-04-01-01 로그인 | `distributor.login` | 구현 있음 |
| RF-04-02-01 발주 대기 | `distributor.orders_pending` | 구현 있음 |
| RF-04-02-01-1 처리 popup | `distributor.orders_pending` 내부 모달 | 구현 있음 |
| RF-04-02-01-2 보기/수정 popup | `distributor.order_details` 및 pending 내부 모달 | 구현 있음. popup 설계와 일치 여부 재검수 |
| RF-04-02-02 발주 완료 | `distributor.orders_completed` | 구현 있음 |
| RF-04-02-02-1 보기/수정 popup | `distributor.order_details` | 구현 있음. popup 설계와 일치 여부 재검수 |

## 운영 화면으로 쓰기 어려운 샘플/중복 파일

아래 파일은 실제 운영 라우트와 맞지 않거나 정적 샘플 데이터가 남아 있습니다.

- `resources/views/shop/sub/shopping_basket.blade.php`
- `resources/views/shop/sub/order_form.blade.php`
- `resources/views/shop/sub/order_confirm.blade.php`
- `resources/views/shop/sub/order_details.blade.php`
- `resources/views/shop/sub/cancel_details.blade.php`
- `resources/views/shop/sub/return_details.blade.php`
- `resources/views/shop/sub/exchange_details.blade.php`
- `resources/views/front/cart/index.blade.php`
- `resources/views/front/checkout/index.blade.php`
- `resources/views/front/payment/index.blade.php`
- `resources/views/front/shop/cancel_details.blade.php`
- `resources/views/front/shop/return_details.blade.php`
- `resources/views/front/shop/exchange_details.blade.php`

## 신규 Blade 생성 또는 재작업 후보 목록

신규 파일명은 현재 프로젝트 구조 기준 제안입니다. 실제 개발 시 라우트/컨트롤러와 함께 확정해야 합니다.

| 필요 화면 | 권장 Blade 후보 | 비고 |
| --- | --- | --- |
| Shop 전용 로그인 | `resources/views/shop/login.blade.php` | FileMap RF-03-01-02가 별도 화면이면 필요 |
| Shop 전용 간편회원가입 | `resources/views/shop/social_join.blade.php` | 기존 `front.member.social_join` 재사용 가능하면 신규 불필요 |
| Shop 구매내역 목록 | `resources/views/front/shop/purchase_history.blade.php` | RF-03-04-06 |
| Shop 주문조회 입력 | `resources/views/front/shop/order_confirm.blade.php` | RF-03-05-01. 비회원 주문조회와 공통화 가능 |
| Shop 주문 상세 보기 | `resources/views/front/shop/order_details_view.blade.php` 또는 기존 `order_details` 확장 | RF-03-05-06 |
| Shop 취소 목록/상세 | 기존 `front/shop/cancel_details.blade.php` 재작업 | 정적 샘플 제거, 실제 claim 데이터 연결 |
| Shop 반품 목록/상세 | 기존 `front/shop/return_details.blade.php` 재작업 | 정적 샘플 제거, 실제 claim 데이터 연결 |
| Shop 교환 목록/상세 | 기존 `front/shop/exchange_details.blade.php` 재작업 | 정적 샘플 제거, 실제 claim 데이터 연결 |
| Shop 공지 상세 | `resources/views/shop/notice_view.blade.php` | `shop.notices` 목록만으로 충분하면 신규 불필요 |
| 비공개 Shop 접속 가능 인원관리 | `resources/views/channel/sub01/inc/pop_shop_access_members.blade.php` | RF-02-04-02-1 |
| 구매현황 문자알림 설정 | 기존 `shop_register/info_update` 보강 또는 별도 include | RF-02-04-02-5-2 |
| 상품 옵션 변경 팝업 | `resources/views/front/shop/inc/pop_cart_option.blade.php` | RF-03-04-03 옵션 변경 요구 |
| 채널 주문 반품보류 action | 기존 `channel/sub04/inc/pop_order_return.blade.php` 보강 | 신규 Blade보다 action/route 보강 우선 |
| 채널 주문 송장정보 변경 action | 기존 `channel/sub04/inc/pop_order_return.blade.php` 보강 | 신규 Blade보다 action/route 보강 우선 |
| 채널 교환 보류/옵션/송장 action | 기존 `channel/sub04/inc/pop_order_exchange.blade.php` 보강 | 신규 Blade보다 action/route 보강 우선 |

## 개발 순서 제안

1. 없는 view로 오류가 나는 `PaypalController::success()`의 `view('cart')`부터 수정
2. Shop 취소/반품/교환 상세 3개를 실제 데이터 기반으로 재작업
3. Shop 구매내역/주문조회/주문상세 흐름을 스토리보드 기준으로 분리
4. 채널 주문관리의 보류/송장/옵션 변경 route/action 추가
5. 비공개 Shop 접속 가능 인원관리, 구매현황 문자알림 설정 보강
6. `shop/sub/*`, `front/cart/index`, `front/checkout/index`, `front/payment/index`는 운영 대상에서 제외하거나 현재 구조로 이관
