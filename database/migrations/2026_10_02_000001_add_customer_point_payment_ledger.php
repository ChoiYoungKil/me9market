<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->string('reference_key', 100)->nullable()->unique();
        });
        Schema::table('orders_products', function (Blueprint $table) {
            $table->unsignedInteger('used_point_amount')->nullable();
            $table->json('point_usage_snapshot')->nullable();
            $table->decimal('paid_line_total_snapshot', 14, 2)->nullable();
            $table->unsignedInteger('stock_deducted_qty')->default(0);
            $table->unsignedBigInteger('stock_attribute_id')->nullable();
            $table->unsignedInteger('attribute_stock_deducted_qty')->default(0);
            $table->timestamp('financial_reversed_at')->nullable();
            $table->string('refund_status', 30)->nullable();
            $table->decimal('refund_cash_amount', 14, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropUnique(['reference_key']);
            $table->dropColumn('reference_key');
        });
        Schema::table('orders_products', fn (Blueprint $table) => $table->dropColumn([
            'used_point_amount', 'point_usage_snapshot', 'paid_line_total_snapshot', 'stock_deducted_qty',
            'financial_reversed_at', 'refund_status', 'refund_cash_amount',
            'stock_attribute_id', 'attribute_stock_deducted_qty',
        ]));
    }
};
