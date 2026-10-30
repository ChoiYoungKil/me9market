<?php

namespace Tests\Feature;

use App\Models\{Admin, Category, ChannelSubAccount, Order, OrdersProduct, Product, ShopChannel, Vendor, VendorsBusinessDetail};
use App\Services\{ChannelDashboard, ShopChannelRuntime};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function setupSeller(string $name = '판매자69'): array
    {
        $vendor = Vendor::create(['name'=>$name,'mobile'=>'01000000000','email'=>$name.'@example.test','status'=>1,'commission'=>0]);
        $admin = Admin::forceCreate(['name'=>'담당자','vendor_id'=>$vendor->id,'email'=>$name.'-admin@example.test',
            'mobile'=>'01000000000','type'=>'vendor','status'=>1,'password'=>bcrypt('test69'),'confirm'=>'Yes']);
        $shop = ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>'shop69-'.$vendor->id,'channel_name'=>$name.'채널',
            'status'=>1,'is_public'=>1,'copyright'=>'Me9','keywords'=>[],'use_period_type'=>0]);
        $category = Category::forceCreate(['parent_id'=>0,'section_id'=>1,'category_name'=>$name.'품목','category_image'=>'','url'=>'cat69-'.$vendor->id]);
        $product = Product::forceCreate(['section_id'=>1,'category_id'=>$category->id,'brand_id'=>1,'vendor_id'=>$vendor->id,
            'admin_id'=>$admin->id,'admin_type'=>'vendor','product_name'=>$name.'상품','product_code'=>'P69-'.$vendor->id,
            'product_color'=>'Blue','product_price'=>100,'product_discount'=>0,'product_weight'=>1,'is_featured'=>'No','is_bestseller'=>'No','status'=>1]);
        return [$vendor,$admin,$shop,$product];
    }

    private function item(Vendor $vendor, ShopChannel $shop, Product $product, string $date, array $extra = [], ?Order $order = null): OrdersProduct
    {
        $order ??= Order::forceCreate(['user_id'=>0,'name'=>'주문자69','buyer_name'=>'주문자69','address'=>'검수주소','city'=>'서울',
            'state'=>'서울','country'=>'KR','pincode'=>'04524','mobile'=>'01000000000','email'=>'buyer69@example.test',
            'shipping_charges'=>0,'order_status'=>'Payment Captured','payment_method'=>'Card','payment_gateway'=>'mock',
            'grand_total'=>100,'created_at'=>$date,'updated_at'=>$date]);
        return OrdersProduct::forceCreate($extra + ['vendor_id'=>$vendor->id,'shop_channel_id'=>$shop->id,'product_id'=>$product->id,
            'order_id'=>$order->id,'user_id'=>0,'admin_id'=>0,'product_code'=>$product->product_code,'product_name'=>$product->product_name,
            'product_color'=>'Blue','product_size'=>'기본','product_price'=>100,'product_qty'=>1,'line_total'=>100,
            'item_status'=>'결제완료','status_code'=>'paid','created_at'=>$date,'updated_at'=>$date]);
    }

    public function test_fifteen_day_counts_deduplicate_orders_and_charts_exclude_refunds_and_replacements(): void
    {
        $this->travelTo(now()->setDate(2026,10,31)->setTime(12,0));
        [$vendor,$admin,$shop,$product] = $this->setupSeller();
        $first = $this->item($vendor,$shop,$product,'2026-10-17 00:00:00',['product_qty'=>2]);
        $this->item($vendor,$shop,$product,'2026-10-17 00:00:00',['product_qty'=>3],$first->order);
        $this->item($vendor,$shop,$product,'2026-10-16 23:59:59',['product_qty'=>7]);
        $this->item($vendor,$shop,$product,'2026-10-31 12:00:00',['status_code'=>'cancelled','item_status'=>'취소완료','product_qty'=>10]);
        $this->item($vendor,$shop,$product,'2026-10-31 12:00:00',['status_code'=>'','item_status'=>'Returned','product_qty'=>11]);
        $this->item($vendor,$shop,$product,'2026-10-31 12:00:00',['is_exchange_replacement'=>true,'product_qty'=>12]);
        $this->item($vendor,$shop,$product,'2026-11-01 00:00:00',['product_qty'=>99]);
        $data = app(ChannelDashboard::class)->data($admin);
        $this->assertSame(4,$data['counts']['total']);
        $this->assertSame(2,$data['counts']['paid']);
        $this->assertSame([5],$data['shopSales']['data']->all());
        $this->assertSame(12,$data['categorySeries'][0]['data'][9]);
        $this->assertSame(0,$data['categorySeries'][0]['data'][10]);
        $this->get('/channel')->assertRedirect(route('channel.login'));
    }

    public function test_shared_sales_are_visible_to_supplier_and_channel_owner_without_unrelated_vendor_data(): void
    {
        [$vendor,$admin,$shop,$product] = $this->setupSeller();
        [$other,$otherAdmin,$otherShop,$otherProduct] = $this->setupSeller('다른판매자');
        $this->item($other,$otherShop,$product,now()->toDateTimeString(),['product_qty'=>3]);
        $this->item($other,$otherShop,$otherProduct,now()->toDateTimeString(),['product_qty'=>9]);
        $data = app(ChannelDashboard::class)->data($admin);
        $this->assertSame(1,$data['counts']['total']);
        $this->assertSame([3],$data['shopSales']['data']->all());
        $this->assertCount(1,$data['recentOrders']);
        $this->assertSame($product->product_name,$data['recentOrders']->first()->orders_products->first()->product_name);
        $this->assertSame(2,app(ChannelDashboard::class)->data($otherAdmin)['counts']['total']);
    }

    public function test_company_approval_comes_from_vendor_and_shortcuts_are_persistent_and_account_specific(): void
    {
        [$vendor,$admin] = $this->setupSeller();
        VendorsBusinessDetail::forceCreate(['vendor_id'=>$vendor->id,'shop_name'=>'실제 회원사명','shop_email'=>'company69@example.test']);
        $vendor->update(['status'=>0]);
        $this->actingAs($admin,'admin')->get('/channel')->assertOk()->assertSee('실제 회원사명')->assertSee('회원사 등록')->assertDontSee('판매인증 완료');
        $this->post('/channel/dashboard/shortcuts',['shortcuts'=>['orders','orders','inquiries']])->assertRedirect(route('channel.index'));
        $this->assertSame(['orders','inquiries'],$admin->fresh()->channel_shortcuts);
        $this->post('/channel/dashboard/shortcuts',['shortcuts'=>['https://outside.example']])->assertSessionHasErrors('shortcuts.0');
        [, $other] = $this->setupSeller('별도회원사');
        $this->assertNull($other->channel_shortcuts);
        $this->actingAs($admin,'admin')->post('/channel/dashboard/shortcuts',[])->assertRedirect();
        $this->assertSame([],$admin->fresh()->channel_shortcuts);
    }

    public function test_only_operating_channels_are_listed_and_private_owner_open_does_not_grant_public_access(): void
    {
        [$vendor,$admin,$shop] = $this->setupSeller();
        $shop->update(['is_public'=>0]);
        $paused = ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>'paused69','channel_name'=>'중지채널69','status'=>0,'keywords'=>[],'copyright'=>'Me9']);
        $future = ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>'future69','channel_name'=>'예약채널69','status'=>1,'keywords'=>[],'copyright'=>'Me9',
            'use_period_type'=>1,'start_at'=>now()->addDay()]);
        $this->actingAs($admin,'admin')->get('/channel')->assertOk()->assertSee($shop->channel_name)
            ->assertDontSee('title="중지채널69 새 창')->assertDontSee('title="예약채널69 새 창')
            ->assertSee('target="_blank"',false);
        $this->get(route('channel.shop.open',$paused->id))->assertNotFound();
        $this->get(route('channel.shop.open',$future->id))->assertNotFound();
        $this->get(route('channel.shop.open',$shop->id))->assertRedirect(route('shop.channel_main'));
        $this->assertTrue(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
        [, $otherAdmin, $otherShop] = $this->setupSeller('외부회원사');
        $this->get(route('channel.shop.open',$otherShop->id))->assertNotFound();
        $this->actingAs($otherAdmin,'admin');
        $this->assertFalse(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
    }

    public function test_subadmin_dashboard_and_shortcuts_respect_assigned_permissions(): void
    {
        [$vendor,$admin,$shop,$product] = $this->setupSeller();
        $admin->forceFill(['type'=>'subadmin'])->save();
        ChannelSubAccount::create(['vendor_id'=>$vendor->id,'admin_id'=>$admin->id,'member_no'=>'M69','permissions'=>['product']]);
        $this->item($vendor,$shop,$product,now()->toDateTimeString());
        $this->actingAs($admin,'admin')->get('/channel')->assertOk()->assertDontSee('id="dashboard-shop-chart"',false)
            ->assertDontSee('주문자69');
        $data = app(ChannelDashboard::class)->data($admin);
        $this->assertFalse($data['canViewOrders']);
        $this->assertSame(['products','public'],array_keys($data['shortcuts']));
        $this->post('/channel/dashboard/shortcuts',['shortcuts'=>['orders']])->assertSessionHasErrors('shortcuts.0');
        $this->get(route('channel.shop.open',$shop->id))->assertForbidden();
    }

    public function test_dashboard_links_include_claims_and_shared_orders_and_exact_order_filter(): void
    {
        [$vendor,$admin,$shop,$product] = $this->setupSeller();
        [$other,,$otherShop,$otherProduct] = $this->setupSeller('다른판매자');
        \App\Models\ShopChannelProduct::create(['shop_channel_id'=>$shop->id,'product_id'=>$otherProduct->id,
            'product_type'=>'public','status'=>1,'product_price'=>100,'selling_price'=>100,'profit'=>0]);
        $paid = $this->item($other,$otherShop,$product,now()->toDateTimeString());
        $cancelled = $this->item($vendor,$shop,$product,now()->toDateTimeString(),['status_code'=>'cancelled','item_status'=>'취소완료']);
        $this->item($other,$otherShop,$otherProduct,now()->toDateTimeString());
        $this->actingAs($admin,'admin')->get('/channel/order/list?dashboard=1&order_type=all')
            ->assertOk()->assertViewHas('orders',fn ($orders) => $orders->total() === 2);
        $this->get('/channel/order/list?dashboard=1&order_type=all&order_id='.$cancelled->order_id)
            ->assertOk()->assertViewHas('orders',fn ($orders) => $orders->total() === 1 && $orders->first()->id === $cancelled->order_id);
        $this->get('/channel/order/list?dashboard=1&order_type=all&order_statuses[]=paid')
            ->assertOk()->assertViewHas('orders',fn ($orders) => $orders->total() === 1 && $orders->first()->id === $paid->order_id);
    }
}
