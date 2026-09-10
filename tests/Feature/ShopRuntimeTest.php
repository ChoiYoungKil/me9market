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
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['shop_channel.seed_demo_data' => false]);
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
}
