<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'attachment',
        'is_important',
        'view_count',
        'status',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'status' => 'boolean',
    ];
}
