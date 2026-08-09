# Publishing / Blade Gap Report

점검 기준일: 2026-08-09

## 점검 기준

- `resources/views` 전체 Blade 파일 수: 282개
- 컨트롤러/라우트에서 직접 참조하는 view 명: 167개
- 확인 패턴: `url()->current()`, `javascript:void(0)`, `data-pop`, `popup_bx`, 샘플 문구, 정적 주문번호/상품명, 컨트롤러 view 참조 누락

## 최우선 보강 필요

| 구분 | 현재 파일/위치 | 현재 상태 | 필요한 작업 |
| --- | --- | --- | --- |
| 결제 실패 복귀 장바구니 | `app/Http/Controllers/Front/PaypalController.php:71` | `return view('cart')`를 호출하지만 `resources/views/cart.blade.php`가 없음 | 새 Blade 생성보다 기존 실제 장바구니인 `front.products.cart`, `front.shop.cart`, 또는 `mypage.cart` 중 의도에 맞는 화면으로 redirect/view 정리 필요 |
| Shop 취소 상세 | `routes/web.php:370`, `app/Http/Controllers/Front/ShopController.php:156`, `resources/views/front/shop/cancel_details.blade.php` | 라우트에 주문/클레임 식별자 없음. Blade에 `상품명 111111`, `txx2212`, 정적 금액/사유가 남아 있음 | `/shop/cancel/details/{claim}` 또는 `{orderItem}` 기준으로 라우트/컨트롤러/Blade 실제 데이터 연결 필요 |
| Shop 반품 상세 | `routes/web.php:372`, `app/Http/Controllers/Front/ShopController.php:166`, `resources/views/front/shop/return_details.blade.php` | 라우트에 식별자 없음. Blade가 정적 데이터와 인라인 스타일 중심 | 반품 클레임 상세 데이터 연결 및 기존 쇼핑몰/마이페이지 스타일로 재퍼블리싱 필요 |
| Shop 교환 상세 | `routes/web.php:371`, `app/Http/Controllers/Front/ShopController.php:161`, `resources/views/front/shop/exchange_details.blade.php` | 라우트에 식별자 없음. Blade에 `Me9-RE-00929111`, `상품명 111111` 등 샘플 데이터 존재 | 교환 클레임 상세 데이터 연결 및 교환 주문번호/추가비용/포인트 환불 실제 계산값 출력 필요 |

## 운영 라우트 미연결 또는 중복 가능성이 큰 퍼블리싱 파일

| 파일 | 판단 | 필요한 작업 |
| --- | --- | --- |
| `resources/views/front/cart/index.blade.php` | `CartController`는 존재하지만 현재 `routes/web.php`에서 직접 연결된 GET 라우트가 확인되지 않음. 파일 내부에 `[예시]` 상품과 정적 금액 존재 | 실제 사용할 화면이면 라우트 추가 및 CRUD 연결. 사용하지 않을 화면이면 제거/보관 또는 기존 `front.shop.cart`/`front.products.cart`로 통합 |
| `resources/views/front/checkout/index.blade.php` | `CheckoutController`는 존재하지만 라우트 연결이 확인되지 않음. 정적 결제 금액 `56,500원` 존재 | 실제 주문서로 사용할지 결정 후 기존 `/shop/order` 흐름과 통합 필요 |
| `resources/views/front/payment/index.blade.php` | `PaymentController`는 존재하지만 라우트 연결이 확인되지 않음. 정적 주문번호/금액 존재 | 결제 완료 화면으로 사용할지 결정 후 기존 `/shop/order/complete` 또는 `front.products.thanks`와 통합 필요 |
| `resources/views/shop/sub/*.blade.php` | 현재 컨트롤러에서 반환하는 경로가 없음. 내부 route 명도 `shop.order`, `shop.cancel.details` 등 현재 활성 route prefix(`front.shop.*`)와 불일치 | 구 퍼블리싱 샘플로 분류. 실제 운영에 쓸 화면이면 route 명/데이터/폼 action 전면 재작업 필요 |

## 퍼블리싱 샘플 또는 템플릿 성격 파일

