<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->string('payment_gateway_type', 20)->nullable();
            $table->json('settlement_policy_snapshot')->nullable();
            $table->decimal('shipping_amount_snapshot', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway_type', 'settlement_policy_snapshot', 'shipping_amount_snapshot']);
        });
    }
};
