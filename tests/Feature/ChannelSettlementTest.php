<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\SettlementRun;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\Vendor;
use App\Services\SettlementCalculator;
use App\Support\OrderItemStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChannelSettlementTest extends TestCase
{
    use RefreshDatabase;

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
        $shop->settlement_rate = 12.5; // 12.5%
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

        return [$vendor, $admin, $shop, $product];
    }

    public function test_settlement_index_displays_correct_data()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();

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
        $order->grand_total = 200.0;
        $order->save();

        // Item 1: 구매확정 (Purchased Confirm)
        $item1 = new OrdersProduct;
        $item1->order_id = $order->id;
        $item1->user_id = 1;
        $item1->vendor_id = $vendor->id;
        $item1->admin_id = $admin->id;
        $item1->product_id = $product->id;
        $item1->product_code = $product->product_code;
        $item1->product_name = $product->product_name;
        $item1->product_color = $product->product_color;
        $item1->product_size = 'M';
        $item1->product_price = 100.0;
        $item1->product_qty = 2;
        $item1->item_status = '구매확정';
        $item1->updated_at = Carbon::parse('2026-05-15 12:00:00');
        $item1->created_at = Carbon::parse('2026-05-15 12:00:00');
        $item1->save();

        // Item 2: Not 구매확정 (should be excluded)
        $item2 = new OrdersProduct;
        $item2->order_id = $order->id;
        $item2->user_id = 1;
        $item2->vendor_id = $vendor->id;
        $item2->admin_id = $admin->id;
        $item2->product_id = $product->id;
        $item2->product_code = $product->product_code;
        $item2->product_name = $product->product_name;
        $item2->product_color = $product->product_color;
        $item2->product_size = 'L';
        $item2->product_price = 150.0;
        $item2->product_qty = 1;
        $item2->item_status = '배송중';
        $item2->updated_at = Carbon::parse('2026-05-15 12:00:00');
        $item2->created_at = Carbon::parse('2026-05-15 12:00:00');
        $item2->save();

        $response = $this->actingAs($admin, 'admin')->get('/channel/settlement/list');

        $response->assertStatus(200);
        $response->assertViewHas('settlements');
        $response->assertViewHas('rate', 12.5);

        $settlements = $response->viewData('settlements');
        $this->assertCount(1, $settlements);

        $settlement = $settlements[0];
        $this->assertEquals('2026-05', $settlement->settlement_period);
        $this->assertEquals(200.0, $settlement->total_sales);
        $this->assertEquals(1, $settlement->order_count);
        $this->assertEquals(30.0, $settlement->commission);
        $this->assertEquals(170.0, $settlement->settlement_amount);
        $this->assertEquals(12.5, $settlement->rate);
        $this->assertEquals('구매확정 기준', $settlement->status);
    }

    public function test_settlement_view_displays_detailed_orders()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();

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
        $order->grand_total = 200.0;
        $order->save();

        // Item 1: 구매확정 in May 2026
        $item1 = new OrdersProduct;
        $item1->order_id = $order->id;
        $item1->user_id = 1;
        $item1->vendor_id = $vendor->id;
        $item1->admin_id = $admin->id;
        $item1->product_id = $product->id;
        $item1->product_code = $product->product_code;
        $item1->product_name = $product->product_name;
        $item1->product_color = $product->product_color;
        $item1->product_size = 'M';
        $item1->product_price = 100.0;
        $item1->product_qty = 2;
        $item1->item_status = '구매확정';
        $item1->updated_at = Carbon::parse('2026-05-15 12:00:00');
        $item1->created_at = Carbon::parse('2026-05-15 12:00:00');
        $item1->save();

        // Item 2: Not 구매확정 (should be excluded)
        $item2 = new OrdersProduct;
        $item2->order_id = $order->id;
        $item2->user_id = 1;
        $item2->vendor_id = $vendor->id;
        $item2->admin_id = $admin->id;
        $item2->product_id = $product->id;
        $item2->product_code = $product->product_code;
        $item2->product_name = $product->product_name;
        $item2->product_color = $product->product_color;
        $item2->product_size = 'L';
        $item2->product_price = 150.0;
        $item2->product_qty = 1;
        $item2->item_status = '배송중';
        $item2->updated_at = Carbon::parse('2026-05-15 12:00:00');
        $item2->created_at = Carbon::parse('2026-05-15 12:00:00');
        $item2->save();

        // Item 3: 구매확정 but in June 2026 (should be excluded when viewing May)
        $item3 = new OrdersProduct;
        $item3->order_id = $order->id;
        $item3->user_id = 1;
        $item3->vendor_id = $vendor->id;
        $item3->admin_id = $admin->id;
        $item3->product_id = $product->id;
        $item3->product_code = $product->product_code;
        $item3->product_name = $product->product_name;
        $item3->product_color = $product->product_color;
        $item3->product_size = 'S';
        $item3->product_price = 50.0;
        $item3->product_qty = 1;
        $item3->item_status = '구매확정';
        $item3->updated_at = Carbon::parse('2026-06-01 12:00:00');
        $item3->created_at = Carbon::parse('2026-06-01 12:00:00');
        $item3->save();

        $response = $this->actingAs($admin, 'admin')->get('/channel/settlement/view/2026-05');

        $response->assertStatus(200);
        $response->assertViewHas('orders');
        $response->assertViewHas('period', '2026-05');
        $response->assertViewHas('rate', 12.5);

        $orders = $response->viewData('orders');
        $this->assertCount(1, $orders);
        $this->assertEquals($item1->id, $orders[0]->id);

        // Check loaded relations
        $this->assertTrue($orders[0]->relationLoaded('product'));
        $this->assertTrue($orders[0]->relationLoaded('order'));
        $this->assertEquals('Test Product', $orders[0]->product->product_name);
        $this->assertEquals($order->id, $orders[0]->order->id);
    }

    public function test_settlement_calculator_allocates_shipping_points_and_commission()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $product->update(['reward_points' => 100]);

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
        $order->shipping_charges = 3000;
        $order->coupon_amount = 2000;
        $order->used_point = 1000;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 22000;
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
        $item->product_price = 10000;
        $item->selling_price = 10000;
        $item->supply_price = 7000;
        $item->product_qty = 2;
        $item->line_total = 20000;
        $item->status_code = OrderItemStatus::CONFIRMED;
        $item->item_status = OrderItemStatus::label(OrderItemStatus::CONFIRMED);
        $item->confirmed_at = Carbon::parse('2026-05-15 12:00:00');
        $item->save();

        $summary = app(SettlementCalculator::class)->preview('2026-05', $vendor->id)->first();

        $this->assertNotNull($summary);
        $this->assertEquals(21000.0, $summary['gross_sales_amount']);
        $this->assertEquals(14000.0, $summary['supply_amount']);
        $this->assertEquals(2890.0, $summary['admin_amount']);
        $this->assertEquals(200.0, $summary['point_deposit_amount']);
        $this->assertEquals(1000.0, $summary['point_used_amount']);
        $this->assertEquals(17910.0, $summary['settlement_amount']);
    }

    public function test_admin_settlement_export_uses_storyboard_detail_columns()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $product->update(['reward_points' => 100]);

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
        $order->shipping_charges = 3000;
        $order->coupon_amount = 2000;
        $order->used_point = 1000;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 22000;
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
        $item->product_price = 10000;
        $item->selling_price = 10000;
        $item->supply_price = 7000;
        $item->product_qty = 2;
        $item->line_total = 20000;
        $item->status_code = OrderItemStatus::CONFIRMED;
        $item->item_status = OrderItemStatus::label(OrderItemStatus::CONFIRMED);
        $item->confirmed_at = Carbon::parse('2026-05-15 12:00:00');
        $item->save();

        $run = app(SettlementCalculator::class)->generate('2026-05', $admin->id)->first();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.export', $run->id));
        $response->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('등록일,주문번호,주문유형,상품유형,구분,PG결제,포인트결제,상품가,배송비,할인금액,상품+수수료,배송비', $csv);
        $this->assertStringContainsString('"SMS수수료 건수","SMS수수료 비용",제공포인트,정산비용', $csv);
        $this->assertStringContainsString('"2026-05-15 12:00",Me9-', $csv);
        $this->assertStringContainsString('일반,자사,상품판매,20000,1000,20000,3000,2000,2890,0,0,0,200,17910', $csv);
    }

    public function test_extra_shipping_export_only_includes_common_pg_items_with_extra_shipping()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();

        $ownPgShop = ShopChannel::create([
            'vendor_id' => $vendor->id,
            'channel_code' => 'ownpg',
            'channel_name' => 'Own PG Channel',
            'copyright' => 'Test Copyright',
            'keywords' => '[]',
            'settlement_type' => 1,
            'settlement_rate' => 12.5,
            'use_own_pg' => true,
            'pg_provider' => 'kcp',
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
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 40000;
        $order->save();

        $commonItem = OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => 'Common PG Extra Shipping',
            'product_color' => $product->product_color,
            'product_size' => 'M',
            'product_price' => 10000,
            'selling_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'return_shipping_fee' => 3000,
            'exchange_shipping_fee' => 2500,
            'extra_shipping_fee' => 500,
            'confirmed_at' => Carbon::parse('2026-05-15 12:00:00'),
        ]);

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => 'Common PG No Extra Shipping',
            'product_color' => $product->product_color,
            'product_size' => 'L',
            'product_price' => 10000,
            'selling_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'confirmed_at' => Carbon::parse('2026-05-15 12:00:00'),
        ]);

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $ownPgShop->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => 'Own PG Extra Shipping',
            'product_color' => $product->product_color,
            'product_size' => 'S',
            'product_price' => 10000,
            'selling_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'return_shipping_fee' => 3000,
            'confirmed_at' => Carbon::parse('2026-05-15 12:00:00'),
        ]);

        $runs = app(SettlementCalculator::class)->generate('2026-05', $admin->id);
        $commonRun = $runs->firstWhere('payment_gateway_type', 'me9_pg');
        $ownPgRun = $runs->firstWhere('payment_gateway_type', 'own_pg');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.extra_shipping.export', $commonRun->id));
        $response->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('주문번호,상품명,상태,PG구분,반품배송비,교환배송비,기타추가배송비,"추가배송비 합계",택배사,송장번호,처리일', $csv);
        $this->assertStringContainsString('"'.$commonItem->product_name.'",구매확정,공용PG,3000,2500,500,6000', $csv);
        $this->assertStringNotContainsString('Common PG No Extra Shipping', $csv);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.extra_shipping.export', $ownPgRun->id));
        $response->assertOk();
        $this->assertStringNotContainsString('Own PG Extra Shipping', $response->streamedContent());
    }

    public function test_own_pg_settlement_tracks_sales_without_me9_payout()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $shop->update(['use_own_pg' => true, 'pg_provider' => 'kcp']);

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
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Own PG';
        $order->grand_total = 200;
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
        $item->product_price = 100;
        $item->selling_price = 100;
        $item->product_qty = 2;
        $item->line_total = 200;
        $item->status_code = OrderItemStatus::CONFIRMED;
        $item->item_status = OrderItemStatus::label(OrderItemStatus::CONFIRMED);
        $item->confirmed_at = Carbon::parse('2026-05-15 12:00:00');
        $item->save();

        $calculator = app(SettlementCalculator::class);
        $summary = $calculator->preview('2026-05', $vendor->id)->first();

        $this->assertNotNull($summary);
        $this->assertEquals('own_pg', $summary['payment_gateway_type']);
        $this->assertEquals(200.0, $summary['invoice_sales_amount']);
        $this->assertEquals(0.0, $summary['payout_amount']);
        $this->assertEquals(0.0, $summary['settlement_amount']);
        $this->assertEquals(0.0, $summary['admin_amount']);

        $runs = $calculator->generate('2026-05', $admin->id);
        $run = $runs->first();

        $this->assertEquals('own_pg', $run->payment_gateway_type);
        $this->assertEquals(0.0, (float) $run->payout_amount);
        $this->assertEquals('own_pg', $run->items->first()->payment_gateway_type);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.payout.export', $run->id));
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('"자사PG 결제액","공용PG 결제액",자사포인트(별도기록없음),Me9포인트(통합사용액),상품가,배송비,할인금액,매출액,"채널 지급액"', $csv);
        $this->assertStringContainsString('testcode,Me9-', $csv);
        $this->assertStringContainsString('자사PG,200,0,0,0,200,0,0,200,0', $csv);
    }

    public function test_own_pg_point_payment_creates_me9_payout_after_sms_fee()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $shop->update(['use_own_pg' => true, 'pg_provider' => 'kcp']);

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
        $order->shipping_charges = 2500;
        $order->used_point = 3000;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Own PG';
        $order->grand_total = 12500;
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
        $item->product_price = 10000;
        $item->selling_price = 10000;
        $item->product_qty = 1;
        $item->line_total = 10000;
        $item->sms_count = 2;
        $item->sms_fee = 100;
        $item->status_code = OrderItemStatus::CONFIRMED;
        $item->item_status = OrderItemStatus::label(OrderItemStatus::CONFIRMED);
        $item->confirmed_at = Carbon::parse('2026-05-15 12:00:00');
        $item->save();

        $calculator = app(SettlementCalculator::class);
        $summary = $calculator->preview('2026-05', $vendor->id)->first();

        $this->assertNotNull($summary);
        $this->assertEquals('own_pg', $summary['payment_gateway_type']);
        $this->assertEquals(12500.0, $summary['invoice_sales_amount']);
        $this->assertEquals(0.0, $summary['invoice_purchase_amount']);
        $this->assertEquals(3000.0, $summary['point_used_amount']);
        $this->assertEquals(100.0, $summary['sms_postpaid_amount']);
        $this->assertEquals(2900.0, $summary['payout_amount']);
        $this->assertEquals(2900.0, $summary['settlement_amount']);
        $this->assertEquals(0.0, $summary['admin_amount']);

        $run = $calculator->generate('2026-05', $admin->id)->first();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.export', $run->id));
        $response->assertOk();
        $this->assertStringContainsString('일반,자사,상품판매,9500,3000,10000,2500,0,0,0,2,100,0,2900', $response->streamedContent());

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.payout.export', $run->id));
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('testcode,Me9-', $csv);
        $this->assertStringContainsString('자사PG,9500,0,0,3000,10000,2500,0,12500,2900', $csv);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settlements.billing.export', $run->id));
        $response->assertOk();
        $this->assertStringContainsString('자사PG,0,0,100,0,0', $response->streamedContent());
    }

    public function test_common_pg_own_product_settlement_deducts_reward_points_and_sms_fee()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $shop->update(['settlement_rate' => 5, 'use_own_pg' => false]);
        $product->update(['product_price' => 10000, 'reward_points' => 500]);

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
        $order->shipping_charges = 2500;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 12500;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => 'M',
            'product_price' => 10000,
            'selling_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'sms_count' => 1,
            'sms_fee' => 100,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'confirmed_at' => Carbon::parse('2026-05-15 12:00:00'),
        ]);

        $summary = app(SettlementCalculator::class)->preview('2026-05', $vendor->id)->first();

        $this->assertNotNull($summary);
        $this->assertEquals('me9_pg', $summary['payment_gateway_type']);
        $this->assertEquals(12500.0, $summary['invoice_sales_amount']);
        $this->assertEquals(690.0, $summary['invoice_purchase_amount']);
        $this->assertEquals(500.0, $summary['point_deposit_amount']);
        $this->assertEquals(100.0, $summary['sms_postpaid_amount']);
        $this->assertEquals(11210.0, $summary['settlement_amount']);
        $this->assertEquals(11210.0, $summary['payout_amount']);
    }

    public function test_shared_free_price_reseller_settlement_deducts_sms_fee()
    {
        [$resellerVendor, $resellerAdmin, $shop] = $this->createSetup();

        $supplierVendor = Vendor::create([
            'name' => 'Supplier Vendor',
            'mobile' => '010-9999-8888',
            'email' => 'supplier@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);

        $product = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => $supplierVendor->id,
            'admin_id' => 999,
            'admin_type' => 'vendor',
            'product_name' => 'Shared Product',
            'product_code' => 'SHARED123',
            'product_color' => 'Black',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 500,
            'status' => 1,
        ]);

        $shop->update(['settlement_rate' => 10, 'use_own_pg' => false]);

        $shopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'public',
            'approval_status' => 'approved',
            'status' => 1,
            'constraint_type' => 'none',
            'stock' => 10,
            'product_price' => 10000,
            'selling_price' => 13000,
            'profit' => 1190,
            'settlement_type_snapshot' => 1,
            'settlement_rate_snapshot' => 10,
            'minimum_selling_price' => 11550,
            'maximum_reward_points' => 1190,
            'price_decider' => 'reseller',
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
        $order->shipping_charges = 2500;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 15500;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $resellerVendor->id,
            'shop_channel_id' => $shop->id,
            'shop_channel_product_id' => $shopProduct->id,
            'admin_id' => $resellerAdmin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => 'M',
            'product_price' => 13000,
            'supply_price' => 10000,
            'selling_price' => 13000,
            'product_qty' => 1,
            'line_total' => 13000,
            'sms_count' => 1,
            'sms_fee' => 100,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'confirmed_at' => Carbon::parse('2026-05-15 12:00:00'),
        ]);

        $rows = app(SettlementCalculator::class)->items('2026-05')->keyBy('settlement_role');

        $this->assertEquals(12500.0, $rows['shared_free_supplier']['settlement_amount']);
        $this->assertEquals(15500.0, $rows['shared_free_reseller']['invoice_sales_amount']);
        $this->assertEquals(14210.0, $rows['shared_free_reseller']['invoice_purchase_amount']);
        $this->assertEquals(100.0, $rows['shared_free_reseller']['sms_postpaid_amount']);
        $this->assertEquals(1190.0, $rows['shared_free_reseller']['settlement_amount']);
        $this->assertEquals(1190.0, $rows['shared_free_reseller']['payout_amount']);
    }

    public function test_own_pg_product_with_reward_points_is_settled_as_common_pg()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $shop->update(['settlement_rate' => 5, 'use_own_pg' => true, 'pg_provider' => 'kcp']);
        $product->update(['product_price' => 10000, 'reward_points' => 500]);

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
        $order->shipping_charges = 2500;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 12500;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => 'M',
            'product_price' => 10000,
            'selling_price' => 10000,
            'product_qty' => 1,
            'line_total' => 10000,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'confirmed_at' => Carbon::parse('2026-05-15 12:00:00'),
        ]);

        $summary = app(SettlementCalculator::class)->preview('2026-05', $vendor->id)->first();

        $this->assertNotNull($summary);
        $this->assertEquals('me9_pg', $summary['payment_gateway_type']);
        $this->assertEquals(12500.0, $summary['invoice_sales_amount']);
        $this->assertEquals(690.0, $summary['invoice_purchase_amount']);
        $this->assertEquals(500.0, $summary['point_deposit_amount']);
        $this->assertEquals(11310.0, $summary['settlement_amount']);
    }

    public function test_joint_purchase_settlement_belongs_to_month_after_end_date_plus_seven_days()
    {
        [$vendor, $admin, $shop, $product] = $this->createSetup();
        $shop->update(['settlement_rate' => 5]);
        $product->update(['product_price' => 10000]);

        $jointPurchaseId = DB::table('joint_purchases')->insertGetId([
            'product_id' => $product->id,
            'min_quantity' => 1,
            'current_quantity' => 1,
            'discount_price' => 8000,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
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
        $order->shipping_charges = 2500;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 10500;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'joint_purchase_id' => $jointPurchaseId,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => 'M',
            'product_price' => 8000,
            'selling_price' => 8000,
            'product_qty' => 1,
            'line_total' => 8000,
            'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'confirmed_at' => Carbon::parse('2026-05-20 12:00:00'),
        ]);

        $calculator = app(SettlementCalculator::class);

        $this->assertNull($calculator->preview('2026-05', $vendor->id)->first());

        $juneSummary = $calculator->preview('2026-06', $vendor->id)->first();
        $this->assertNotNull($juneSummary);
        $this->assertEquals('2026-06', $juneSummary['period']);
        $this->assertEquals(10500.0, $juneSummary['invoice_sales_amount']);
    }

    public function test_vendor_cannot_export_another_vendors_settlement(): void
    {
        [, $admin] = $this->createSetup();
        $otherVendor = Vendor::create([
            'name' => 'Other Settlement Vendor',
            'mobile' => '010-5555-6666',
            'email' => 'other-settlement@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);
        $run = SettlementRun::create([
            'settlement_key' => '2026-05:other-vendor',
            'period' => '2026-05',
            'vendor_id' => $otherVendor->id,
            'vendor_name' => $otherVendor->name,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settlements.export', $run->id))
            ->assertNotFound();
    }
    private function reviewLine($admin,$shop,$product,array $policyChanges=[],array $changes=[]): OrdersProduct
    {
        $order=Order::forceCreate(['user_id'=>0,'name'=>'QA buyer','address'=>'QA address','city'=>'Seoul','state'=>'Seoul','country'=>'KR','pincode'=>'00000','mobile'=>'01000000000','email'=>'qa168@example.test','shipping_charges'=>2500,'order_status'=>'New','payment_method'=>'QA mock','payment_gateway'=>'Me9 Mock Payment','grand_total'=>12500]);
        $policy=['product_type'=>'own','supplier_vendor_id'=>$product->vendor_id,'supplier_vendor_name'=>'QA','is_fixed_shared'=>false,'reward_points'=>0,'payment_gateway_type'=>'me9_pg','settlement_type'=>1,'settlement_rate'=>5,'profit_share_type'=>'fixed','profit_share_value'=>0];
        return OrdersProduct::forceCreate(array_merge(['order_id'=>$order->id,'user_id'=>0,'vendor_id'=>$admin->vendor_id,'admin_id'=>$admin->id,'shop_channel_id'=>$shop->id,'product_id'=>$product->id,'product_code'=>'QA168','product_name'=>'QA settlement','product_color'=>'Default','product_size'=>'M','product_price'=>10000,'selling_price'=>10000,'supply_price'=>10000,'product_qty'=>1,'line_total'=>10000,'paid_line_total_snapshot'=>10000,'shipping_amount_snapshot'=>2500,'used_point_amount'=>0,'point_usage_snapshot'=>['channel'=>0,'me9'=>0],'status_code'=>'confirmed','item_status'=>'구매확정','confirmed_at'=>now(),'settlement_policy_snapshot'=>array_merge($policy,$policyChanges)],$changes));
    }

    public function test_own_and_common_pg_are_separate_and_generation_does_not_overwrite_either(): void
    {
        [$vendor,$admin,$shop,$product]=$this->createSetup();
        $this->reviewLine($admin,$shop,$product);
        $this->reviewLine($admin,$shop,$product,['payment_gateway_type'=>'own_pg']);
        $calculator=app(SettlementCalculator::class);$period=now()->format('Y-m');
        $rows=$calculator->preview($period,$vendor->id);
        $this->assertCount(2,$rows);$this->assertCount(2,$rows->pluck('settlement_key')->unique());
        foreach([1,2]as$i)$calculator->generate($period,$admin->id,$vendor->id,$shop->id);
        $this->assertDatabaseCount('settlement_runs',2);$this->assertDatabaseCount('settlement_items',2);
    }

    public function test_extra_shipping_includes_completed_return_and_excludes_own_pg_and_other_vendors(): void
    {
        [$vendor,$admin,$shop,$product]=$this->createSetup();
        $common=$this->reviewLine($admin,$shop,$product,[],['status_code'=>'returned','item_status'=>'반품완료','return_shipping_fee'=>2500]);
        $own=$this->reviewLine($admin,$shop,$product,['payment_gateway_type'=>'own_pg'],['status_code'=>'returned','item_status'=>'반품완료','return_shipping_fee'=>3000]);
        foreach([$common,$own]as$item)\App\Models\OrderClaim::create(['order_id'=>$item->order_id,'order_product_id'=>$item->id,'vendor_id'=>$vendor->id,'user_id'=>0,'type'=>'return','reason'=>'QA','status'=>'completed']);
        $rows=app(\App\Services\SettlementReport::class)->shipping(now()->format('Y-m'),$vendor->id);
        $this->assertCount(1,$rows);$this->assertEquals(2500,$rows->first()[8]);
        $this->assertCount(0,app(\App\Services\SettlementReport::class)->shipping(now()->format('Y-m'),$vendor->id+999));
    }

    public function test_xlsx_is_readable_and_channel_identifiers_are_not_spreadsheet_formulas(): void
    {
        [, $admin,$shop,$product]=$this->createSetup();$shop->channel_code='=1+1';$shop->save();
        $this->reviewLine($admin,$shop,$product);
        $response=$this->actingAs($admin,'admin')->get('/channel/settlement/export?period='.now()->format('Y-m'));
        $response->assertOk()->assertHeader('Content-Type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path=tempnam(sys_get_temp_dir(),'qa168');file_put_contents($path,$response->streamedContent());
        try{$sheet=\PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();$this->assertSame('=1+1',$sheet->getCell('B2')->getValue());$this->assertSame('s',$sheet->getCell('B2')->getDataType());$this->assertEquals(11810,$sheet->getCell('Q2')->getValue());}finally{unlink($path);}
    }

    public function test_execution_targets_channel_and_settlement_month_and_attachment_is_private(): void
    {
        [$vendor,$admin,$shop,$product]=$this->createSetup();
        \Illuminate\Support\Facades\Storage::fake('local');\Illuminate\Support\Facades\Storage::fake('public');
        $admin->type='superadmin';$admin->save();$period=now()->format('Y-m');$title='QA168 execution record';
        $this->actingAs($admin,'admin')->post('/admin/settlements/executions',['period'=>$period,'shop_channel_id'=>$shop->id,'title'=>$title,'amount'=>1000,'executed_at'=>now()->addMonth()->toDateString(),'attachment'=>\Illuminate\Http\UploadedFile::fake()->create('proof.pdf',1,'application/pdf')])->assertRedirect();
        $execution=\App\Models\SettlementExecution::sole();$this->assertSame($vendor->id,(int)$execution->vendor_id);$this->assertSame('local',$execution->attachment_disk);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($execution->attachment_path);\Illuminate\Support\Facades\Storage::disk('public')->assertMissing($execution->attachment_path);
        $admin->type='vendor';$admin->save();$this->actingAs($admin,'admin')->get('/channel/settlement/list?period='.$period)->assertOk()->assertSee($title);
        $this->get('/channel/settlement/executions/'.$execution->id.'/download')->assertOk();
        $other=$admin->replicate();$other->email='other168@example.test';$other->vendor_id=$vendor->id+999;$other->save();
        $this->actingAs($other,'admin')->get('/channel/settlement/executions/'.$execution->id.'/download')->assertNotFound();
    }

    public function test_execution_rejects_run_from_another_settlement_month(): void
    {
        [$vendor,$admin,$shop,$product]=$this->createSetup();$this->reviewLine($admin,$shop,$product);
        $run=app(SettlementCalculator::class)->generate(now()->format('Y-m'),$admin->id)->first();$admin->type='superadmin';$admin->save();
        $this->actingAs($admin,'admin')->postJson('/admin/settlements/executions',['period'=>now()->startOfMonth()->subMonth()->format('Y-m'),'shop_channel_id'=>$shop->id,'settlement_run_id'=>$run->id,'title'=>'QA','amount'=>1000])->assertUnprocessable();
        $this->assertDatabaseCount('settlement_executions',0);
    }

    public function test_amount_report_preserves_point_wallet_split_and_own_pg_sign(): void
    {
        [$vendor,$admin,$shop,$product]=$this->createSetup();$this->reviewLine($admin,$shop,$product,['payment_gateway_type'=>'own_pg'],['used_point_amount'=>3000,'point_usage_snapshot'=>['channel'=>1000,'me9'=>2000],'sms_count'=>2,'sms_fee'=>100]);
        $rows=app(SettlementCalculator::class)->items(now()->format('Y-m'),$vendor->id);$report=app(\App\Services\SettlementReport::class);
        [, $payout]=$report->amountRows($rows,'payout');[, $billing]=$report->amountRows($rows,'billing');
        $this->assertEquals(1000,$payout->first()[5]);$this->assertEquals(2000,$payout->first()[6]);$this->assertEquals(-3000,$payout->first()[10]);$this->assertEquals(100,$billing->first()[7]);
    }
    public function test_admin_scoped_summary_and_workbook_use_the_selected_recipient_vendor(): void
    {
        [$vendor,$admin,$shop,$product]=$this->createSetup();$supplier=$vendor->replicate();$supplier->email='supplier168@example.test';$supplier->name='QA supplier168';$supplier->save();
        $this->reviewLine($admin,$shop,$product,['product_type'=>'public','is_fixed_shared'=>true,'supplier_vendor_id'=>$supplier->id,'profit_share_type'=>'fixed','profit_share_value'=>1500]);
        $admin->type='superadmin';$admin->save();$period=now()->format('Y-m');$query='?period='.$period.'&shop_channel_id='.$shop->id;
        $this->actingAs($admin,'admin')->get('/admin/settlements'.$query)->assertOk()->assertSee('정산 집행 등록')->assertViewHas('rows',fn($rows)=>$rows->count()===2);
        $this->post('/admin/settlements/generate',['period'=>$period,'shop_channel_id'=>$shop->id])->assertRedirect(route('admin.settlements.index',['period'=>$period,'shop_channel_id'=>$shop->id]));
        $this->assertDatabaseCount('settlement_runs',2);
        $this->get('/admin/settlements'.$query)->assertOk()->assertViewHas('rows',fn($rows)=>$rows->count()===2);
        $response=$this->get('/admin/settlements/workbook/'.$period.'?shop_channel_id='.$shop->id.'&vendor_id='.$supplier->id.'&kind=orders')->assertOk();
        $path=tempnam(sys_get_temp_dir(),'qa174');file_put_contents($path,$response->streamedContent());
        try{$sheet=\PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();$this->assertEquals(10310,$sheet->getCell('Q2')->getValue());}finally{unlink($path);}
        $this->get('/admin/settlements/workbook/'.$period.'?shop_channel_id='.$shop->id.'&vendor_id='.$supplier->id.'&kind=billing')->assertOk()->assertSee('채널 청구액 상세 목록');
        $this->post('/admin/settlements/executions',['period'=>$period,'shop_channel_id'=>$shop->id,'recipient_vendor_id'=>$supplier->id,'title'=>'Supplier execution','amount'=>10310])->assertRedirect();
        $this->assertDatabaseHas('settlement_executions',['shop_channel_id'=>$shop->id,'vendor_id'=>$supplier->id,'amount'=>10310]);
        $unrelated=$vendor->replicate();$unrelated->email='unrelated168@example.test';$unrelated->save();
        $this->postJson('/admin/settlements/executions',['period'=>$period,'shop_channel_id'=>$shop->id,'recipient_vendor_id'=>$unrelated->id,'title'=>'Wrong recipient','amount'=>1000])->assertUnprocessable();
        $this->assertDatabaseCount('settlement_executions',1);
    }

    public function test_invalid_period_cannot_silently_generate_current_month_records(): void
    {
        [, $admin,$shop]=$this->createSetup();$admin->type='superadmin';$admin->save();
        $this->actingAs($admin,'admin')->postJson('/admin/settlements/generate',['period'=>'2099-01','shop_channel_id'=>$shop->id])->assertUnprocessable();
        $this->assertDatabaseCount('settlement_runs',0);
    }
}
