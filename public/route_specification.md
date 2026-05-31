# Route and Functional Specification (최종 라우터 및 기능 정의서)

본 문서는 플랫폼의 최종 실서버 도메인을 기준으로 매핑된 전체 URL 경로와 각 기능에 대한 명세서입니다.

---

## 1. 🖥️ 최고 관리자 포털 (도메인: http://admin.replyer.co.kr)

최고 관리자 및 부관리자가 로그인하여 사이트 전체 환경설정, 회원, 카테고리, 상품, 수수료, 주문을 최종 통제하는 영역입니다. (인증 Guard: `admin`)

| HTTP Method | 기능 및 상세 URL | 라우트 명 | 매핑 컨트롤러 & 액션 | 기능 설명 |
| :--- | :--- | :--- | :--- | :--- |
| **GET\|POST** | `http://admin.replyer.co.kr/admin/login` | `admin.login` | `AdminController@login` | 관리자 로그인 폼 노출 및 세션 생성 |
| **GET** | `http://admin.replyer.co.kr/admin/logout` | - | `AdminController@logout` | 관리자 세션 파기 및 로그아웃 |
| **GET** | `http://admin.replyer.co.kr/admin/dashboard` | `admin.dashboard` | `AdminController@dashboard` | 전체 통계 카운트(상품, 주문, 회원 등) 대시보드 |
| **GET** | `http://admin.replyer.co.kr/admin/admins/{type?}` | `admin.admins` | `AdminController@admins` | 관리자 등급별(superadmin, admin, subadmin, vendor) 목록 조회 및 검색 |
| **GET\|POST** | `http://admin.replyer.co.kr/admin/add-edit-admin/{id?}` | - | `AdminController@addEditAdmin` | 관리자/판매자 계정 신규 생성 및 정보 수정 |
| **GET** | `http://admin.replyer.co.kr/admin/delete-admin/{id}` | - | `AdminController@deleteAdmin` | 특정 관리자/판매자 레코드 및 연관 데이터(상점 등) 삭제 |
| **GET** | `http://admin.replyer.co.kr/admin/view-vendor-details/{id}` | - | `AdminController@viewVendorDetails` | 특정 입점업체의 개인/사업자/정산 계좌 상세 정보 확인 |
| **POST** | `http://admin.replyer.co.kr/admin/update-admin-status` | - | `AdminController@updateAdminStatus` | AJAX를 통한 관리자 활성화/비활성화 상태 변경 및 승인 메일 발송 |
| **POST** | `http://admin.replyer.co.kr/admin/update-vendor-certification`| - | `AdminController@updateVendorCertification` | 판매자 승인 상태(상태값 1 또는 0)를 업데이트하여 활동 허용 설정 |
| **POST** | `http://admin.replyer.co.kr/admin/update-vendor-commission` | - | `AdminController@updateVendorCommission` | 특정 판매자에 대한 판매 수수료율(commission)을 관리자 기준으로 설정 |
| **GET\|POST** | `http://admin.replyer.co.kr/admin/update-vendor-details/{slug}` | - | `AdminController@updateVendorDetails` | 판매자용 프로필 수정 (`slug`: `personal`, `business`, `bank`) |
| **GET** | `http://admin.replyer.co.kr/admin/sections` | `admin.sections` | `SectionController@sections` | 대분류 섹션 목록 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/categories` | `admin.categories` | `CategoryController@categories` | 중분류/소분류 다단계 카테고리 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/brands` | `admin.brands` | `BrandController@brands` | 상품 브랜드 목록 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/products` | `admin.products` | `ProductsController@products` | 전체 등록 상품 목록 관리 및 검색 |
| **GET** | `http://admin.replyer.co.kr/admin/banners` | `admin.banners` | `BannersController@banners` | 슬라이드/고정 배너 이미지 목록 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/notices` | `admin.notices` | `SupportController@notices` | 관리자 전용 고객 공지사항 목록 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/faqs` | `admin.faqs` | `SupportController@faqs` | 자주 묻는 질문(FAQ) 목록 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/contacts` | `admin.contacts` | `SupportController@contacts` | 1:1 고객 문의 목록 조회 |
| **GET** | `http://admin.replyer.co.kr/admin/coupons` | `admin.coupons` | `CouponsController@coupons` | 쿠폰(정액/정률) 목록 조회 및 혜택 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/users` | `admin.users` | `UserController@users` | 가입된 일반 회원 목록 관리 |
| **GET** | `http://admin.replyer.co.kr/admin/orders` | `admin.orders` | `OrderController@orders` | 플랫폼 전체 고객 주문 목록 확인 |
| **GET** | `http://admin.replyer.co.kr/admin/subscribers` | `admin.subscribers` | `NewsletterController@subscribers` | 뉴스레터 구독 신청자 목록 및 엑셀 다운로드 관리 |

