<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_channels', fn (Blueprint $table) => $table->json('banner_links')->nullable());
    }

    public function down(): void
    {
        Schema::table('shop_channels', fn (Blueprint $table) => $table->dropColumn('banner_links'));
    }
};
