<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelPointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'shop_channel_id',
        'admin_id',
        'user_id',
        'order_id',
        'order_product_id',
        'type',
        'status',
        'points',
        'payment_amount',
        'payment_method',
        'memo',
        'reference_type',
        'reference_id',
        'requested_at',
        'approved_by',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }

    public function orderProduct()
    {
        return $this->belongsTo(OrdersProduct::class, 'order_product_id');
    }
}
