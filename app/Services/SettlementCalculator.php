<?php

namespace App\Services;

use App\Models\OrdersProduct;
use App\Models\SettlementItem;
use App\Models\SettlementRun;
use App\Support\OrderItemStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SettlementCalculator
{
    private const COMMISSION_VAT_MULTIPLIER = 1.1;

    private array $orderLineTotals = [];
    private array $fallbackShops = [];

    public function periodOptions(int $months = 12): array
    {
        $periods = [];
        $cursor = now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $period = $cursor->copy()->subMonths($i)->format('Y-m');
            $periods[$period] = $period;
        }

        return $periods;
    }

    public function normalizePeriod(?string $period): string
    {
        if ($period && preg_match('/^\d{4}-\d{2}$/', $period) && array_key_exists($period, $this->periodOptions())) {
            return $period;
        }

        return now()->format('Y-m');
    }

    public function preview(string $period, ?int $vendorId = null, ?int $shopChannelId = null): Collection
    {
        $rows = $this->items($period, $vendorId, $shopChannelId);

        return $rows
            ->groupBy(fn (array $row) => $row['vendor_id'] . ':' . ($row['shop_channel_id'] ?: 0) . ':' . ($row['settlement_role'] ?? 'seller'))
            ->map(fn (Collection $group) => $this->summarizeGroup($period, $group))
            ->values();
    }

    public function items(string $period, ?int $vendorId = null, ?int $shopChannelId = null): Collection
    {
        return $this->confirmedItems($period, $vendorId, $shopChannelId)
            ->get()
            ->flatMap(fn (OrdersProduct $item) => $this->calculateItemRows($item, $period))
            ->when($vendorId, fn (Collection $rows) => $rows->where('vendor_id', $vendorId))
            ->values();
    }

    public function generate(string $period, ?int $adminId = null): Collection
    {
        $summaries = $this->preview($period);

        return DB::transaction(function () use ($summaries, $adminId) {
            return $summaries->map(function (array $summary) use ($adminId) {
                $run = SettlementRun::where('settlement_key', $summary['settlement_key'])->first();

                if ($run && $run->status === 'completed') {
                    return $run->fresh('items');
                }

                $run = SettlementRun::updateOrCreate(
                    ['settlement_key' => $summary['settlement_key']],
                    collect($summary)
                        ->except('items')
                        ->merge([
                            'status' => 'pending',
                            'executed_at' => null,
                            'executed_by' => $adminId,
                        ])
                        ->all()
                );

                $run->items()->delete();
                foreach ($summary['items'] as $item) {
                    SettlementItem::create(array_merge($item, [
                        'settlement_run_id' => $run->id,
                        'status' => 'pending',
                    ]));
                }

                return $run->fresh('items');
            });
        });
    }

    private function confirmedItems(string $period, ?int $vendorId = null, ?int $shopChannelId = null)
    {
        [$from, $to] = $this->periodRange($period);
        $statusValues = $this->confirmedStatusValues();
        $settlementDateExpression = $this->settlementDateExpression();

        return OrdersProduct::with(['order', 'shopChannel', 'shopChannelProduct', 'product', 'product.vendor'])
            ->when($vendorId, function ($query) use ($vendorId) {
                $query->where(function ($inner) use ($vendorId) {
                    $inner->where('vendor_id', $vendorId)
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('vendor_id', $vendorId));
                });
            })
            ->when($shopChannelId !== null, function ($query) use ($shopChannelId) {
                if ($shopChannelId > 0) {
                    $query->where('shop_channel_id', $shopChannelId);
                } else {
                    $query->whereNull('shop_channel_id');
                }
            })
            ->where(function ($query) use ($statusValues) {
                $query->whereIn('status_code', $statusValues)
                    ->orWhereIn('item_status', $statusValues);
            })
            ->whereRaw($settlementDateExpression . ' >= ?', [$from])
            ->whereRaw($settlementDateExpression . ' <= ?', [$to])
            ->orderBy('vendor_id')
            ->orderBy('shop_channel_id')
            ->orderBy('id');
    }

    private function calculateItemRows(OrdersProduct $item, string $period): Collection
    {
        $quantity = (int) $item->product_qty;
        $lineTotal = (float) ($item->line_total > 0 ? $item->line_total : $item->product_price * $quantity);
        $grossSales = round(max($lineTotal - $this->allocatedCouponAmount($item), 0), 2);
        $supplyUnit = (float) ($item->supply_price > 0 ? $item->supply_price : $item->product_price);
        $supplyAmount = round($supplyUnit * $quantity, 2);
        $shippingAmount = $this->allocatedShippingAmount($item);
        $invoiceGross = round($grossSales + $shippingAmount, 2);
        $salesProfit = max($grossSales - $supplyAmount, 0);
        $shop = $item->shopChannel ?: $this->fallbackShopForVendor((int) $item->vendor_id);
        $shopProduct = $item->shopChannelProduct;
        $productType = $shopProduct?->product_type ?: 'own';
        $isShared = in_array($productType, ['public', 'partial'], true);
        $configuredRewardPoints = round(max(0, (float) ($item->product?->reward_points ?? 0)) * $quantity, 2);
        $usesOwnPg = (bool) ($shop?->use_own_pg ?? false) && $configuredRewardPoints <= 0 && !$isShared;
        $paymentGatewayType = $usesOwnPg ? 'own_pg' : 'me9_pg';
        $vendor = $item->product?->vendor;
        $settlementType = (int) ($shopProduct?->settlement_type_snapshot ?: $shop?->settlement_type ?: 1);
        $settlementRate = (float) ($shopProduct?->settlement_rate_snapshot ?? $shop?->settlement_rate ?? $vendor?->commission ?? 0);
        $commissionAmount = $this->commissionAmount($invoiceGross, $quantity, $settlementType, $settlementRate);
        $confirmedAt = $this->settlementDateForItem($item);
        $rewardPoints = $usesOwnPg ? 0 : $configuredRewardPoints;
        $usedPointAmount = $this->allocatedUsedPointAmount($item);
        $smsFee = (float) ($item->sms_fee ?? 0);
        $ownPgPayoutAmount = $usesOwnPg ? max(0, round($usedPointAmount - $smsFee, 2)) : 0;
        $settlementCommissionAmount = $usesOwnPg ? 0 : $commissionAmount;
        $isFixedShared = $isShared
            && (bool) ($item->product?->price_constraint_enabled)
            && $item->product?->price_constraint_type === 'fixed';

        if (!$isShared) {
            return collect([
                $this->baseRow($item, $period, [
                    'settlement_role' => 'seller',
                    'vendor_id' => (int) $item->vendor_id,
                    'vendor_name' => $shop?->vendor?->name ?: '판매자 #' . $item->vendor_id,
                    'gross_sales_amount' => $invoiceGross,
                    'supply_amount' => $supplyAmount,
                    'sales_profit_amount' => $salesProfit,
                    'invoice_sales_amount' => $invoiceGross,
                    'invoice_purchase_amount' => $settlementCommissionAmount,
                    'point_deposit_amount' => $rewardPoints,
                    'point_used_amount' => $usedPointAmount,
                    'sms_postpaid_amount' => $smsFee,
                    'payment_gateway_type' => $paymentGatewayType,
                    'settlement_amount' => $usesOwnPg ? $ownPgPayoutAmount : $this->payoutAfterCosts($invoiceGross, $commissionAmount, $rewardPoints, $smsFee),
                    'admin_amount' => $settlementCommissionAmount,
                    'payout_amount' => $usesOwnPg ? $ownPgPayoutAmount : $this->payoutAfterCosts($invoiceGross, $commissionAmount, $rewardPoints, $smsFee),
                    'settlement_type' => $settlementType,
                    'settlement_rate' => $settlementRate,
                    'confirmed_at' => $confirmedAt,
                ]),
            ]);
        }

        $supplierVendorId = (int) ($item->product?->vendor_id ?: $item->vendor_id);
        $channelVendorId = (int) $item->vendor_id;
        $rebateAmount = $this->rebateAmount($item, $invoiceGross);

        if ($isFixedShared) {
            return collect([
                $this->baseRow($item, $period, [
                    'settlement_role' => 'shared_fixed_supplier',
                    'vendor_id' => $supplierVendorId,
                    'vendor_name' => $vendor?->name ?: '판매자 #' . $supplierVendorId,
                    'gross_sales_amount' => $invoiceGross,
                    'supply_amount' => $supplyAmount,
                    'sales_profit_amount' => 0,
                    'invoice_sales_amount' => $invoiceGross,
                    'invoice_purchase_amount' => round($commissionAmount + $rebateAmount, 2),
                    'point_deposit_amount' => 0,
                    'point_used_amount' => $usedPointAmount,
                    'sms_postpaid_amount' => 0,
                    'payment_gateway_type' => $paymentGatewayType,
                    'settlement_amount' => $usesOwnPg ? 0 : round($invoiceGross - $commissionAmount - $rebateAmount, 2),
                    'admin_amount' => $commissionAmount,
                    'payout_amount' => $usesOwnPg ? 0 : round($invoiceGross - $commissionAmount - $rebateAmount, 2),
                    'settlement_type' => $settlementType,
                    'settlement_rate' => $settlementRate,
                    'confirmed_at' => $confirmedAt,
                ]),
                $this->baseRow($item, $period, [
                    'settlement_role' => 'shared_fixed_reseller',
                    'vendor_id' => $channelVendorId,
                    'vendor_name' => $shop?->vendor?->name ?: '판매자 #' . $channelVendorId,
                    'gross_sales_amount' => $rebateAmount,
                    'supply_amount' => 0,
                    'sales_profit_amount' => $rebateAmount,
                    'invoice_sales_amount' => $rebateAmount,
                    'invoice_purchase_amount' => 0,
                    'point_deposit_amount' => $rewardPoints,
                    'point_used_amount' => 0,
                    'sms_postpaid_amount' => $smsFee,
                    'payment_gateway_type' => $paymentGatewayType,
                    'settlement_amount' => $usesOwnPg ? 0 : $this->payoutAfterCosts($rebateAmount, 0, $rewardPoints, $smsFee),
                    'admin_amount' => 0,
                    'payout_amount' => $usesOwnPg ? 0 : $this->payoutAfterCosts($rebateAmount, 0, $rewardPoints, $smsFee),
                    'settlement_type' => $settlementType,
                    'settlement_rate' => $settlementRate,
                    'confirmed_at' => $confirmedAt,
                ]),
            ]);
        }

        return collect([
            $this->baseRow($item, $period, [
                'settlement_role' => 'shared_free_supplier',
                'vendor_id' => $supplierVendorId,
                'vendor_name' => $vendor?->name ?: '판매자 #' . $supplierVendorId,
                'gross_sales_amount' => round($supplyAmount + $shippingAmount, 2),
                'supply_amount' => $supplyAmount,
                'sales_profit_amount' => 0,
                'invoice_sales_amount' => round($supplyAmount + $shippingAmount, 2),
                'invoice_purchase_amount' => 0,
                'point_deposit_amount' => 0,
                'point_used_amount' => 0,
                'sms_postpaid_amount' => 0,
                'payment_gateway_type' => $paymentGatewayType,
                'settlement_amount' => $usesOwnPg ? 0 : round($supplyAmount + $shippingAmount, 2),
                'admin_amount' => 0,
                'payout_amount' => $usesOwnPg ? 0 : round($supplyAmount + $shippingAmount, 2),
                'settlement_type' => $settlementType,
                'settlement_rate' => $settlementRate,
                'confirmed_at' => $confirmedAt,
            ]),
            $this->baseRow($item, $period, [
                'settlement_role' => 'shared_free_reseller',
                'vendor_id' => $channelVendorId,
                'vendor_name' => $shop?->vendor?->name ?: '판매자 #' . $channelVendorId,
                'gross_sales_amount' => $invoiceGross,
                'supply_amount' => $supplyAmount,
                'sales_profit_amount' => $salesProfit,
                'invoice_sales_amount' => $invoiceGross,
                'invoice_purchase_amount' => round($supplyAmount + $shippingAmount + $commissionAmount, 2),
                'point_deposit_amount' => $rewardPoints,
                'point_used_amount' => $usedPointAmount,
                'sms_postpaid_amount' => $smsFee,
                'payment_gateway_type' => $paymentGatewayType,
                'settlement_amount' => $usesOwnPg ? 0 : $this->payoutAfterCosts($invoiceGross - $supplyAmount - $shippingAmount, $commissionAmount, $rewardPoints, $smsFee),
                'admin_amount' => $commissionAmount,
                'payout_amount' => $usesOwnPg ? 0 : $this->payoutAfterCosts($invoiceGross - $supplyAmount - $shippingAmount, $commissionAmount, $rewardPoints, $smsFee),
                'settlement_type' => $settlementType,
                'settlement_rate' => $settlementRate,
                'confirmed_at' => $confirmedAt,
            ]),
        ]);
    }

    private function baseRow(OrdersProduct $item, string $period, array $amounts): array
    {
        $vendorId = (int) ($amounts['vendor_id'] ?? $item->vendor_id);
        $role = $amounts['settlement_role'] ?? 'seller';

        return array_merge([
            'settlement_key' => $this->settlementKey($period, $vendorId, $item->shop_channel_id ? (int) $item->shop_channel_id : null, $role),
            'period' => $period,
            'order_product_id' => $item->id,
            'order_id' => $item->order_id,
            'shop_channel_id' => $item->shop_channel_id,
            'shop_channel_name' => $item->shopChannel?->channel_name ?: 'Me9 Market',
            'product_id' => $item->product_id,
            'order_no' => 'Me9-' . str_pad((string) $item->order_id, 8, '0', STR_PAD_LEFT),
            'product_code' => $item->product_code,
            'product_name' => $item->product_name,
            'quantity' => (int) $item->product_qty,
        ], $amounts);
    }

    private function summarizeGroup(string $period, Collection $group): array
    {
        $first = $group->first();

        return [
            'settlement_key' => $first['settlement_key'],
            'settlement_role' => $first['settlement_role'] ?? 'seller',
            'payment_gateway_type' => $first['payment_gateway_type'] ?? 'me9_pg',
            'period' => $period,
            'vendor_id' => $first['vendor_id'],
            'shop_channel_id' => $first['shop_channel_id'],
            'vendor_name' => $first['vendor_name'],
            'shop_channel_name' => $first['shop_channel_name'],
            'settlement_type' => $first['settlement_type'],
            'settlement_rate' => $first['settlement_rate'],
            'order_count' => $group->pluck('order_id')->unique()->count(),
            'item_count' => $group->count(),
            'quantity' => $group->sum('quantity'),
            'gross_sales_amount' => round($group->sum('gross_sales_amount'), 2),
            'supply_amount' => round($group->sum('supply_amount'), 2),
            'sales_profit_amount' => round($group->sum('sales_profit_amount'), 2),
            'invoice_sales_amount' => round($group->sum('invoice_sales_amount'), 2),
            'invoice_purchase_amount' => round($group->sum('invoice_purchase_amount'), 2),
            'point_deposit_amount' => round($group->sum('point_deposit_amount'), 2),
            'point_used_amount' => round($group->sum('point_used_amount'), 2),
            'sms_postpaid_amount' => round($group->sum('sms_postpaid_amount'), 2),
            'payout_amount' => round($group->sum('payout_amount'), 2),
            'settlement_amount' => round($group->sum('settlement_amount'), 2),
            'admin_amount' => round($group->sum('admin_amount'), 2),
            'items' => $group->map(fn (array $row) => collect($row)
                ->only([
                    'order_product_id',
                    'settlement_role',
                    'payment_gateway_type',
                    'order_id',
                    'vendor_id',
                    'shop_channel_id',
                    'product_id',
                    'order_no',
                    'product_code',
                    'product_name',
                    'quantity',
                    'gross_sales_amount',
                    'supply_amount',
                    'sales_profit_amount',
                    'invoice_sales_amount',
                    'invoice_purchase_amount',
                    'point_deposit_amount',
                    'point_used_amount',
                    'sms_postpaid_amount',
                    'payout_amount',
                    'settlement_type',
                    'settlement_rate',
                    'settlement_amount',
                    'admin_amount',
                    'confirmed_at',
                ])
                ->all())
                ->values()
                ->all(),
        ];
    }

    private function settlementKey(string $period, int $vendorId, ?int $shopChannelId, string $role): string
    {
        return $period . ':vendor:' . $vendorId . ':shop:' . ($shopChannelId ?: 0) . ':role:' . $role;
    }

    private function commissionAmount(float $grossAmount, int $quantity, int $settlementType, float $settlementRate): float
    {
        $amount = $settlementType === 2
            ? $quantity * $settlementRate
            : $grossAmount * ($settlementRate / 100) * self::COMMISSION_VAT_MULTIPLIER;

        return $this->ceilToTen(max(0, $amount));
    }

    private function payoutAfterCosts(float $baseAmount, float $commissionAmount = 0, float $rewardPoints = 0, float $smsFee = 0): float
    {
        return round($baseAmount - $commissionAmount - $rewardPoints - $smsFee, 2);
    }

    private function ceilToTen(float $amount): float
    {
        return ceil($amount / 10) * 10;
    }

    private function rebateAmount(OrdersProduct $item, float $invoiceGross): float
    {
        $type = $item->product?->profit_share_type;
        $value = (float) ($item->product?->profit_share_value ?? 0);

        if ($type === 'percent') {
            return round($invoiceGross * ($value / 100), 2);
        }

        if ($type === 'fixed') {
            return round($value * (int) $item->product_qty, 2);
        }

        return 0;
    }

    private function allocatedShippingAmount(OrdersProduct $item): float
    {
        $shipping = (float) ($item->order?->shipping_charges ?? 0);
        if ($shipping <= 0 || !$item->order_id) {
            return 0;
        }

        $lineTotal = (float) ($item->line_total > 0 ? $item->line_total : $item->product_price * $item->product_qty);
        $orderTotal = $this->orderLineTotal((int) $item->order_id);

        if ($orderTotal <= 0) {
            return 0;
        }

        return round($shipping * ($lineTotal / $orderTotal), 2);
    }

    private function allocatedUsedPointAmount(OrdersProduct $item): float
    {
        $usedPoint = (float) ($item->order?->used_point ?? 0);
        if ($usedPoint <= 0 || !$item->order_id) {
            return 0;
        }

        $lineTotal = (float) ($item->line_total > 0 ? $item->line_total : $item->product_price * $item->product_qty);
        $orderTotal = $this->orderLineTotal((int) $item->order_id);

        if ($orderTotal <= 0) {
            return 0;
        }

        return round($usedPoint * ($lineTotal / $orderTotal), 2);
    }

    private function allocatedCouponAmount(OrdersProduct $item): float
    {
        $couponAmount = (float) ($item->order?->coupon_amount ?? 0);
        if ($couponAmount <= 0 || !$item->order_id) {
            return 0;
        }

        $lineTotal = (float) ($item->line_total > 0 ? $item->line_total : $item->product_price * $item->product_qty);
        $orderTotal = $this->orderLineTotal((int) $item->order_id);

        if ($orderTotal <= 0) {
            return 0;
        }

        return round($couponAmount * ($lineTotal / $orderTotal), 2);
    }

    private function orderLineTotal(int $orderId): float
    {
        if (!array_key_exists($orderId, $this->orderLineTotals)) {
            $this->orderLineTotals[$orderId] = (float) OrdersProduct::where('order_id', $orderId)
                ->selectRaw('SUM(CASE WHEN line_total > 0 THEN line_total ELSE product_price * product_qty END) as total')
                ->value('total');
        }

        return $this->orderLineTotals[$orderId];
    }

    private function periodRange(string $period): array
    {
        $from = Carbon::createFromFormat('Y-m-d H:i:s', $period . '-01 00:00:00')->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return [$from, $to];
    }

    private function settlementDateExpression(): string
    {
        $jointSettlementDate = DB::connection()->getDriverName() === 'sqlite'
            ? "date((SELECT end_date FROM joint_purchases WHERE joint_purchases.id = orders_products.joint_purchase_id), '+7 days')"
            : "(SELECT DATE_ADD(end_date, INTERVAL 7 DAY) FROM joint_purchases WHERE joint_purchases.id = orders_products.joint_purchase_id)";

        return "COALESCE(CASE WHEN orders_products.joint_purchase_id IS NOT NULL THEN {$jointSettlementDate} END, orders_products.confirmed_at, orders_products.updated_at)";
    }

    private function settlementDateForItem(OrdersProduct $item)
    {
        if ($item->joint_purchase_id && DB::getSchemaBuilder()->hasTable('joint_purchases')) {
            $endDate = DB::table('joint_purchases')->where('id', $item->joint_purchase_id)->value('end_date');
            if ($endDate) {
                return Carbon::parse($endDate)->addDays(7)->endOfDay();
            }
        }

        return $item->confirmed_at ?: $item->updated_at;
    }

    private function confirmedStatusValues(): array
    {
        return array_values(array_unique([
            OrderItemStatus::CONFIRMED,
            OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'Confirmed',
            '구매확정',
        ]));
    }

    private function fallbackShopForVendor(int $vendorId)
    {
        if ($vendorId <= 0) {
            return null;
        }

        if (!array_key_exists($vendorId, $this->fallbackShops)) {
            $this->fallbackShops[$vendorId] = \App\Models\ShopChannel::where('vendor_id', $vendorId)
                ->orderByDesc('status')
                ->orderBy('id')
                ->first();
        }

        return $this->fallbackShops[$vendorId];
    }
}
