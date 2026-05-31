<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Section;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Order;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        list($admin, $vendor, $section, $category, $brand, $product, $banner, $coupon, $user, $order, $subscriber, $vendorAdmin) = $this->createSetup();

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
        $this->actingAs($admin, 'admin')->get('/admin/subscribers')->assertStatus(200);

        // Sub-routes and slugs (using vendor admin to avoid toArray() on null vendor_id)
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/personal')->assertStatus(200);
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/business')->assertStatus(200);
        $this->actingAs($vendorAdmin, 'admin')->get('/admin/update-vendor-details/bank')->assertStatus(200);
    }
}
