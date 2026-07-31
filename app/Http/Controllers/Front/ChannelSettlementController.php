<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SettlementRun;
use App\Models\SettlementExecution;
use App\Services\SettlementCalculator;
use App\Support\OrderItemStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ChannelSettlementController extends Controller
{
    public function index(Request $request, SettlementCalculator $calculator)
    {
        $admin = Auth::guard('admin')->user();
        $vendor_id = $admin->vendor_id;
        $period = $request->query('period')
            ? $calculator->normalizePeriod($request->query('period'))
            : $this->latestSettlementPeriod($vendor_id, $calculator);
        $periodOptions = $calculator->periodOptions();
        $runs = SettlementRun::where('period', $period)
            ->where('vendor_id', $vendor_id)
            ->get()
            ->keyBy('settlement_key');

        $rows = $calculator->preview($period, $vendor_id)
            ->map(function (array $row) use ($runs) {
                $run = $runs->get($row['settlement_key']);
                $runStatus = $run?->status ?? 'preview';
                $row['run_status'] = $runStatus;
                $row['status'] = $this->statusLabel($runStatus);
                $row['settlement_period'] = $row['period'];
                $row['total_sales'] = $row['gross_sales_amount'];
                $row['rate'] = $row['settlement_rate'];
                $row['commission'] = $this->legacyCommissionAmount($row);

                return (object) $row;
            })
            ->values();
        $rate = optional($rows->first())->rate ?? 0;
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

        $settlements = $this->paginateRows($rows, $request, 20, route('channel.settlement.list'));
        $executions = SettlementExecution::with(['shopChannel'])
            ->where('period', $period)
            ->where('vendor_id', $vendor_id)
            ->latest('executed_at')
            ->latest('id')
            ->get();

        return view('channel.sub05.settlement_list', [
            'settlements' => $settlements,
            'executions' => $executions,
            'dep1_id' => '05',
            'period' => $period,
            'periodOptions' => $periodOptions,
            'totals' => $totals,
            'rate' => $rate,
        ]);
    }

    public function downloadExecutionAttachment($id)
    {
        $admin = Auth::guard('admin')->user();
        $execution = SettlementExecution::where('vendor_id', $admin->vendor_id)->findOrFail($id);

        if (!$execution->attachment_path || !Storage::disk('public')->exists($execution->attachment_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($execution->attachment_path, $execution->attachment_name ?: basename($execution->attachment_path));
    }

    public function view(Request $request, $period, SettlementCalculator $calculator)
    {
        $admin = Auth::guard('admin')->user();
        $vendor_id = $admin->vendor_id;
        $period = $calculator->normalizePeriod($period);
        $shopChannelId = $this->normalizeShopChannelId($request->query('shop_channel_id'));

        $summary = $calculator->preview($period, $vendor_id, $shopChannelId)->first();
        $rowData = $calculator->items($period, $vendor_id, $shopChannelId)->values();
        $rows = $this->ordersFromSettlementRows($rowData);

        if (!$summary && $shopChannelId !== null) {
            abort(404);
        }

        $orders = $this->paginateRows($rows, $request, 30, route('channel.settlement.view', ['period' => $period]));

        $totals = [
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

        return view('channel.sub05.settlement_view', [
            'orders' => $orders,
            'period' => $period,
            'shopChannelId' => $shopChannelId,
            'summary' => $summary ? (object) $summary : null,
            'totals' => $totals,
            'dep1_id' => '05',
            'rate' => $summary['settlement_rate'] ?? optional($rows->first())->rate ?? 0,
        ]);
    }

    private function ordersFromSettlementRows($rowData)
    {
        if ($rowData->isEmpty()) {
            return collect();
        }

        $items = \App\Models\OrdersProduct::with(['product', 'order'])
            ->whereIn('id', $rowData->pluck('order_product_id')->all())
            ->get()
            ->keyBy('id');

        return $rowData
            ->map(function (array $row) use ($items) {
                $item = $items->get($row['order_product_id']);
                if (!$item) {
                    return (object) $row;
                }

                foreach ($row as $key => $value) {
                    $item->setAttribute($key, $value);
                }
                $item->setAttribute('rate', $row['settlement_rate'] ?? 0);
                $item->setAttribute('total_sales', $row['gross_sales_amount'] ?? 0);
                $item->setAttribute('commission', $this->legacyCommissionAmount($row));

                return $item;
            })
            ->values();
    }

    private function statusLabel(string $status): string
    {
        return [
            'completed' => '정산자료 생성',
            'pending' => '정산자료 생성',
            'preview' => '구매확정 기준',
        ][$status] ?? $status;
    }

    private function legacyCommissionAmount(array $row): float
    {
        if ((int) ($row['settlement_type'] ?? 1) === 2) {
            return $this->ceilToTen((float) ($row['quantity'] ?? 0) * (float) ($row['settlement_rate'] ?? 0));
        }

        return $this->ceilToTen((float) ($row['gross_sales_amount'] ?? 0) * ((float) ($row['settlement_rate'] ?? 0) / 100) * 1.1);
    }

    private function ceilToTen(float $amount): float
    {
        return ceil(max(0, $amount) / 10) * 10;
    }

    private function normalizeShopChannelId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function latestSettlementPeriod(int $vendorId, SettlementCalculator $calculator): string
    {
        $statusValues = [
            OrderItemStatus::CONFIRMED,
            OrderItemStatus::label(OrderItemStatus::CONFIRMED),
            'Confirmed',
            '구매확정',
        ];

        $latest = \App\Models\OrdersProduct::where('vendor_id', $vendorId)
            ->where(function ($query) use ($statusValues) {
                $query->whereIn('status_code', $statusValues)
                    ->orWhereIn('item_status', $statusValues);
            })
            ->selectRaw('MAX(COALESCE(confirmed_at, updated_at)) as latest_at')
            ->value('latest_at');

        return $latest
            ? \Carbon\Carbon::parse($latest)->format('Y-m')
            : $calculator->normalizePeriod(null);
    }

    private function paginateRows($rows, Request $request, int $perPage, string $path): LengthAwarePaginator
    {
        $page = max((int) $request->query('page', 1), 1);
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
}
