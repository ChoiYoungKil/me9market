<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_channel_id',
        'order_id',
        'order_product_id',
        'type',
        'points',
        'description',
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
