<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Services\SettlementCalculator;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

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
        list($vendor, $admin, $shop, $product) = $this->createSetup();

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
        $this->assertEquals('정산완료', $settlement->status);
    }

    public function test_settlement_view_displays_detailed_orders()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

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
        list($vendor, $admin, $shop, $product) = $this->createSetup();
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
        $this->assertEquals(23000.0, $summary['gross_sales_amount']);
        $this->assertEquals(14000.0, $summary['supply_amount']);
        $this->assertEquals(3170.0, $summary['admin_amount']);
        $this->assertEquals(200.0, $summary['point_deposit_amount']);
        $this->assertEquals(1000.0, $summary['point_used_amount']);
        $this->assertEquals(19630.0, $summary['settlement_amount']);
    }
}
