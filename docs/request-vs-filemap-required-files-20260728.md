# Request vs FileMap Required Files

점검 기준일: 2026-08-09

## 비교한 파일

| 파일 | 역할 |
| --- | --- |
| `Me9_플랫폼_웹페이지_작업요청서_20260728.xlsx` | 부족/추가 요청 화면 목록. 실제 요청 항목은 45개 |
| `Me9_FileMap.xlsx` | 전체 화면 맵. 화면 ID, Page Path, Page Type, 퍼블리싱 상태가 포함된 기준표 |

## 판단 기준

이번 정리는 단순히 FileMap의 `파일요청중`만 보고 부족하다고 판단하지 않았습니다. 현재 Laravel 프로젝트의 route/controller/blade와 같이 대조했습니다.

- FileMap에는 `파일요청중`이지만 현재 Laravel에 이미 구현된 화면은 “구현 있음”으로 분류
- Blade는 있으나 정적 샘플 데이터, 잘못된 route명, 식별자 없는 상세 화면이면 “재작업 필요”로 분류
- Layer/Popup은 별도 `.blade.php`가 꼭 필요한 경우와 기존 화면 내부 레이어로 충분한 경우를 구분
- `resources/views/shop/sub/*`, `resources/views/admin/sub/*`, `resources/views/master/*`처럼 퍼블리싱 샘플 성격이 강한 파일은 운영 화면과 분리해서 판단

## 최종적으로 실제 필요한 작업 파일

### 1. 반드시 신규 또는 재작업이 필요한 화면

| 구분 | FileMap 기준 | 현재 상태 | 필요한 파일/작업 |
| --- | --- | --- | --- |
| 결제 실패 복귀 장바구니 | FileMap 직접 항목은 아니지만 현재 코드 오류 가능 | `PaypalController::success()`에서 `view('cart')` 호출. `resources/views/cart.blade.php` 없음 | 신규 Blade보다 기존 장바구니로 redirect 수정 필요 |
| Shop 구매내역 확인 | RF-03-04-06 `shop/purchase_history.html` | 전용 구매내역 목록 없음 | `resources/views/front/shop/purchase_history.blade.php` 신규 또는 `front.shop.order_details`를 목록/상세로 분리 |
| Shop 주문조회 입력 | RF-03-05-01 `shop/order_confirm.html` | 전용 입력 페이지 없음 | `resources/views/front/shop/order_confirm.blade.php` 신규. 비회원 주문조회와 공통화 가능 |
| Shop 주문 상세 보기 | RF-03-05-06 `shop/order_details_view.html` | `/shop/order/details`는 최근 주문 1건 중심 | `resources/views/front/shop/order_details_view.blade.php` 신규 또는 기존 `order_details` 확장 |
| Shop 취소 목록/상세 | RF-03-05-03 `shop/cancel_details.html` | `front/shop/cancel_details.blade.php`는 존재하지만 정적 샘플 | 기존 파일 재작업. 실제 `OrderClaim`/`OrdersProduct` 데이터 연결 |
| Shop 반품 목록/상세 | RF-03-05-04 `shop/return_details.html` | `front/shop/return_details.blade.php`는 존재하지만 정적 샘플 | 기존 파일 재작업. 실제 반품 claim 데이터 연결 |
| Shop 교환 목록/상세 | RF-03-05-05 `shop/exchange_details.html` | `front/shop/exchange_details.blade.php`는 존재하지만 정적 샘플 | 기존 파일 재작업. 실제 교환 claim 데이터 연결 |
| Shop 공지사항 상세 | RF-03-06-02 `shop/notice_view.html` | `shop.notices` 목록은 있으나 상세 전용 route/blade는 확인 안 됨 | `resources/views/shop/notice_view.blade.php` 신규 검토 |
| 비공개 Shop 접속 가능 인원관리 | RF-02-04-02-1 | 비공개/회원전용 설정은 있으나 접속 가능 인원관리 화면은 없음 | `resources/views/channel/sub01/inc/pop_shop_access_members.blade.php` 신규 또는 `info_update` 내 관리 UI 추가 |
| 구매현황 문자알림 설정 | RF-02-04-02-5-2 | SMS 차감/발송 로직은 있으나 Shop 채널 설정 UI가 명확하지 않음 | `shop_register/info_update`에 설정 UI 보강 또는 include 분리 |

