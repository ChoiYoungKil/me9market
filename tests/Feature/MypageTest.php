<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cart;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\Vendor;
use App\Models\VendorsBankDetail;
use App\Models\VendorsBusinessDetail;
use App\Models\Wishlist;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_mypage()
    {
        $response = $this->get('/mypage/main');

        $response->assertRedirect('/member/login');
    }

    public function test_default_member_can_login_from_member_login_page()
    {
        $this->get(route('front.member.login'))->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'user@user.com',
            'username' => 'user@user.com',
            'status' => 1,
        ]);

        $response = $this->post(route('front.member.login.submit'), [
            'login_id' => 'user@user.com',
            'password' => '123456',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated('web');
    }

    public function test_authenticated_user_can_access_mypage_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mypage/main');

        $response->assertStatus(200);
    }

    public function test_user_profile_update()
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'mobile' => '010-1111-1111',
        ]);

        $response = $this->actingAs($user)->post('/mypage/profile', [
            'name' => 'New Name',
            'gender' => 'Male',
            'mobile_1' => '010',
            'mobile_2' => '2222',
            'mobile_3' => '2222',
            'email_1' => 'new',
            'email_2' => 'example.com',
            'zipcode' => '54321',
            'address1' => 'Seoul',
            'address2' => 'Gangnam',
        ]);

        $response->assertRedirect();
        
        $user = $user->fresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('010-2222-2222', $user->mobile);
        $this->assertEquals('54321', $user->pincode);
        $this->assertEquals('Seoul', $user->address);
        $this->assertEquals('Gangnam', $user->city);
    }

    public function test_user_profile_edit_screen_submits_real_update_form()
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'mobile' => '010-1111-1111',
        ]);

        $this->actingAs($user)
            ->get('/mypage/profile')
            ->assertOk()
            ->assertSee('id="profileForm"', false)
            ->assertSee('action="' . route('mypage.profile.update') . '"', false)
            ->assertSee('type="submit"', false)
            ->assertSee('정보수정')
            ->assertSee("execDaumPostcode('profile_zipcode', 'profile_address1', 'profile_address2')", false)
            ->assertSee('id="companyInfoForm"', false)
            ->assertSee('action="' . route('front.member.register.step2.update') . '"', false)
            ->assertSee('회원사 정보 저장')
            ->assertSee("execDaumPostcode('company_zipcode', 'company_address1', 'company_address2')", false)
            ->assertSee('id="sellerCertificationForm"', false)
            ->assertSee('action="' . route('front.member.register.step3.update') . '"', false)
            ->assertSee('인증요청')
            ->assertDontSee('href="#" class="btn_submit"', false);
    }

    public function test_mypage_company_info_update_persists_vendor_details()
    {
        $user = User::factory()->create([
            'type' => 'general',
            'mobile' => '010-1111-1111',
        ]);

        $this->actingAs($user)
            ->postJson(route('front.member.register.step2.update'), [
                'shop_name' => 'Profile Shop',
                'shop_business_type' => 'business',
                'business_license_1' => '123',
                'business_license_2' => '45',
                'business_license_3' => '67890',
                'mobile_1' => '010',
                'mobile_2' => '2222',
                'mobile_3' => '3333',
                'email_1' => 'shop',
                'email_2' => 'example.com',
                'zipcode' => '12345',
                'address1' => 'Seoul',
                'address2' => 'Mapo',
                'bank_name' => '국민은행',
                'account_number' => '1234567890',
                'account_holder_name' => 'Shop Owner',
                'agree1' => '1',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $user = $user->fresh();
        $this->assertNotNull($user->vendor_id);
        $this->assertEquals('company', $user->type);

        $this->assertDatabaseHas('vendors_business_details', [
            'vendor_id' => $user->vendor_id,
            'shop_name' => 'Profile Shop',
            'business_license_number' => '123-45-67890',
            'shop_mobile' => '010-2222-3333',
            'shop_email' => 'shop@example.com',
            'bank_name' => '국민은행',
            'bank_account_number' => '1234567890',
            'bank_account_holder_name' => 'Shop Owner',
        ]);

        $this->assertDatabaseHas('vendors_bank_details', [
            'vendor_id' => $user->vendor_id,
            'bank_name' => '국민은행',
            'account_number' => '1234567890',
            'account_holder_name' => 'Shop Owner',
        ]);
    }

    public function test_mypage_seller_certification_request_updates_vendor_user_flow()
    {
        $vendor = new Vendor;
        $vendor->name = 'Profile Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'vendor@example.com';
        $vendor->status = 0;
        $vendor->commission = 0;
        $vendor->confirm = 'No';
        $vendor->save();

        $business = new VendorsBusinessDetail;
        $business->vendor_id = $vendor->id;
        $business->shop_name = 'Profile Vendor Shop';
        $business->shop_address = 'Seoul';
        $business->shop_mobile = '010-1234-5678';
        $business->shop_email = 'vendor@example.com';
        $business->bank_name = '국민은행';
        $business->bank_account_number = '11112222';
        $business->bank_account_holder_name = 'Old Owner';
        $business->save();

        $bank = new VendorsBankDetail;
        $bank->vendor_id = $vendor->id;
        $bank->bank_name = '국민은행';
        $bank->account_number = '11112222';
        $bank->account_holder_name = 'Old Owner';
        $bank->bank_ifsc_code = '';
        $bank->save();

        $user = User::factory()->create([
            'vendor_id' => $vendor->id,
            'type' => 'company',
        ]);

        $this->actingAs($user)
            ->postJson(route('front.member.register.step3.update'), [
                'bank_name' => '국민은행',
                'account_number' => '99990000',
                'account_holder_name' => 'New Owner',
                'agree1' => '1',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals('vendor', $user->fresh()->type);
        $this->assertDatabaseHas('vendors_business_details', [
            'vendor_id' => $vendor->id,
            'bank_account_number' => '99990000',
            'bank_account_holder_name' => 'New Owner',
        ]);
        $this->assertDatabaseHas('vendors_bank_details', [
            'vendor_id' => $vendor->id,
            'account_number' => '99990000',
            'account_holder_name' => 'New Owner',
        ]);
    }

    public function test_user_password_update_from_profile_ajax()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        $this->actingAs($user)
            ->postJson(route('user.update.password'), [
                'current_password' => 'old-password',
                'new_password' => 'new-password',
                'confirm_password' => 'new-password',
            ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson([
                'type' => 'success',
            ]);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_delivery_addresses_management_crud()
    {
        $user = User::factory()->create();

        // 1. Create (Add)
        $response = $this->actingAs($user)->post('/mypage/delivery/add', [
            'name' => 'Home Address',
            'zipcode' => '12345',
            'address1' => 'Seoul',
            'address2' => 'Mapo-gu',
            'is_default' => '1',
            'mobile' => '01012345678',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('delivery_addresses', [
            'user_id' => $user->id,
            'name' => 'Home Address',
            'address' => 'Seoul',
            'city' => 'Mapo-gu',
            'is_default' => 1,
        ]);

        $address = DeliveryAddress::where('user_id', $user->id)->first();

        // 2. Update
        $response = $this->actingAs($user)->post('/mypage/delivery/update', [
            'id' => $address->id,
            'name' => 'Updated Home Address',
            'zipcode' => '54321',
            'address1' => 'Seoul',
            'address2' => 'Yongsan-gu',
            'is_default' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('delivery_addresses', [
            'id' => $address->id,
            'name' => 'Updated Home Address',
            'city' => 'Yongsan-gu',
        ]);

        // 3. Delete
        $response = $this->actingAs($user)->post('/mypage/delivery/delete', [
            'id' => $address->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('delivery_addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_user_withdrawal_flow()
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        // Fail with wrong password
        $response = $this->actingAs($user)->post('/mypage/withdraw/submit', [
            'password' => 'wrong-password',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error_message', '비밀번호가 일치하지 않습니다.');
        $this->assertEquals(1, $user->fresh()->status);

        // Succeed with correct password
        $response = $this->actingAs($user)->post('/mypage/withdraw/submit', [
            'password' => 'correct-password',
            'password_confirmation' => 'correct-password',
        ]);

        $response->assertRedirect(route('mypage.withdraw.success'));
        $this->assertEquals(0, $user->fresh()->status);
    }

    public function test_user_order_cancel_is_linked_to_mypage_cancel_views()
    {
        $user = User::factory()->create([
            'email' => 'cancel-user@example.com',
        ]);

        $order = new Order;
        $order->user_id = $user->id;
        $order->name = $user->name;
        $order->address = 'Seoul';
        $order->city = 'Gangnam';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01012345678';
        $order->email = $user->email;
        $order->shipping_charges = 0;
        $order->order_status = 'New';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Test';
        $order->grand_total = 10000;
        $order->save();

        $item = OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'vendor_id' => 1,
            'admin_id' => 1,
            'product_id' => 1,
            'product_code' => 'T-CANCEL-001',
            'product_name' => '마이페이지 취소 연동 상품',
            'product_color' => 'Black',
            'product_size' => 'FREE',
            'product_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'item_status' => '결제완료',
            'status_code' => OrderItemStatus::PAID,
        ]);

        $this->actingAs($user)
            ->get(route('mypage.order.list'))
            ->assertStatus(200)
            ->assertSee('마이페이지 취소 연동 상품')
            ->assertSee('취소신청');

        $this->actingAs($user)
            ->post(route('mypage.order.claim.submit'), [
                'order_item_id' => $item->id,
                'type' => 'cancel',
                'reason' => '고객 단순 변심',
                'detail_reason' => '테스트 취소 요청',
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '취소 신청이 완료되었습니다.',
            ]);

        $item->refresh();
        $this->assertSame(OrderItemStatus::CANCEL_REQUESTED, $item->status_code);
        $this->assertSame('취소요청', $item->item_status);

        $this->assertDatabaseHas('order_claims', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'order_product_id' => $item->id,
            'type' => 'cancel',
            'reason' => '고객 단순 변심',
            'status' => 'requested',
        ]);

        $this->actingAs($user)
            ->get(route('mypage.order.list', ['tab' => 'cancel']))
            ->assertStatus(200)
            ->assertSee('마이페이지 취소 연동 상품')
            ->assertSee('취소요청');

        $this->actingAs($user)
            ->get(route('mypage.order.list', ['tab' => 'cancel', 'status' => 'cancel_request']))
            ->assertStatus(200)
            ->assertSee('마이페이지 취소 연동 상품');

        $this->actingAs($user)
            ->get(route('mypage.order.cancel_return_list', ['type' => 'cancel']))
            ->assertStatus(200)
            ->assertSee('마이페이지 취소 연동 상품')
            ->assertSee('취소');

        $this->actingAs($user)
            ->get(route('mypage.order.view', ['id' => $order->id]))
            ->assertStatus(200)
            ->assertSee('마이페이지 취소 연동 상품')
            ->assertSee('취소요청');
    }

    public function test_mypage_sidebar_order_badges_use_real_order_item_counts()
    {
        $user = User::factory()->create();

        $order = new Order;
        $order->user_id = $user->id;
        $order->name = $user->name;
        $order->address = 'Seoul';
        $order->city = 'Gangnam';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01012345678';
        $order->email = $user->email;
        $order->shipping_charges = 0;
        $order->order_status = 'New';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Test';
        $order->grand_total = 90000;
        $order->save();

        $statuses = [
            [OrderItemStatus::PAID, '결제완료 상품'],
            [OrderItemStatus::READY_TO_SHIP, '배송대기 상품'],
            [OrderItemStatus::SHIPPING, '배송중 상품'],
            [OrderItemStatus::DELIVERED, '배송완료 상품'],
            [OrderItemStatus::CONFIRMED, '구매확정 상품'],
            [OrderItemStatus::CANCEL_REQUESTED, '취소요청 상품'],
            [OrderItemStatus::CANCELLED, '취소완료 상품'],
            [OrderItemStatus::RETURN_REQUESTED, '반품신청 상품'],
            [OrderItemStatus::RETURNED, '반품완료 상품'],
            [OrderItemStatus::EXCHANGE_REQUESTED, '교환신청 상품'],
            [OrderItemStatus::EXCHANGED, '교환완료 상품'],
        ];

        foreach ($statuses as $index => [$status, $name]) {
            $item = new OrdersProduct([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'vendor_id' => 1,
                'admin_id' => 1,
                'product_id' => $index + 1,
                'product_code' => 'BADGE-' . $index,
                'product_name' => $name,
                'product_color' => 'Black',
                'product_size' => 'FREE',
                'product_price' => 10000,
                'product_qty' => 1,
                'line_total' => 10000,
            ]);
            $item->setStatus($status);
            $item->save();
        }

        $this->actingAs($user)
            ->get(route('mypage.order.list'))
            ->assertStatus(200)
            ->assertSee('aria-label="주문목록 5건"', false)
            ->assertSee('aria-label="결제완료 1건"', false)
            ->assertSee('aria-label="배송대기중 1건"', false)
            ->assertSee('aria-label="배송중 2건"', false)
            ->assertSee('aria-label="구매확정 1건"', false)
            ->assertSee('aria-label="취소요청 1건"', false)
            ->assertSee('aria-label="취소완료 1건"', false)
            ->assertSee('aria-label="반품신청 1건"', false)
            ->assertSee('aria-label="반품완료 1건"', false)
            ->assertSee('aria-label="교환신청 1건"', false)
            ->assertSee('aria-label="교환완료 1건"', false);
    }

    public function test_mypage_search_and_review_actions_are_wired_to_real_routes()
    {
        $user = User::factory()->create();

        $order = new Order;
        $order->user_id = $user->id;
        $order->name = $user->name;
        $order->address = 'Seoul';
        $order->city = 'Gangnam';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01012345678';
        $order->email = $user->email;
        $order->shipping_charges = 0;
        $order->order_status = 'New';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Test';
        $order->grand_total = 10000;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'vendor_id' => 1,
            'admin_id' => 1,
            'product_id' => 10,
            'product_code' => 'REVIEW-001',
            'product_name' => '리뷰 작성 대상 상품',
            'product_color' => 'Black',
            'product_size' => 'FREE',
            'product_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'item_status' => '구매확정',
            'status_code' => OrderItemStatus::CONFIRMED,
        ]);

        $this->actingAs($user)
            ->get(route('mypage.order.list'))
            ->assertOk()
            ->assertSee('class="btn_search"', false)
            ->assertSee('js-review-popup', false)
            ->assertSee('action="' . route('front.rating.add') . '"', false)
            ->assertDontSee('<a href="#" class="btn02 col3">리뷰작성</a>', false);

        $this->actingAs($user)
            ->get(route('mypage.cart'))
            ->assertOk()
            ->assertSee('action="' . route('mypage.cart') . '"', false)
            ->assertDontSee('<a href="#" class="btn01 btn_black">검색</a>', false);

        $this->actingAs($user)
            ->get(route('mypage.wishlist'))
            ->assertOk()
            ->assertSee('action="' . route('mypage.wishlist') . '"', false)
            ->assertDontSee('<a href="#" class="btn01 btn_black">검색</a>', false);

        $this->actingAs($user)
            ->get(route('mypage.point.history'))
            ->assertOk()
            ->assertSee('action="' . route('mypage.point.history') . '"', false)
            ->assertDontSee('href="#" class="btn01" style="background-color: #000; border-color: #000;">검색', false);
    }

    public function test_mypage_search_filters_and_review_submit_persist_data()
    {
        $user = User::factory()->create();

        $matchedShop = ShopChannel::create([
            'vendor_id' => 1,
            'channel_code' => 'MATCH-SHOP',
            'status' => 1,
            'is_public' => 1,
            'is_member_only' => 0,
            'channel_name' => '검색대상 채널',
            'copyright' => 'Me9',
            'keywords' => [],
        ]);

        $otherShop = ShopChannel::create([
            'vendor_id' => 2,
            'channel_code' => 'OTHER-SHOP',
            'status' => 1,
            'is_public' => 1,
            'is_member_only' => 0,
            'channel_name' => '다른 채널',
            'copyright' => 'Me9',
            'keywords' => [],
        ]);

        $matchedProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => 1,
            'admin_id' => 1,
            'admin_type' => 'vendor',
            'product_name' => '검색 노출 상품',
            'product_code' => 'MATCH-PRODUCT',
            'product_color' => 'Black',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 1,
            'description' => 'test',
            'status' => 1,
        ]);

        $otherProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => 2,
            'admin_id' => 1,
            'admin_type' => 'vendor',
            'product_name' => '검색 제외 상품',
            'product_code' => 'OTHER-PRODUCT',
            'product_color' => 'White',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 1,
            'description' => 'test',
            'status' => 1,
        ]);

        $matchedShopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $matchedShop->id,
            'product_id' => $matchedProduct->id,
            'product_type' => 'own',
            'status' => 1,
            'selling_price' => 10000,
        ]);

        ShopChannelProduct::create([
            'shop_channel_id' => $otherShop->id,
            'product_id' => $otherProduct->id,
            'product_type' => 'own',
            'status' => 1,
            'selling_price' => 10000,
        ]);

        $cart = new Cart;
        $cart->session_id = 'test-session';
        $cart->user_id = $user->id;
        $cart->product_id = $matchedProduct->id;
        $cart->size = 'FREE';
        $cart->quantity = 1;
        $cart->save();

        $otherCart = new Cart;
        $otherCart->session_id = 'test-session';
        $otherCart->user_id = $user->id;
        $otherCart->product_id = $otherProduct->id;
        $otherCart->size = 'FREE';
        $otherCart->quantity = 1;
        $otherCart->save();

        Wishlist::create([
            'user_id' => $user->id,
            'shop_channel_product_id' => $matchedShopProduct->id,
        ]);

        PointTransaction::create([
            'user_id' => $user->id,
            'shop_channel_id' => $matchedShop->id,
            'type' => 'earn',
            'points' => 500,
            'description' => '검색 가능한 적립',
        ]);

        PointTransaction::create([
            'user_id' => $user->id,
            'shop_channel_id' => $otherShop->id,
            'type' => 'earn',
            'points' => 100,
            'description' => '다른 적립',
        ]);

        $this->actingAs($user)
            ->get(route('mypage.cart', ['channel_name' => '검색대상']))
            ->assertOk()
            ->assertSee('검색 노출 상품')
            ->assertDontSee('검색 제외 상품');

        $this->actingAs($user)
            ->get(route('mypage.wishlist', ['channel_name' => '검색대상']))
            ->assertOk()
            ->assertSee('검색 노출 상품');

        $this->actingAs($user)
            ->get(route('mypage.point.history', ['shop_channel' => '검색대상', 'point_min' => 400]))
            ->assertOk()
            ->assertSee('검색 가능한 적립')
            ->assertDontSee('다른 적립');

        $this->actingAs($user)
            ->post(route('front.rating.add'), [
                'product_id' => $matchedProduct->id,
                'rating' => 4,
                'review' => '마이페이지 리뷰 저장 테스트',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'product_id' => $matchedProduct->id,
            'rating' => 4,
            'review' => '마이페이지 리뷰 저장 테스트',
            'status' => 0,
        ]);
    }

    public function test_approved_closed_channel_points_can_convert_to_me9_points()
    {
        $user = User::factory()->create();
        $shop = ShopChannel::create([
            'vendor_id' => 1,
            'channel_code' => 'stopped-shop',
            'status' => 0,
            'closure_status' => 'approved',
            'is_public' => 1,
            'is_member_only' => 0,
            'channel_name' => '운영종료 채널',
            'copyright' => 'Me9',
            'keywords' => [],
        ]);

        PointTransaction::create([
            'user_id' => $user->id,
            'shop_channel_id' => $shop->id,
            'type' => 'earn',
            'points' => 1000,
            'description' => '테스트 적립',
        ]);

        $this->actingAs($user)
            ->post(route('mypage.point.convert'), ['shop_channel_id' => $shop->id])
            ->assertRedirect();

        $this->assertSame(0, (int) PointTransaction::where('user_id', $user->id)->where('shop_channel_id', $shop->id)->sum('points'));
        $this->assertSame(1000, (int) PointTransaction::where('user_id', $user->id)->whereNull('shop_channel_id')->sum('points'));
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'shop_channel_id' => $shop->id,
            'type' => 'convert_out',
            'points' => -1000,
        ]);
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'shop_channel_id' => null,
            'type' => 'convert_in',
            'points' => 1000,
        ]);
    }

    public function test_unapproved_channel_points_cannot_convert_to_me9_points()
    {
        $user = User::factory()->create();
        $shop = ShopChannel::create([
            'vendor_id' => 1,
            'channel_code' => 'unapproved-shop',
            'status' => 0,
            'closure_status' => 'requested',
            'is_public' => 1,
            'is_member_only' => 0,
            'channel_name' => '승인대기 채널',
            'copyright' => 'Me9',
            'keywords' => [],
        ]);

        PointTransaction::create([
            'user_id' => $user->id,
            'shop_channel_id' => $shop->id,
            'type' => 'earn',
            'points' => 1000,
            'description' => '테스트 적립',
        ]);

        $this->actingAs($user)
            ->post(route('mypage.point.convert'), ['shop_channel_id' => $shop->id])
            ->assertRedirect()
            ->assertSessionHas('error_message', 'Shop 채널 운영중지 승인 완료 후 Me9 포인트로 전환할 수 있습니다.');

        $this->assertSame(1000, (int) PointTransaction::where('user_id', $user->id)->where('shop_channel_id', $shop->id)->sum('points'));
        $this->assertSame(0, (int) PointTransaction::where('user_id', $user->id)->whereNull('shop_channel_id')->sum('points'));
    }
}
