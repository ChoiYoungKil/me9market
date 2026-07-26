<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distributors')) {
            Schema::create('distributors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        } else {
            Schema::table('distributors', function (Blueprint $table) {
                if (!Schema::hasColumn('distributors', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('distributors', 'email')) {
                    $table->string('email')->unique()->after('name');
                }
                if (!Schema::hasColumn('distributors', 'password')) {
                    $table->string('password')->after('email');
                }
                if (!Schema::hasColumn('distributors', 'phone')) {
                    $table->string('phone')->nullable()->after('password');
                }
                if (!Schema::hasColumn('distributors', 'status')) {
                    $table->tinyInteger('status')->default(1)->after('phone');
                }
                if (!Schema::hasColumn('distributors', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'distributor_id')) {
                    $table->unsignedBigInteger('distributor_id')->nullable()->after('admin_id');
                }
                if (!Schema::hasColumn('products', 'order_manager_enabled')) {
                    $table->boolean('order_manager_enabled')->default(false)->after('distributor_id');
                }
            });
        }

        if (Schema::hasTable('orders_products')) {
            Schema::table('orders_products', function (Blueprint $table) {
                if (!Schema::hasColumn('orders_products', 'distributor_id')) {
                    $table->unsignedBigInteger('distributor_id')->nullable()->after('product_id');
                }
            });
        }
    }

    public function down(): void
    {
        // This migration is intentionally non-destructive because it may run
        // against databases that already had these columns from storyboard migrations.
    }
};
