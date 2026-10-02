<?php

namespace Tests\Feature;

use App\Mail\ShopOrderConfirmation;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelNotice;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ChannelPointService;
use App\Services\SettlementCalculator;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShopRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['shop_channel.seed_demo_data' => false]);
    }

    public function test_signed_option_deltas_are_charged_and_frozen_per_order_line(): void
    {
        Mail::fake();
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('delta', 'DELTA', 'Delta');
        foreach (['Plus' => 1500, 'Minus' => -500] as $name => $delta) {
            \App\Models\ProductsAttribute::create(['product_id' => $product->id, 'size' => $name,
                'sku' => 'DELTA-'.$name, 'price' => 12000 + $delta, 'price_adjustment' => $delta,
                'stock' => 10, 'status' => 1, 'option_type' => 'price']);
        }
        $this->withSession(['shop_channel_id' => $shop->id])->postJson(route('front.shop.cart.add'), [
            'shop_product_id' => $shopProduct->id,
            'options' => [['option' => 'Plus', 'qty' => 1], ['option' => 'Minus', 'qty' => 2]],
        ])->assertRedirect();
        $this->get(route('front.shop.cart.index'))->assertOk()->assertSee('13,500')->assertSee('23,000');
        $this->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect(route('front.shop.order.complete'));
        $order = Order::latest('id')->firstOrFail();
        $this->assertSame(36500.0, (float) $order->grand_total);
        $items = $order->orders_products->keyBy('product_size');
        $this->assertSame(1500.0, (float) $items['Plus']->option_price_adjustment);
        $this->assertSame(-500.0, (float) $items['Minus']->option_price_adjustment);
        \App\Models\ProductsAttribute::where('product_id', $product->id)->update(['price_adjustment' => 9000]);
        $this->assertSame(13500.0, (float) $items['Plus']->fresh()->paid_line_total_snapshot);
        $this->assertSame(23000.0, (float) $items['Minus']->fresh()->paid_line_total_snapshot);
    }

    public function test_half_star_confirmation_is_optional_and_rejects_invalid_precision(): void
    {
        [, , $shop, $product] = $this->createShopProduct('half-star', 'HALF-STAR', 'Half Star');
        $order = $this->createOrderForShop($shop, $product, 'Star Buyer');
        $item = $order->orders_products->first();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $this->withSession(['shop_channel_id' => $shop->id, 'nonmember_order_id' => $order->id]);
        $url = route('front.shop.order.item.status', $item->id);
        $this->postJson($url, ['action' => 'confirm', 'rating' => 4.3])->assertUnprocessable();
        $this->post($url, ['action' => 'confirm', 'rating' => 4.5])->assertRedirect();
        $this->assertDatabaseHas('ratings', ['product_id' => $product->id, 'rating' => 4.5, 'review' => '', 'status' => 0]);
    }

    public function test_monitor_login_is_read_only_shop_scoped_and_revoked_on_password_change(): void
    {
        [, , $shop, $product] = $this->createShopProduct('monitor', 'MONITOR', 'Monitor');
        [, , $other, $otherProduct] = $this->createShopProduct('monitor-other', 'MONITOR-OTHER', 'Hidden');
        $shop->update(['use_admin' => 1, 'admin_login_id' => 'monitor-login', 'admin_password' => bcrypt('monitor-password')]);
        $this->createOrderForShop($shop, $product, 'Private Buyer');
        $this->createOrderForShop($other, $otherProduct, 'Hidden Buyer');
        $this->get(route('shop.monitor.index'))->assertRedirect(route('shop.monitor.login'));
        $this->post(route('shop.monitor.login.submit'), ['login_id' => 'monitor-login', 'password' => 'wrong'])
            ->assertSessionHasErrors('login_id');
        $this->post(route('shop.monitor.login.submit'), ['login_id' => 'monitor-login', 'password' => 'monitor-password'])
            ->assertRedirect(route('shop.monitor.index'));
        $this->get(route('shop.monitor.index'))->assertOk()->assertSee('Monitor Product')->assertDontSee('Hidden Product')
            ->assertDontSee('Private Buyer');
        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->assertArrayNotHasKey('admin_password', $shop->toArray());
        $shop->update(['admin_password' => bcrypt('changed-password')]);
        $this->get(route('shop.monitor.index'))->assertRedirect(route('shop.monitor.login'));
    }

    public function test_expired_and_unhashed_monitor_accounts_cannot_login(): void
    {
        [, , $shop] = $this->createShopProduct('monitor-expired', 'MONITOR-EXPIRED', 'Expired');
        $shop->update(['use_admin' => 1, 'admin_login_id' => 'expired', 'admin_password' => 'legacy-plaintext']);
        $payload = ['login_id' => 'expired', 'password' => 'legacy-plaintext'];
        $this->post(route('shop.monitor.login.submit'), $payload)->assertSessionHasErrors('login_id');
        $shop->update(['admin_password' => bcrypt('legacy-plaintext'), 'use_period_type' => 1, 'end_at' => now()->subDay()]);
        $this->post(route('shop.monitor.login.submit'), $payload)->assertSessionHasErrors('login_id');
        $this->get(route('shop.monitor.index'))->assertRedirect(route('shop.monitor.login'));
    }

    public function test_member_review_requires_owned_confirmed_purchase_and_valid_half_stars(): void
    {
        [, , $shop, $product] = $this->createShopProduct('member-review', 'MEMBER-REVIEW', 'Member Review');
        $user = User::factory()->create();
        $order = $this->createOrderForShop($shop, $product, 'Review Buyer');
        $order->forceFill(['user_id' => $user->id])->save();
        $item = $order->orders_products->first();
        $item->update(['user_id' => $user->id, 'status_code' => OrderItemStatus::CONFIRMED]);
        $payload = ['product_id' => $product->id, 'rating' => 3.5, 'review' => 'Verified half-star review'];
        $this->actingAs(User::factory()->create())->post(route('front.rating.add'), $payload)->assertForbidden();
        $this->actingAs($user)->postJson(route('front.rating.add'), array_merge($payload, ['rating' => 6]))->assertUnprocessable();
        $this->post(route('front.rating.add'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ratings', ['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 3.5, 'status' => 0]);
        $this->post(route('front.rating.add'), $payload)->assertRedirect();
        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_guest_invoice_renders_real_totals_and_customer_pdf_without_admin_login(): void
    {
        [, $admin, $shop, $product] = $this->createShopProduct('invoice-render', 'INV-RENDER', 'Invoice Render');
        $order = $this->createOrderForShop($shop, $product, '<script>alert(1)</script>');
        $order->forceFill(['shipping_charges' => 3000, 'used_point' => 1000, 'grand_total' => 14000])->save();
        $this->actingAs($admin, 'admin')->get('/admin/orders/invoice/'.$order->id)->assertOk()
            ->assertSee('3,000')->assertSee('1,000')->assertSee('14,000')->assertDontSee('INR')
            ->assertDontSee('<script>alert(1)</script>', false);
        \Illuminate\Support\Facades\Auth::guard('admin')->logout();
        $response = $this->withSession(['nonmember_order_id' => $order->id])->get('/orders/invoice/download/'.$order->id);
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_ordered_stock_option_cannot_be_deleted_before_refund(): void
    {
        [$vendor, $admin, $shop, $product, , $user, $order] = $this->checkoutWithPoints(false, true);
        $item = $order->orders_products->first();
        $attribute = \App\Models\ProductsAttribute::findOrFail($item->stock_attribute_id);
        $this->actingAs($admin, 'admin')->postJson('/admin/delete-attribute/'.$attribute->id)->assertUnprocessable();
        $this->assertDatabaseHas('products_attributes', ['id' => $attribute->id]);
        $this->postJson('/channel/order/cancel/request', ['order_id' => $order->id, 'item_ids' => [$item->id],
            'reason' => 'Cancel', 'detail_reason' => 'Return stock'])->assertOk();
        $this->assertSame((int) $attribute->stock + (int) $item->attribute_stock_deducted_qty, (int) $attribute->fresh()->stock);
    }

    public function test_vendor_cannot_read_another_vendors_html_or_pdf_invoice(): void
    {
        [, $admin] = $this->createShopProduct('invoice-owner', 'INV-OWNER', 'Invoice Owner');
        [, , $other, $product] = $this->createShopProduct('invoice-hidden', 'INV-HIDDEN', 'Invoice Hidden');
        $order = $this->createOrderForShop($other, $product, 'Hidden Buyer');
        $this->actingAs($admin, 'admin')->get('/admin/orders/invoice/'.$order->id)->assertNotFound();
        $this->get('/admin/orders/invoice/pdf/'.$order->id)->assertNotFound();
    }

    private function createShopProduct(string $channelCode, string $productCode, string $customerPrefix): array
    {
        $vendor = Vendor::create([
            'name' => $customerPrefix.' Vendor',
            'mobile' => '010-0000-0000',
            'email' => strtolower($customerPrefix).'-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);

        $admin = new Admin;
        $admin->name = $customerPrefix.' Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '010-0000-0000';
        $admin->email = strtolower($customerPrefix).'-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $shop = ShopChannel::create([
            'vendor_id' => $vendor->id,
            'channel_code' => $channelCode,
            'channel_name' => $customerPrefix.' Channel',
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
            'product_name' => $customerPrefix.' Product',
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
        $order->address = $customerName.' Address';
        $order->city = 'Seoul';
        $order->state = 'Jung';
        $order->country = 'Korea';
        $order->pincode = '04524';
        $order->mobile = '010-1111-2222';
        $order->email = strtolower(str_replace(' ', '-', $customerName)).'@example.com';
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
        [, , $currentShop, , $currentShopProduct] = $this->createShopProduct('current-shop', 'CUR-001', 'Current');
        [, , , , $otherShopProduct] = $this->createShopProduct('other-shop', 'OTH-001', 'Other');

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

    public function test_shop_cart_item_can_be_updated_and_is_scoped_to_current_channel()
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('cart-update', 'CART-UP-001', 'Cart Update');
        [, , , , $otherProduct] = $this->createShopProduct('other-cart', 'CART-UP-002', 'Other Cart');
        $session = [
            'shop_channel_id' => $shop->id,
            'shop_channel_cart' => [$shopProduct->id => ['qty' => 1, 'option' => '기본옵션']],
        ];

        $this->withSession($session)->post(route('front.shop.cart.update'), [
            'shop_product_id' => $shopProduct->id,
            'qty' => 3,
            'option' => '검정 / L',
        ])->assertRedirect();

        $this->assertSame(3, session('shop_channel_cart')[$shopProduct->id]['qty']);
        $this->assertSame('검정 / L', session('shop_channel_cart')[$shopProduct->id]['option']);

        $this->withSession($session)->post(route('front.shop.cart.update'), [
            'shop_product_id' => $otherProduct->id,
            'qty' => 2,
            'option' => '기본옵션',
        ])->assertNotFound();
    }

    public function test_checkout_decrements_locked_stock_and_creates_order()
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('stock-shop', 'STOCK-001', 'Stock');
        $shopProduct->update(['stock' => 5, 'purchase_limit' => 4]);

        $this->withSession([
            'shop_channel_id' => $shop->id,
            'shop_channel_cart' => [$shopProduct->id => ['qty' => 3, 'option' => '기본옵션']],
        ])->post(route('front.shop.order.checkout'), [
            'order_confirmed' => '1',
            'name' => '구매자',
            'mobile' => '01012345678',
            'email' => 'buyer@example.com',
            'pincode' => '04524',
            'address' => '서울특별시 중구 세종대로 110',
        ])->assertRedirect(route('front.shop.order.complete'));

        $this->assertSame(2, (int) $shopProduct->fresh()->stock);
        $this->assertDatabaseHas('orders_products', [
            'shop_channel_product_id' => $shopProduct->id,
            'product_qty' => 3,
        ]);
        Mail::assertQueued(ShopOrderConfirmation::class);
    }

    public function test_checkout_rejects_insufficient_stock_without_creating_order()
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('soldout-shop', 'STOCK-002', 'Soldout');
        $shopProduct->update(['stock' => 1]);

        $this->withSession([
            'shop_channel_id' => $shop->id,
            'shop_channel_cart' => [$shopProduct->id => ['qty' => 2, 'option' => '기본옵션']],
        ])->from(route('front.shop.order.form'))->post(route('front.shop.order.checkout'), [
            'order_confirmed' => '1',
            'name' => '구매자',
            'mobile' => '01012345678',
            'email' => 'buyer@example.com',
            'pincode' => '04524',
            'address' => '서울특별시 중구 세종대로 110',
        ])->assertRedirect(route('front.shop.order.form'))
            ->assertSessionHasErrors('qty');

        $this->assertSame(1, (int) $shopProduct->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_cart_rejects_purchase_limit_excess()
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('limit-shop', 'LIMIT-001', 'Limit');
        $shopProduct->update(['purchase_limit' => 2]);

        $this->withSession(['shop_channel_id' => $shop->id])
            ->from(route('front.shop.cart.index'))
            ->post(route('front.shop.cart.add'), [
                'shop_product_id' => $shopProduct->id,
                'qty' => 3,
            ])->assertRedirect(route('front.shop.cart.index'))
            ->assertSessionHasErrors('qty');

        $this->assertSame([], session('shop_channel_cart', []));
    }

    public function test_shop_notice_detail_is_scoped_to_current_channel_and_counts_views()
    {
        [, , $shop] = $this->createShopProduct('notice-shop', 'NOTICE-001', 'Notice');
        [, , $otherShop] = $this->createShopProduct('other-notice', 'NOTICE-002', 'Other Notice');
        $notice = ShopChannelNotice::create([
            'shop_channel_id' => $shop->id,
            'type' => 'notice',
            'title' => '배송 일정 공지',
            'author' => '관리자',
            'content' => '배송 일정 상세 내용입니다.',
            'status' => 1,
            'view_count' => 0,
        ]);
        $otherNotice = ShopChannelNotice::create([
            'shop_channel_id' => $otherShop->id,
            'type' => 'notice',
            'title' => '다른 채널 공지',
            'author' => '관리자',
            'content' => '노출되면 안 됩니다.',
            'status' => 1,
            'view_count' => 0,
        ]);

        $this->withSession(['shop_channel_id' => $shop->id])
            ->get(route('shop.notices.show', $notice->id))
            ->assertOk()
            ->assertSee('배송 일정 상세 내용입니다.');
        $this->assertSame(1, $notice->fresh()->view_count);

        $this->withSession(['shop_channel_id' => $shop->id])
            ->get(route('shop.notices.show', $otherNotice->id))
            ->assertNotFound();
    }

    public function test_shop_order_details_do_not_expose_other_channel_orders()
    {
        [, , $currentShop, $currentProduct] = $this->createShopProduct('current-shop', 'CUR-002', 'Current');
        [, , $otherShop, $otherProduct] = $this->createShopProduct('other-shop', 'OTH-002', 'Other');

        $otherOrder = $this->createOrderForShop($otherShop, $otherProduct, 'Other Customer');
        $currentOrder = $this->createOrderForShop($currentShop, $currentProduct, 'Current Customer');

        $this->withSession([
            'shop_channel_id' => $currentShop->id,
            'nonmember_order_id' => $currentOrder->id,
        ])
            ->get(route('front.shop.order.details', ['id' => $otherOrder->id]))
            ->assertOk()
            ->assertSee('Current Customer')
            ->assertDontSee('Other Customer')
            ->assertDontSee('other-customer@example.com');
    }

    public function test_shop_order_details_can_open_an_order_outside_the_first_page()
    {
        [, , $shop, $product] = $this->createShopProduct('many-orders', 'MANY-001', 'Many');
        $user = User::factory()->create();
        $oldestOrder = null;

        for ($index = 0; $index < 11; $index++) {
            $order = $this->createOrderForShop($shop, $product, 'Customer '.($index + 1));
            $order->forceFill([
                'user_id' => $user->id,
                'created_at' => now()->subMinutes(20 - $index),
            ])->save();
            $order->orders_products()->update(['user_id' => $user->id]);
            $oldestOrder ??= $order;
        }

        $this->actingAs($user)
            ->withSession(['shop_channel_id' => $shop->id])
            ->get(route('front.shop.order.details', ['id' => $oldestOrder->id]))
            ->assertOk()
            ->assertSee('Customer 1');
    }

    public function test_shop_product_details_do_not_fallback_to_another_product()
    {
        [, , $currentShop, , $currentShopProduct] = $this->createShopProduct('current-shop', 'CUR-PROD', 'Current');
        [, , , , $otherShopProduct] = $this->createShopProduct('other-shop', 'OTH-PROD', 'Other');

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
        [, , $shop, $product] = $this->createShopProduct('invoice-shop', 'INV-001', 'Invoice');
        $order = $this->createOrderForShop($shop, $product, 'Invoice Customer');

        $this->get("orders/invoice/download/{$order->id}")
            ->assertForbidden();
    }

    public function test_shop_joint_purchases_are_scoped_to_current_channel()
    {
        [, , $currentShop, $currentProduct] = $this->createShopProduct('current-shop', 'CUR-JOINT', 'Current');
        [, , , $otherProduct] = $this->createShopProduct('other-shop', 'OTH-JOINT', 'Other');

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

    public function test_manual_return_uses_matched_distributor_address_and_accepts_shipment_later()
    {
        [$vendor, , $shop, $product] = $this->createShopProduct('claim-shop', 'CLAIM-001', 'Claim');
        $distributor = Distributor::create([
            'vendor_id' => $vendor->id,
            'name' => 'Claim Distributor',
            'email' => 'claim-distributor@example.com',
            'password' => bcrypt('password'),
            'return_postcode' => '06236',
            'return_address' => '서울특별시 강남구 테헤란로 123 반품센터',
            'status' => 1,
        ]);
        $order = $this->createOrderForShop($shop, $product, 'Claim Customer');
        $item = $order->orders_products()->firstOrFail();
        $item->update([
            'distributor_id' => $distributor->id,
            'status_code' => OrderItemStatus::SHIPPING,
            'item_status' => '배송중',
        ]);

        $this->withSession(['shop_channel_id' => $shop->id, 'nonmember_order_id' => $order->id])
            ->post(route('front.shop.order.item.status', $item->id), [
                'action' => 'return',
                'reason' => '상품이 파손되었습니다.',
                'pickup_method' => 'manual',
            ])
            ->assertRedirect();

        $claim = OrderClaim::where('order_product_id', $item->id)->firstOrFail();
        $this->assertSame('manual', $claim->pickup_method);
        $this->assertSame('06236 서울특별시 강남구 테헤란로 123 반품센터', $claim->return_address);
        $this->assertSame(OrderItemStatus::RETURN_REQUESTED, $item->fresh()->status_code);

        $this->withSession(['shop_channel_id' => $shop->id, 'nonmember_order_id' => $order->id])
            ->post(route('front.shop.order.claim.shipment', $claim->id), [
                'customer_courier_name' => 'CJ대한통운',
                'customer_tracking_number' => '1234567890',
            ])
            ->assertRedirect();

        $claim->refresh();
        $this->assertSame('CJ대한통운', $claim->customer_courier_name);
        $this->assertSame('1234567890', $claim->customer_tracking_number);
        $this->assertNotNull($claim->customer_shipped_at);
    }

    public function test_customer_actions_reject_invalid_order_status_transitions()
    {
        [, , $shop, $product] = $this->createShopProduct('transition-shop', 'TRANSITION-001', 'Transition');
        $order = $this->createOrderForShop($shop, $product, 'Transition Customer');
        $item = $order->orders_products()->firstOrFail();

        $this->withSession(['shop_channel_id' => $shop->id, 'nonmember_order_id' => $order->id])
            ->post(route('front.shop.order.item.status', $item->id), [
                'action' => 'confirm',
            ])
            ->assertSessionHasErrors('action');

        $this->assertSame(OrderItemStatus::PAID, $item->fresh()->status_code);
        $this->assertDatabaseMissing('order_claims', ['order_product_id' => $item->id]);
    }

    public function test_nonmember_actions_require_verified_order_session_and_store_manual_return_fields()
    {
        [$vendor, , $shop, $product] = $this->createShopProduct('guest-claim', 'GUEST-CLAIM-001', 'Guest Claim');
        $distributor = Distributor::create([
            'vendor_id' => $vendor->id,
            'name' => 'Guest Claim Distributor',
            'email' => 'guest-claim-distributor@example.com',
            'password' => bcrypt('password'),
            'return_postcode' => '04168',
            'return_address' => '서울특별시 마포구 반송로 10',
            'status' => 1,
        ]);
        $order = $this->createOrderForShop($shop, $product, 'Verified Guest');
        $item = $order->orders_products()->firstOrFail();
        $item->update([
            'distributor_id' => $distributor->id,
            'status_code' => OrderItemStatus::SHIPPING,
            'item_status' => '배송중',
        ]);

        $payload = [
            'order_id' => $order->id,
            'order_product_id' => $item->id,
            'type' => 'return',
            'reason' => '상품 파손',
            'recovery_method' => '수동회수',
            'customer_courier_name' => '우체국택배',
            'customer_tracking_number' => '9876543210',
        ];
        $this->post(route('front.nonmember.order_claim.submit'), $payload)->assertForbidden();

        $this->withSession(['nonmember_order_id' => $order->id])
            ->post(route('front.nonmember.order_claim.submit'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('order_claims', [
            'order_product_id' => $item->id,
            'pickup_method' => 'manual',
            'return_address' => '04168 서울특별시 마포구 반송로 10',
            'customer_courier_name' => '우체국택배',
            'customer_tracking_number' => '9876543210',
        ]);
    }

    public function test_member_manual_return_uses_supplier_address_instead_of_customer_address()
    {
        [$vendor, , $shop, $product] = $this->createShopProduct('member-claim', 'MEMBER-CLAIM-001', 'Member Claim');
        $user = User::factory()->create(['address' => '고객 자택 주소']);
        $distributor = Distributor::create([
            'vendor_id' => $vendor->id,
            'name' => 'Member Claim Distributor',
            'email' => 'member-claim-distributor@example.com',
            'password' => bcrypt('password'),
            'return_postcode' => '04524',
            'return_address' => '서울특별시 중구 공급처 반송센터',
            'status' => 1,
        ]);
        $order = $this->createOrderForShop($shop, $product, 'Member Customer');
        $order->user_id = $user->id;
        $order->save();
        $item = $order->orders_products()->firstOrFail();
        $item->update([
            'user_id' => $user->id,
            'distributor_id' => $distributor->id,
            'status_code' => OrderItemStatus::DELIVERED,
            'item_status' => '배송완료',
        ]);

        $this->actingAs($user)->postJson(route('mypage.order.claim.submit'), [
            'order_item_id' => $item->id,
            'type' => 'exchange',
            'reason' => '상품 불량',
            'recovery_method' => '수동회수',
        ])->assertOk()->assertJson(['success' => true]);

        $claim = OrderClaim::where('order_product_id', $item->id)->firstOrFail();
        $this->assertSame('manual', $claim->pickup_method);
        $this->assertSame('04524 서울특별시 중구 공급처 반송센터', $claim->return_address);
        $this->assertStringNotContainsString('고객 자택 주소', $claim->return_address);
    }

    public function test_product_inquiry_category_is_saved_with_order_context()
    {
        [, , $shop, $product] = $this->createShopProduct('inquiry-shop', 'INQ-001', 'Inquiry');
        $order = $this->createOrderForShop($shop, $product, 'Inquiry Customer');
        $item = $order->orders_products()->firstOrFail();

        $this->withSession(['shop_channel_id' => $shop->id, 'nonmember_order_id' => $order->id])
            ->post(route('front.shop.order.inquiry'), [
                'order_product_id' => $item->id,
                'inquiry_category' => 'delivery',
                'subject' => '배송 일정 문의',
                'message' => '언제 출고되는지 확인 부탁드립니다.',
            ])
            ->assertRedirect();

        $inquiry = Contact::where('order_product_id', $item->id)->firstOrFail();
        $this->assertSame('delivery', $inquiry->inquiry_category);
        $this->assertSame($shop->id, $inquiry->shop_channel_id);
        $this->assertSame($order->id, $inquiry->order_id);
    }

    public function test_product_detail_inquiry_is_saved_without_an_order()
    {
        [, $admin, $shop, $product, $shopProduct] = $this->createShopProduct('product-inquiry', 'PINQ-001', 'Product Inquiry');

        $this->withSession(['shop_channel_id' => $shop->id])
            ->get(route('shop.product_details', $shopProduct->id))
            ->assertOk()
            ->assertSee('상품 문의하기')
            ->assertSee('name="inquiry_category"', false);

        $this->withSession(['shop_channel_id' => $shop->id])
            ->post(route('front.shop.order.inquiry'), [
                'shop_product_id' => $shopProduct->id,
                'inquiry_category' => 'product',
                'subject' => '상품 소재 문의',
                'message' => '상품 소재를 알려주세요.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'order_id' => null,
            'order_product_id' => null,
            'inquiry_category' => 'product',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('channel.inquiries.index'))
            ->assertOk()
            ->assertSee('상품관련')
            ->assertSee('상품 소재 문의');
    }

    public function test_guest_cannot_submit_actions_for_another_order_in_same_channel()
    {
        [, , $shop, $product] = $this->createShopProduct('secure-shop', 'SECURE-001', 'Secure');
        $ownOrder = $this->createOrderForShop($shop, $product, 'Own Customer');
        $otherOrder = $this->createOrderForShop($shop, $product, 'Other Customer');
        $otherItem = $otherOrder->orders_products()->firstOrFail();

        $session = ['shop_channel_id' => $shop->id, 'nonmember_order_id' => $ownOrder->id];
        $this->withSession($session)
            ->post(route('front.shop.order.item.status', $otherItem->id), [
                'action' => 'cancel',
                'reason' => '권한 확인',
            ])
            ->assertNotFound();
        $this->withSession($session)
            ->post(route('front.shop.order.inquiry'), [
                'order_product_id' => $otherItem->id,
                'inquiry_category' => 'product',
                'subject' => '권한 확인',
                'message' => '다른 주문에는 등록할 수 없습니다.',
            ])
            ->assertNotFound();
    }

    public function test_order_management_uses_status_filters_with_counts()
    {
        [, , $shop, $product] = $this->createShopProduct('filter-shop', 'FILTER-001', 'Filter');
        $delivered = $this->createOrderForShop($shop, $product, 'Delivered Customer');
        $shipping = $this->createOrderForShop($shop, $product, 'Shipping Customer');
        $delivered->orders_products()->update(['status_code' => OrderItemStatus::DELIVERED, 'item_status' => '배송완료']);
        $shipping->orders_products()->update(['status_code' => OrderItemStatus::SHIPPING, 'item_status' => '배송중']);
        $claimItem = $delivered->orders_products()->firstOrFail()->replicate();
        $claimItem->status_code = OrderItemStatus::RETURN_REQUESTED;
        $claimItem->item_status = '반품요청';
        $claimItem->save();

        $this->withSession([
            'shop_channel_id' => $shop->id,
            'last_shop_order_id' => $delivered->id,
            'nonmember_order_id' => $shipping->id,
        ])
            ->get(route('front.shop.order.details', ['status' => 'shipping']))
            ->assertOk()
            ->assertSee('전체 2건')
            ->assertSee('확정대기 1건')
            ->assertSee('배송중 1건')
            ->assertSee('취소·교환·반품 1건')
            ->assertSee('Shipping Customer')
            ->assertDontSee('Delivered Customer');
    }

    public function test_cart_and_order_email_show_channel_name_and_code()
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('brand-cart', 'BRAND-001', 'Brand');
        $this->withSession([
            'shop_channel_id' => $shop->id,
            'shop_channel_cart' => [$shopProduct->id => ['qty' => 1, 'option' => '기본옵션']],
        ])->get(route('front.shop.cart.index'))
            ->assertOk()
            ->assertSee('Brand Channel (brand-cart)');

        $order = $this->createOrderForShop($shop, $product, 'Mail Customer');
        $mailable = new ShopOrderConfirmation($shop, $order, $order->orders_products()->get());
        $mailable->assertSeeInHtml('Brand Channel (brand-cart)');
        $this->assertSame('[Brand Channel] 주문이 접수되었습니다.', $mailable->build()->subject);
    }

    public function test_shop_login_preserves_channel_and_rejects_inactive_accounts(): void
    {
        [, , $shop] = $this->createShopProduct('login-shop', 'LOGIN-001', 'Login');
        $user = User::factory()->create(['username' => 'shop-buyer', 'status' => 1]);
        $this->withSession(['shop_channel_id' => $shop->id])->get(route('shop.login'))
            ->assertOk()->assertSee(route('shop.login.submit'), false)->assertDontSee('헤더 영역');
        $this->post(route('shop.login.submit'), ['login_id' => 'shop-buyer', 'password' => 'password'])
            ->assertRedirect(route('shop.channel_main'))->assertSessionHas('shop_channel_id', $shop->id);
        $this->assertAuthenticatedAs($user);
        $this->post(route('shop.logout'))->assertRedirect(route('shop.login'))->assertSessionMissing('shop_channel_id');
        $this->assertGuest();
        $user->update(['status' => 0]);
        $this->post(route('shop.login.submit'), ['login_id' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login_id');
        $this->assertGuest();
    }

    public function test_shop_order_lookup_verifies_customer_and_current_channel(): void
    {
        [, , $shop, $product] = $this->createShopProduct('lookup-shop', 'LOOKUP-001', 'Lookup');
        [, , $otherShop, $otherProduct] = $this->createShopProduct('lookup-other', 'LOOKUP-002', 'Lookup Other');
        $order = $this->createOrderForShop($shop, $product, 'Lookup Customer');
        $otherOrder = $this->createOrderForShop($otherShop, $otherProduct, 'Lookup Customer');
        $this->withSession(['shop_channel_id' => $shop->id])->get(route('front.shop.order.confirm'))->assertOk();
        $payload = ['order_id' => 'Me9-Shop-'.str_pad($order->id, 7, '0', STR_PAD_LEFT), 'name' => $order->name, 'phone' => '01011112222'];
        $this->post(route('front.shop.order.confirm.submit'), array_merge($payload, ['phone' => '01099999999']))
            ->assertSessionHasErrors('order_id')->assertSessionMissing('nonmember_order_id');
        $this->post(route('front.shop.order.confirm.submit'), array_merge($payload, ['order_id' => (string) $otherOrder->id]))
            ->assertSessionHasErrors('order_id')->assertSessionMissing('nonmember_order_id');
        $this->post(route('front.shop.order.confirm.submit'), $payload)
            ->assertRedirect(route('front.shop.order.view', $order->id))->assertSessionHas('nonmember_order_id', $order->id);
        $this->get(route('front.shop.order.view', $order->id))->assertOk()->assertSee('Lookup Product')->assertSee('Lookup Customer');
        $this->get(route('front.shop.order.view', $otherOrder->id))->assertNotFound();
    }

    public function test_order_view_and_completion_do_not_expose_unverified_or_other_channel_orders(): void
    {
        [, , $shop, $product] = $this->createShopProduct('view-shop', 'VIEW-001', 'View');
        [, , $otherShop, $otherProduct] = $this->createShopProduct('view-other', 'VIEW-002', 'View Other');
        $order = $this->createOrderForShop($shop, $product, 'Own View Customer');
        $otherOrder = $this->createOrderForShop($otherShop, $otherProduct, 'Secret Customer');
        $this->withSession(['shop_channel_id' => $shop->id])->get(route('front.shop.order.view', $order->id))->assertNotFound();
        $this->withSession(['shop_channel_id' => $shop->id, 'last_shop_order_id' => $otherOrder->id])
            ->get(route('front.shop.order.complete'))->assertOk()->assertDontSee('Secret Customer');
        $this->get(route('front.shop.order.view', $otherOrder->id))->assertNotFound();
    }

    public function test_claim_management_lists_only_matching_claims_and_has_detail_popups(): void
    {
        [, , $shop, $product] = $this->createShopProduct('claim-list', 'CLIST-001', 'Claim List');
        $user = User::factory()->create();
        foreach (['cancel'=>OrderItemStatus::CANCEL_REQUESTED, 'return'=>OrderItemStatus::RETURN_REQUESTED, 'exchange'=>OrderItemStatus::EXCHANGE_REQUESTED] as $type=>$status) {
            $order = $this->createOrderForShop($shop, $product, ucfirst($type).' Buyer');
            $order->forceFill(['user_id'=>$user->id])->save();
            $item = $order->orders_products()->firstOrFail();
            $item->update(['status_code'=>$status]);
            OrderClaim::create(['order_id'=>$order->id, 'order_product_id'=>$item->id, 'vendor_id'=>$shop->vendor_id, 'user_id'=>$user->id, 'type'=>$type, 'status'=>'requested', 'reason'=>ucfirst($type).' Reason']);
        }
        $other = $this->createOrderForShop($shop, $product, 'Hidden Buyer');
        OrderClaim::create(['order_id'=>$other->id, 'order_product_id'=>$other->orders_products()->first()->id, 'vendor_id'=>$shop->vendor_id, 'user_id'=>0, 'type'=>'cancel', 'status'=>'requested', 'reason'=>'Hidden Reason']);
        foreach (['cancel','return','exchange'] as $type) {
            $response = $this->actingAs($user)->withSession(['shop_channel_id'=>$shop->id])->get(route('front.shop.'.$type.'.details'));
            $response->assertOk()->assertSee(ucfirst($type).' Buyer')->assertSee(ucfirst($type).' Reason')->assertSee('<dialog', false)->assertDontSee('Hidden Buyer');
            foreach (array_diff(['cancel','return','exchange'], [$type]) as $otherType) $response->assertDontSee(ucfirst($otherType).' Buyer');
        }
    }

    public function test_order_list_displays_all_orders_and_supports_date_and_product_search(): void
    {
        [, , $shop, $product] = $this->createShopProduct('history-shop', 'HISTORY-001', 'History');
        $user = User::factory()->create();
        $older = $this->createOrderForShop($shop, $product, 'Older Buyer');
        $older->forceFill(['user_id'=>$user->id, 'created_at'=>now()->subDays(5)])->save();
        $recent = $this->createOrderForShop($shop, $product, 'Recent Buyer');
        $recent->forceFill(['user_id'=>$user->id])->save();
        $recent->orders_products()->update(['product_name'=>'Searchable Product']);
        $this->actingAs($user)->withSession(['shop_channel_id'=>$shop->id])->get(route('front.shop.order.details'))
            ->assertOk()->assertSee('Older Buyer')->assertSee('Recent Buyer');
        $this->get(route('front.shop.order.details', ['search'=>'Searchable']))->assertOk()->assertSee('Recent Buyer')->assertDontSee('Older Buyer');
        $this->get(route('front.shop.order.details', ['to'=>now()->subDays(1)->toDateString()]))->assertOk()->assertSee('Older Buyer')->assertDontSee('Recent Buyer');
    }

    public function test_cart_option_popup_bulk_removal_and_buy_now_navigation(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('bulk-cart', 'BULK-001', 'Bulk');
        $this->withSession(['shop_channel_id'=>$shop->id])->post(route('front.shop.cart.add'), ['shop_product_id'=>$shopProduct->id, 'qty'=>1, 'buy_now'=>1])
            ->assertRedirect(route('front.shop.order.form'));
        $this->get(route('front.shop.cart.index'))->assertOk()->assertSee('옵션/수량변경')->assertSee('cart-option-'.$shopProduct->id)->assertSee('선택삭제');
        $this->get(route('front.shop.order.form'))->assertOk()->assertDontSee('guest@me9.local')->assertDontSee('홍길동');
        $this->post(route('front.shop.cart.update'), ['shop_product_id'=>$shopProduct->id, 'qty'=>2, 'option'=>'변경 옵션'])->assertRedirect();
        $this->get(route('front.shop.cart.index'))->assertSee('변경 옵션 / 2개');
        $this->post(route('front.shop.cart.remove_selected'), ['shop_product_ids'=>[$shopProduct->id]])->assertRedirect();
        $this->get(route('front.shop.cart.index'))->assertSee('장바구니가 비어 있습니다.');
    }

    public function test_notice_search_navigation_and_attachment_are_channel_scoped(): void
    {
        [, , $shop] = $this->createShopProduct('notice-search', 'NOTICE-001', 'Notice Search');
        [, , $otherShop] = $this->createShopProduct('notice-search-other', 'NOTICE-002', 'Notice Other');
        $first = ShopChannelNotice::create(['shop_channel_id'=>$shop->id, 'title'=>'First Announcement', 'content'=>'First Body', 'status'=>1, 'view_count'=>0]);
        $second = ShopChannelNotice::create(['shop_channel_id'=>$shop->id, 'title'=>'Second Announcement', 'content'=>'Second Body', 'status'=>1, 'view_count'=>0, 'attachment'=>'not-present.txt']);
        $other = ShopChannelNotice::create(['shop_channel_id'=>$otherShop->id, 'title'=>'Secret Announcement', 'content'=>'Secret Body', 'status'=>1, 'view_count'=>0, 'attachment'=>'not-present.txt']);
        $this->withSession(['shop_channel_id'=>$shop->id])->get(route('shop.notices', ['search'=>'Second']))->assertOk()->assertSee('Second Announcement')->assertDontSee('First Announcement')->assertDontSee('Secret Announcement');
        $this->get(route('shop.notices.show', $first->id))->assertOk()->assertSee('Second Announcement')->assertDontSee('Secret Announcement');
        $this->get(route('shop.notices.show', $second->id))->assertOk()->assertSee(route('shop.notices.attachment', $second->id), false);
        $this->get(route('shop.notices.attachment', $second->id))->assertNotFound();
        $this->get(route('shop.notices.attachment', $other->id))->assertNotFound();
    }

    public function test_shop_product_search_and_detail_images_use_published_assets(): void
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('search-shop', 'SEARCH-001', 'Search');
        $product->update(['detail_display_type'=>'image', 'detail_pc_image'=>'desktop-detail.jpg', 'detail_mobile_image'=>'mobile-detail.jpg']);
        $this->withSession(['shop_channel_id'=>$shop->id])->get(route('shop.products_list', ['search'=>'Search']))->assertOk()->assertSee('Search Product');
        $this->get(route('shop.products_list', ['search'=>'not-a-product']))->assertOk()->assertDontSee('Search Product');
        $this->get(route('shop.product_details', $shopProduct->id))->assertOk()->assertSee('/front/images/product_detail_images/desktop-detail.jpg', false)->assertSee('/front/images/product_detail_images/mobile-detail.jpg', false);
        $shopProduct->update(['approval_status'=>'pending']);
        $this->get(route('shop.product_details', $shopProduct->id))->assertNotFound();
    }

    public function test_checkout_accepts_blank_optional_address_fields(): void
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('blank-address', 'BLANK-001', 'Blank');
        $this->withSession([
            'shop_channel_id'=>$shop->id,
            'shop_channel_cart'=>[$shopProduct->id=>['qty'=>1, 'option'=>'기본옵션']],
        ])->post(route('front.shop.order.checkout'), [
            'order_confirmed'=>'1',
            'name'=>'Address Buyer', 'mobile'=>'010-1234-5678', 'email'=>'address-buyer@example.com',
            'pincode'=>'04524', 'address'=>'배송 주소', 'city'=>'', 'state'=>'', 'payment_method'=>'Card',
        ])->assertRedirect(route('front.shop.order.complete'));
        $order = Order::where('email', 'address-buyer@example.com')->firstOrFail();
        $this->assertSame('', $order->city);
        $this->assertSame('', $order->state);
        $this->get(route('front.shop.order.complete'))->assertOk()->assertSee('Address Buyer');
    }

    private function checkoutPayload(): array
    {
        return [
            'order_confirmed' => '1',
            'name' => 'Policy Buyer', 'mobile' => '01012345678', 'email' => 'policy-buyer@example.com',
            'pincode' => '04524', 'address' => 'Seoul', 'payment_method' => 'Card',
        ];
    }

    private function checkoutWithPoints(bool $twoItems = false, bool $stockOptions = false): array
    {
        Mail::fake();
        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest']);
        [$vendor, $admin, $shop, $product, $shopProduct] = $this->createShopProduct('point-wallet', 'POINT-WALLET', 'Wallet');
        $user = User::factory()->create();
        $product->update(['reward_points' => 300]);
        if ($stockOptions) {
            $product->update(['stock_usage' => 'used']);
            foreach (['Black' => 2, 'White' => 0] as $size => $stock) {
                \App\Models\ProductsAttribute::create(['product_id' => $product->id, 'size' => $size, 'price' => 12000,
                    'stock' => $stock, 'sku' => 'WALLET-'.$size, 'status' => 1]);
            }
        }
        foreach (['channel' => 5000, 'me9' => 3000] as $wallet => $points) {
            \App\Models\PointTransaction::create(['user_id' => $user->id, 'shop_channel_id' => $wallet === 'channel' ? $shop->id : null,
                'type' => 'earn', 'points' => $points, 'description' => 'Test opening balance']);
        }
        $cart = [$shopProduct->id => ['qty' => 1, 'option' => $stockOptions ? 'Black' : '기본옵션']];
        if ($twoItems) {
            $secondProduct = $product->replicate();
            $secondProduct->product_code = 'POINT-WALLET-SECOND';
            $secondProduct->save();
            $second = $shopProduct->replicate();
            $second->product_id = $secondProduct->id;
            $second->save();
            $cart[$second->id] = ['qty' => 1, 'option' => '기본옵션'];
        }
        $this->actingAs($user)->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => $cart])
            ->post(route('front.shop.order.checkout'), array_merge($this->checkoutPayload(), ['channel_points' => 5000, 'me9_points' => 3000]))
            ->assertRedirect(route('front.shop.order.complete'));

        return [$vendor, $admin, $shop, $product, $shopProduct, $user, Order::latest('id')->firstOrFail()];
    }

    public function test_point_wallets_restore_once_across_partial_cancellation_and_return(): void
    {
        [$vendor, $admin, $shop, , $shopProduct, $user, $order] = $this->checkoutWithPoints(true);
        $wallet = app(\App\Services\CustomerPointService::class);
        $this->assertSame(['channel' => 0, 'me9' => 0], $wallet->balances($user->id, $shop->id));
        $items = $order->orders_products()->orderBy('id')->get();
        $this->assertEquals(8000, $items->sum('used_point_amount'));
        $this->assertEquals($order->grand_total + 8000, $items->sum('line_total') + $items->sum('shipping_amount_snapshot'));
        $this->assertSame(9, (int) $shopProduct->fresh()->stock);
        $first = $items->first();
        $this->post(route('front.shop.order.item.status', $first->id), ['action' => 'cancel', 'reason' => 'Changed mind'])->assertRedirect();
        $this->assertSame(['channel' => 0, 'me9' => 0], $wallet->balances($user->id, $shop->id));
        $this->actingAs($admin, 'admin')->postJson(route('channel.order.claim.action'), [
            'order_id' => $order->id, 'item_ids' => [$first->id], 'action' => 'cancel_approve',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertEquals($first->point_usage_snapshot, $wallet->balances($user->id, $shop->id));
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);
        DB::transaction(fn () => app(\App\Services\CustomerPointService::class)->reverseItem($first->fresh()));
        $this->assertEquals($first->point_usage_snapshot, $wallet->balances($user->id, $shop->id));
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);

        $second = $items->last();
        foreach (['shipping', 'delivered'] as $status) {
            $this->postJson('/channel/order/status/update', ['order_id' => $order->id, 'item_ids' => [$second->id],
                'status' => $status, 'courier_name' => 'CJ', 'tracking_number' => '123456789'], ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()->assertJson(['status' => true]);
        }
        $this->actingAs($user, 'web')->post(route('front.shop.order.item.status', $second->id), ['action' => 'return', 'reason' => 'Damaged', 'pickup_method' => 'automatic'])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin, 'admin');
        foreach (['return_receive', 'return_complete'] as $action) {
            $this->postJson(route('channel.order.claim.action'), ['order_id' => $order->id, 'item_ids' => [$second->id], 'action' => $action])
                ->assertOk()->assertJson(['status' => true]);
        }
        $this->assertSame(['channel' => 5000, 'me9' => 3000], $wallet->balances($user->id, $shop->id));
        $this->assertDatabaseCount('point_transactions', 10);
        $this->assertEquals($order->grand_total, $order->orders_products()->sum('refund_cash_amount'));
        $this->assertSame(2, $order->orders_products()->where('refund_status', 'mock_refunded')->count());
        $this->assertCount(0, app(SettlementCalculator::class)->items(now()->format('Y-m'), $vendor->id));
        $this->get(route('channel.point.list'))->assertOk()->assertViewHas('summary', fn ($summary) => $summary['balance'] === 5000 && $summary['restored'] === 5000);
    }

    public function test_exchange_replacement_return_restores_original_payment_only_once(): void
    {
        [, $admin, $shop, , $shopProduct, $user, $order] = $this->checkoutWithPoints();
        $item = $order->orders_products()->sole();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $this->post(route('front.shop.order.item.status', $item->id), ['action' => 'exchange', 'reason' => 'Damaged', 'pickup_method' => 'automatic'])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin, 'admin');
        foreach (['exchange_approve', 'exchange_receive', 'exchange_complete'] as $action) {
            $this->postJson(route('channel.order.claim.action'), ['order_id' => $order->id, 'item_ids' => [$item->id], 'action' => $action])
                ->assertOk()->assertJson(['status' => true]);
        }
        $replacement = $item->fresh()->exchangeReplacement;
        $replacement->setStatus(OrderItemStatus::DELIVERED);
        $replacement->save();
        $this->actingAs($user, 'web')->post(route('front.shop.order.item.status', $replacement->id), ['action' => 'return', 'reason' => 'Still damaged', 'pickup_method' => 'automatic'])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin, 'admin');
        foreach (['return_receive', 'return_complete'] as $action) {
            $this->postJson(route('channel.order.claim.action'), ['order_id' => $order->id, 'item_ids' => [$replacement->id], 'action' => $action])
                ->assertOk()->assertJson(['status' => true]);
        }
        $this->assertSame(['channel' => 5000, 'me9' => 3000], app(\App\Services\CustomerPointService::class)->balances($user->id, $shop->id));
        $this->assertSame(OrderItemStatus::RETURNED, $item->fresh()->status_code);
        $this->assertEquals($order->grand_total, $item->fresh()->refund_cash_amount);
        $this->assertSame('reversed_original', $replacement->fresh()->refund_status);
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);
        DB::transaction(fn () => app(\App\Services\CustomerPointService::class)->reverseItem($replacement->fresh()));
        $this->assertSame(2, \App\Models\PointTransaction::where('type', 'refund')->count());
    }

    public function test_points_are_scoped_to_owner_and_channel_and_cannot_overdraw(): void
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('wallet-security', 'WALLET-SECURITY', 'Wallet Security');
        [, , $otherShop] = $this->createShopProduct('other-wallet', 'OTHER-WALLET', 'Other Wallet');
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        \App\Models\PointTransaction::create(['user_id' => $user->id, 'shop_channel_id' => $otherShop->id, 'type' => 'earn', 'points' => 10000]);
        \App\Models\PointTransaction::create(['user_id' => $otherUser->id, 'shop_channel_id' => $shop->id, 'type' => 'earn', 'points' => 10000]);
        $this->actingAs($user)->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]]);
        foreach ([['channel_points' => 1], ['me9_points' => 1], ['channel_points' => -1], ['channel_points' => 99999]] as $points) {
            $this->postJson(route('front.shop.order.checkout'), array_merge($this->checkoutPayload(), $points))->assertUnprocessable();
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('point_transactions', 2);
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);
        $this->assertNotEmpty(session('shop_channel_cart'));
    }

    public function test_commerce_audit_is_read_only_and_detects_corrupted_point_and_cash_totals(): void
    {
        [, , , , , , $order] = $this->checkoutWithPoints();
        $audit = app(\App\Services\CommerceAudit::class);
        $this->assertTrue($audit->inspect()['passed']);
        $before = $order->fresh()->getAttributes();
        $this->artisan('commerce:audit', ['--json' => true])->assertExitCode(0);
        $this->assertSame($before, $order->fresh()->getAttributes());
        $order->forceFill(['grand_total' => $order->grand_total + 1])->save();
        \App\Models\PointTransaction::where('type', 'use')->whereNotNull('shop_channel_id')->decrement('points', 1);
        $result = $audit->inspect();
        $this->assertFalse($result['passed']);
        $this->assertArrayHasKey('order_cash_total_mismatch', $result['issues']);
        $this->assertArrayHasKey('negative_customer_wallet', $result['issues']);
        $this->assertArrayHasKey('item_channel_spend_mismatch', $result['issues']);
        $this->artisan('commerce:audit', ['--json' => true])->assertExitCode(1);
    }

    public function test_point_ledger_write_failure_rolls_back_payment_stock_and_wallet(): void
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('point-rollback', 'POINT-ROLLBACK', 'Point Rollback');
        $user = User::factory()->create();
        \App\Models\PointTransaction::create(['user_id' => $user->id, 'shop_channel_id' => $shop->id, 'type' => 'earn', 'points' => 1000]);
        \App\Models\PointTransaction::creating(function ($entry) {
            if ($entry->type === 'use') {
                throw new \RuntimeException('Simulated ledger storage failure');
            }
        });
        try {
            $this->actingAs($user)->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
                ->postJson(route('front.shop.order.checkout'), array_merge($this->checkoutPayload(), ['channel_points' => 1000]))->assertStatus(500);
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('orders_products', 0);
            $this->assertSame(1000, (int) \App\Models\PointTransaction::sum('points'));
            $this->assertSame(10, (int) $shopProduct->fresh()->stock);
            $this->assertNotEmpty(session('shop_channel_cart'));
            Mail::assertNothingQueued();
        } finally {
            \App\Models\PointTransaction::flushEventListeners();
        }
    }

    public function test_refund_ledger_failure_rolls_back_claim_stock_and_state_without_leaking_errors(): void
    {
        [, $admin, $shop, , $listing, $user, $order] = $this->checkoutWithPoints();
        $item = $order->orders_products()->sole();
        \App\Models\PointTransaction::creating(function ($entry) {
            if ($entry->type === 'refund') {
                throw new \RuntimeException('Private database failure details');
            }
        });
        try {
            $this->actingAs($admin, 'admin')->postJson(route('channel.order.cancel.request'), [
                'order_id' => $order->id, 'item_ids' => [$item->id], 'reason' => 'Cancel', 'detail_reason' => 'Cancel',
            ])->assertStatus(500)->assertDontSee('Private database failure details');
            $this->assertSame(OrderItemStatus::PAID, $item->fresh()->status_code);
            $this->assertNull($item->fresh()->financial_reversed_at);
            $this->assertDatabaseCount('order_claims', 0);
            $this->assertSame(9, (int) $listing->fresh()->stock);
            $this->assertSame(['channel' => 0, 'me9' => 0], app(\App\Services\CustomerPointService::class)->balances($user->id, $shop->id));
        } finally {
            \App\Models\PointTransaction::flushEventListeners();
        }
    }

    public function test_seller_cancellation_completes_existing_customer_claim_without_duplicates(): void
    {
        [, $admin, , , , , $order] = $this->checkoutWithPoints();
        $item = $order->orders_products()->sole();
        $this->post(route('front.shop.order.item.status', $item->id), ['action' => 'cancel', 'reason' => 'Buyer reason'])->assertRedirect();
        $this->actingAs($admin, 'admin')->postJson(route('channel.order.cancel.request'), [
            'order_id' => $order->id, 'item_ids' => [$item->id], 'reason' => 'Seller reason', 'detail_reason' => 'Approved',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertSame('completed', OrderClaim::sole()->status);
        $this->assertSame('Buyer reason', OrderClaim::sole()->reason);
        $this->postJson(route('channel.order.cancel.request'), [
            'order_id' => $order->id, 'item_ids' => [$item->id], 'reason' => 'Retry', 'detail_reason' => 'Retry',
        ])->assertUnprocessable();
        $this->assertDatabaseCount('order_claims', 1);
        $this->assertSame(2, \App\Models\PointTransaction::where('type', 'refund')->count());
    }

    public function test_repeated_confirmation_does_not_repeat_sms_or_rewards(): void
    {
        [, $admin, $shop, , , , $order] = $this->checkoutWithPoints();
        $shop->update(['use_purchase_sms' => true, 'purchase_sms_templates' => ['purchase_confirmed' => 'Confirmed']]);
        $item = $order->orders_products()->sole();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->actingAs($admin, 'admin')->postJson('/channel/order/status/update', [
                'order_id' => $order->id, 'item_ids' => [$item->id], 'status' => 'confirmed',
            ])->assertOk()->assertJson(['status' => true]);
        }
        $this->assertSame(1, \App\Models\ShopChannelSmsLog::where('template_type', 'purchase_confirmed')->count());
        $this->assertSame(1, \App\Models\PointTransaction::where('reference_key', 'earn:'.$item->id)->count());
    }

    public function test_buyer_distributor_channel_subadmin_and_admin_share_the_same_settlement_flow(): void
    {
        [$vendor, $owner, $shop, , , $user, $order] = $this->checkoutWithPoints();
        $item = $order->orders_products()->sole();
        $distributor = Distributor::create(['vendor_id' => $vendor->id, 'name' => 'Flow Distributor', 'email' => 'point-flow@example.com', 'password' => bcrypt('password'), 'status' => 1]);
        $item->update(['distributor_id' => $distributor->id]);
        $subadmin = Admin::forceCreate(['vendor_id' => $vendor->id, 'type' => 'subadmin', 'name' => 'Flow Assistant', 'mobile' => '01011112222', 'email' => 'point-assistant@example.com', 'password' => bcrypt('password'), 'status' => 1]);
        \App\Models\ChannelSubAccount::create(['vendor_id' => $vendor->id, 'admin_id' => $subadmin->id, 'permissions' => ['order', 'settings']]);
        $this->actingAs($subadmin, 'admin')->postJson('/channel/order/status/update', ['order_id' => $order->id, 'item_ids' => [$item->id], 'status' => 'ready_to_ship'])
            ->assertOk()->assertJson(['status' => true]);
        foreach ([OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED] as $status) {
            $this->withSession(['distributor_id' => $distributor->id])->post(route('distributor.order.update', $item->id), [
                'courier' => 'CJ', 'tracking_no' => 'ROLE-FLOW', 'status_code' => $status,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->actingAs($user, 'web')->post(route('front.shop.order.item.status', $item->id), ['action' => 'confirm'])->assertRedirect();
        $superadmin = Admin::forceCreate(['vendor_id' => 0, 'type' => 'superadmin', 'name' => 'Flow Superadmin', 'mobile' => '01011112222', 'email' => 'point-superadmin@example.com', 'password' => bcrypt('password'), 'status' => 1]);
        $this->actingAs($superadmin, 'admin')->post(route('admin.settlements.generate'), ['period' => now()->format('Y-m')])->assertRedirect();
        $run = \App\Models\SettlementRun::where('vendor_id', $vendor->id)->sole();
        $this->assertEquals(8000, $run->point_used_amount);
        $this->assertTrue(app(\App\Services\CommerceAudit::class)->inspect()['passed']);
        $this->actingAs($owner, 'admin')->get(route('admin.settlements.export', $run->id))->assertOk();
        $this->actingAs($subadmin, 'admin')->post(route('admin.settlements.generate'), ['period' => now()->format('Y-m')])->assertForbidden();
    }

    public function test_option_stock_is_enforced_at_checkout_and_restored_once_on_cancellation(): void
    {
        Mail::fake();
        [, $admin, $shop, $product, $shopProduct] = $this->createShopProduct('option-ledger', 'OPTION-LEDGER', 'Option Ledger');
        $product->update(['stock_usage' => 'used']);
        $attribute = \App\Models\ProductsAttribute::create(['product_id' => $product->id, 'size' => 'M', 'price' => 12000, 'stock' => 2, 'sku' => 'OPTION-M', 'status' => 1]);
        $this->withSession(['shop_channel_id' => $shop->id])->postJson(route('front.shop.cart.add'), ['shop_product_id' => $shopProduct->id, 'qty' => 3, 'option' => 'M'])->assertUnprocessable();
        $this->withSession(['shop_channel_cart' => [$shopProduct->id => ['qty' => 3, 'option' => 'M']]])
            ->postJson(route('front.shop.order.checkout'), $this->checkoutPayload())->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
        $this->withSession(['shop_channel_cart' => [$shopProduct->id => ['qty' => 2, 'option' => 'M']]])
            ->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect();
        $this->assertSame(0, (int) $attribute->fresh()->stock);
        $item = OrdersProduct::sole();
        $this->assertSame($attribute->id, (int) $item->stock_attribute_id);
        $this->actingAs($admin, 'admin')->postJson(route('channel.order.cancel.request'), ['order_id' => $item->order_id, 'item_ids' => [$item->id], 'reason' => 'Test cancel', 'detail_reason' => 'Test'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJson(['status' => true]);
        $this->assertSame(2, (int) $attribute->fresh()->stock);
        DB::transaction(fn () => app(\App\Services\CustomerPointService::class)->reverseItem($item->fresh()));
        $this->assertSame(2, (int) $attribute->fresh()->stock);
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);
    }

    public function test_new_joint_purchase_repricing_does_not_rewrite_collected_cash_or_points(): void
    {
        [$vendor, , , $product, , , $order] = $this->checkoutWithPoints();
        $item = $order->orders_products()->sole();
        $jointId = DB::table('joint_purchases')->insertGetId(['product_id' => $product->id, 'min_quantity' => 1, 'current_quantity' => 1,
            'discount_price' => 5000, 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'status' => 1]);
        $item->update(['joint_purchase_id' => $jointId, 'status_code' => OrderItemStatus::CONFIRMED, 'confirmed_at' => now()]);
        $beforeCash = $order->grand_total;
        app(\App\Services\JointPurchasePricingService::class)->repricePurchase($jointId);
        $this->assertEquals($beforeCash, $order->fresh()->grand_total);
        $this->assertEquals(8000, $order->fresh()->used_point);
        $this->assertEquals(12000, $item->fresh()->paid_line_total_snapshot);
        $this->assertEquals(7000, $item->fresh()->reprice_adjustment_amount);
        $this->assertSame('pending_repayment', $item->fresh()->reprice_status);
        $this->assertCount(0, app(SettlementCalculator::class)->items(now()->format('Y-m'), $vendor->id));
        $this->assertArrayHasKey('joint_purchase_payment_adjustment_pending', app(\App\Services\CommerceAudit::class)->inspect()['issues']);
    }

    public function test_exchange_reserves_the_new_option_and_return_restores_only_that_option(): void
    {
        [, $admin, , $product, $listing, $user, $order] = $this->checkoutWithPoints(false, true);
        $black = \App\Models\ProductsAttribute::where('product_id', $product->id)->where('size', 'Black')->sole();
        $white = \App\Models\ProductsAttribute::where('product_id', $product->id)->where('size', 'White')->sole();
        $item = $order->orders_products()->sole();
        $this->assertSame(1, (int) $black->stock);
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $this->post(route('front.shop.order.item.status', $item->id), ['action' => 'exchange', 'reason' => 'Change color', 'pickup_method' => 'automatic'])->assertRedirect();
        $this->actingAs($admin, 'admin');
        $payload = ['order_id' => $order->id, 'item_ids' => [$item->id]];
        foreach (['exchange_approve', 'exchange_receive', 'exchange_option'] as $action) {
            $this->postJson(route('channel.order.claim.action'), $payload + ['action' => $action, 'option' => 'White'])->assertOk();
        }
        $this->postJson(route('channel.order.claim.action'), $payload + ['action' => 'exchange_complete'])->assertUnprocessable();
        $this->assertSame(OrderItemStatus::EXCHANGE_RECEIVED, $item->fresh()->status_code);
        $this->assertNull($item->fresh()->exchangeReplacement);
        $this->assertSame(1, (int) $black->fresh()->stock);
        $white->update(['stock' => 2]);
        $this->postJson(route('channel.order.claim.action'), $payload + ['action' => 'exchange_complete'])->assertOk();
        $replacement = $item->fresh()->exchangeReplacement;
        $this->assertSame(2, (int) $black->fresh()->stock);
        $this->assertSame(1, (int) $white->fresh()->stock);
        $this->assertSame($white->id, (int) $item->fresh()->stock_attribute_id);
        $replacement->setStatus(OrderItemStatus::DELIVERED);
        $replacement->save();
        $this->actingAs($user, 'web')->post(route('front.shop.order.item.status', $replacement->id), [
            'action' => 'return', 'reason' => 'Return replacement', 'pickup_method' => 'automatic',
        ])->assertRedirect();
        $this->actingAs($admin, 'admin');
        foreach (['return_receive', 'return_complete'] as $action) {
            $this->postJson(route('channel.order.claim.action'), ['order_id' => $order->id, 'item_ids' => [$replacement->id], 'action' => $action])->assertOk();
        }
        $this->assertSame(2, (int) $black->fresh()->stock);
        $this->assertSame(2, (int) $white->fresh()->stock);
        $this->assertSame(10, (int) $listing->fresh()->stock);
        $this->assertTrue(app(\App\Services\CommerceAudit::class)->inspect()['passed']);
    }

    public function test_confirmation_awards_without_precharging_and_preserves_settlement_points(): void
    {
        [$vendor, $admin, $shop, , , $user, $order] = $this->checkoutWithPoints();
        $item = $order->orders_products()->sole();
        $item->setStatus(OrderItemStatus::DELIVERED);
        $item->save();
        $this->post(route('front.shop.order.item.status', $item->id), ['action' => 'confirm'])->assertRedirect();
        $this->assertSame(['channel' => 300, 'me9' => 0], app(\App\Services\CustomerPointService::class)->balances($user->id, $shop->id));
        $this->postJson(route('front.shop.order.item.status', $item->id), ['action' => 'confirm'])->assertUnprocessable();
        $this->assertSame(1, \App\Models\PointTransaction::where('reference_key', 'earn:'.$item->id)->count());
        $row = app(SettlementCalculator::class)->items(now()->format('Y-m'), $vendor->id)->sole();
        $this->assertEquals(8000, $row['point_used_amount']);
        $run = app(SettlementCalculator::class)->generate(now()->format('Y-m'), $admin->id)->sole();
        $this->assertSame(1, $run->items->count());
        $this->assertEquals(8000, $run->items->sum('point_used_amount'));
        $this->actingAs($admin, 'admin')->postJson('/channel/order/status/update', [
            'order_id' => $order->id, 'item_ids' => [$item->id], 'status' => 'shipping', 'courier_name' => 'CJ', 'tracking_number' => '123',
        ], ['X-Requested-With' => 'XMLHttpRequest'])->assertUnprocessable();
        $this->assertSame(OrderItemStatus::CONFIRMED, $item->fresh()->status_code);
    }

    public function test_disabled_payment_preserves_cart_stock_and_does_not_create_order(): void
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('disabled-pay', 'DISABLED-PAY', 'Disabled');
        config(['shop_channel.payment_driver' => 'disabled']);
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->get(route('front.shop.order.form'))->assertOk()->assertSee('현재 결제를 이용할 수 없습니다.');
        $this->postJson(route('front.shop.order.checkout'), $this->checkoutPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);
        $this->assertArrayHasKey($shopProduct->id, session('shop_channel_cart'));
        Mail::assertNothingQueued();
    }

    public function test_production_cannot_complete_mock_payments_even_when_driver_is_mock(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('prod-pay', 'PROD-PAY', 'Production');
        config(['shop_channel.payment_driver' => 'mock']);
        $this->app->instance('env', 'production');
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->postJson(route('front.shop.order.checkout'), $this->checkoutPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, (int) $shopProduct->fresh()->stock);
    }

    public function test_checkout_preserves_commission_and_reward_policy_after_settings_change(): void
    {
        Mail::fake();
        [$vendor, $admin, $shop, $product, $shopProduct] = $this->createShopProduct('policy-pay', 'POLICY-PAY', 'Policy');
        $user = User::factory()->create();
        $shop->update(['use_own_pg' => true]);
        $product->update(['reward_points' => 300]);
        $shopProduct->update(['settlement_type_snapshot' => 1, 'settlement_rate_snapshot' => 5]);
        $this->actingAs($user)->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect(route('front.shop.order.complete'));
        $item = OrdersProduct::firstOrFail();
        $this->assertSame('me9_pg', $item->payment_gateway_type);
        $this->assertSame(300, $item->settlement_policy_snapshot['reward_points']);

        $product->update(['reward_points' => 900]);
        $shopProduct->update(['settlement_rate_snapshot' => 50]);
        $shop->update(['settlement_rate' => 70]);
        $item->update(['status_code' => OrderItemStatus::CONFIRMED, 'confirmed_at' => now()]);
        $row = app(SettlementCalculator::class)->items(now()->format('Y-m'), $vendor->id)->sole();
        $this->assertEquals(800, $row['admin_amount']);
        $this->assertEquals(300, $row['point_deposit_amount']);
        $this->assertEquals(13400, $row['payout_amount']);
        $this->assertSame('me9_pg', $row['payment_gateway_type']);

        $points = app(ChannelPointService::class);
        $points->approve($points->requestPurchase($vendor->id, 1000, 'card'), $admin->id);
        $points->recordCustomerPayback($item->fresh());
        $points->recordCustomerPayback($item->fresh());
        $this->assertSame(700, $points->balanceForVendor($vendor->id));
        $this->assertDatabaseHas('point_transactions', ['order_product_id' => $item->id, 'points' => 300]);
        $this->assertDatabaseCount('point_transactions', 1);

        $item->update(['extra_shipping_fee' => 500]);
        $run = app(SettlementCalculator::class)->generate(now()->format('Y-m'))->sole();
        $this->actingAs($admin, 'admin');
        $payout = $this->get(route('admin.settlements.payout.export', $run->id));
        $payout->assertOk();
        $this->assertStringContainsString('공용PG,0,14500,0,0,12000,2500,0,14500,13400', $payout->streamedContent());
        $billing = $this->get(route('admin.settlements.billing.export', $run->id));
        $billing->assertOk();
        $this->assertStringContainsString('공용PG,800,0,0,300,1100', $billing->streamedContent());
        $shipping = $this->get(route('admin.settlements.extra_shipping.export', $run->id));
        $shipping->assertOk();
        $this->assertStringContainsString('공용PG,0,0,500,500', $shipping->streamedContent());
    }

    public function test_own_pg_order_remains_own_pg_when_rewards_are_added_later(): void
    {
        Mail::fake();
        [$vendor, , $shop, $product, $shopProduct] = $this->createShopProduct('own-policy', 'OWN-POLICY', 'Own Policy');
        $shop->update(['use_own_pg' => true]);
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect();
        $item = OrdersProduct::firstOrFail();
        $this->assertSame('own_pg', $item->payment_gateway_type);
        $shop->update(['use_own_pg' => false]);
        $product->update(['reward_points' => 500]);
        $item->update(['status_code' => OrderItemStatus::CONFIRMED, 'confirmed_at' => now()]);
        $row = app(SettlementCalculator::class)->items(now()->format('Y-m'), $vendor->id)->sole();
        $this->assertSame('own_pg', $row['payment_gateway_type']);
        $this->assertEquals(0, $row['admin_amount']);
        $this->assertEquals(0, $row['point_deposit_amount']);
        $this->assertEquals(0, $row['payout_amount']);
    }

    public function test_shared_order_preserves_supplier_fixed_price_and_rebate_policy(): void
    {
        Mail::fake();
        [$seller, , $shop, $product, $shopProduct] = $this->createShopProduct('shared-policy', 'SHARED-POLICY', 'Shared Policy');
        [$supplier] = $this->createShopProduct('supplier-policy', 'SUPPLIER-POLICY', 'Supplier Policy');
        $product->update(['vendor_id' => $supplier->id, 'price_constraint_enabled' => true, 'price_constraint_type' => 'fixed', 'profit_share_type' => 'fixed', 'profit_share_value' => 2000]);
        $shopProduct->update(['product_type' => 'public']);
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect();
        $item = OrdersProduct::firstOrFail();
        $product->update(['vendor_id' => $seller->id, 'price_constraint_enabled' => false, 'profit_share_value' => 4000]);
        $shopProduct->update(['product_type' => 'own']);
        $item->update(['status_code' => OrderItemStatus::CONFIRMED, 'confirmed_at' => now()]);
        $row = app(SettlementCalculator::class)->items(now()->format('Y-m'), $supplier->id)->sole();
        $this->assertSame('shared_fixed_supplier', $row['settlement_role']);
        $this->assertSame($supplier->id, $row['vendor_id']);
        $this->assertEquals(10900, $row['payout_amount']);
        $reseller = app(SettlementCalculator::class)->items(now()->format('Y-m'), $seller->id)->sole();
        $this->assertSame('shared_fixed_reseller', $reseller['settlement_role']);
        $this->assertEquals(2000, $reseller['payout_amount']);
    }

    public function test_shop_copy_copies_only_settings_and_is_owner_scoped(): void
    {
        [, $admin, $shop] = $this->createShopProduct('copy-source', 'COPY-SOURCE', 'Copy Source');
        [, , $otherShop] = $this->createShopProduct('copy-other', 'COPY-OTHER', 'Copy Other');
        $shop->update(['use_admin' => 1, 'admin_login_id' => 'monitor', 'admin_password' => bcrypt('password')]);
        $this->actingAs($admin, 'admin')->post(route('channel.shop.copy', $otherShop->id))->assertNotFound();
        $this->post(route('channel.shop.copy', $shop->id))->assertRedirect();
        $copy = ShopChannel::where('vendor_id', $shop->vendor_id)->where('id', '!=', $shop->id)->sole();
        $this->assertNotSame($shop->channel_code, $copy->channel_code);
        $this->assertSame($shop->channel_name.' (복사)', $copy->channel_name);
        $this->assertEquals(0, $copy->status);
        $this->assertEquals(0, $copy->use_admin);
        $this->assertNull($copy->admin_login_id);
        $this->assertCount(0, $copy->shopChannelProducts);
        $this->assertCount(0, $copy->privateAccesses);
        $this->assertCount(1, $shop->shopChannelProducts);
    }

    public function test_shop_list_and_stopped_product_modal_show_real_channel_data(): void
    {
        [, $admin, $shop, $product, $shopProduct] = $this->createShopProduct('screen-data', 'SCREEN-DATA', 'Screen Data');
        $name = 'Buyer\'s "Special" <Product>';
        $product->update(['product_name' => $name]);
        $shopProduct->update(['status' => 0, 'stock' => 0]);
        $response = $this->actingAs($admin, 'admin')->get(route('channel.shop_list'));
        $response->assertOk()->assertSee('data:image/png;base64,', false)->assertDontSee('qr_sample1.jpg');
        $this->assertSame(1, $response->viewData('shops')->first()->shop_channel_products_count);
        foreach (['channel.shop_product01', 'channel.shop_product02'] as $route) {
            $response = $this->get(route($route, ['shop_id' => $shop->id]));
            $response->assertOk()->assertSee('id="view_product_name"', false)
                ->assertSee(route('channel.shop_info', ['id' => $shop->id]), false)
                ->assertDontSee('Me9-Shop-0032022');
        }
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $button = (new \DOMXPath($dom))->query('//button[@data-product]')->item(0);
        $this->assertNotNull($button);
        $data = json_decode($button->getAttribute('data-product'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($name, $data['name']);
        $this->assertSame('0개', $data['stock_text']);
    }

    public function test_product_minimum_and_maximum_quantity_are_enforced_in_cart_and_checkout(): void
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('quantity-policy', 'QTY-POLICY', 'Quantity');
        $product->update(['purchase_limit_enabled' => true, 'purchase_min_qty' => 2, 'purchase_max_qty' => 4]);
        $this->withSession(['shop_channel_id' => $shop->id])
            ->postJson(route('front.shop.cart.add'), ['shop_product_id' => $shopProduct->id, 'qty' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('qty');
        $this->postJson(route('front.shop.cart.add'), ['shop_product_id' => $shopProduct->id, 'qty' => 5])
            ->assertUnprocessable()->assertJsonValidationErrors('qty');
        $this->post(route('front.shop.cart.add'), ['shop_product_id' => $shopProduct->id, 'qty' => 2])->assertRedirect();
        $product->update(['purchase_min_qty' => 3]);
        $this->postJson(route('front.shop.order.checkout'), $this->checkoutPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('qty');
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(10, $shopProduct->fresh()->stock);
    }

    public function test_order_shipping_matches_preview_and_is_preserved_for_settlement(): void
    {
        Mail::fake();
        [$vendor, , $shop, $product, $shopProduct] = $this->createShopProduct('shipping-policy', 'SHIPPING-POLICY', 'Shipping');
        $product->update(['shipping_policy_type' => 'paid', 'shipping_payment_type' => 'prepaid', 'shipping_base_fee' => 4000]);
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->get(route('front.shop.order.form'))->assertOk()->assertViewHas('totals', fn ($totals) => $totals['shipping'] == 4000 && $totals['total'] == 16000);
        $this->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect();
        $item = OrdersProduct::firstOrFail();
        $this->assertEquals(4000, $item->shipping_amount_snapshot);
        $this->assertEquals(4000, $item->order->shipping_charges);
        $this->assertEquals(16000, $item->order->grand_total);
        $product->update(['shipping_policy_type' => 'free', 'shipping_base_fee' => 0]);
        $item->update(['status_code' => OrderItemStatus::CONFIRMED, 'confirmed_at' => now()]);
        $row = app(SettlementCalculator::class)->items(now()->format('Y-m'), $vendor->id)->sole();
        $this->assertEquals(16000, $row['gross_sales_amount']);
    }

    public function test_catalog_filters_inactive_products_and_paginates_sorted_results(): void
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('catalog', 'CATALOG', 'Catalog');
        for ($index = 1; $index <= 13; $index++) {
            $copy = $product->replicate();
            $copy->product_name = 'Catalog item '.$index;
            $copy->product_code = 'CATALOG-'.$index;
            $copy->category_id = $index < 3 ? 2 : 1;
            $copy->save();
            $listing = $shopProduct->replicate();
            $listing->product_id = $copy->id;
            $listing->selling_price = 12000 + $index;
            $listing->save();
        }
        DB::table('ratings')->insert([
            ['product_id' => $product->id, 'user_id' => 0, 'rating' => 4, 'review' => 'Approved', 'status' => 1],
            ['product_id' => $product->id, 'user_id' => 0, 'rating' => 1, 'review' => 'Hidden', 'status' => 0],
        ]);
        $this->withSession(['shop_channel_id' => $shop->id])->get(route('shop.products_list', ['sort' => 'rating']))
            ->assertOk()->assertViewHas('products', fn ($products) => $products->total() === 14 && $products->count() === 12 && $products->first()->id === $shopProduct->id)
            ->assertSee('평점 4.0점, 후기 1건');
        $this->get(route('shop.products_list', ['category' => 2]))->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 2);
        $this->get(route('shop.products_list', ['page' => 2]))->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 2);
        $product->update(['status' => 0]);
        $this->withSession(['shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->get(route('front.shop.cart.index'))->assertOk()->assertSee('장바구니가 비어 있습니다.');
        $this->get(route('shop.products_list'))->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 13);
    }

    public function test_cart_does_not_silently_replace_an_existing_option(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('option', 'OPTION', 'Option');
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 2, 'option' => 'Black']]])
            ->postJson(route('front.shop.cart.add'), ['shop_product_id' => $shopProduct->id, 'qty' => 1, 'option' => 'White'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(['qty' => 2, 'option' => 'Black'], session('shop_channel_cart')[$shopProduct->id]);
        $this->assertCount(2, session('shop_channel_cart'));
        $this->assertSame(['qty' => 1, 'option' => 'White'], collect(session('shop_channel_cart'))->last());
    }

    public function test_cart_and_checkout_reject_inactive_product_options(): void
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('inactive-option', 'INACTIVE-OPTION', 'Inactive Option');
        DB::table('products_attributes')->insert(['product_id' => $product->id, 'size' => 'Black', 'sku' => 'BLACK', 'price' => 12000, 'stock' => 10, 'status' => 0]);
        $this->withSession(['shop_channel_id' => $shop->id])
            ->postJson(route('front.shop.cart.add'), ['shop_product_id' => $shopProduct->id, 'qty' => 1, 'option' => 'Black'])
            ->assertUnprocessable()->assertJsonValidationErrors('option');
        $this->withSession(['shop_channel_cart' => [$shopProduct->id => ['qty' => 1, 'option' => 'Black']]])
            ->postJson(route('front.shop.order.checkout'), $this->checkoutPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('option');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_preserves_buyer_recipient_and_delivery_memo(): void
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('recipient', 'RECIPIENT', 'Recipient');
        $payload = array_merge($this->checkoutPayload(), [
            'buyer_name' => 'Purchaser', 'buyer_mobile' => '010-7777-8888',
            'name' => 'Recipient', 'mobile' => '010-1111-2222', 'delivery_memo' => 'Leave at reception',
        ]);
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->post(route('front.shop.order.checkout'), $payload)->assertRedirect(route('front.shop.order.complete'));
        $order = Order::firstOrFail();
        $this->assertSame('Purchaser', $order->buyer_name);
        $this->assertSame('Recipient', $order->name);
        $this->assertSame('Leave at reception', $order->delivery_memo);
        $this->assertNotNull($order->order_confirmed_at);
        $this->get(route('front.shop.order.view', $order->id))->assertOk()->assertSee('Purchaser')->assertSee('Recipient')->assertSee('Leave at reception');
        $this->post(route('front.shop.order.confirm.submit'), ['order_id' => (string) $order->id, 'name' => 'Purchaser', 'phone' => '01077778888'])
            ->assertRedirect(route('front.shop.order.view', $order->id));
    }

    public function test_checkout_requires_order_confirmation(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('consent', 'CONSENT', 'Consent');
        $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => [$shopProduct->id => ['qty' => 1]]])
            ->postJson(route('front.shop.order.checkout'), array_merge($this->checkoutPayload(), ['order_confirmed' => 0]))
            ->assertUnprocessable()->assertJsonValidationErrors('order_confirmed');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_production_registration_requires_published_policies(): void
    {
        [, , $shop] = $this->createShopProduct('terms', 'TERMS', 'Terms');
        $this->app->instance('env', 'production');
        config(['shop_channel.terms_url' => null, 'shop_channel.privacy_url' => null]);
        $this->withSession(['shop_channel_id' => $shop->id])->get(route('shop.register'))
            ->assertOk()->assertSee('회원가입 약관을 준비 중입니다.');
        $this->postJson(route('shop.register.submit'), [])->assertUnprocessable()->assertJsonValidationErrors('terms_service');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_order_form_displays_only_the_customers_saved_addresses(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('addresses', 'ADDRESSES', 'Addresses');
        $user = User::factory()->create();
        foreach ([$user, User::factory()->create()] as $index => $owner) {
            \App\Models\DeliveryAddress::create(['user_id'=>$owner->id, 'name'=>'Recipient', 'mobile'=>'01011112222', 'pincode'=>'12345', 'address'=>$index === 0 ? 'My Address' : 'Private Other Address', 'city'=>'Seoul', 'state'=>'Seoul', 'country'=>'Korea', 'status'=>1, 'is_default'=>1]);
        }
        $this->actingAs($user)->withSession(['shop_channel_id'=>$shop->id, 'shop_channel_cart'=>[$shopProduct->id=>['qty'=>1]]])
            ->get(route('front.shop.order.form'))->assertOk()->assertSee('My Address')->assertDontSee('Private Other Address');
    }

    public function test_purchase_confirmation_creates_a_moderated_review_only_once(): void
    {
        [, , $shop, $product] = $this->createShopProduct('review', 'REVIEW', 'Review');
        $order = $this->createOrderForShop($shop, $product, 'Reviewer');
        $item = $order->orders_products()->first();
        $item->update(['status_code'=>OrderItemStatus::DELIVERED]);
        $payload = ['action'=>'confirm', 'rating'=>4, 'review'=>'Purchase verified review'];
        $this->withSession(['shop_channel_id'=>$shop->id, 'nonmember_order_id'=>$order->id])
            ->post(route('front.shop.order.item.status', $item->id), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ratings', ['product_id'=>$product->id, 'rating'=>4, 'review'=>'Purchase verified review', 'status'=>0]);
        $this->post(route('front.shop.order.item.status', $item->id), $payload)->assertSessionHasErrors('action');
        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_product_inquiries_and_replies_are_scoped_to_their_author(): void
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('qa', 'QA', 'Inquiry');
        $user = User::factory()->create();
        foreach ([$user, User::factory()->create()] as $index=>$author) {
            Contact::create(['user_id'=>$author->id, 'shop_channel_id'=>$shop->id, 'vendor_id'=>$shop->vendor_id, 'product_id'=>$product->id, 'name'=>$author->name, 'email'=>$author->email, 'type'=>'inquiry', 'status'=>'pending', 'subject'=>$index === 0 ? 'My inquiry' : 'Other private inquiry', 'message'=>'Message', 'admin_reply'=>$index === 0 ? 'My reply' : 'Other private reply']);
        }
        $this->actingAs($user)->withSession(['shop_channel_id'=>$shop->id])->get(route('shop.product_details',$shopProduct->id))
            ->assertOk()->assertSee('My inquiry')->assertSee('My reply')->assertDontSee('Other private inquiry')->assertDontSee('Other private reply');
    }

    public function test_guest_can_read_the_reply_to_a_session_owned_inquiry(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('guest-qa', 'GUEST-QA', 'Guest Inquiry');
        $this->withSession(['shop_channel_id'=>$shop->id])->post(route('front.shop.order.inquiry'), [
            'shop_product_id'=>$shopProduct->id, 'inquiry_category'=>'product', 'subject'=>'Guest question', 'message'=>'My question',
        ])->assertRedirect();
        $inquiry = Contact::firstOrFail();
        $inquiry->update(['admin_reply'=>'Guest private reply']);
        $this->get(route('shop.product_details',$shopProduct->id))->assertOk()->assertSee('Guest private reply');
        $this->withSession(['shop_inquiry_ids'=>[]])->get(route('shop.product_details',$shopProduct->id))->assertOk()->assertDontSee('Guest private reply');
    }

    public function test_multiple_options_share_stock_limit_and_preserve_separate_order_lines(): void
    {
        Mail::fake();
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('multi', 'MULTI', 'Multi');
        $shopProduct->update(['stock'=>5, 'purchase_limit'=>4]);
        $product->update(['purchase_limit_enabled'=>true,'purchase_min_qty'=>2,'shipping_policy_type'=>'paid','shipping_base_fee'=>3000]);
        $this->withSession(['shop_channel_id'=>$shop->id])->post(route('front.shop.cart.add'), [
            'shop_product_id'=>$shopProduct->id, 'options'=>[['option'=>'Black','qty'=>1],['option'=>'White','qty'=>2]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->get(route('front.shop.cart.index'))->assertOk()->assertSee('Black')->assertSee('White')
            ->assertViewHas('totals',fn($totals)=>$totals['shipping'] == 3000 && $totals['total'] == 39000);
        $saved = session('shop_channel_cart');
        $this->postJson(route('front.shop.cart.add'), ['shop_product_id'=>$shopProduct->id,'option'=>'Red','qty'=>2])
            ->assertUnprocessable()->assertJsonValidationErrors('qty');
        $this->assertSame($saved, session('shop_channel_cart'));
        $this->post(route('front.shop.order.checkout'), $this->checkoutPayload())->assertRedirect(route('front.shop.order.complete'));
        $this->assertEquals(2, $shopProduct->fresh()->stock);
        $this->assertDatabaseCount('orders_products', 2);
        $this->assertEquals(3000, OrdersProduct::sum('shipping_amount_snapshot'));
        $this->assertEquals(3000, Order::first()->shipping_charges);
        $this->assertSame(['Black','White'], OrdersProduct::orderBy('id')->pluck('product_size')->all());
    }

    public function test_multi_option_updates_merge_matching_options_and_delete_only_selected_rows(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('multi-update','MULTI-UPDATE','Multi Update');
        $this->withSession(['shop_channel_id'=>$shop->id])->post(route('front.shop.cart.add'), [
            'shop_product_id'=>$shopProduct->id,'options'=>[['option'=>'Black','qty'=>1],['option'=>'White','qty'=>2],['option'=>'Red','qty'=>1]],
        ])->assertRedirect();
        $whiteKey = collect(session('shop_channel_cart'))->search(fn($row)=>$row['option']==='White');
        $redKey = collect(session('shop_channel_cart'))->search(fn($row)=>$row['option']==='Red');
        $this->post(route('front.shop.cart.update'), ['shop_product_id'=>$shopProduct->id,'cart_key'=>$whiteKey,'qty'=>3,'option'=>'Black'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertCount(2, session('shop_channel_cart'));
        $this->assertSame(['qty'=>4,'option'=>'Black'], session('shop_channel_cart')[$whiteKey]);
        $this->post(route('front.shop.cart.remove_selected'), ['shop_product_ids'=>[$redKey]])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertCount(1, session('shop_channel_cart'));
        $this->assertArrayHasKey($whiteKey, session('shop_channel_cart'));
        $this->post(route('front.shop.cart.remove'), ['shop_product_id'=>$shopProduct->id,'cart_key'=>$whiteKey])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame([], session('shop_channel_cart'));
    }

    public function test_invalid_option_batch_leaves_the_original_cart_unchanged(): void
    {
        [, , $shop, $product, $shopProduct] = $this->createShopProduct('batch','BATCH','Batch');
        \App\Models\ProductsAttribute::create(['product_id'=>$product->id,'size'=>'Black','sku'=>'BLACK','price'=>12000,'stock'=>10,'status'=>1]);
        $this->withSession(['shop_channel_id'=>$shop->id])->postJson(route('front.shop.cart.add'), [
            'shop_product_id'=>$shopProduct->id,'options'=>[['option'=>'Black','qty'=>1],['option'=>'Invalid','qty'=>1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('option');
        $this->assertSame([], session('shop_channel_cart', []));
    }

    public function test_readding_an_option_after_renaming_its_cart_row_preserves_both_options(): void
    {
        [, , $shop, , $shopProduct] = $this->createShopProduct('option-rename', 'OPTION-RENAME', 'Rename');
        $this->withSession(['shop_channel_id' => $shop->id])->post(route('front.shop.cart.add'), [
            'shop_product_id' => $shopProduct->id,
            'options' => [['option' => 'Black', 'qty' => 1], ['option' => 'White', 'qty' => 2]],
        ])->assertRedirect();
        $whiteKey = collect(session('shop_channel_cart'))->search(fn ($row) => $row['option'] === 'White');
        $this->post(route('front.shop.cart.update'), [
            'shop_product_id' => $shopProduct->id, 'cart_key' => $whiteKey, 'qty' => 2, 'option' => 'Red',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('front.shop.cart.add'), [
            'shop_product_id' => $shopProduct->id, 'qty' => 1, 'option' => 'White',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(['Black' => 1, 'Red' => 2, 'White' => 1], collect(session('shop_channel_cart'))->pluck('qty', 'option')->all());
    }

    public function test_sms_log_driver_does_not_record_delivery_or_charge_the_order(): void
    {
        config(['services.sms.driver' => 'log']);
        [, , $shop, $product] = $this->createShopProduct('sms-log', 'SMS-LOG', 'Sms Log');
        $shop->update(['use_purchase_sms' => true, 'purchase_sms_templates' => ['purchase' => 'Order received']]);
        $order = $this->createOrderForShop($shop, $product, 'Sms Customer');
        $item = $order->orders_products->first();
        $log = app(\App\Services\ShopChannelSmsService::class)->send($shop, $order, $item, 'purchase');

        $this->assertSame('simulated', $log->status);
        $this->assertSame(0, $log->billing_amount);
        $this->assertNull($log->sent_at);
        $this->assertSame(0, (int) $item->fresh()->sms_fee);
        $this->assertSame(0, (int) $item->fresh()->sms_count);
    }

    public function test_sms_failure_does_not_turn_a_committed_claim_into_an_error_response(): void
    {
        [, , $shop, $product] = $this->createShopProduct('sms-failure', 'SMS-FAILURE', 'Sms Failure');
        $shop->update(['use_purchase_sms' => true, 'purchase_sms_templates' => ['cancel' => 'Cancellation received']]);
        $order = $this->createOrderForShop($shop, $product, 'Sms Failure Customer');
        $item = $order->orders_products->first();
        \App\Models\ShopChannelSmsLog::creating(fn () => throw new \RuntimeException('SMS log unavailable'));
        try {
            $this->withSession(['shop_channel_id' => $shop->id, 'nonmember_order_id' => $order->id])
                ->post(route('front.shop.order.item.status', $item->id), ['action' => 'cancel', 'reason' => 'Changed mind'])
                ->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame(OrderItemStatus::CANCEL_REQUESTED, $item->fresh()->status_code);
            $this->assertDatabaseCount('order_claims', 1);
        } finally {
            \App\Models\ShopChannelSmsLog::flushEventListeners();
        }
    }

    public function test_non_development_environment_cannot_complete_mock_payments(): void
    {
        config(['shop_channel.payment_driver' => 'mock']);
        $this->app->instance('env', 'staging');
        $this->assertFalse(app(\App\Services\ShopChannelRuntime::class)->canCheckout());
    }

    public function test_sms_fees_accumulate_without_overwriting_a_stale_order_item(): void
    {
        config(['services.sms.driver' => 'http', 'services.sms.endpoint' => 'https://sms.example.test/send',
            'services.sms.authorization' => 'test-only', 'services.sms.sender_id' => 'test-only']);
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake(['sms.example.test/*' => \Illuminate\Support\Facades\Http::response('accepted', 200)]);
        [, , $shop, $product] = $this->createShopProduct('sms-fees', 'SMS-FEES', 'Sms Fees');
        $shop->update(['use_purchase_sms' => true, 'purchase_sms_templates' => ['purchase' => 'Received', 'cancel' => 'Cancelled']]);
        $order = $this->createOrderForShop($shop, $product, 'Sms Fees Customer');
        $item = $order->orders_products->first();
        $sms = app(\App\Services\ShopChannelSmsService::class);
        $sms->send($shop, $order, $item, 'purchase');
        $sms->send($shop, $order, $item, 'cancel');

        $this->assertSame(200, (int) $item->fresh()->sms_fee);
        $this->assertSame(2, (int) $item->fresh()->sms_count);
        $this->assertSame(2, \App\Models\ShopChannelSmsLog::where('status', 'sent')->where('billing_amount', 100)->count());
    }

    public function test_failed_sms_is_not_billable(): void
    {
        config(['services.sms.driver' => 'http', 'services.sms.authorization' => null, 'services.sms.sender_id' => null]);
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        [, , $shop, $product] = $this->createShopProduct('sms-no-provider', 'SMS-NO-PROVIDER', 'Sms No Provider');
        $shop->update(['use_purchase_sms' => true, 'purchase_sms_templates' => ['purchase' => 'Received']]);
        $order = $this->createOrderForShop($shop, $product, 'Missing Provider Customer');
        $item = $order->orders_products->first();
        $log = app(\App\Services\ShopChannelSmsService::class)->send($shop, $order, $item, 'purchase');

        $this->assertSame('failed', $log->status);
        $this->assertSame(0, $log->billing_amount);
        $this->assertNull($log->sent_at);
        $this->assertSame(0, (int) $item->fresh()->sms_fee);
    }

    public function test_order_storage_failure_rolls_back_order_and_stock_and_keeps_cart(): void
    {
        Mail::fake();
        [, , $shop, , $shopProduct] = $this->createShopProduct('rollback', 'ROLLBACK', 'Rollback');
        $cart = [$shopProduct->id => ['qty' => 2, 'option' => '기본옵션']];
        OrdersProduct::creating(fn () => throw new \RuntimeException('Simulated order item failure'));
        try {
            $this->withSession(['shop_channel_id' => $shop->id, 'shop_channel_cart' => $cart])
                ->postJson(route('front.shop.order.checkout'), $this->checkoutPayload())->assertStatus(500);
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('orders_products', 0);
            $this->assertSame(10, (int) $shopProduct->fresh()->stock);
            $this->assertSame($cart, session('shop_channel_cart'));
            Mail::assertNothingQueued();
        } finally {
            OrdersProduct::flushEventListeners();
        }
    }
}
