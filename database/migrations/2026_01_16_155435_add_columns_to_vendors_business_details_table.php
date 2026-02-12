<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('vendors_business_details', function (Blueprint $table) {
            $table->string('shop_business_type')->nullable()->after('shop_name');
            $table->string('shop_address_detail')->nullable()->after('shop_pincode');
            $table->string('bank_name')->nullable()->after('business_license_number');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder_name')->nullable()->after('bank_account_number');
            $table->string('bank_copy_image')->nullable()->after('bank_account_holder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('vendors_business_details', function (Blueprint $table) {
            $table->dropColumn([
                'shop_business_type',
                'shop_address_detail',
                'bank_name',
                'bank_account_number',
                'bank_account_holder_name',
                'bank_copy_image'
            ]);
        });
    }
};
