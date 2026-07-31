<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementExecution extends Model
{
    protected $fillable = [
        'period',
        'settlement_run_id',
        'vendor_id',
        'shop_channel_id',
        'title',
        'amount',
        'executed_at',
        'registered_by',
        'memo',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'executed_at' => 'datetime',
    ];

    public function settlementRun()
    {
        return $this->belongsTo(SettlementRun::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }
}
