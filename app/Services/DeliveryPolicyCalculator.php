<?php

namespace App\Services;

use App\Models\ChannelDeliveryCharge;
use Illuminate\Validation\ValidationException;

class DeliveryPolicyCalculator
{
    public function fee(ChannelDeliveryCharge $policy, float $amount, int $quantity, array $destination = []): float
    {
        $rules = $policy->calculation_rules ?? [];
        $region = 'mainland';
        $postcode = (string) ($destination['pincode'] ?? '');
        foreach (['jeju', 'remote'] as $candidate) {
            foreach ($rules[$candidate.'_postcodes'] ?? [] as $range) {
                if (preg_match('/^([0-9]{5})(?:-([0-9]{5}))?$/', trim($range), $match)
                    && strlen($postcode) === 5 && ctype_digit($postcode)
                    && $postcode >= $match[1] && $postcode <= ($match[2] ?? $match[1])) {
                    $region = $candidate;
                }
            }
        }
        if (str_contains((string) ($destination['address'] ?? ''), '제주')) {
            $region = 'jeju';
        }
        if (($rules['block_'.$region] ?? false) && $destination) {
            throw ValidationException::withMessages(['address' => '해당 지역에는 배송할 수 없는 상품이 포함되어 있습니다.']);
        }
        $fee = (float) $policy->base_fee;
        $repeats = 0;
        if ($policy->shipping_type === 'free') {
            $fee = 0;
        } elseif ($policy->shipping_type === 'fixed') {
            $fee = (float) ($policy->fixed_fee ?? $policy->base_fee);
        } elseif ($policy->shipping_type === 'conditional') {
            if (($policy->free_order_amount > 0 && $amount >= $policy->free_order_amount)
                || ($policy->free_order_quantity > 0 && $quantity >= $policy->free_order_quantity)) {
                $fee = 0;
            }
        } elseif ($policy->shipping_type === 'variable') {
            $basis = ($rules['basis'] ?? 'quantity') === 'amount' ? $amount : $quantity;
            $step = (int) ($rules['repeat_every'] ?? 0);
            if ($step < 1) {
                throw ValidationException::withMessages(['shipping' => '반복 배송비 조건을 확인해 주세요.']);
            }
            $repeats = ($rules['start_at_one'] ?? false)
                ? (int) floor(max(0, $basis - 1) / $step) + ($basis > 0 ? 1 : 0)
                : (int) floor($basis / $step);
            $fee += $repeats * (float) ($rules['repeat_fee'] ?? 0);
        } elseif ($policy->shipping_type === 'tiered') {
            $basis = ($rules['basis'] ?? 'quantity') === 'amount' ? $amount : $quantity;
            $tier = collect($rules['tiers'] ?? [])->first(fn ($tier) => $basis >= $tier['min'] && ($tier['max'] === null || $basis < $tier['max']));
            if (! $tier) {
                throw ValidationException::withMessages(['shipping' => '적용 가능한 배송비 구간이 없습니다.']);
            }
            $fee = (float) $tier['fee'];
        }
        $regionalFee = 0;
        if ($destination && ($rules['region_mode'] ?? 'none') !== 'none') {
            $feeKey = $region === 'mainland' ? 'mainland_fee' : (($rules['region_mode'] ?? '') === 'two' ? 'jeju_fee' : $region.'_fee');
            $regionalFee = (float) ($rules[$feeKey] ?? 0);
            if ($policy->shipping_type === 'variable' && ($rules['repeat_region'] ?? false)) {
                $regionalFee *= 1 + $repeats;
            }
        }

        // Restrictions still apply to cash-on-delivery orders.
        return $policy->payment_type === 'cod' ? 0.0 : max(0, $fee + $regionalFee);
    }
}
