<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_channel_private_accesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_channel_id');
            $table->string('phone', 30);
            $table->string('phone_normalized', 20);
            $table->string('entry_code', 80);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('first_accessed_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);
            $table->unsignedInteger('purchase_count')->default(0);
            $table->timestamps();

            $table->unique(['shop_channel_id', 'phone_normalized'], 'scp_access_channel_phone_unique');
            $table->index(['shop_channel_id', 'entry_code'], 'scp_access_channel_code_idx');
            $table->foreign('shop_channel_id')->references('id')->on('shop_channels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_channel_private_accesses');
    }
};
