<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Distributor extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'email',
        'password',
        'phone',
        'return_postcode',
        'return_address',
        'dispatch_postcode',
        'dispatch_address',
        'dispatch_address_detail',
        'memo',
        'status',
        'access_started_at',
        'access_ended_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'access_started_at' => 'datetime',
        'access_ended_at' => 'datetime',
    ];

    public function canAccess(): bool
    {
        return (int) $this->status === 1
            && (! $this->access_started_at || $this->access_started_at->lte(now()))
            && (! $this->access_ended_at || $this->access_ended_at->gte(now()));
    }

    public function getAccessStatusAttribute(): string
    {
        if ((int) $this->status !== 1) {
            return '중지';
        }
        if ($this->access_started_at?->isFuture()) {
            return '접속 전';
        }
        if ($this->access_ended_at?->isPast()) {
            return '기간 만료';
        }

        return '운영';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function ordersProducts()
    {
        return $this->hasMany(OrdersProduct::class);
    }
}
