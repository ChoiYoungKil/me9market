<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['shop_channel.seed_demo_data' => false]);
    }

    private function createShopProduct(string $channelCode, string $productCode, string $customerPrefix): array
    {
        $vendor = Vendor::create([
            'name' => $customerPrefix . ' Vendor',
            'mobile' => '010-0000-0000',
            'email' => strtolower($customerPrefix) . '-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);

        $admin = new Admin;
        $admin->name = $customerPrefix . ' Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '010-0000-0000';
        $admin->email = strtolower($customerPrefix) . '-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $shop = ShopChannel::create([
            'vendor_id' => $vendor->id,
            'channel_code' => $channelCode,
            'channel_name' => $customerPrefix . ' Channel',
            'copyright' => $customerPrefix,
            'keywords' => [],
            'settlement_type' => 1,
            'settlement_rate' => 10,
            'status' => 1,
        ]);

        $product = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'admin_type' => 'vendor',
            'product_name' => $customerPrefix . ' Product',
            'product_code' => $productCode,
            'product_color' => 'Black',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 1,
            'status' => 1,
        ]);

        $shopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'own',
            'approval_status' => 'approved',
            'status' => 1,
            'constraint_type' => 'none',
            'stock' => 10,
            'product_price' => 10000,
            'selling_price' => 12000,
            'profit' => 2000,
        ]);

        return [$vendor, $admin, $shop, $product, $shopProduct];
    }

    private function createOrderForShop(ShopChannel $shop, Product $product, string $customerName): Order
    {
        $order = new Order;
        $order->user_id = 0;
        $order->name = $customerName;
        $order->address = $customerName . ' Address';
        $order->city = 'Seoul';
        $order->state = 'Jung';
        $order->country = 'Korea';
        $order->pincode = '04524';
        $order->mobile = '010-1111-2222';
        $order->email = strtolower(str_replace(' ', '-', $customerName)) . '@example.com';
        $order->shipping_charges = 0;
        $order->coupon_code = '';
        $order->coupon_amount = 0;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Test';
        $order->grand_total = 12000;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 0,
            'vendor_id' => $shop->vendor_id,
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'admin_id' => $product->admin_id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => '기본옵션',
            'product_price' => 12000,
            'supply_price' => 10000,
            'selling_price' => 12000,
            'product_qty' => 1,
            'line_total' => 12000,
            'item_status' => '결제완료',
            'status_code' => 'paid',
            'commission' => 1200,
            'settlement_status' => 'pending',
        ]);

        return $order;
    }

    public function test_shop_cart_accepts_only_current_channel_products()
    {
        list(, , $currentShop, , $currentShopProduct) = $this->createShopProduct('current-shop', 'CUR-001', 'Current');
        list(, , , , $otherShopProduct) = $this->createShopProduct('other-shop', 'OTH-001', 'Other');

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->post(route('front.shop.cart.add'), [
                'shop_product_id' => $otherShopProduct->id,
                'qty' => 1,
            ])
            ->assertNotFound();

        $this->assertSame([], session('shop_channel_cart', []));

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->post(route('front.shop.cart.add'), [
                'shop_product_id' => $currentShopProduct->id,
                'qty' => 2,
            ])
            ->assertRedirect();

        $this->assertArrayHasKey($currentShopProduct->id, session('shop_channel_cart', []));
    }

    public function test_shop_order_details_do_not_expose_other_channel_orders()
    {
        list(, , $currentShop, $currentProduct) = $this->createShopProduct('current-shop', 'CUR-002', 'Current');
        list(, , $otherShop, $otherProduct) = $this->createShopProduct('other-shop', 'OTH-002', 'Other');

        $otherOrder = $this->createOrderForShop($otherShop, $otherProduct, 'Other Customer');
        $this->createOrderForShop($currentShop, $currentProduct, 'Current Customer');

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('front.shop.order.details', ['id' => $otherOrder->id]))
            ->assertOk()
            ->assertSee('Current Customer')
            ->assertDontSee('Other Customer')
            ->assertDontSee('other-customer@example.com');
    }

    public function test_shop_product_details_do_not_fallback_to_another_product()
    {
        list(, , $currentShop, , $currentShopProduct) = $this->createShopProduct('current-shop', 'CUR-PROD', 'Current');
        list(, , , , $otherShopProduct) = $this->createShopProduct('other-shop', 'OTH-PROD', 'Other');

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('shop.product_details', $currentShopProduct->id))
            ->assertOk()
            ->assertSee('Current Product');

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('shop.product_details', $otherShopProduct->id))
            ->assertNotFound();

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('shop.product_details', 999999))
            ->assertNotFound();
    }

    public function test_public_invoice_download_requires_order_ownership_or_verified_session()
    {
        list(, , $shop, $product) = $this->createShopProduct('invoice-shop', 'INV-001', 'Invoice');
        $order = $this->createOrderForShop($shop, $product, 'Invoice Customer');

        $this->get("orders/invoice/download/{$order->id}")
            ->assertForbidden();
    }

    public function test_shop_joint_purchases_are_scoped_to_current_channel()
    {
        list(, , $currentShop, $currentProduct) = $this->createShopProduct('current-shop', 'CUR-JOINT', 'Current');
        list(, , , $otherProduct) = $this->createShopProduct('other-shop', 'OTH-JOINT', 'Other');

        $currentJointId = DB::table('joint_purchases')->insertGetId([
            'product_id' => $currentProduct->id,
            'min_quantity' => 2,
            'current_quantity' => 0,
            'discount_price' => 9000,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherJointId = DB::table('joint_purchases')->insertGetId([
            'product_id' => $otherProduct->id,
            'min_quantity' => 2,
            'current_quantity' => 0,
            'discount_price' => 9000,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('shop.joint_purchases_list'))
            ->assertOk()
            ->assertSee('Current Product')
            ->assertDontSee('Other Product');

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('shop.joint_purchase_details', $currentJointId))
            ->assertOk()
            ->assertSee('Current Product');

        $this->withSession(['shop_channel_id' => $currentShop->id])
            ->get(route('shop.joint_purchase_details', $otherJointId))
            ->assertNotFound();
    }
}