### 2. Layer/Popup으로 별도 정리가 필요한 화면

| 구분 | FileMap 기준 | 현재 상태 | 필요한 파일/작업 |
| --- | --- | --- | --- |
| Shop 상품 상세 문의하기 | RF-03-04-02-4 `shop/pop.product_view_contact_us.html` | Shop 상품상세 전용 문의 팝업 확인 필요 | `resources/views/front/shop/inc/pop_product_contact.blade.php` 후보 |
| Shop 장바구니 옵션 변경 | RF-03-04-03 `shop/pop.shopping_basket_option.html` | `front.shop.cart`에 옵션변경 팝업 없음 | `resources/views/front/shop/inc/pop_cart_option.blade.php` 후보 |
| Shop 주문상세 판매자 문의 | RF-03-05-02-1 `shop/pop.order_details_contact_us.html` | `front.shop.order_details`에는 즉시 action 버튼 중심 | `resources/views/front/shop/inc/pop_order_contact.blade.php` 후보 |
| Shop 취소 신청 | RF-03-05-02-2 `shop/pop.order_details_cancel_request.html` | 현재 즉시 POST 버튼 방식 | 레이어 사유 입력형으로 보강 필요 |
| Shop 반품 신청 | RF-03-05-02-3 `shop/pop.order_details_return_request.html` | 현재 즉시 POST 버튼 방식 | 레이어 사유/회수정보 입력형으로 보강 필요 |
| Shop 교환 신청 | RF-03-05-02-4 `shop/pop.order_details_exchange_request.html` | 현재 즉시 POST 버튼 방식 | 레이어 교환정보/회수정보 입력형으로 보강 필요 |
| Shop 구매확정 | RF-03-05-02-5 `shop/pop.order_details_purchase_confirm.html` | 현재 즉시 POST 버튼 방식 | 확인 레이어로 보강 필요 |
| 채널 반품보류 | RF-02-07-05-2 | `channel.sub04.inc.pop_order_return` UI 있음 | 별도 Blade 신규보다 실제 상태 변경 action 필요 |
| 채널 반품 송장수정 | RF-02-07-05-3 | `channel.sub04.inc.pop_order_return` UI 있음 | 실제 송장 변경 action 필요 |
| 채널 교환 회수 전 보류 | RF-02-07-06-2 | `channel.sub04.inc.pop_order_exchange` UI 있음 | 실제 상태 변경 action 필요 |
| 채널 교환 회수 후 보류 | RF-02-07-06-6 | `channel.sub04.inc.pop_order_exchange` UI 있음 | 실제 상태 변경 action 필요 |
| 채널 교환 옵션 변경 | RF-02-07-06-8 | `channel.sub04.inc.pop_order_exchange` UI 있음 | 실제 옵션 변경 action 필요 |
| 채널 교환 송장수정 | RF-02-07-06-9 | `channel.sub04.inc.pop_order_exchange` UI 있음 | 실제 송장 변경 action 필요 |

## 작업요청서 45개 항목별 상세 매칭

### 홈페이지/마이페이지

| 요청 No | SB No | 요청 화면 | FileMap Path/Type | 현재 Laravel 매칭 | 최종 판단 |
| --- | --- | --- | --- | --- | --- |
| 1 | RF-01-06-01 | 주문조회 입력페이지 | `/homepage/order/general_order_confirm.html` Page | `front.pages.nonmember_order_check` | 구현 있음 |
| 2 | RF-01-06-02 | 주문 상세 페이지 | `/homepage/order/general_order_detail.html` Page | `front.pages.nonmember_order_details` | 구현 있음 |
| 3 | RF-01-06-02-1 | 취소신청 | Popup | `front.pages.nonmember_order_details` 내 form | 구현 있음 |
| 4 | RF-01-06-02-2 | 반품신청 | Popup | `front.pages.nonmember_order_details` 내 form | 구현 있음 |
| 5 | RF-01-06-02-3 | 교환신청 | Popup | `front.pages.nonmember_order_details` 내 form | 구현 있음 |
| 6 | RF-01-06-02-4 | 구매확정하기 | Popup | `front.pages.nonmember_order_details` | 기능/레이어 UX 재검수 |
| 7 | RF-01-06-02-5 | 상품 문의하기 | Popup | `front.pages.nonmember_order_details` | 구현 있음 |
| 8 | RF-01-07-02 | 간편 회원 약관 동의 | `/homepage/member/social_join.html` Page | `front.member.social_join` | 구현 있음 |
| 9 | RF-01-07-11-1 | 배송지 추가/설정 | `/homepage/mypage/pop.delivery_destination_register.html` Layer | `front.mypage.sub01.delivery_destination` | 구현 있음 |
| 10 | RF-01-07-11-3 | 배송지 수정 | `/homepage/mypage/pop.delivery_destination_update.html` Layer | `front.mypage.sub01.delivery_destination` | 구현 있음 |
| 11 | RF-01-07-14-1 | 방문한 채널 QR 확대 | FileMap상 방문채널 하위 Layer | `front.mypage.sub01.visited_channels` | 구현 있음 |
| 12 | RF-01-07-15-1 | 사용가능 Shop 채널 보기/QR 확대 | 포인트 현황 하위 Layer | `front.mypage.sub01.point_status` | 부분 구현. 보기 클릭 UX 확인 필요 |
| 13 | RF-01-07-16-1 | 장바구니 QR 확대 | 장바구니 하위 Layer | `front.mypage.sub01.cart_list` | 구현 있음 |

