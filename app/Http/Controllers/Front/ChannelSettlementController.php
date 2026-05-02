<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrdersProduct;
use Carbon\Carbon;

class ChannelSettlementController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $vendor_id = $admin->vendor_id;

        // Fetch the shop channel's settlement rate (using the first one as default if multiple exist)
        $shop = \App\Models\ShopChannel::where('vendor_id', $vendor_id)->first();
        $rate = $shop ? ($shop->settlement_rate / 100) : 0.10; // Fallback to 10%

        // Calculate monthly settlements
        $settlements = OrdersProduct::select(
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as settlement_period"),
                DB::raw('SUM(product_price * product_qty) as total_sales'),
                DB::raw('COUNT(id) as order_count')
            )
            ->where('vendor_id', $vendor_id)
            ->whereIn('item_status', ['구매확정'])
            ->groupBy('settlement_period')
            ->orderBy('settlement_period', 'desc')
            ->paginate(10);
            
        $settlements->getCollection()->transform(function($item) use ($rate) {
             $item->commission = $item->total_sales * $rate;
             $item->settlement_amount = $item->total_sales - $item->commission;
             $item->status = '정산완료'; 
             $item->rate = $rate * 100; // Store percentage for view
             
             return $item;
        });

        return view('channel.sub05.settlement_list', [
            'settlements' => $settlements,
            'dep1_id' => '05',
            'rate' => $rate * 100
        ]);
    }

    public function view(Request $request, $period)
    {
        $admin = Auth::guard('admin')->user();
        $vendor_id = $admin->vendor_id;

        // Fetch the shop channel's settlement rate
        $shop = \App\Models\ShopChannel::where('vendor_id', $vendor_id)->first();
        $rate = $shop ? ($shop->settlement_rate / 100) : 0.10;

        // Fetch orders for this period and vendor
        $orders = OrdersProduct::where('vendor_id', $vendor_id)
            ->where(DB::raw("DATE_FORMAT(updated_at, '%Y-%m')"), $period)
            ->whereIn('item_status', ['구매확정'])
            ->with(['product', 'order'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('channel.sub05.settlement_view', [
            'orders' => $orders,
            'period' => $period,
            'dep1_id' => '05',
            'rate' => $rate * 100
        ]);
    }
}
