<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('joint_purchase_price_tiers')) {
            Schema::create('joint_purchase_price_tiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('joint_purchase_id');
                $table->integer('min_quantity');
                $table->integer('max_quantity')->nullable();
                $table->decimal('unit_price', 12, 2);
                $table->timestamps();

                $table->index('joint_purchase_id');
                $table->index(['min_quantity', 'max_quantity']);
            });
        }

        if (Schema::hasTable('orders_products')) {
            Schema::table('orders_products', function (Blueprint $table) {
                if (!Schema::hasColumn('orders_products', 'joint_purchase_id')) {
                    $table->unsignedBigInteger('joint_purchase_id')->nullable()->after('shop_channel_product_id');
                }
                if (!Schema::hasColumn('orders_products', 'joint_price_tier_id')) {
                    $table->unsignedBigInteger('joint_price_tier_id')->nullable()->after('joint_purchase_id');
                }
                if (!Schema::hasColumn('orders_products', 'original_unit_price')) {
                    $table->decimal('original_unit_price', 12, 2)->nullable()->after('selling_price');
                }
                if (!Schema::hasColumn('orders_products', 'original_line_total')) {
                    $table->decimal('original_line_total', 12, 2)->nullable()->after('original_unit_price');
                }
                if (!Schema::hasColumn('orders_products', 'repriced_unit_price')) {
                    $table->decimal('repriced_unit_price', 12, 2)->nullable()->after('original_line_total');
                }
                if (!Schema::hasColumn('orders_products', 'repriced_line_total')) {
                    $table->decimal('repriced_line_total', 12, 2)->nullable()->after('repriced_unit_price');
                }
                if (!Schema::hasColumn('orders_products', 'reprice_adjustment_amount')) {
                    $table->decimal('reprice_adjustment_amount', 12, 2)->default(0)->after('repriced_line_total');
                }
                if (!Schema::hasColumn('orders_products', 'reprice_status')) {
                    $table->string('reprice_status', 30)->nullable()->after('reprice_adjustment_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders_products')) {
            Schema::table('orders_products', function (Blueprint $table) {
                foreach ([
                    'joint_purchase_id',
                    'joint_price_tier_id',
                    'original_unit_price',
                    'original_line_total',
                    'repriced_unit_price',
                    'repriced_line_total',
                    'reprice_adjustment_amount',
                    'reprice_status',
                ] as $column) {
                    if (Schema::hasColumn('orders_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('joint_purchase_price_tiers');
    }
};
