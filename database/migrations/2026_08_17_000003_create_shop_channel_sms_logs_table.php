<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_channel_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_channel_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_product_id')->nullable();
            $table->string('template_type', 40);
            $table->string('recipient_phone', 30);
            $table->text('message');
            $table->unsignedInteger('billing_amount')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['shop_channel_id', 'template_type'], 'sc_sms_channel_type_idx');
            $table->index(['vendor_id', 'created_at'], 'sc_sms_vendor_created_idx');
            $table->foreign('shop_channel_id')->references('id')->on('shop_channels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_channel_sms_logs');
    }
};
