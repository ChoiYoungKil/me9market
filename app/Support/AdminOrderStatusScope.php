<?php

namespace App\Support;

class AdminOrderStatusScope
{
    public static function apply($query, string $status): void
    {
        $aliases = ['paid' => ['Payment Captured', 'New'], 'ready_to_ship' => ['In Process', '배송준비중'], 'shipping' => ['Shipped'], 'delivered' => ['Delivered'], 'confirmed' => ['Confirmed'], 'cancelled' => ['Cancelled']];
        $legacy = array_merge([$status, OrderItemStatus::label($status)], $aliases[$status] ?? []);
        $query->where(fn ($q) => $q->where('status_code', $status)->orWhere(fn ($q) => $q->where(fn ($q) => $q->whereNull('status_code')->orWhere('status_code', ''))->whereIn('item_status', $legacy)));
    }
}
