<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_channel_notices', function (Blueprint $table) {
            $table->enum('type', ['notice', 'general'])->default('general')->after('shop_channel_id'); // 공지/일반
            $table->string('author', 100)->nullable()->after('title'); // 작성자
            $table->index('type'); // 타입 인덱스
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_channel_notices', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'author']);
        });
    }
};
