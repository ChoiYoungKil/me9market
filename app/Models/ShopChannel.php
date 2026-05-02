<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'shop_name',
        'shop_code',
        'shop_url',
        'shop_logo',
        'description',
        'status',
        'settlement_type',
        'settlement_rate',
        'settlement_amount',
    ];

    public function vendor()
    {
        return $this->belongsTo(Admin::class, 'vendor_id'); // Assuming Vendor is in Admin table
    }

    public function shopChannelProducts()
    {
        return $this->hasMany(ShopChannelProduct::class);
    }
}
