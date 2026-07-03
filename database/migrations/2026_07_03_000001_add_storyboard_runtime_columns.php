<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shop_channel_products')) {
            Schema::table('shop_channel_products', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_channel_products', 'approval_status')) {
                    $table->string('approval_status')->default('approved')->after('product_type');
                }
                if (!Schema::hasColumn('shop_channel_products', 'distributor_id')) {
                    $table->unsignedBigInteger('distributor_id')->nullable()->after('product_id');
                }
            });
        }

        if (Schema::hasTable('orders_products')) {
            Schema::table('orders_products', function (Blueprint $table) {
                if (!Schema::hasColumn('orders_products', 'shop_channel_id')) {
                    $table->unsignedBigInteger('shop_channel_id')->nullable()->after('vendor_id');
                }
                if (!Schema::hasColumn('orders_products', 'shop_channel_product_id')) {
                    $table->unsignedBigInteger('shop_channel_product_id')->nullable()->after('shop_channel_id');
                }
                if (!Schema::hasColumn('orders_products', 'status_code')) {
                    $table->string('status_code', 40)->default('paid')->after('item_status');
                }
                if (!Schema::hasColumn('orders_products', 'supply_price')) {
                    $table->decimal('supply_price', 12, 2)->default(0)->after('product_price');
                }
                if (!Schema::hasColumn('orders_products', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0)->after('supply_price');
                }
                if (!Schema::hasColumn('orders_products', 'line_total')) {
                    $table->decimal('line_total', 12, 2)->default(0)->after('product_qty');
                }
                if (!Schema::hasColumn('orders_products', 'settlement_status')) {
                    $table->string('settlement_status', 40)->default('pending')->after('commission');
                }
                if (!Schema::hasColumn('orders_products', 'shipped_at')) {
                    $table->timestamp('shipped_at')->nullable()->after('tracking_number');
                }
                if (!Schema::hasColumn('orders_products', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('shipped_at');
                }
                if (!Schema::hasColumn('orders_products', 'confirmed_at')) {
                    $table->timestamp('confirmed_at')->nullable()->after('delivered_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders_products')) {
            Schema::table('orders_products', function (Blueprint $table) {
                foreach ([
                    'shop_channel_id',
                    'shop_channel_product_id',
                    'status_code',
                    'supply_price',
                    'selling_price',
                    'line_total',
                    'settlement_status',
                    'shipped_at',
                    'delivered_at',
                    'confirmed_at',
                ] as $column) {
                    if (Schema::hasColumn('orders_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('shop_channel_products')) {
            Schema::table('shop_channel_products', function (Blueprint $table) {
                foreach (['approval_status', 'distributor_id'] as $column) {
                    if (Schema::hasColumn('shop_channel_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
