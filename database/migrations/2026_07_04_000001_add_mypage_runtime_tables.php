<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('shop_channel_product_id');
                $table->timestamps();

                $table->unique(['user_id', 'shop_channel_product_id']);
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('point_transactions')) {
            Schema::create('point_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('shop_channel_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('order_product_id')->nullable();
                $table->string('type', 30);
                $table->integer('points');
                $table->string('description')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index('shop_channel_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('wishlists');
    }
};
