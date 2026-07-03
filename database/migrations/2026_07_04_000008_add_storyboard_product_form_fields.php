<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sale_scope')) {
                $table->string('sale_scope', 30)->default('own')->after('is_partial');
            }
            if (!Schema::hasColumn('products', 'reward_points')) {
                $table->integer('reward_points')->nullable()->after('product_price');
            }
            if (!Schema::hasColumn('products', 'tax_type')) {
                $table->string('tax_type', 20)->default('taxable')->after('reward_points');
            }
            if (!Schema::hasColumn('products', 'price_constraint_enabled')) {
                $table->boolean('price_constraint_enabled')->default(false)->after('tax_type');
            }
            if (!Schema::hasColumn('products', 'price_constraint_type')) {
                $table->string('price_constraint_type', 20)->nullable()->after('price_constraint_enabled');
            }
            if (!Schema::hasColumn('products', 'price_min')) {
                $table->decimal('price_min', 12, 2)->nullable()->after('price_constraint_type');
            }
            if (!Schema::hasColumn('products', 'price_max')) {
                $table->decimal('price_max', 12, 2)->nullable()->after('price_min');
            }
            if (!Schema::hasColumn('products', 'price_fixed')) {
                $table->decimal('price_fixed', 12, 2)->nullable()->after('price_max');
            }
            if (!Schema::hasColumn('products', 'profit_share_type')) {
                $table->string('profit_share_type', 20)->nullable()->after('price_fixed');
            }
            if (!Schema::hasColumn('products', 'profit_share_value')) {
                $table->decimal('profit_share_value', 12, 2)->nullable()->after('profit_share_type');
            }
            if (!Schema::hasColumn('products', 'purchase_limit_enabled')) {
                $table->boolean('purchase_limit_enabled')->default(false)->after('profit_share_value');
            }
            if (!Schema::hasColumn('products', 'purchase_min_qty')) {
                $table->integer('purchase_min_qty')->nullable()->after('purchase_limit_enabled');
            }
            if (!Schema::hasColumn('products', 'purchase_max_qty')) {
                $table->integer('purchase_max_qty')->nullable()->after('purchase_min_qty');
            }
            if (!Schema::hasColumn('products', 'stock_usage')) {
                $table->string('stock_usage', 20)->default('unused')->after('purchase_max_qty');
            }
            if (!Schema::hasColumn('products', 'detail_display_type')) {
                $table->string('detail_display_type', 20)->default('unused')->after('description');
            }
            if (!Schema::hasColumn('products', 'detail_text')) {
                $table->longText('detail_text')->nullable()->after('detail_display_type');
            }
            if (!Schema::hasColumn('products', 'detail_pc_image')) {
                $table->string('detail_pc_image')->nullable()->after('detail_text');
            }
            if (!Schema::hasColumn('products', 'detail_mobile_image')) {
                $table->string('detail_mobile_image')->nullable()->after('detail_pc_image');
            }
            if (!Schema::hasColumn('products', 'order_manager_enabled')) {
                $table->boolean('order_manager_enabled')->default(false)->after('distributor_id');
            }
            if (!Schema::hasColumn('products', 'shipping_policy_type')) {
                $table->string('shipping_policy_type', 40)->default('free_conditional')->after('order_manager_enabled');
            }
            if (!Schema::hasColumn('products', 'shipping_policy_name')) {
                $table->string('shipping_policy_name')->nullable()->after('shipping_policy_type');
            }
            if (!Schema::hasColumn('products', 'shipping_payment_type')) {
                $table->string('shipping_payment_type', 20)->default('prepaid')->after('shipping_policy_name');
            }
            if (!Schema::hasColumn('products', 'shipping_base_fee')) {
                $table->decimal('shipping_base_fee', 12, 2)->nullable()->after('shipping_payment_type');
            }
            if (!Schema::hasColumn('products', 'shipping_free_threshold')) {
                $table->decimal('shipping_free_threshold', 12, 2)->nullable()->after('shipping_base_fee');
            }
            if (!Schema::hasColumn('products', 'cancel_refund_policy_id')) {
                $table->unsignedBigInteger('cancel_refund_policy_id')->nullable()->after('shipping_free_threshold')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'cancel_refund_policy_id',
                'shipping_free_threshold',
                'shipping_base_fee',
                'shipping_payment_type',
                'shipping_policy_name',
                'shipping_policy_type',
                'order_manager_enabled',
                'detail_mobile_image',
                'detail_pc_image',
                'detail_text',
                'detail_display_type',
                'stock_usage',
                'purchase_max_qty',
                'purchase_min_qty',
                'purchase_limit_enabled',
                'profit_share_value',
                'profit_share_type',
                'price_fixed',
                'price_max',
                'price_min',
                'price_constraint_type',
                'price_constraint_enabled',
                'tax_type',
                'reward_points',
                'sale_scope',
            ];

            if (Schema::hasColumn('products', 'cancel_refund_policy_id')) {
                $table->dropIndex(['cancel_refund_policy_id']);
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
