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
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action])->assertUnprocessable();
        }
        foreach (['return_receive', 'return_hold'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action])->assertOk();
        }
        $this->postJson('/channel/order/claim/action', $payload + ['action' => 'return_complete'])->assertUnprocessable();
        foreach (['return_release', 'return_complete'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action])->assertOk();
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
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action])->assertUnprocessable();
        }
        $this->postJson('/channel/order/claim/action', $payload + ['action' => 'exchange_option', 'option' => 'L'])->assertUnprocessable();
        foreach (['exchange_hold_before', 'exchange_release_before', 'exchange_approve', 'exchange_receive', 'exchange_hold_after', 'exchange_release_after', 'exchange_complete'] as $action) {
            $this->postJson('/channel/order/claim/action', $payload + ['action' => $action])->assertOk();
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
            'message' => '취소 처리가 완료되었습니다.',
        ]);

        $this->assertDatabaseHas('order_claims', [
            'order_id' => $order->id,
            'order_product_id' => $item->id,
            'type' => 'cancel',
            'reason' => 'Out of stock',
            'detail_reason' => 'Inventory count mismatch, item is unavailable.',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'item_status' => '취소완료',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/channel/order/cancel/list')
            ->assertStatus(200)
            ->assertSee('P123')
            ->assertSee('취소완료');
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
        [, $admin, , , $order, $item] = $this->createSetup();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
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
}
