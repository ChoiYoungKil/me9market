<?php

namespace Tests\Feature;

use App\Models\{Order, OrderClaim, OrdersProduct, User};
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private function orderFor(User $user): Order
    {
        return Order::forceCreate([
            'user_id'=>$user->id, 'name'=>'수취인', 'buyer_name'=>'주문자',
            'address'=>'검수 주소', 'city'=>'상세주소', 'state'=>'서울', 'country'=>'Korea',
            'pincode'=>'04524', 'mobile'=>'01000000000', 'email'=>$user->email,
            'shipping_charges'=>2500, 'order_status'=>'New', 'payment_method'=>'Mock Card',
            'payment_gateway'=>'Me9 Mock Payment', 'grand_total'=>5500, 'delivery_memo'=>'검수 배송메모',
        ]);
    }

    private function itemFor(Order $order, string $status, string $name): OrdersProduct
    {
        return OrdersProduct::create([
            'order_id'=>$order->id, 'user_id'=>$order->user_id, 'vendor_id'=>1, 'admin_id'=>1,
            'product_id'=>1, 'product_code'=>'QA-DETAIL', 'product_name'=>$name,
            'product_color'=>'검정', 'product_size'=>'기본옵션', 'product_price'=>3000,
            'product_qty'=>1, 'line_total'=>3000, 'paid_line_total_snapshot'=>3000,
            'shipping_amount_snapshot'=>2500, 'status_code'=>$status, 'item_status'=>OrderItemStatus::label($status),
        ]);
    }

    public function test_detail_shows_replacement_shipping_without_charging_for_the_replacement(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user);
        $original = $this->itemFor($order, OrderItemStatus::EXCHANGED, '교환 원상품');
        OrderClaim::create(['order_id'=>$order->id, 'order_product_id'=>$original->id,
            'user_id'=>$user->id, 'vendor_id'=>1, 'type'=>'exchange', 'reason'=>'other',
            'detail_reason'=>'교환 검수 사유', 'status'=>'completed']);
        $replacement = $this->itemFor($order, OrderItemStatus::SHIPPING, '교환 재발송 상품');
        $replacement->forceFill([
            'replacement_for_order_product_id'=>$original->id, 'is_exchange_replacement'=>true,
            'product_price'=>0, 'line_total'=>0, 'paid_line_total_snapshot'=>0, 'shipping_amount_snapshot'=>0,
            'courier_name'=>'직접배송', 'tracking_number'=>'QA-SHIP-64', 'shipped_at'=>now(),
        ])->save();

        $this->actingAs($user)->get(route('mypage.order.view', ['id'=>$order->id]))
            ->assertOk()->assertSee('교환 원상품')->assertSee('교환 재발송 상품')
            ->assertSee('추가 상품 결제 없음')->assertSee('QA-SHIP-64')->assertSee('직접배송')
            ->assertSee('교환 검수 사유')->assertSee('교환완료')->assertSee('검수 배송메모')
            ->assertSee('5,500')->assertSee('0 원')->assertDontSee('<td>completed</td>', false);
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('mypage.order.view', ['id'=>$order->id]))
            ->assertRedirect(route('mypage.order.list'));
    }

    public function test_pending_claims_remain_in_their_member_lists_and_detail_sections(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user);
        $return = $this->itemFor($order, OrderItemStatus::RETURN_HOLD, '반품 보류 상품');
        $exchange = $this->itemFor($order, OrderItemStatus::EXCHANGE_RECEIVED, '교환 회수 상품');
        foreach (['return'=>$return, 'exchange'=>$exchange] as $type=>$item) {
            OrderClaim::create(['order_id'=>$order->id, 'order_product_id'=>$item->id,
                'user_id'=>$user->id, 'vendor_id'=>1, 'type'=>$type, 'reason'=>'other',
                'detail_reason'=>'검수 사유', 'status'=>'received']);
            $this->actingAs($user)->get(route('mypage.order.list', ['tab'=>$type, 'status'=>$type.'_request']))
                ->assertOk()->assertSee($item->product_name)->assertSee($item->status_label);
        }
        $this->get(route('mypage.order.view', ['id'=>$order->id]))->assertOk()
            ->assertSee('반품 상품')->assertSee('반품보류')->assertSee('교환 상품')->assertSee('교환회수완료');
    }
}
