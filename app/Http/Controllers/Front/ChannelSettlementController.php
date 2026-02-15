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
        $vendor_id = Auth::guard('admin')->user()->vendor_id;

        // Calculate monthly settlements
        // Group orders by month (Y-m)
        // Using Eloquent's query builder for aggregation
        $settlements = OrdersProduct::select(
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as settlement_period"), // Using updated_at as finalize date
                DB::raw('SUM(product_price * product_qty) as total_sales'),
                DB::raw('COUNT(id) as order_count')
            )
            ->where('vendor_id', $vendor_id)
            ->whereIn('item_status', ['구매확정', '배송완료']) // Include 배송완료 for testing if needed, or stick to strict logic
            ->groupBy('settlement_period')
            ->orderBy('settlement_period', 'desc')
            ->paginate(10);
            
        // Transform to add calculated fields
        $settlements->getCollection()->transform(function($item) {
             // Example commission rate 10%
             $commissionRate = 0.10;
             $item->commission = $item->total_sales * $commissionRate;
             $item->settlement_amount = $item->total_sales - $item->commission;
             $item->status = '정산예정'; // Default status for now
             
             return $item;
        });

        // Pass data to view
        $dep1_id = '05'; // Assuming 05 is settlement
        
        return view('channel.sub05.settlement_list', compact('settlements', 'dep1_id'));
    }
}