### 채널관리자

| 요청 No | SB No | 요청 화면 | FileMap Path/Type | 현재 Laravel 매칭 | 최종 판단 |
| --- | --- | --- | --- | --- | --- |
| 14 | RF-02-04-02-1 | 비공개시 접속 가능 인원관리 | Shop 채널 등록 하위 Layer | 비공개/회원전용 필드만 확인 | 신규/보강 필요 |
| 15 | RF-02-04-02-2 | 그룹 keyword 추가 화면 | `channel/shop_register.html` Page | `channel.sub01.shop_register`, `info_update` | 구현 있음 |
| 16 | RF-02-04-02-3 | 기간제 사용기간 화면 | `channel/shop_register.html` Page | `channel.sub01.shop_register`, `info_update` | 구현 있음 |
| 17 | RF-02-04-02-5-2 | 구매현황 문자알림 사용 화면 | Shop 채널 등록/수정 하위 화면 | SMS 로직은 있으나 설정 UI 불명확 | 보강 필요 |
| 18 | RF-02-04-02-6 | OG 태그 사용 화면 | `channel/shop_register.html` Page | `channel.sub01.shop_register`, `info_update` | 구현 있음 |
| 19 | RF-02-04-02-6-1 | Shop 채널 PG 정보 사용 화면 | `channel/shop_register.html` Page | `channel.sub01.shop_register`, `info_update`, `shop_info` | 구현 있음 |
| 20 | RF-02-04-02-7 | 모니터링 관리자 정보 사용 화면 | `channel/shop_register.html` Page | `channel.sub01.shop_register`, `info_update`, `shop_info` | 구현 있음 |
| 21 | RF-02-04-05-3 | Shop 상세에서 판매상품 추가시 2중 팝업 | `channel/pop.shop_product_own_add.html` Layer | `channel.sub01.inc.pop_shop_product_*` | UI 있음. 2중 팝업 검수 필요 |
| 22 | RF-02-04-05-5 | 상품 확인용 popup | `channel/pop.shop_product_public_view.html` Layer | `channel.sub01.inc.pop_shop_product_public` | 구현 있음 |
| 23 | RF-02-04-05-6 | 상품 추가용 popup/위탁판매 | `channel/pop.shop_product_public_add.html` Layer | `channel.sub01.inc.pop_shop_product_public` | 부분 구현. 위탁판매 패턴 검수 필요 |
| 24 | RF-02-04-05-8 | 상품 판매 신청 popup | `channel/pop.shop_product_partial_request.html` Layer | `channel.sub01.inc.pop_shop_product_partial` | 구현 있음 |
| 25 | RF-02-05-02-1 | 상품 대분류별 필수표시 변경 | `channel/product_register.html` Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 26 | RF-02-05-02-2 | 상품정보 등록타입별 변화 | `channel/product_register.html` Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 27 | RF-02-05-02-4 | 옵션 사용/추가/삭제 변화 | `channel/product_register.html` Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 28 | RF-02-05-02-5 | 상품 제약 조건 변화 | `channel/product_register.html` Page | `channel.sub02.product_base_edit` | 구현 있음 |
| 29 | RF-02-06-01 | 공동구매 목록 | 공동구매 Page | `channel.sub03.joint_purchase_list` | 구현 있음 |
| 30 | RF-02-06-02 | 공동구매 등록 | 공동구매 Page | `channel.sub03.joint_purchase_create` | 구현 있음 |
| 31 | RF-02-06-03 | 공동구매 등록 필수표시 퍼포먼스 | 공동구매 수정 Page | `channel.sub03.joint_purchase_create/edit` | 상품등록과 동일 수준인지 재검수 |
| 32 | RF-02-07-03-2 | 주문 배송정보 popup | `channel/pop.order_normal_delivery.html` Layer | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 33 | RF-02-07-03-3 | 반품요청 처리 popup | `channel/pop.order_normal_return_request.html` Layer | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 34 | RF-02-07-03-4 | 교환요청 처리 popup | `channel/pop.order_normal_exchange_request.html` Layer | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 35 | RF-02-07-03-5 | 취소요청 처리 popup | `channel/pop.order_normal_cancel_request.html` Layer | `channel.sub04.inc.pop_order_normal` | 구현 있음 |
| 36 | RF-02-07-05-2 | 반품보류 popup | `channel/pop.order_return_request_pending.html` Layer | `channel.sub04.inc.pop_order_return` | UI 있음. action 필요 |
| 37 | RF-02-07-05-3 | 송장정보 변경 popup | `channel/pop.order_return_request_invoice.html` Layer | `channel.sub04.inc.pop_order_return` | UI 있음. action 필요 |
| 38 | RF-02-07-06-2 | 교환회수 전 보류 popup | `channel/pop.order_exchange_request_hold_before.html` Layer | `channel.sub04.inc.pop_order_exchange` | UI 있음. action 필요 |
| 39 | RF-02-07-06-6 | 교환회수 후 보류 popup | `channel/pop.order_exchange_request_hold_after.html` Layer | `channel.sub04.inc.pop_order_exchange` | UI 있음. action 필요 |
| 40 | RF-02-07-06-8 | 교환 옵션 변경 popup | `channel/pop.order_exchange_request_option.html` Layer | `channel.sub04.inc.pop_order_exchange` | UI 있음. action 필요 |
| 41 | RF-02-07-06-9 | 송장수정 popup | `channel/pop.order_exchange_request_invoice.html` Layer | `channel.sub04.inc.pop_order_exchange` | UI 있음. action 필요 |
| 42 | RF-02-08-01 | 정산월 선택 전 정산 기본화면 | 정산관리 Page | `channel.sub05.settlement_list` | 구현 있음 |
| 43 | RF-02-08-01-2 | 정산월 선택 후 정산 기본화면 | 정산관리 Page | `channel.sub05.settlement_list`, `settlement_view` | 구현 있음 |

