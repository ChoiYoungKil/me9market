<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'used_point')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('used_point', 14, 2)->default(0)->after('coupon_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'used_point')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('used_point');
            });
        }
    }
};
