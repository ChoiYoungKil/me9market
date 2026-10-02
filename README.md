# Me9 Market

Laravel 기반 Me9 쇼핑몰, 채널관리자, 총괄관리자, 발주사 포털입니다.

- 기획 기준: `M9-SB-Ver3.0.0.pptx` 및 추가 가격/정산 정책 메일. 문서 간 차이는 `docs/Me9_3종문서_메일정책_비교_20260817.md` 참조.
- 최신 구현 점검 및 수정: [2026-10-02 갱신 검수 내역](docs/implementation-audit-20261001.md). 최신 사용자 요청에 따라 현재 수정분을 우선 배포합니다. 배송비 세부 사양, 승인 자료 및 운영 HTTPS 조건은 별도 미완료 사항이며 이번 배포가 전체 기능 인수 완료를 의미하지 않습니다.
- 항목별 대조: [271개 슬라이드](docs/requirements-audit-20261002.csv), [요청서 45건](docs/requested-pages-audit-20261002.csv), [기타 문서 원문 인덱스](docs/supporting-documents-index-20261002.csv). 부분검증 항목은 전체 합격을 의미하지 않습니다.
- 운영 설정과 배포: [운영 배포 체크리스트](PRODUCTION_DEPLOYMENT_CHECKLIST.md).
- 최신 운영 반영 결과: [2026-10-03 배포 기록](DEPLOYMENT_REPORT_20261003.md). 두 서버 Git 반영, 마이그레이션/캐시 명령 및 실제 계정 4종의 Chrome 검수 완료. HTTPS와 전체 기능 인수는 별도 미완료 항목입니다.
- 검증: `php artisan test`. `phpunit.xml`은 테스트 전용 DB `newme9markte_testing`을 사용합니다.
- 운영 설정 사전 검사: `php artisan deployment:check`. 실제 PG 미구현 상태에서는 전체 오픈 검사가 실패합니다. 승인된 결제 중지 배포만 `--restricted`로 검사하며, 서버 실검수와 외부 연동 확인은 별도입니다.
- 원장/정산 일관성 읽기 전용 검사: `php artisan commerce:audit --json`. 근거 없는 과거 스냅샷을 자동 생성하지 않으며, 검토 필요 사항도 종료 코드 1로 표시합니다.
- 로컬 실행: `php artisan serve --host=127.0.0.1 --port=8001`.
- 주요 진입점: `/shop-channel/gate`, `/channel/login`, `/admin/login`, `/shop-monitor/login`.
- 최신 검증: 자동 테스트 231개/2,829개 검증 항목, 부운영자 32개 권한 조합, Shop 54개 로컬 화면 조합 및 운영 42개 경로 방문. 이는 전체 사양 인수 및 운영 무장애 보증을 의미하지 않습니다.
- 결제: 실제 PG 미선정. `SHOP_PAYMENT_DRIVER=disabled`로 주문 결제를 차단합니다. 모의 결제는 `local`/`testing`에서만 허용합니다. PG 구분 스냅샷은 정산 정책을 보존하며 실제 승인 연동을 대신하지 않습니다.

## 원본 프로젝트 문서

Shop 신규 디자인과 검증 범위는 [점검 내역](docs/implementation-audit-20261001.md)을 참고하세요. 운영 회원가입은 `SHOP_TERMS_URL`, `SHOP_PRIVACY_URL`, `SHOP_THIRD_PARTY_URL`에 확정 HTTPS 문서를 설정하고 `SHOP_TERMS_VERSION`을 지정해야 합니다. 미설정 시 가입 제출은 차단됩니다.

테스트베드 경로는 `/admin/storyboard-test`입니다. `STORYBOARD_TEST_ENABLED=true`일 때만 등록되며 전체관리자 로그인 권한이 필요합니다. 기본값은 비활성입니다.

홈페이지 사업자 표시는 `STOREFRONT_COMPANY_NAME`, `STOREFRONT_REPRESENTATIVE`, `STOREFRONT_BUSINESS_NUMBER`, `STOREFRONT_COMMERCE_NUMBER`, `STOREFRONT_ADDRESS`, `STOREFRONT_EMAIL`에 승인된 실정보를 설정합니다. 샘플 사업자 정보를 운영 정보로 표시하지 않습니다.

아래 내용은 기반 오픈소스의 기존 기능 설명입니다. PayPal/Iyzico/Shiprocket 설명은 현재 Me9 운영 연동 완료를 의미하지 않습니다.

### Laravel Multi-vendor E-commerce Application (Mega Project)
Multi-vendor E-commerce is a large-scale project/application built with Laravel framework. The application contains comprehensive and feature-rich modules and functionalities. It is designed to provide a robust platform for businesses to create their online marketplaces, allowing multiple vendors to sell their products and manage their stores within a single platform. Additionally, the application has its own dedicated extensive API, which requires authentication using Laravel Passport package.

Frontend technologies used: jQuery, AJAX, and many JavaScript & jQuery libraries and plugins.

## Features:
1- Third-pary API Integration (Shiprocket API integration (for shipping and order management services)).

2- PayPal Payment Gateway Integration.

3- Iyzico Payment Gateway Integration.

4- A dedicated extensive API with multiple different endpoints for the application.

5- API authentication using Laravel Passport package.

6- Webhook implemented for inventory/stock update.

7 - Using PHP cURL.

8- Multiple Authentication using Laravel Guards.

9- Multi-level Relationships/Categories.

10- Product Dynamic Filters (using AJAX).

11- Shipping Charges Module (third-party service API integration, product-weight and country-wise shipping charges, etc).

12- Showing Order Shipping Status.

13- Vendor Commissions Module.

14- Coupon Codes Module (single time/multiple times, percentage/fixed).

