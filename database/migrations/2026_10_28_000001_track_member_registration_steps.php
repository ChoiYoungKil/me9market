<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
        });
        Schema::table('vendors', function (Blueprint $table) {
            $table->timestamp('company_terms_accepted_at')->nullable();
            $table->timestamp('seller_terms_accepted_at')->nullable();
            $table->timestamp('seller_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['birth_date', 'profile_completed_at']));
        Schema::table('vendors', fn (Blueprint $table) => $table->dropColumn(['company_terms_accepted_at', 'seller_terms_accepted_at', 'seller_requested_at']));
    }
};
