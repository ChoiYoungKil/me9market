<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\ShopCancelRefundPolicy;
use App\Models\ShopChannelNotice;
use App\Models\Distributor;
use App\Models\ChannelDeliveryCharge;
use App\Models\ChannelSubAccount;
use App\Models\Order;
use App\Models\OrdersProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_channel_order_manager_can_be_created_and_open_distributor_portal()
    {
        list($vendor, $admin) = $this->createSetup();

        $this->actingAs($admin, 'admin')->post('/channel/settings/order-manager/store', [
            'status' => 1,
            'email' => 'orders-manager@example.com',
            'name' => 'Orders Manager',
            'phone' => '01012345678',
            'password' => 'secret123',
        ])->assertRedirect();

        $this->assertDatabaseHas('distributors', [
            'vendor_id' => $vendor->id,
            'email' => 'orders-manager@example.com',
            'name' => 'Orders Manager',
            'status' => 1,
        ]);

        $manager = Distributor::where('email', 'orders-manager@example.com')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->post("/channel/settings/order-manager/{$manager->id}/portal")
            ->assertRedirect(route('distributor.orders.pending'));

        $this->assertSame($manager->id, session('distributor_id'));
        $this->assertSame($manager->email, session('distributor_email'));
    }

    public function test_channel_order_manager_crud_is_scoped_to_current_vendor()
    {
        list($vendor, $admin) = $this->createSetup();

        $otherVendor = Vendor::create([
            'name' => 'Other Vendor',
            'mobile' => '010-9999-0000',
            'email' => 'other-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);

        $otherManager = Distributor::create([
            'vendor_id' => $otherVendor->id,
            'email' => 'other-manager@example.com',
            'name' => 'Other Manager',
            'phone' => '01099990000',
            'password' => bcrypt('secret123'),
            'status' => 1,
        ]);

        $ownManager = Distributor::create([
            'vendor_id' => $vendor->id,
            'email' => 'own-manager@example.com',
            'name' => 'Own Manager',
            'phone' => '01011110000',
            'password' => bcrypt('secret123'),
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/channel/settings/order-manager')
            ->assertOk()
            ->assertSee('own-manager@example.com')
            ->assertDontSee('other-manager@example.com');

        $this->actingAs($admin, 'admin')->post("/channel/settings/order-manager/{$otherManager->id}/update", [
            'status' => 0,
            'email' => 'changed-other-manager@example.com',
            'name' => 'Changed Other Manager',
            'phone' => '01099990001',
        ])->assertNotFound();

        $this->assertDatabaseHas('distributors', [
            'id' => $otherManager->id,
            'email' => 'other-manager@example.com',
            'name' => 'Other Manager',
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/settings/order-manager/{$otherManager->id}/portal")
            ->assertNotFound();

        $this->actingAs($admin, 'admin')->post("/channel/settings/order-manager/{$ownManager->id}/update", [
            'status' => 0,
            'email' => 'updated-own-manager@example.com',
            'name' => 'Updated Own Manager',
            'phone' => '01011110001',
        ])->assertRedirect();

        $this->assertDatabaseHas('distributors', [
            'id' => $ownManager->id,
            'email' => 'updated-own-manager@example.com',
            'name' => 'Updated Own Manager',
            'status' => 0,
        ]);
    }

    public function test_channel_delivery_charge_crud_is_scoped_to_current_vendor()
    {
        list($vendor, $admin) = $this->createSetup();

        $this->actingAs($admin, 'admin')->post('/channel/settings/delivery/store', [
            'status' => 1,
            'name' => '기본 배송비',
            'courier' => '자체배송',
            'shipping_type' => 'conditional',
            'payment_type' => 'prepaid',
            'base_fee' => 3000,
            'free_order_amount' => 30000,
            'free_order_quantity' => 3,
        ])->assertRedirect(route('channel.delivery.list'));

        $this->assertDatabaseHas('channel_delivery_charges', [
            'vendor_id' => $vendor->id,
            'name' => '기본 배송비',
            'base_fee' => 3000,
            'free_order_amount' => 30000,
        ]);

        $charge = ChannelDeliveryCharge::where('vendor_id', $vendor->id)->firstOrFail();

        $this->actingAs($admin, 'admin')->post("/channel/settings/delivery/{$charge->id}/update", [
            'status' => 0,
            'name' => '수정 배송비',
            'courier' => 'CJ대한통운',
            'shipping_type' => 'fixed',
            'payment_type' => 'cod',
            'fixed_fee' => 5000,
        ])->assertRedirect(route('channel.delivery.list'));

        $this->assertDatabaseHas('channel_delivery_charges', [
            'id' => $charge->id,
            'name' => '수정 배송비',
            'status' => 0,
            'base_fee' => 5000,
            'payment_type' => 'cod',
        ]);

        $otherVendor = Vendor::create([
            'name' => 'Other Delivery Vendor',
            'mobile' => '010-8888-0000',
            'email' => 'other-delivery-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);
        $otherCharge = ChannelDeliveryCharge::create([
            'vendor_id' => $otherVendor->id,
            'status' => 1,
            'name' => '타 채널 배송비',
            'shipping_type' => 'free',
            'payment_type' => 'prepaid',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/settings/delivery/{$otherCharge->id}/delete")
            ->assertNotFound();

        $this->assertDatabaseHas('channel_delivery_charges', ['id' => $otherCharge->id]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/settings/delivery/{$charge->id}/delete")
            ->assertRedirect(route('channel.delivery.list'));

        $this->assertDatabaseMissing('channel_delivery_charges', ['id' => $charge->id]);
    }

    public function test_channel_sub_account_crud_is_scoped_to_current_vendor()
    {
        list($vendor, $admin) = $this->createSetup();

        $this->actingAs($admin, 'admin')->post('/channel/settings/sub-accounts/store', [
            'status' => 1,
            'member_no' => 'M1001',
            'email' => 'sub-manager@example.com',
            'name' => 'Sub Manager',
            'mobile' => '01012341234',
            'password' => 'secret123',
            'started_at' => '2026-08-01',
            'ended_at' => '2026-09-01',
            'permissions' => ['shop', 'order'],
        ])->assertRedirect(route('channel.sub_accounts.list'));

        $this->assertDatabaseHas('admins', [
            'vendor_id' => $vendor->id,
            'type' => 'subadmin',
            'email' => 'sub-manager@example.com',
            'name' => 'Sub Manager',
            'status' => 1,
        ]);
        $this->assertDatabaseHas('channel_sub_accounts', [
            'vendor_id' => $vendor->id,
            'member_no' => 'M1001',
        ]);

        $account = ChannelSubAccount::where('vendor_id', $vendor->id)->firstOrFail();

        $this->actingAs($admin, 'admin')->post("/channel/settings/sub-accounts/{$account->id}/update", [
            'status' => 0,
            'member_no' => 'M1002',
            'email' => 'updated-sub-manager@example.com',
            'name' => 'Updated Sub',
            'mobile' => '01099998888',
            'started_at' => '2026-08-02',
            'ended_at' => '2026-09-02',
            'permissions' => ['product', 'settings'],
        ])->assertRedirect(route('channel.sub_accounts.list'));

        $this->assertDatabaseHas('admins', [
            'id' => $account->admin_id,
            'email' => 'updated-sub-manager@example.com',
            'name' => 'Updated Sub',
            'status' => 0,
        ]);
        $this->assertDatabaseHas('channel_sub_accounts', [
            'id' => $account->id,
            'member_no' => 'M1002',
        ]);

        $otherVendor = Vendor::create([
            'name' => 'Other Sub Vendor',
            'mobile' => '010-7777-0000',
            'email' => 'other-sub-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);
        $otherAdmin = new Admin;
        $otherAdmin->name = 'Other Sub';
        $otherAdmin->type = 'subadmin';
        $otherAdmin->vendor_id = $otherVendor->id;
        $otherAdmin->mobile = '01000000000';
        $otherAdmin->email = 'other-sub@example.com';
        $otherAdmin->password = bcrypt('secret123');
        $otherAdmin->confirm = 'Yes';
        $otherAdmin->status = 1;
        $otherAdmin->save();
        $otherAccount = ChannelSubAccount::create([
            'vendor_id' => $otherVendor->id,
            'admin_id' => $otherAdmin->id,
            'member_no' => 'OTHER',
            'permissions' => ['shop'],
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/settings/sub-accounts/{$otherAccount->id}/delete")
            ->assertNotFound();

        $this->assertDatabaseHas('channel_sub_accounts', ['id' => $otherAccount->id]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/settings/sub-accounts/{$account->id}/delete")
            ->assertRedirect(route('channel.sub_accounts.list'));

        $this->assertDatabaseMissing('channel_sub_accounts', ['id' => $account->id]);
        $this->assertDatabaseMissing('admins', ['id' => $account->admin_id]);
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

    public function test_channel_shop_delete_action_is_wired_and_scoped()
    {
        list($vendor, $admin, $shop, $product, $shopProduct) = $this->createSetup();

        $otherVendor = Vendor::create([
            'name' => 'Other Shop Vendor',
            'mobile' => '010-6666-0000',
            'email' => 'other-shop-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);
        $otherShop = ShopChannel::create([
            'vendor_id' => $otherVendor->id,
            'channel_code' => 'othershop',
            'channel_name' => 'Other Shop',
            'copyright' => 'Other',
            'keywords' => ['other'],
            'settlement_type' => 1,
            'settlement_rate' => 10,
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/shop/delete/{$otherShop->id}")
            ->assertNotFound();
        $this->assertDatabaseHas('shop_channels', ['id' => $otherShop->id]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/shop/delete/{$shop->id}")
            ->assertRedirect(route('channel.shop_list'));

        $this->assertDatabaseMissing('shop_channels', ['id' => $shop->id]);
        $this->assertDatabaseMissing('shop_channel_products', ['id' => $shopProduct->id]);
    }

    public function test_channel_shop_delete_is_blocked_when_order_history_exists()
    {
        list($vendor, $admin, $shop, $product, $shopProduct) = $this->createSetup();

        $order = new Order;
        $order->user_id = 0;
        $order->name = 'Buyer';
        $order->address = 'Address';
        $order->city = 'City';
        $order->state = 'State';
        $order->country = 'KR';
        $order->pincode = '12345';
        $order->mobile = '01012345678';
        $order->email = 'buyer@example.com';
        $order->shipping_charges = 0;
        $order->coupon_code = '';
        $order->coupon_amount = 0;
        $order->order_status = 'New';
        $order->payment_method = 'COD';
        $order->payment_gateway = 'common_pg';
        $order->grand_total = 120;
        $order->save();

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => 0,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'shop_channel_id' => $shop->id,
            'shop_channel_product_id' => $shopProduct->id,
            'product_id' => $product->id,
            'product_code' => 'P123',
            'product_name' => 'Test Product',
            'product_color' => 'Blue',
            'product_size' => 'Default',
            'product_price' => 120,
            'product_qty' => 1,
            'item_status' => 'New',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/channel/shop/delete/{$shop->id}")
            ->assertRedirect(route('channel.shop_list'));

        $this->assertDatabaseHas('shop_channels', ['id' => $shop->id]);
    }

    public function test_channel_joint_purchase_management_is_scoped_to_current_vendor()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        $otherVendor = Vendor::create([
            'name' => 'Other Vendor',
            'mobile' => '010-9999-0000',
            'email' => 'other-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);

        $otherAdmin = new Admin;
        $otherAdmin->name = 'Other Admin';
        $otherAdmin->type = 'vendor';
        $otherAdmin->vendor_id = $otherVendor->id;
        $otherAdmin->mobile = '01099990000';
        $otherAdmin->email = 'other-admin@example.com';
        $otherAdmin->password = bcrypt('password');
        $otherAdmin->status = 1;
        $otherAdmin->save();

        $otherProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => $otherVendor->id,
            'admin_id' => $otherAdmin->id,
            'admin_type' => 'vendor',
            'product_name' => 'Other Product',
            'product_code' => 'OTHER-JP',
            'product_color' => 'Black',
            'product_price' => 1000,
            'product_discount' => 0,
            'product_weight' => 1,
            'status' => 1,
        ]);

        $ownJointId = DB::table('joint_purchases')->insertGetId([
            'product_id' => $product->id,
            'min_quantity' => 2,
            'current_quantity' => 0,
            'discount_price' => 90,
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
            'discount_price' => 900,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('channel.joint_purchase.list'))
            ->assertOk()
            ->assertSee('P123')
            ->assertDontSee('OTHER-JP');

        $this->actingAs($admin, 'admin')
            ->get(route('channel.joint_purchase.edit', $otherJointId))
            ->assertNotFound();

        $this->actingAs($admin, 'admin')->post(route('channel.joint_purchase.update', $ownJointId), [
            'product_id' => $otherProduct->id,
            'min_quantity' => 3,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'tier_min_quantity' => [3],
            'tier_unit_price' => [800],
        ])->assertNotFound();

        $this->assertDatabaseHas('joint_purchases', [
            'id' => $ownJointId,
            'product_id' => $product->id,
        ]);
    }
}
