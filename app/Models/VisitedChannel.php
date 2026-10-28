<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitedChannel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }

    // Relationship with User
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    // Relationship with Vendor (Channel)
    public function vendor() {
        return $this->belongsTo('App\Models\Vendor', 'vendor_id');
    }
}
