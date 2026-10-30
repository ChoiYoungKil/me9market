<?php

namespace App\Services;

use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SettlementReport
{
    public const HEADERS = ['등록일', '채널아이디', '주문번호', '주문유형', '상품유형', '구분', 'PG 결제', '포인트 결제', '상품가', '배송비', '할인금액', '상품+수수료', '매입 배송비', 'SMS 건수', 'SMS 비용', '제공포인트', '정산비용'];

    public const SHIPPING_HEADERS = ['완료일시', '반품/교환 주문번호', '주문번호', '주문상태', '주문유형', '상품유형', 'PG 결제', '포인트 결제', '추가 배송비'];

    public function rows(Collection $rows): Collection
    {
        $items = OrdersProduct::with(['order', 'shopChannel', 'shopChannelProduct', 'product'])->whereIn('id', $rows->pluck('order_product_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($items) {
            $item = $items[$row['order_product_id']];
            $payment = app(SettlementCalculator::class)->paymentAmounts($item);
            $policy = app(OrderSettlementPolicy::class)->forItem($item);
            $reseller = str_ends_with($row['settlement_role'], 'reseller');
            $purchaseShipping = $row['settlement_role'] === 'shared_free_reseller' ? $payment['shipping'] : 0;
            $collects = in_array($row['settlement_role'], ['seller', 'shared_fixed_supplier', 'shared_free_reseller'], true);
            $jointDiscount = $item->joint_purchase_id ? max(0, (float) $item->original_line_total - $payment['paid_line_total']) : 0;
            $productAmount = $row['settlement_role'] === 'shared_fixed_reseller' ? $row['invoice_sales_amount'] : ($row['settlement_role'] === 'shared_free_supplier' ? $row['supply_amount'] : $payment['paid_line_total']);

            return [
                $row['confirmed_at'] instanceof \DateTimeInterface ? $row['confirmed_at']->format('Y-m-d H:i:s') : (string) $row['confirmed_at'],
                $item->shopChannel?->channel_code ?: '-', $row['order_no'], $item->joint_purchase_id ? '공동구매' : '일반',
                ['own' => '자사', 'public' => '공유', 'partial' => '제휴'][$policy['product_type']] ?? '자사', $reseller ? '위탁판매' : '상품판매',
                $collects ? $payment['cash'] : 0, $collects ? $payment['points'] : 0, $productAmount + $jointDiscount, $row['settlement_role'] === 'shared_fixed_reseller' ? 0 : $payment['shipping'], $payment['coupon'] + $jointDiscount,
                $row['invoice_purchase_amount'] - $purchaseShipping, $purchaseShipping,
                $reseller || $row['settlement_role'] === 'seller' ? (int) $item->sms_count : 0,
                $row['sms_postpaid_amount'], $row['point_deposit_amount'], $row['payout_amount'],
            ];
        });
    }

    public function amountRows(Collection $rows, string $kind): array
    {
        $details = $this->rows($rows);
        $items = OrdersProduct::with(['shopChannel', 'order', 'product', 'shopChannelProduct'])->whereIn('id', $rows->pluck('order_product_id'))->get()->keyBy('id');
        $values = $rows->values()->map(function ($row, $index) use ($items, $details, $kind) {
            $item = $items[$row['order_product_id']];
            $line = $details[$index];
            $own = $row['payment_gateway_type'] === 'own_pg';
            $points = $item->point_usage_snapshot;
            $channel = (int) ($points['channel'] ?? 0);
            $me9 = (int) ($points['me9'] ?? $row['point_used_amount']);
            if (in_array($row['settlement_role'], ['shared_fixed_reseller', 'shared_free_supplier'], true)) {
                $channel = $me9 = 0;
            }
            if ($kind === 'billing') {
                return [$line[0], $line[1], $line[2], $line[11], $line[12], $row['sms_postpaid_amount'], $row['point_deposit_amount'], $row['invoice_purchase_amount'] + $row['sms_postpaid_amount'] + $row['point_deposit_amount']];
            }

            return [$line[0], $line[1], $line[2], $own ? $line[6] : 0, $own ? 0 : $line[6], $channel, $me9, $line[8], $line[9], $line[10], $own ? -$row['point_used_amount'] : $row['invoice_sales_amount']];
        });

        return [$kind === 'billing' ? ['등록일', '채널아이디', '주문번호', '상품+수수료', '배송비', 'SMS 수수료', '지급포인트', '채널 청구액'] : ['등록일', '채널아이디', '주문번호', '자사 PG', '공용 PG', '자사포인트', 'Me9 포인트', '상품가', '배송비', '할인금액', '매출액'], $values];
    }

    public function shipping(string $period, int $vendorId, ?int $shopId = null): Collection
    {
        return OrderClaim::with(['product.order', 'product.shopChannelProduct', 'product.product', 'product.shopChannel'])
            ->where('vendor_id', $vendorId)->whereIn('type', ['return', 'exchange'])->where('status', 'completed')
            ->when($shopId !== null, fn ($q) => $q->whereHas('product', fn ($item) => $item->where('shop_channel_id', $shopId)))
            ->get()->filter(function ($claim) use ($period) {
                $date = $claim->lifecycle_dates['completed'] ?? $claim->updated_at?->format('Y-m-d H:i:s');

                return $date && substr($date, 0, 7) === $period && $claim->product && app(OrderSettlementPolicy::class)->forItem($claim->product)['payment_gateway_type'] === 'me9_pg';
            })->map(function ($claim) {
                $item = $claim->product;
                $amount = $claim->type === 'return' ? (int) $item->return_shipping_fee : (int) $item->exchange_shipping_fee;
                $payment = app(SettlementCalculator::class)->paymentAmounts($item);

                return [$claim->lifecycle_dates['completed'], 'Me9-'.($claim->type === 'return' ? 'Return' : 'Exchange').'-'.str_pad($claim->id, 8, '0', STR_PAD_LEFT),
                    'Me9-'.str_pad($item->order_id, 8, '0', STR_PAD_LEFT), $claim->type === 'return' ? '반품' : '교환', $item->joint_purchase_id ? '공동구매' : '일반',
                    ['own' => '자사', 'public' => '공유', 'partial' => '제휴'][app(OrderSettlementPolicy::class)->forItem($item)['product_type']] ?? '자사',
                    $payment['cash'], $payment['points'], $amount];
            })->filter(fn ($row) => $row[8] > 0)->values();
    }

    public function download(string $filename, array $headers, Collection $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $book = new Spreadsheet;
            $sheet = $book->getActiveSheet();
            $sheet->setTitle('정산 내역');
            foreach (array_merge([$headers], $rows->all()) as $r => $values) {
                foreach (array_values($values) as $c => $value) {
                    $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($c + 1).($r + 1), $value ?? '', is_int($value) || is_float($value) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
                }
            }
            $last = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->freezePane('D2');
            $sheet->setAutoFilter('A1:'.$last.max(1, $rows->count() + 1));
            $sheet->getStyle('A1:'.$last.'1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A1:'.$last.'1')->getFill()->setFillType('solid')->getStartColor()->setRGB('51489C');
            foreach (range(1, count($headers)) as $c) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth($c <= 3 ? 25 : 18);
            }
            $sheet->getStyle('A1:'.$last.($rows->count() + 1))->getFont()->setName('맑은 고딕')->setSize(11);
            $sheet->getStyle('A2:'.$last.max(2, $rows->count() + 1))->getNumberFormat()->setFormatCode('#,##0;[Red](#,##0)');
            (new Xlsx($book))->save('php://output');
            $book->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
