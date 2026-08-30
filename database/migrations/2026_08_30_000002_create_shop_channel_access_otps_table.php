<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_channel_access_otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_channel_private_access_id');
            $table->string('phone_normalized', 20);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('sent_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['shop_channel_private_access_id', 'created_at'], 'shop_access_otp_access_created_idx');
            $table->foreign('shop_channel_private_access_id', 'shop_access_otp_access_fk')
                ->references('id')->on('shop_channel_private_accesses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_channel_access_otps');
    }
};