---

## 2. 🏪 셀러/판매자 채널 포털 (도메인: http://front.replyer.co.kr)

입점 판매자가 자신의 샵 정보를 구성하고, 자사/공용 상품을 개설/매핑하며, 주문/배송 처리 및 정산 분배 상태를 조회하는 영역입니다. (인증 Guard: `admin` 중 `type == 'vendor'`)

| HTTP Method | 기능 및 상세 URL | 라우트 명 | 매핑 컨트롤러 & 액션 | 기능 설명 |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `http://front.replyer.co.kr/channel` | `channel.index` | `Front\ChannelController@index` | 셀러 전용 대시보드(월간 매출 그래프, 배송 현황 카운트 등) |
| **GET** | `http://front.replyer.co.kr/channel/login` | `channel.login` | `Front\ChannelController@login` | 셀러 전용 로그인 화면 (단독 분리 완료) |
| **GET\|POST** | `http://front.replyer.co.kr/channel/register` | `channel.register` / `.submit` | `Front\ChannelController@register` | 셀러 기본 회원가입 폼 제공 및 AJAX 가입 신청 처리 |
| **GET\|POST** | `http://front.replyer.co.kr/channel/complete-profile` | `channel.complete_profile` / `.submit` | `Front\ChannelController@completeProfile` | 셀러 가입 승인 대기 단계에서 상점, 사업자, 계좌 등록 |
| **GET** | `http://front.replyer.co.kr/channel/shop/list` | `channel.shop_list` | `Front\ChannelController@shopList` | 판매자가 보유한 Shop채널 목록 조회 및 검색 |
| **GET\|POST** | `http://front.replyer.co.kr/channel/shop/register` | `channel.shop_register` / `.submit` | `Front\ChannelController@shopRegister` | 신규 판매 Shop채널 개설 신청 |
| **GET** | `http://front.replyer.co.kr/channel/shop/info` | `channel.shop_info` | `Front\ChannelController@shopInfo` | 개설된 Shop채널 정보 조회 (상점명, 도메인, 로고 등) |
| **GET\|POST** | `http://front.replyer.co.kr/channel/shop/info-update/{id}`| `channel.info_update` / `.submit` | `Front\ChannelController@infoUpdate` | 개설된 Shop채널 정보 수정 |
| **GET** | `http://front.replyer.co.kr/channel/shop/product01` | `channel.shop_product01` | `Front\ChannelController@shopProduct01` | 채널 내 판매 중인 상품 목록 조회 및 판매 상품 추가 팝업 렌더링 |
| **GET** | `http://front.replyer.co.kr/channel/shop/product02` | `channel.shop_product02` | `Front\ChannelController@shopProduct02` | 채널 내 판매 중지(정지)된 상품 목록 조회 및 복원/재개 |
| **GET\|POST** | `http://front.replyer.co.kr/channel/shop/product/edit/{id}`| `channel.product.edit` / `.update` | `Front\ChannelProductController@editShopProduct` | 채널 판매 상품 상세 가격 조건, 마진액 설정 수정 |
| **POST** | `http://front.replyer.co.kr/channel/shop/product/own/store` | `channel.product.own.store` | `Front\ChannelProductController@storeOwnProduct` | 셀러가 자체 보유한 지사 상품(Own)을 판매 채널에 등록 |
| **POST** | `http://front.replyer.co.kr/channel/shop/product/public/store`| `channel.product.public.store`| `Front\ChannelProductController@storePublicProduct` | 공용 베이스 상품(Public)을 가져와 판매 채널에 공유 매핑 |
| **POST** | `http://front.replyer.co.kr/channel/shop/product/partial/store`| `channel.product.partial.store`| `Front\ChannelProductController@storePartialProduct` | 일부 권한 상품(Partial)을 가져와 판매 채널에 공유 매핑 |
| **POST** | `http://front.replyer.co.kr/channel/shop/product/partial/request`| `channel.product.partial.request`| `Front\ChannelProductController@requestPartialProduct`| 부분 권한 상품에 대해 입점사 판매 허가 신청 제출 |
| **POST** | `http://front.replyer.co.kr/channel/shop/product/status/update`| `channel.product.status.update`| `Front\ChannelProductController@updateProductStatus`| 개별 채널 상품의 상태 변경(판매중/판매중지) |
| **POST** | `http://front.replyer.co.kr/channel/shop/product/request/update`| `channel.product.request.update`| `Front\ChannelProductController@updateRequestStatus`| 판매 요청 승인 상태 처리 |
| **GET** | `http://front.replyer.co.kr/channel/product/own` | `channel.product_own` | `Front\ChannelController@productOwn` | 판매자 본인이 직접 제조/등록한 기초 베이스 상품 리스트 |
| **GET** | `http://front.replyer.co.kr/channel/product/public` | `channel.product_public` | `Front\ChannelController@productPublic` | 본사가 공개하여 아무나 판매할 수 있는 전체 기초 베이스 상품 리스트 |
| **GET** | `http://front.replyer.co.kr/channel/product/partial` | `channel.product_partial` | `Front\ChannelController@productPartial` | 승인을 받아야만 판매할 수 있는 일부 공개용 기초 베이스 상품 리스트 |
| **GET** | `http://front.replyer.co.kr/channel/product/request` | `channel.product_request` | `Front\ChannelController@productRequest` | 승인 요청 진행 중인 베이스 상품의 상태 조회 목록 |
| **GET** | `http://front.replyer.co.kr/channel/product/base/detail/{id}`| `channel.product.base.detail` | `Front\ChannelProductController@getBaseProductDetail` | 등록 전 기초 베이스 상품 스펙 상세 정보 확인 |
| **GET\|POST** | `http://front.replyer.co.kr/channel/product/base/edit/{id}` | `channel.product.base.edit` / `.update` | `Front\ChannelProductController@editBaseProduct` | 기초 베이스 상품 사양(이름, 단가 등) 수정 |
| **POST** | `http://front.replyer.co.kr/channel/product/base/delete/{id}`| `channel.product.base.delete` | `Front\ChannelProductController@deleteBaseProduct` | 본인 소유의 베이스 상품 완전 삭제 |
| **POST** | `http://front.replyer.co.kr/channel/product/base/copy/{id}` | `channel.product.base.copy` | `Front\ChannelProductController@copyBaseProduct` | 기존 베이스 상품 구성을 그대로 복사하여 복사본 신규 생성 |
| **GET** | `http://front.replyer.co.kr/channel/shop/community` | `channel.shop_community` | `Front\ChannelController@shopCommunity` | 각 상점 채널별 커뮤니티(소식지/공지사항) 피드 목록 |
| **GET\|POST** | `http://front.replyer.co.kr/channel/shop/community/register` | `channel.community.register` / `.submit`| `Front\ChannelController@communityRegister` | 공지사항 소식 글 작성 및 저장 |
| **GET** | `http://front.replyer.co.kr/channel/shop/community/view/{id}` | `channel.community.view` | `Front\ChannelController@communityView` | 공지사항 게시글 상세 보기 및 조회수 증가 |
| **GET\|POST** | `http://front.replyer.co.kr/channel/shop/community/update/{id}`| `channel.community.update` / `.submit`| `Front\ChannelController@communityUpdate` | 작성한 공지사항 수정 |
| **POST** | `http://front.replyer.co.kr/channel/shop/community/delete/{id}`| `channel.community.delete` | `Front\ChannelController@communityDelete` | 작성한 공지사항 삭제 |
| **GET** | `http://front.replyer.co.kr/channel/order/list` | `channel.order.list` | `Front\ChannelController@orderList` | 해당 판매자 상품이 포함된 전체 신규 주문 내역 조회 |
| **GET** | `http://front.replyer.co.kr/channel/order/cancel/list` | `channel.order.cancel_list` | `Front\ChannelController@orderCancelList` | 판매 취소 처리된 주문 목록 조회 |
| **GET** | `http://front.replyer.co.kr/channel/order/return/list` | `channel.order.return_list` | `Front\ChannelController@orderReturnRequestList` | 반품 요청(반품 중/완료) 목록 조회 |
| **GET** | `http://front.replyer.co.kr/channel/order/exchange/list` | `channel.order.exchange_list` | `Front\ChannelController@orderExchangeRequestList`| 교환 요청 목록 조회 |
| **POST** | `http://front.replyer.co.kr/channel/order/status/update` | `channel.order.status.update` | `Front\ChannelOrderController@updateStatus` | 주문 상품에 택배 배송사 및 송장(Tracking) 정보 매핑 및 배송중 처리 |
| **POST** | `http://front.replyer.co.kr/channel/order/cancel/request` | `channel.order.cancel.request` | `Front\ChannelOrderController@requestCancel` | 판매자 사유 취소 처리 및 취소 `OrderClaim` 강제 등록 |
| **POST** | `http://front.replyer.co.kr/channel/order/return/request` | `channel.order.return.request` | `Front\ChannelOrderController@requestReturn` | 반품 건에 대한 수령 및 동의 `OrderClaim` 처리 |
| **POST** | `http://front.replyer.co.kr/channel/order/exchange/request` | `channel.order.exchange.request` | `Front\ChannelOrderController@requestExchange` | 교환 대상 접수 및 교환 `OrderClaim` 처리 |
| **GET** | `http://front.replyer.co.kr/channel/settlement/list` | `channel.settlement.list` | `Front\ChannelSettlementController@index` | 판매자별 월별 구매확정 기준 정산 리스트 및 마진 분배 요약 |
| **GET** | `http://front.replyer.co.kr/channel/settlement/view/{period}`| `channel.settlement.view` | `Front\ChannelSettlementController@view` | 해당 정산 월(`YYYY-MM`)의 상세 주문 건별 수수료 차감 내역 정보 |
| **GET** | `http://front.replyer.co.kr/channel/settings/delivery` | `channel.delivery.list` | `Front\ChannelController@deliveryChargeList` | 기본 배송 정책 및 도서산간 배송비 설정 |
| **GET** | `http://front.replyer.co.kr/channel/settings/refund` | `channel.refund.list` | `Front\ChannelController@cancelRefundList` | 취소 및 환불 안내 가이드 목록 관리 |
| **POST** | `http://front.replyer.co.kr/channel/settings/refund/store` | `channel.refund.store` | `Front\ChannelController@storeCancelRefundPolicy` | 신규 취소/환불 기준 작성 등록 |
| **GET** | `http://front.replyer.co.kr/channel/settings/refund/{id}` | `channel.refund.get` | `Front\ChannelController@getCancelRefundPolicy`| 특정 취소/환불 기준 정보 상세 조회 |
| **POST** | `http://front.replyer.co.kr/channel/settings/refund/{id}/update`| `channel.refund.update` | `Front\ChannelController@updateCancelRefundPolicy`| 취소/환불 기준 정책 정보 수정 |
| **POST** | `http://front.replyer.co.kr/channel/settings/refund/{id}/delete`| `channel.refund.delete` | `Front\ChannelController@deleteCancelRefundPolicy`| 지정한 취소/환불 정책 삭제 |
| **POST** | `http://front.replyer.co.kr/channel/settings/refund/{id}/copy` | `channel.refund.copy` | `Front\ChannelController@copyCancelRefundPolicy` | 기존 취소/환불 안내 정책 복제 추가 |
| **GET** | `http://front.replyer.co.kr/channel/settings/info` | `channel.info.management` | `Front\ChannelController@infoManagement` | 판매자 개인 정보 수정 화면 |
| **POST** | `http://front.replyer.co.kr/channel/settings/info/update` | `channel.info.update` | `Front\ChannelController@updateInfo` | 비밀번호 및 연락처 정보 수정 처리 |
| **POST** | `http://front.replyer.co.kr/channel/settings/update-password`| `channel.update_password` | `Front\ChannelController@updatePassword` | 비밀번호 변경 AJAX 처리 |
| **GET** | `http://front.replyer.co.kr/channel/settings/sub-accounts` | `channel.sub_accounts.list` | `Front\ChannelController@subList` | 부관리자(Sub-Account) 계정 목록 관리 |

