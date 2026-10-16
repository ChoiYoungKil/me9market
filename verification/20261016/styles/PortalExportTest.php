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

class PortalExportTest extends TestCase
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


    public function test_export_portal_pages(): void
    {
        [$admin,$vendor,$section,$category,$brand,$product,$banner,$coupon,$user,$order,$subscriber,$vendorAdmin] = $this->createSetup();
        $shop = \App\Models\ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>'UIREVIEW','channel_name'=>'검수 Shop 채널','status'=>1,'is_public'=>1,'copyright'=>'Me9','keywords'=>[]]);
        \App\Models\ChannelDeliveryCharge::create(['vendor_id'=>$vendor->id,'status'=>1,'name'=>'검수 배송비 정책','shipping_type'=>'conditional','payment_type'=>'prepaid','base_fee'=>3000,'free_order_quantity'=>2]);
        \App\Models\ShopChannelProduct::create(['vendor_id'=>$vendor->id,'shop_channel_id'=>$shop->id,'product_id'=>$product->id,'product_type'=>'own','constraint_type'=>'none','stock'=>10,'profit'=>2000,'product_price'=>12000,'selling_price'=>12000,'status'=>1,'approval_status'=>'approved']);
        \App\Models\PointTransaction::create(['user_id'=>$user->id,'shop_channel_id'=>$shop->id,'points'=>200,'type'=>'earn']);
        $banner->delete();
        $manager = \App\Models\Distributor::create(['vendor_id'=>$vendor->id,'name'=>'검수 발주사','email'=>'review-distributor@example.test','password'=>bcrypt('ReviewOnly123!'),'phone'=>'01000000000','status'=>1]);
        $dir='/tmp/me9-portal-pages';
        if (!is_dir($dir)) mkdir($dir,0700,true);
        $index=[];
        $groups = [
            'admin'=>[$admin,'admin',['/admin/dashboard','/admin/users','/admin/admins','/admin/categories','/admin/products','/admin/add-edit-product/'.$product->id,'/admin/sections','/admin/brands','/admin/banners','/admin/coupons','/admin/orders','/admin/orders/'.$order->id,'/admin/order-managers','/admin/subscribers','/admin/notices','/admin/faqs','/admin/contacts','/admin/settlements','/admin/channel-points','/admin/refund-policies','/admin/shipping-charges','/admin/edit-shipping-charges/1','/admin/update-admin-details','/admin/update-admin-password']],
            'channel'=>[$vendorAdmin,'admin',['/channel','/channel/product/own','/channel/shop/list','/channel/settings/delivery','/channel/settings/refund','/channel/order/list','/channel/settlement/list','/channel/product/base/edit/'.$product->id,'/channel/shop/register','/channel/settings/points','/channel/settings/order-manager','/channel/settings/sub-accounts']],
            'member'=>[$user,'web',['/mypage/main','/mypage/points/status','/mypage/delivery','/mypage/profile','/mypage/order/list','/mypage/visited-channels','/mypage/points/history','/mypage/coupons','/mypage/cart','/mypage/wishlist']],
            'shop'=>[$user,'web',['/shop-channel/main','/shop-channel/products','/shop-channel/products/1','/shop/cart','/shop/order']],
            'distributor'=>[$user,'web',['/distributor/orders/pending','/distributor/orders/completed']],
            'public'=>[$user,'web',['/','/service','/features','/subscription-information','/member/login','/register/member','/channel/login','/admin/login','/distributor/login']],
        ];
        foreach($groups as $role=>[$actor,$guard,$paths]) {
            $this->actingAs($actor,$guard);
            $this->withSession(['shop_channel_id'=>$shop->id,'shop_channel_cart'=>[1=>['qty'=>2]],'distributor_id'=>$manager->id]);
            foreach($paths as $path) {
                if ($path === '/distributor/login') { session()->forget('distributor_id'); }
                $response=$this->get($path);
                $filename=$role.'-'.substr(sha1($path),0,10).'.html';
                file_put_contents($dir.'/'.$filename,$response->getContent());
                $index[]=['role'=>$role,'path'=>$path,'status'=>$response->status(),'file'=>$filename];
            }
            auth($guard)->logout();
        }
        $response = $this->get('/register');
        file_put_contents($dir.'/generic-register.html', $response->getContent());
        $index[] = ['role'=>'public','path'=>'/register','status'=>$response->status(),'file'=>'generic-register.html'];
        file_put_contents($dir.'/index.json',json_encode($index,JSON_PRETTY_PRINT));
        $this->assertTrue(true);
    }
}
