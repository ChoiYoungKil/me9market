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
            if (!Schema::hasColumn('orders_products', 'return_shipping_fee')) {
                $table->integer('return_shipping_fee')->default(0)->after('confirmed_at');
            }
            if (!Schema::hasColumn('orders_products', 'exchange_shipping_fee')) {
                $table->integer('exchange_shipping_fee')->default(0)->after('return_shipping_fee');
            }
            if (!Schema::hasColumn('orders_products', 'extra_shipping_fee')) {
                $table->integer('extra_shipping_fee')->default(0)->after('exchange_shipping_fee');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders_products')) {
            return;
        }

        Schema::table('orders_products', function (Blueprint $table) {
            foreach (['extra_shipping_fee', 'exchange_shipping_fee', 'return_shipping_fee'] as $column) {
                if (Schema::hasColumn('orders_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
