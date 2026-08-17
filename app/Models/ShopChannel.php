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
        'closure_status',
        'closure_requested_at',
        'closure_approved_at',
        'closure_rejected_at',
        'closure_reviewed_by',
        'closure_memo',
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
        'use_own_pg',
        'pg_provider',
        'pg_merchant_id',
        'pg_site_code',
        'pg_client_key',
        'pg_secret_key',
        'use_purchase_sms',
        'purchase_sms_templates',
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
        'closure_requested_at' => 'datetime',
        'closure_approved_at' => 'datetime',
        'closure_rejected_at' => 'datetime',
        'use_own_pg' => 'boolean',
        'pg_merchant_id' => 'encrypted',
        'pg_site_code' => 'encrypted',
        'pg_client_key' => 'encrypted',
        'pg_secret_key' => 'encrypted',
        'use_purchase_sms' => 'boolean',
        'purchase_sms_templates' => 'array',
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

    public function privateAccesses()
    {
        return $this->hasMany(ShopChannelPrivateAccess::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(ShopChannelSmsLog::class);
    }
}
