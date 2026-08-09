<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelDeliveryCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'status',
        'name',
        'courier',
        'shipping_type',
        'payment_type',
        'base_fee',
        'free_order_amount',
        'free_order_quantity',
        'fixed_fee',
        'product_count',
        'memo',
    ];

    protected $casts = [
        'status' => 'integer',
        'base_fee' => 'integer',
        'free_order_amount' => 'integer',
        'free_order_quantity' => 'integer',
        'fixed_fee' => 'integer',
        'product_count' => 'integer',
    ];
}
