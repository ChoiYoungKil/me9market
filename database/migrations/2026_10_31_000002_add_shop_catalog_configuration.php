<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('shop_channel_products',function(Blueprint $table){
            $table->unsignedInteger('reward_points')->nullable();
            $table->timestamp('listed_at')->nullable()->useCurrent();
        });
        Schema::create('shop_product_favorites',function(Blueprint $table){
            $table->id();$table->unsignedBigInteger('vendor_id');$table->unsignedBigInteger('product_id');
            $table->timestamps();$table->unique(['vendor_id','product_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('shop_product_favorites');
        Schema::table('shop_channel_products',fn(Blueprint $table)=>$table->dropColumn(['reward_points','listed_at']));
    }
};
