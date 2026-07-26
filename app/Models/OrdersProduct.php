<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\OrderItemStatus;

class OrdersProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'vendor_id',
        'shop_channel_id',
        'shop_channel_product_id',
        'joint_purchase_id',
        'joint_price_tier_id',
        'admin_id',
        'product_id',
        'distributor_id',
        'product_code',
        'product_name',
        'product_color',
        'product_size',
        'product_price',
        'supply_price',
        'selling_price',
        'original_unit_price',
        'original_line_total',
        'repriced_unit_price',
        'repriced_line_total',
        'reprice_adjustment_amount',
        'reprice_status',
        'product_qty',
        'line_total',
        'item_status',
        'status_code',
        'courier_name',
        'tracking_number',
        'commission',
        'settlement_status',
        'shipped_at',
        'delivered_at',
        'confirmed_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class, 'shop_channel_id');
    }

    public function shopChannelProduct()
    {
        return $this->belongsTo(ShopChannelProduct::class, 'shop_channel_product_id');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function getNormalizedStatusAttribute(): string
    {
        return OrderItemStatus::normalize($this->status_code ?: $this->item_status);
    }

    public function getStatusLabelAttribute(): string
    {
        return OrderItemStatus::label($this->normalized_status);
    }

    public function setStatus(string $status): void
    {
        $normalized = OrderItemStatus::normalize($status);
        $this->status_code = $normalized;
        $this->item_status = OrderItemStatus::label($normalized);
    }
}