### Shop 채널 전체

| 요청 No | SB No | FileMap 항목 | FileMap Path/Type | 현재 Laravel 매칭 | 최종 판단 |
| --- | --- | --- | --- | --- | --- |
| 44 | RF-03-01-01 | 입장 코드 확인 | `shop/code_confirm.html` Page | `shop.gate` | 구현 있음 |
| 44 | RF-03-01-02 | 로그인 | `shop/login.html` Page | Shop 전용 로그인 없음 | 별도 화면 요구 시 신규 필요 |
| 44 | RF-03-02-01 | 간편 회원 가입 | `shop/social_join.html` Page | `front.member.social_join` | Shop 전용이면 신규 필요 |
| 44 | RF-03-03-01 | 메인 | `shop/main.html` Page | `shop.channel_main` | 구현 있음 |
| 44 | RF-03-04-01 | 일반상품 목록 | `shop/product_list.html` Page | `shop.products_list` | 구현 있음 |
| 44 | RF-03-04-02-6 | 공동구매 목록 | Page | `shop.joint_purchases_list` | 구현 있음 |
| 44 | RF-03-04-02 | 일반상품 상세 | `shop/product_view.html` Page | `shop.product_details` | 구현 있음 |
| 44 | RF-03-04-02-4 | 상품 문의하기 | `shop/pop.product_view_contact_us.html` Popup | 전용 팝업 확인 필요 | 보강 후보 |
| 44 | RF-03-04-02-7 | 공동구매 상세 | Page | `shop.joint_purchase_details` | 구현 있음 |
| 44 | RF-03-04-03 | 장바구니 | `shop/shopping_basket.html` Page | `front.shop.cart` | 구현 있음 |
| 44 | RF-03-04-03 | 옵션 변경 | `shop/pop.shopping_basket_option.html` Popup | 없음 | 신규 popup 필요 |
| 44 | RF-03-04-04 | 주문서 작성 | `shop/order_form.html` Page | `front.shop.order_form` | 구현 있음 |
| 44 | RF-03-04-05 | 주문 완료 | `shop/order_completed.html` Page | `front.shop.order_complete` | 구현 있음 |
| 44 | RF-03-04-06 | 구매내역 확인 | `shop/purchase_history.html` Page | 없음 | 신규/분리 필요 |
| 44 | RF-03-05-01 | 주문 조회 | `shop/order_confirm.html` Page | 없음 | 신규/공통화 필요 |
| 44 | RF-03-05-02 | 주문 관리 | `shop/order_details.html` Page | `front.shop.order_details` | 구현 있음 |
| 44 | RF-03-05-02-1 | 판매자 문의하기 | `shop/pop.order_details_contact_us.html` Popup | 없음 | 신규 popup 필요 |
| 44 | RF-03-05-02-2 | 취소 신청 | `shop/pop.order_details_cancel_request.html` Popup | 즉시 POST 방식 | Layer popup 필요 시 보강 |
| 44 | RF-03-05-02-3 | 반품 신청 | `shop/pop.order_details_return_request.html` Popup | 즉시 POST 방식 | Layer popup 필요 시 보강 |
| 44 | RF-03-05-02-4 | 교환 신청 | `shop/pop.order_details_exchange_request.html` Popup | 즉시 POST 방식 | Layer popup 필요 시 보강 |
| 44 | RF-03-05-02-5 | 구매 확정 | `shop/pop.order_details_purchase_confirm.html` Popup | 즉시 POST 방식 | Layer popup 필요 시 보강 |
| 44 | RF-03-05-06 | 주문 상세 | `shop/order_details_view.html` Page | `front.shop.order_details`와 혼재 | 목록/상세 분리 필요 |
| 44 | RF-03-05-03 | 취소 목록 | `shop/cancel_details.html` Page | 정적 샘플 Blade 존재 | 실제 데이터로 재작업 |
| 44 | RF-03-05-04 | 반품 목록 | `shop/return_details.html` Page | 정적 샘플 Blade 존재 | 실제 데이터로 재작업 |
| 44 | RF-03-05-05 | 교환 목록 | `shop/exchange_details.html` Page | 정적 샘플 Blade 존재 | 실제 데이터로 재작업 |
| 44 | RF-03-06-01 | 공지사항 목록 | `shop/notice_list.html` Page | `shop.notices` | 구현 있음 |
| 44 | RF-03-06-02 | 공지사항 상세 | `shop/notice_view.html` Page | 전용 상세 없음 | 신규 필요 가능 |

