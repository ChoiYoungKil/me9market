<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Distributor;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\ProductsImage;
use App\Models\Section;
use App\Models\User;
use App\Models\Vendor;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup()
    {
        $vendor = new Vendor;
        $vendor->name = 'Test Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'vendor@example.com';
        $vendor->status = 1;
        $vendor->commission = 10;
        $vendor->confirm = 'Yes';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Super Admin';
        $admin->type = 'superadmin';
        $admin->vendor_id = 0;
        $admin->mobile = '01011112222';
        $admin->email = 'superadmin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $vendorAdmin = new Admin;
        $vendorAdmin->name = 'Vendor Admin';
        $vendorAdmin->type = 'vendor';
        $vendorAdmin->vendor_id = $vendor->id;
        $vendorAdmin->mobile = '01022223333';
        $vendorAdmin->email = 'vendoradmin@example.com';
        $vendorAdmin->password = bcrypt('password');
        $vendorAdmin->status = 1;
        $vendorAdmin->save();

        $section = new Section;
        $section->name = 'Electronics';
        $section->status = 1;
        $section->save();

        $category = new Category;
        $category->section_id = $section->id;
        $category->parent_id = 0;
        $category->category_name = 'Laptops';
        $category->category_image = '';
        $category->category_discount = 0;
        $category->description = '';
        $category->url = 'laptops';
        $category->meta_title = '';
        $category->meta_description = '';
        $category->meta_keywords = '';
        $category->status = 1;
        $category->save();

        $brand = new Brand;
        $brand->name = 'Samsung';
        $brand->status = 1;
        $brand->save();

        $product = Product::create([
            'section_id' => $section->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'admin_type' => 'superadmin',
            'product_name' => 'Galaxy Book',
            'product_code' => 'GB123',
            'product_color' => 'Silver',
            'product_price' => 1200.0,
            'product_discount' => 0.0,
            'product_weight' => 1200,
            'status' => 1,
        ]);

        $banner = new Banner;
        $banner->image = 'banner.png';
        $banner->link = '#';
        $banner->title = 'Summer Sale';
        $banner->alt = 'Sale';
        $banner->type = 'Slider';
        $banner->status = 1;
        $banner->save();

        $coupon = new Coupon;
        $coupon->vendor_id = 0;
        $coupon->coupon_option = 'Single';
        $coupon->coupon_code = 'SUMMER10';
        $coupon->categories = '1';
        $coupon->users = 'superadmin@example.com';
        $coupon->coupon_type = 'Percentage';
        $coupon->amount_type = 'Percentage';
        $coupon->amount = 10;
        $coupon->expiry_date = '2026-12-31';
        $coupon->status = 1;
        $coupon->save();

        $user = new User;
        $user->name = 'Jane Doe';
        $user->mobile = '01055556666';
        $user->email = 'jane@example.com';
        $user->password = bcrypt('password');
        $user->status = 1;
        $user->save();

        $order = new Order;
        $order->user_id = $user->id;
        $order->name = 'Jane Doe';
        $order->address = 'Address';
        $order->city = 'Seoul';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01055556666';
        $order->email = 'jane@example.com';
        $order->shipping_charges = 0;
        $order->order_status = 'New';
        $order->payment_method = 'COD';
        $order->payment_gateway = 'COD';
        $order->grand_total = 1200.0;
        $order->save();

        $subscriber = new NewsletterSubscriber;
        $subscriber->email = 'sub@example.com';
        $subscriber->status = 1;
        $subscriber->save();

        return [$admin, $vendor, $section, $category, $brand, $product, $banner, $coupon, $user, $order, $subscriber, $vendorAdmin];
    }

    public function test_filter_value_edit_keeps_its_parent_selection_and_saved_value(): void
    {
        [$admin, , , $category] = $this->createSetup();
        $parent = \App\Models\ProductsFilter::forceCreate(['cat_ids' => (string) $category->id, 'filter_name' => '재질', 'filter_column' => 'review_material', 'status' => 1]);
        \App\Models\ProductsFilter::forceCreate(['cat_ids' => (string) $category->id, 'filter_name' => '형태', 'filter_column' => 'review_shape', 'status' => 1]);
        $value = \App\Models\ProductsFiltersValue::forceCreate(['filter_id' => $parent->id, 'filter_value' => '선택된 필터값 검수', 'status' => 1]);
        $response = $this->actingAs($admin, 'admin')->get('/admin/add-edit-filter-value/'.$value->id)->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        $selected = $xpath->query('//select[@name="filter_id"]/option[@selected]');
        $this->assertSame(1, $selected->length);
        $this->assertSame((string) $parent->id, $selected->item(0)->getAttribute('value'));
        $this->assertSame('선택된 필터값 검수', $xpath->query('//input[@name="filter_value"]')->item(0)->getAttribute('value'));
        $this->get('/admin/filters-values')->assertOk()->assertSee(url('admin/add-edit-filter-value/'.$value->id));
    }

    public function test_admin_shipping_uses_current_vendor_policies_and_shared_validation(): void
    {
        [$admin, $vendor, , , , $product, , , , , , $vendorAdmin] = $this->createSetup();
        $this->actingAs($admin, 'admin')->get('/admin/shipping-charges')->assertOk()
            ->assertSee('배송비 설정')->assertSee('common/portal.css')->assertDontSee('Rate (0g to 500g)');
        $payload = ['vendor_id' => $vendor->id, 'status' => 1, 'name' => '관리자 수량 정책', 'shipping_type' => 'conditional',
            'payment_type' => 'prepaid', 'base_fee' => 3000, 'free_order_quantity' => 2];
        $this->post('/admin/shipping-charges', $payload)->assertRedirect();
        $charge = \App\Models\ChannelDeliveryCharge::where('name', $payload['name'])->firstOrFail();
        $this->get('/admin/edit-shipping-charges/'.$charge->id)->assertOk()->assertSee('관리자 수량 정책')->assertSee('변동 배송비');
        $product->update(['shipping_policy_id' => $charge->id]);
        $totals = app(\App\Services\ShopOrderTotals::class)->calculate([['id'=>$product->id,'product'=>$product,'qty'=>2,'line_total'=>10000]]);
        $this->assertEquals(0, $totals['shipping']);
        $this->post('/admin/edit-shipping-charges/'.$charge->id, array_replace($payload, ['free_order_quantity'=>3]))->assertRedirect();
        $totals = app(\App\Services\ShopOrderTotals::class)->calculate([['id'=>$product->id,'product'=>$product,'qty'=>2,'line_total'=>10000]]);
        $this->assertEquals(3000, $totals['shipping']);
        $this->postJson('/admin/edit-shipping-charges/'.$charge->id, array_replace($payload, ['base_fee'=>-1]))->assertUnprocessable();
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/shipping-charges')->assertForbidden();
        $this->post('/admin/shipping-charges', $payload)->assertForbidden();
        $this->actingAs($vendorAdmin, 'admin')->get('/channel/settings/delivery')->assertOk()->assertSee('관리자 수량 정책');
    }

    public function test_guest_admin_routes_redirect_to_login()
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_guest_admin_login_screen_renders()
    {
        $this->get('/admin/login')->assertStatus(200);
    }

    public function test_authenticated_admin_portal_get_routes()
    {
        [$admin, $vendor, $section, $category, $brand, $product, $banner, $coupon, $user, $order, $subscriber, $vendorAdmin] = $this->createSetup();

        $this->actingAs($admin, 'admin')->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/sub01')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/sub02')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/sub03')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/view')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/newpage')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/loading')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/update-admin-details')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/admins')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/sections')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/categories')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/brands')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/products')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/banners')->assertStatus(200);
        $this->get('/admin/add-edit-banner')->assertOk()->assertDontSee('admin/images/photos');
        $this->get('/admin/add-edit-banner/'.$banner->id)->assertOk()
            ->assertSee('front/images/banner_images/'.$banner->image)
            ->assertSee($banner->title)->assertDontSee('admin/images/photos');
        $this->actingAs($admin, 'admin')->get('/admin/notices')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/faqs')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/contacts')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/coupons')->assertStatus(200);
        $this->get('/admin/add-edit-coupon')->assertOk();
        $this->get('/admin/add-edit-coupon/'.$coupon->id)->assertOk()->assertSee($coupon->coupon_code);
        $this->actingAs($admin, 'admin')->get('/admin/users')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/orders')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/order-managers')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/subscribers')->assertStatus(200);

        // Sub-routes and slugs (using vendor admin to avoid toArray() on null vendor_id)
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/personal')->assertStatus(200);
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/business')->assertStatus(200);
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/bank')->assertStatus(200);
    }

    public function test_dashboard_uses_real_confirmed_sales_and_scopes_vendor_data(): void
    {
        [$admin, $vendor, , , , $product, , , $user, $order, , $vendorAdmin] = $this->createSetup();
        $item = OrdersProduct::create([
            'order_id' => $order->id, 'user_id' => $user->id, 'vendor_id' => $vendor->id,
            'admin_id' => $vendorAdmin->id, 'product_id' => $product->id,
            'product_code' => $product->product_code, 'product_name' => 'Dashboard Own Product',
            'product_color' => 'Black', 'product_size' => 'M', 'product_price' => 1200,
            'product_qty' => 2, 'line_total' => 2400, 'status_code' => OrderItemStatus::CONFIRMED,
            'item_status' => 'Confirmed', 'confirmed_at' => now(),
        ]);
        $otherVendor = Vendor::create(['name' => 'Dashboard Other Vendor', 'email' => 'dashboard-other@example.com',
            'mobile' => '01099998888', 'status' => 1, 'commission' => 0, 'confirm' => 'Yes']);
        $other = $item->replicate();
        $other->fill(['vendor_id' => $otherVendor->id, 'product_name' => 'Dashboard Private Product', 'line_total' => 5000])->save();
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/dashboard')->assertOk()
            ->assertViewHas('totalItems', 1)
            ->assertViewHas('topProducts', fn ($rows) => $rows->count() === 1 && (int) $rows->sum('amount') === 2400)
            ->assertViewHas('topCategories', fn ($rows) => (int) $rows->sum('amount') === 2400)
            ->assertSee('Dashboard Own Product')->assertDontSee('Dashboard Private Product');
        $this->actingAs($admin, 'admin')->get('/admin/dashboard')->assertOk()
            ->assertViewHas('totalItems', 2)
            ->assertViewHas('topCategories', fn ($rows) => (int) $rows->sum('amount') === 7400);
    }

    public function test_admin_logout_clears_the_admin_guard(): void
    {
        [$admin] = $this->createSetup();
        $this->actingAs($admin, 'admin')->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_can_manage_order_managers_and_open_distributor_portal()
    {
        [$admin, $vendor, $section, $category, $brand, $product, $banner, $coupon, $user, $order] = $this->createSetup();

        $this->actingAs($admin, 'admin')->post('/admin/order-managers', [
            'status' => 1,
            'email' => 'distributor-admin@example.com',
            'name' => 'Distributor Admin',
            'phone' => '01099998888',
            'return_postcode' => '04524',
            'return_address' => '서울특별시 중구 세종대로 110',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123',
        ])->assertRedirect(route('admin.order_managers.index'));

        $this->assertDatabaseHas('distributors', [
            'email' => 'distributor-admin@example.com',
            'name' => 'Distributor Admin',
            'return_postcode' => '04524',
            'return_address' => '서울특별시 중구 세종대로 110',
            'status' => 1,
        ]);

        $manager = Distributor::where('email', 'distributor-admin@example.com')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/order-managers')
            ->assertStatus(200)
            ->assertSee(route('distributor.login'))
            ->assertSee('distributor-admin@example.com')
            ->assertDontSee('PW초기화');

        $this->actingAs($admin, 'admin')->post("/admin/order-managers/{$manager->id}/update", [
            'status' => 0,
            'email' => 'distributor-updated@example.com',
            'name' => 'Updated Distributor',
            'phone' => '01077776666',
            'return_postcode' => '06236',
            'return_address' => '서울특별시 강남구 테헤란로 123',
        ])->assertRedirect(route('admin.order_managers.index'));

        $this->assertDatabaseHas('distributors', [
            'id' => $manager->id,
            'email' => 'distributor-updated@example.com',
            'name' => 'Updated Distributor',
            'return_postcode' => '06236',
            'return_address' => '서울특별시 강남구 테헤란로 123',
            'status' => 0,
        ]);

        $this->actingAs($admin, 'admin')->post("/admin/order-managers/{$manager->id}/portal")
            ->assertRedirect(route('admin.order_managers.index'))->assertSessionHas('error_message');
        $this->withSession(['distributor_id' => $manager->id])->get(route('distributor.orders.pending'))
            ->assertRedirect(route('distributor.login'));
        $manager->refresh()->update(['status' => 1]);

        $this->actingAs($admin, 'admin')
            ->post("/admin/order-managers/{$manager->id}/portal")
            ->assertRedirect(route('distributor.orders.pending'));

        $this->assertSame($manager->id, session('distributor_id'));
        $this->assertSame('distributor-updated@example.com', session('distributor_email'));

        $product->update([
            'distributor_id' => $manager->id,
            'order_manager_enabled' => true,
        ]);

        OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'distributor_id' => $manager->id,
            'product_code' => $product->product_code,
            'product_name' => 'Distributor Linked Product',
            'product_color' => 'Silver',
            'product_size' => '기본옵션',
            'product_price' => 1200,
            'product_qty' => 1,
            'line_total' => 1200,
            'item_status' => 'Payment Captured',
            'status_code' => OrderItemStatus::PAID,
        ]);

        $this->withSession([
            'distributor_id' => $manager->id,
            'distributor_name' => 'Updated Distributor',
            'distributor_email' => 'distributor-updated@example.com',
        ])->get(route('distributor.orders.pending'))
            ->assertStatus(200)
            ->assertSee('Distributor Linked Product');

        $this->actingAs($admin, 'admin')
            ->post("/admin/order-managers/{$manager->id}/portal", ['destination' => 'completed'])
            ->assertRedirect(route('distributor.orders.completed'));

        $this->actingAs($admin, 'admin')
            ->post("/admin/order-managers/{$manager->id}/delete")
            ->assertRedirect(route('admin.order_managers.index'));

        $this->assertDatabaseMissing('distributors', ['id' => $manager->id]);
    }

    public function test_vendor_admin_order_access_is_scoped_to_own_order_items()
    {
        [$admin, $vendor, $section, $category, $brand, $product, $banner, $coupon, $user, $order, $subscriber, $vendorAdmin] = $this->createSetup();

        $ownItem = OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'admin_id' => $vendorAdmin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => 'Own Vendor Product',
            'product_color' => 'Silver',
            'product_size' => '기본옵션',
            'product_price' => 1200,
            'product_qty' => 1,
            'line_total' => 1200,
            'item_status' => 'Payment Captured',
            'status_code' => OrderItemStatus::PAID,
        ]);

        $otherVendor = Vendor::create([
            'name' => 'Other Vendor',
            'mobile' => '01099990000',
            'email' => 'other-vendor@example.com',
            'status' => 1,
            'commission' => 10,
            'confirm' => 'Yes',
        ]);

        $otherOrder = new Order;
        $otherOrder->user_id = $user->id;
        $otherOrder->name = 'Other Order Customer';
        $otherOrder->address = 'Other Address';
        $otherOrder->city = 'Seoul';
        $otherOrder->state = 'Seoul';
        $otherOrder->country = 'Korea';
        $otherOrder->pincode = '12345';
        $otherOrder->mobile = '01099998888';
        $otherOrder->email = 'other-order@example.com';
        $otherOrder->shipping_charges = 0;
        $otherOrder->order_status = 'New';
        $otherOrder->payment_method = 'COD';
        $otherOrder->payment_gateway = 'COD';
        $otherOrder->grand_total = 500;
        $otherOrder->save();

        $otherItem = OrdersProduct::create([
            'order_id' => $otherOrder->id,
            'user_id' => $user->id,
            'vendor_id' => $otherVendor->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => 'OTHER-PRODUCT',
            'product_name' => 'Other Vendor Product',
            'product_color' => 'Black',
            'product_size' => '기본옵션',
            'product_price' => 500,
            'product_qty' => 1,
            'line_total' => 500,
            'item_status' => 'Payment Captured',
            'status_code' => OrderItemStatus::PAID,
        ]);

        $this->actingAs($vendorAdmin, 'admin')
            ->get("/admin/orders/{$otherOrder->id}")
            ->assertNotFound();

        $this->actingAs($vendorAdmin, 'admin')
            ->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Own Vendor Product')
            ->assertDontSee('Other Vendor Product');

        Mail::fake();

        $this->actingAs($vendorAdmin, 'admin')->post('/admin/update-order-status', [
            'order_id' => $order->id,
            'order_status' => 'Shipped',
        ])->assertForbidden();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'New',
        ]);

        $this->actingAs($vendorAdmin, 'admin')->post('/admin/update-order-item-status', [
            'order_item_id' => $otherItem->id,
            'order_item_status' => 'Shipped',
        ])->assertNotFound();

        $this->assertDatabaseHas('orders_products', [
            'id' => $otherItem->id,
            'item_status' => 'Payment Captured',
        ]);

        $this->actingAs($vendorAdmin, 'admin')->post('/admin/update-order-item-status', [
            'order_item_id' => $ownItem->id,
            'order_item_status' => 'Shipped',
            'item_courier_name' => 'CJ',
            'item_tracking_number' => '123456789',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders_products', [
            'id' => $ownItem->id,
            'item_status' => OrderItemStatus::label(OrderItemStatus::SHIPPING),
            'status_code' => OrderItemStatus::SHIPPING,
        ]);
        $otherItem->update(['order_id' => $order->id]);
        \App\Models\OrdersLog::forceCreate(['order_id' => $order->id, 'order_item_id' => $otherItem->id, 'order_status' => 'Private vendor status']);
        $this->get("/admin/orders/{$order->id}")->assertOk()
            ->assertDontSee('Other Vendor Product')->assertDontSee('Private vendor status')
            ->assertViewHas('orderLog', fn ($logs) => collect($logs)->every(fn ($log) => $log['order_item_id'] === $ownItem->id));
    }

    public function test_admin_delete_routes_require_post_requests()
    {
        [$admin, $vendor, $section] = $this->createSetup();

        $this->actingAs($admin, 'admin')
            ->get("/admin/delete-section/{$section->id}")
            ->assertStatus(405);

        $this->actingAs($admin, 'admin')
            ->post("/admin/delete-section/{$section->id}")
            ->assertRedirect('/');

        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
    }

    public function test_vendor_can_delete_only_own_product_attributes(): void
    {
        [, , , , , $product, , , , , , $vendorAdmin] = $this->createSetup();
        $attribute = ProductsAttribute::create([
            'product_id' => $product->id,
            'sku' => 'ATTR-OWN-001',
            'size' => 'M',
            'price' => 100,
            'stock' => 5,
            'status' => 1,
        ]);
        $otherVendor = Vendor::create([
            'name' => 'Other Attribute Vendor',
            'mobile' => '01099991111',
            'email' => 'other-attribute@example.com',
            'status' => 1,
            'commission' => 10,
            'confirm' => 'Yes',
        ]);
        $otherAdmin = new Admin;
        $otherAdmin->name = 'Other Attribute Admin';
        $otherAdmin->type = 'vendor';
        $otherAdmin->vendor_id = $otherVendor->id;
        $otherAdmin->mobile = '01099991111';
        $otherAdmin->email = 'other-attribute-admin@example.com';
        $otherAdmin->password = bcrypt('password');
        $otherAdmin->status = 1;
        $otherAdmin->save();

        $this->actingAs($otherAdmin, 'admin')
            ->post("/admin/delete-attribute/{$attribute->id}")
            ->assertNotFound();
        $this->assertDatabaseHas('products_attributes', ['id' => $attribute->id]);

        $this->actingAs($vendorAdmin, 'admin')
            ->post("/admin/delete-attribute/{$attribute->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('products_attributes', ['id' => $attribute->id]);
    }

    public function test_unavailable_distributor_portals_show_reason_without_switching_session(): void
    {
        [$admin] = $this->createSetup();
        $previous = Distributor::create(['name' => '기존 발주사', 'email' => 'previous-portal@example.test',
            'password' => bcrypt('test-password'), 'status' => 1]);
        $manager = Distributor::create(['name' => '접속 제한 발주사', 'email' => 'blocked-portal@example.test',
            'password' => bcrypt('test-password'), 'status' => 0]);
        $this->actingAs($admin, 'admin')->withSession(['distributor_id' => $previous->id,
            'distributor_name' => $previous->name, 'distributor_email' => $previous->email]);
        foreach ([
            ['status' => 0, 'access_started_at' => null, 'access_ended_at' => null, 'label' => '중지'],
            ['status' => 1, 'access_started_at' => now()->addDay(), 'access_ended_at' => now()->addDays(2), 'label' => '접속 전'],
            ['status' => 1, 'access_started_at' => now()->subDays(2), 'access_ended_at' => now()->subDay(), 'label' => '기간 만료'],
        ] as $case) {
            $label = $case['label'];
            unset($case['label']);
            $manager->update($case);
            $response = $this->get('/admin/order-managers')->assertOk()->assertSee($label);
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
            $xpath = new \DOMXPath($dom);
            $buttons = $xpath->query('//form[@action="'.route('admin.order_managers.portal', $manager->id).'"]//button[@disabled]');
            $this->assertCount(2, $buttons);
            foreach (['pending', 'completed'] as $destination) {
                $this->post(route('admin.order_managers.portal', $manager->id), ['destination' => $destination])
                    ->assertRedirect(route('admin.order_managers.index'))
                    ->assertSessionHas('error_message', fn ($message) => str_contains($message, $label))
                    ->assertSessionHas('distributor_id', $previous->id)
                    ->assertSessionHas('distributor_email', $previous->email);
                $this->get('/admin/order-managers')->assertOk()->assertSee('상태와 접속 기간을 확인해 주세요.');
            }
        }
        $manager->update(['status' => 1, 'access_started_at' => null, 'access_ended_at' => null]);
        foreach (['pending', 'completed'] as $destination) {
            $this->post(route('admin.order_managers.portal', $manager->id), ['destination' => $destination])
                ->assertRedirect(route('distributor.orders.'.$destination))
                ->assertSessionHas('distributor_id', $manager->id);
            $this->get(route('distributor.orders.'.$destination))->assertOk()->assertSee($manager->name);
        }
    }

    public function test_coupon_edit_preserves_code_owner_and_current_status(): void
    {
        [$admin, $vendor, , $category, $brand, , , $coupon] = $this->createSetup();
        $coupon->vendor_id = $vendor->id;
        $coupon->coupon_option = 'Automatic';
        $coupon->coupon_type = 'Multiple Times';
        $coupon->status = 0;
        $coupon->save();
        $this->actingAs($admin, 'admin');
        $payload = ['coupon_option' => 'Automatic', 'coupon_code' => 'UNTRUSTED-REPLACEMENT',
            'coupon_type' => 'Multiple Times', 'amount_type' => 'Percentage', 'amount' => 15,
            'categories' => [$category->id], 'brands' => [$brand->id], 'expiry_date' => '2030-12-31'];
        foreach ([0, 1] as $status) {
            $coupon->status = $status;
            $coupon->save();
            $this->post('/admin/add-edit-coupon/'.$coupon->id, $payload)
                ->assertSessionHasNoErrors()->assertRedirect('/admin/coupons');
            $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'coupon_code' => 'SUMMER10',
                'coupon_option' => 'Automatic', 'vendor_id' => $vendor->id, 'status' => $status,
                'amount' => 15, 'expiry_date' => '2030-12-31']);
        }
    }

    public function test_filter_editor_preserves_saved_multiple_categories_and_deep_options(): void
    {
        [$admin, , , $category] = $this->createSetup();
        $child = $category->replicate();
        $child->parent_id = $category->id;
        $child->category_name = '하위 분류';
        $child->url = 'filter-child';
        $child->save();
        $leaf = $child->replicate();
        $leaf->parent_id = $child->id;
        $leaf->category_name = '최하위 분류';
        $leaf->url = 'filter-leaf';
        $leaf->save();
        $filter = \App\Models\ProductsFilter::forceCreate(['cat_ids' => $category->id.','.$leaf->id,
            'filter_name' => '소재', 'filter_column' => 'qa_material', 'status' => 1]);
        $response = $this->actingAs($admin, 'admin')->get('/admin/add-edit-filter/'.$filter->id)->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $selected = (new \DOMXPath($dom))->query('//select[@id="cat_ids"]//option[@selected]');
        $this->assertCount(2, $selected);
        $this->assertSame([(string) $category->id, (string) $leaf->id],
            array_map(fn ($option) => $option->getAttribute('value'), iterator_to_array($selected)));
        $this->assertDatabaseHas('products_filters', ['id' => $filter->id,
            'cat_ids' => $category->id.','.$leaf->id, 'filter_column' => 'qa_material']);
    }

    public function test_product_editor_keeps_a_deeply_nested_category_selected(): void
    {
        [$admin, , , $category, , $product] = $this->createSetup();
        $child = $category->replicate();
        $child->parent_id = $category->id;
        $child->category_name = '하위 분류';
        $child->url = 'child-category';
        $child->save();
        $grandchild = $child->replicate();
        $grandchild->parent_id = $child->id;
        $grandchild->category_name = '최하위 분류';
        $grandchild->url = 'grandchild-category';
        $grandchild->save();
        $product->category_id = $grandchild->id;
        $product->save();

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/add-edit-product/{$product->id}")->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $options = (new \DOMXPath($dom))->query('//select[@name="category_id"]/optgroup/option[@selected]');
        $this->assertCount(1, $options);
        $this->assertSame((string) $grandchild->id, $options->item(0)->getAttribute('value'));
    }

    public function test_product_image_upload_resize_status_and_delete(): void
    {
        [$admin, , , , , $product] = $this->createSetup();
        $images = collect();
        try {
            $this->actingAs($admin, 'admin')->post('/admin/add-images/'.$product->id, [
                'images' => [\Illuminate\Http\UploadedFile::fake()->image('wide.png', 800, 400),
                    \Illuminate\Http\UploadedFile::fake()->image('tall.jpg', 300, 600)],
            ])->assertSessionHasNoErrors()->assertRedirect('/admin/add-images/'.$product->id);
            $images = ProductsImage::where('product_id', $product->id)->orderBy('id')->get();
            $this->assertCount(2, $images);
            foreach ($images as $image) {
                $this->assertSame(1, (int) $image->status);
                foreach (['large', 'medium', 'small'] as $size) {
                    $this->assertFileExists(public_path('front/images/product_images/'.$size.'/'.$image->image));
                }
            }
            $this->assertSame([250, 125], array_slice(getimagesize(public_path('front/images/product_images/small/'.$images[0]->image)), 0, 2));
            $this->assertSame([800, 400], array_slice(getimagesize(public_path('front/images/product_images/large/'.$images[0]->image)), 0, 2));
            $this->postJson('/admin/update-image-status', ['image_id' => $images[0]->id, 'status' => 'Active'], ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()->assertJsonPath('status', 0);
            $this->assertDatabaseHas('products_images', ['id' => $images[0]->id, 'status' => 0]);
            $this->postJson('/admin/update-image-status', ['image_id' => $images[0]->id, 'status' => 'Inactive'], ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()->assertJsonPath('status', 1);
            $this->post('/admin/delete-image/'.$images[0]->id)->assertRedirect();
            $this->assertDatabaseMissing('products_images', ['id' => $images[0]->id]);
            foreach (['large', 'medium', 'small'] as $size) {
                $this->assertFileDoesNotExist(public_path('front/images/product_images/'.$size.'/'.$images[0]->image));
            }
        } finally {
            foreach ($images as $image) {
                foreach (['large', 'medium', 'small'] as $size) {
                    $path = public_path('front/images/product_images/'.$size.'/'.$image->image);
                    if (is_file($path)) unlink($path);
                }
            }
        }
    }

    public function test_product_images_reject_missing_invalid_large_and_excess_files(): void
    {
        [$admin, , , , , $product] = $this->createSetup();
        $this->actingAs($admin, 'admin');
        foreach ([
            [],
            ['images' => [\Illuminate\Http\UploadedFile::fake()->createWithContent('fake.png', 'not an image')]],
            ['images' => [\Illuminate\Http\UploadedFile::fake()->image('large.png')->size(5121)]],
            ['images' => [\Illuminate\Http\UploadedFile::fake()->image('wide.png', 4097, 1)]],
            ['images' => array_fill(0, 11, \Illuminate\Http\UploadedFile::fake()->image('many.png'))],
        ] as $invalid) {
            $this->postJson('/admin/add-images/'.$product->id, $invalid)->assertUnprocessable();
            $this->assertSame(0, ProductsImage::where('product_id', $product->id)->count());
        }
    }

    public function test_product_image_batch_failure_rolls_back_records_and_files(): void
    {
        [, , , , , $product] = $this->createSetup();
        $dispatcher = ProductsImage::getEventDispatcher();
        ProductsImage::setEventDispatcher(clone $dispatcher);
        $filenames = [];
        ProductsImage::creating(function ($image) use (&$filenames) {
            $filenames[] = $image->image;
            if (count($filenames) === 2) throw new \RuntimeException('Test image storage failure');
        });
        try {
            app(\App\Services\AdminProductImageUpload::class)->store($product->id, [
                \Illuminate\Http\UploadedFile::fake()->image('one.png'),
                \Illuminate\Http\UploadedFile::fake()->image('two.png'),
            ]);
            $this->fail('The simulated failure should be propagated.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Test image storage failure', $error->getMessage());
            $this->assertSame(0, ProductsImage::where('product_id', $product->id)->count());
            foreach ($filenames as $filename) {
                foreach (['large', 'medium', 'small'] as $size) {
                    $this->assertFileDoesNotExist(public_path('front/images/product_images/'.$size.'/'.$filename));
                }
            }
        } finally {
            ProductsImage::setEventDispatcher($dispatcher);
        }
    }

    public function test_product_options_register_and_reject_duplicate_batch_without_partial_rows(): void
    {
        [$admin, , , , , $product] = $this->createSetup();
        $this->actingAs($admin, 'admin')->post('/admin/add-edit-attributes/'.$product->id, [
            'size' => ['기본', '대형'], 'sku' => ['OPTION-BASE', 'OPTION-LARGE'],
            'price' => [1200, 1500], 'stock' => [10, 0],
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/add-edit-attributes/'.$product->id);
        $this->assertDatabaseHas('products_attributes', ['product_id' => $product->id,
            'size' => '기본', 'sku' => 'OPTION-BASE', 'price' => 1200, 'stock' => 10, 'status' => 1]);
        $this->assertDatabaseHas('products_attributes', ['product_id' => $product->id,
            'size' => '대형', 'sku' => 'OPTION-LARGE', 'price' => 1500, 'stock' => 0,
            'option_type' => 'price', 'price_adjustment' => 300]);

        $this->post('/admin/add-edit-attributes/'.$product->id, [
            'size' => ['새 옵션', '중복 코드'], 'sku' => ['OPTION-NEW', 'OPTION-LARGE'],
            'price' => [100, 200], 'stock' => [1, 1],
        ])->assertSessionHasErrors('sku');
        $this->assertDatabaseMissing('products_attributes', ['sku' => 'OPTION-NEW']);
        $this->post('/admin/add-edit-attributes/'.$product->id, [
            'size' => ['새 옵션', '기본'], 'sku' => ['OPTION-NEW', 'OPTION-OTHER'],
            'price' => [100, 200], 'stock' => [1, 1],
        ])->assertSessionHasErrors('size');
        $this->assertSame(2, $product->attributes()->count());
    }

    public function test_product_options_validate_rows_and_numbers_before_writing(): void
    {
        [$admin, , , , , $product] = $this->createSetup();
        $this->actingAs($admin, 'admin');
        $valid = ['size' => ['기본', '대형'], 'sku' => ['OPT-1', 'OPT-2'],
            'price' => [100, 200], 'stock' => [1, 2]];
        foreach ([
            ['stock' => [1, -1]], ['stock' => [1, '1.5']], ['price' => [100, -2]],
            ['sku' => ['OPT-1', 'opt-1']], ['size' => ['기본', '기본']],
            ['stock' => [1]], ['price' => [1 => 100, 2 => 200]],
        ] as $invalid) {
            $this->postJson('/admin/add-edit-attributes/'.$product->id, array_replace($valid, $invalid))
                ->assertUnprocessable();
            $this->assertSame(0, $product->attributes()->count());
        }
    }

    public function test_product_option_edits_are_validated_and_atomic_with_product_scope(): void
    {
        [$admin, , , , , $product] = $this->createSetup();
        $option = ProductsAttribute::create(['product_id' => $product->id, 'size' => '기본',
            'sku' => 'EDIT-1', 'price' => 100, 'stock' => 10, 'status' => 1]);
        $otherProduct = $product->replicate();
        $otherProduct->product_code = 'OTHER-EDIT';
        $otherProduct->save();
        $other = ProductsAttribute::create(['product_id' => $otherProduct->id, 'size' => '대형',
            'sku' => 'EDIT-2', 'price' => 200, 'stock' => 20, 'status' => 1]);
        $this->actingAs($admin, 'admin')->post('/admin/edit-attributes/'.$product->id, [
            'attributeId' => [$option->id], 'price' => [150], 'stock' => [12],
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/add-edit-attributes/'.$product->id);
        $this->assertDatabaseHas('products_attributes', ['id' => $option->id, 'price' => 150, 'stock' => 12,
            'option_type' => 'price', 'price_adjustment' => -1050]);
        $this->post('/admin/edit-attributes/'.$product->id, [
            'attributeId' => [$option->id, $other->id], 'price' => [1, 1], 'stock' => [1, 1],
        ])->assertNotFound();
        $this->postJson('/admin/edit-attributes/'.$product->id, [
            'attributeId' => [$option->id], 'price' => [-1], 'stock' => [1],
        ])->assertUnprocessable();
        $this->postJson('/admin/edit-attributes/'.$product->id, [
            'attributeId' => [$option->id], 'price' => [1], 'stock' => [],
        ])->assertUnprocessable();
        $this->assertDatabaseHas('products_attributes', ['id' => $option->id, 'price' => 150, 'stock' => 12]);
        $this->assertDatabaseHas('products_attributes', ['id' => $other->id, 'price' => 200, 'stock' => 20]);

        $textOption = ProductsAttribute::create(['product_id' => $product->id, 'size' => '각인 문구',
            'sku' => 'EDIT-TEXT', 'price' => 1200, 'stock' => 20, 'status' => 1, 'option_type' => 'text']);
        $this->postJson('/admin/edit-attributes/'.$product->id, [
            'attributeId' => [$option->id, $textOption->id], 'price' => [1, 1300], 'stock' => [1, 1],
        ])->assertUnprocessable();
        $this->assertDatabaseHas('products_attributes', ['id' => $option->id, 'price' => 150, 'stock' => 12]);
        $this->post('/admin/edit-attributes/'.$product->id, [
            'attributeId' => [$textOption->id], 'price' => [1200], 'stock' => [15],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products_attributes', ['id' => $textOption->id,
            'option_type' => 'text', 'price' => 1200, 'stock' => 15]);
    }

    public function test_vendor_product_and_coupon_writes_are_scoped_to_own_records(): void
    {
        [, , $section, $category, $brand, $ownProduct, , , , , , $vendorAdmin] = $this->createSetup();

        $otherVendor = Vendor::create([
            'name' => 'Other Vendor',
            'mobile' => '010-9000-0000',
            'email' => 'other-scope@example.com',
            'status' => 1,
            'commission' => 10,
            'confirm' => 'Yes',
        ]);
        $otherProduct = Product::create([
            'section_id' => $section->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'vendor_id' => $otherVendor->id,
            'admin_id' => $vendorAdmin->id,
            'admin_type' => 'vendor',
            'product_name' => 'Other Vendor Product',
            'product_code' => 'OTHER001',
            'product_color' => 'Black',
            'product_price' => 500,
            'product_discount' => 0,
            'product_weight' => 100,
            'is_featured' => 'No',
            'status' => 1,
        ]);
        $otherAttribute = ProductsAttribute::create([
            'product_id' => $otherProduct->id,
            'size' => 'M',
            'price' => 500,
            'stock' => 3,
            'sku' => 'OTHER001-M',
            'status' => 1,
        ]);
        $otherImage = new ProductsImage;
        $otherImage->product_id = $otherProduct->id;
        $otherImage->image = 'other.png';
        $otherImage->status = 1;
        $otherImage->save();
        $otherCoupon = new Coupon;
        $otherCoupon->vendor_id = $otherVendor->id;
        $otherCoupon->coupon_option = 'Manual';
        $otherCoupon->coupon_code = 'OTHER10';
        $otherCoupon->categories = (string) $category->id;
        $otherCoupon->brands = (string) $brand->id;
        $otherCoupon->users = '';
        $otherCoupon->coupon_type = 'Multiple';
        $otherCoupon->amount_type = 'Fixed';
        $otherCoupon->amount = 10;
        $otherCoupon->expiry_date = '2026-12-31';
        $otherCoupon->status = 1;
        $otherCoupon->save();

        $this->actingAs($vendorAdmin, 'admin')
            ->get('/admin/products')
            ->assertOk()
            ->assertSee($ownProduct->product_name)
            ->assertDontSee($otherProduct->product_name);
        $this->actingAs($vendorAdmin, 'admin')->get("/admin/add-edit-product/{$otherProduct->id}")->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')->get("/admin/add-edit-attributes/{$otherProduct->id}")->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')->get("/admin/add-images/{$otherProduct->id}")->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')->post("/admin/delete-product/{$otherProduct->id}")->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')
            ->post('/admin/update-product-status', [
                'product_id' => $otherProduct->id,
                'status' => 'Active',
            ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')
            ->post('/admin/update-attribute-status', [
                'attribute_id' => $otherAttribute->id,
                'status' => 'Active',
            ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')
            ->post("/admin/edit-attributes/{$otherProduct->id}", [
                'attributeId' => [$otherAttribute->id],
                'price' => [1],
                'stock' => [1],
            ])
            ->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')
            ->post('/admin/update-image-status', [
                'image_id' => $otherImage->id,
                'status' => 'Active',
            ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')->post("/admin/delete-image/{$otherImage->id}")->assertNotFound();

        $this->actingAs($vendorAdmin, 'admin')
            ->get('/admin/coupons')
            ->assertOk()
            ->assertDontSee($otherCoupon->coupon_code);
        $this->actingAs($vendorAdmin, 'admin')->get("/admin/add-edit-coupon/{$otherCoupon->id}")->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')
            ->post('/admin/update-coupon-status', [
                'coupon_id' => $otherCoupon->id,
                'status' => 'Active',
            ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound();
        $this->actingAs($vendorAdmin, 'admin')->post("/admin/delete-coupon/{$otherCoupon->id}")->assertNotFound();

        $this->assertDatabaseHas('products', ['id' => $otherProduct->id, 'status' => 1]);
        $this->assertDatabaseHas('products_attributes', ['id' => $otherAttribute->id, 'price' => 500, 'stock' => 3, 'status' => 1]);
        $this->assertDatabaseHas('products_images', ['id' => $otherImage->id, 'status' => 1]);
        $this->assertDatabaseHas('coupons', ['id' => $otherCoupon->id, 'status' => 1]);
    }

    public function test_vendor_cannot_access_global_admin_management_routes(): void
    {
        [, , , , , , , , , , , $vendorAdmin] = $this->createSetup();

        foreach ([
            '/admin/admins',
            '/admin/sections',
            '/admin/categories',
            '/admin/brands',
            '/admin/users',
            '/admin/order-managers',
            '/admin/settlements',
            '/admin/channel-points',
        ] as $url) {
            $this->actingAs($vendorAdmin, 'admin')->get($url)->assertForbidden();
        }
    }
    public function test_vendor_edit_and_detail_resolve_company_and_bank_by_vendor_id(): void
    {
        [$admin,$vendor,,,,,,,,,, $vendorAdmin]=$this->createSetup();
        $other=Vendor::forceCreate(['name'=>'Other vendor','email'=>'other-record@example.test','mobile'=>'01000000000','status'=>1]);
        \App\Models\VendorsBusinessDetail::forceCreate(['id'=>$vendor->id+10000,'vendor_id'=>$vendor->id,'shop_name'=>'Correct company','address_proof_image'=>'qa-license.png','bank_copy_image'=>'qa-bank.png']);
        \App\Models\VendorsBusinessDetail::forceCreate(['id'=>$vendor->id,'vendor_id'=>$other->id,'shop_name'=>'Wrong company']);
        \App\Models\VendorsBankDetail::forceCreate(['id'=>$vendor->id+10000,'vendor_id'=>$vendor->id,'bank_name'=>'Correct bank','account_number'=>'QA-account','account_holder_name'=>'QA owner','bank_ifsc_code'=>'']);
        \App\Models\VendorsBankDetail::forceCreate(['id'=>$vendor->id,'vendor_id'=>$other->id,'bank_name'=>'Wrong bank','account_number'=>'Other-account','account_holder_name'=>'Other owner','bank_ifsc_code'=>'']);
        $this->actingAs($admin,'admin');
        $this->get('/admin/add-edit-admin/'.$vendorAdmin->id)->assertOk()->assertSee('Correct company')->assertSee('Correct bank')->assertDontSee('Wrong company')->assertDontSee('Wrong bank');
        $detail=$this->get('/admin/view-vendor-details/'.$vendorAdmin->id)->assertOk()->assertSee('Correct company')->assertSee('Correct bank')->assertDontSee('Wrong company')->assertDontSee('Wrong bank');
        $detail->assertSee('name="vendor_id" value="'.$vendor->id.'"',false)->assertSee(route('admin.member.document',['vendor'=>$vendor->id,'kind'=>'license']),false)->assertSee(route('admin.member.document',['vendor'=>$vendor->id,'kind'=>'bankbook']),false);
    }
    public function test_vendor_form_accepts_split_mobile_and_stores_bankbook_in_business_documents(): void
    {
        [$admin,$vendor,,,,,,,,,, $vendorAdmin]=$this->createSetup();
        $this->actingAs($admin,'admin');$storedPath=null;
        try {
            $response=$this->post('/admin/add-edit-admin/'.$vendorAdmin->id,[
                'name'=>$vendorAdmin->name,'email'=>$vendorAdmin->email,'type'=>'vendor',
                'mobile_1'=>'010','mobile_2'=>'2222','mobile_3'=>'3333','status'=>'1','seller_status'=>'1',
                'shop_name'=>'Saved company','bank_name'=>'QA bank','account_number'=>'000000','account_holder_name'=>'QA owner',
                'bank_copy_image'=>\Illuminate\Http\UploadedFile::fake()->image('bankbook.png'),
            ]);
            $response->assertSessionHasNoErrors();
            $this->assertNull(session('error_message'),(string)session('error_message'));
            $response->assertRedirect('/admin/admins');
            $business=\App\Models\VendorsBusinessDetail::where('vendor_id',$vendor->id)->firstOrFail();
            $storedPath=public_path('front/images/bank_copies/'.$business->bank_copy_image);
            $this->assertFileExists($storedPath);
            $this->assertSame('010-2222-3333',$vendorAdmin->fresh()->mobile);
            $this->assertDatabaseHas('vendors_bank_details',['vendor_id'=>$vendor->id,'bank_name'=>'QA bank','bank_ifsc_code'=>'']);
            $this->get(route('admin.member.document',['vendor'=>$vendor->id,'kind'=>'bankbook']))->assertOk()->assertDownload();
        } finally { if($storedPath && is_file($storedPath))unlink($storedPath); }
    }
}
