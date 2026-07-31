<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementRun extends Model
{
    protected $fillable = [
        'settlement_key',
        'settlement_role',
        'payment_gateway_type',
        'period',
        'vendor_id',
        'shop_channel_id',
        'vendor_name',
        'shop_channel_name',
        'settlement_type',
        'settlement_rate',
        'order_count',
        'item_count',
        'quantity',
        'gross_sales_amount',
        'supply_amount',
        'sales_profit_amount',
        'invoice_sales_amount',
        'invoice_purchase_amount',
        'point_deposit_amount',
        'point_used_amount',
        'payout_amount',
        'settlement_amount',
        'admin_amount',
        'status',
        'executed_at',
        'executed_by',
        'memo',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SettlementItem::class);
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
