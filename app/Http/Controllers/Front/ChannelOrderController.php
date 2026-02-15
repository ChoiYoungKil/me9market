<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrdersProduct; // 주문 상품 모델 가정

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
            $vendor_id = Auth::guard('admin')->user()->vendor_id;
            
            try {
                // Update specific items belonging to this vendor
                $updateData = ['item_status' => $data['status']];
                
                if ($data['status'] == 'shipping' || $data['status'] == 'shipped') {
                     if (empty($data['courier_name']) || empty($data['tracking_number'])) {
                         return response()->json(['status' => false, 'message' => '배송 정보를 모두 입력해주세요.']);
                     }
                    $updateData['courier_name'] = $data['courier_name'];
                    $updateData['tracking_number'] = $data['tracking_number'];
                }

                OrdersProduct::whereIn('id', $data['item_ids'])
                            ->where('vendor_id', $vendor_id)
                            ->update($updateData);

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

            $vendor_id = Auth::guard('admin')->user()->vendor_id;

            DB::beginTransaction();
            try {
                // Fetch the order to get user_id
                $order = Order::find($data['order_id']);
                
                // Fetch items to verify ownership and get details
                $items = OrdersProduct::whereIn('id', $data['item_ids'])
                            ->where('vendor_id', $vendor_id)
                            ->get();

                if ($items->isEmpty()) {
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
                     
                     // Update Item Status
                     $item->item_status = '취소요청'; // Or '취소완료' if seller cancels directly? Let's say '취소완료' as per previous logic for seller action
                     $item->item_status = '취소완료'; 
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
            
            $vendor_id = Auth::guard('admin')->user()->vendor_id;

            DB::beginTransaction();
            try {
                $order = Order::find($data['order_id']);
                $items = OrdersProduct::whereIn('id', $data['item_ids'])
                            ->where('vendor_id', $vendor_id)
                            ->get();

                 if ($items->isEmpty()) {
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

                     $item->item_status = '반품요청';
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

            $vendor_id = Auth::guard('admin')->user()->vendor_id;

             DB::beginTransaction();
             try {
                $order = Order::find($data['order_id']);
                $items = OrdersProduct::whereIn('id', $data['item_ids'])
                            ->where('vendor_id', $vendor_id)
                            ->get();

                 if ($items->isEmpty()) {
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

                     $item->item_status = '교환요청';
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
}