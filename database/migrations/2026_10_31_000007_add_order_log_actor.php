<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_logs', fn (Blueprint $table) => $table->string('actor', 100)->nullable());
    }

    public function down(): void
    {
        Schema::table('orders_logs', fn (Blueprint $table) => $table->dropColumn('actor'));
    }
};