### 발주사

| 요청 No | SB No | FileMap 항목 | FileMap Path/Type | 현재 Laravel 매칭 | 최종 판단 |
| --- | --- | --- | --- | --- | --- |
| 45 | RF-04-01-01 | 로그인 | `purchase/login.html` Page | `distributor.login` | 구현 있음 |
| 45 | RF-04-02-01 | 발주 대기 목록 | `purchase/waiting_list.html` Page | `distributor.orders_pending` | 구현 있음 |
| 45 | RF-04-02-01-1 | 발주 처리 | `purchase/pop.process.html` Popup | `distributor.orders_pending` 내부 modal | 구현 있음 |
| 45 | RF-04-02-01-2 | 발주 대기 보기 | `purchase/pop.waiting_view.html` Popup | `distributor.order_details` 또는 내부 modal | popup 설계와 일치 여부 검수 |
| 45 | RF-04-02-01-2 | 발주 대기 수정 | `purchase/pop.waiting_update.html` Popup | `distributor.order_details` update form | popup 설계와 일치 여부 검수 |
| 45 | RF-04-02-02 | 발주 완료 목록 | `purchase/completed_list.html` Page | `distributor.orders_completed` | 구현 있음 |
| 45 | RF-04-02-02-1 | 발주 완료 보기 | `purchase/pop.completed_view.html` Popup | `distributor.order_details` | popup 설계와 일치 여부 검수 |
| 45 | RF-04-02-02-1 | 발주 완료 수정 | `purchase/pop.completed_update.html` Popup | `distributor.order_details` update form | popup 설계와 일치 여부 검수 |

