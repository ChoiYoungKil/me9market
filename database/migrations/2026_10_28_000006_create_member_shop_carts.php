<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('member_shop_carts', function (Blueprint $table) {
            $table->id();$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_channel_product_id')->constrained()->cascadeOnDelete();
            $table->string('cart_key',100);$table->string('option_name',100);$table->unsignedInteger('quantity');$table->timestamps();
            $table->unique(['user_id','cart_key']);
        });
    }
    public function down(): void {Schema::dropIfExists('member_shop_carts');}
};
