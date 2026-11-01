<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNoticeComment extends Model
{
    protected $fillable = ['notice_id', 'admin_id', 'author_name', 'content'];
}
