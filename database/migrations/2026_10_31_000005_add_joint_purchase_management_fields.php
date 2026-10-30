<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('joint_purchases', function (Blueprint $table) {
            $table->string('purchase_code', 80)->nullable()->unique();
            $table->string('sale_name')->nullable();
            $table->boolean('period_enabled')->default(true);
            $table->json('benefits')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('joint_purchases', fn (Blueprint $table) => $table->dropColumn(['purchase_code', 'sale_name', 'period_enabled', 'benefits', 'deleted_at']));
    }
};
