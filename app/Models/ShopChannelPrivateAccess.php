<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChannelPrivateAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_channel_id',
        'phone',
        'phone_normalized',
        'entry_code',
        'user_id',
        'first_accessed_at',
        'access_count',
        'purchase_count',
    ];

    protected $casts = [
        'first_accessed_at' => 'datetime',
        'access_count' => 'integer',
        'purchase_count' => 'integer',
    ];

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }

    public static function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: '';
    }
}
