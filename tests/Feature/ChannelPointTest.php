<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ChannelPointTransaction;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ChannelPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ChannelPointTest extends TestCase
{
    use RefreshDatabase;

    private function setupVendor(): array
    {
        $vendor = new Vendor;
        $vendor->name = 'Point Vendor';
        $vendor->mobile = '010-1111-2222';
        $vendor->email = 'point-vendor@example.com';
        $vendor->status = 1;
        $vendor->commission = 0;
        $vendor->confirm = 'Yes';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Point Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01011112222';
        $admin->email = 'point-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $shop = new ShopChannel;
        $shop->vendor_id = $vendor->id;
        $shop->channel_code = 'point-shop';
        $shop->channel_name = 'Point Shop';
        $shop->copyright = 'Point Shop';
        $shop->keywords = '[]';
        $shop->status = 1;
        $shop->settlement_type = 1;
        $shop->settlement_rate = 10;
        $shop->save();

        return [$vendor, $admin, $shop];
    }

    public function test_admin_approval_adds_seller_point_balance()
    {
        [$vendor, $admin] = $this->setupVendor();
        $service = app(ChannelPointService::class);

        $transaction = $service->requestPurchase($vendor->id, 100000, 'card', '테스트 구매', null, $admin->id);

        $this->assertSame(0, $service->balanceForVendor($vendor->id));
        $service->approve($transaction, $admin->id);

        $this->assertSame(100000, $service->balanceForVendor($vendor->id));
        $this->assertDatabaseHas('channel_point_transactions', [
            'id' => $transaction->id,
            'status' => 'approved',
            'points' => 100000,
        ]);
    }

    public function test_refund_request_is_blocked_while_channel_is_active()
    {
        [$vendor, $admin] = $this->setupVendor();
        $service = app(ChannelPointService::class);
        $purchase = $service->requestPurchase($vendor->id, 100000, 'card', null, null, $admin->id);
        $service->approve($purchase, $admin->id);

        $this->expectException(ValidationException::class);

        $service->requestRefund($vendor->id, 10000, '운영 중 환급 요청', null, $admin->id);
    }

    public function test_purchase_confirm_payback_deducts_seller_points_and_credits_user()
    {
        [$vendor, $admin, $shop] = $this->setupVendor();
        $service = app(ChannelPointService::class);
        $purchase = $service->requestPurchase($vendor->id, 100000, 'card', null, null, $admin->id);
        $service->approve($purchase, $admin->id);

        $user = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        $product = Product::create([
            'section_id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'vendor_id' => $vendor->id,
            'admin_id' => $admin->id,
            'admin_type' => 'vendor',
            'product_name' => 'Reward Product',
            'product_code' => 'RP-001',
            'product_color' => 'Black',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 100,
            'is_featured' => 'No',
            'status' => 1,
            'reward_points' => 300,
        ]);

        $order = new Order;
        $order->user_id = $user->id;
        $order->name = 'Buyer';
        $order->address = 'Seoul';
        $order->city = 'Seoul';
        $order->state = 'Seoul';
        $order->country = 'Korea';
        $order->pincode = '12345';
        $order->mobile = '01000000000';
        $order->email = $user->email;
        $order->shipping_charges = 0;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Me9';
        $order->grand_total = 20000;
        $order->save();

        $item = OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'admin_id' => $admin->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => '기본',
            'product_price' => 10000,
            'product_qty' => 2,
            'line_total' => 20000,
            'item_status' => '구매확정',
        ]);

        $service->recordCustomerPayback($item);

        $this->assertSame(99400, $service->balanceForVendor($vendor->id));
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'order_product_id' => $item->id,
            'points' => 600,
        ]);
        $this->assertDatabaseHas('channel_point_transactions', [
            'vendor_id' => $vendor->id,
            'order_product_id' => $item->id,
            'type' => ChannelPointService::TYPE_CUSTOMER_PAYBACK,
            'points' => -600,
            'status' => 'approved',
        ]);
    }

    public function test_sms_debit_uses_seller_points()
    {
        [$vendor, $admin, $shop] = $this->setupVendor();
        $service = app(ChannelPointService::class);
        $purchase = $service->requestPurchase($vendor->id, 100000, 'card', null, null, $admin->id);
        $service->approve($purchase, $admin->id);

        $debit = $service->recordSmsDebit($vendor->id, 3, 20, $shop->id, '배송 문자 3건');

        $this->assertNotNull($debit);
        $this->assertSame(99940, $service->balanceForVendor($vendor->id));
        $this->assertDatabaseHas('channel_point_transactions', [
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'type' => ChannelPointService::TYPE_SMS,
            'points' => -60,
            'status' => 'approved',
        ]);
    }
}
