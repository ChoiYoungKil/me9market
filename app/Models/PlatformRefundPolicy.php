<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRefundPolicy extends Model
{
    protected $fillable = ['content', 'updated_by'];

    public static function currentContent(): string
    {
        return (string) static::find(1)?->content;
    }
}
