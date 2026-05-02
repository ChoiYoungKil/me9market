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
        Schema::create('shop_cancel_refund_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id'); // 판매자 ID
            $table->enum('type', ['default', 'custom'])->default('custom'); // 설정구분: 기본/사용자
            $table->enum('status', ['active', 'inactive'])->default('active'); // 상태: 사용/중지
            $table->string('name'); // 취소/환불안내 명칭
            $table->text('content')->nullable(); // 취소/환불안내 내용
            $table->integer('product_count')->default(0); // 연결된 상품 수
            $table->timestamps();

            // 외래키 제약조건
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            
            // 인덱스
            $table->index('vendor_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_cancel_refund_policies');
    }
};
