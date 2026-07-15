<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settlement_runs')) {
            Schema::create('settlement_runs', function (Blueprint $table) {
                $table->id();
                $table->string('settlement_key', 120)->unique();
                $table->string('period', 7)->index();
                $table->unsignedBigInteger('vendor_id')->nullable()->index();
                $table->unsignedBigInteger('shop_channel_id')->nullable()->index();
                $table->string('vendor_name')->nullable();
                $table->string('shop_channel_name')->nullable();
                $table->tinyInteger('settlement_type')->default(1);
                $table->decimal('settlement_rate', 12, 2)->default(0);
                $table->unsignedInteger('order_count')->default(0);
                $table->unsignedInteger('item_count')->default(0);
                $table->unsignedInteger('quantity')->default(0);
                $table->decimal('gross_sales_amount', 14, 2)->default(0);
                $table->decimal('supply_amount', 14, 2)->default(0);
                $table->decimal('sales_profit_amount', 14, 2)->default(0);
                $table->decimal('invoice_sales_amount', 14, 2)->default(0);
                $table->decimal('invoice_purchase_amount', 14, 2)->default(0);
                $table->decimal('point_deposit_amount', 14, 2)->default(0);
                $table->decimal('point_used_amount', 14, 2)->default(0);
                $table->decimal('payout_amount', 14, 2)->default(0);
                $table->decimal('settlement_amount', 14, 2)->default(0);
                $table->decimal('admin_amount', 14, 2)->default(0);
                $table->string('status', 30)->default('pending')->index();
                $table->timestamp('executed_at')->nullable();
                $table->unsignedBigInteger('executed_by')->nullable();
                $table->string('memo')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settlement_items')) {
            Schema::create('settlement_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('settlement_run_id')->index();
                $table->unsignedBigInteger('order_product_id')->index();
                $table->string('settlement_role', 40)->default('seller')->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('vendor_id')->nullable()->index();
                $table->unsignedBigInteger('shop_channel_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('order_no')->nullable();
                $table->string('product_code')->nullable();
                $table->string('product_name')->nullable();
                $table->unsignedInteger('quantity')->default(0);
                $table->decimal('gross_sales_amount', 14, 2)->default(0);
                $table->decimal('supply_amount', 14, 2)->default(0);
                $table->decimal('sales_profit_amount', 14, 2)->default(0);
                $table->decimal('invoice_sales_amount', 14, 2)->default(0);
                $table->decimal('invoice_purchase_amount', 14, 2)->default(0);
                $table->decimal('point_deposit_amount', 14, 2)->default(0);
                $table->decimal('point_used_amount', 14, 2)->default(0);
                $table->decimal('payout_amount', 14, 2)->default(0);
                $table->tinyInteger('settlement_type')->default(1);
                $table->decimal('settlement_rate', 12, 2)->default(0);
                $table->decimal('settlement_amount', 14, 2)->default(0);
                $table->decimal('admin_amount', 14, 2)->default(0);
                $table->timestamp('confirmed_at')->nullable();
                $table->string('status', 30)->default('pending')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_items');
        Schema::dropIfExists('settlement_runs');
    }
};
