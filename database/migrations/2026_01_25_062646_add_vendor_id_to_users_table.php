<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 마이그레이션 실행.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('vendor_id')->nullable()->after('id');
        });
    }

    /**
     * 마이그레이션 되돌리기.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
        });
    }
};
