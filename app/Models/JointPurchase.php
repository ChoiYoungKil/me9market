<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JointPurchase extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['benefits' => 'array', 'period_enabled' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function state($row): int
    {
        $today = now('Asia/Seoul')->format('Y-m-d');
        if ((int) $row->status === 2 || ($row->end_date && substr($row->end_date, 0, 10) < $today)) {
            return 2;
        }
        if ((int) $row->status === 0 || ($row->start_date && substr($row->start_date, 0, 10) > $today)) {
            return 0;
        }

        return 1;
    }
}
