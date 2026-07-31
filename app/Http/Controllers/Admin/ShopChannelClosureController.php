<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ShopChannelClosureController extends Controller
{
    public function index(Request $request)
    {
        Session::put('page', 'shop-channel-closures');

        $status = $request->query('status', 'requested');
        $keyword = trim((string) $request->query('keyword', ''));

        $channels = ShopChannel::with('vendor')
            ->when($status !== 'all', fn ($query) => $query->where('closure_status', $status))
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('channel_name', 'like', '%' . $keyword . '%')
                        ->orWhere('channel_code', 'like', '%' . $keyword . '%')
                        ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('name', 'like', '%' . $keyword . '%'));
                });
            })
            ->orderByRaw("case when closure_status = 'requested' then 0 else 1 end")
            ->orderByDesc('closure_requested_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shop_channel_closures.index', compact('channels', 'status', 'keyword'));
    }

    public function approve($id)
    {
        $channel = ShopChannel::findOrFail($id);

        $channel->forceFill([
            'status' => 0,
            'closure_status' => 'approved',
            'closure_approved_at' => now(),
            'closure_rejected_at' => null,
            'closure_reviewed_by' => Auth::guard('admin')->id(),
        ])->save();

        return back()->with('success_message', 'Shop 채널 운영중지 요청을 승인했습니다.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'closure_memo' => 'nullable|string|max:255',
        ]);

        $channel = ShopChannel::findOrFail($id);

        $channel->forceFill([
            'closure_status' => 'rejected',
            'closure_rejected_at' => now(),
            'closure_approved_at' => null,
            'closure_reviewed_by' => Auth::guard('admin')->id(),
            'closure_memo' => $data['closure_memo'] ?? $channel->closure_memo,
        ])->save();

        return back()->with('success_message', 'Shop 채널 운영중지 요청을 반려했습니다.');
    }
}
