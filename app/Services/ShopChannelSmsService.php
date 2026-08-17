<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\ShopChannel;
use App\Models\ShopChannelSmsLog;
use App\Models\Sms;

class ShopChannelSmsService
{
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_PURCHASE_CONFIRMED = 'purchase_confirmed';
    public const TYPE_CANCEL = 'cancel';
    public const TYPE_RETURN = 'return';

    private const BILLING_AMOUNT = 100;

    public function send(ShopChannel $shop, Order $order, ?OrdersProduct $item, string $type): ?ShopChannelSmsLog
    {
        if (!$shop->use_purchase_sms) {
            return null;
        }

        $templates = is_array($shop->purchase_sms_templates) ? $shop->purchase_sms_templates : [];
        $message = trim((string) ($templates[$type] ?? ''));
        $phone = trim((string) $order->mobile);
        if ($message === '' || $phone === '') {
            return null;
        }

        $message = strtr($message, $this->replacements($shop, $order, $item));
        $log = ShopChannelSmsLog::create([
            'shop_channel_id' => $shop->id,
            'vendor_id' => $shop->vendor_id,
            'order_id' => $order->id,
            'order_product_id' => $item?->id,
            'template_type' => $type,
            'recipient_phone' => $phone,
            'message' => $message,
            'billing_amount' => self::BILLING_AMOUNT,
            'status' => 'pending',
        ]);

        $response = Sms::sendSms($message, $phone, (int) $shop->vendor_id, (int) $shop->id, self::BILLING_AMOUNT, false);
        $log->forceFill([
            'status' => $response === false ? 'failed' : 'sent',
            'provider_response' => is_scalar($response) ? (string) $response : json_encode($response, JSON_UNESCAPED_UNICODE),
            'sent_at' => $response === false ? null : now(),
        ])->save();

        $this->applyBillingToOrderItem($item, self::BILLING_AMOUNT);

        return $log;
    }

    private function replacements(ShopChannel $shop, Order $order, ?OrdersProduct $item): array
    {
        $productName = $item?->product_name ?: ($order->orders_products->first()->product_name ?? '');
        $quantity = $item ? (int) $item->product_qty : (int) $order->orders_products->sum('product_qty');

        return [
            '{{구매자}}' => $order->name,
            '{{구매상품}}' => $productName,
            '{{구매수량}}' => (string) max(1, $quantity),
            '{{구매액}}' => number_format((float) $order->grand_total),
            '{{구매날짜}}' => optional($order->created_at)->format('Y-m-d') ?: now()->format('Y-m-d'),
            '{채널명}' => $shop->channel_name,
            '{주문자명}' => $order->name,
            '{주문번호}' => 'Me9-' . sprintf('%08d', $order->id),
            '{주문금액}' => number_format((float) $order->grand_total),
        ];
    }

    private function applyBillingToOrderItem(?OrdersProduct $item, int $amount): void
    {
        if (!$item) {
            return;
        }

        $item->sms_count = (int) $item->sms_count + 1;
        $item->sms_fee = (int) $item->sms_fee + $amount;
        $item->save();
    }
}
