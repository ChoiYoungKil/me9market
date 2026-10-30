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
        'user_id',
        'member_no',
        'started_at',
        'ended_at',
        'permissions',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'permissions' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActiveNow($query)
    {
        return $query->where(fn ($q) => $q->whereNull('started_at')->orWhere('started_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('user_id')->orWhereHas('member', fn ($q) => $q->where('status', 1)));
    }

    public function isActive(): bool
    {
        return (int) $this->admin?->status === 1
            && (! $this->started_at || $this->started_at->lte(now()))
            && (! $this->ended_at || $this->ended_at->gte(now()))
            && (! $this->user_id || (int) $this->member?->status === 1);
    }

    public function getAccessStatusAttribute(): string
    {
        if ((int) $this->admin?->status !== 1 || ($this->user_id && (int) $this->member?->status !== 1)) {
            return '중지';
        }
        if ($this->started_at?->isFuture()) {
            return '이용 전';
        }
        if ($this->ended_at?->isPast()) {
            return '기간 만료';
        }

        return '운영';
    }
}
