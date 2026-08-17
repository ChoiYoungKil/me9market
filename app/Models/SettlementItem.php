<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementItem extends Model
{
    protected $fillable = [
        'settlement_run_id',
        'order_product_id',
        'settlement_role',
        'payment_gateway_type',
        'order_id',
        'vendor_id',
        'shop_channel_id',
        'product_id',
        'order_no',
        'product_code',
        'product_name',
        'quantity',
        'gross_sales_amount',
        'supply_amount',
        'sales_profit_amount',
        'invoice_sales_amount',
        'invoice_purchase_amount',
        'point_deposit_amount',
        'point_used_amount',
        'sms_postpaid_amount',
        'payout_amount',
        'settlement_type',
        'settlement_rate',
        'settlement_amount',
        'admin_amount',
        'confirmed_at',
        'status',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function settlementRun()
    {
        return $this->belongsTo(SettlementRun::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrdersProduct::class, 'order_product_id');
    }
}
