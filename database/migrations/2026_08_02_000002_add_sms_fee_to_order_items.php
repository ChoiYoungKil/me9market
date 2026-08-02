<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders_products')) {
            return;
        }

        Schema::table('orders_products', function (Blueprint $table) {
            if (!Schema::hasColumn('orders_products', 'sms_count')) {
                $table->unsignedInteger('sms_count')->default(0)->after('extra_shipping_fee');
            }
            if (!Schema::hasColumn('orders_products', 'sms_fee')) {
                $table->integer('sms_fee')->default(0)->after('sms_count');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders_products')) {
            return;
        }

        Schema::table('orders_products', function (Blueprint $table) {
            foreach (['sms_fee', 'sms_count'] as $column) {
                if (Schema::hasColumn('orders_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
