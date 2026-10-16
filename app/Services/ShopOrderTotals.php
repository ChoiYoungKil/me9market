<?php

namespace App\Services;

use App\Models\ChannelDeliveryCharge;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ShopOrderTotals
{
    public function shippingLabel(Product $product): string
    {
        if ($policy = $this->policy($product)) {
            $label = $policy->name;
            if ($policy->payment_type === 'cod') {
                $label = '착불 '.$label;
            }
            if ($policy->shipping_type === 'conditional') {
                $conditions = [];
                if ($policy->free_order_amount > 0) {
                    $conditions[] = number_format($policy->free_order_amount).'원 이상';
                }
                if ($policy->free_order_quantity > 0) {
                    $conditions[] = number_format($policy->free_order_quantity).'개 이상';
                }
                $label .= ' / '.implode(' 또는 ', $conditions).' 무료';
            }

            return $label;
        }
        if ($product->shipping_policy_type === 'free') {
            return '무료배송';
        }

        $label = number_format($product->shipping_base_fee ?? 2500).'원';
        if ($product->shipping_policy_type === 'free_conditional' || $product->shipping_base_fee === null) {
            $label .= ' / '.number_format($product->shipping_free_threshold ?? 30000).'원 이상 무료';
        }

        return ($product->shipping_payment_type === 'collect' ? '착불 ' : '').$label;
    }

    private function policy(Product $product): ?ChannelDeliveryCharge
    {
        if (! $product->shipping_policy_id && ! $product->shipping_policy_name) {
            return null;
        }
        $query = ChannelDeliveryCharge::where('vendor_id', $product->vendor_id);
        if ($product->shipping_policy_id) {
            $policy = $query->find($product->shipping_policy_id);
        } else {
            $matches = $query->where('name', $product->shipping_policy_name)->get();
            if ($matches->count() > 1) {
                throw ValidationException::withMessages(['shipping' => '배송비 정책을 식별자로 다시 선택해 주세요.']);
            }
            $policy = $matches->first();
        }
        if (($product->shipping_policy_id && ! $policy) || ($policy && ! $policy->status)) {
            throw ValidationException::withMessages(['shipping' => '사용할 수 없는 배송비 정책입니다.']);
        }

        return $policy;
    }

    public function calculate(array $items, array $destination = []): array
    {
        $shippingByProduct = [];
        $shippingPolicies = [];
        $legacyItems = [];
        $groups = collect($items)->groupBy('id');
        foreach ($groups as $rows) {
            $item = $rows->first();
            $item['line_total'] = $rows->sum('line_total');
            $item['qty'] = $rows->sum(fn ($row) => $row['qty'] ?? 1);
            $product = $item['product'];
            $fee = 0;
            $policy = $this->policy($product);
            if ($policy) {
                $shippingPolicies[$item['id']] = $policy->only(['id', 'vendor_id', 'name', 'shipping_type', 'payment_type', 'base_fee', 'fixed_fee', 'free_order_amount', 'free_order_quantity', 'calculation_rules']);
                $fee = app(DeliveryPolicyCalculator::class)->fee($policy, $item['line_total'], $item['qty'], $destination);
            } elseif ($product->shipping_payment_type !== 'collect' && $product->shipping_policy_type !== 'free') {
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
            'shipping_policies' => $shippingPolicies,
            'shipping_by_item' => $shippingByItem,
        ];
    }
}
