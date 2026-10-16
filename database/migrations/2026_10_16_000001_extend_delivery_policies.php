<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_delivery_charges', function (Blueprint $table) {
            $table->json('calculation_rules')->nullable();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('shipping_policy_id')->nullable()->constrained('channel_delivery_charges')->restrictOnDelete();
        });
        // Link existing names only when the vendor/name pair identifies exactly one policy.
        DB::table('products')->whereNotNull('shipping_policy_name')->orderBy('id')->chunkById(200, function ($products) {
            foreach ($products as $product) {
                $ids = DB::table('channel_delivery_charges')
                    ->where('vendor_id', $product->vendor_id)->where('name', $product->shipping_policy_name)->pluck('id');
                if ($ids->count() === 1) {
                    DB::table('products')->where('id', $product->id)->update(['shipping_policy_id' => $ids->first()]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropConstrainedForeignId('shipping_policy_id'));
        Schema::table('channel_delivery_charges', fn (Blueprint $table) => $table->dropColumn('calculation_rules'));
    }
};
