<?php

namespace App\Support;

class OrderItemStatus
{
    public const PAID = 'paid';

    public const READY_TO_SHIP = 'ready_to_ship';

    public const SHIPPING = 'shipping';

    public const DELIVERED = 'delivered';

    public const CONFIRMED = 'confirmed';

    public const CANCEL_REQUESTED = 'cancel_requested';

    public const CANCELLED = 'cancelled';

    public const RETURN_REQUESTED = 'return_requested';

    public const RETURN_RECEIVED = 'return_received';

    public const RETURN_HOLD = 'return_hold';

    public const RETURNED = 'returned';

    public const EXCHANGE_REQUESTED = 'exchange_requested';

    public const EXCHANGE_APPROVED = 'exchange_approved';

    public const EXCHANGE_HOLD_BEFORE = 'exchange_hold_before';

    public const EXCHANGE_RECEIVED = 'exchange_received';

    public const EXCHANGE_HOLD_AFTER = 'exchange_hold_after';

    public const EXCHANGED = 'exchanged';

    public static function label(?string $status): string
    {
        return self::labels()[$status] ?? ($status ?: '상태없음');
    }

    public static function labels(): array
    {
        return [
            self::PAID => '결제완료',
            self::READY_TO_SHIP => '배송대기',
            self::SHIPPING => '배송중',
            self::DELIVERED => '배송완료',
            self::CONFIRMED => '구매확정',
            self::CANCEL_REQUESTED => '취소요청',
            self::CANCELLED => '취소완료',
            self::RETURN_REQUESTED => '반품요청',
            self::RETURN_RECEIVED => '반품회수완료',
            self::RETURN_HOLD => '반품보류',
            self::RETURNED => '반품완료',
            self::EXCHANGE_REQUESTED => '교환요청',
            self::EXCHANGE_APPROVED => '교환승인',
            self::EXCHANGE_HOLD_BEFORE => '교환회수전보류',
            self::EXCHANGE_RECEIVED => '교환회수완료',
            self::EXCHANGE_HOLD_AFTER => '교환회수후보류',
            self::EXCHANGED => '교환완료',
        ];
    }

    public static function normalize(?string $status): string
    {
        $status = trim((string) $status);
        $map = [
            'New' => self::PAID,
            'Payment Captured' => self::PAID,
            '결제완료' => self::PAID,
            'In Process' => self::READY_TO_SHIP,
            '배송준비중' => self::READY_TO_SHIP,
            '배송대기' => self::READY_TO_SHIP,
            'Shipped' => self::SHIPPING,
            'shipping' => self::SHIPPING,
            '배송중' => self::SHIPPING,
            'Delivered' => self::DELIVERED,
            '배송완료' => self::DELIVERED,
            'Confirmed' => self::CONFIRMED,
            '구매확정' => self::CONFIRMED,
            'Cancel Requested' => self::CANCEL_REQUESTED,
            '취소요청' => self::CANCEL_REQUESTED,
            'Cancelled' => self::CANCELLED,
            '취소완료' => self::CANCELLED,
            'Return Requested' => self::RETURN_REQUESTED,
            '반품요청' => self::RETURN_REQUESTED,
            '반품회수완료' => self::RETURN_RECEIVED,
            '반품보류' => self::RETURN_HOLD,
            'Returned' => self::RETURNED,
            '반품완료' => self::RETURNED,
            'Exchange Requested' => self::EXCHANGE_REQUESTED,
            '교환요청' => self::EXCHANGE_REQUESTED,
            '교환승인' => self::EXCHANGE_APPROVED,
            '교환회수전보류' => self::EXCHANGE_HOLD_BEFORE,
            '교환회수완료' => self::EXCHANGE_RECEIVED,
            '교환회수후보류' => self::EXCHANGE_HOLD_AFTER,
            'Exchanged' => self::EXCHANGED,
            '교환완료' => self::EXCHANGED,
        ];

        return $map[$status] ?? ($status ?: self::PAID);
    }
}
