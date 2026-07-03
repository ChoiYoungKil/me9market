<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_channel_product_id',
    ];

    public function shopChannelProduct()
    {
        return $this->belongsTo(ShopChannelProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
