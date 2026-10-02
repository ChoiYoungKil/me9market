<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\PointTransaction;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Support\OrderItemStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPointService
{
    public function balances(int $userId, int $shopId): array
    {
        return [
            'channel' => (int) PointTransaction::where('user_id', $userId)->where('shop_channel_id', $shopId)->sum('points'),
            'me9' => (int) PointTransaction::where('user_id', $userId)->whereNull('shop_channel_id')->sum('points'),
        ];
    }

    public function allocateForCheckout(int $userId, int $shopId, int $channelPoints, int $me9Points, array $items, array $totals): array
    {
        if (min($channelPoints, $me9Points) < 0 || $channelPoints + $me9Points > floor($totals['total'])) {
            throw ValidationException::withMessages(['channel_points' => '사용 포인트는 결제금액을 초과할 수 없습니다.']);
        }
        if ($channelPoints + $me9Points > 0) {
            $user = User::whereKey($userId)->lockForUpdate()->first();
            if (! $user) {
                throw ValidationException::withMessages(['channel_points' => '포인트 사용은 회원 로그인 후 가능합니다.']);
            }
            $balances = $this->balances($userId, $shopId);
            if ($channelPoints > $balances['channel'] || $me9Points > $balances['me9']) {
                throw ValidationException::withMessages(['channel_points' => '사용 가능한 포인트가 부족합니다. 잔액을 다시 확인해 주세요.']);
            }
        }
        $capacities = collect($items)->mapWithKeys(fn ($item) => [$item['key'] => (int) floor($item['line_total'] + $totals['shipping_by_item'][$item['key']])])->all();
        $channel = $this->allocate($channelPoints, $capacities);
        $remaining = array_map(fn ($key) => $capacities[$key] - $channel[$key], array_keys($capacities));
        $me9 = $this->allocate($me9Points, array_combine(array_keys($capacities), $remaining));

        return collect($capacities)->mapWithKeys(fn ($amount, $key) => [$key => ['channel' => $channel[$key], 'me9' => $me9[$key]]])->all();
    }

    public function recordSpend(Order $order, OrdersProduct $item): void
    {
        foreach ($item->point_usage_snapshot ?? [] as $wallet => $points) {
            if ($points <= 0) {
                continue;
            }
            PointTransaction::create([
                'user_id' => $order->user_id,
                'shop_channel_id' => $wallet === 'channel' ? $item->shop_channel_id : null,
                'order_id' => $order->id,
                'order_product_id' => $item->id,
                'type' => 'use',
                'points' => -$points,
                'description' => $item->product_name.' 주문 포인트 사용',
                'reference_key' => 'spend:'.$item->id.':'.$wallet,
            ]);
        }
    }

    public function reverseItem(OrdersProduct $item): void
    {
        // The caller locks the order item before changing its terminal claim status.
        if (! in_array($item->normalized_status, [OrderItemStatus::CANCELLED, OrderItemStatus::RETURNED], true) || $item->financial_reversed_at) {
            return;
        }
        if ($item->is_exchange_replacement) {
            $original = OrdersProduct::whereKey($item->replacement_for_order_product_id)->lockForUpdate()->firstOrFail();
            if ($original->normalized_status !== OrderItemStatus::EXCHANGED || $original->financial_reversed_at) {
                throw ValidationException::withMessages(['status' => '원주문의 정산/환불 상태를 먼저 확인해 주세요.']);
            }
            $original->setStatus(OrderItemStatus::RETURNED);
            $original->return_shipping_fee = $item->return_shipping_fee;
            $original->save();
            $this->reverseItem($original);
            $item->forceFill([
                'financial_reversed_at' => $original->financial_reversed_at,
                'refund_status' => 'reversed_original',
                'refund_cash_amount' => 0,
            ])->save();

            return;
        }
        if ($item->user_id) {
            User::whereKey($item->user_id)->lockForUpdate()->first();
        }
        $spends = PointTransaction::where('order_product_id', $item->id)->where('user_id', $item->user_id)->where('type', 'use')->where('points', '<', 0)->get();
        foreach ($spends as $spend) {
            PointTransaction::firstOrCreate(['reference_key' => 'restore:'.$spend->id], [
                'user_id' => $spend->user_id,
                'shop_channel_id' => $spend->shop_channel_id,
                'order_id' => $spend->order_id,
                'order_product_id' => $item->id,
                'type' => 'refund',
                'points' => -(int) $spend->points,
                'description' => $item->product_name.' 취소/반품 포인트 복구',
            ]);
        }
        if (! $item->is_exchange_replacement && $item->stock_deducted_qty > 0) {
            ShopChannelProduct::whereKey($item->shop_channel_product_id)->whereNotNull('stock')->increment('stock', $item->stock_deducted_qty);
        }
        if ($item->stock_attribute_id && $item->attribute_stock_deducted_qty > 0) {
            \App\Models\ProductsAttribute::whereKey($item->stock_attribute_id)->increment('stock', $item->attribute_stock_deducted_qty);
        }
        $item->loadMissing('order');
        $knownAmounts = $item->point_usage_snapshot !== null && $item->shipping_amount_snapshot !== null;
        $paidCash = $knownAmounts ? max(0, round(($item->paid_line_total_snapshot ?? $item->line_total) + $item->shipping_amount_snapshot - $item->used_point_amount, 2)) : 0;
        $fee = $item->normalized_status === OrderItemStatus::RETURNED ? max(0, (float) $item->return_shipping_fee) : 0;
        $cash = max(0, $paidCash - $fee);
        $requiresReview = ! $knownAmounts || $fee > $paidCash || ($knownAmounts && -(int) $spends->sum('points') !== (int) $item->used_point_amount);
        $item->forceFill([
            'financial_reversed_at' => now(),
            'refund_cash_amount' => $cash,
            'refund_status' => $requiresReview ? 'review_required' : ($cash <= 0 ? 'not_required' : ($item->order?->payment_gateway === 'Me9 Mock Payment' ? 'mock_refunded' : 'pending_external')),
        ])->save();
    }

    public function convertClosedChannel(int $userId, ShopChannel $shop): void
    {
        DB::transaction(function () use ($userId, $shop) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            $shop = ShopChannel::whereKey($shop->id)->lockForUpdate()->firstOrFail();
            if ((int) $shop->status === 1 || $shop->closure_status !== 'approved') {
                throw ValidationException::withMessages(['shop_channel_id' => 'Shop 채널 운영중지 승인 완료 후 전환할 수 있습니다.']);
            }
            $points = $this->balances($userId, $shop->id)['channel'];
            if ($points <= 0) {
                throw ValidationException::withMessages(['shop_channel_id' => '전환 가능한 채널 포인트가 없습니다.']);
            }
            foreach (['convert_out' => -$points, 'convert_in' => $points] as $type => $amount) {
                PointTransaction::create([
                    'user_id' => $userId, 'shop_channel_id' => $amount < 0 ? $shop->id : null,
                    'type' => $type, 'points' => $amount,
                    'description' => $shop->channel_name.' 채널 포인트 Me9 포인트 전환',
                ]);
            }
        });
    }

    private function allocate(int $points, array $capacities): array
    {
        $total = array_sum($capacities);
        if ($points > $total) {
            throw ValidationException::withMessages(['channel_points' => '품목별 결제금액을 초과해 포인트를 사용할 수 없습니다.']);
        }
        $amounts = array_map(fn ($capacity) => $total > 0 ? (int) floor($points * $capacity / $total) : 0, $capacities);
        $remainder = $points - array_sum($amounts);
        foreach ($capacities as $key => $capacity) {
            if ($remainder > 0 && $amounts[$key] < $capacity) {
                $amounts[$key]++;
                $remainder--;
            }
        }

        return $amounts;
    }
}
