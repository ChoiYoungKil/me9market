<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\ShopCancelRefundPolicy;
use App\Models\ShopChannelNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelRoutesTest extends TestCase
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

        $shopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'own',
            'status' => 1,
            'constraint_type' => 'none',
            'stock' => 10,
            'product_price' => 100.0,
            'selling_price' => 120.0,
            'profit' => 20.0,
        ]);

        $policy = ShopCancelRefundPolicy::create([
            'vendor_id' => $vendor->id,
            'type' => 'custom',
            'name' => 'Refund Policy',
            'content' => 'Policy Content',
            'status' => 'active'
        ]);

        $notice = ShopChannelNotice::create([
            'shop_channel_id' => $shop->id,
            'type' => 'notice',
            'title' => 'Important Notice',
            'author' => 'Author Name',
            'content' => 'Notice Content',
            'status' => 1,
            'view_count' => 0
        ]);

        return [$vendor, $admin, $shop, $product, $shopProduct, $policy, $notice];
    }

    public function test_guest_routes()
    {
        $this->get('/channel/login')->assertStatus(200);
        $this->get('/channel/register')->assertStatus(200);
    }

    public function test_authenticated_dashboard_routes()
    {
        list($vendor, $admin, $shop, $product, $shopProduct, $policy, $notice) = $this->createSetup();

        $this->actingAs($admin, 'admin')->get('/channel')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/complete-profile')->assertStatus(200);
    }

    public function test_authenticated_order_list_routes()
    {
        list($vendor, $admin, $shop, $product, $shopProduct, $policy, $notice) = $this->createSetup();

        $this->actingAs($admin, 'admin')->get('/channel/order/list')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/order/cancel/list')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/order/return/list')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/order/exchange/list')->assertStatus(200);
    }

    public function test_authenticated_product_management_routes()
    {
        list($vendor, $admin, $shop, $product, $shopProduct, $policy, $notice) = $this->createSetup();

        $this->actingAs($admin, 'admin')->get('/channel/product/own')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/product/public')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/product/partial')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/product/request')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/product/base/detail/{$product->id}")->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/product/base/edit/{$product->id}")->assertStatus(200);
    }

    public function test_authenticated_settings_routes()
    {
        list($vendor, $admin, $shop, $product, $shopProduct, $policy, $notice) = $this->createSetup();

        $this->actingAs($admin, 'admin')->get('/channel/settings/delivery')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/settings/info')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/settings/order-manager')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/settings/points')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/settings/refund')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/settings/refund/{$policy->id}")->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/settings/sub-accounts')->assertStatus(200);
    }

    public function test_authenticated_shop_routes()
    {
        list($vendor, $admin, $shop, $product, $shopProduct, $policy, $notice) = $this->createSetup();

        $this->actingAs($admin, 'admin')->get('/channel/shop/list')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/shop/register')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/shop/info?id={$shop->id}")->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/shop/product01')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/shop/product02')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/shop/community')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/channel/shop/community/register')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/shop/community/view/{$notice->id}")->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/shop/community/update/{$notice->id}")->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/shop/info-update/{$shop->id}")->assertStatus(200);
        $this->actingAs($admin, 'admin')->get("/channel/shop/product/edit/{$shopProduct->id}")->assertStatus(200);
    }
}
