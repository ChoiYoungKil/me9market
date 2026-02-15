<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'vendor_id',
        'order_product_id',
        'type',
        'reason',
        'detail_reason',
        'status',
        'admin_comment'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        // Assuming OrderProduct has product_id or directly links to Product
        // But here we link to the specific order item
        return $this->belongsTo(OrdersProduct::class, 'order_product_id'); 
    }
}
