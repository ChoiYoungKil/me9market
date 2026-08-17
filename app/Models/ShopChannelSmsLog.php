<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChannelSmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_channel_id',
        'vendor_id',
        'order_id',
        'order_product_id',
        'template_type',
        'recipient_phone',
        'message',
        'billing_amount',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected $casts = [
        'billing_amount' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProduct()
    {
        return $this->belongsTo(OrdersProduct::class, 'order_product_id');
    }
}
