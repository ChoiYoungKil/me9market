<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'channel_code',
        'status',
        'is_public',
        'password',
        'is_member_only',
        'channel_name',
        'copyright',
        'keywords',
        'use_period_type',
        'start_at',
        'end_at',
        'use_logo',
        'logo_image',
        'use_banner',
        'banner_images',
        'use_og',
        'og_title',
        'og_description',
        'og_image',
        'use_admin',
        'admin_name',
        'admin_login_id',
        'admin_password',
        'settlement_type',
        'settlement_rate',
    ];

    protected $casts = [
        'keywords' => 'array',
        'banner_images' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function shopChannelProducts()
    {
        return $this->hasMany(ShopChannelProduct::class);
    }

    public function activeProducts()
    {
        return $this->hasMany(ShopChannelProduct::class)
            ->where('status', 1)
            ->where('approval_status', 'approved');
    }

    public function notices()
    {
        return $this->hasMany(ShopChannelNotice::class);
    }
}
