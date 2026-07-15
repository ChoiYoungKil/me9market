<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settlement_runs')) {
            return;
        }

        Schema::table('settlement_runs', function (Blueprint $table) {
            if (!Schema::hasColumn('settlement_runs', 'settlement_role')) {
                $table->string('settlement_role', 40)->default('seller')->after('settlement_key')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settlement_runs') || !Schema::hasColumn('settlement_runs', 'settlement_role')) {
            return;
        }

        Schema::table('settlement_runs', function (Blueprint $table) {
            $table->dropColumn('settlement_role');
        });
    }
};
