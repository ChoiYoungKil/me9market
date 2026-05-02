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
        Schema::create('shop_channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id'); // 상점 주인
            $table->string('channel_code')->unique();
            $table->tinyInteger('status')->default(0); // 1: 운영, 0: 중지
            $table->tinyInteger('is_public')->default(1); // 1: 공개, 0: 비공개
            $table->string('password')->nullable();
            $table->tinyInteger('is_member_only')->default(0); // 구매권한
            $table->string('channel_name'); // * 필수
            $table->string('copyright'); // * 필수
            $table->text('keywords'); // * 필수 (JSON 또는 스트링)
            
            // 사용주기
            $table->tinyInteger('use_period_type')->default(0); // 0: 무기한, 1: 기간제
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            
            // 로고
            $table->tinyInteger('use_logo')->default(0);
            $table->string('logo_image')->nullable();
            
            // 메인 배너 (JSON으로 다중 이미지 저장 권장 또는 별도 테이블, 여기서는 단순화를 위해 JSON)
            $table->tinyInteger('use_banner')->default(0);
            $table->text('banner_images')->nullable();
            
            // OG TAG
            $table->tinyInteger('use_og')->default(0);
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('og_image')->nullable();
            
            // 관리자 정보
            $table->tinyInteger('use_admin')->default(0);
            $table->string('admin_name')->nullable();
            $table->string('admin_login_id')->nullable();
            $table->string('admin_password')->nullable();
            $table->tinyInteger('settlement_type')->default(1); // 1: %, 2: 금액
            $table->decimal('settlement_rate', 10, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_channels');
    }
};
