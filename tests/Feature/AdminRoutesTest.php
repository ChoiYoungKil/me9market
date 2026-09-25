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
        $this->actingAs($admin, 'admin')->get('/admin/notices')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/faqs')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/contacts')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/coupons')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/users')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/orders')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/order-managers')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('/admin/subscribers')->assertStatus(200);

        // Sub-routes and slugs (using vendor admin to avoid toArray() on null vendor_id)
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/personal')->assertStatus(200);
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/business')->assertStatus(200);
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/bank')->assertStatus(200);
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
        ])->assertRedirect();

        $this->assertDatabaseHas('orders_products', [
            'id' => $ownItem->id,
            'item_status' => 'Shipped',
        ]);
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
}
