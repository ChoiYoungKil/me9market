<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SettlementExecution;
use App\Models\SettlementRun;
use App\Services\SettlementCalculator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SettlementController extends Controller
{
    public function index(Request $request, SettlementCalculator $calculator)
    {
        Session::put('page', 'settlements');

        $period = $calculator->normalizePeriod($request->query('period'));
        $periodOptions = $calculator->periodOptions();
        $previewRows = $calculator->preview($period);
        $runs = SettlementRun::where('period', $period)
            ->orderBy('vendor_name')
            ->orderBy('shop_channel_name')
            ->get()
            ->keyBy('settlement_key');

        $rows = $previewRows->map(function (array $row) use ($runs) {
            $run = $runs->get($row['settlement_key']);
            $row['run'] = $run;
            $row['status'] = $run?->status ?? 'preview';
            $row['run_id'] = $run?->id;

            return $row;
        });

        $previewKeys = $previewRows->pluck('settlement_key')->all();
        $runs->except($previewKeys)->each(function (SettlementRun $run) use (&$rows) {
            $rows->push([
                'settlement_key' => $run->settlement_key,
                'settlement_role' => $run->settlement_role ?? 'seller',
                'payment_gateway_type' => $run->payment_gateway_type ?? 'me9_pg',
                'period' => $run->period,
                'vendor_id' => $run->vendor_id,
                'shop_channel_id' => $run->shop_channel_id,
                'vendor_name' => $run->vendor_name,
                'shop_channel_name' => $run->shop_channel_name,
                'settlement_type' => $run->settlement_type,
                'settlement_rate' => $run->settlement_rate,
                'order_count' => $run->order_count,
                'item_count' => $run->item_count,
                'quantity' => $run->quantity,
                'gross_sales_amount' => $run->gross_sales_amount,
                'supply_amount' => $run->supply_amount,
                'sales_profit_amount' => $run->sales_profit_amount,
                'invoice_sales_amount' => $run->invoice_sales_amount,
                'invoice_purchase_amount' => $run->invoice_purchase_amount,
                'point_deposit_amount' => $run->point_deposit_amount,
                'point_used_amount' => $run->point_used_amount,
                'payout_amount' => $run->payout_amount,
                'settlement_amount' => $run->settlement_amount,
                'admin_amount' => $run->admin_amount,
                'status' => $run->status,
                'run' => $run,
                'run_id' => $run->id,
            ]);
        });

        $totals = [
            'order_count' => $rows->sum('order_count'),
            'quantity' => $rows->sum('quantity'),
            'gross_sales_amount' => $rows->sum('gross_sales_amount'),
            'supply_amount' => $rows->sum('supply_amount'),
            'sales_profit_amount' => $rows->sum('sales_profit_amount'),
            'invoice_sales_amount' => $rows->sum('invoice_sales_amount'),
            'invoice_purchase_amount' => $rows->sum('invoice_purchase_amount'),
            'point_deposit_amount' => $rows->sum('point_deposit_amount'),
            'point_used_amount' => $rows->sum('point_used_amount'),
            'payout_amount' => $rows->sum('payout_amount'),
            'settlement_amount' => $rows->sum('settlement_amount'),
            'admin_amount' => $rows->sum('admin_amount'),
        ];

        $executions = SettlementExecution::with(['vendor', 'shopChannel', 'settlementRun'])
            ->where('period', $period)
            ->latest('executed_at')
            ->latest('id')
            ->get();

        return view('admin.settlements.index', compact('period', 'periodOptions', 'rows', 'totals', 'executions'));
    }

    public function generate(Request $request, SettlementCalculator $calculator)
    {
        $period = $calculator->normalizePeriod($request->input('period'));
        $runs = $calculator->generate($period, Auth::guard('admin')->id());

        return redirect()
            ->route('admin.settlements.index', ['period' => $period])
            ->with('success_message', $period . ' 정산 자료 ' . $runs->count() . '건을 생성했습니다.');
    }

    public function show($id)
    {
        Session::put('page', 'settlements');

        $settlement = SettlementRun::with('items')->findOrFail($id);
        $items = $settlement->items()->orderBy('confirmed_at')->paginate(30);
        $detailTotals = $this->totalsFromRows($settlement->items);
        $isBalanced = $this->totalsMatch($settlement, $detailTotals);
        $isPreview = false;

        return view('admin.settlements.show', compact('settlement', 'items', 'detailTotals', 'isBalanced', 'isPreview'));
    }

    public function preview(Request $request, SettlementCalculator $calculator)
    {
        Session::put('page', 'settlements');

        $period = $calculator->normalizePeriod($request->query('period'));
        $vendorId = (int) $request->query('vendor_id');

        if (!$vendorId) {
            abort(404);
        }

        $shopChannelId = $this->normalizeShopChannelId($request->query('shop_channel_id'));
        $summary = $calculator->preview($period, $vendorId, $shopChannelId)->first();

        if (!$summary) {
            abort(404);
        }

        $rows = $calculator->items($period, $vendorId, $shopChannelId)
            ->map(fn (array $row) => (object) $row)
            ->values();
        $items = $this->paginateRows($rows, $request, route('admin.settlements.preview'));
        $settlement = (object) array_merge($summary, [
            'id' => null,
            'status' => 'preview',
            'executed_at' => null,
            'executed_by' => null,
        ]);
        $detailTotals = $this->totalsFromRows($rows);
        $isBalanced = $this->totalsMatch($settlement, $detailTotals);
        $isPreview = true;

        return view('admin.settlements.show', compact('settlement', 'items', 'detailTotals', 'isBalanced', 'isPreview'));
    }

    public function complete($id)
    {
        $settlement = SettlementRun::with('items')->findOrFail($id);

        return redirect()
            ->route('admin.settlements.show', $settlement->id)
            ->with('success_message', '정산 리스트는 구매확정 주문 기준으로 생성되며 완료 상태 변경은 사용하지 않습니다. 실제 집행 여부는 정산 집행 등록에서 관리해 주세요.');
    }

    public function storeExecution(Request $request)
    {
        $data = $request->validate([
            'period' => 'required|string|size:7',
            'settlement_run_id' => 'nullable|integer|exists:settlement_runs,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'executed_at' => 'nullable|date',
            'memo' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240|mimes:xls,xlsx,csv,pdf',
        ]);

        $run = !empty($data['settlement_run_id'])
            ? SettlementRun::find($data['settlement_run_id'])
            : null;

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $attachmentPath = $attachment->store('settlement_executions', 'public');
            $attachmentName = $attachment->getClientOriginalName();
        }

        SettlementExecution::create([
            'period' => $data['period'],
            'settlement_run_id' => $run?->id,
            'vendor_id' => $run?->vendor_id,
            'shop_channel_id' => $run?->shop_channel_id,
            'title' => $data['title'],
            'amount' => $data['amount'],
            'executed_at' => $data['executed_at'] ?? now(),
            'registered_by' => Auth::guard('admin')->id(),
            'memo' => $data['memo'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        return redirect()
            ->route('admin.settlements.index', ['period' => $data['period']])
            ->with('success_message', '정산 집행 내역을 등록했습니다.');
    }

    public function downloadExecutionAttachment($id)
    {
        $execution = SettlementExecution::findOrFail($id);

        if (!$execution->attachment_path || !Storage::disk('public')->exists($execution->attachment_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($execution->attachment_path, $execution->attachment_name ?: basename($execution->attachment_path));
    }

    public function payoutDetail($id)
    {
        return $this->amountDetail($id, 'payout');
    }

    public function billingDetail($id)
    {
        return $this->amountDetail($id, 'billing');
    }

    public function payoutExport($id)
    {
        $settlement = SettlementRun::with('items.orderItem')->findOrFail($id);

        return $this->downloadCsv(
            'settlement_payout_' . $settlement->period . '_' . $settlement->id . '.csv',
            ['등록일', '채널아이디', '주문번호', 'PG구분', '자사PG 결제액', '공용PG 결제액', '자사포인트', 'Me9포인트', '상품가', '배송비', '할인금액', '채널 지급액', '지급사유'],
            $this->payoutRows($settlement)
        );
    }

    public function billingExport($id)
    {
        $settlement = SettlementRun::with('items.orderItem')->findOrFail($id);

        return $this->downloadCsv(
            'settlement_billing_' . $settlement->period . '_' . $settlement->id . '.csv',
            ['등록일', '채널아이디', '주문번호', 'PG구분', '상품+수수료', '배송비', 'SMS수수료', '지급포인트', '채널청구액', '청구사유'],
            $this->billingRows($settlement)
        );
    }

    public function export($id)
    {
        $settlement = SettlementRun::with('items')->findOrFail($id);

        return $this->downloadCsv(
            'settlement_' . $settlement->period . '_' . $settlement->id . '.csv',
            [
                '주문번호',
                '상품코드',
                '상품명',
                '수량',
                'PG구분',
                '매출',
                '매입',
                '판매이익',
                '포인트예수금',
                '포인트사용',
                '지급액',
                '정산방식',
                '정산율',
                'Me9수수료',
                '구매확정일',
                '상태',
            ],
            $settlement->items->map(fn ($item) => [
                $item->order_no,
                $item->product_code,
                $item->product_name,
                $item->quantity,
                $this->paymentGatewayLabel($item->payment_gateway_type ?? 'me9_pg'),
                $item->gross_sales_amount,
                $item->supply_amount,
                $item->sales_profit_amount,
                $item->point_deposit_amount,
                $item->point_used_amount,
                $item->payout_amount,
                $this->settlementTypeLabel($item->settlement_type),
                $item->settlement_rate,
                $item->admin_amount,
                optional($item->confirmed_at)->format('Y-m-d H:i'),
                $item->status,
            ])
        );
    }

    public function exportExtraShipping($id)
    {
        $settlement = SettlementRun::with('items.orderItem')->findOrFail($id);

        $rows = $settlement->items->map(function ($item) {
            $orderItem = $item->orderItem;
            $extraShipping = (int) data_get($orderItem, 'return_shipping_fee', 0)
                + (int) data_get($orderItem, 'exchange_shipping_fee', 0)
                + (int) data_get($orderItem, 'extra_shipping_fee', 0);

            return [
                $item->order_no,
                $item->product_name,
                $item->status,
                $extraShipping,
                data_get($orderItem, 'courier_name', ''),
                data_get($orderItem, 'tracking_number', ''),
                optional($item->confirmed_at)->format('Y-m-d H:i'),
            ];
        });

        return $this->downloadCsv(
            'settlement_extra_shipping_' . $settlement->period . '_' . $settlement->id . '.csv',
            ['주문번호', '상품명', '상태', '추가배송비', '택배사', '송장번호', '처리일'],
            $rows
        );
    }

    private function normalizeShopChannelId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function paginateRows($rows, Request $request, string $path): LengthAwarePaginator
    {
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 30;
        $query = $request->query();
        unset($query['page']);

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $path, 'query' => $query]
        );
    }

    private function totalsFromRows($rows): array
    {
        return [
            'order_count' => $rows->pluck('order_id')->filter()->unique()->count(),
            'item_count' => $rows->count(),
            'quantity' => (int) $rows->sum('quantity'),
            'gross_sales_amount' => round((float) $rows->sum('gross_sales_amount'), 2),
            'supply_amount' => round((float) $rows->sum('supply_amount'), 2),
            'sales_profit_amount' => round((float) $rows->sum('sales_profit_amount'), 2),
            'invoice_sales_amount' => round((float) $rows->sum('invoice_sales_amount'), 2),
            'invoice_purchase_amount' => round((float) $rows->sum('invoice_purchase_amount'), 2),
            'point_deposit_amount' => round((float) $rows->sum('point_deposit_amount'), 2),
            'point_used_amount' => round((float) $rows->sum('point_used_amount'), 2),
            'payout_amount' => round((float) $rows->sum('payout_amount'), 2),
            'settlement_amount' => round((float) $rows->sum('settlement_amount'), 2),
            'admin_amount' => round((float) $rows->sum('admin_amount'), 2),
        ];
    }

    private function totalsMatch($summary, array $totals): bool
    {
        foreach (['order_count', 'item_count', 'quantity'] as $key) {
            if ((int) data_get($summary, $key) !== (int) $totals[$key]) {
                return false;
            }
        }

        foreach (['gross_sales_amount', 'supply_amount', 'sales_profit_amount', 'invoice_sales_amount', 'invoice_purchase_amount', 'point_deposit_amount', 'point_used_amount', 'payout_amount', 'settlement_amount', 'admin_amount'] as $key) {
            if (abs(round((float) data_get($summary, $key), 2) - round((float) $totals[$key], 2)) > 0.01) {
                return false;
            }
        }

        return true;
    }

    private function settlementTypeLabel($type): string
    {
        return (string) $type === '2' ? '매출이익' : '공급가';
    }

    private function amountDetail($id, string $mode)
    {
        $settlement = SettlementRun::with('items.orderItem')->findOrFail($id);
        $rows = $mode === 'billing' ? $this->billingRows($settlement) : $this->payoutRows($settlement);
        $title = $mode === 'billing' ? '채널 청구액 상세 목록' : '채널 지급액 상세 목록';
        $exportRoute = $mode === 'billing'
            ? route('admin.settlements.billing.export', $settlement->id)
            : route('admin.settlements.payout.export', $settlement->id);

        return view('admin.settlements.amount_detail', compact('settlement', 'rows', 'title', 'mode', 'exportRoute'));
    }

    private function payoutRows(SettlementRun $settlement)
    {
        return $settlement->items->map(function ($item) {
            $orderItem = $item->orderItem;
            $usesOwnPg = ($item->payment_gateway_type ?? null) === 'own_pg'
                || (bool) data_get($orderItem, 'shopChannel.use_own_pg', false);
            $pgPayment = max(0, (float) $item->invoice_sales_amount - (float) $item->point_used_amount);
            $payoutAmount = $usesOwnPg ? 0 : (float) $item->payout_amount;

            return [
                optional($item->confirmed_at)->format('Y-m-d H:i'),
                $item->shop_channel_id ?: '-',
                $item->order_no,
                $this->paymentGatewayLabel($usesOwnPg ? 'own_pg' : 'me9_pg'),
                $usesOwnPg ? $pgPayment : 0,
                $usesOwnPg ? 0 : $pgPayment,
                0,
                (float) $item->point_used_amount,
                (float) $item->gross_sales_amount,
                max(0, (float) $item->invoice_sales_amount - (float) $item->gross_sales_amount),
                0,
                $payoutAmount,
                $usesOwnPg ? '자사PG 결제건은 Me9 지급 대상 아님' : '공용PG 수납 기준 지급',
            ];
        });
    }

    private function billingRows(SettlementRun $settlement)
    {
        return $settlement->items->map(function ($item) {
            $smsFee = (float) data_get($item->orderItem, 'sms_fee', 0);
            $billingAmount = (float) $item->invoice_purchase_amount + $smsFee + (float) $item->point_deposit_amount;
            $usesOwnPg = ($item->payment_gateway_type ?? null) === 'own_pg'
                || (bool) data_get($item->orderItem, 'shopChannel.use_own_pg', false);

            return [
                optional($item->confirmed_at)->format('Y-m-d H:i'),
                $item->shop_channel_id ?: '-',
                $item->order_no,
                $this->paymentGatewayLabel($usesOwnPg ? 'own_pg' : 'me9_pg'),
                (float) $item->invoice_purchase_amount,
                0,
                $smsFee,
                (float) $item->point_deposit_amount,
                $billingAmount,
                $usesOwnPg ? '자사PG 결제건 수수료/부가비용 청구' : '공용PG 정산 차감/청구',
            ];
        });
    }

    private function paymentGatewayLabel(?string $type): string
    {
        return $type === 'own_pg' ? '자사PG' : '공용PG';
    }

    private function downloadCsv(string $filename, array $headers, $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
