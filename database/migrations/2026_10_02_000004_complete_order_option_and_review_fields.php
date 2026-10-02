<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products_attributes', function (Blueprint $table) {
            $table->decimal('price_adjustment', 12, 2)->default(0);
        });
        Schema::table('orders_products', function (Blueprint $table) {
            $table->decimal('option_price_adjustment', 12, 2)->default(0);
            $table->string('claim_previous_status', 40)->nullable();
        });
        Schema::table('ratings', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products_attributes', fn (Blueprint $table) => $table->dropColumn('price_adjustment'));
        Schema::table('orders_products', fn (Blueprint $table) => $table->dropColumn(['option_price_adjustment', 'claim_previous_status']));
        // Keep fractional ratings intact when reverting application code.
    }
};
