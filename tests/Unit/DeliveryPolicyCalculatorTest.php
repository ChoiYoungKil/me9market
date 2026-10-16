<?php

namespace Tests\Unit;

use App\Models\ChannelDeliveryCharge;
use App\Services\DeliveryPolicyCalculator;
use PHPUnit\Framework\TestCase;

class DeliveryPolicyCalculatorTest extends TestCase
{
    private function fee(string $type, array $rules, float $amount, int $qty, array $destination = [], array $extra = []): float
    {
        $policy = new ChannelDeliveryCharge($extra + ['shipping_type' => $type, 'base_fee' => 3000, 'payment_type' => 'prepaid', 'calculation_rules' => $rules]);

        return (new DeliveryPolicyCalculator)->fee($policy, $amount, $qty, $destination);
    }

    public function test_conditional_shipping_uses_either_threshold_inclusively(): void
    {
        foreach ([[9999, 1, 3000], [10000, 1, 0], [9999, 2, 0]] as [$amount, $qty, $expected]) {
            $this->assertEquals($expected, $this->fee('conditional', [], $amount, $qty, [], ['free_order_amount' => 10000, 'free_order_quantity' => 2]));
        }
    }

    public function test_variable_shipping_quantity_amount_and_start_at_one_boundaries(): void
    {
        foreach (['quantity', 'amount'] as $basis) {
            foreach ([[1, 3000, 3500], [2, 3500, 3500], [3, 3500, 4000], [4, 4000, 4000]] as [$value, $normal, $fromOne]) {
                $rules = ['basis' => $basis, 'repeat_every' => 2, 'repeat_fee' => 500];
                $this->assertEquals($normal, $this->fee('variable', $rules, $value, $value));
                $this->assertEquals($fromOne, $this->fee('variable', $rules + ['start_at_one' => true], $value, $value));
            }
        }
    }

    public function test_tiers_apply_one_range_and_regional_fees_are_separate(): void
    {
        foreach (['quantity', 'amount'] as $basis) {
            $rules = ['basis' => $basis, 'tiers' => [['min' => 0, 'max' => 2, 'fee' => 5000], ['min' => 2, 'max' => 4, 'fee' => 3000], ['min' => 4, 'max' => null, 'fee' => 0]],
                'region_mode' => 'three', 'jeju_fee' => 1000, 'remote_fee' => 2000, 'mainland_fee' => 500, 'remote_postcodes' => ['12340-12349']];
            foreach ([[1, 5000], [2, 3000], [3, 3000], [4, 0]] as [$value, $base]) {
                $this->assertEquals($base, $this->fee('tiered', $rules, $value, $value));
                $this->assertEquals($base + 1000, $this->fee('tiered', $rules, $value, $value, ['address' => '제주특별자치도']));
                $this->assertEquals($base + 2000, $this->fee('tiered', $rules, $value, $value, ['pincode' => '12345']));
            }
        }
    }

    public function test_regional_fee_repeats_and_two_regions_combine_jeju_and_remote(): void
    {
        $rules = ['basis' => 'quantity', 'repeat_every' => 2, 'repeat_fee' => 500, 'region_mode' => 'two', 'jeju_fee' => 1000,
            'remote_fee' => 9999, 'repeat_region' => true, 'remote_postcodes' => ['12345']];
        $this->assertEquals(7000, $this->fee('variable', $rules, 10000, 4, ['pincode' => '12345']));
        $this->assertEquals(0, $this->fee('fixed', [], 10000, 1, [], ['payment_type' => 'cod']));
    }
}
