<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'use_own_pg')) {
                    $table->boolean('use_own_pg')->default(false)->after('commission');
                }
                if (!Schema::hasColumn('vendors', 'pg_provider')) {
                    $table->string('pg_provider', 30)->nullable()->after('use_own_pg');
                }
                if (!Schema::hasColumn('vendors', 'pg_merchant_id')) {
                    $table->text('pg_merchant_id')->nullable()->after('pg_provider');
                }
                if (!Schema::hasColumn('vendors', 'pg_site_code')) {
                    $table->text('pg_site_code')->nullable()->after('pg_merchant_id');
                }
                if (!Schema::hasColumn('vendors', 'pg_client_key')) {
                    $table->text('pg_client_key')->nullable()->after('pg_site_code');
                }
                if (!Schema::hasColumn('vendors', 'pg_secret_key')) {
                    $table->text('pg_secret_key')->nullable()->after('pg_client_key');
                }
            });
        }

        if (Schema::hasTable('shop_channels')) {
            Schema::table('shop_channels', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_channels', 'closure_status')) {
                    $table->string('closure_status', 20)->default('none')->index()->after('status');
                }
                if (!Schema::hasColumn('shop_channels', 'closure_requested_at')) {
                    $table->timestamp('closure_requested_at')->nullable()->after('closure_status');
                }
                if (!Schema::hasColumn('shop_channels', 'closure_approved_at')) {
                    $table->timestamp('closure_approved_at')->nullable()->after('closure_requested_at');
                }
                if (!Schema::hasColumn('shop_channels', 'closure_rejected_at')) {
                    $table->timestamp('closure_rejected_at')->nullable()->after('closure_approved_at');
                }
                if (!Schema::hasColumn('shop_channels', 'closure_reviewed_by')) {
                    $table->unsignedBigInteger('closure_reviewed_by')->nullable()->after('closure_rejected_at');
                }
                if (!Schema::hasColumn('shop_channels', 'closure_memo')) {
                    $table->string('closure_memo')->nullable()->after('closure_reviewed_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shop_channels')) {
            Schema::table('shop_channels', function (Blueprint $table) {
                foreach ([
                    'closure_memo',
                    'closure_reviewed_by',
                    'closure_rejected_at',
                    'closure_approved_at',
                    'closure_requested_at',
                    'closure_status',
                ] as $column) {
                    if (Schema::hasColumn('shop_channels', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                foreach ([
                    'pg_secret_key',
                    'pg_client_key',
                    'pg_site_code',
                    'pg_merchant_id',
                    'pg_provider',
                    'use_own_pg',
                ] as $column) {
                    if (Schema::hasColumn('vendors', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
