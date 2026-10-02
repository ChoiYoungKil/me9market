<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\SettlementRun;
use App\Support\OrderItemStatus;
use Illuminate\Support\Facades\DB;

class CommerceAudit
{
    public function inspect(): array
    {
        $issues = [];
        $flag = function (string $code, string $severity, int $id) use (&$issues): void {
            $issues[$code] ??= ['severity' => $severity, 'count' => 0, 'sample_ids' => []];
            $issues[$code]['count']++;
            if (count($issues[$code]['sample_ids']) < 20) {
                $issues[$code]['sample_ids'][] = $id;
            }
        };

        Order::with('orders_products')->chunkById(200, function ($orders) use ($flag) {
            foreach ($orders as $order) {
                $items = $order->orders_products->where('is_exchange_replacement', false);
                if ($items->isEmpty() || $items->contains(fn ($item) => $item->paid_line_total_snapshot === null || $item->point_usage_snapshot === null)) {
                    $flag('legacy_order_without_payment_snapshot', 'review', $order->id);
                } else {
                    $expected = $items->sum('paid_line_total_snapshot') + (float) $order->shipping_charges - (float) $order->coupon_amount - (int) $order->used_point;
                    if (abs($expected - (float) $order->grand_total) > 0.01) {
                        $flag('order_cash_total_mismatch', 'error', $order->id);
                    }
                    if ((int) $items->sum('used_point_amount') !== (int) $order->used_point) {
                        $flag('order_point_total_mismatch', 'error', $order->id);
                    }
                    if ($items->sum('refund_cash_amount') > (float) $order->grand_total + 0.01) {
                        $flag('refund_exceeds_collected_cash', 'error', $order->id);
                    }
                }
                foreach ($items as $item) {
                    if ($item->settlement_policy_snapshot === null) {
                        $flag('legacy_item_without_settlement_policy', 'review', $item->id);
                    }
                    if ($item->point_usage_snapshot !== null) {
                        $ledger = PointTransaction::where('order_product_id', $item->id)->get();
                        foreach (['channel', 'me9'] as $wallet) {
                            $scoped = $ledger->filter(fn ($entry) => (int) $entry->user_id === (int) $item->user_id
                                && ($wallet === 'me9' ? $entry->shop_channel_id === null : (int) $entry->shop_channel_id === (int) $item->shop_channel_id));
                            $used = -(int) $scoped->where('type', 'use')->sum('points');
                            $expectedUsed = (int) ($item->point_usage_snapshot[$wallet] ?? 0);
                            if ($used !== $expectedUsed) {
                                $flag('item_'.$wallet.'_spend_mismatch', 'error', $item->id);
                            }
                            $restored = (int) $scoped->where('type', 'refund')->sum('points');
                            if ($restored > $used || ($item->financial_reversed_at && $restored !== $used)) {
                                $flag('item_'.$wallet.'_restoration_mismatch', 'error', $item->id);
                            }
                        }
                        if (in_array($item->normalized_status, [OrderItemStatus::CANCELLED, OrderItemStatus::RETURNED], true) && ! $item->financial_reversed_at) {
                            $flag('closed_claim_without_reversal', 'error', $item->id);
                        }
                    }
                    if ($item->refund_status === 'review_required') {
                        $flag('refund_requires_manual_evidence', 'review', $item->id);
                    } elseif ($item->refund_status === 'pending_external') {
                        $flag('refund_pending_payment_provider', 'external', $item->id);
                    }
                    if ($item->reprice_status === 'pending_repayment' && ! $item->financial_reversed_at) {
                        $flag('joint_purchase_payment_adjustment_pending', 'review', $item->id);
                    }
                }
            }
        });

        PointTransaction::select('user_id', 'shop_channel_id')->selectRaw('SUM(points) as balance')
            ->groupBy('user_id', 'shop_channel_id')->havingRaw('SUM(points) < 0')->get()
            ->each(fn ($wallet) => $flag('negative_customer_wallet', 'error', $wallet->user_id));

        SettlementRun::with('items')->chunkById(100, function ($runs) use ($flag) {
            foreach ($runs as $run) {
                foreach (['gross_sales_amount', 'supply_amount', 'sales_profit_amount', 'invoice_sales_amount', 'invoice_purchase_amount',
                    'point_deposit_amount', 'point_used_amount', 'sms_postpaid_amount', 'payout_amount', 'settlement_amount', 'admin_amount'] as $field) {
                    if (abs((float) $run->$field - (float) $run->items->sum($field)) > 0.01) {
                        $flag('settlement_'.$field.'_mismatch', 'error', $run->id);
                    }
                }
            }
        });
        DB::table('settlement_executions')->join('settlement_runs', 'settlement_runs.id', '=', 'settlement_executions.settlement_run_id')
            ->whereColumn('settlement_executions.period', '!=', 'settlement_runs.period')
            ->pluck('settlement_executions.id')->each(fn ($id) => $flag('execution_period_mismatch', 'error', $id));

        return [
            'checked_at' => now()->toIso8601String(),
            'read_only' => true,
            'orders' => Order::count(),
            'settlement_runs' => SettlementRun::count(),
            'issues' => $issues,
            'passed' => empty($issues),
            'limitation' => 'Stored-data consistency only. Historical contracts, provider receipts, bank transfers and consent documents need separate evidence; missing snapshots are never invented.',
        ];
    }
}
