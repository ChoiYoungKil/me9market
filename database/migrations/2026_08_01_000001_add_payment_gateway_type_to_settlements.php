<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settlement_runs') && !Schema::hasColumn('settlement_runs', 'payment_gateway_type')) {
            Schema::table('settlement_runs', function (Blueprint $table) {
                $table->string('payment_gateway_type', 20)->default('me9_pg')->after('settlement_role');
            });
        }

        if (Schema::hasTable('settlement_items') && !Schema::hasColumn('settlement_items', 'payment_gateway_type')) {
            Schema::table('settlement_items', function (Blueprint $table) {
                $table->string('payment_gateway_type', 20)->default('me9_pg')->after('settlement_role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settlement_items') && Schema::hasColumn('settlement_items', 'payment_gateway_type')) {
            Schema::table('settlement_items', function (Blueprint $table) {
                $table->dropColumn('payment_gateway_type');
            });
        }

        if (Schema::hasTable('settlement_runs') && Schema::hasColumn('settlement_runs', 'payment_gateway_type')) {
            Schema::table('settlement_runs', function (Blueprint $table) {
                $table->dropColumn('payment_gateway_type');
            });
        }
    }
};
