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
        Schema::create('shop_channel_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_channel_id'); // Shop 채널 ID
            $table->unsignedBigInteger('product_id'); // 상품 ID
            $table->string('product_type'); // 상품 구분: 'own'(지사), 'public'(공개), 'partial'(부분공개)
            $table->tinyInteger('status')->default(1); // 1: 판매, 0: 판매중지
            $table->string('constraint_type')->nullable(); // 제약조건: 'none'(없음), 'range'(범위형), 'fixed'(고정형)
            $table->integer('stock')->nullable(); // 재고
            $table->integer('purchase_limit')->nullable(); // 1회 구매 제한 수량
            $table->decimal('product_price', 10, 2)->nullable(); // 상품가격
            $table->decimal('selling_price', 10, 2)->nullable(); // 판매가
            $table->decimal('profit', 10, 2)->nullable(); // 판매이익
            $table->timestamps();
            
            // 인덱스
            $table->index('shop_channel_id');
            $table->index('product_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_channel_products');
    }
};
