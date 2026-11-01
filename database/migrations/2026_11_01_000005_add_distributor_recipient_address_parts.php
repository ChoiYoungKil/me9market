<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->string('recipient_postcode', 10)->nullable();
            $table->string('recipient_address_detail', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders_products', fn (Blueprint $table) => $table->dropColumn(['recipient_postcode', 'recipient_address_detail']));
    }
};
