<?php

use App\Support\OrderItemStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders_products')
            || !Schema::hasTable('shop_channel_products')
            || !Schema::hasTable('shop_channels')) {
            return;
        }

        DB::table('orders_products')
            ->select([
                'id',
                'product_id',
                'vendor_id',
                'shop_channel_id',
                'shop_channel_product_id',
                'status_code',
                'item_status',
            ])
            ->where(function ($query) {
                $query->whereNull('shop_channel_id')
                    ->orWhereNull('shop_channel_product_id')
                    ->orWhere('vendor_id', 0)
                    ->orWhereNull('status_code')
                    ->orWhereNull('item_status')
                    ->orWhere('item_status', '');
            })
            ->orderBy('id')
            ->chunkById(500, function ($items) {
                foreach ($items as $item) {
                    if (!$item->product_id) {
                        continue;
                    }

                    $shopProduct = DB::table('shop_channel_products')
                        ->join('shop_channels', 'shop_channel_products.shop_channel_id', '=', 'shop_channels.id')
                        ->where('shop_channel_products.product_id', $item->product_id)
                        ->where('shop_channel_products.status', 1)
                        ->where('shop_channels.status', 1)
                        ->select([
                            'shop_channel_products.id as shop_channel_product_id',
                            'shop_channel_products.shop_channel_id',
                            'shop_channel_products.distributor_id',
                            'shop_channels.vendor_id',
                        ])
                        ->orderByDesc('shop_channel_products.id')
                        ->first();

                    $normalized = OrderItemStatus::normalize($item->status_code ?: $item->item_status);
                    $updates = [
                        'status_code' => $normalized,
                        'item_status' => OrderItemStatus::label($normalized),
                        'updated_at' => now(),
                    ];

                    if ($shopProduct) {
                        $updates['shop_channel_id'] = $item->shop_channel_id ?: $shopProduct->shop_channel_id;
                        $updates['shop_channel_product_id'] = $item->shop_channel_product_id ?: $shopProduct->shop_channel_product_id;
                        $updates['vendor_id'] = $item->vendor_id ?: $shopProduct->vendor_id;
                        if (Schema::hasColumn('orders_products', 'distributor_id')) {
                            $updates['distributor_id'] = $shopProduct->distributor_id;
                        }
                    }

                    DB::table('orders_products')->where('id', $item->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        // This migration links existing order items to channel metadata and is intentionally not reversed.
    }
};
