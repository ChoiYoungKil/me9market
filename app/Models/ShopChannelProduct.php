<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChannelProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_channel_id',
        'product_id',
        'distributor_id',
        'product_type',
        'approval_status',
        'request_reason',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'status',
        'constraint_type',
        'stock',
        'purchase_limit',
        'product_price',
        'selling_price',
        'profit',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationship with ShopChannel
    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrdersProduct::class, 'shop_channel_product_id');
    }
}
