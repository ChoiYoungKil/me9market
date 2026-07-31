<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChannelPointTransaction;
use App\Services\ChannelPointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ChannelPointController extends Controller
{
    public function index(Request $request, ChannelPointService $pointService)
    {
        Session::put('page', 'channel-points');

        $status = $request->query('status', 'all');
        $type = $request->query('type', 'all');

        $query = ChannelPointTransaction::with(['vendor', 'shopChannel'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $transactions = $query->paginate(30)->withQueryString();

        $totals = [
            'pending_purchase' => ChannelPointTransaction::where('status', 'pending')->where('type', ChannelPointService::TYPE_PURCHASE)->sum('points'),
            'pending_refund' => abs((int) ChannelPointTransaction::where('status', 'pending')->where('type', ChannelPointService::TYPE_REFUND)->sum('points')),
            'approved_purchase' => ChannelPointTransaction::where('status', 'approved')->where('type', ChannelPointService::TYPE_PURCHASE)->sum('points'),
            'approved_refund' => abs((int) ChannelPointTransaction::where('status', 'approved')->where('type', ChannelPointService::TYPE_REFUND)->sum('points')),
            'approved_payback' => abs((int) ChannelPointTransaction::where('status', 'approved')->where('type', ChannelPointService::TYPE_CUSTOMER_PAYBACK)->sum('points')),
            'approved_sms' => abs((int) ChannelPointTransaction::where('status', 'approved')->where('type', ChannelPointService::TYPE_SMS)->sum('points')),
            'approved_balance' => ChannelPointTransaction::where('status', 'approved')->sum('points'),
        ];

        return view('admin.channel_points.index', compact('transactions', 'totals', 'status', 'type'));
    }

    public function approve($id, ChannelPointService $pointService)
    {
        $transaction = ChannelPointTransaction::findOrFail($id);
        $pointService->approve($transaction, (int) Auth::guard('admin')->id());

        return redirect()->route('admin.channel_points.index')->with('success_message', '포인트 요청을 승인했습니다.');
    }

    public function reject(Request $request, $id, ChannelPointService $pointService)
    {
        $request->validate(['memo' => 'nullable|string|max:255']);
        $transaction = ChannelPointTransaction::findOrFail($id);
        $pointService->reject($transaction, (int) Auth::guard('admin')->id(), $request->input('memo'));

        return redirect()->route('admin.channel_points.index')->with('success_message', '포인트 요청을 반려했습니다.');
    }
}
