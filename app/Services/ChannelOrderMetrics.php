<?php

namespace App\Services;

use App\Models\OrdersProduct;
use App\Support\OrderItemStatus;

class ChannelOrderMetrics
{
    public function items(int $vendorId): \Illuminate\Database\Eloquent\Builder
    {
        return OrdersProduct::where(function ($query) use ($vendorId) {
            $query->where('orders_products.vendor_id', $vendorId)
                ->orWhereHas('shopChannel', fn ($q) => $q->where('vendor_id', $vendorId))
                ->orWhereHas('shopChannelProduct.shopChannel', fn ($q) => $q->where('vendor_id', $vendorId))
                ->orWhereHas('product', fn ($q) => $q->where('vendor_id', $vendorId));
        });
    }

    public function counts(int $vendorId, ?\Carbon\CarbonInterface $from = null, ?\Carbon\CarbonInterface $to = null): array
    {
        $items = $this->items($vendorId)
            ->whereHas('order', fn ($q) => $q
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to)))
            ->with('order:id,created_at')
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
                ->filter(fn ($item) => $item->order?->created_at?->isToday())
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
