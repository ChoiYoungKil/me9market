<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_channels', function (Blueprint $table) {
            if (! Schema::hasColumn('shop_channels', 'first_visit_points')) {
                $table->unsignedInteger('first_visit_points')->default(0)->after('purchase_sms_templates');
            }
        });
        Schema::table('visited_channels', function (Blueprint $table) {
            if (! Schema::hasColumn('visited_channels', 'shop_channel_id')) {
                $table->unsignedBigInteger('shop_channel_id')->nullable()->after('vendor_id');
                $table->index(['user_id', 'shop_channel_id'], 'visited_channels_user_shop_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visited_channels', function (Blueprint $table) {
            if (Schema::hasColumn('visited_channels', 'shop_channel_id')) {
                $table->dropIndex('visited_channels_user_shop_idx');
                $table->dropColumn('shop_channel_id');
            }
        });
        Schema::table('shop_channels', function (Blueprint $table) {
            if (Schema::hasColumn('shop_channels', 'first_visit_points')) {
                $table->dropColumn('first_visit_points');
            }
        });
    }
};
