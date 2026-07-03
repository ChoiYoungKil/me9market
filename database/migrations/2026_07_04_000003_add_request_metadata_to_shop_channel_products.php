<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shop_channel_products')) {
            return;
        }

        Schema::table('shop_channel_products', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_channel_products', 'request_reason')) {
                $table->text('request_reason')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('shop_channel_products', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('request_reason');
            }
            if (!Schema::hasColumn('shop_channel_products', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('requested_at');
            }
            if (!Schema::hasColumn('shop_channel_products', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('shop_channel_products')) {
            return;
        }

        Schema::table('shop_channel_products', function (Blueprint $table) {
            foreach (['reviewed_by', 'reviewed_at', 'requested_at', 'request_reason'] as $column) {
                if (Schema::hasColumn('shop_channel_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
