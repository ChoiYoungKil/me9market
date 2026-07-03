<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'vendor_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('section_id')->index();
            });
        }

        if (Schema::hasTable('products_attributes')) {
            Schema::table('products_attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('products_attributes', 'option_name')) {
                    $table->string('option_name')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('products_attributes', 'option_type')) {
                    $table->string('option_type', 40)->nullable()->after('option_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products_attributes')) {
            Schema::table('products_attributes', function (Blueprint $table) {
                if (Schema::hasColumn('products_attributes', 'option_type')) {
                    $table->dropColumn('option_type');
                }
                if (Schema::hasColumn('products_attributes', 'option_name')) {
                    $table->dropColumn('option_name');
                }
            });
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'vendor_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }
    }
};
