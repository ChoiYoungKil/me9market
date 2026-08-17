<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settlement_runs') && !Schema::hasColumn('settlement_runs', 'sms_postpaid_amount')) {
            Schema::table('settlement_runs', function (Blueprint $table) {
                $table->decimal('sms_postpaid_amount', 14, 2)->default(0)->after('point_used_amount');
            });
        }

        if (Schema::hasTable('settlement_items') && !Schema::hasColumn('settlement_items', 'sms_postpaid_amount')) {
            Schema::table('settlement_items', function (Blueprint $table) {
                $table->decimal('sms_postpaid_amount', 14, 2)->default(0)->after('point_used_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settlement_items') && Schema::hasColumn('settlement_items', 'sms_postpaid_amount')) {
            Schema::table('settlement_items', function (Blueprint $table) {
                $table->dropColumn('sms_postpaid_amount');
            });
        }

        if (Schema::hasTable('settlement_runs') && Schema::hasColumn('settlement_runs', 'sms_postpaid_amount')) {
            Schema::table('settlement_runs', function (Blueprint $table) {
                $table->dropColumn('sms_postpaid_amount');
            });
        }
    }
};
