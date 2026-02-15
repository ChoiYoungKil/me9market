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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 문의자 이름
            $table->string('email'); // 문의자 이메일
            $table->string('phone')->nullable(); // 문의자 전화번호
            $table->string('company')->nullable(); // 회사명
            $table->string('subject'); // 문의 제목
            $table->text('message'); // 문의 내용
            $table->enum('type', ['partnership', 'inquiry', 'other'])->default('inquiry'); // 문의 유형
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending'); // 처리 상태
            $table->text('admin_reply')->nullable(); // 관리자 답변
            $table->timestamp('replied_at')->nullable(); // 답변 일시
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
