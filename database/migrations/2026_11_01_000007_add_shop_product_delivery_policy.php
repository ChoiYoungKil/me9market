<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('shop_channel_products', function(Blueprint $table){
            $table->foreignId('shipping_policy_id')->nullable()->constrained('channel_delivery_charges')->restrictOnDelete();
        });
    }
    public function down(): void { Schema::table('shop_channel_products',fn(Blueprint $table)=>$table->dropConstrainedForeignId('shipping_policy_id')); }
};
