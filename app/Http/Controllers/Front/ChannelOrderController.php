<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrdersProduct; // 주문 상품 모델 가정
use App\Services\ChannelPointService;
use App\Support\OrderItemStatus;

class ChannelOrderController extends Controller
{
    // 주문 상태 업데이트 (예: 송장 입력)
    public function updateStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            
            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'status' => 'required|string',
                'item_ids' => 'required|array', // Must select items
                'item_ids.*' => 'exists:orders_products,id',
                'courier_name' => 'nullable|string', // required_if:status,shipping removed for flexibility, but logic handles it
                'tracking_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            // Vendor ID check
            $vendor_id = $this->currentVendorId();
            if (!$vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            $status = OrderItemStatus::normalize($data['status']);
            if (!array_key_exists($status, OrderItemStatus::labels())) {
                return response()->json(['status' => false, 'message' => '처리할 수 없는 주문 상태입니다.']);
            }
            
            try {
                if ($status === OrderItemStatus::SHIPPING && (empty($data['courier_name']) || empty($data['tracking_number']))) {
                    return response()->json(['status' => false, 'message' => '배송 정보를 모두 입력해주세요.']);
                }

                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);
                if ($items->isEmpty()) {
                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    $item->setStatus($status);
                    $item->item_status = $data['status'];

                    if ($status === OrderItemStatus::SHIPPING) {
                        $item->courier_name = $data['courier_name'];
                        $item->tracking_number = $data['tracking_number'];
                    }

                    $this->applyStatusTimestamps($item, $status);
                    $item->save();
                }

                $this->debitStatusSmsPoints($vendor_id, $status, $items);

                // Check if all items in order are shipped, then maybe update main order status?
                // For now, we stick to item status updates.

                return response()->json(['status' => true, 'message' => '주문 상품 상태가 업데이트되었습니다.']);

            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    // 취소 요청
    public function requestCancel(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'item_ids' => 'required|array',
                'reason' => 'required|string',
                'detail_reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            $vendor_id = $this->currentVendorId();
            if (!$vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            DB::beginTransaction();
            try {
                // Fetch the order to get user_id
                $order = Order::find($data['order_id']);
                
                // Fetch items to verify ownership and get details
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);

                if ($items->isEmpty()) {
                    DB::rollback();
                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                     // Check if claim already exists for this item to avoid duplicates (optional, strictly speaking)
                     // For now, allow multiple claims if previous one was rejected or cancelled, but here we assume simple flow.
                     
                     // Create Claim
                     \App\Models\OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0, // Fallback if guest order
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'cancel',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'],
                        'status' => 'requested'
                     ]);
                     
                     // 판매자 화면의 취소 처리는 요청 접수와 동시에 취소 완료 상태로 마감한다.
                     $item->setStatus(OrderItemStatus::CANCELLED);
                     $this->applyStatusTimestamps($item, OrderItemStatus::CANCELLED);
                     $item->save();
                }

                DB::commit();

                return response()->json(['status' => true, 'message' => '취소 처리가 완료되었습니다.']);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    // 반품 요청
    public function requestReturn(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'item_ids' => 'required|array',
                'reason' => 'required|string',
                // 'detail_reason' => 'required|string', // View might need to send this
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }
            
            $vendor_id = $this->currentVendorId();
            if (!$vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            DB::beginTransaction();
            try {
                $order = Order::find($data['order_id']);
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);

                 if ($items->isEmpty()) {
                    DB::rollback();
                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    \App\Models\OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0,
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'return',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'] ?? $data['reason'], // Use detailed reason if available
                        'status' => 'requested'
                     ]);

                     $item->setStatus(OrderItemStatus::RETURN_REQUESTED);
                     $this->applyStatusTimestamps($item, OrderItemStatus::RETURN_REQUESTED);
                     $item->save();
                }
                
                DB::commit();

                return response()->json(['status' => true, 'message' => '반품 요청이 접수되었습니다.']);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    // 교환 요청
    public function requestExchange(Request $request)
    {
        if ($request->ajax()) {
             $data = $request->all();
             
            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'item_ids' => 'required|array',
                'reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            $vendor_id = $this->currentVendorId();
            if (!$vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

             DB::beginTransaction();
             try {
                $order = Order::find($data['order_id']);
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);

                 if ($items->isEmpty()) {
                    DB::rollback();
                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    \App\Models\OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0,
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'exchange',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'] ?? $data['reason'],
                        'status' => 'requested'
                     ]);

                     $item->setStatus(OrderItemStatus::EXCHANGE_REQUESTED);
                     $this->applyStatusTimestamps($item, OrderItemStatus::EXCHANGE_REQUESTED);
                     $item->save();
                }

                DB::commit();

                return response()->json(['status' => true, 'message' => '교환 요청이 접수되었습니다.']);
             } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
             }
        }
    }

    private function currentVendorId(): ?int
    {
        $admin = Auth::guard('admin')->user();

        return $admin?->vendor_id ? (int) $admin->vendor_id : null;
    }

    private function vendorItems(array $itemIds, int $vendorId, int $orderId)
    {
        return OrdersProduct::whereIn('id', $itemIds)
            ->where('vendor_id', $vendorId)
            ->where('order_id', $orderId)
            ->get();
    }

    private function applyStatusTimestamps(OrdersProduct $item, string $status): void
    {
        if ($status === OrderItemStatus::SHIPPING && !$item->shipped_at) {
            $item->shipped_at = now();
        }

        if ($status === OrderItemStatus::DELIVERED && !$item->delivered_at) {
            $item->delivered_at = now();
        }

        if ($status === OrderItemStatus::CONFIRMED && !$item->confirmed_at) {
            $item->confirmed_at = now();
        }
    }

    private function debitStatusSmsPoints(int $vendorId, string $status, $items): void
    {
        if (!in_array($status, [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED], true)) {
            return;
        }

        $itemsByChannel = $items
            ->filter(fn ($item) => !empty($item->shop_channel_id))
            ->groupBy('shop_channel_id');

        foreach ($itemsByChannel as $shopChannelId => $channelItems) {
            app(ChannelPointService::class)->recordSmsDebit(
                $vendorId,
                1,
                20,
                (int) $shopChannelId,
                OrderItemStatus::label($status) . ' 안내 문자 발송'
            );
        }
    }
}
