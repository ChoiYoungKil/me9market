<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrdersProduct;
use App\Support\OrderItemStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JointPurchasePricingService
{
    public function activePurchaseForProduct(int $productId)
    {
        if (!Schema::hasTable('joint_purchases')) {
            return null;
        }

        return DB::table('joint_purchases')
            ->where('product_id', $productId)
            ->where('status', 1)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('id')
            ->first();
    }

    public function tiers(int $jointPurchaseId): Collection
    {
        if (!Schema::hasTable('joint_purchase_price_tiers')) {
            return collect();
        }

        return DB::table('joint_purchase_price_tiers')
            ->where('joint_purchase_id', $jointPurchaseId)
            ->orderBy('min_quantity')
            ->get();
    }

    public function priceForQuantity($jointPurchase, int $quantity): array
    {
        $quantity = max(1, $quantity);
        $tiers = $this->tiers((int) $jointPurchase->id);

        $tier = $tiers->first(function ($row) use ($quantity) {
            return $quantity >= (int) $row->min_quantity
                && ($row->max_quantity === null || $quantity <= (int) $row->max_quantity);
        });

        if (!$tier) {
            $tier = $tiers->sortByDesc('min_quantity')->first(fn ($row) => $quantity >= (int) $row->min_quantity);
        }

        if ($tier) {
            return [
                'unit_price' => (float) $tier->unit_price,
                'tier_id' => (int) $tier->id,
                'tier' => $tier,
            ];
        }

        return [
            'unit_price' => (float) ($jointPurchase->discount_price ?? 0),
            'tier_id' => null,
            'tier' => null,
        ];
    }

    public function projectedPriceForProduct(int $productId, int $additionalQuantity = 1): ?array
    {
        $jointPurchase = $this->activePurchaseForProduct($productId);
        if (!$jointPurchase) {
            return null;
        }

        $projectedQuantity = $this->currentQuantity((int) $jointPurchase->id, (int) $jointPurchase->product_id) + max(1, $additionalQuantity);

        return array_merge(
            ['joint_purchase' => $jointPurchase, 'projected_quantity' => $projectedQuantity],
            $this->priceForQuantity($jointPurchase, $projectedQuantity)
        );
    }

    public function currentQuantity(int $jointPurchaseId, int $productId): int
    {
        $statuses = [
            OrderItemStatus::CANCELLED,
            OrderItemStatus::RETURNED,
            OrderItemStatus::CANCEL_REQUESTED,
            OrderItemStatus::RETURN_REQUESTED,
            '취소완료',
            '반품완료',
            '취소요청',
            '반품요청',
        ];

        return (int) OrdersProduct::where('product_id', $productId)
            ->where('joint_purchase_id', $jointPurchaseId)
            ->where(function ($query) use ($statuses) {
                $query->whereNull('status_code')->orWhereNotIn('status_code', $statuses);
            })
            ->where(function ($query) use ($statuses) {
                $query->whereNull('item_status')->orWhereNotIn('item_status', $statuses);
            })
            ->sum('product_qty');
    }

    public function syncTiers(int $jointPurchaseId, array $tiers): void
    {
        DB::table('joint_purchase_price_tiers')->where('joint_purchase_id', $jointPurchaseId)->delete();

        foreach ($tiers as $tier) {
            DB::table('joint_purchase_price_tiers')->insert([
                'joint_purchase_id' => $jointPurchaseId,
                'min_quantity' => (int) $tier['min_quantity'],
                'max_quantity' => $tier['max_quantity'] === null ? null : (int) $tier['max_quantity'],
                'unit_price' => (float) $tier['unit_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function normalizeTierInput(array $input): array
    {
        $mins = $input['tier_min_quantity'] ?? [];
        $maxes = $input['tier_max_quantity'] ?? [];
        $prices = $input['tier_unit_price'] ?? [];
        $tiers = [];

        foreach ($mins as $index => $min) {
            $price = $prices[$index] ?? null;
            if ($min === null || $min === '' || $price === null || $price === '') {
                continue;
            }

            $max = $maxes[$index] ?? null;
            $tiers[] = [
                'min_quantity' => max(1, (int) $min),
                'max_quantity' => ($max === null || $max === '') ? null : max(1, (int) $max),
                'unit_price' => max(0, (float) $price),
            ];
        }

        usort($tiers, fn ($a, $b) => $a['min_quantity'] <=> $b['min_quantity']);

        return $tiers;
    }

    public function repricePurchase(int $jointPurchaseId): void
    {
        $jointPurchase = DB::table('joint_purchases')->where('id', $jointPurchaseId)->first();
        if (!$jointPurchase) {
            return;
        }

        DB::transaction(function () use ($jointPurchase) {
            $quantity = $this->currentQuantity((int) $jointPurchase->id, (int) $jointPurchase->product_id);
            DB::table('joint_purchases')->where('id', $jointPurchase->id)->update([
                'current_quantity' => $quantity,
                'updated_at' => now(),
            ]);

            if ($quantity <= 0) {
                return;
            }

            $price = $this->priceForQuantity($jointPurchase, $quantity);
            $items = OrdersProduct::where('product_id', $jointPurchase->product_id)
                ->where('joint_purchase_id', $jointPurchase->id)
                ->where(function ($query) {
                    $query->whereNull('status_code')
                        ->orWhereNotIn('status_code', [OrderItemStatus::CANCELLED, OrderItemStatus::RETURNED]);
                })
                ->get();

            $orderIds = [];
            foreach ($items as $item) {
                $qty = max(1, (int) $item->product_qty);
                $originalUnit = (float) ($item->original_unit_price ?? $item->selling_price ?? $item->product_price);
                $originalLine = (float) ($item->original_line_total ?? ($originalUnit * $qty));
                $newUnit = (float) $price['unit_price'];
                $newLine = round($newUnit * $qty, 2);

                $item->forceFill([
                    'joint_purchase_id' => $jointPurchase->id,
                    'joint_price_tier_id' => $price['tier_id'],
                    'original_unit_price' => $item->original_unit_price ?? $originalUnit,
                    'original_line_total' => $item->original_line_total ?? $originalLine,
                    'product_price' => $newUnit,
                    'selling_price' => $newUnit,
                    'line_total' => $newLine,
                    'repriced_unit_price' => $newUnit,
                    'repriced_line_total' => $newLine,
                    'reprice_adjustment_amount' => round($originalLine - $newLine, 2),
                    'reprice_status' => $originalLine == $newLine ? 'none' : 'pending_repayment',
                    'commission' => round($newLine * 0.1),
                ])->save();

                $orderIds[] = (int) $item->order_id;
            }

            foreach (array_unique($orderIds) as $orderId) {
                $this->recalculateOrderTotal($orderId);
            }
        });
    }

    private function recalculateOrderTotal(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        $lineTotal = (float) OrdersProduct::where('order_id', $orderId)->sum('line_total');
        $order->grand_total = round($lineTotal + (float) $order->shipping_charges - (float) $order->coupon_amount, 2);
        $order->save();
    }
}
