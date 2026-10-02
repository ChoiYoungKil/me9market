<?php

namespace App\Support;

class OrderClaimActions
{
    public static function definitions(): array
    {
        return [
            'return_release' => ['from' => [OrderItemStatus::RETURN_HOLD], 'to' => OrderItemStatus::RETURN_RECEIVED, 'claim_status' => 'received', 'label' => '반품 보류 해제'],
            'exchange_release_before' => ['from' => [OrderItemStatus::EXCHANGE_HOLD_BEFORE], 'to' => OrderItemStatus::EXCHANGE_REQUESTED, 'claim_status' => 'requested', 'label' => '교환 회수 전 보류 해제'],
            'exchange_release_after' => ['from' => [OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::EXCHANGE_RECEIVED, 'claim_status' => 'received', 'label' => '교환 회수 후 보류 해제'],
            'cancel_approve' => ['from' => [OrderItemStatus::CANCEL_REQUESTED], 'to' => OrderItemStatus::CANCELLED, 'claim_status' => 'completed', 'label' => '취소 승인'],
            'cancel_reject' => ['from' => [OrderItemStatus::CANCEL_REQUESTED], 'to' => OrderItemStatus::PAID, 'claim_status' => 'rejected', 'label' => '취소 거절'],
            'return_receive' => ['from' => [OrderItemStatus::RETURN_REQUESTED], 'to' => OrderItemStatus::RETURN_RECEIVED, 'claim_status' => 'received', 'label' => '반품 회수 완료'],
            'return_complete' => ['from' => [OrderItemStatus::RETURN_RECEIVED], 'to' => OrderItemStatus::RETURNED, 'claim_status' => 'completed', 'label' => '반품 확정'],
            'return_hold' => ['from' => [OrderItemStatus::RETURN_RECEIVED], 'to' => OrderItemStatus::RETURN_HOLD, 'claim_status' => 'held', 'label' => '반품 보류'],
            'return_withdraw' => ['from' => [OrderItemStatus::RETURN_REQUESTED], 'to' => OrderItemStatus::SHIPPING, 'claim_status' => 'withdrawn', 'label' => '반품 철회'],
            'return_invoice' => ['from' => [OrderItemStatus::RETURN_REQUESTED, OrderItemStatus::RETURN_RECEIVED, OrderItemStatus::RETURN_HOLD], 'to' => OrderItemStatus::RETURN_REQUESTED, 'claim_status' => 'requested', 'label' => '반품 송장 수정'],
            'exchange_approve' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED], 'to' => OrderItemStatus::EXCHANGE_APPROVED, 'claim_status' => 'approved', 'label' => '교환 승인'],
            'exchange_hold_before' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED], 'to' => OrderItemStatus::EXCHANGE_HOLD_BEFORE, 'claim_status' => 'held_before', 'label' => '교환 회수 전 보류'],
            'exchange_withdraw' => ['from' => [OrderItemStatus::EXCHANGE_HOLD_BEFORE, OrderItemStatus::EXCHANGE_RECEIVED], 'to' => OrderItemStatus::DELIVERED, 'claim_status' => 'withdrawn', 'label' => '교환 철회'],
            'exchange_receive' => ['from' => [OrderItemStatus::EXCHANGE_APPROVED], 'to' => OrderItemStatus::EXCHANGE_RECEIVED, 'claim_status' => 'received', 'label' => '교환 회수 완료'],
            'exchange_complete' => ['from' => [OrderItemStatus::EXCHANGE_RECEIVED], 'to' => OrderItemStatus::EXCHANGED, 'claim_status' => 'completed', 'label' => '교환 확정'],
            'exchange_hold_after' => ['from' => [OrderItemStatus::EXCHANGE_RECEIVED], 'to' => OrderItemStatus::EXCHANGE_HOLD_AFTER, 'claim_status' => 'held_after', 'label' => '교환 회수 후 보류'],
            'exchange_to_return' => ['from' => [OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::RETURNED, 'claim_status' => 'converted_to_return', 'label' => '반품 전환'],
            'exchange_option' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED, OrderItemStatus::EXCHANGE_APPROVED, OrderItemStatus::EXCHANGE_HOLD_BEFORE, OrderItemStatus::EXCHANGE_RECEIVED, OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::EXCHANGE_REQUESTED, 'claim_status' => 'requested', 'label' => '교환 옵션 변경'],
            'exchange_invoice' => ['from' => [OrderItemStatus::EXCHANGE_APPROVED, OrderItemStatus::EXCHANGE_RECEIVED, OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::EXCHANGE_APPROVED, 'claim_status' => 'approved', 'label' => '교환 송장 수정'],
        ];
    }

    public static function allowedStatuses(): array
    {
        return array_map(fn ($definition) => $definition['from'], self::definitions());
    }
}
