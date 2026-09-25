<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    public function orders_products()
    {
        return $this->hasMany(OrdersProduct::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claims()
    {
        return $this->hasMany(OrderClaim::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrdersProduct::class, 'order_id');
    }

    public static function pushOrder($orderId): array
    {
        $config = config('services.shiprocket');
        $required = ['email', 'password', 'pickup_location', 'channel_id', 'payment_method', 'length', 'breadth', 'height', 'weight'];

        if (collect($required)->contains(fn ($key) => blank($config[$key] ?? null))) {
            return ['status' => false, 'message' => '배송 연동 설정이 완료되지 않았습니다.'];
        }

        $order = self::with('order_items')->find($orderId);
        if (! $order) {
            return ['status' => false, 'message' => '주문을 찾을 수 없습니다.'];
        }

        $payload = [
            'order_id' => (string) $order->id,
            'order_date' => $order->created_at?->format('Y-m-d H:i:s'),
            'pickup_location' => $config['pickup_location'],
            'channel_id' => $config['channel_id'],
            'billing_customer_name' => $order->name,
            'billing_last_name' => '',
            'billing_address' => $order->address,
            'billing_address_2' => '',
            'billing_city' => $order->city,
            'billing_pincode' => $order->pincode,
            'billing_state' => $order->state,
            'billing_country' => $order->country,
            'billing_email' => $order->email,
            'billing_phone' => preg_replace('/\D+/', '', (string) $order->mobile),
            'shipping_is_billing' => true,
            'order_items' => $order->order_items->map(fn ($item) => [
                'name' => $item->product_name,
                'sku' => $item->product_code,
                'units' => (int) $item->product_qty,
                'selling_price' => (float) $item->product_price,
            ])->values()->all(),
            'payment_method' => $config['payment_method'],
            'shipping_charges' => (float) ($order->shipping_charges ?? 0),
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => (float) ($order->coupon_amount ?? 0),
            'sub_total' => (float) $order->grand_total,
            'length' => (float) $config['length'],
            'breadth' => (float) $config['breadth'],
            'height' => (float) $config['height'],
            'weight' => (float) $config['weight'],
        ];

        try {
            $client = Http::baseUrl(rtrim($config['base_url'], '/'))
                ->acceptJson()
                ->timeout(15)
                ->connectTimeout(5)
                ->retry(2, 250);

            $token = $client->post('/auth/login', [
                'email' => $config['email'],
                'password' => $config['password'],
            ])->throw()->json('token');

            if (blank($token)) {
                throw new \RuntimeException('Shipping provider returned no access token.');
            }

            $result = $client->withToken($token)
                ->post('/orders/create/adhoc', $payload)
                ->throw()
                ->json();

            if (($result['status_code'] ?? null) != 1) {
                throw new \RuntimeException('Shipping provider rejected the order.');
            }

            $order->update(['is_pushed' => 1]);

            return ['status' => true, 'message' => '주문이 성공적으로 전송되었습니다.'];
        } catch (\Throwable $e) {
            Log::error('Shipping order push failed.', [
                'order_id' => $order->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return ['status' => false, 'message' => '주문 전송에 실패했습니다. 관리자에게 문의하세요.'];
        }
    }
}
