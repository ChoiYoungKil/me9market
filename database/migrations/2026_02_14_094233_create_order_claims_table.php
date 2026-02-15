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
        Schema::create('order_claims', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('user_id');
            $table->integer('vendor_id')->nullable();
            $table->integer('order_product_id');
            $table->enum('type', ['cancel', 'return', 'exchange']);
            $table->string('reason');
            $table->text('detail_reason')->nullable();
            $table->string('status')->default('requested'); // requested, approved, rejected, completed
            $table->text('admin_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_claims');
    }
};
