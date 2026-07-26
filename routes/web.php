<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

// 수정: 기본 /login 경로를 새로운 /member/login 페이지로 리다이렉트
Route::redirect('/login', '/member/login');



// 참고: 웹사이트는 두 가지 주요 섹션으로 구분됩니다: 관리자(Admin) 경로 및 사용자(Frontend) 경로!:

Route::get('/test-route', function() { return 'Test Route OK'; });

Route::get('/session-test', function() {
    $sessionWritable = is_writable(storage_path('framework/sessions'));
    $storageWritable = is_writable(storage_path());
    
    $sessionVal = session('test_key');
    session(['test_key' => 'Hello-' . time()]);
    
    $info = [
        'Host (Request)' => request()->getHost(),
        'URL (Current)' => request()->fullUrl(),
        'Session Writable' => $sessionWritable ? 'Yes' : 'No',
        'Storage Writable' => $storageWritable ? 'Yes' : 'No',
        'Session Value (from last request)' => $sessionVal ?? 'NONE (First visit or session lost)',
        'Session Driver' => config('session.driver'),
        'Session Domain' => config('session.domain') ?? 'NULL',
        'Session Secure' => config('session.secure') ? 'Yes' : 'No',
        'Cookies Received' => request()->cookies->all(),
    ];
    
    return response()->json($info);
});


