<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\Vendor;
use App\Services\JointPurchasePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JointPurchasePricingTest extends TestCase
{
    use RefreshDatabase;

    private function setupJointPurchase(): array
    {
        $vendor = new Vendor;
        $vendor->name = 'Joint Vendor';
        $vendor->mobile = '010-2222-3333';
        $vendor->email = 'joint-vendor@example.com';
        $vendor->status = 1;
        $vendor->commission = 0;
        $vendor->confirm = 'Yes';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Joint Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01022223333';
        $admin->email = 'joint-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $shop = new ShopChannel;
        $shop->vendor_id = $vendor->id;
        $shop->channel_code = 'joint-shop';
        $shop->channel_name = 'Joint Shop';
        $shop->copyright = 'Joint Shop';
        $shop->keywords = '[]';
        $shop->status = 1;
        $shop->settlement_type = 1;
        $shop->settlement_rate = 10;
        $shop->save();

        $product = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'admin_type' => 'vendor',
            'product_name' => 'Joint Product',
            'product_code' => 'JP-001',
            'product_color' => 'Black',
            'product_price' => 500,
            'product_discount' => 0,
            'product_weight' => 100,
            'is_featured' => 'No',
            'status' => 1,
        ]);

        $jointPurchaseId = DB::table('joint_purchases')->insertGetId([
            'product_id' => $product->id,
            'min_quantity' => 100,
            'current_quantity' => 0,
            'discount_price' => 500,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(JointPurchasePricingService::class)->syncTiers($jointPurchaseId, [
            ['min_quantity' => 1, 'max_quantity' => 100, 'unit_price' => 500],
            ['min_quantity' => 101, 'max_quantity' => 200, 'unit_price' => 400],
        ]);

        return [$vendor, $admin, $shop, $product, $jointPurchaseId];
    }

    public function test_joint_purchase_reprices_existing_orders_when_next_tier_is_reached()
    {
        [$vendor, $admin, $shop, $product, $jointPurchaseId] = $this->setupJointPurchase();
        $service = app(JointPurchasePricingService::class);

        $firstOrder = $this->createOrder(1, 50000);
        $firstItem = OrdersProduct::create([
            'order_id' => $firstOrder->id,
            'user_id' => 1,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'joint_purchase_id' => $jointPurchaseId,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => '기본',
            'product_price' => 500,
            'selling_price' => 500,
            'original_unit_price' => 500,
            'original_line_total' => 50000,
            'product_qty' => 100,
            'line_total' => 50000,
            'item_status' => '결제완료',
        ]);

        $projected = $service->projectedPriceForProduct($product->id, 1);
        $this->assertSame(400.0, $projected['unit_price']);

        $secondOrder = $this->createOrder(2, 400);
        OrdersProduct::create([
            'order_id' => $secondOrder->id,
            'user_id' => 2,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'joint_purchase_id' => $jointPurchaseId,
            'joint_price_tier_id' => $projected['tier_id'],
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => '기본',
            'product_price' => 400,
            'selling_price' => 400,
            'original_unit_price' => 500,
            'original_line_total' => 500,
            'product_qty' => 1,
            'line_total' => 400,
            'item_status' => '결제완료',
        ]);

        $service->repricePurchase($jointPurchaseId);

        $this->assertDatabaseHas('joint_purchases', [
            'id' => $jointPurchaseId,
            'current_quantity' => 101,
        ]);
        $this->assertDatabaseHas('orders_products', [
            'id' => $firstItem->id,
            'selling_price' => 400,
            'line_total' => 40000,
            'reprice_adjustment_amount' => 10000,
            'reprice_status' => 'pending_repayment',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $firstOrder->id,
            'grand_total' => 40000,
        ]);
    }

    private function createOrder(int $userId, float $grandTotal): Order
    {
        $order = new Order;
        $order->user_id = $userId;
        $order->name = 'Buyer ' . $userId;
        $order->address = 'Seoul';
        $order->city = 'Seoul';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01000000000';
        $order->email = 'buyer' . $userId . '@example.com';
        $order->shipping_charges = 0;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = $grandTotal;
        $order->save();

        return $order;
    }
}
