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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); // FAQ 카테고리 (예: 주문/배송, 결제, 회원 등)
            $table->string('question');
            $table->text('answer');
            $table->integer('order')->default(0); // 정렬 순서
            $table->integer('view_count')->default(0); // 조회수
            $table->boolean('status')->default(1); // 1: 활성, 0: 비활성
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
