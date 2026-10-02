<?php

namespace App\Services;

use App\Models\Product;

class ShopOrderTotals
{
    public function shippingLabel(Product $product): string
    {
        if ($product->shipping_policy_type === 'free') {
            return '무료배송';
        }

        $label = number_format($product->shipping_base_fee ?? 2500).'원';
        if ($product->shipping_policy_type === 'free_conditional' || $product->shipping_base_fee === null) {
            $label .= ' / '.number_format($product->shipping_free_threshold ?? 30000).'원 이상 무료';
        }

        return ($product->shipping_payment_type === 'collect' ? '착불 ' : '').$label;
    }

    public function calculate(array $items): array
    {
        $shippingByProduct = [];
        $legacyItems = [];
        $groups = collect($items)->groupBy('id');
        foreach ($groups as $rows) {
            $item = $rows->first();
            $item['line_total'] = $rows->sum('line_total');
            $product = $item['product'];
            $fee = 0;
            if ($product->shipping_payment_type !== 'collect' && $product->shipping_policy_type !== 'free') {
                if ($product->shipping_base_fee === null) {
                    $legacyItems[] = $item;
                } elseif ($product->shipping_policy_type !== 'free_conditional' || $item['line_total'] < (float) $product->shipping_free_threshold) {
                    $fee = max(0, (float) $product->shipping_base_fee);
                }
            }
            $shippingByProduct[$item['id']] = $fee;
        }

        // Products created before shipping policies existed retain the original cart rule.
        $legacySubtotal = array_sum(array_column($legacyItems, 'line_total'));
        $remainingFee = $legacySubtotal > 0 && $legacySubtotal < 30000 ? 2500 : 0;
        $legacyFee = $remainingFee;
        foreach ($legacyItems as $index => $item) {
            $fee = $index === count($legacyItems) - 1
                ? $remainingFee
                : ($legacySubtotal > 0 ? round($legacyFee * $item['line_total'] / $legacySubtotal, 2) : 0);
            $shippingByProduct[$item['id']] = $fee;
            $remainingFee -= $fee;
        }

        $subtotal = array_sum(array_column($items, 'line_total'));
        $shipping = round(array_sum($shippingByProduct), 2);
        $shippingByItem = [];
        foreach ($groups as $id => $rows) {
            $fee = $shippingByProduct[$id];
            $remaining = $fee;
            $productTotal = $rows->sum('line_total');
            foreach ($rows->values() as $index => $row) {
                $allocation = $index === $rows->count() - 1 ? $remaining : ($productTotal > 0 ? round($fee * $row['line_total'] / $productTotal, 2) : 0);
                $shippingByItem[$row['key'] ?? $row['id']] = $allocation;
                $remaining -= $allocation;
            }
        }

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'shipping_by_product' => $shippingByProduct,
            'shipping_by_item' => $shippingByItem,
        ];
    }
}