## 신규/재작업 파일 후보 전체 목록

### 신규 Blade 후보

- `resources/views/shop/login.blade.php`
- `resources/views/shop/social_join.blade.php`
- `resources/views/shop/notice_view.blade.php`
- `resources/views/front/shop/purchase_history.blade.php`
- `resources/views/front/shop/order_confirm.blade.php`
- `resources/views/front/shop/order_details_view.blade.php`
- `resources/views/front/shop/inc/pop_product_contact.blade.php`
- `resources/views/front/shop/inc/pop_cart_option.blade.php`
- `resources/views/front/shop/inc/pop_order_contact.blade.php`
- `resources/views/front/shop/inc/pop_order_cancel_request.blade.php`
- `resources/views/front/shop/inc/pop_order_return_request.blade.php`
- `resources/views/front/shop/inc/pop_order_exchange_request.blade.php`
- `resources/views/front/shop/inc/pop_order_purchase_confirm.blade.php`
- `resources/views/channel/sub01/inc/pop_shop_access_members.blade.php`

### 기존 Blade 재작업 후보

- `resources/views/front/shop/cancel_details.blade.php`
- `resources/views/front/shop/return_details.blade.php`
- `resources/views/front/shop/exchange_details.blade.php`
- `resources/views/front/shop/order_details.blade.php`
- `resources/views/front/shop/cart.blade.php`
- `resources/views/channel/sub01/shop_register.blade.php`
- `resources/views/channel/sub01/info_update.blade.php`
- `resources/views/channel/sub03/joint_purchase_create.blade.php`
- `resources/views/channel/sub03/joint_purchase_edit.blade.php`
- `resources/views/channel/sub04/inc/pop_order_return.blade.php`
- `resources/views/channel/sub04/inc/pop_order_exchange.blade.php`

### 운영 대상에서 제외하거나 정리해야 할 샘플/중복 파일

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

## 필요한 외부/기획 문서

개발을 확정하려면 아래 문서 또는 명확한 의사결정이 필요합니다.

| 필요한 문서/결정 | 이유 |
| --- | --- |
| Shop 채널 `25.11.07 Shop채널_img.zip` 원본 또는 최신 캡처 | FileMap RF-03 전체가 이 이미지 제공본을 기준으로 되어 있음 |
| Shop 전용 로그인/간편가입을 실제로 별도 구현할지 결정 | 기존 회원 로그인/간편가입 재사용 여부에 따라 신규 Blade 필요성이 달라짐 |
| Shop 주문조회/구매내역/주문상세의 URL 구조 확정 | `order_confirm`, `purchase_history`, `order_details_view`를 새로 만들지 기존 `order_details`를 확장할지 결정 필요 |
| 취소/반품/교환 목록이 “목록”인지 “상세”인지 명확화 | FileMap 명칭은 목록이지만 현재 파일명은 details라 route 설계가 혼재됨 |
| 채널 주문 보류/송장/옵션 변경의 상태 코드 정의 | UI는 있으나 실제 상태 transition/action을 안정적으로 구현하려면 상태표 필요 |
| 비공개 Shop 접속 가능 인원관리 정책 | 회원번호 기준인지 사용자 ID 기준인지, 대량등록/검색/삭제가 필요한지 결정 필요 |
| 구매현황 문자알림 설정 범위 | 주문접수/배송/취소/반품/교환 중 어느 상태에서 문자 발송할지 설정 범위 필요 |

## 우선 개발 순서

1. `PaypalController::success()`의 없는 `cart` view 호출 수정
2. Shop 주문조회/구매내역/주문상세 URL 구조 확정 및 구현
3. Shop 취소/반품/교환 파일의 정적 샘플 제거 및 실제 데이터 연결
4. Shop 주문상세의 취소/반품/교환/구매확정/문의 popup 구현
5. 채널 주문관리의 보류/송장/옵션 변경 실제 action 구현
6. 비공개 Shop 접속 가능 인원관리와 구매현황 문자알림 설정 추가
7. 샘플/중복 Blade 정리
