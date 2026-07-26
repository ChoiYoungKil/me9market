<?php

namespace App\Services;

use App\Models\ChannelPointTransaction;
use App\Models\OrdersProduct;
use App\Models\PointTransaction;
use App\Models\ShopChannel;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ChannelPointService
{
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_CUSTOMER_PAYBACK = 'customer_payback';
    public const TYPE_SMS = 'sms';
    public const TYPE_REFUND = 'refund';

    public function balanceForVendor(int $vendorId): int
    {
        if (!Schema::hasTable('channel_point_transactions')) {
            return 0;
        }

        return (int) ChannelPointTransaction::where('vendor_id', $vendorId)
            ->where('status', 'approved')
            ->sum('points');
    }

    public function summaryForVendor(int $vendorId): array
    {
        if (!Schema::hasTable('channel_point_transactions')) {
            return [
                'balance' => 0,
                'purchased' => 0,
                'customer_payback' => 0,
                'sms_used' => 0,
                'refunded' => 0,
                'pending_purchase' => 0,
                'pending_refund' => 0,
            ];
        }

        $approved = ChannelPointTransaction::where('vendor_id', $vendorId)->where('status', 'approved');
        $pending = ChannelPointTransaction::where('vendor_id', $vendorId)->where('status', 'pending');

        return [
            'balance' => (int) (clone $approved)->sum('points'),
            'purchased' => (int) (clone $approved)->where('type', self::TYPE_PURCHASE)->sum('points'),
            'customer_payback' => abs((int) (clone $approved)->where('type', self::TYPE_CUSTOMER_PAYBACK)->sum('points')),
            'sms_used' => abs((int) (clone $approved)->where('type', self::TYPE_SMS)->sum('points')),
            'refunded' => abs((int) (clone $approved)->where('type', self::TYPE_REFUND)->sum('points')),
            'pending_purchase' => (int) (clone $pending)->where('type', self::TYPE_PURCHASE)->sum('points'),
            'pending_refund' => abs((int) (clone $pending)->where('type', self::TYPE_REFUND)->sum('points')),
        ];
    }

    public function requestPurchase(int $vendorId, int $points, string $paymentMethod, ?string $memo = null, ?int $shopChannelId = null, ?int $adminId = null): ChannelPointTransaction
    {
        $this->assertPositivePointAmount($points);

        return ChannelPointTransaction::create([
            'vendor_id' => $vendorId,
            'shop_channel_id' => $shopChannelId,
            'admin_id' => $adminId,
            'type' => self::TYPE_PURCHASE,
            'status' => 'pending',
            'points' => $points,
            'payment_amount' => $points,
            'payment_method' => $paymentMethod,
            'memo' => $memo,
            'requested_at' => now(),
        ]);
    }

    public function requestRefund(int $vendorId, int $points, ?string $memo = null, ?int $shopChannelId = null, ?int $adminId = null): ChannelPointTransaction
    {
        $this->assertPositivePointAmount($points);

        return DB::transaction(function () use ($vendorId, $points, $memo, $shopChannelId, $adminId) {
            $this->lockVendorPoints($vendorId);

            if ($this->hasActiveChannel($vendorId)) {
                throw ValidationException::withMessages([
                    'points' => '포인트 환급은 판매자가 운영 중인 Shop 채널을 모두 종료한 경우에만 요청할 수 있습니다.',
                ]);
            }

            if ($this->availableBalanceForVendor($vendorId) < $points) {
                throw ValidationException::withMessages(['points' => '보유 포인트를 초과해 환급 요청할 수 없습니다.']);
            }

            return ChannelPointTransaction::create([
                'vendor_id' => $vendorId,
                'shop_channel_id' => $shopChannelId,
                'admin_id' => $adminId,
                'type' => self::TYPE_REFUND,
                'status' => 'pending',
                'points' => -$points,
                'payment_amount' => $points,
                'memo' => $memo,
                'requested_at' => now(),
            ]);
        });
    }

    public function approve(ChannelPointTransaction $transaction, int $adminId): ChannelPointTransaction
    {
        if ($transaction->status !== 'pending') {
            return $transaction;
        }

        DB::transaction(function () use ($transaction, $adminId) {
            $this->lockVendorPoints((int) $transaction->vendor_id);
            $transaction->refresh();

            if ($transaction->status !== 'pending') {
                return;
            }

            if ($transaction->type === self::TYPE_REFUND && $this->balanceForVendor((int) $transaction->vendor_id) < abs((int) $transaction->points)) {
                throw ValidationException::withMessages(['points' => '현재 보유 포인트가 부족해 환급 승인할 수 없습니다.']);
            }

            $transaction->forceFill([
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
            ])->save();
        });

        return $transaction;
    }

    public function reject(ChannelPointTransaction $transaction, int $adminId, ?string $memo = null): ChannelPointTransaction
    {
        if ($transaction->status !== 'pending') {
            return $transaction;
        }

        $transaction->forceFill([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'memo' => $memo ?: $transaction->memo,
            'rejected_at' => now(),
        ])->save();

        return $transaction;
    }

    public function recordCustomerPayback(OrdersProduct $item): ?PointTransaction
    {
        if (!$item->user_id || !$item->vendor_id || !Schema::hasTable('channel_point_transactions')) {
            return null;
        }

        $item->loadMissing(['product', 'order']);
        $points = max(0, (int) ($item->product?->reward_points ?? 0)) * max(1, (int) $item->product_qty);

        if ($points <= 0) {
            return null;
        }

        return DB::transaction(function () use ($item, $points) {
            $this->lockVendorPoints((int) $item->vendor_id);

            $exists = ChannelPointTransaction::where('type', self::TYPE_CUSTOMER_PAYBACK)
                ->where('reference_type', 'orders_product')
                ->where('reference_id', $item->id)
                ->lockForUpdate()
                ->exists();

            if ($exists || $this->balanceForVendor((int) $item->vendor_id) < $points) {
                return null;
            }

            ChannelPointTransaction::create([
                'vendor_id' => $item->vendor_id,
                'shop_channel_id' => $item->shop_channel_id,
                'user_id' => $item->user_id,
                'order_id' => $item->order_id,
                'order_product_id' => $item->id,
                'type' => self::TYPE_CUSTOMER_PAYBACK,
                'status' => 'approved',
                'points' => -$points,
                'memo' => $item->product_name . ' 구매확정 포인트 페이백',
                'reference_type' => 'orders_product',
                'reference_id' => $item->id,
                'requested_at' => now(),
                'approved_at' => now(),
            ]);

            return PointTransaction::firstOrCreate(
                [
                    'user_id' => $item->user_id,
                    'order_product_id' => $item->id,
                    'type' => 'earn',
                ],
                [
                    'shop_channel_id' => $item->shop_channel_id,
                    'order_id' => $item->order_id,
                    'points' => $points,
                    'description' => $item->product_name . ' 구매확정 포인트 페이백',
                ]
            );
        });
    }

    public function recordSmsDebit(int $vendorId, int $messageCount = 1, int $pointPerMessage = 20, ?int $shopChannelId = null, ?string $memo = null): ?ChannelPointTransaction
    {
        $points = max(1, $messageCount) * max(1, $pointPerMessage);

        return DB::transaction(function () use ($vendorId, $shopChannelId, $memo, $points) {
            $this->lockVendorPoints($vendorId);

            if ($this->balanceForVendor($vendorId) < $points) {
                return null;
            }

            return ChannelPointTransaction::create([
                'vendor_id' => $vendorId,
                'shop_channel_id' => $shopChannelId,
                'type' => self::TYPE_SMS,
                'status' => 'approved',
                'points' => -$points,
                'memo' => $memo ?: '문자 발송 포인트 차감',
                'requested_at' => now(),
                'approved_at' => now(),
            ]);
        });
    }

    public function hasActiveChannel(int $vendorId): bool
    {
        if (!Schema::hasTable('shop_channels')) {
            return false;
        }

        return ShopChannel::where('vendor_id', $vendorId)
            ->where('status', 1)
            ->exists();
    }

    private function assertPositivePointAmount(int $points): void
    {
        if ($points < 1000 || $points % 1000 !== 0) {
            throw ValidationException::withMessages(['points' => '포인트는 1,000P 이상, 1,000P 단위로 입력해 주세요.']);
        }
    }

    private function availableBalanceForVendor(int $vendorId): int
    {
        $approvedBalance = $this->balanceForVendor($vendorId);
        $pendingRefunds = abs((int) ChannelPointTransaction::where('vendor_id', $vendorId)
            ->where('type', self::TYPE_REFUND)
            ->where('status', 'pending')
            ->sum('points'));

        return $approvedBalance - $pendingRefunds;
    }

    private function lockVendorPoints(int $vendorId): void
    {
        Vendor::whereKey($vendorId)->lockForUpdate()->first();
    }
}
