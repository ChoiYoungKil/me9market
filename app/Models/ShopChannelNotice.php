<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChannelNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_channel_id',
        'type',
        'title',
        'author',
        'content',
        'attachment',
        'status',
        'view_count',
    ];

    // Relationship with ShopChannel
    public function shopChannel()
    {
        return $this->belongsTo(ShopChannel::class);
    }
}
