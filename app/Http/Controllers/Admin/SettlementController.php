<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SettlementRun;
use App\Services\SettlementCalculator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

        return view('admin.settlements.index', compact('period', 'periodOptions', 'rows', 'totals'));
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

    public function complete($id, SettlementCalculator $calculator)
    {
        $settlement = SettlementRun::with('items')->findOrFail($id);

        if ($settlement->status === 'completed') {
            return redirect()
                ->route('admin.settlements.show', $settlement->id)
                ->with('success_message', '이미 정산 완료된 내역입니다.');
        }

        $calculator->complete($settlement, Auth::guard('admin')->id());

        return redirect()
            ->route('admin.settlements.show', $settlement->id)
            ->with('success_message', '정산 완료 처리했습니다.');
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
}
