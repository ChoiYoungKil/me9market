# Route and Functional Specification (라우터 기준 URL 및 기능 정의서)

본 문서는 현재까지 구축 및 테스트가 완료된 쇼핑몰 플랫폼의 전체 URL 경로와 각 기능의 설명서입니다. 

---

## 1. 시스템 관리자 포털 라우트 (`/admin`)

시스템 전체를 통제하고 판매자 가입 승인, 카테고리, 상품, 주문, 정산 수수료 등을 관리하는 최고 관리자 페이지입니다. (가드: `admin`, 컨트롤러 네임스페이스: `App\Http\Controllers\Admin`)

| HTTP Method | URL 경로 | 라우트 명 | 매핑 컨트롤러 & 액션 | 기능 설명 |
| :--- | :--- | :--- | :--- | :--- |
| **GET\|POST** | `/admin/login` | `admin.login` | `AdminController@login` | 관리자 로그인 폼 노출 및 세션 생성 |
| **GET** | `/admin/logout` | - | `AdminController@logout` | 관리자 세션 파기 및 로그아웃 |
| **GET** | `/admin/dashboard` | `admin.dashboard` | `AdminController@dashboard` | 전체 통계 카운트(상품, 주문, 회원 등) 대시보드 |
| **GET** | `/admin/admins/{type?}` | `admin.admins` | `AdminController@admins` | 관리자 등급별(superadmin, admin, subadmin, vendor) 목록 조회 및 검색 |
| **GET\|POST** | `/admin/add-edit-admin/{id?}` | - | `AdminController@addEditAdmin` | 관리자/판매자 계정 신규 생성 및 정보 수정 |
| **GET** | `/admin/delete-admin/{id}` | - | `AdminController@deleteAdmin` | 특정 관리자/판매자 레코드 및 연관 데이터(상점 등) 삭제 |
| **GET** | `/admin/view-vendor-details/{id}`| - | `AdminController@viewVendorDetails` | 특정 입점업체의 개인/사업자/정산 계좌 상세 정보 확인 |
| **POST** | `/admin/update-admin-status` | - | `AdminController@updateAdminStatus` | AJAX를 통한 관리자 활성화/비활성화 상태 변경 및 승인 메일 발송 |
| **POST** | `/admin/update-vendor-certification`| -| `AdminController@updateVendorCertification` | 판매자 승인 상태(상태값 1 또는 0)를 업데이트하여 활동 허용 설정 |
| **POST** | `/admin/update-vendor-commission`| - | `AdminController@updateVendorCommission` | 특정 판매자에 대한 판매 수수료율(commission)을 관리자 기준으로 설정 |
| **GET\|POST** | `/admin/update-vendor-details/{slug}`| -| `AdminController@updateVendorDetails` | 판매자용 프로필 수정 (`slug`: `personal`, `business`, `bank`) |
| **GET** | `/admin/sections` | `admin.sections` | `SectionController@sections` | 대분류 섹션 목록 관리 |
| **GET** | `/admin/categories` | `admin.categories` | `CategoryController@categories` | 중분류/소분류 다단계 카테고리 관리 |
| **GET** | `/admin/brands` | `admin.brands` | `BrandController@brands` | 상품 브랜드 목록 관리 |
| **GET** | `/admin/products` | `admin.products` | `ProductsController@products` | 전체 등록 상품 목록 관리 및 검색 |
| **GET** | `/admin/banners` | `admin.banners` | `BannersController@banners` | 슬라이드/고정 배너 이미지 목록 관리 |
| **GET** | `/admin/notices` | `admin.notices` | `SupportController@notices` | 관리자 전용 고객 공지사항 목록 관리 |
| **GET** | `/admin/faqs` | `admin.faqs` | `SupportController@faqs` | 자주 묻는 질문(FAQ) 목록 관리 |
| **GET** | `/admin/contacts` | `admin.contacts` | `SupportController@contacts` | 1:1 고객 문의 목록 조회 |
| **GET** | `/admin/coupons` | `admin.coupons` | `CouponsController@coupons` | 쿠폰(정액/정률) 목록 조회 및 혜택 관리 |
| **GET** | `/admin/users` | `admin.users` | `UserController@users` | 가입된 일반 회원 목록 관리 |
| **GET** | `/admin/orders` | `admin.orders` | `OrderController@orders` | 플랫폼 전체 고객 주문 목록 확인 |
| **GET** | `/admin/subscribers` | `admin.subscribers` | `NewsletterController@subscribers` | 뉴스레터 구독 신청자 목록 및 엑셀 다운로드 관리 |

---

