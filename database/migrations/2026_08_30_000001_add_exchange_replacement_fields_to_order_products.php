<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            if (! Schema::hasColumn('orders_products', 'replacement_for_order_product_id')) {
                $table->unsignedBigInteger('replacement_for_order_product_id')->nullable()->after('order_id');
                $table->index('replacement_for_order_product_id', 'order_products_replacement_for_idx');
            }
            if (! Schema::hasColumn('orders_products', 'is_exchange_replacement')) {
                $table->boolean('is_exchange_replacement')->default(false)->after('replacement_for_order_product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            if (Schema::hasColumn('orders_products', 'replacement_for_order_product_id')) {
                $table->dropIndex('order_products_replacement_for_idx');
                $table->dropColumn('replacement_for_order_product_id');
            }
            if (Schema::hasColumn('orders_products', 'is_exchange_replacement')) {
                $table->dropColumn('is_exchange_replacement');
            }
        });
    }
};
