<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->timestamp('seller_approved_at')->nullable();
            $table->unsignedBigInteger('seller_approved_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', fn (Blueprint $table) => $table->dropColumn(['seller_approved_at', 'seller_approved_by']));
    }
};
