<?php

namespace App\Services;

use App\Models\OrdersProduct;
use App\Support\OrderItemStatus;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentService
{
    public function change(OrdersProduct $item, string $target): void
    {
        $target = OrderItemStatus::normalize($target);
        $current = $item->normalized_status;
        $allowed = [
            OrderItemStatus::PAID => [OrderItemStatus::READY_TO_SHIP, OrderItemStatus::SHIPPING, OrderItemStatus::CANCELLED],
            OrderItemStatus::READY_TO_SHIP => [OrderItemStatus::SHIPPING, OrderItemStatus::CANCELLED],
            OrderItemStatus::SHIPPING => [OrderItemStatus::DELIVERED],
            OrderItemStatus::DELIVERED => [OrderItemStatus::CONFIRMED],
            OrderItemStatus::CANCEL_REQUESTED => [OrderItemStatus::CANCELLED],
            OrderItemStatus::RETURN_RECEIVED => [OrderItemStatus::RETURNED],
        ];
        if ($target !== $current && ! in_array($target, $allowed[$current] ?? [], true)) {
            throw ValidationException::withMessages(['status' => '현재 주문 상태에서는 요청한 배송/구매확정 처리가 불가능합니다.']);
        }
        if (! in_array($target, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP, OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED, OrderItemStatus::CONFIRMED, OrderItemStatus::CANCELLED, OrderItemStatus::RETURNED], true)) {
            throw ValidationException::withMessages(['status' => '취소/반품/교환은 클레임 처리 화면에서 진행해 주세요.']);
        }
        $item->setStatus($target);
        $date = match ($target) {
            OrderItemStatus::SHIPPING => 'shipped_at',
            OrderItemStatus::DELIVERED => 'delivered_at',
            OrderItemStatus::CONFIRMED => 'confirmed_at',
            default => null,
        };
        if ($date && ! $item->$date) {
            $item->$date = now();
        }
        $item->save();
        if (in_array($target, [OrderItemStatus::CANCELLED, OrderItemStatus::RETURNED], true)) {
            app(CustomerPointService::class)->reverseItem($item);
            \App\Models\OrderClaim::where('order_product_id', $item->id)
                ->where('type', $target === OrderItemStatus::CANCELLED ? 'cancel' : 'return')
                ->whereIn('status', ['requested', 'received', 'held'])->update(['status' => 'completed']);
        }
        if ($target === OrderItemStatus::CONFIRMED) {
            $this->confirmPoints($item);
        }
    }

    public function confirmPoints(OrdersProduct $item): void
    {
        if ($item->is_exchange_replacement) {
            $original = OrdersProduct::whereKey($item->replacement_for_order_product_id)->lockForUpdate()->firstOrFail();
            if ($original->normalized_status === OrderItemStatus::EXCHANGED) {
                $original->setStatus(OrderItemStatus::CONFIRMED);
                $original->confirmed_at = $item->confirmed_at ?: now();
                $original->save();
                $this->confirmPoints($original);
            }

            return;
        }
        app(ChannelPointService::class)->recordCustomerPayback($item);
    }

    public function transferExchangeStock(OrdersProduct $item): void
    {
        $original = $item;
        while ($original->is_exchange_replacement) {
            $original = OrdersProduct::whereKey($original->replacement_for_order_product_id)->lockForUpdate()->firstOrFail();
        }
        $quantity = (int) $original->attribute_stock_deducted_qty;
        if (! $quantity) {
            return;
        }
        $attributes = \App\Models\ProductsAttribute::where('product_id', $item->product_id)->orderBy('id')->lockForUpdate()->get();
        $previous = $attributes->firstWhere('id', $original->stock_attribute_id);
        $next = $attributes->firstWhere('size', $item->product_size);
        if (! $previous || ! $next || (int) $next->status !== 1) {
            throw ValidationException::withMessages(['option' => '교환할 옵션의 재고 정보를 확인해 주세요.']);
        }
        if ($previous->id === $next->id) {
            return;
        }
        if ((int) $next->stock < $quantity) {
            throw ValidationException::withMessages(['option' => '교환할 옵션의 재고가 부족합니다.']);
        }
        $previous->increment('stock', $quantity);
        $next->decrement('stock', $quantity);
        $original->stock_attribute_id = $next->id;
        $original->save();
    }
}
