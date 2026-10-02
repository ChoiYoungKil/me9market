<?php

namespace App\Models;

use App\Support\OrderItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'vendor_id',
        'shop_channel_id',
        'shop_channel_product_id',
        'joint_purchase_id',
        'joint_price_tier_id',
        'admin_id',
        'product_id',
        'distributor_id',
        'product_code',
        'product_name',
        'product_color',
        'product_size',
        'option_price_adjustment',
        'claim_previous_status',
        'product_price',
        'supply_price',
        'selling_price',
        'original_unit_price',
        'original_line_total',
        'repriced_unit_price',
        'repriced_line_total',
        'reprice_adjustment_amount',
        'reprice_status',
        'product_qty',
        'line_total',
        'item_status',
        'status_code',
        'courier_name',
        'tracking_number',
        'commission',
        'settlement_status',
        'payment_gateway_type',
        'settlement_policy_snapshot',
        'shipping_amount_snapshot',
        'used_point_amount',
        'point_usage_snapshot',
        'paid_line_total_snapshot',
        'stock_deducted_qty',
        'stock_attribute_id',
        'attribute_stock_deducted_qty',
        'financial_reversed_at',
        'refund_status',
        'refund_cash_amount',
        'shipped_at',
        'delivered_at',
        'confirmed_at',
        'return_shipping_fee',
        'exchange_shipping_fee',
        'extra_shipping_fee',
        'sms_count',
        'sms_fee',
        'replacement_for_order_product_id',
        'is_exchange_replacement',
    ];

    protected $casts = [
        'settlement_policy_snapshot' => 'array',
        'shipping_amount_snapshot' => 'decimal:2',
        'used_point_amount' => 'integer',
        'point_usage_snapshot' => 'array',
        'stock_deducted_qty' => 'integer',
        'financial_reversed_at' => 'datetime',
        'refund_cash_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'return_shipping_fee' => 'integer',
        'exchange_shipping_fee' => 'integer',
        'extra_shipping_fee' => 'integer',
        'sms_count' => 'integer',
        'sms_fee' => 'integer',
        'is_exchange_replacement' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class, 'shop_channel_id');
    }

    public function shopChannelProduct()
    {
        return $this->belongsTo(ShopChannelProduct::class, 'shop_channel_product_id');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function replacementFor()
    {
        return $this->belongsTo(self::class, 'replacement_for_order_product_id');
    }

    public function exchangeReplacement()
    {
        return $this->hasOne(self::class, 'replacement_for_order_product_id');
    }

    public function claims()
    {
        return $this->hasMany(OrderClaim::class, 'order_product_id');
    }

    public function getNormalizedStatusAttribute(): string
    {
        return OrderItemStatus::normalize($this->status_code ?: $this->item_status);
    }

    public function getStatusLabelAttribute(): string
    {
        return OrderItemStatus::label($this->normalized_status);
    }

    public function getRefundStatusLabelAttribute(): string
    {
        return match ($this->refund_status) {
            'mock_refunded' => '모의 환불 반영',
            'pending_external' => 'PG 환불 대기',
            'review_required' => '환불 근거 확인 필요',
            'not_required' => '현금 환불 없음',
            'reversed_original' => '원주문 환불 참조',
            default => '환불 미처리',
        };
    }

    public function setStatus(string $status): void
    {
        $normalized = OrderItemStatus::normalize($status);
        if (in_array($normalized, [OrderItemStatus::CANCEL_REQUESTED, OrderItemStatus::RETURN_REQUESTED, OrderItemStatus::EXCHANGE_REQUESTED], true)
            && in_array($this->normalized_status, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP, OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED], true)) {
            $this->claim_previous_status = $this->normalized_status;
        }
        $this->status_code = $normalized;
        $this->item_status = OrderItemStatus::label($normalized);
    }
}
