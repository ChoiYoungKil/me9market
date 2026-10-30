<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelProductTest extends TestCase
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

        return [$vendor, $admin, $shop, $product];
    }

    public function test_posted_channel_popup_uses_approved_product_channels_and_local_qr(): void
    {
        [, $admin, $shop, $product] = $this->createSetup();
        $shop->update(['channel_name' => '<script>listed channel</script>']);
        $listing = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id, 'product_id' => $product->id,
            'product_type' => 'own', 'status' => 1, 'approval_status' => 'approved',
            'constraint_type' => 'none', 'stock' => 10, 'product_price' => 100,
            'selling_price' => 120, 'profit' => 20,
        ]);

        $this->actingAs($admin, 'admin')->get('/channel/product/own')
            ->assertOk()->assertSee('data-id="posted-channels-'.$product->id.'"', false)
            ->assertSee('&lt;script&gt;listed channel&lt;/script&gt;', false)
            ->assertSee('data:image/png;base64,', false)
            ->assertSee(route('shop.enter', $shop->channel_code), false)
            ->assertDontSee('qr_sample1.jpg', false)->assertDontSee('a20392');

        $listing->update(['approval_status' => 'pending']);
        $this->get('/channel/product/own')->assertOk()
            ->assertSee('게시된 채널이 없습니다.')
            ->assertDontSee('data:image/png;base64,', false);
    }

    public function test_base_product_option_validation_rolls_back_and_signed_prices_persist(): void
    {
        [, $admin] = $this->createSetup();
        $section = \App\Models\Section::forceCreate(['name' => 'Section', 'status' => 1]);
        $major = \App\Models\Category::forceCreate(['section_id' => $section->id, 'parent_id' => 0,
            'category_name' => 'Major', 'category_image' => '', 'url' => 'major', 'status' => 1]);
        $middle = \App\Models\Category::forceCreate(['section_id' => $section->id, 'parent_id' => $major->id,
            'category_name' => 'Middle', 'category_image' => '', 'url' => 'middle', 'status' => 1]);
        $brand = \App\Models\Brand::forceCreate(['name' => 'Brand', 'status' => 1]);
        $payload = ['product_code' => 'SIGNED-BASE', 'product_name' => 'Signed base product',
            'major_category_id' => $major->id, 'middle_category_id' => $middle->id, 'brand_id' => $brand->id,
            'product_price' => 10000, 'sale_scope' => 'own', 'tax_type' => 'taxable',
            'price_constraint_enabled' => 0, 'purchase_limit_enabled' => 0, 'stock_usage' => 'used',
            'detail_display_type' => 'unused', 'order_manager_enabled' => 0,
            'shipping_policy_type' => 'free', 'shipping_payment_type' => 'prepaid', 'status' => 1,
            'option_values' => ['Plus', 'Minus'], 'option_names' => ['Size', 'Size'],
            'option_types' => ['price', 'price'], 'option_prices' => [1500, -15000],
            'option_stocks' => [10, 10], 'option_statuses' => [1, 1]];
        $this->actingAs($admin, 'admin')->postJson(route('channel.product.base.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('option_prices.1');
        $this->assertDatabaseMissing('products', ['product_code' => 'SIGNED-BASE']);
        $this->assertDatabaseMissing('products_attributes', ['size' => 'Plus']);
        $payload['option_prices'][1] = -500;
        $this->post(route('channel.product.base.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $product = Product::where('product_code', 'SIGNED-BASE')->firstOrFail();
        $this->assertDatabaseHas('products_attributes', ['product_id' => $product->id, 'size' => 'Minus', 'price_adjustment' => -500, 'price' => 9500]);
    }

    public function test_store_own_product()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/own/store', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'selling_price' => 120.0,
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '상품이 성공적으로 채널에 추가되었습니다.',
        ]);

        $this->assertDatabaseHas('shop_channel_products', [
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'own',
            'selling_price' => 120.0,
        ]);
    }

    public function test_store_public_product()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        // Create a product not belonging to the vendor (public base product)
        $publicProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => 999, // other vendor
            'admin_id' => 999,
            'admin_type' => 'vendor',
            'product_name' => 'Public Product',
            'product_code' => 'PUB123', 'is_public'=>'Yes',
            'product_color' => 'Red',
            'product_price' => 200.0,
            'product_discount' => 0.0,
            'product_weight' => 600,
            'status' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/public/store', [
            'shop_id' => $shop->id,
            'product_id' => $publicProduct->id,
            'selling_price' => 250.0,
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '공유 상품이 성공적으로 추가되었습니다.',
        ]);

        $this->assertDatabaseHas('shop_channel_products', [
            'shop_channel_id' => $shop->id,
            'product_id' => $publicProduct->id,
            'product_type' => 'public',
            'selling_price' => 250.0,
        ]);
    }

    public function test_common_pg_own_product_rejects_price_below_minimum_with_reward_points()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();
        $shop->update(['settlement_rate' => 5, 'use_own_pg' => false]);
        $product->update(['product_price' => 10000, 'reward_points' => 500]);

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/own/store', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'selling_price' => 10000,
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => false,
        ]);
        $this->assertStringContainsString('최소 판매가는', $response->json('message'));
    }

    public function test_fixed_shared_product_rebate_must_cover_commission_and_reward_points()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();
        $shop->update(['settlement_rate' => 5, 'use_own_pg' => false]);

        $publicProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => 999,
            'admin_id' => 999,
            'admin_type' => 'vendor',
            'product_name' => 'Fixed Shared Product',
            'product_code' => 'FIXED123', 'is_public'=>'Yes',
            'product_color' => 'Black',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 500,
            'reward_points' => 500,
            'price_constraint_enabled' => true,
            'price_constraint_type' => 'fixed',
            'price_fixed' => 10000,
            'profit_share_type' => 'fixed',
            'profit_share_value' => 600,
            'status' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/public/store', [
            'shop_id' => $shop->id,
            'product_id' => $publicProduct->id,
            'selling_price' => 10000,
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => false,
        ]);
        $this->assertStringContainsString('수수료와 지급 포인트', $response->json('message'));
    }

    public function test_store_partial_product()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        $partialProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => 999,
            'admin_id' => 999,
            'admin_type' => 'vendor',
            'product_name' => 'Partial Product',
            'product_code' => 'PART123', 'is_partial'=>'Yes',
            'product_color' => 'Green',
            'product_price' => 150.0,
            'product_discount' => 0.0,
            'product_weight' => 400,
            'status' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/partial/store', [
            'shop_id' => $shop->id,
            'product_id' => $partialProduct->id,
            'selling_price' => 180.0,
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '판매 권한 요청이 성공적으로 접수되었습니다. (승인 대기)',
        ]);

        $this->assertDatabaseHas('shop_channel_products', [
            'shop_channel_id' => $shop->id,
            'product_id' => $partialProduct->id,
            'product_type' => 'partial',
            'status' => 0,
        ]);
    }

    public function test_update_product_status()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

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

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/status/update', [
            'shop_product_id' => $shopProduct->id,
            'status' => 0,
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '판매가 중지되었습니다.',
        ]);

        $this->assertEquals(0, $shopProduct->fresh()->status);
    }

    public function test_update_shop_product()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

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

        $response = $this->actingAs($admin, 'admin')->post("/channel/shop/product/edit/{$shopProduct->id}", [
            'selling_price' => 130.0,
            'stock' => 15,
            'purchase_limit' => 5,
            'status' => 1,
        ]);

        $response->assertRedirect();
        
        $shopProduct = $shopProduct->fresh();
        $this->assertEquals(130.0, $shopProduct->selling_price);
        $this->assertEquals(15, $shopProduct->stock);
        $this->assertEquals(5, $shopProduct->purchase_limit);
    }

    public function test_delete_base_product()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        $response = $this->actingAs($admin, 'admin')->post("/channel/product/base/delete/{$product->id}", [], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '상품이 삭제되었습니다.',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_copy_base_product()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        $response = $this->actingAs($admin, 'admin')->post("/channel/product/base/copy/{$product->id}", [], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '상품이 복사되었습니다.',
        ]);

        $this->assertDatabaseHas('products', [
            'product_name' => 'Test Product (복사본)',
        ]);
    }

    public function test_update_request_status()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();

        $shopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'partial',
            'status' => 0, // 대기 상태
            'constraint_type' => 'none',
            'stock' => 10,
            'product_price' => 100.0,
            'selling_price' => 120.0,
            'profit' => 20.0,
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/channel/shop/product/request/update', [
            'request_id' => $shopProduct->id,
            'status' => 1, // 허용
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'status' => true,
            'message' => '판매 요청이 성공적으로 허용 처리되었습니다.',
        ]);

        $this->assertEquals(0, $shopProduct->fresh()->status);
        $this->assertSame('approved',$shopProduct->fresh()->approval_status);
        $this->assertNull($shopProduct->fresh()->listed_at);
    }

    public function test_own_pg_channel_blocks_shared_products_and_stores_snapshot()
    {
        list($vendor, $admin, $shop, $product) = $this->createSetup();
        $shop->forceFill([
            'use_own_pg' => true,
            'pg_provider' => 'toss',
            'pg_merchant_id' => 'merchant-1',
            'pg_client_key' => 'client-1',
            'pg_secret_key' => 'secret-1',
        ])->save();

        $publicProduct = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => 999,
            'admin_id' => 999,
            'admin_type' => 'vendor',
            'product_name' => 'Own PG Block Product',
            'product_code' => 'PG-BLOCK',
            'product_color' => 'Red',
            'product_price' => 200.0,
            'product_discount' => 0.0,
            'product_weight' => 600,
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin')->post('/channel/shop/product/public/store', [
            'shop_id' => $shop->id,
            'product_id' => $publicProduct->id,
            'selling_price' => 250.0,
        ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson([
                'status' => false,
                'message' => '자사 PG를 사용하는 Shop 채널은 자사상품만 판매할 수 있습니다.',
            ]);

        $this->actingAs($admin, 'admin')->post('/channel/shop/product/own/store', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'selling_price' => 120.0,
        ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson(['status' => true]);

        $shopProduct = ShopChannelProduct::where('shop_channel_id', $shop->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $this->assertSame(1, (int) $shopProduct->settlement_type_snapshot);
        $this->assertSame(10.0, (float) $shopProduct->settlement_rate_snapshot);
        $this->assertSame('seller', $shopProduct->price_decider);
    }

    public function test_base_product_form_renders_progressive_category_controls()
    {
        list(, $admin) = $this->createSetup();

        $this->actingAs($admin, 'admin')
            ->get(route('channel.product.base.create'))
            ->assertOk()
            ->assertSee('major_category_choices', false)
            ->assertSee('middle_category_choices', false)
            ->assertSee('minor_category_choices', false)
            ->assertSee('category-more', false);
    }
    private function baseReviewPayload(): array
    {
        $section = \App\Models\Section::forceCreate(['name'=>'Review section','status'=>1]);
        $major = \App\Models\Category::forceCreate(['section_id'=>$section->id,'parent_id'=>0,'category_name'=>'Review major','category_image'=>'','url'=>'review-major','status'=>1]);
        $brand = \App\Models\Brand::forceCreate(['name'=>'Review brand','status'=>1]);
        return ['product_code'=>'REVIEW-109','product_name'=>'Review product','major_category_id'=>$major->id,'brand_id'=>$brand->id,
            'product_price'=>10000,'sale_scope'=>'both','tax_type'=>'taxable','price_constraint_enabled'=>1,'price_constraint_type'=>'fixed',
            'price_fixed'=>1,'profit_share_type'=>'base_percent','profit_share_value'=>10,'purchase_limit_enabled'=>0,'stock_usage'=>'unused',
            'detail_display_type'=>'text','detail_text'=>'<p><strong>Safe description</strong><script>alert(1)</script></p>',
            'order_manager_enabled'=>0,'shipping_policy_type'=>'free','shipping_payment_type'=>'prepaid','status'=>0];
    }

    public function test_stop_notice_requires_three_days_and_never_automatically_stops_product(): void
    {
        [, $admin, $shop, $product] = $this->createSetup();
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-31 10:00:00','Asia/Seoul'));
        $this->actingAs($admin,'admin');
        $this->postJson('/channel/product/base/stop-notice/'.$product->id,['stop_notice_at'=>'2026-11-02'])
            ->assertUnprocessable()->assertJsonValidationErrors('stop_notice_at');
        $this->postJson('/channel/product/base/stop-notice/'.$product->id,['stop_notice_at'=>'2026-11-03'])->assertJsonPath('status',true);
        $this->assertEquals(1,$product->fresh()->status);
        ShopChannelProduct::forceCreate(['shop_channel_id'=>$shop->id,'product_id'=>$product->id,'product_type'=>'own','status'=>1,'approval_status'=>'approved','listed_at'=>now(),'product_price'=>100,'selling_price'=>120,'profit'=>20]);
        $this->postJson('/channel/product/base/delete/'.$product->id,[],['X-Requested-With'=>'XMLHttpRequest'])->assertJsonPath('status',false);
        $this->assertModelExists($product);
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-03 00:01:00','Asia/Seoul'));
        $this->postJson('/channel/product/base/delete/'.$product->id,[],['X-Requested-With'=>'XMLHttpRequest'])->assertJsonPath('status',true);
        $this->travelBack();
    }

    public function test_base_registration_supports_both_sharing_modes_and_derives_price_on_server(): void
    {
        [, $admin] = $this->createSetup();$data=$this->baseReviewPayload();$this->actingAs($admin,'admin');
        $this->postJson(route('channel.product.base.store'),$data+['reward_points'=>100])
            ->assertUnprocessable()->assertJsonValidationErrors('reward_points');
        $this->post(route('channel.product.base.store'),$data)->assertRedirect()->assertSessionHasNoErrors();
        $product=Product::where('product_code','REVIEW-109')->firstOrFail();
        $this->assertEquals('Yes',$product->is_public);$this->assertEquals('Yes',$product->is_partial);
        $this->getJson('/channel/product/base/detail/'.$product->id)->assertOk()->assertJsonPath('product.detail_html',fn($html)=>!str_contains($html,'<script')&&str_contains($html,'<strong>'));
        $this->assertEquals(11000,$product->price_fixed);$this->assertEquals(0,$product->status);$this->assertNull($product->reward_points);
        $this->assertStringNotContainsString('<script',$product->detail_text);$this->assertStringContainsString('<strong>',$product->detail_text);
        $policy=app(\App\Services\OrderSettlementPolicy::class)->capture(null,null,$product);
        $this->assertSame('fixed',$policy['profit_share_type']);$this->assertEquals(1000,$policy['profit_share_value']);
    }

    public function test_image_limit_counts_representative_and_replacement_does_not_add_sixth_image(): void
    {
        [, $admin] = $this->createSetup();$data=$this->baseReviewPayload();$this->actingAs($admin,'admin');
        $image=fn()=>\Illuminate\Http\UploadedFile::fake()->image('review.png',32,32);
        $this->postJson(route('channel.product.base.store'),$data+['slot_images'=>[$image(),$image(),$image(),$image(),$image()]])
            ->assertUnprocessable()->assertJsonValidationErrors('slot_images');
        $this->post(route('channel.product.base.store'),$data+['product_image'=>$image(),'slot_images'=>[$image(),$image(),$image(),$image()]])->assertSessionHasNoErrors();
        $product=Product::where('product_code','REVIEW-109')->firstOrFail();$files=$product->images->pluck('image')->all();
        try {
            $this->assertSame(5,$product->images()->count());
            $this->post(route('channel.product.base.update',$product->id),$data+['product_image'=>$image()])->assertSessionHasNoErrors();
            $this->assertSame(5,$product->images()->count());
        } finally {
            foreach(array_unique(array_merge($files,$product->images()->pluck('image')->all())) as $file)
                foreach(['large','medium','small'] as $size) @unlink(public_path('front/images/product_images/'.$size.'/'.$file));
        }
    }

    public function test_copy_preserves_options_and_images_but_never_copies_shop_listings(): void
    {
        [, $admin, $shop, $product] = $this->createSetup();
        \App\Models\ProductsImage::forceCreate(['product_id'=>$product->id,'image'=>'review-original.png','status'=>1]);
        \App\Models\ProductsAttribute::forceCreate(['product_id'=>$product->id,'sku'=>'REVIEW-SKU','size'=>'Small','price'=>90,'price_adjustment'=>-10,'option_name'=>'Size','option_type'=>'price','stock'=>7,'status'=>1]);
        $this->actingAs($admin,'admin')->postJson('/channel/product/base/copy/'.$product->id)->assertJsonPath('status',true);
        $copy=Product::where('id','!=',$product->id)->latest('id')->firstOrFail();
        $this->assertSame(1,$copy->images()->count());$this->assertEquals('review-original.png',$copy->images()->first()->image);
        $this->assertSame(1,$copy->attributes()->count());$this->assertEquals(-10,$copy->attributes()->first()->price_delta);
        $this->assertEquals(0,$copy->status);$this->assertNull($copy->stop_notice_at);
        $this->assertNotEquals('REVIEW-SKU',$copy->attributes()->first()->sku);$this->assertSame(0,$copy->shopChannelProducts()->count());
    }

    public function test_shared_management_lists_only_current_vendors_added_products_and_searches_actual_price(): void
    {
        [, $admin, $shop, $own] = $this->createSetup();
        $supplier=Vendor::forceCreate(['name'=>'Supplier review','email'=>'supplier-review@example.test','mobile'=>'01000000000','status'=>1,'commission'=>5]);
        $shared=$own->replicate();$shared->vendor_id=$supplier->id;$shared->product_code='SHARED-109';$shared->is_public='Yes';$shared->is_partial='Yes';
        $shared->price_constraint_enabled=1;$shared->price_constraint_type='fixed';$shared->price_fixed=8000;$shared->save();
        $this->actingAs($admin,'admin')->get('/channel/product/public')->assertOk()->assertViewHas('products',fn($rows)=>$rows->total()===0);
        $mapping=ShopChannelProduct::forceCreate(['shop_channel_id'=>$shop->id,'product_id'=>$shared->id,'product_type'=>'public','status'=>1,'approval_status'=>'approved','listed_at'=>now(),'product_price'=>100,'selling_price'=>8000,'profit'=>2000]);
        $this->get('/channel/product/public?seller=Supplier&price_min=7000&price_max=9000')->assertOk()->assertViewHas('products',fn($rows)=>$rows->total()===1);
        $this->get('/channel/product/partial')->assertOk()->assertViewHas('products',fn($rows)=>$rows->total()===0);
        $mapping->update(['product_type'=>'partial','listed_at'=>null]);
        $this->get('/channel/product/partial')->assertOk()->assertViewHas('products',fn($rows)=>$rows->total()===0);
        $mapping->update(['listed_at'=>now()]);
        $this->get('/channel/product/partial')->assertOk()->assertViewHas('products',fn($rows)=>$rows->total()===1);
    }

    public function test_only_full_administrator_can_publish_default_refund_information(): void
    {
        [, $admin] = $this->createSetup();$this->actingAs($admin,'admin');
        $this->post('/admin/refund-policies/default',['content'=>'Review default'])->assertForbidden();
        $this->assertDatabaseCount('platform_refund_policies',0);
        $admin->type='admin';$admin->vendor_id=0;$admin->save();
        $this->post('/admin/refund-policies/default',['content'=>'Review default'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Review default',\App\Models\PlatformRefundPolicy::currentContent());
    }
}
