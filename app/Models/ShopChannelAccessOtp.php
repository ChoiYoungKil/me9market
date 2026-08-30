<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopChannelAccessOtp extends Model
{
    protected $fillable = [
        'shop_channel_private_access_id',
        'phone_normalized',
        'code_hash',
        'attempts',
        'expires_at',
        'sent_at',
        'verified_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function privateAccess()
    {
        return $this->belongsTo(ShopChannelPrivateAccess::class, 'shop_channel_private_access_id');
    }
}
