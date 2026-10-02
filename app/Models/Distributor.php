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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function ordersProducts()
    {
        return $this->hasMany(OrdersProduct::class);
    }
}
