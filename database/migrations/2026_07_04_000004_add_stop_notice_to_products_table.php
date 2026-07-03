<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stop_notice_at')) {
                $table->date('stop_notice_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('products', 'stop_notice_reason')) {
                $table->text('stop_notice_reason')->nullable()->after('stop_notice_at');
            }
            if (!Schema::hasColumn('products', 'stop_notice_requested_at')) {
                $table->timestamp('stop_notice_requested_at')->nullable()->after('stop_notice_reason');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            foreach (['stop_notice_requested_at', 'stop_notice_reason', 'stop_notice_at'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
