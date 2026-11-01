<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\OrdersLog;
use App\Models\OrderStatus;
use App\Models\OrderItemStatus;


class OrderController extends Controller
{
    // 참고: 관리자 패널의 주문 관리 섹션에서, 로그인한 사용자가 '판매자(vendor)'인 경우 해당 판매자가 등록한 상품과 관련된 주문만 표시하며, '관리자(admin)'인 경우 모든 주문을 표시함    



    // 관리자 패널의 주문 관리 섹션(admin/orders/orders.blade.php) 렌더링    
    public function orders(Request $request) {
        Session::put('page','orders');
        $filters=$request->validate(['keyword'=>'nullable|string|max:100','item_status'=>['nullable',\Illuminate\Validation\Rule::in(array_keys(\App\Support\OrderItemStatus::labels()))],'from'=>'nullable|date_format:Y-m-d','to'=>['nullable','date_format:Y-m-d',\Illuminate\Validation\Rule::when($request->filled('from'),'after_or_equal:from')],'per_page'=>'nullable|in:10,20,50,100','export'=>'nullable|in:1']);
        $admin=Auth::guard('admin')->user();$vendorId=$admin->type==='vendor'?(int)$admin->vendor_id:null;
        $owner=fn($q)=>$q->when($vendorId!==null,fn($q)=>$q->where('vendor_id',$vendorId));
        $query=Order::whereHas('orders_products',$owner)->with(['orders_products'=>fn($q)=>$owner($q)->with('shopChannel')]);
        if($request->filled('keyword'))$query->where(function($q)use($filters,$owner){$keyword=$filters['keyword'];$q->where('name','like','%'.$keyword.'%')->orWhere('buyer_name','like','%'.$keyword.'%')->orWhere('email','like','%'.$keyword.'%')->orWhereHas('orders_products',fn($q)=>$owner($q)->where(fn($q)=>$q->where('product_name','like','%'.$keyword.'%')->orWhere('product_code','like','%'.$keyword.'%')->orWhere('tracking_number','like','%'.$keyword.'%')));$id=preg_replace('/^Me9(?:-Shop)?-0*/i','',$keyword);if(ctype_digit($id))$q->orWhere('id',(int)$id);});
        if($request->filled('item_status'))$query->whereHas('orders_products',function($q)use($owner,$filters){$owner($q);\App\Support\AdminOrderStatusScope::apply($q,$filters['item_status']);});
        if($request->filled('from'))$query->where('created_at','>=',$filters['from'].' 00:00:00');
        if($request->filled('to'))$query->where('created_at','<',\Carbon\Carbon::parse($filters['to'])->addDay()->toDateString());
        $query->orderByDesc('id');
        if($request->boolean('export'))return app(\App\Services\AdminListExport::class)->download($query,'주문목록',['주문번호'=>fn($o)=>'Me9-Shop-'.str_pad($o->id,7,'0',STR_PAD_LEFT),'주문일'=>'created_at','주문자'=>fn($o)=>$o->buyer_name?:$o->name,'상품'=>fn($o)=>$o->orders_products->pluck('product_name')->implode(' / '),'상품금액'=>fn($o)=>$o->orders_products->sum('line_total'),'상품상태'=>fn($o)=>$o->orders_products->pluck('status_label')->unique()->implode(' / ')]);
        $orders=$query->paginate((int)($filters['per_page']??20))->withQueryString();
        return view('admin.orders.orders',compact('orders','filters'));
    }

    // 주문 상세 보기 페이지(admin/orders/order_details.blade.php) 렌더링
    public function orderDetails($id) {
        if (in_array(Auth::guard('admin')->user()?->type,['admin','superadmin'],true)) return app(AdminOrderWorkspaceController::class)->show((int)$id);
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
        $cancellationClaims = OrderClaim::with('product:id,product_name,item_status,status_code')
            ->where('order_id', $order->id)
            ->whereIn('order_product_id', $order->orders_products->pluck('id'))
            ->where('type', 'cancel')->latest('id')->get();

        $user = User::where('id', $orderDetails['user_id'])->first();
        $userDetails = $user ? $user->toArray() : [
            'name' => $order->buyer_name ?: $order->name,
            'mobile' => $order->buyer_mobile ?: $order->mobile,
            'email' => $order->email,
        ];

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


        return view('admin.orders.order_details')->with(compact('orderDetails', 'userDetails', 'orderStatuses', 'orderItemStatuses', 'orderLog', 'item_discount', 'cancellationClaims'));
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
        $log->actor = mb_substr(Auth::guard('admin')->user()?->name ?? '전체관리자',0,100);
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
