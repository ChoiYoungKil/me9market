<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
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
}
