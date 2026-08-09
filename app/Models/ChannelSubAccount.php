<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelSubAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'admin_id',
        'member_no',
        'started_at',
        'ended_at',
        'permissions',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'permissions' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
