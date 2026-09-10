<?php

namespace App\Services;

use App\Models\OrdersProduct;

class ReturnAddressResolver
{
    public function forOrderItem(OrdersProduct $item): string
    {
        $item->loadMissing([
            'distributor',
            'product.distributor',
            'product.vendor.vendorbusinessdetails',
            'shopChannel.vendor.vendorbusinessdetails',
        ]);

        $distributor = $item->distributor ?: $item->product?->distributor;
        if ($distributor?->return_address) {
            return trim(implode(' ', array_filter([
                $distributor->return_postcode,
                $distributor->return_address,
            ])));
        }

        $vendor = $item->product?->vendor ?: $item->shopChannel?->vendor;
        $business = $vendor?->vendorbusinessdetails;

        return trim(implode(' ', array_filter([
            $business?->shop_pincode ?: $vendor?->pincode,
            $business?->shop_address ?: $vendor?->address,
            $business?->shop_address_detail,
            $business?->shop_city ?: $vendor?->city,
            $business?->shop_state ?: $vendor?->state,
        ]))) ?: '반송지 정보는 채널 관리자에게 문의해 주세요.';
    }
}
