<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('contacts', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('contacts', 'shop_channel_id')) {
                $table->unsignedBigInteger('shop_channel_id')->nullable()->after('vendor_id')->index();
            }
            if (!Schema::hasColumn('contacts', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('shop_channel_id')->index();
            }
            if (!Schema::hasColumn('contacts', 'order_product_id')) {
                $table->unsignedBigInteger('order_product_id')->nullable()->after('order_id')->index();
            }
            if (!Schema::hasColumn('contacts', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('order_product_id')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            foreach ([
                'product_id',
                'order_product_id',
                'order_id',
                'shop_channel_id',
                'vendor_id',
                'user_id',
            ] as $column) {
                if (Schema::hasColumn('contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
