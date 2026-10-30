<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\OrderClaim;
use App\Models\ShopChannelProduct;
use App\Services\ChannelPointService;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_steps_cannot_be_skipped_and_holds_can_be_released(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $this->actingAs($admin, 'admin')->withHeaders(['X-Requested-With' => 'XMLHttpRequest']);
        $payload = ['order_id' => $order->id, 'item_ids' => [$item->id]];
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $this->postJson('/channel/order/return/request', $payload + ['reason' => 'Damaged', 'detail_reason' => 'Damaged parcel'])->assertOk();
        foreach (['return_complete', 'return_hold'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action, 'reason' => '검수 보류 사유'])->assertUnprocessable();
        }
        foreach (['return_receive', 'return_hold'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action, 'reason' => '검수 보류 사유'])->assertOk();
        }
        $this->postJson('/channel/order/claim/action', $payload + ['action' => 'return_complete'])->assertUnprocessable();
        foreach (['return_release', 'return_complete'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action, 'reason' => '검수 보류 사유'])->assertOk();
        }
        $this->assertSame(OrderItemStatus::RETURNED, $item->fresh()->normalized_status);
        $this->postJson('/channel/order/claim/action', $payload + ['action' => 'return_complete'])->assertUnprocessable();
    }

    public function test_exchange_requires_approval_and_rejects_price_changing_options(): void
    {
        [, $admin, , $product, $order, $item] = $this->createSetup();
        \App\Models\ProductsAttribute::create(['product_id' => $product->id, 'size' => 'L', 'sku' => 'L',
            'price' => 1100, 'price_adjustment' => 1000, 'option_type' => 'price', 'stock' => 10, 'status' => 1]);
        $this->actingAs($admin, 'admin')->withHeaders(['X-Requested-With' => 'XMLHttpRequest']);
        $payload = ['order_id' => $order->id, 'item_ids' => [$item->id]];
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $this->postJson('/channel/order/exchange/request', $payload + ['reason' => 'Size', 'detail_reason' => 'Wrong size'])->assertOk();
        foreach (['exchange_receive', 'exchange_complete', 'exchange_hold_after', 'exchange_to_return'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action, 'reason' => '검수 보류 사유'])->assertUnprocessable();
        }
        $this->postJson('/channel/order/claim/action', $payload + ['action' => 'exchange_option', 'option' => 'L'])->assertUnprocessable();
        foreach (['exchange_hold_before', 'exchange_release_before', 'exchange_approve', 'exchange_receive', 'exchange_hold_after', 'exchange_release_after', 'exchange_complete'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action, 'reason' => '검수 보류 사유'])->assertOk();
        }
        $this->assertSame(OrderItemStatus::EXCHANGED, $item->fresh()->normalized_status);
    }

    public function test_cancellation_rejection_restores_ready_to_ship(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->setStatus(OrderItemStatus::READY_TO_SHIP);
        $item->save();
        $this->actingAs($admin, 'admin')->withHeaders(['X-Requested-With' => 'XMLHttpRequest']);
        $payload = ['order_id' => $order->id, 'item_ids' => [$item->id]];
        $item->setStatus(OrderItemStatus::CANCEL_REQUESTED);
        $item->save();
        OrderClaim::create(['order_id' => $order->id, 'order_product_id' => $item->id, 'vendor_id' => $item->vendor_id,
            'user_id' => $item->user_id, 'type' => 'cancel', 'reason' => 'Cancel', 'status' => 'requested']);
        $this->postJson('/channel/order/claim/action', $payload + ['action' => 'cancel_reject'])->assertOk();
        $this->assertSame(OrderItemStatus::READY_TO_SHIP, $item->fresh()->normalized_status);
    }

    private function createSetup()
    {
        $vendor = new Vendor;
        $vendor->name = 'Test Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'vendor@example.com';
        $vendor->status = 1;
        $vendor->commission = 0;
        $vendor->confirm = 'Yes';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Test Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01012345678';
        $admin->email = 'admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $shop = new ShopChannel;
        $shop->vendor_id = $vendor->id;
        $shop->channel_code = 'testcode';
        $shop->channel_name = 'Test Channel';
        $shop->copyright = 'Test Copyright';
        $shop->keywords = '[]';
        $shop->settlement_type = 1;
        $shop->settlement_rate = 10;
        $shop->status = 1;
        $shop->save();

        $product = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'admin_type' => 'vendor',
            'product_name' => 'Test Product',
            'product_code' => 'P123',
            'product_color' => 'Blue',
            'product_price' => 100.0,
            'product_discount' => 0.0,
            'product_weight' => 500,
            'status' => 1,
        ]);

        $order = new Order;
        $order->user_id = 1;
        $order->name = 'Customer Name';
        $order->address = 'Test Address';
        $order->city = 'Seoul';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01011112222';
        $order->email = 'customer@example.com';
        $order->shipping_charges = 0;
        $order->order_status = 'New';
        $order->payment_method = 'COD';
        $order->payment_gateway = 'COD';
        $order->grand_total = 100.0;
        $order->save();

        $item = new OrdersProduct;
        $item->order_id = $order->id;
        $item->user_id = 1;
        $item->vendor_id = $vendor->id;
        $item->shop_channel_id = $shop->id;
        $item->admin_id = $admin->id;
        $item->product_id = $product->id;
        $item->product_code = $product->product_code;
        $item->product_name = $product->product_name;
        $item->product_color = $product->product_color;
        $item->product_size = 'M';
        $item->product_price = 100.0;
        $item->product_qty = 1;
        $item->setStatus(OrderItemStatus::PAID);
        $item->save();

        return [$vendor, $admin, $shop, $product, $order, $item];
    }

    public function test_update_status_updates_item_status_and_tracking_info()
    {
        list($vendor, $admin, $shop, $product, $order, $item) = $this->createSetup();

        $item->setStatus(OrderItemStatus::READY_TO_SHIP);
        $item->save();
        $response = $this->actingAs($admin, 'admin')->post('/channel/order/status/update', [
            'order_id' => $order->id,
            'status' => 'shipping',
            'item_ids' => [$item->id],
            'courier_name' => 'FastDelivery',
            'tracking_number' => 'TRACK123',
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '주문 상품 상태가 업데이트되었습니다.',
        ]);

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'status_code' => OrderItemStatus::SHIPPING,
            'item_status' => OrderItemStatus::label(OrderItemStatus::SHIPPING),
            'courier_name' => 'FastDelivery',
            'tracking_number' => 'TRACK123',
        ]);
    }

    public function test_shipping_status_does_not_charge_for_unsent_sms()
    {
        list($vendor, $admin, $shop, $product, $order, $item) = $this->createSetup();
        $service = app(ChannelPointService::class);
        $purchase = $service->requestPurchase($vendor->id, 100000, 'card', null, $shop->id, $admin->id);
        $service->approve($purchase, $admin->id);

        $item->setStatus(OrderItemStatus::READY_TO_SHIP);
        $item->save();
        $response = $this->actingAs($admin, 'admin')->post('/channel/order/status/update', [
            'order_id' => $order->id,
            'status' => 'shipping',
            'item_ids' => [$item->id],
            'courier_name' => 'FastDelivery',
            'tracking_number' => 'TRACK123',
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson(['status' => true]);

        $this->assertSame(100000, $service->balanceForVendor($vendor->id));
        $this->assertDatabaseMissing('channel_point_transactions', [
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'type' => ChannelPointService::TYPE_SMS,
            'points' => -20,
            'status' => 'approved',
            'memo' => '배송중 안내 문자 발송',
        ]);
        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'sms_count' => 0,
            'sms_fee' => 0,
        ]);
    }

    public function test_request_cancel_creates_claim_and_updates_status()
    {
        list($vendor, $admin, $shop, $product, $order, $item) = $this->createSetup();

        $response = $this->actingAs($admin, 'admin')->post('/channel/order/cancel/request', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'reason' => 'Out of stock',
            'detail_reason' => 'Inventory count mismatch, item is unavailable.',
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '취소 요청이 접수되었습니다.',
        ]);

        $this->assertDatabaseHas('order_claims', [
            'order_id' => $order->id,
            'order_product_id' => $item->id,
            'type' => 'cancel',
            'reason' => 'Out of stock',
            'detail_reason' => 'Inventory count mismatch, item is unavailable.',
            'status' => 'requested',
        ]);

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'item_status' => '취소요청',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/channel/order/cancel/list')
            ->assertStatus(200)
            ->assertSee('P123')
            ->assertSee('취소요청');
    }

    public function test_request_return_creates_claim_and_updates_status()
    {
        list($vendor, $admin, $shop, $product, $order, $item) = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();

        $response = $this->actingAs($admin, 'admin')->post('/channel/order/return/request', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'reason' => 'Defective item',
            'detail_reason' => 'Item arrived damaged.',
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '반품 요청이 접수되었습니다.',
        ]);

        $this->assertDatabaseHas('order_claims', [
            'order_id' => $order->id,
            'order_product_id' => $item->id,
            'type' => 'return',
            'reason' => 'Defective item',
            'detail_reason' => 'Item arrived damaged.',
            'status' => 'requested',
        ]);

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'item_status' => '반품요청',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/channel/order/return/list')
            ->assertStatus(200)
            ->assertSee('P123')
            ->assertSee('반품요청');
    }

    public function test_request_exchange_creates_claim_and_updates_status()
    {
        list($vendor, $admin, $shop, $product, $order, $item) = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();

        $response = $this->actingAs($admin, 'admin')->post('/channel/order/exchange/request', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'reason' => 'Wrong size',
            'detail_reason' => 'Need size L instead of M.',
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '교환 요청이 접수되었습니다.',
        ]);

        $this->assertDatabaseHas('order_claims', [
            'order_id' => $order->id,
            'order_product_id' => $item->id,
            'type' => 'exchange',
            'reason' => 'Wrong size',
            'detail_reason' => 'Need size L instead of M.',
            'status' => 'requested',
        ]);

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'item_status' => '교환요청',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/channel/order/exchange/list')
            ->assertStatus(200)
            ->assertSee('P123')
            ->assertSee('교환요청');
    }

    public function test_order_list_uses_shop_channel_product_link_when_vendor_id_is_missing()
    {
        list($vendor, $admin, $shop, $product, $order, $item) = $this->createSetup();

        $shopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'own',
            'status' => 1,
            'constraint_type' => 'none',
            'stock' => 10,
            'product_price' => 80,
            'selling_price' => 120,
            'profit' => 40,
        ]);

        $item->vendor_id = 0;
        $item->shop_channel_id = null;
        $item->shop_channel_product_id = $shopProduct->id;
        $item->save();

        $this->actingAs($admin, 'admin')
            ->get('/channel/order/list')
            ->assertStatus(200)
            ->assertSee('P123')
            ->assertSee('Test Product');
    }

    public function test_return_claim_enforces_transitions_and_completes_after_receipt(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];

        $this->actingAs($admin, 'admin')->post('/channel/order/return/request', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'reason' => 'Defective',
            'detail_reason' => 'Defective after opening',
        ], $headers)->assertJson(['status' => true]);

        $this->post('/channel/order/claim/action', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'action' => 'return_complete',
        ], $headers)->assertStatus(422)->assertJson(['status' => false]);

        foreach (['return_receive', 'return_complete'] as $action) {
            $this->post('/channel/order/claim/action', [
                'order_id' => $order->id,
                'item_ids' => [$item->id],
                'action' => $action,
            ], $headers)->assertOk()->assertJson(['status' => true]);
        }

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'status_code' => OrderItemStatus::RETURNED,
        ]);
        $this->assertDatabaseHas('order_claims', [
            'order_product_id' => $item->id,
            'type' => 'return',
            'status' => 'completed',
        ]);
    }

    public function test_exchange_claim_creates_one_zero_value_replacement_item(): void
    {
        [, $admin, , $product, $order, $item] = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        \App\Models\ProductsAttribute::create(['product_id'=>$product->id,'size'=>'L','sku'=>'QA-exchange-L','price'=>100,'price_adjustment'=>0,'option_type'=>'general','stock'=>10,'status'=>1]);
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];

        $this->actingAs($admin, 'admin')->post('/channel/order/exchange/request', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'reason' => 'Wrong size',
            'detail_reason' => 'Need a larger size',
        ], $headers)->assertJson(['status' => true]);

        foreach (['exchange_approve', 'exchange_receive'] as $action) {
            $this->post('/channel/order/claim/action', [
                'order_id' => $order->id,
                'item_ids' => [$item->id],
                'action' => $action,
            ], $headers)->assertOk()->assertJson(['status' => true]);
        }
        $this->post('/channel/order/claim/action', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'action' => 'exchange_option',
            'option' => 'L',
        ], $headers)->assertOk()->assertJson(['status' => true]);
        $this->post('/channel/order/claim/action', [
            'order_id' => $order->id,
            'item_ids' => [$item->id],
            'action' => 'exchange_complete',
        ], $headers)->assertOk()->assertJson(['status' => true]);

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'status_code' => OrderItemStatus::EXCHANGED,
            'product_size' => 'L',
        ]);
        $this->assertDatabaseHas('orders_products', [
            'replacement_for_order_product_id' => $item->id,
            'is_exchange_replacement' => 1,
            'status_code' => OrderItemStatus::READY_TO_SHIP,
            'product_price' => 0,
            'line_total' => 0,
            'settlement_status' => 'excluded_exchange_replacement',
        ]);
        $this->assertSame(1, OrdersProduct::where('replacement_for_order_product_id', $item->id)->count());
    }

    public function test_order_actions_reject_non_ajax_requests(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();

        $this->actingAs($admin, 'admin')->post('/channel/order/status/update', [
            'order_id' => $order->id,
            'status' => 'shipping',
            'item_ids' => [$item->id],
        ])->assertStatus(422)->assertJson(['status' => false]);
    }
    public function test_pending_payment_only_and_shipping_requires_ready_state(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id]];
        $this->postJson('/channel/order/status/update',$payload+['status'=>'paid'])->assertUnprocessable();
        $this->postJson('/channel/order/status/update',$payload+['status'=>'shipping','courier_name'=>'CJ','tracking_number'=>'QA'])->assertUnprocessable();
        $item->setStatus(OrderItemStatus::AWAITING_PAYMENT);$item->save();
        $this->postJson('/channel/order/status/update',$payload+['status'=>'paid'])->assertOk()->assertJson(['status'=>true]);
        $this->assertSame(OrderItemStatus::PAID,$item->fresh()->normalized_status);
        $this->assertDatabaseHas('orders_logs',['order_id'=>$order->id,'order_item_id'=>$item->id,'order_status'=>'결제완료','actor'=>$admin->name]);
        $this->postJson('/channel/order/status/update',$payload+['status'=>'paid'])->assertUnprocessable();
    }

    public function test_cancel_request_is_not_a_refund_and_approval_reverses_only_once(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->forceFill(['line_total'=>100,'paid_line_total_snapshot'=>100,'shipping_amount_snapshot'=>0,'point_usage_snapshot'=>['channel'=>0,'me9'=>0]])->save();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id]];
        $this->postJson('/channel/order/cancel/request',$payload+['reason'=>'변심','detail_reason'=>'검수'])->assertOk()->assertJson(['status'=>true]);
        $this->assertSame(OrderItemStatus::CANCEL_REQUESTED,$item->fresh()->normalized_status);
        $this->assertNull($item->fresh()->financial_reversed_at);
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'cancel_approve'])->assertOk();
        $this->assertSame(OrderItemStatus::CANCELLED,$item->fresh()->normalized_status);
        $this->assertNotNull($item->fresh()->financial_reversed_at);
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'cancel_approve'])->assertUnprocessable();
        $this->assertSame(1,OrderClaim::where('order_product_id',$item->id)->count());
    }

    public function test_pickup_contacts_and_total_return_fee_are_persisted_without_duplicate_deductions(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->forceFill(['line_total'=>100,'paid_line_total_snapshot'=>100,'shipping_amount_snapshot'=>0,'point_usage_snapshot'=>['channel'=>0,'me9'=>0]])->save();
        $item->setStatus(OrderItemStatus::SHIPPING);$item->save();
        $second=$item->replicate();$second->save();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id,$second->id],'reason'=>'불량','detail_reason'=>'검수','pickup_method'=>'automatic','pickup_name'=>'QA','pickup_mobile'=>'01000000000','return_address'=>'QA address','claim_shipping_fee'=>150];
        $this->postJson('/channel/order/return/request',$payload)->assertOk()->assertJson(['status'=>true]);
        $this->assertSame(150,$item->fresh()->return_shipping_fee+$second->fresh()->return_shipping_fee);
        $this->assertDatabaseHas('order_claims',['order_product_id'=>$item->id,'pickup_method'=>'automatic','return_address'=>'QA address','pickup_name'=>'QA','pickup_mobile'=>'01000000000']);
        foreach ([$item,$second] as $line) {
            foreach(['return_receive','return_complete'] as $action)$this->postJson('/channel/order/claim/action',['order_id'=>$order->id,'item_ids'=>[$line->id],'action'=>$action])->assertOk();
        }
        $this->assertEquals(50,$item->fresh()->refund_cash_amount+$second->fresh()->refund_cash_amount);
    }

    public function test_invalid_pickup_fee_and_hold_reasons_do_not_mutate_order(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);$item->save();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id],'reason'=>'QA'];
        $this->postJson('/channel/order/return/request',$payload+['pickup_method'=>'automatic','claim_shipping_fee'=>1])->assertJson(['status'=>false]);
        $this->postJson('/channel/order/return/request',$payload+['claim_shipping_fee'=>101])->assertUnprocessable();
        $this->assertSame(OrderItemStatus::DELIVERED,$item->fresh()->normalized_status);
        $this->assertDatabaseCount('order_claims',0);
        $this->postJson('/channel/order/exchange/request',$payload)->assertOk();
        $this->postJson('/channel/order/claim/action',['order_id'=>$order->id,'item_ids'=>[$item->id],'action'=>'exchange_hold_before'])->assertUnprocessable();
        $this->assertSame(OrderItemStatus::EXCHANGE_REQUESTED,$item->fresh()->normalized_status);
    }

    public function test_withdrawal_restores_delivery_status_and_invoice_edit_keeps_claim_status(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);$item->save();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id]];
        $this->postJson('/channel/order/return/request',$payload+['reason'=>'QA'])->assertOk();
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'return_invoice','courier_name'=>'CJ','tracking_number'=>'QA-153'])->assertOk();
        $this->assertSame(OrderItemStatus::RETURN_REQUESTED,$item->fresh()->normalized_status);
        $this->assertDatabaseHas('order_claims',['order_product_id'=>$item->id,'customer_courier_name'=>'CJ','customer_tracking_number'=>'QA-153','status'=>'requested']);
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'return_withdraw'])->assertOk();
        $this->assertSame(OrderItemStatus::DELIVERED,$item->fresh()->normalized_status);
    }

    public function test_claim_item_cannot_be_submitted_under_another_order(): void
    {
        [, $admin, , , $order, $item] = $this->createSetup();
        $other=$order->replicate();$other->save();
        $item->setStatus(OrderItemStatus::CANCEL_REQUESTED);$item->save();
        OrderClaim::create(['order_id'=>$order->id,'order_product_id'=>$item->id,'vendor_id'=>$item->vendor_id,'user_id'=>$item->user_id,'type'=>'cancel','reason'=>'QA','status'=>'requested']);
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
            ->postJson('/channel/order/claim/action',['order_id'=>$other->id,'item_ids'=>[$item->id],'action'=>'cancel_approve'])->assertUnprocessable();
        $this->assertSame(OrderItemStatus::CANCEL_REQUESTED,$item->fresh()->normalized_status);
    }
    public function test_rejected_cancel_stays_in_claim_list_and_searches_by_claim_number(): void
    {
        [, $admin, , , $order, $item]=$this->createSetup();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id]];
        $this->postJson('/channel/order/cancel/request',$payload+['reason'=>'QA','detail_reason'=>'QA'])->assertOk();
        $claim=OrderClaim::sole();
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'cancel_reject'])->assertOk();
        $number='Me9-Cancel-'.str_pad($claim->id,8,'0',STR_PAD_LEFT);
        $this->get('/channel/order/cancel/list?search_type=order_no&keyword='.$number)->assertOk()->assertSee($number)->assertSee('거절')->assertViewHas('orders',fn($orders)=>$orders->total()===1);
        $this->assertNotEmpty($claim->fresh()->lifecycle_dates['rejected']);
        $this->assertNull($claim->fresh()->lifecycle_dates['completed']);
        $this->assertSame(OrderItemStatus::PAID,$item->fresh()->normalized_status);
    }

    public function test_status_filtered_list_keeps_other_owned_items_in_order_detail(): void
    {
        [, $admin, , , $order, $item]=$this->createSetup();
        $second=$item->replicate();$second->product_code='OTHER-LINE';$second->setStatus(OrderItemStatus::SHIPPING);$second->save();
        $this->actingAs($admin,'admin')->get('/channel/order/list?order_statuses[]=paid')->assertOk()
            ->assertViewHas('orders',fn($orders)=>$orders->first()->items->count()===2 && $orders->first()->list_items->count()===1);
    }

    public function test_joint_campaign_on_same_product_does_not_change_normal_order_type(): void
    {
        [, $admin, , $product, $order, $item]=$this->createSetup();
        $campaign=\Illuminate\Support\Facades\DB::table('joint_purchases')->insertGetId(['product_id'=>$product->id,'min_quantity'=>10,'discount_price'=>90,'start_date'=>now()->toDateString(),'end_date'=>now()->addWeek()->toDateString(),'status'=>1,'created_at'=>now(),'updated_at'=>now()]);
        $this->actingAs($admin,'admin')->get('/channel/order/list?order_type=normal')->assertOk()->assertViewHas('orders',fn($orders)=>$orders->total()===1 && $orders->first()->items->first()['order_type']==='normal');
        $this->get('/channel/order/list?order_type=joint')->assertOk()->assertViewHas('orders',fn($orders)=>$orders->total()===0);
        $item->joint_purchase_id=$campaign;$item->save();
        $this->get('/channel/order/list?order_type=joint')->assertOk()->assertViewHas('orders',fn($orders)=>$orders->total()===1);
    }

    public function test_unpaid_cancellation_does_not_refund_uncollected_cash(): void
    {
        [, $admin, , , $order, $item]=$this->createSetup();
        $item->forceFill(['line_total'=>100,'paid_line_total_snapshot'=>100,'shipping_amount_snapshot'=>0,'point_usage_snapshot'=>['channel'=>0,'me9'=>0]])->save();
        $item->setStatus(OrderItemStatus::AWAITING_PAYMENT);$item->save();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id]];
        $this->postJson('/channel/order/cancel/request',$payload+['reason'=>'QA','detail_reason'=>'QA'])->assertOk();
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'cancel_approve'])->assertOk();
        $this->assertEquals(0,$item->fresh()->refund_cash_amount);
        $this->assertSame('not_required',$item->fresh()->refund_status);
    }

    public function test_exchange_replacement_keeps_recipient_and_no_second_payment(): void
    {
        [, $admin, , $product, $order, $item]=$this->createSetup();
        \App\Models\ProductsAttribute::create(['product_id'=>$product->id,'size'=>'L','sku'=>'QA-NEW-L','price'=>100,'price_adjustment'=>0,'option_type'=>'general','stock'=>10,'status'=>1]);
        $item->setStatus(OrderItemStatus::SHIPPING);$item->save();
        $this->actingAs($admin,'admin')->withHeaders(['X-Requested-With'=>'XMLHttpRequest']);
        $payload=['order_id'=>$order->id,'item_ids'=>[$item->id]];
        $this->postJson('/channel/order/exchange/request',$payload+['reason'=>'QA','exchange_options'=>[$item->id=>'L'],'exchange_recipient_name'=>'QA receiver','exchange_recipient_mobile'=>'01000000000','exchange_recipient_address'=>'QA replacement address'])->assertOk();
        foreach(['exchange_approve','exchange_receive','exchange_complete'] as $action)$this->postJson('/channel/order/claim/action',$payload+['action'=>$action])->assertOk();
        $replacement=$item->fresh()->exchangeReplacement;
        $this->assertNotNull($replacement);
        $this->assertSame('QA replacement address',$replacement->recipient_address);
        $this->assertSame('L',$replacement->product_size);
        $this->assertEquals(0,$replacement->paid_line_total_snapshot);
        $this->assertEquals(0,$replacement->shipping_amount_snapshot);
        $this->assertSame('M',OrderClaim::sole()->original_option);
        $this->postJson('/channel/order/claim/action',$payload+['action'=>'exchange_complete'])->assertUnprocessable();
        $this->assertSame(1,OrdersProduct::where('replacement_for_order_product_id',$item->id)->count());
    }
}
