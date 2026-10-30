<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'vendor_id',
        'order_product_id',
        'type',
        'reason',
        'detail_reason',
        'pickup_method',
        'return_address',
        'original_option',
        'exchange_recipient_name',
        'exchange_recipient_mobile',
        'exchange_recipient_address',
        'pickup_name',
        'pickup_mobile',
        'customer_courier_name',
        'customer_tracking_number',
        'customer_shipped_at',
        'status',
        'admin_comment',
    ];

    protected $casts = [
        'customer_shipped_at' => 'datetime',
    ];

    public function getLifecycleDatesAttribute(): array
    {
        $dates=['requested'=>$this->created_at?->format('Y-m-d H:i:s'),'withdrawn'=>null,'rejected'=>null,'completed'=>null];
        foreach (['withdrawn'=>'철회','rejected'=>'거절|거부','completed'=>'취소 승인|반품 확정|교환 확정'] as $key=>$labels) {
            preg_match_all('/^\[([^\]]+)\] (?:'.$labels.')(?:$|:)/mu',(string)$this->admin_comment,$matches);
            $dates[$key]=$matches[1] ? end($matches[1]) : ($this->status===$key ? $this->updated_at?->format('Y-m-d H:i:s') : null);
        }
        return $dates;
    }

    public function getStatusLabelAttribute(): string
    {
        return ['requested'=>'신청','received'=>'회수완료','held'=>'보류','withdrawn'=>'철회','rejected'=>'거절','completed'=>'완료','approved'=>'승인','held_before'=>'회수 전 보류','held_after'=>'회수 후 보류','converted_to_return'=>'반품 전환'][$this->status] ?? $this->status;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        // Assuming OrderProduct has product_id or directly links to Product
        // But here we link to the specific order item
        return $this->belongsTo(OrdersProduct::class, 'order_product_id');
    }
}
