<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->dateTime('access_started_at')->nullable();
            $table->dateTime('access_ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('distributors', fn (Blueprint $table) => $table->dropColumn(['access_started_at', 'access_ended_at']));
    }
};
