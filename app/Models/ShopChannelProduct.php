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
        'product_type',
        'status',
        'constraint_type',
        'stock',
        'purchase_limit',
        'product_price',
        'selling_price',
        'profit',
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
}
