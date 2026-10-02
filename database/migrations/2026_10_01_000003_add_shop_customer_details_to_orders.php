<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('buyer_name', 100)->nullable();
            $table->string('buyer_mobile', 30)->nullable();
            $table->string('delivery_memo', 500)->nullable();
            $table->timestamp('order_confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['buyer_name', 'buyer_mobile', 'delivery_memo', 'order_confirmed_at']));
    }
};
