<?php

namespace App\Services;

use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;

class OrderSettlementPolicy
{
    public function capture(?ShopChannel $shop, ?ShopChannelProduct $shopProduct, ?Product $product): array
    {
        $productType = $shopProduct?->product_type ?: 'own';
        $isShared = in_array($productType, ['public', 'partial'], true);
        $isFixedShared = $isShared && $product?->price_constraint_enabled && $product?->price_constraint_type === 'fixed';
        $rewardPoints = $isFixedShared ? 0 : max(0, (int) ($product?->reward_points ?? 0));

        return [
            'product_type' => $productType,
            'supplier_vendor_id' => $product?->vendor_id,
            'supplier_vendor_name' => $product?->vendor?->name,
            'is_fixed_shared' => (bool) $isFixedShared,
            'reward_points' => $rewardPoints,
            'payment_gateway_type' => $shop?->use_own_pg && $rewardPoints <= 0 && ! $isShared ? 'own_pg' : 'me9_pg',
            'settlement_type' => (int) ($shopProduct?->settlement_type_snapshot ?: $shop?->settlement_type ?: 1),
            'settlement_rate' => (float) ($shopProduct?->settlement_rate_snapshot ?? $shop?->settlement_rate ?? $product?->vendor?->commission ?? 0),
            'profit_share_type' => $product?->profit_share_type,
            'profit_share_value' => (float) ($product?->profit_share_value ?? 0),
        ];
    }

    public function forItem(OrdersProduct $item, ?ShopChannel $fallbackShop = null): array
    {
        // Older orders have no snapshot; retain their existing policy fallback.
        $policy = $item->settlement_policy_snapshot ?? $this->capture(
            $item->shopChannel ?: $fallbackShop,
            $item->shopChannelProduct,
            $item->product
        );
        $policy['payment_gateway_type'] = $item->payment_gateway_type ?: $policy['payment_gateway_type'];

        return $policy;
    }
}
