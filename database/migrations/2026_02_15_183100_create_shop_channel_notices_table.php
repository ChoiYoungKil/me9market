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
        Schema::create('shop_channel_notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_channel_id'); // Shop 채널 ID
            $table->string('title', 500); // 공지사항 제목
            $table->text('content'); // 공지사항 내용
            $table->string('attachment')->nullable(); // 첨부파일
            $table->tinyInteger('status')->default(1); // 1: 활성, 0: 비활성
            $table->integer('view_count')->default(0); // 조회수
            $table->timestamps();
            
            // 인덱스
            $table->index('shop_channel_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_channel_notices');
    }
};