| 파일 | 근거 | 처리 의견 |
| --- | --- | --- |
| `resources/views/admin/sub/sub01.blade.php` | `대제목 #1`, `중제목 #1`, 샘플 이메일, `url()->current()` | 운영 메뉴에서 제거하거나 실제 관리자 기능으로 재정의 필요 |
| `resources/views/admin/sub/sub02.blade.php` | 다수 샘플 테이블/폼, `url()->current()` | 운영 기능 아님. 필요한 경우 별도 기능명으로 Blade 재작성 |
| `resources/views/admin/sub/sub03.blade.php` | `메인 타이틑`, 대분류/상품명 샘플, 다수 현재 URL 링크 | 운영 기능 아님. 상품/정산/회원 등 실제 도메인과 매핑 필요 |
| `resources/views/admin/sub/view.blade.php` | 첨부파일명 샘플, 댓글 입력/수정/삭제가 실제 action 없음 | 게시판 상세 샘플. 실제 게시판 기능으로 쓸 경우 CRUD route와 form 연결 필요 |
| `resources/views/admin/sub/newpage.blade.php` | 독립 HTML 구조, `상품명 111111`, 카테고리 샘플 | 운영 레이아웃과 분리된 퍼블리싱 샘플. 사용 여부 결정 필요 |
| `resources/views/admin/sub/layer_large.blade.php` | 레이어 샘플 페이지 | 실제 팝업 화면으로 쓸 경우 대상 기능과 연결 필요 |
| `resources/views/master/sub01.blade.php` | 관리자 샘플과 동일한 대제목/중제목/샘플 이메일 | `MasterController`가 admin 샘플로 redirect 중. 운영 메뉴 필요성 재검토 |
| `resources/views/master/sub02.blade.php` | 샘플 콘텐츠/표 | 위와 동일 |
| `resources/views/master/sub03.blade.php` | 샘플 검색/상품명/카테고리/현재 URL 링크 | 위와 동일 |

## 레이어 팝업으로 구현되어 별도 Blade 추가 여부 확인이 필요한 영역

이 항목들은 무조건 누락은 아닙니다. 현재 설계가 “레이어 처리”라면 유지 가능하지만, 스토리보드에서 별도 페이지로 지정되어 있으면 `.blade.php` 분리가 필요합니다.

| 영역 | 현재 파일 | 확인 필요 |
| --- | --- | --- |
| 채널 주문 상세 팝업 | `resources/views/channel/sub04/inc/pop_order_info.blade.php`, `pop_order_normal.blade.php`, `pop_order_cancel.blade.php`, `pop_order_return.blade.php`, `pop_order_exchange.blade.php` | 주문/취소/반품/교환 상세가 레이어가 맞는지, 별도 상세 페이지가 필요한지 확인 필요 |
| 채널 상품 상세/복사/요청 팝업 | `resources/views/channel/sub01/inc/pop_shop_product_*.blade.php`, `resources/views/channel/sub02/inc/*.blade.php` | 상품 상세가 팝업 설계인지 별도 상세/수정 페이지 설계인지 확인 필요 |
| 마이페이지 주문 액션 팝업 | `resources/views/front/mypage/order/list.blade.php` | 취소/반품/교환/구매확정 신청은 AJAX form으로 연결되어 있어 기능 누락으로 보이지 않음. 다만 상세 화면은 `order/view` 중심으로 정리 필요 |
| 관리자 발주사 등록/수정 팝업 | `resources/views/admin/order_managers/index.blade.php` | 등록/수정/삭제/상태 변경 form은 연결되어 있음. 별도 페이지가 필요한 설계인지 확인만 필요 |

## 현재 운영 기능으로 보기 어려운 파일 목록

다음 파일들은 샘플 데이터, 현재 URL 링크, 잘못된 route prefix, 또는 컨트롤러 미연결 때문에 그대로 운영 화면으로 쓰기 어렵습니다.

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
- `resources/views/admin/sub/sub01.blade.php`
- `resources/views/admin/sub/sub02.blade.php`
- `resources/views/admin/sub/sub03.blade.php`
- `resources/views/admin/sub/view.blade.php`
- `resources/views/admin/sub/newpage.blade.php`
- `resources/views/admin/sub/layer_large.blade.php`
- `resources/views/master/sub01.blade.php`
- `resources/views/master/sub02.blade.php`
- `resources/views/master/sub03.blade.php`

## 권장 작업 순서

1. `PaypalController`의 `view('cart')` 누락을 기존 실제 장바구니로 정리
2. `/shop/cancel/details`, `/shop/return/details`, `/shop/exchange/details`를 식별자 기반 실제 상세 페이지로 재구성
3. `front/cart`, `front/checkout`, `front/payment`의 사용 여부 결정 후 라우트 연결 또는 제거
4. `shop/sub/*` 구 퍼블리싱 샘플을 운영 대상에서 제외하거나 현재 `front.shop.*` 구조로 이관
5. `admin/sub/*`, `master/*` 샘플 라우트를 운영 메뉴에서 제거하거나 실제 기능으로 재정의
6. 스토리보드에서 레이어가 아닌 별도 페이지로 지정된 팝업 영역만 Blade 분리
