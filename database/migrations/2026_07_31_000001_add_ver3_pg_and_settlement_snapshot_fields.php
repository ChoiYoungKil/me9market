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
                if (!Schema::hasColumn('shop_channels', 'use_own_pg')) {
                    $table->boolean('use_own_pg')->default(false)->after('og_image');
                }
                if (!Schema::hasColumn('shop_channels', 'pg_provider')) {
                    $table->string('pg_provider', 30)->nullable()->after('use_own_pg');
                }
                if (!Schema::hasColumn('shop_channels', 'pg_merchant_id')) {
                    $table->text('pg_merchant_id')->nullable()->after('pg_provider');
                }
                if (!Schema::hasColumn('shop_channels', 'pg_site_code')) {
                    $table->text('pg_site_code')->nullable()->after('pg_merchant_id');
                }
                if (!Schema::hasColumn('shop_channels', 'pg_client_key')) {
                    $table->text('pg_client_key')->nullable()->after('pg_site_code');
                }
                if (!Schema::hasColumn('shop_channels', 'pg_secret_key')) {
                    $table->text('pg_secret_key')->nullable()->after('pg_client_key');
                }
            });
        }

        if (Schema::hasTable('shop_channel_products')) {
            Schema::table('shop_channel_products', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_channel_products', 'settlement_type_snapshot')) {
                    $table->tinyInteger('settlement_type_snapshot')->nullable()->after('profit');
                }
                if (!Schema::hasColumn('shop_channel_products', 'settlement_rate_snapshot')) {
                    $table->decimal('settlement_rate_snapshot', 10, 2)->nullable()->after('settlement_type_snapshot');
                }
                if (!Schema::hasColumn('shop_channel_products', 'minimum_selling_price')) {
                    $table->decimal('minimum_selling_price', 12, 2)->nullable()->after('settlement_rate_snapshot');
                }
                if (!Schema::hasColumn('shop_channel_products', 'maximum_reward_points')) {
                    $table->integer('maximum_reward_points')->default(0)->after('minimum_selling_price');
                }
                if (!Schema::hasColumn('shop_channel_products', 'price_decider')) {
                    $table->string('price_decider', 30)->default('seller')->after('maximum_reward_points');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shop_channel_products')) {
            Schema::table('shop_channel_products', function (Blueprint $table) {
                foreach (['price_decider', 'maximum_reward_points', 'minimum_selling_price', 'settlement_rate_snapshot', 'settlement_type_snapshot'] as $column) {
                    if (Schema::hasColumn('shop_channel_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('shop_channels')) {
            Schema::table('shop_channels', function (Blueprint $table) {
                foreach (['pg_secret_key', 'pg_client_key', 'pg_site_code', 'pg_merchant_id', 'pg_provider', 'use_own_pg'] as $column) {
                    if (Schema::hasColumn('shop_channels', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
