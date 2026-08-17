<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shop_channels')) {
            Schema::table('shop_channels', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_channels', 'use_purchase_sms')) {
                    $table->boolean('use_purchase_sms')->default(false)->after('pg_secret_key');
                }
                if (!Schema::hasColumn('shop_channels', 'purchase_sms_templates')) {
                    $table->json('purchase_sms_templates')->nullable()->after('use_purchase_sms');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shop_channels')) {
            Schema::table('shop_channels', function (Blueprint $table) {
                foreach (['purchase_sms_templates', 'use_purchase_sms'] as $column) {
                    if (Schema::hasColumn('shop_channels', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