---

## 3. 🛍️ 일반 쇼핑몰 및 마이페이지 (도메인: http://front.replyer.co.kr)

일반 구매 고객들이 가입, 로그인, 상품 쇼핑을 하고 포인트/장바구니/주소록을 관리하는 영역입니다. (인증 Guard: `web`)

| HTTP Method | 기능 및 상세 URL | 라우트 명 | 매핑 컨트롤러 & 액션 | 기능 설명 |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `http://front.replyer.co.kr/` | `home` | - | 메인 쇼핑몰 홈 화면 |
| **GET** | `http://front.replyer.co.kr/register/member` | `front.member.register.member` | `Front\UserController@register` | 다단계 회원가입 기본 진입점 |
| **POST** | `http://front.replyer.co.kr/register/step1/update` | `front.member.register.step1.update`| `Front\UserController@updateMemberStep1` | 회원가입 1단계: 아이디/휴대폰 점검 및 정보 등록 |
| **POST** | `http://front.replyer.co.kr/register/step2/update` | `front.member.register.step2.update`| `Front\UserController@updateMemberStep2` | 회원가입 2단계: 회원 주소지 정보 등록 |
| **POST** | `http://front.replyer.co.kr/register/step3/update` | `front.member.register.step3.update`| `Front\UserController@updateMemberStep3` | 회원가입 3단계: 가입 완료 및 동의 정보 기록 |
| **GET\|POST** | `http://front.replyer.co.kr/find/id` | `front.member.find_id` | `Front\UserController@findId` | 휴대폰 번호 기반 아이디 찾기 |
| **GET\|POST** | `http://front.replyer.co.kr/find/pw` | `front.member.find_pw` | `Front\UserController@findPw` | 이메일 기반 임시 비밀번호 발송 |
| **GET** | `http://front.replyer.co.kr/mypage/main` | `mypage.dashboard` | `Front\UserController@dashboard` | 마이페이지 대시보드(최근 주문 요약, 포인트 현황 노출) |
| **GET** | `http://front.replyer.co.kr/mypage/profile` | `mypage.profile` | `Front\UserController@profileEdit` | 본인 개인 프로필 편집 |
| **POST** | `http://front.replyer.co.kr/mypage/profile` | `mypage.profile.update` | `Front\UserController@profileUpdate` | 이메일, 주소, 이름 정보 최종 업데이트 |
| **GET** | `http://front.replyer.co.kr/mypage/delivery` | `mypage.delivery` | `Front\UserController@delivery` | 등록된 배송 주소록 전체 관리 목록 |
| **POST** | `http://front.replyer.co.kr/mypage/delivery/add` | `mypage.delivery.add` | `Front\UserController@addDeliveryAddress` | 신규 배송지 등록 |
| **POST** | `http://front.replyer.co.kr/mypage/delivery/update` | `mypage.delivery.update` | `Front\UserController@updateDeliveryAddress`| 저장된 배송지 정보(수령인, 주소 등) 수정 |
| **POST** | `http://front.replyer.co.kr/mypage/delivery/update/default`| `mypage.delivery.update_default`| `Front\UserController@setDefaultDeliveryAddress`| 기본 배송지로 선택 지정 |
| **POST** | `http://front.replyer.co.kr/mypage/delivery/delete` | `mypage.delivery.delete` | `Front\UserController@deleteDeliveryAddress`| 주소록에서 특정 배송지 삭제 |
| **GET** | `http://front.replyer.co.kr/mypage/withdraw` | `mypage.withdraw` | `Front\UserController@withdraw` | 회원 자진 탈퇴 동의 페이지 |
| **POST** | `http://front.replyer.co.kr/mypage/withdraw/submit` | `mypage.withdraw.submit` | `Front\UserController@withdrawSubmit` | 탈퇴 정보 최종 제출 및 회원 상태 삭제(Withdrawal) 처리 |
| **GET** | `http://front.replyer.co.kr/mypage/points/status` | `mypage.point.status` | `Front\UserController@pointStatus` | 현재 총 적립 포인트 현황 조회 |
| **GET** | `http://front.replyer.co.kr/mypage/points/history` | `mypage.point.history` | `Front\UserController@pointHistory` | 포인트 상세 획득/사용 타임라인 조회 |
| **GET** | `http://front.replyer.co.kr/mypage/cart` | `mypage.cart` | `Front\UserController@cartList` | 본인 장바구니 리스트 확인 |
| **GET** | `http://front.replyer.co.kr/mypage/wishlist` | `mypage.wishlist` | `Front\UserController@wishlist` | 찜한 상품 위시리스트 조회 |
| **GET** | `http://front.replyer.co.kr/mypage/coupon` | `mypage.coupon` | `Front\UserController@couponList` | 보유 중인 혜택 쿠폰함 조회 |
