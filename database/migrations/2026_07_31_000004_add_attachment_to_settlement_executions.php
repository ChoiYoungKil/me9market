<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settlement_executions')) {
            Schema::table('settlement_executions', function (Blueprint $table) {
                if (!Schema::hasColumn('settlement_executions', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('memo');
                }
                if (!Schema::hasColumn('settlement_executions', 'attachment_name')) {
                    $table->string('attachment_name')->nullable()->after('attachment_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settlement_executions')) {
            Schema::table('settlement_executions', function (Blueprint $table) {
                foreach (['attachment_name', 'attachment_path'] as $column) {
                    if (Schema::hasColumn('settlement_executions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
