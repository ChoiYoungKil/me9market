<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('admin_id');
            $table->string('member_no')->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();

            $table->unique('admin_id');
            $table->index(['vendor_id', 'member_no']);
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_sub_accounts');
    }
};
