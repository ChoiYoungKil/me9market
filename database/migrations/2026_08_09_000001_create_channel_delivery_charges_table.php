<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->tinyInteger('status')->default(1);
            $table->string('name');
            $table->string('courier')->nullable();
            $table->string('shipping_type')->default('free');
            $table->string('payment_type')->default('prepaid');
            $table->unsignedInteger('base_fee')->default(0);
            $table->unsignedInteger('free_order_amount')->nullable();
            $table->unsignedInteger('free_order_quantity')->nullable();
            $table->unsignedInteger('fixed_fee')->nullable();
            $table->unsignedInteger('product_count')->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_delivery_charges');
    }
};
