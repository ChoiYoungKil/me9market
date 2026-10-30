<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_claims', function (Blueprint $table) {
            $table->string('pickup_name', 100)->nullable();
            $table->string('pickup_mobile', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_claims', fn (Blueprint $table) => $table->dropColumn(['pickup_name', 'pickup_mobile']));
    }
};
