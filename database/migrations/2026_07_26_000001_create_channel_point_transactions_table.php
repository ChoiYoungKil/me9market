<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('channel_point_transactions')) {
            Schema::create('channel_point_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id');
                $table->unsignedBigInteger('shop_channel_id')->nullable();
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('order_product_id')->nullable();
                $table->string('type', 40);
                $table->string('status', 20)->default('pending');
                $table->integer('points');
                $table->integer('payment_amount')->default(0);
                $table->string('payment_method', 40)->nullable();
                $table->string('memo')->nullable();
                $table->string('reference_type', 60)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'status']);
                $table->index(['type', 'status']);
                $table->index(['reference_type', 'reference_id']);
                $table->index('shop_channel_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_point_transactions');
    }
};
