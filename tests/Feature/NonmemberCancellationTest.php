<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonmemberCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $status = 'paid', int $vendorId = 0): array
    {
        $order = new Order;
        $order->forceFill([
            'user_id'=>0, 'name'=>'취소 검수 수령인', 'mobile'=>'010-0000-2222',
            'email'=>'cancel@example.test', 'address'=>'검수용 주소', 'city'=>'Seoul',
            'state'=>'Seoul', 'country'=>'Korea', 'pincode'=>'00000',
            'shipping_charges'=>2500, 'coupon_code'=>'', 'coupon_amount'=>0,
            'grand_total'=>14500, 'order_status'=>'Payment Captured',
            'payment_method'=>'Test', 'payment_gateway'=>'Test',
        ])->save();
        $product = Product::create([
            'section_id'=>1, 'category_id'=>1, 'brand_id'=>1, 'vendor_id'=>$vendorId,
            'admin_id'=>0, 'admin_type'=>'admin', 'product_name'=>'취소 검수 상품',
            'product_code'=>'CANCEL-'.$order->id, 'product_color'=>'Black',
            'product_price'=>12000, 'product_discount'=>0, 'product_weight'=>1, 'status'=>1,
        ]);
        $item = OrdersProduct::create([
            'order_id'=>$order->id, 'user_id'=>0, 'vendor_id'=>$vendorId,
            'admin_id'=>0, 'product_id'=>$product->id, 'product_code'=>$product->product_code,
            'product_name'=>$product->product_name, 'product_color'=>'Black', 'product_size'=>'기본옵션',
            'product_price'=>12000, 'product_qty'=>1, 'line_total'=>12000,
            'item_status'=>OrderItemStatus::label($status), 'status_code'=>$status,
            'commission'=>0, 'settlement_status'=>'pending', 'stock_deducted_qty'=>0,
        ]);

        return [$order, $item];
    }

    private function payload(Order $order, OrdersProduct $item, array $extra = []): array
    {
        return array_merge(['order_id'=>$order->id, 'order_product_id'=>$item->id,
            'type'=>'cancel', 'reason'=>'기타', 'detail_reason'=>'중복 주문으로 취소 요청합니다.'], $extra);
    }

    private function admin(string $type = 'superadmin', int $vendorId = 0): Admin
    {
        $admin = new Admin;
        $admin->forceFill(['name'=>'Cancel Admin', 'type'=>$type, 'vendor_id'=>$vendorId,
            'mobile'=>'01000000000', 'email'=>$type.'@example.test', 'password'=>bcrypt('password'),
            'confirm'=>'Yes', 'status'=>1])->save();

        return $admin;
    }

    public function test_paid_and_preparing_orders_accept_a_request_without_completing_refund(): void
    {
        foreach ([OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP] as $status) {
            [$order, $item] = $this->fixture($status);
            $this->withSession(['nonmember_order_id'=>$order->id])->from('/nonmember/order/details')
                ->post('/nonmember/order/claim', $this->payload($order, $item))
                ->assertRedirect('/nonmember/order/details')->assertSessionHas('flash_message_success');
            $this->assertSame(OrderItemStatus::CANCEL_REQUESTED, $item->fresh()->status_code);
            $this->assertSame($status, $item->fresh()->claim_previous_status);
            $this->assertDatabaseHas('order_claims', ['order_product_id'=>$item->id, 'type'=>'cancel',
                'status'=>'requested', 'reason'=>'기타', 'detail_reason'=>'중복 주문으로 취소 요청합니다.']);
            $this->assertNull($item->fresh()->financial_reversed_at);
            $this->assertEquals(14500, $order->fresh()->grand_total);
            $this->get('/nonmember/order/details')->assertOk()->assertSee('취소신청')
                ->assertSee('중복 주문으로 취소 요청합니다.')->assertDontSee('data-cancel-item=', false);
        }
    }

    public function test_other_reason_requires_text_and_validation_reopens_the_correct_modal(): void
    {
        [$order, $item] = $this->fixture();
        $this->withSession(['nonmember_order_id'=>$order->id])->from('/nonmember/order/details')
            ->post('/nonmember/order/claim', $this->payload($order, $item, ['detail_reason'=>'']))
            ->assertRedirect('/nonmember/order/details')->assertSessionHasErrors('detail_reason')
            ->assertSessionHasInput('reason', '기타');
        $this->get('/nonmember/order/details')->assertOk()->assertSee('기타 취소 사유를 입력해 주세요.');
        $this->assertSame('paid', $item->fresh()->status_code);
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_invalid_reasons_and_payload_types_do_not_change_the_order(): void
    {
        [$order, $item] = $this->fixture();
        foreach ([['reason'=>''], ['reason'=>'알 수 없는 사유'], ['detail_reason'=>['bad']],
            ['detail_reason'=>str_repeat('가', 2001)], ['order_product_id'=>['bad']]] as $invalid) {
            $this->withSession(['nonmember_order_id'=>$order->id])
                ->post('/nonmember/order/claim', $this->payload($order, $item, $invalid))
                ->assertSessionHasErrors(array_key_first($invalid));
        }
        $this->assertSame('paid', $item->fresh()->status_code);
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_standard_reason_does_not_store_a_hidden_other_reason(): void
    {
        [$order, $item] = $this->fixture();
        $this->withSession(['nonmember_order_id'=>$order->id])
            ->post('/nonmember/order/claim', $this->payload($order, $item, ['reason'=>'배송 지연']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('order_claims', ['order_product_id'=>$item->id, 'reason'=>'배송 지연', 'detail_reason'=>'']);
    }

    public function test_repeated_request_does_not_duplicate_or_overwrite_the_original_reason(): void
    {
        [$order, $item] = $this->fixture();
        $this->withSession(['nonmember_order_id'=>$order->id])
            ->post('/nonmember/order/claim', $this->payload($order, $item))->assertSessionHasNoErrors();
        $this->post('/nonmember/order/claim', $this->payload($order, $item, ['detail_reason'=>'중복된 다른 사유']))
            ->assertSessionHasErrors('action');
        $this->assertDatabaseCount('order_claims', 1);
        $this->assertSame('중복 주문으로 취소 요청합니다.', OrderClaim::firstOrFail()->detail_reason);
        $this->get('/nonmember/order/details')->assertOk()->assertSee('현재 주문 상태에서는 요청한 처리를 진행할 수 없습니다.');
    }

    public function test_shipped_delivered_and_confirmed_items_cannot_request_cancellation(): void
    {
        foreach (['shipping', 'delivered', 'confirmed'] as $status) {
            [$order, $item] = $this->fixture($status);
            $this->withSession(['nonmember_order_id'=>$order->id])
                ->post('/nonmember/order/claim', $this->payload($order, $item))->assertSessionHasErrors('action');
            $this->assertSame($status, $item->fresh()->status_code);
        }
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_unverified_session_and_another_orders_item_cannot_be_used(): void
    {
        [$order, $item] = $this->fixture();
        [$other, $otherItem] = $this->fixture();
        $this->post('/nonmember/order/claim', $this->payload($order, $item))->assertForbidden();
        $this->withSession(['nonmember_order_id'=>$order->id])
            ->post('/nonmember/order/claim', $this->payload($other, $otherItem))->assertForbidden();
        $this->post('/nonmember/order/claim', $this->payload($order, $otherItem))->assertNotFound();
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_admin_can_read_the_saved_cancellation_reason(): void
    {
        [$order, $item] = $this->fixture();
        $this->withSession(['nonmember_order_id'=>$order->id])
            ->post('/nonmember/order/claim', $this->payload($order, $item))->assertSessionHasNoErrors();
        $this->actingAs($this->admin(), 'admin')->get('/admin/orders/'.$order->id)
            ->assertOk()->assertSee('취소 신청 내역')->assertSee('접수 대기')
            ->assertSee('중복 주문으로 취소 요청합니다.')->assertSee('취소요청');
    }

    public function test_vendor_sees_only_their_own_items_cancellation_reasons(): void
    {
        [$order, $ownItem] = $this->fixture('paid', 11);
        [, $otherItem] = $this->fixture('paid', 22);
        $otherItem->update(['order_id'=>$order->id]);
        foreach ([[$ownItem, '허용된 사유'], [$otherItem, '다른 판매자의 비공개 사유']] as [$item, $reason]) {
            OrderClaim::create(['order_id'=>$order->id, 'order_product_id'=>$item->id,
                'user_id'=>0, 'vendor_id'=>$item->vendor_id, 'type'=>'cancel', 'status'=>'requested',
                'reason'=>'기타', 'detail_reason'=>$reason]);
        }
        $this->actingAs($this->admin('vendor', 11), 'admin')->get('/admin/orders/'.$order->id)
            ->assertOk()->assertSee('허용된 사유')->assertDontSee('다른 판매자의 비공개 사유');
    }

    public function test_guest_can_confirm_with_a_half_star_review_only_once(): void
    {
        [$order, $item] = $this->fixture('delivered');
        $payload = ['order_id'=>$order->id, 'order_product_id'=>$item->id, 'type'=>'confirm', 'rating'=>4.3, 'review'=>'구매 후기'];
        $this->withSession(['nonmember_order_id'=>$order->id])->post('/nonmember/order/claim', $payload)->assertSessionHasErrors('rating');
        $payload['rating'] = 4.5;
        $this->post('/nonmember/order/claim', $payload)->assertRedirect();
        $this->assertSame('confirmed', $item->fresh()->status_code);
        $this->assertNotNull($item->fresh()->confirmed_at);
        $this->assertDatabaseHas('ratings', ['user_id'=>0, 'product_id'=>$item->product_id, 'rating'=>4.5, 'review'=>'구매 후기', 'status'=>0]);
        $this->post('/nonmember/order/claim', $payload)->assertSessionHasErrors('action');
        $this->assertDatabaseCount('ratings', 1);
        $this->get('/nonmember/order/details')->assertOk()->assertSee('구매확정일');
    }

    public function test_return_and_exchange_require_other_text_and_exchange_requires_delivery(): void
    {
        foreach (['return', 'exchange'] as $type) {
            [$order, $item] = $this->fixture('delivered');
            $payload = $this->payload($order, $item, ['type'=>$type, 'recovery_method'=>'수동회수', 'detail_reason'=>'']);
            $this->withSession(['nonmember_order_id'=>$order->id])->post('/nonmember/order/claim', $payload)->assertSessionHasErrors('detail_reason');
            $this->assertSame('delivered', $item->fresh()->status_code);
        }
        [$order, $item] = $this->fixture('shipping');
        $this->withSession(['nonmember_order_id'=>$order->id])->post('/nonmember/order/claim', $this->payload($order, $item, ['type'=>'exchange', 'recovery_method'=>'자동회수']))->assertSessionHasErrors('action');
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_seller_completes_claims_and_ships_one_exchange_replacement(): void
    {
        $admin = $this->admin('vendor', 77);
        foreach (['cancel'=>'paid','return'=>'shipping','exchange'=>'delivered'] as $type=>$status) {
            [$order, $item] = $this->fixture($status, 77);
            $order->forceFill(['payment_gateway'=>'Me9 Mock Payment'])->save();
            $item->forceFill(['paid_line_total_snapshot'=>12000, 'shipping_amount_snapshot'=>2500,
                'point_usage_snapshot'=>['channel'=>0,'me9'=>0], 'used_point_amount'=>0])->save();
            $this->withSession(['nonmember_order_id'=>$order->id])->post('/nonmember/order/claim', $this->payload($order, $item, [
                'type'=>$type, 'recovery_method'=>'수동회수',
            ]))->assertRedirect();
            $this->actingAs($admin,'admin');
            $actions = match($type) { 'cancel'=>['cancel_approve'], 'return'=>['return_receive','return_complete'], default=>['exchange_approve','exchange_receive','exchange_complete'] };
            foreach ($actions as $action) {
                $this->postJson('/channel/order/claim/action', ['order_id'=>$order->id,'item_ids'=>[$item->id],'action'=>$action], ['X-Requested-With'=>'XMLHttpRequest'])->assertOk()->assertJson(['status'=>true]);
            }
            $this->assertSame(['cancel'=>'cancelled','return'=>'returned','exchange'=>'exchanged'][$type],$item->fresh()->status_code);
            $this->assertDatabaseHas('order_claims', ['order_product_id'=>$item->id,'type'=>$type,'status'=>'completed']);
            $this->get('/nonmember/order/details')->assertOk()->assertSee(['cancel'=>'취소완료일','return'=>'반품완료일','exchange'=>'교환완료일'][$type]);
            if ($type !== 'exchange') {
                $this->assertSame('mock_refunded',$item->fresh()->refund_status);
                $this->assertEquals(14500,$item->fresh()->refund_cash_amount);
            } else {
                $replacement = OrdersProduct::where('replacement_for_order_product_id',$item->id)->sole();
                $this->assertEquals(0,$replacement->line_total);
                $this->postJson('/channel/order/claim/action', ['order_id'=>$order->id,'item_ids'=>[$item->id],'action'=>'exchange_complete'], ['X-Requested-With'=>'XMLHttpRequest'])->assertUnprocessable();
                $this->assertSame(1,OrdersProduct::where('replacement_for_order_product_id',$item->id)->count());
                $this->postJson('/channel/order/status/update', ['order_id'=>$order->id,'item_ids'=>[$replacement->id],'status'=>'shipping','courier_name'=>'검수 택배','tracking_number'=>'TEST-RESEND'], ['X-Requested-With'=>'XMLHttpRequest'])->assertOk()->assertJson(['status'=>true]);
                $this->assertSame('shipping',$replacement->fresh()->status_code);
                $this->assertNotNull($replacement->fresh()->shipped_at);
                $this->get('/nonmember/order/details')->assertOk()->assertSee('TEST-RESEND')->assertSee('원상품 보기');
            }
        }
    }

    public function test_inquiry_and_seller_reply_are_visible_only_to_the_verified_order(): void
    {
        [$order,$item] = $this->fixture('confirmed',77);
        $order->forceFill(['buyer_name'=>'주문자 이름','buyer_mobile'=>'010-7777-8888'])->save();
        $this->withSession(['nonmember_order_id'=>$order->id])->post('/nonmember/order/inquiry',[
            'order_id'=>$order->id,'order_product_id'=>$item->id,'type'=>'inquiry',
            'inquiry_category'=>'product','subject'=>'관리 방법 문의','message'=>'상품 관리 방법을 알려주세요',
        ])->assertRedirect();
        $inquiry = Contact::sole();
        $this->assertSame('주문자 이름',$inquiry->name);
        $this->assertSame('010-7777-8888',$inquiry->phone);
        $this->actingAs($this->admin('vendor',77),'admin')->post('/channel/inquiries/'.$inquiry->id.'/reply',[
            'status'=>'completed','admin_reply'=>'부드러운 천으로 관리해 주세요',
        ])->assertRedirect();
        $this->get('/nonmember/order/details')->assertOk()->assertSee('답변완료')->assertSee('부드러운 천으로 관리해 주세요');
        [$other] = $this->fixture();
        $this->withSession(['nonmember_order_id'=>$other->id])->get('/nonmember/order/details')
            ->assertOk()->assertDontSee('부드러운 천으로 관리해 주세요')->assertDontSee('상품 관리 방법을 알려주세요');
    }

    public function test_channel_order_payload_does_not_expose_another_vendors_claim(): void
    {
        [$order,$own] = $this->fixture('cancel_requested',11);
        [, $other] = $this->fixture('cancel_requested',22);
        $other->update(['order_id'=>$order->id]);
        foreach ([[$own,'본인 판매자 사유'],[$other,'타 판매자 비공개 사유']] as [$item,$text]) {
            OrderClaim::create(['order_id'=>$order->id,'order_product_id'=>$item->id,'user_id'=>0,
                'vendor_id'=>$item->vendor_id,'type'=>'cancel','status'=>'requested','reason'=>'기타','detail_reason'=>$text]);
        }
        $response = $this->actingAs($this->admin('vendor',11),'admin')->get('/channel/order/cancel/list')->assertOk();
        $visibleOrder = $response->viewData('orders')->first();
        $this->assertSame(['본인 판매자 사유'],$visibleOrder->claims_data->pluck('detail_reason')->all());
        $this->assertSame(['본인 판매자 사유'],$visibleOrder->claims->pluck('detail_reason')->all());
    }
}
