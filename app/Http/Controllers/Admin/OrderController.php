<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\OrdersLog;
use App\Models\OrderStatus;
use App\Models\OrderItemStatus;


class OrderController extends Controller
{
    // 참고: 관리자 패널의 주문 관리 섹션에서, 로그인한 사용자가 '판매자(vendor)'인 경우 해당 판매자가 등록한 상품과 관련된 주문만 표시하며, '관리자(admin)'인 경우 모든 주문을 표시함    



    // 관리자 패널의 주문 관리 섹션(admin/orders/orders.blade.php) 렌더링    
    public function orders() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'orders');


        // 로그인한 사용자를 확인하여 판매자인 경우 해당 판매자의 주문만, 관리자인 경우 전체 주문을 가져옴
        $adminType = Auth::guard('admin')->user()->type;      
        $vendor_id = Auth::guard('admin')->user()->vendor_id; 


        if ($adminType == 'vendor') { // 로그인한 사용자가 판매자인 경우 상태 확인
            $vendorStatus = Auth::guard('admin')->user()->status; 

            if ($vendorStatus == 0) { // 판매자가 비활성 상태인 경우
                return redirect('admin/update-vendor-details/personal')->with('error_message', '판매자 계정이 아직 승인되지 않았습니다. 개인, 사업자, 은행 정보를 모두 정확히 입력해 주세요.'); 
            }
        }


        if ($adminType == 'vendor') { // 판매자인 경우 해당 판매자의 상품이 포함된 주문만 표시
            $orders = Order::with([ 
                'orders_products' => function($query) use ($vendor_id) { 
                    $query->where('vendor_id', $vendor_id); 
                }
            ])->whereHas('orders_products', fn ($query) => $query->where('vendor_id', $vendor_id))->orderBy('id', 'Desc')->get()->toArray();

        } else { // 관리자인 경우 모든 주문 표시
            $orders = Order::with('orders_products')->orderBy('id', 'Desc')->get()->toArray(); 
        }


        return view('admin.orders.orders')->with(compact('orders'));
    }

    // 주문 상세 보기 페이지(admin/orders/order_details.blade.php) 렌더링
    public function orderDetails($id) {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'orders');

        $admin = Auth::guard('admin')->user();
        $adminType = $admin->type;
        $vendor_id = $admin->vendor_id;
        
        if ($adminType == 'vendor') { 
            $vendorStatus = Auth::guard('admin')->user()->status; 

            if ($vendorStatus == 0) { 
                return redirect('admin/update-vendor-details/personal')->with('error_message', '판매자 계정이 아직 승인되지 않았습니다. 개인, 사업자, 은행 정보를 모두 정확히 입력해 주세요.'); 
            }
        }


        if ($adminType == 'vendor') {
            $order = Order::with([
                'orders_products' => function($query) use ($vendor_id) {
                    $query->where('vendor_id', $vendor_id);
                }
            ])
                ->where('id', $id)
                ->whereHas('orders_products', function ($query) use ($vendor_id) {
                    $query->where('vendor_id', $vendor_id);
                })
                ->firstOrFail();

        } else {
            $order = Order::with('orders_products')->where('id', $id)->firstOrFail();
        }

        $orderDetails = $order->toArray();

        $user = User::where('id', $orderDetails['user_id'])->first();
        $userDetails = $user ? $user->toArray() : [];

        // `order_statuses` 테이블에서 활성화된 주문 상태 가져오기 (관리자만 변경 가능)
        $orderStatuses = OrderStatus::where('status', 1)->get()->toArray();

        // `order_item_statuses` 테이블에서 활성화된 개별 품목 상태 가져오기 (판매자 및 관리자 모두 변경 가능)
        $orderItemStatuses = OrderItemStatus::where('status', 1)->get()->toArray();

        // 주문 상태 변경 이력(로그) 가져오기
        $orderLog = OrdersLog::with('orders_products')->where('order_id', $id)
            ->when($adminType === 'vendor', fn ($query) => $query->whereHas('orders_products',
                fn ($items) => $items->where('vendor_id', $vendor_id)))
            ->orderBy('id', 'Desc')->get()->toArray();


        
        // 장바구니 내 전체 품목 수량 계산
        $total_items = 0;

        foreach ($orderDetails['orders_products'] as $product) {
            $total_items = $total_items + $product['product_qty'];
        }

        // 품목 할인액 계산 (쿠폰 사용 시)
        if ($total_items > 0 && $orderDetails['coupon_amount'] > 0) {
            $item_discount = round($orderDetails['coupon_amount'] / $total_items, 2); 
        } else {
            $item_discount = 0;
        }


        return view('admin.orders.order_details')->with(compact('orderDetails', 'userDetails', 'orderStatuses', 'orderItemStatuses', 'orderLog', 'item_discount'));
    }

    // 주문 상태 업데이트 (관리자 전용: 대기, 배송 중, 진행 중, 취소 등)
    public function updateOrderStatus(Request $request)
    {
        abort_if(Auth::guard('admin')->user()->type === 'vendor', 403);
        $data = $request->validate([
            'order_id' => 'required|integer',
            'order_status' => 'required|string|max:50',
            'courier_name' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
        ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $order = Order::whereKey($data['order_id'])->lockForUpdate()->firstOrFail();
            $items = $order->orders_products()->orderBy('id')->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['order_status' => '처리할 주문상품이 없습니다.']);
            }
            foreach ($items as $item) {
                $this->changeItem($item, $data['order_status'], $data['courier_name'] ?? null, $data['tracking_number'] ?? null);
            }
            $order->forceFill([
                'order_status' => $data['order_status'],
                'courier_name' => $data['courier_name'] ?? $order->courier_name,
                'tracking_number' => $data['tracking_number'] ?? $order->tracking_number,
            ])->save();
        });

        return back()->with('success_message', '주문 상태가 업데이트되었습니다.');
    }

    public function updateOrderItemStatus(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => 'required|integer',
            'order_item_status' => 'required|string|max:50',
            'item_courier_name' => 'nullable|string|max:100',
            'item_tracking_number' => 'nullable|string|max:100',
        ]);
        $admin = Auth::guard('admin')->user();
        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $admin) {
            $item = OrdersProduct::whereKey($data['order_item_id'])
                ->when($admin->type === 'vendor', fn ($query) => $query->where('vendor_id', $admin->vendor_id))
                ->lockForUpdate()->firstOrFail();
            $this->changeItem($item, $data['order_item_status'], $data['item_courier_name'] ?? null, $data['item_tracking_number'] ?? null);
        });

        return back()->with('success_message', '주문 품목 상태가 업데이트되었습니다.');
    }

    private function changeItem(OrdersProduct $item, string $status, ?string $courier, ?string $tracking): void
    {
        $status = \App\Support\OrderItemStatus::normalize($status);
        $changed = $item->normalized_status !== $status;
        $item->courier_name = $courier ?? $item->courier_name;
        $item->tracking_number = $tracking ?? $item->tracking_number;
        if ($status === \App\Support\OrderItemStatus::SHIPPING && (! $item->courier_name || ! $item->tracking_number)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['courier_name' => '택배사와 송장번호를 입력해 주세요.']);
        }
        app(\App\Services\OrderFulfillmentService::class)->change($item, $status);
        if (! $changed) {
            return;
        }
        $log = new OrdersLog;
        $log->order_id = $item->order_id;
        $log->order_item_id = $item->id;
        $log->order_status = $item->item_status;
        $log->save();

        \Illuminate\Support\Facades\DB::afterCommit(function () use ($item) {
            try {
                $order = Order::with(['orders_products' => fn ($query) => $query->whereKey($item->id)])->findOrFail($item->order_id);
                \Illuminate\Support\Facades\Mail::send('emails.order_item_status', [
                    'email' => $order->email, 'name' => $order->name, 'order_id' => $order->id,
                    'orderDetails' => $order->toArray(), 'order_status' => $item->item_status,
                    'courier_name' => $item->courier_name, 'tracking_number' => $item->tracking_number,
                ], fn ($message) => $message->to($order->email)->subject('주문 품목 상태가 업데이트되었습니다 - me9market.com'));
            } catch (\Throwable $exception) {
                report($exception);
            }
        });
    }

    private function invoiceOrder($id): Order
    {
        $admin = Auth::guard('admin')->user();
        return Order::with(['orders_products' => fn ($query) => $query
            ->when($admin->type === 'vendor', fn ($items) => $items->where('vendor_id', $admin->vendor_id))])
            ->when($admin->type === 'vendor', fn ($query) => $query->whereHas('orders_products',
                fn ($items) => $items->where('vendor_id', $admin->vendor_id)))
            ->findOrFail($id);
    }

    public function viewOrderInvoice($order_id)
    {
        $data = app(\App\Services\OrderInvoiceService::class)->data($this->invoiceOrder($order_id));
        return view('admin.orders.order_invoice', $data + ['pdf' => false]);
    }

    public function viewPDFInvoice($order_id)
    {
        return app(\App\Services\OrderInvoiceService::class)->download($this->invoiceOrder($order_id));
    }
}
