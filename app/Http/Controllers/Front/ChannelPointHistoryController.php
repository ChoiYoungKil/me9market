<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ChannelPointHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'history' => 'nullable|string|in:all,earn,use,refund',
            'date_from' => 'bail|nullable|string|date_format:Y-m-d',
            'date_to' => 'bail|nullable|string|date_format:Y-m-d',
        ], [
            'history.in' => '포인트 구분을 다시 선택해 주세요.',
            'date_from.date_format' => '시작일을 올바른 날짜로 입력해 주세요.',
            'date_to.date_format' => '종료일을 올바른 날짜로 입력해 주세요.',
        ]);
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $request->validate(['date_to' => 'after_or_equal:date_from'], ['date_to.after_or_equal' => '종료일은 시작일 이후로 입력해 주세요.']);
        }
        $filters = array_replace(['history' => 'all', 'date_from' => '', 'date_to' => ''], array_filter($filters, fn ($value) => $value !== null));
        $vendorId = (int) Auth::guard('admin')->user()->vendor_id;
        abort_unless($vendorId > 0, 403);
        // Include closed shops: their historical distribution and conversion remain in circulation totals.
        $ledger = PointTransaction::whereHas('shopChannel', fn ($query) => $query->where('vendor_id', $vendorId));
        $balance = (int) (clone $ledger)->sum('points');
        $distributed = (int) (clone $ledger)->where('points', '>', 0)->where('type', '!=', 'refund')->sum('points');
        $summary = [
            'balance' => $balance,
            'distributed' => $distributed,
            'consumed' => $distributed - $balance,
            'used' => -(int) (clone $ledger)->where('type', 'use')->where('points', '<', 0)->sum('points'),
            'converted' => -(int) (clone $ledger)->where('type', 'convert_out')->where('points', '<', 0)->sum('points'),
            'restored' => (int) (clone $ledger)->where('type', 'refund')->where('points', '>', 0)->sum('points'),
        ];
        $history = clone $ledger;
        if ($filters['history'] === 'earn') {
            $history->where('points', '>', 0)->where('type', '!=', 'refund');
        } elseif ($filters['history'] === 'use') {
            $history->where(fn ($query) => $query->where('points', '<', 0)->orWhere('type', 'refund'));
        } elseif ($filters['history'] === 'refund') {
            // Keep previously bookmarked restoration-history URLs usable.
            $history->where('type', 'refund');
        }
        if ($filters['date_from'] !== '') {
            $history->where('created_at', '>=', Carbon::createFromFormat('!Y-m-d', $filters['date_from'])->startOfDay());
        }
        if ($filters['date_to'] !== '') {
            $history->where('created_at', '<=', Carbon::createFromFormat('!Y-m-d', $filters['date_to'])->endOfDay());
        }
        $filteredPoints = (int) (clone $history)->sum('points');
        $transactions = $history->with(['shopChannel:id,channel_name', 'user:id,name'])
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('channel.sub00.point_list', compact('filters', 'summary', 'transactions', 'filteredPoints'));
    }
}
