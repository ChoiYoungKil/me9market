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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('parent_id')->nullable()->after('id')->default(0)->comment('Parent Product ID for Partial Products');
            $table->enum('is_public', ['No', 'Yes'])->default('No')->after('status')->comment('Is Shared Product');
            $table->enum('is_partial', ['No', 'Yes'])->default('No')->after('is_public')->comment('Is Partial Product');
            $table->enum('partial_approved', ['Pending', 'Approved', 'Rejected'])->default('Pending')->after('is_partial')->comment('Approval Status for Partial Product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
