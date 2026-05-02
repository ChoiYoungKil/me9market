<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCancelRefundPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'type',
        'status',
        'name',
        'content',
        'product_count'
    ];

    /**
     * 판매자와의 관계
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
