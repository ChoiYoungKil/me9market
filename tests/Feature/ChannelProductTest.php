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

        return [$vendor, $admin, $shop, $product];
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
            'product_code' => 'PUB123',
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
            'product_code' => 'PART123',
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

        $this->assertEquals(1, $shopProduct->fresh()->status);
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
}
