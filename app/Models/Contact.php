<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'shop_channel_id',
        'order_id',
        'order_product_id',
        'product_id',
        'name',
        'email',
        'phone',
        'company',
        'subject',
        'message',
        'type',
        'status',
        'admin_reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProduct()
    {
        return $this->belongsTo(OrdersProduct::class, 'order_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
