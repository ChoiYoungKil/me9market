<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_claims', function (Blueprint $table) {
            $table->string('original_option')->nullable();
            $table->string('exchange_recipient_name', 100)->nullable();
            $table->string('exchange_recipient_mobile', 30)->nullable();
            $table->string('exchange_recipient_address', 1000)->nullable();
        });
        Schema::table('orders_products', function (Blueprint $table) {
            $table->string('recipient_name', 100)->nullable();
            $table->string('recipient_mobile', 30)->nullable();
            $table->string('recipient_address', 1000)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_claims', fn (Blueprint $table) => $table->dropColumn(['original_option', 'exchange_recipient_name', 'exchange_recipient_mobile', 'exchange_recipient_address']));
        Schema::table('orders_products', fn (Blueprint $table) => $table->dropColumn(['recipient_name', 'recipient_mobile', 'recipient_address']));
    }
};
