<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settlement_executions')) {
            Schema::create('settlement_executions', function (Blueprint $table) {
                $table->id();
                $table->string('period', 7)->index();
                $table->unsignedBigInteger('settlement_run_id')->nullable()->index();
                $table->unsignedBigInteger('vendor_id')->nullable()->index();
                $table->unsignedBigInteger('shop_channel_id')->nullable()->index();
                $table->string('title');
                $table->decimal('amount', 14, 2)->default(0);
                $table->timestamp('executed_at')->nullable();
                $table->unsignedBigInteger('registered_by')->nullable();
                $table->string('memo')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_executions');
    }
};
