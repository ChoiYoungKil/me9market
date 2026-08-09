<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Models\Vendor;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrossRoleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function createLinkedOrder(string $prefix = 'Linked'): array
    {
        $vendor = Vendor::create([
            'name' => $prefix . ' Vendor',
            'mobile' => '010-1000-2000',
            'email' => strtolower($prefix) . '-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);

        $channelAdmin = new Admin;
        $channelAdmin->name = $prefix . ' Channel Admin';
        $channelAdmin->type = 'vendor';
        $channelAdmin->vendor_id = $vendor->id;
        $channelAdmin->mobile = '01010002000';
        $channelAdmin->email = strtolower($prefix) . '-channel@example.com';
        $channelAdmin->password = Hash::make('password');
        $channelAdmin->status = 1;
        $channelAdmin->save();

        $superAdmin = new Admin;
        $superAdmin->name = $prefix . ' Super Admin';
        $superAdmin->type = 'admin';
        $superAdmin->vendor_id = 0;
        $superAdmin->mobile = '01030004000';
        $superAdmin->email = strtolower($prefix) . '-admin@example.com';
        $superAdmin->password = Hash::make('password');
        $superAdmin->status = 1;
        $superAdmin->save();

        $user = User::create([
            'name' => $prefix . ' User',
            'email' => strtolower($prefix) . '-user@example.com',
            'password' => Hash::make('password'),
        ]);

        $distributor = Distributor::create([
            'vendor_id' => $vendor->id,
            'name' => $prefix . ' Distributor',
            'email' => strtolower($prefix) . '-distributor@example.com',
            'password' => Hash::make('password'),
            'phone' => '01050006000',
            'status' => 1,
        ]);

        $shop = ShopChannel::create([
            'vendor_id' => $vendor->id,
            'channel_code' => strtolower($prefix) . '-shop',
            'channel_name' => $prefix . ' Shop',
            'copyright' => $prefix,
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
            'admin_id' => $channelAdmin->id,
            'admin_type' => 'vendor',
            'distributor_id' => $distributor->id,
            'order_manager_enabled' => true,
            'product_name' => $prefix . ' Product',
            'product_code' => strtoupper($prefix) . '-P001',
            'product_color' => 'Black',
            'product_price' => 10000,
            'product_discount' => 0,
            'product_weight' => 1,
            'status' => 1,
        ]);

        $shopProduct = ShopChannelProduct::create([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'distributor_id' => $distributor->id,
            'product_type' => 'own',
            'approval_status' => 'approved',
            'status' => 1,
            'constraint_type' => 'none',
            'stock' => 10,
            'product_price' => 10000,
            'selling_price' => 12500,
            'profit' => 2500,
        ]);

        $order = new Order;
        $order->user_id = $user->id;
        $order->name = $prefix . ' Receiver';
        $order->address = $prefix . ' Address';
        $order->city = 'Seoul';
        $order->state = 'Jung';
        $order->country = 'Korea';
        $order->pincode = '04524';
        $order->mobile = '01011112222';
        $order->email = $user->email;
        $order->shipping_charges = 2500;
        $order->coupon_code = '';
        $order->coupon_amount = 0;
        $order->order_status = 'Payment Captured';
        $order->payment_method = 'Card';
        $order->payment_gateway = 'Test';
        $order->grand_total = 12500;
        $order->save();

        $item = OrdersProduct::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'shop_channel_id' => $shop->id,
            'shop_channel_product_id' => $shopProduct->id,
            'product_id' => $product->id,
            'distributor_id' => $distributor->id,
            'admin_id' => $channelAdmin->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'product_color' => $product->product_color,
            'product_size' => '기본옵션',
            'product_price' => 12500,
            'supply_price' => 10000,
            'selling_price' => 12500,
            'product_qty' => 1,
            'line_total' => 12500,
            'item_status' => OrderItemStatus::label(OrderItemStatus::PAID),
            'status_code' => OrderItemStatus::PAID,
            'commission' => 0,
            'settlement_status' => 'pending',
        ]);

        return compact('vendor', 'channelAdmin', 'superAdmin', 'user', 'distributor', 'shop', 'product', 'shopProduct', 'order', 'item');
    }

    public function test_distributor_shipping_update_is_visible_to_channel_admin_admin_and_user()
    {
        $data = $this->createLinkedOrder('Flow');
        $item = $data['item'];
        $distributor = $data['distributor'];

        $this->withSession(['distributor_id' => $distributor->id])
            ->post(route('distributor.order.update', $item->id), [
                'receiver' => 'Flow Receiver',
                'zipcode' => '04524',
                'address' => 'Flow Address Updated',
                'city' => 'Seoul',
                'state' => 'Jung',
                'courier' => 'CJ대한통운',
                'tracking_no' => 'TRK-FLOW-001',
                'status_code' => OrderItemStatus::SHIPPING,
            ])
            ->assertRedirect(route('distributor.order.details', $item->id));

        $this->assertDatabaseHas('orders_products', [
            'id' => $item->id,
            'status_code' => OrderItemStatus::SHIPPING,
            'item_status' => '배송중',
            'courier_name' => 'CJ대한통운',
            'tracking_number' => 'TRK-FLOW-001',
        ]);

        $this->withSession(['distributor_id' => $distributor->id])
            ->get(route('distributor.orders.completed'))
            ->assertOk()
            ->assertSee('Flow Product')
            ->assertSee('배송중');

        $this->actingAs($data['channelAdmin'], 'admin')
            ->get(route('channel.order.list', [
                'search_type' => 'tracking_number',
                'keyword' => 'TRK-FLOW-001',
            ]))
            ->assertOk()
            ->assertSee('Flow Product')
            ->assertSee('배송중');

        $this->actingAs($data['superAdmin'], 'admin')
            ->get(route('admin.orders') . '/' . $data['order']->id)
            ->assertOk()
            ->assertSee('Flow Product')
            ->assertSee('TRK-FLOW-001');

        $this->actingAs($data['user'])
            ->get(route('mypage.order.view', ['id' => $data['order']->id]))
            ->assertOk()
            ->assertSee('Flow Product')
            ->assertSee('배송중');
    }

    public function test_distributor_cannot_read_or_update_other_distributor_orders()
    {
        $own = $this->createLinkedOrder('OwnFlow');
        $other = $this->createLinkedOrder('OtherFlow');

        $this->withSession(['distributor_id' => $own['distributor']->id])
            ->get(route('distributor.order.details', $other['item']->id))
            ->assertNotFound();

        $this->withSession(['distributor_id' => $own['distributor']->id])
            ->post(route('distributor.order.update', $other['item']->id), [
                'courier' => 'Wrong Courier',
                'tracking_no' => 'WRONG-TRACKING',
                'status_code' => OrderItemStatus::SHIPPING,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('orders_products', [
            'id' => $other['item']->id,
            'status_code' => OrderItemStatus::PAID,
            'tracking_number' => null,
        ]);
    }
}