15- Star Rating and Reviews System.

16- Recently Viewed Products Feature.

17- Order Logs/History.

18- New Arrivals, Discounted Products, Featured Products, Similar Products, and Best-Seller Products Features.

19- Using external libraries and packages such as 'Intervention Image' for image manipulation, 'Dompdf' library for printing PDF order invoices, 'Laravel Excel' package for importing/exporting database tables as Excel files, 'Laravel Barcode/QR Code Generator' to generate barcodes and QR codes for both Product ID and Product Code in order invoices, etc.

20- Using JavaScript libraries and jQuery plugins such as 'DataTables' for adding interaction controls to HTML tables, 'EasyZoom' for zooming product images, etc.

21 - Sending Confirmation Emails (Mailtrap) upon registration, account activation and approval, order shipping status, etc.

22- Sending offline SMSs (upon registration, starting order shipping process, ...).

23- Multiple Delivery Addresses.

24- Website Search Form functionality for products by name, color, and code.

25- User Roles and Permissions (superadmin, admins, vendors, users).

26- User and vendor registration approval by the superadmin.

27- Image & Video Upload Functionality.

28- Dynamically creating and editing Sections and Categories.

29- Dynamic Banner Sliders Module.

30- Dynamic Breadcrumb Navigation.

31- Dynamic SEO/HTML Meta tags.

32- Newsletter Subscription (email).

33- Regular Expression.

34- Database Seeders.

35- Tens of jQuery AJAX requests (update admin password via AJAX, AJAX form validation, ...).

36- Custom AJAX pop-up Mini-Cart.

37- Showing a Preloading Screen upon form submission.

38- TinyMCE WYSIWYG Editor Integrated.

39- Using two Favicons for both the Frontend and Admin Panel Sections of the application.

## Screenshots:
### Frontend Section Homepage:
![frontend-homepage](https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application/assets/118033266/37646610-8c9f-4ac6-8a75-75e83cc469c7)

### Product Listing Page:
![frontend-product-listing-page](https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application/assets/118033266/6a68ba25-ebd0-4b93-b687-487e35bf4912)

### Shopping Cart Page:
![shopping-cart-page](https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application/assets/118033266/64f9cbbf-87d2-4f26-aaf1-5c942d1db85b)

### Checkout Page:
![checkout-page](https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application/assets/118033266/0e4057a8-dd7e-4db5-944d-8d8754b86c32)

### Admin Panel HomePage:
![admin-panel-homepage](https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application/assets/118033266/afda126b-2ab2-4ce8-9f42-2bd6eee36bfa)

### Admin Panel Products Management Page:
![admin-panel-products-management](https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application/assets/118033266/06d8fd5b-6538-4574-b6f4-c3bf4a6a5c32)

## Application URLs:
1- **Frontend**: The public-facing website can be accessed at http://127.0.0.1:8000/. This is where users/customers/members can view categories and products and interact with the website in general. The frontend URL is typically accessible to all visitors of the website.

2- **Admin Panel**: The Admin Panel for managing the application is available at http://127.0.0.1:8000/admin/login. This secure area is exclusively accessible to authorized administrators where only authenticated superadmin, admins, and vendors can access. It grants access to the administrative functionalities of the application, such as adding new products and their features, orders management, users management, creating and editing website sections and categories, orders shipping management, etc.

## Application Routes and API Endpoints:
All application routes & API endpoints are defined in both the **[web.php](routes/web.php)** file (Frontend and Admin Panel routes) and **[api.php](routes/api.php)** file (API Endpoints).

## API Endpoints:
> ***\*\* Check the application API Collection on my Postman Profile: https://www.postman.com/ahmed-yahya/workspace/my-public-portfolio-postman-workspace/collection/28181483-179adc20-2dcc-426c-a755-5a48da9ca7a4***

> ***\*\* Also, you can test the API Endpoints yourself using Postman. Here is the API's Postman Collection .json file [API Postman Collection file](<Postman Collection of API Endpoints/Multi-vendor E-commerce Application API.postman_collection.json>) that you can download and import into your Postman.***

## Installation & Configuration:

1- Open your terminal, and use the '***git clone https://github.com/AhmedYahyaE/laravel-multi-vendor-e-commerce-application.git***' command, or just download the ZIP project.

2- Navigate/Change into (using the **cd** command) to the project root directory, then run the '***composer install***' command.

3- Run the '***npm install***' command (and only in case you face any issues/errors, run the 'npm audit fix' command), and then run the '***npm run build***' command.

4- Create a MySQL database named **\`multivendor_ecommerce\`**, then import the **[multivendor_ecommerce database SQL Dump File](<Database - multivendor_ecommerce/multivendor_ecommerce database - SQL Dump File - phpMyAdmin Export.sql>)** into your **\`multivendor_ecommerce\`** database.

5- Navigate to the **[.env](.env)** file and configure/update it with your MySQL database credentials and other configuration settings.

6- Run the '***php artisan serve***' command, and then open your browser and visit **http://127.0.0.1:8000** to access the Frontend section of the application, or **http://127.0.0.1:8000/admin/login** to access the Admin Panel.

\*\* Ready-to-use registered accounts credentials you can use to log in:
> 1) Superadmin (to access the Admin Panel): Email: **admin@admin.com**, Password: **123456**

> 2) Vendor (to access the Admin Panel): Email: **yasser@admin.com**, Password: **123456**
    
> 3) User (to access the Frontend): Email: **ibrahim@gmail.com**, Password: **123456**

## Contribution:
Contributions to my Multi-vendor E-commerce Laravel application are most welcome! If you find any issues or have suggestions for improvements or want to add new features, please open an issue or submit a pull request.
