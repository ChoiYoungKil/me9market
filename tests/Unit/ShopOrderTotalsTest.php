<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\ShopOrderTotals;
use PHPUnit\Framework\TestCase;

class ShopOrderTotalsTest extends TestCase
{
    private function item(int $id, float $total, array $policy): array
    {
        return ['id' => $id, 'line_total' => $total, 'product' => new Product($policy)];
    }

    public function test_shipping_respects_each_product_policy_and_collect_is_not_charged_online(): void
    {
        $totals = (new ShopOrderTotals)->calculate([
            $this->item(1, 10000, ['shipping_policy_type' => 'free']),
            $this->item(2, 10000, ['shipping_policy_type' => 'paid', 'shipping_base_fee' => 3500]),
            $this->item(3, 10000, ['shipping_policy_type' => 'free_conditional', 'shipping_base_fee' => 2000, 'shipping_free_threshold' => 20000]),
            $this->item(4, 20000, ['shipping_policy_type' => 'free_conditional', 'shipping_base_fee' => 2000, 'shipping_free_threshold' => 20000]),
            $this->item(5, 10000, ['shipping_policy_type' => 'paid', 'shipping_payment_type' => 'collect', 'shipping_base_fee' => 5000]),
        ]);
        $this->assertEquals([1 => 0, 2 => 3500, 3 => 2000, 4 => 0, 5 => 0], $totals['shipping_by_product']);
        $this->assertEquals(5500, $totals['shipping']);
        $this->assertEquals(65500, $totals['total']);
    }

    public function test_legacy_shipping_allocation_sums_to_the_charged_amount(): void
    {
        $service = new ShopOrderTotals;
        $items = [$this->item(1, 1000, []), $this->item(2, 1000, []), $this->item(3, 1000, [])];
        $totals = $service->calculate($items);
        $this->assertEquals([1 => 833.33, 2 => 833.33, 3 => 833.34], $totals['shipping_by_product']);
        $this->assertEquals(2500, $totals['shipping']);
        $this->assertEquals(0, $service->calculate([$this->item(4, 30000, [])])['shipping']);
        $this->assertEquals(0, $service->calculate([])['total']);
    }

    public function test_option_rows_share_product_shipping_and_free_shipping_threshold(): void
    {
        $policy = ['shipping_policy_type'=>'free_conditional','shipping_base_fee'=>3000,'shipping_free_threshold'=>30000];
        $items = [
            $this->item(1,10000,$policy) + ['key'=>'1:black'],
            $this->item(1,15000,$policy) + ['key'=>'1:white'],
        ];
        $service = new ShopOrderTotals;
        $totals = $service->calculate($items);
        $this->assertEquals(3000, $totals['shipping']);
        $this->assertEquals(['1:black'=>1200,'1:white'=>1800], $totals['shipping_by_item']);
        $items[1]['line_total'] = 20000;
        $this->assertEquals(0, $service->calculate($items)['shipping']);
    }
}
