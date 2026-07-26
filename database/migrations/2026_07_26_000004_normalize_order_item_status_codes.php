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
            || !Schema::hasColumn('orders_products', 'status_code')
            || !Schema::hasColumn('orders_products', 'item_status')) {
            return;
        }

        DB::table('orders_products')
            ->select(['id', 'status_code', 'item_status'])
            ->orderBy('id')
            ->chunkById(500, function ($items) {
                foreach ($items as $item) {
                    $normalized = OrderItemStatus::normalize($item->status_code ?: $item->item_status);
                    $label = OrderItemStatus::label($normalized);

                    if ($item->status_code === $normalized && $item->item_status === $label) {
                        continue;
                    }

                    DB::table('orders_products')
                        ->where('id', $item->id)
                        ->update([
                            'status_code' => $normalized,
                            'item_status' => $label,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Status normalization is data repair and should not be reversed.
    }
};
