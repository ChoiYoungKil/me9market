<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create distributors table
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
        }

        // 2. Create joint_purchases table
        if (!Schema::hasTable('joint_purchases')) {
            Schema::create('joint_purchases', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('product_id')->unsigned();
                $table->integer('min_quantity');
                $table->integer('current_quantity')->default(0);
                $table->double('discount_price');
                $table->date('start_date');
                $table->date('end_date');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        // 3. Add distributor_id to products table
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'distributor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->bigInteger('distributor_id')->unsigned()->nullable()->after('admin_id');
            });
        }

        // 4. Add distributor_id to orders_products table
        if (Schema::hasTable('orders_products') && !Schema::hasColumn('orders_products', 'distributor_id')) {
            Schema::table('orders_products', function (Blueprint $table) {
                $table->bigInteger('distributor_id')->unsigned()->nullable()->after('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('joint_purchases');
        Schema::dropIfExists('distributors');

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'distributor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('distributor_id');
            });
        }

        if (Schema::hasTable('orders_products') && Schema::hasColumn('orders_products', 'distributor_id')) {
            Schema::table('orders_products', function (Blueprint $table) {
                $table->dropColumn('distributor_id');
            });
        }
    }
};