// 첫째: 관리자 패널 라우트:
// 웹사이트 'ADMIN' 섹션: 'admin'으로 시작하는 라우트 그룹 (Admin 라우트 그룹)    // 참고: 모든 라우트는 'admin/'으로 시작하므로 접두어 내부에서는 '/admin'을 생략하고 정의합니다!!
Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')->group(function () {
    Route::match(['get', 'post'], 'login', 'AdminController@login')->name('admin.login'); // match() 메소드는 동일한 라우트에 대해 하나 이상의 HTTP 요청 메소드를 허용합니다 (예: 페이지 렌더링은 GET, 폼 제출은 POST)


    // 'admin/-'으로 시작하며 'admin' 인증 가드를 사용하는 모든 라우트 그룹    // 참고: 이 그룹 내부의 라우트에서는 '/admin' 부분을 제거해야 합니다 (예: Route::get('admin/logout'); 대신 Route::get('logout'); 사용)
    Route::group(['middleware' => ['admin']], function () { // 'admin' 가드 사용 (auth.php에서 생성됨)
        Route::get('sub01', 'AdminController@sub01')->name('admin.sub01');
        Route::get('sub02', 'AdminController@sub02')->name('admin.sub02');
        Route::get('sub03', 'AdminController@sub03')->name('admin.sub03');
        Route::get('view', 'AdminController@view')->name('admin.view');
        Route::get('newpage', 'AdminController@newpage')->name('admin.newpage');
        Route::get('loading', 'AdminController@loading')->name('admin.loading');
        Route::get('sub/layer-large', 'AdminController@layerLarge')->name('admin.layer_large');
        Route::get('dashboard', 'AdminController@dashboard')->name('admin.dashboard'); // 관리자 로그인
        Route::get('logout', 'AdminController@logout'); // 관리자 로그아웃
        Route::match(['get', 'post'], 'update-admin-password', 'AdminController@updateAdminPassword'); // 비밀번호 변경 폼 보기(GET) 및 제출(POST)
        Route::post('check-admin-password', 'AdminController@checkAdminPassword'); // 관리자 비밀번호 확인 // admin/js/custom.js의 AJAX 호출에서 사용됨
        Route::match(['get', 'post'], 'update-admin-details', 'AdminController@updateAdminDetails'); // update_admin_details.blade.php 페이지에서 관리자 정보 수정    // 페이지 렌더링(GET) 및 폼 제출(POST)
        Route::match(['get', 'post'], 'update-vendor-details/{slug}', 'AdminController@updateVendorDetails'); // 입점업체 정보 수정    // slug에 'personal', 'business', 'bank' 전달 가능 (개인, 사업자, 은행 정보 수정)    // 하나의 뷰에서 $slug 값에 따라 내용 변경    // 페이지 렌더링(GET) 및 폼 제출(POST)

        // 입점업체 수수료율 업데이트 (관리자 전용)
        // 입점업체 수수료율 업데이트 (관리자 전용)
        Route::post('update-vendor-commission', 'AdminController@updateVendorCommission');

        Route::get('admins/{type?}', 'AdminController@admins')->name('admin.admins'); // 인증된 사용자 등급(superadmin, admin, subadmin, vendor)에 따른 관리자 목록 표시. Optional Route Parameter '?' 사용 (전달되지 않으면 모든 목록 표시)
        Route::match(['get', 'post'], 'add-edit-admin/{id?}', 'AdminController@addEditAdmin'); // 관리자/판매자 추가 및 수정
        Route::get('delete-admin/{id}', 'AdminController@deleteAdmin'); // 관리자/판매자 삭제
        
        Route::get('view-vendor-details/{id}', 'AdminController@viewVendorDetails'); // 관리자 관리 테이블에서 입점업체 상세 정보 보기 (superadmin, admin, subadmin인 경우)
        Route::post('update-admin-status', 'AdminController@updateAdminStatus'); // AJAX를 사용한 관리자 상태 업데이트 (admins.blade.php)
        Route::post('update-vendor-certification', 'AdminController@updateVendorCertification'); // 판매자 인증 상태 업데이트 (view_vendor_details.blade.php)


        // 섹션 (Sections, Categories, Subcategories, Products, Attributes)
        Route::get('sections', 'SectionController@sections')->name('admin.sections');
        Route::post('update-section-status', 'SectionController@updateSectionStatus'); // AJAX를 사용한 섹션 상태 업데이트 (sections.blade.php)
        Route::get('delete-section/{id}', 'SectionController@deleteSection'); // 섹션 삭제 (sections.blade.php)
        Route::match(['get', 'post'], 'add-edit-section/{id?}', 'SectionController@addEditSection'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.

        // 카테고리 (Categories)
        Route::get('categories', 'CategoryController@categories')->name('admin.categories'); // 카테고리 관리
        Route::post('update-category-status', 'CategoryController@updateCategoryStatus'); // AJAX를 사용한 카테고리 상태 업데이트 (categories.blade.php)
        Route::match(['get', 'post'], 'add-edit-category/{id?}', 'CategoryController@addEditCategory'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.
        Route::get('append-categories-level', 'CategoryController@appendCategoryLevel'); // 선택된 섹션에 따른 카테고리 레벨 표시 (AJAX, append_categories_level.blade.php)
        Route::get('delete-category/{id}', 'CategoryController@deleteCategory'); // 카테고리 삭제 (categories.blade.php)
        Route::get('delete-category-image/{id}', 'CategoryController@deleteCategoryImage'); // 카테고리 이미지 삭제 (서버 및 데이터베이스 모두)

        // 브랜드 (Brands)
        Route::get('brands', 'BrandController@brands')->name('admin.brands');
        Route::post('update-brand-status', 'BrandController@updateBrandStatus'); // AJAX를 사용한 브랜드 상태 업데이트 (brands.blade.php)
        Route::get('delete-brand/{id}', 'BrandController@deleteBrand'); // 브랜드 삭제 (brands.blade.php)
        Route::match(['get', 'post'], 'add-edit-brand/{id?}', 'BrandController@addEditBrand'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.

        // 상품 (Products)
        Route::get('products', 'ProductsController@products')->name('admin.products'); // 상품 목록 렌더링
        Route::post('update-product-status', 'ProductsController@updateProductStatus'); // AJAX를 사용한 상품 상태 업데이트 (products.blade.php)
        Route::get('delete-product/{id}', 'ProductsController@deleteProduct'); // 상품 삭제 (products.blade.php)
        Route::match(['get', 'post'], 'add-edit-product/{id?}', 'ProductsController@addEditProduct'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.    // GET 요청은 뷰 렌더링, POST 요청은 폼 제출
        Route::get('delete-product-image/{id}', 'ProductsController@deleteProductImage'); // 상품 이미지 삭제 (서버 및 데이터베이스)
        Route::get('delete-product-video/{id}', 'ProductsController@deleteProductVideo'); // 상품 동영상 삭제 (서버 및 데이터베이스)

        // 속성 (Attributes)
        Route::match(['get', 'post'], 'add-edit-attributes/{id}', 'ProductsController@addAttributes'); // GET 요청 뷰 렌더링, POST 요청 폼 제출
        Route::post('update-attribute-status', 'ProductsController@updateAttributeStatus'); // AJAX를 사용한 속성 상태 업데이트 (add_edit_attributes.blade.php)
        Route::get('delete-attribute/{id}', 'ProductsController@deleteAttribute'); // 속성 삭제 (add_edit_attributes.blade.php)
        Route::match(['get', 'post'], 'edit-attributes/{id}', 'ProductsController@editAttributes'); // 속성 수정

        // 이미지 (Images)
        Route::match(['get', 'post'], 'add-images/{id}', 'ProductsController@addImages'); // GET 요청 뷰 렌더링, POST 요청 폼 제출
        Route::post('update-image-status', 'ProductsController@updateImageStatus'); // AJAX를 사용한 이미지 상태 업데이트 (add_images.blade.php)
        Route::get('delete-image/{id}', 'ProductsController@deleteImage'); // 이미지 삭제 (add_images.blade.php)

        // 배너 (Banners)
        Route::get('banners', 'BannersController@banners')->name('admin.banners');
        Route::post('update-banner-status', 'BannersController@updateBannerStatus'); // AJAX를 사용한 배너 상태 업데이트 (banners.blade.php)
        Route::get('delete-banner/{id}', 'BannersController@deleteBanner'); // 배너 삭제 (banners.blade.php)
        Route::match(['get', 'post'], 'add-edit-banner/{id?}', 'BannersController@addEditBanner'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.    // GET 요청은 뷰 렌더링, POST 요청은 폼 제출

        // 고객센터 - 공지사항 (Notices)
        Route::get('notices', 'SupportController@notices')->name('admin.notices');
        Route::match(['get', 'post'], 'add-edit-notice/{id?}', 'SupportController@addEditNotice');
        Route::get('delete-notice/{id}', 'SupportController@deleteNotice');
        Route::get('delete-notice-attachment/{id}', 'SupportController@deleteNoticeAttachment');
        Route::post('update-notice-status', 'SupportController@updateNoticeStatus');

        // 고객센터 - 자주묻는질문 (FAQs)
        Route::get('faqs', 'SupportController@faqs')->name('admin.faqs');
        Route::match(['get', 'post'], 'add-edit-faq/{id?}', 'SupportController@addEditFaq');
        Route::get('delete-faq/{id}', 'SupportController@deleteFaq');
        Route::post('update-faq-status', 'SupportController@updateFaqStatus');

        // 고객센터 - 제휴/문의 (Contacts)
        Route::get('contacts', 'SupportController@contacts')->name('admin.contacts');
        Route::get('view-contact/{id}', 'SupportController@viewContact');
        Route::post('update-contact/{id}', 'SupportController@updateContact');
        Route::get('delete-contact/{id}', 'SupportController@deleteContact');

        // 필터 (Filters)
        Route::get('filters', 'FilterController@filters'); // filters.blade.php 렌더링
        Route::post('update-filter-status', 'FilterController@updateFilterStatus'); // AJAX를 사용한 필터 상태 업데이트 (filters.blade.php)
        Route::post('update-filter-value-status', 'FilterController@updateFilterValueStatus'); // AJAX를 사용한 필터 값 상태 업데이트 (filters_values.blade.php)
        Route::get('filters-values', 'FilterController@filtersValues'); // filters_values.blade.php 렌더링
        Route::match(['get', 'post'], 'add-edit-filter/{id?}', 'FilterController@addEditFilter'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.    // GET 요청은 뷰 렌더링, POST 요청은 폼 제출
        Route::match(['get', 'post'], 'add-edit-filter-value/{id?}', 'FilterController@addEditFilterValue'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.    // GET 요청은 뷰 렌더링, POST 요청은 폼 제출
        Route::post('category-filters', 'FilterController@categoryFilters'); // 선택된 카테고리에 따라 관련 필터 표시 (AJAX, category_filters.blade.php). admin/js/custom.js 확인

        // 쿠폰 (Coupons)
        Route::get('coupons', 'CouponsController@coupons')->name('admin.coupons'); // admin/coupons/coupons.blade.php 렌더링
        Route::post('update-coupon-status', 'CouponsController@updateCouponStatus'); // AJAX를 사용한 쿠폰 상태 업데이트 (active/inactive)
        Route::get('delete-coupon/{id}', 'CouponsController@deleteCoupon'); // AJAX를 사용한 쿠폰 삭제

        // {id?} 선택적 파라미터 전달 시 쿠폰 수정, 아니면 쿠폰 추가. GET 요청은 뷰 렌더링, POST 요청은 폼 제출
        Route::match(['get', 'post'], 'add-edit-coupon/{id?}', 'CouponsController@addEditCoupon'); // {id?}는 선택적 파라미터로, 전달되면 수정, 없으면 추가를 의미합니다.    // GET 요청은 뷰 렌더링, POST 요청은 폼 제출

        // 사용자 (Users)
        Route::get('users', 'UserController@users')->name('admin.users'); // admin/users/users.blade.php 렌더링
        Route::post('update-user-status', 'UserController@updateUserStatus'); // AJAX를 사용한 사용자 상태 업데이트
        Route::match(['get', 'post'], 'add-edit-user/{id?}', 'UserController@addEditUser'); // 사용자 추가 및 수정
        Route::get('delete-user/{id}', 'UserController@deleteUser'); // 사용자 삭제

        // 주문 (Orders)
        // admin/orders/orders.blade.php 렌더링 (주문 관리)
        Route::get('orders', 'OrderController@orders')->name('admin.orders');

        // 주문 상세 보기 (admin/orders/order_details.blade.php)
        Route::get('orders/{id}', 'OrderController@orderDetails');

        // 주문 상태 업데이트 (관리자 전용)
        Route::post('update-order-status', 'OrderController@updateOrderStatus');

        // 주문 품목 상태 업데이트 (관리자 및 판매자 모두 가능)
        Route::post('update-order-item-status', 'OrderController@updateOrderItemStatus');

        // 주문 인보이스
        // HTML 인보이스 렌더링 (order_invoice.blade.php)
        Route::get('orders/invoice/{id}', 'OrderController@viewOrderInvoice');

        // PDF 인보이스 렌더링 (Dompdf 패키지 사용)
        Route::get('orders/invoice/pdf/{id}', 'OrderController@viewPDFInvoice');

        // 정산관리
        Route::get('settlements', 'SettlementController@index')->name('admin.settlements.index');
        Route::post('settlements/generate', 'SettlementController@generate')->name('admin.settlements.generate');
        Route::get('settlements/preview', 'SettlementController@preview')->name('admin.settlements.preview');
        Route::get('settlements/{id}', 'SettlementController@show')->name('admin.settlements.show');
        Route::post('settlements/{id}/complete', 'SettlementController@complete')->name('admin.settlements.complete');

        // 판매자 포인트 구매/환급 승인
        Route::get('channel-points', 'ChannelPointController@index')->name('admin.channel_points.index');
        Route::post('channel-points/{id}/approve', 'ChannelPointController@approve')->name('admin.channel_points.approve');
        Route::post('channel-points/{id}/reject', 'ChannelPointController@reject')->name('admin.channel_points.reject');

        // 배송비 관리 모듈 (Shipping Charges module)
        // 배송비 관리 페이지 (admin/shipping/shipping_charges.blade.php) 렌더링 (관리자 전용)
        Route::get('shipping-charges', 'ShippingController@shippingCharges');

        // AJAX를 사용한 배송비 상태 업데이트 (active/inactive)
        Route::post('update-shipping-status', 'ShippingController@updateShippingStatus');

        // 배송비 수정 페이지 (edit_shipping_charges.blade.php) 렌더링 (GET) 및 폼 제출 (POST)
        Route::match(['get', 'post'], 'edit-shipping-charges/{id}', 'ShippingController@editShippingCharges');



        // 뉴스레터 구독자 모듈 (Newsletter Subscribers module)
        // 구독자 목록 페이지 렌더링
        Route::get('subscribers', 'NewsletterController@subscribers')->name('admin.subscribers');

        // AJAX를 사용한 구독자 상태 업데이트
        Route::post('update-subscriber-status', 'NewsletterController@updateSubscriberStatus');

        // AJAX를 사용한 구독자 삭제
        Route::get('delete-subscriber/{id}', 'NewsletterController@deleteSubscriber');



        // Maatwebsite/Laravel Excel 패키지를 사용하여 구독자 목록을 엑셀 파일로 내보내기
        Route::get('export-subscribers', 'NewsletterController@exportSubscribers');

        // 사용자 평가 및 리뷰 (User Ratings & Reviews)
        // 평가 관리 페이지 렌더링
        Route::get('ratings', 'RatingController@ratings');

        // AJAX를 사용한 평가 상태 업데이트
        Route::post('update-rating-status', 'RatingController@updateRatingStatus');

        // AJAX를 사용한 평가 삭제
        Route::get('delete-rating/{id}', 'RatingController@deleteRating');
    });

});






// 사용자 주문 PDF 인보이스 다운로드 (사용자가 다운로드할 수 있도록 관리자 패널 외부에 라우트 생성)
Route::get('orders/invoice/download/{id}', 'App\Http\Controllers\Admin\OrderController@viewPDFInvoice');







// 참고: 웹사이트는 두 가지 주요 섹션으로 구분됩니다: 관리자(Admin) 및 사용자(Frontend)!:

// 새로운 프로젝트 재구축 라우트 (우선순위)
Route::namespace('App\Http\Controllers\Front')->group(function () {
    // Me9market (메인 몰)
    Route::get('/', function() { return view('front.index'); })->name('home');

    // 회원 라우트
    Route::prefix('member')->name('front.member.')->group(function () {
        Route::get('/login', 'UserController@login')->name('login');
        Route::post('/login', 'UserController@loginUser')->name('login.submit');
    });

    Route::get('/register/member', 'UserController@registerMember')->name('front.member.register.member');
    Route::post('/register/member', 'UserController@registerMemberSubmit')->name('front.member.register.submit');
    Route::post('/check-id-availability', 'UserController@checkIdAvailability')->name('front.member.check_id');
    Route::get('/register/step1', 'UserController@registerStep1')->name('front.member.register.step1');
    Route::post('/register/step1/update', 'UserController@updateMemberStep1')->name('front.member.register.step1.update'); // 새로운 라우트
    Route::post('/user/update-password', 'UserController@userUpdatePassword')->name('user.update.password'); // AJAX를 위한 별칭
    Route::get('/register/step2', 'UserController@registerStep2')->name('front.member.register.step2');
    Route::post('/register/step2/update', 'UserController@updateMemberStep2')->name('front.member.register.step2.update'); // 새로운 라우트
    Route::get('/register/step3', 'UserController@registerStep3')->name('front.member.register.step3');
    Route::post('/register/step3/update', 'UserController@updateMemberStep3')->name('front.member.register.step3.update'); // 새로운 라우트
    // 아이디 찾기 (GET: 폼 표시, POST: 검색 처리)
    Route::match(['get', 'post'], '/find/id', 'UserController@findId')->name('front.member.find_id');
    // 비밀번호 찾기 (GET: 폼 표시, POST: 임시비밀번호 발급)
    Route::match(['get', 'post'], '/find/pw', 'UserController@findPw')->name('front.member.find_pw');

    // 판매자 등록
    Route::get('/seller/register', 'UserController@vendorRegisterPage')->name('front.seller.register');
    Route::post('/seller/register', 'UserController@vendorRegister')->name('front.seller.register.submit');

    // 고객 서비스 (CS)
    Route::get('/notice', 'FrontController@notice')->name('cs.notice');
    Route::get('/notice/view/{id?}', 'FrontController@noticeView')->name('cs.notice.view');
    Route::get('/faq', 'FrontController@faq')->name('cs.faq');
    Route::match(['get', 'post'], '/contact', 'CmsController@contact')->name('cs.contact');

    // 홈페이지 소개 및 비회원 주문조회 (RF-01-02 ~ 04, RF-01-06)
    Route::get('/service', 'FrontController@service')->name('front.service');
    Route::get('/features', 'FrontController@features')->name('front.features');
    Route::get('/subscription-information', 'FrontController@subscriptionInfo')->name('front.subscription_info');
    Route::get('/nonmember/order/check', 'FrontController@nonmemberOrderCheck')->name('front.nonmember.order_check');
    Route::post('/nonmember/order/check', 'FrontController@nonmemberOrderCheckSubmit')->name('front.nonmember.order_check.submit');
    Route::get('/nonmember/order/details', 'FrontController@nonmemberOrderDetails')->name('front.nonmember.order_details');
    Route::post('/nonmember/order/claim', 'FrontController@nonmemberOrderClaimSubmit')->name('front.nonmember.order_claim.submit');
    Route::post('/nonmember/order/inquiry', 'FrontController@nonmemberOrderInquirySubmit')->name('front.nonmember.order_inquiry.submit');

    // 소셜 로그인 후 동의가입 (RF-01-07-02)
    Route::get('/member/social-join', 'UserController@socialJoin')->name('front.member.social_join');
    Route::post('/member/social-join', 'UserController@socialJoinSubmit')->name('front.member.social_join.submit');

    // shop 채널 (RF-03)
    Route::prefix('shop-channel')->group(function () {
        Route::get('/gate', 'FrontController@shopGate')->name('shop.gate');
        Route::post('/gate', 'FrontController@shopGateSubmit')->name('shop.gate.submit');
        Route::get('/register', 'FrontController@shopRegister')->name('shop.register');
        Route::post('/register', 'FrontController@shopRegisterSubmit')->name('shop.register.submit');
        Route::get('/enter/{channelCode}', 'FrontController@shopEnter')->name('shop.enter');
        Route::get('/main', 'FrontController@shopMain')->name('shop.channel_main');
        Route::get('/products', 'FrontController@shopProducts')->name('shop.products_list');
        Route::get('/products/{id}', 'FrontController@shopProductDetails')->name('shop.product_details');
        Route::get('/joint-purchases', 'FrontController@shopJointPurchases')->name('shop.joint_purchases_list');
        Route::get('/joint-purchases/{id}', 'FrontController@shopJointPurchaseDetails')->name('shop.joint_purchase_details');
        Route::get('/notices', 'FrontController@shopNotices')->name('shop.notices');
    });

    // 통합 테스트베드 (Index)
    Route::get('/storyboard-test', 'FrontController@storyboardTestbed')->name('front.storyboard_testbed');

    // Shop 라우트
    Route::prefix('shop')->name('front.shop.')->group(function () {
        Route::get('/cart', 'ShopController@cart')->name('cart.index');
        Route::post('/cart/add', 'ShopController@addToCart')->name('cart.add');
        Route::post('/cart/remove', 'ShopController@removeFromCart')->name('cart.remove');
        Route::get('/order', 'ShopController@order')->name('order.form');
        Route::post('/order', 'ShopController@checkout')->name('order.checkout');
        Route::get('/order/complete', 'ShopController@orderComplete')->name('order.complete');
        
        // 상세 페이지 라우트 (추가됨)
        Route::get('/order/details', 'ShopController@orderDetails')->name('order.details');
        Route::post('/order/item/{id}/status', 'ShopController@updateOrderItem')->name('order.item.status');
        Route::get('/cancel/details', 'ShopController@cancelDetails')->name('cancel.details');
        Route::get('/exchange/details', 'ShopController@exchangeDetails')->name('exchange.details');
        Route::get('/return/details', 'ShopController@returnDetails')->name('return.details');
    });

    // 채널 포털 라우트 (Channel Portal)
    Route::prefix('channel')->group(function () {
        // Guest Channel Routes
        Route::get('/login', 'App\Http\Controllers\Front\ChannelController@login')->name('channel.login');
        Route::post('/login', 'App\Http\Controllers\Front\ChannelController@loginUser')->name('channel.login.submit');
        Route::get('/logout', 'App\Http\Controllers\Front\ChannelController@logout')->name('channel.logout');
        Route::get('/register', 'App\Http\Controllers\Front\ChannelController@register')->name('channel.register');
        Route::post('/register', 'App\Http\Controllers\Front\ChannelController@registerSubmit')->name('channel.register.submit');

        // Protected Channel Routes
        Route::group(['middleware' => ['admin']], function () {
            Route::get('/', 'App\Http\Controllers\Front\ChannelController@index')->name('channel.index');

            // 판매자 프로필 완성 (대기 중인 판매자용)
            Route::get('/complete-profile', 'App\Http\Controllers\Front\ChannelController@completeProfile')->name('channel.complete_profile');
            Route::post('/complete-profile', 'App\Http\Controllers\Front\ChannelController@completeProfileSubmit')->name('channel.complete_profile.submit');

            // 상점 관리 (Sub01)
            Route::prefix('shop')->group(function () {
                Route::get('/list', 'ChannelController@shopList')->name('channel.shop_list');
                Route::get('/register', 'ChannelController@shopRegister')->name('channel.shop_register');
                Route::post('/register', 'ChannelController@shopRegisterSubmit')->name('channel.shop_register.submit');
                Route::get('/info', 'ChannelController@shopInfo')->name('channel.shop_info');
                Route::get('/product01', 'ChannelController@shopProduct01')->name('channel.shop_product01');
                Route::get('/product02', 'ChannelController@shopProduct02')->name('channel.shop_product02');
                Route::get('/community', 'ChannelController@shopCommunity')->name('channel.shop_community');

                Route::get('/community/register', 'ChannelController@communityRegister')->name('channel.community.register');
                Route::post('/community/register', 'ChannelController@communityRegisterSubmit')->name('channel.community.register.submit');
                Route::get('/community/view/{id}', 'ChannelController@communityView')->name('channel.community.view');
                Route::get('/community/update/{id}', 'ChannelController@communityUpdate')->name('channel.community.update');
                Route::post('/community/update/{id}', 'ChannelController@communityUpdateSubmit')->name('channel.community.update.submit');
                Route::post('/community/delete/{id}', 'ChannelController@communityDelete')->name('channel.community.delete');
                Route::get('/info-update/{id}', 'ChannelController@infoUpdate')->name('channel.info_update');
                Route::post('/info-update/{id}', 'ChannelController@infoUpdateSubmit')->name('channel.info_update.submit');

                Route::post('/product/own/store', 'ChannelProductController@storeOwnProduct')->name('channel.product.own.store');
                Route::post('/product/public/store', 'ChannelProductController@storePublicProduct')->name('channel.product.public.store');
                Route::post('/product/partial/store', 'ChannelProductController@storePartialProduct')->name('channel.product.partial.store');
                Route::post('/product/partial/request', 'ChannelProductController@requestPartialProduct')->name('channel.product.partial.request');
                Route::post('/product/status/update', 'ChannelProductController@updateProductStatus')->name('channel.product.status.update');
                Route::post('/product/request/update', 'ChannelProductController@updateRequestStatus')->name('channel.product.request.update');
                Route::get('/product/edit/{id}', 'ChannelProductController@editShopProduct')->name('channel.product.edit');
                Route::post('/product/edit/{id}', 'ChannelProductController@updateShopProduct')->name('channel.product.update');
            });

            // Product Management (Sub02)
            Route::prefix('product')->group(function () {
                Route::get('/own', 'ChannelController@productOwn')->name('channel.product_own');
                Route::get('/own/export', 'ChannelProductController@exportOwnProducts')->name('channel.product.own.export');
                Route::get('/public', 'ChannelController@productPublic')->name('channel.product_public');
                Route::get('/partial', 'ChannelController@productPartial')->name('channel.product_partial');
                Route::get('/request', 'ChannelController@productRequest')->name('channel.product_request');
                Route::get('/categories', 'ChannelProductController@productCategories')->name('channel.product.categories');
                Route::post('/categories', 'ChannelProductController@storeProductCategory')->name('channel.product.categories.store');
                Route::post('/categories/{id}/update', 'ChannelProductController@updateProductCategory')->name('channel.product.categories.update');
                Route::post('/categories/{id}/delete', 'ChannelProductController@deleteProductCategory')->name('channel.product.categories.delete');

                // Base Product Management actions
                Route::get('/base/detail/{id}', 'ChannelProductController@getBaseProductDetail')->name('channel.product.base.detail');
                Route::get('/base/create', 'ChannelProductController@createBaseProduct')->name('channel.product.base.create');
                Route::post('/base/store', 'ChannelProductController@storeBaseProduct')->name('channel.product.base.store');
                Route::get('/base/edit/{id}', 'ChannelProductController@editBaseProduct')->name('channel.product.base.edit');
                Route::post('/base/update/{id}', 'ChannelProductController@updateBaseProduct')->name('channel.product.base.update');
                Route::post('/base/stop-notice/{id}', 'ChannelProductController@updateStopNotice')->name('channel.product.base.stop_notice');
                Route::post('/base/delete/{id}', 'ChannelProductController@deleteBaseProduct')->name('channel.product.base.delete');
                Route::post('/base/copy/{id}', 'ChannelProductController@copyBaseProduct')->name('channel.product.base.copy');
            });

            // Order Management (Sub04)
            Route::prefix('order')->group(function () {
                Route::get('/list', 'ChannelController@orderList')->name('channel.order.list');
                Route::get('/joint/list', 'ChannelController@orderJointPurchaseList')->name('channel.order.joint_list');
                Route::get('/cancel/list', 'ChannelController@orderCancelList')->name('channel.order.cancel_list');
                Route::get('/return/list', 'ChannelController@orderReturnRequestList')->name('channel.order.return_list');
                Route::get('/exchange/list', 'ChannelController@orderExchangeRequestList')->name('channel.order.exchange_list');

                // Order Actions (New)
                Route::post('/status/update', 'ChannelOrderController@updateStatus')->name('channel.order.status.update');
                Route::post('/cancel/request', 'ChannelOrderController@requestCancel')->name('channel.order.cancel.request');
                Route::post('/return/request', 'ChannelOrderController@requestReturn')->name('channel.order.return.request');
                Route::post('/exchange/request', 'ChannelOrderController@requestExchange')->name('channel.order.exchange.request');
            });

            // Customer Product Inquiries
            Route::prefix('inquiries')->group(function () {
                Route::get('/', 'ChannelController@inquiryList')->name('channel.inquiries.index');
                Route::get('/{id}', 'ChannelController@inquiryView')->name('channel.inquiries.show');
                Route::post('/{id}/reply', 'ChannelController@inquiryReply')->name('channel.inquiries.reply');
            });

            // Settlement Management (Sub05)
            Route::prefix('settlement')->group(function () {
                Route::get('/list', 'ChannelSettlementController@index')->name('channel.settlement.list');
                Route::get('/view/{period}', 'ChannelSettlementController@view')->name('channel.settlement.view');
            });

            // Joint Purchase Management (Sub03) (RF-02-06-XX)
            Route::prefix('joint-purchase')->group(function () {
                Route::get('/list', 'ChannelController@jointPurchaseList')->name('channel.joint_purchase.list');
                Route::get('/create', 'ChannelController@jointPurchaseCreate')->name('channel.joint_purchase.create');
                Route::post('/store', 'ChannelController@jointPurchaseStore')->name('channel.joint_purchase.store');
                Route::get('/edit/{id}', 'ChannelController@jointPurchaseEdit')->name('channel.joint_purchase.edit');
                Route::post('/update/{id}', 'ChannelController@jointPurchaseUpdate')->name('channel.joint_purchase.update');
            });

            // Settings / Additional Info (Sub00)
            Route::prefix('settings')->group(function () {
                Route::get('/delivery', 'ChannelController@deliveryChargeList')->name('channel.delivery.list');
                Route::get('/refund', 'ChannelController@cancelRefundList')->name('channel.refund.list');

                // 취소/환불 정책 CRUD
                Route::post('/refund/store', 'ChannelController@storeCancelRefundPolicy')->name('channel.refund.store');
                Route::get('/refund/{id}', 'ChannelController@getCancelRefundPolicy')->name('channel.refund.get');
                Route::post('/refund/{id}/update', 'ChannelController@updateCancelRefundPolicy')->name('channel.refund.update');
                Route::post('/refund/{id}/delete', 'ChannelController@deleteCancelRefundPolicy')->name('channel.refund.delete');
                Route::post('/refund/{id}/copy', 'ChannelController@copyCancelRefundPolicy')->name('channel.refund.copy');

                Route::get('/info', 'ChannelController@infoManagement')->name('channel.info.management');
                Route::post('/info/update', 'ChannelController@updateInfo')->name('channel.info.update');
                Route::post('/update-password', 'ChannelController@updatePassword')->name('channel.update_password');
                Route::get('/order-manager', 'ChannelController@orderManagerList')->name('channel.order.manager');
                Route::get('/points', 'ChannelController@pointList')->name('channel.point.list');
                Route::post('/points/purchase', 'ChannelController@requestPointPurchase')->name('channel.point.purchase');
                Route::post('/points/refund', 'ChannelController@requestPointRefund')->name('channel.point.refund');
                Route::get('/sub-accounts', 'ChannelController@subList')->name('channel.sub_accounts.list');
            });
        });
    });

    // Mypage Routes (Protected by auth)
    Route::group(['middleware' => ['auth'], 'prefix' => 'mypage'], function () {
        Route::get('/main', 'UserController@dashboard')->name('mypage.dashboard');
        
        // Order Management
        Route::get('/order/list', 'UserController@orderList')->name('mypage.order.list'); // Check list of orders
        Route::get('/order/view', 'UserController@orderView')->name('mypage.order.view'); // Check specific order details
        Route::post('/order/claim', 'UserController@orderClaimSubmit')->name('mypage.order.claim.submit'); // Submit cancel/return/exchange/confirm claims

        Route::get('/profile', 'UserController@profileEdit')->name('mypage.profile');
        Route::post('/profile', 'UserController@profileUpdate')->name('mypage.profile.update');
        Route::get('/order/cancel-return-list', 'UserController@cancelReturnList')->name('mypage.order.cancel_return_list');
        Route::get('/delivery', 'UserController@delivery')->name('mypage.delivery');
            Route::post('/delivery/add', 'UserController@addDelivery')->name('mypage.delivery.add');
            Route::post('/delivery/update/default', 'UserController@updateDefaultDelivery')->name('mypage.delivery.update_default');
            Route::post('/delivery/update', 'UserController@updateDelivery')->name('mypage.delivery.update');
            Route::post('/delivery/delete', 'UserController@deleteDelivery')->name('mypage.delivery.delete');
        Route::get('/withdraw', 'UserController@withdraw')->name('mypage.withdraw');
        Route::post('/withdraw/submit', 'UserController@withdrawSubmit')->name('mypage.withdraw.submit');
        Route::get('/withdraw/success', 'UserController@withdrawSuccess')->name('mypage.withdraw.success');
        Route::get('/withdraw/logout', 'UserController@withdrawLogout')->name('mypage.withdraw.logout');
        Route::get('/visited-channels', 'UserController@visitedChannels')->name('mypage.visited_channels');
        Route::post('/visited-channels/delete-all', 'UserController@deleteAllVisitedChannels')->name('mypage.visited_channels.delete_all');
        Route::post('/visited-channels/delete/{id}', 'UserController@deleteVisitedChannel')->name('mypage.visited_channels.delete');
        
        // 포인트 관리
        Route::get('/points/status', 'UserController@pointStatus')->name('mypage.point.status');
        Route::get('/points/history', 'UserController@pointHistory')->name('mypage.point.history');

        // 장바구니 목록
        Route::get('/cart', 'UserController@cartList')->name('mypage.cart');
        Route::post('/cart/delete/{id}', 'UserController@deleteCartItem')->name('mypage.cart.delete');

        // 찜한 상품 목록
        Route::get('/wishlist', 'UserController@wishlist')->name('mypage.wishlist');
        Route::post('/wishlist/delete/{id}', 'UserController@deleteWishlistItem')->name('mypage.wishlist.delete');

        // 쿠폰 목록 (PPT Slide 48)
    });

});

// Member Logout & Legacy Redirects
Route::namespace('App\Http\Controllers\Front')->group(function () {
    Route::get('/logout', 'UserController@userLogout')->name('front.logout');
    Route::redirect('/user/account', '/mypage/main');
    Route::redirect('/user/orders', '/mypage/order/list');
    Route::redirect('/user/login-register', '/member/login');
    Route::redirect('/user/logout', '/logout');
});

// Master (Admin) Portal Routes
Route::group(['prefix' => 'master', 'namespace' => 'App\Http\Controllers\Master'], function () {
    Route::get('/', 'MasterController@index')->name('master.index');
    Route::get('/sub01', 'MasterController@sub01')->name('master.sub01');
    Route::get('/sub02', 'MasterController@sub02')->name('master.sub02');
    Route::get('/sub03', 'MasterController@sub03')->name('master.sub03');
    Route::get('/loading', 'MasterController@loading')->name('master.loading');
});

// Admin routes (aliases for master routes)
// Admin routes (aliases for master routes) - REMOVED (Conflicting with main admin/ routes)

// Distributor Portal Routes (RF-04)
Route::prefix('distributor')->namespace('App\Http\Controllers\Distributor')->group(function () {
    Route::get('/login', 'DistributorController@login')->name('distributor.login');
    Route::post('/login', 'DistributorController@loginSubmit')->name('distributor.login.submit');
    Route::get('/logout', 'DistributorController@logout')->name('distributor.logout');

    Route::get('/orders/pending', 'DistributorController@ordersPending')->name('distributor.orders.pending');
    Route::get('/orders/completed', 'DistributorController@ordersCompleted')->name('distributor.orders.completed');
    Route::get('/orders/{id}', 'DistributorController@orderDetails')->name('distributor.order.details');
    Route::post('/orders/{id}/update', 'DistributorController@updateOrder')->name('distributor.order.update');
    Route::post('/orders/upload-invoice', 'DistributorController@uploadInvoice')->name('distributor.orders.upload_invoice');
});

// Legacy Admin Routes (Below)
// Routes ...
