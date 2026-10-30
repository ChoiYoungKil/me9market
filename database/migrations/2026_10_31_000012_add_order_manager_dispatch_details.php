<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->string('dispatch_postcode', 5)->nullable();
            $table->string('dispatch_address')->nullable();
            $table->string('dispatch_address_detail')->nullable();
            $table->text('memo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('distributors', fn (Blueprint $table) => $table->dropColumn(['dispatch_postcode', 'dispatch_address', 'dispatch_address_detail', 'memo']));
    }
};
