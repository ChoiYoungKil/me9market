<?php

namespace App\Services;

use App\Models\OrdersProduct;
use App\Support\OrderItemStatus;

class ChannelOrderMetrics
{
    public function counts(int $vendorId): array
    {
        $items = OrdersProduct::where('vendor_id', $vendorId)
            ->get(['order_id', 'item_status', 'status_code', 'settlement_status', 'created_at']);

        $statusOrderIds = [];

        foreach ($items as $item) {
            $status = OrderItemStatus::normalize($item->status_code ?: $item->item_status);
            $statusOrderIds[$status][$item->order_id] = true;
        }

        $countStatus = function (string $status) use ($statusOrderIds): int {
            return isset($statusOrderIds[$status]) ? count($statusOrderIds[$status]) : 0;
        };

        return [
            'total' => $items->pluck('order_id')->unique()->count(),
            'today_orders' => $items
                ->filter(fn ($item) => $item->created_at && $item->created_at->isToday())
                ->pluck('order_id')
                ->unique()
                ->count(),
            'paid' => $countStatus(OrderItemStatus::PAID),
            'shipping_ready' => $countStatus(OrderItemStatus::READY_TO_SHIP),
            'shipping' => $countStatus(OrderItemStatus::SHIPPING),
            'complete' => $countStatus(OrderItemStatus::CONFIRMED),
            'cancel_request' => $countStatus(OrderItemStatus::CANCEL_REQUESTED),
            'return_request' => $countStatus(OrderItemStatus::RETURN_REQUESTED),
            'settlement_wait' => $countStatus(OrderItemStatus::CONFIRMED),
            'settlement_complete' => $items
                ->where('settlement_status', 'completed')
                ->pluck('order_id')
                ->unique()
                ->count(),
        ];
    }
}
