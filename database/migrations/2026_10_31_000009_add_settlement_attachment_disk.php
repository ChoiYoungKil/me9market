<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_executions', fn (Blueprint $table) => $table->string('attachment_disk', 20)->default('public'));
    }

    public function down(): void
    {
        Schema::table('settlement_executions', fn (Blueprint $table) => $table->dropColumn('attachment_disk'));
    }
};