## 2. 셀러/판매자 채널 포털 라우트 (`/channel`)

입점 판매자들이 자신의 상점 정보를 관리하고, 상품 등록(자사/공유), 정산 상태 조회, 고객 주문 처리를 수행하는 영역입니다. (가드: `admin` 중 `type == 'vendor'`)

| HTTP Method | URL 경로 | 라우트 명 | 매핑 컨트롤러 & 액션 | 기능 설명 |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/channel` | `channel.index` | `Front\ChannelController@index` | 셀러 전용 대시보드(월간 매출 그래프, 배송 현황 카운트 등) |
| **GET** | `/channel/login` | `channel.login` | `Front\ChannelController@login` | 셀러 전용 로그인 화면 |
| **GET\|POST** | `/channel/register` | `channel.register` / `.submit` | `Front\ChannelController@register` | 셀러 기본 회원가입 폼 제공 및 AJAX 가입 신청 처리 |
| **GET\|POST** | `/channel/complete-profile` | `channel.complete_profile` / `.submit` | `Front\ChannelController@completeProfile` | 셀러 가입 승인 대기 단계에서 상점, 사업자, 계좌 등록 |
| **GET** | `/channel/shop/list` | `channel.shop_list` | `Front\ChannelController@shopList` | 판매자가 보유한 Shop채널 목록 조회 및 검색 |
| **GET\|POST** | `/channel/shop/register` | `channel.shop_register` / `.submit` | `Front\ChannelController@shopRegister` | 신규 판매 Shop채널 개설 신청 |
| **GET** | `/channel/shop/info` | `channel.shop_info` | `Front\ChannelController@shopInfo` | 개설된 Shop채널 정보 조회 (상점명, 도메인, 로고 등) |
| **GET\|POST** | `/channel/shop/info-update/{id}`| `channel.info_update` / `.submit` | `Front\ChannelController@infoUpdate` | 개설된 Shop채널 정보 수정 |
| **GET** | `/channel/shop/product01` | `channel.shop_product01` | `Front\ChannelController@shopProduct01` | 채널 내 판매 중인 상품 목록 조회 및 판매 상품 추가 팝업 렌더링 |
| **GET** | `/channel/shop/product02` | `channel.shop_product02` | `Front\ChannelController@shopProduct02` | 채널 내 판매 중지(정지)된 상품 목록 조회 및 복원/재개 |
| **GET\|POST** | `/channel/shop/product/edit/{id}`| `channel.product.edit` / `.update` | `Front\ChannelProductController@editShopProduct` | 채널 판매 상품 상세 가격 조건, 마진액 설정 수정 |
| **POST** | `/channel/shop/product/own/store` | `channel.product.own.store` | `Front\ChannelProductController@storeOwnProduct` | 셀러가 자체 보유한 지사 상품(Own)을 판매 채널에 등록 |
| **POST** | `/channel/shop/product/public/store`| `channel.product.public.store`| `Front\ChannelProductController@storePublicProduct` | 공용 베이스 상품(Public)을 가져와 판매 채널에 공유 매핑 |
| **POST** | `/channel/shop/product/partial/store`| `channel.product.partial.store`| `Front\ChannelProductController@storePartialProduct` | 일부 권한 상품(Partial)을 가져와 판매 채널에 공유 매핑 |
| **POST** | `/channel/shop/product/partial/request`| `channel.product.partial.request`| `Front\ChannelProductController@requestPartialProduct`| 부분 권한 상품에 대해 입점사 판매 허가 신청 제출 |
| **POST** | `/channel/shop/product/status/update`| `channel.product.status.update`| `Front\ChannelProductController@updateProductStatus`| 개별 채널 상품의 상태 변경(판매중/판매중지) |
| **POST** | `/channel/shop/product/request/update`| `channel.product.request.update`| `Front\ChannelProductController@updateRequestStatus`| 판매 요청 승인 상태 처리 |
| **GET** | `/channel/product/own` | `channel.product_own` | `Front\ChannelController@productOwn` | 판매자 본인이 직접 제조/등록한 기초 베이스 상품 리스트 |
| **GET** | `/channel/product/public` | `channel.product_public` | `Front\ChannelController@productPublic` | 본사가 공개하여 아무나 판매할 수 있는 전체 기초 베이스 상품 리스트 |
| **GET** | `/channel/product/partial` | `channel.product_partial` | `Front\ChannelController@productPartial` | 승인을 받아야만 판매할 수 있는 일부 공개용 기초 베이스 상품 리스트 |
| **GET** | `/channel/product/request` | `channel.product_request` | `Front\ChannelController@productRequest` | 승인 요청 진행 중인 베이스 상품의 상태 조회 목록 |
| **GET** | `/channel/product/base/detail/{id}`| `channel.product.base.detail` | `Front\ChannelProductController@getBaseProductDetail` | 등록 전 기초 베이스 상품 스펙 상세 정보 확인 |
| **GET\|POST** | `/channel/product/base/edit/{id}` | `channel.product.base.edit` / `.update` | `Front\ChannelProductController@editBaseProduct` | 기초 베이스 상품 사양(이름, 단가 등) 수정 |
| **POST** | `/channel/product/base/delete/{id}`| `channel.product.base.delete` | `Front\ChannelProductController@deleteBaseProduct` | 본인 소유의 베이스 상품 완전 삭제 |
| **POST** | `/channel/product/base/copy/{id}` | `channel.product.base.copy` | `Front\ChannelProductController@copyBaseProduct` | 기존 베이스 상품 구성을 그대로 복사하여 복사본 신규 생성 |
| **GET** | `/channel/shop/community` | `channel.shop_community` | `Front\ChannelController@shopCommunity` | 각 상점 채널별 커뮤니티(소식지/공지사항) 피드 목록 |
| **GET\|POST** | `/channel/shop/community/register` | `channel.community.register` / `.submit`| `Front\ChannelController@communityRegister` | 공지사항 소식 글 작성 및 저장 |
| **GET** | `/channel/shop/community/view/{id}` | `channel.community.view` | `Front\ChannelController@communityView` | 공지사항 게시글 상세 보기 및 조회수 증가 |
| **GET\|POST** | `/channel/shop/community/update/{id}`| `channel.community.update` / `.submit`| `Front\ChannelController@communityUpdate` | 작성한 공지사항 수정 |
| **POST** | `/channel/shop/community/delete/{id}`| `channel.community.delete` | `Front\ChannelController@communityDelete` | 작성한 공지사항 삭제 |
| **GET** | `/channel/order/list` | `channel.order.list` | `Front\ChannelController@orderList` | 해당 판매자 상품이 포함된 전체 신규 주문 내역 조회 |
| **GET** | `/channel/order/cancel/list` | `channel.order.cancel_list` | `Front\ChannelController@orderCancelList` | 판매 취소 처리된 주문 목록 조회 |
| **GET** | `/channel/order/return/list` | `channel.order.return_list` | `Front\ChannelController@orderReturnRequestList` | 반품 요청(반품 중/완료) 목록 조회 |
| **GET** | `/channel/order/exchange/list` | `channel.order.exchange_list` | `Front\ChannelController@orderExchangeRequestList`| 교환 요청 목록 조회 |
| **POST** | `/channel/order/status/update` | `channel.order.status.update` | `Front\ChannelOrderController@updateStatus` | 주문 상품에 택배 배송사 및 송장(Tracking) 정보 매핑 및 배송중 처리 |
| **POST** | `/channel/order/cancel/request` | `channel.order.cancel.request` | `Front\ChannelOrderController@requestCancel` | 판매자 사유 취소 처리 및 취소 `OrderClaim` 강제 등록 |
| **POST** | `/channel/order/return/request` | `channel.order.return.request` | `Front\ChannelOrderController@requestReturn` | 반품 건에 대한 수령 및 동의 `OrderClaim` 처리 |
| **POST** | `/channel/order/exchange/request` | `channel.order.exchange.request` | `Front\ChannelOrderController@requestExchange` | 교환 대상 접수 및 교환 `OrderClaim` 처리 |
| **GET** | `/channel/settlement/list` | `channel.settlement.list` | `Front\ChannelSettlementController@index` | 판매자별 월별 구매확정 기준 정산 리스트 및 마진 분배 요약 |
| **GET** | `/channel/settlement/view/{period}`| `channel.settlement.view` | `Front\ChannelSettlementController@view` | 해당 정산 월(`YYYY-MM`)의 상세 주문 건별 수수료 차감 내역 정보 |
| **GET** | `/channel/settings/delivery` | `channel.delivery.list` | `Front\ChannelController@deliveryChargeList` | 기본 배송 정책 및 도서산간 배송비 설정 |
| **GET** | `/channel/settings/refund` | `channel.refund.list` | `Front\ChannelController@cancelRefundList` | 취소 및 환불 안내 가이드 목록 관리 |
| **POST** | `/channel/settings/refund/store` | `channel.refund.store` | `Front\ChannelController@storeCancelRefundPolicy` | 신규 취소/환불 기준 작성 등록 |
| **GET** | `/channel/settings/refund/{id}` | `channel.refund.get` | `Front\Front\ChannelController@getCancelRefundPolicy`| 특정 취소/환불 기준 정보 상세 조회 |
| **POST** | `/channel/settings/refund/{id}/update`| `channel.refund.update` | `Front\ChannelController@updateCancelRefundPolicy`| 취소/환불 기준 정책 정보 수정 |
| **POST** | `/channel/settings/refund/{id}/delete`| `channel.refund.delete` | `Front\ChannelController@deleteCancelRefundPolicy`| 지정한 취소/환불 정책 삭제 |
| **POST** | `/channel/settings/refund/{id}/copy` | `channel.refund.copy` | `Front\ChannelController@copyCancelRefundPolicy` | 기존 취소/환불 안내 정책 복제 추가 |
| **GET** | `/channel/settings/info` | `channel.info.management` | `Front\ChannelController@infoManagement` | 판매자 개인 정보 수정 화면 |
| **POST** | `/channel/settings/info/update` | `channel.info.update` | `Front\ChannelController@updateInfo` | 비밀번호 및 연락처 정보 수정 처리 |
| **POST** | `/channel/settings/update-password`| `channel.update_password` | `Front\ChannelController@updatePassword` | 비밀번호 변경 AJAX 처리 |
| **GET** | `/channel/settings/sub-accounts` | `channel.sub_accounts.list` | `Front\ChannelController@subList` | 부관리자(Sub-Account) 계정 목록 관리 |

---

## 3. 일반 사용자 및 마이페이지 라우트 (`/mypage` 및 공통)

구매자들의 로그인, 비밀번호 찾기, 주문 확인 및 배송 주소 관리를 수행하는 영역입니다. (가드: `web`)

| HTTP Method | URL 경로 | 라우트 명 | 매핑 컨트롤러 & 액션 | 기능 설명 |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/register/member` | `front.member.register.member` | `Front\UserController@register` | 다단계 회원가입 기본 진입점 |
| **POST** | `/register/step1/update` | `front.member.register.step1.update`| `Front\UserController@updateMemberStep1` | 회원가입 1단계: 아이디/휴대폰 점검 및 정보 등록 |
| **POST** | `/register/step2/update` | `front.member.register.step2.update`| `Front\UserController@updateMemberStep2` | 회원가입 2단계: 회원 주소지 정보 등록 |
| **POST** | `/register/step3/update` | `front.member.register.step3.update`| `Front\UserController@updateMemberStep3` | 회원가입 3단계: 가입 완료 및 동의 정보 기록 |
| **GET\|POST** | `/find/id` | `front.member.find_id` | `Front\UserController@findId` | 휴대폰 번호 기반 아이디 찾기 |
| **GET\|POST** | `/find/pw` | `front.member.find_pw` | `Front\UserController@findPw` | 이메일 기반 임시 비밀번호 발송 |
| **GET** | `/mypage/main` | `mypage.dashboard` | `Front\UserController@dashboard` | 마이페이지 대시보드(최근 주문 요약, 포인트 현황 노출) |
| **GET** | `/mypage/profile` | `mypage.profile` | `Front\UserController@profileEdit` | 본인 개인 프로필 편집 |
| **POST** | `/mypage/profile` | `mypage.profile.update` | `Front\UserController@profileUpdate` | 이메일, 주소, 이름 정보 최종 업데이트 |
| **GET** | `/mypage/delivery` | `mypage.delivery` | `Front\UserController@delivery` | 등록된 배송 주소록 전체 관리 목록 |
| **POST** | `/mypage/delivery/add` | `mypage.delivery.add` | `Front\UserController@addDeliveryAddress` | 신규 배송지 등록 |
| **POST** | `/mypage/delivery/update` | `mypage.delivery.update` | `Front\UserController@updateDeliveryAddress`| 저장된 배송지 정보(수령인, 주소 등) 수정 |
| **POST** | `/mypage/delivery/update/default`| `mypage.delivery.update_default`| `Front\UserController@setDefaultDeliveryAddress`| 기본 배송지로 선택 지정 |
| **POST** | `/mypage/delivery/delete` | `mypage.delivery.delete` | `Front\UserController@deleteDeliveryAddress`| 주소록에서 특정 배송지 삭제 |
| **GET** | `/mypage/withdraw` | `mypage.withdraw` | `Front\UserController@withdraw` | 회원 자진 탈퇴 동의 페이지 |
| **POST** | `/mypage/withdraw/submit` | `mypage.withdraw.submit` | `Front\UserController@withdrawSubmit` | 탈퇴 정보 최종 제출 및 회원 상태 삭제(Withdrawal) 처리 |
