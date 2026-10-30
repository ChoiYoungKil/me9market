<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Services\ChannelPointService;
use App\Services\ShopChannelSmsService;
use App\Support\OrderItemStatus; // 주문 상품 모델 가정
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChannelOrderController extends Controller
{
    // 주문 상태 업데이트 (예: 송장 입력)
    public function updateStatus(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'message' => 'AJAX 요청만 허용됩니다.'], 422);
        }

        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'status' => 'required|in:paid,ready_to_ship,shipping,delivered,confirmed,New,In Process,Shipped,Delivered,Confirmed',
                'item_ids' => 'required|array|min:1|max:500', // Must select items
                'item_ids.*' => 'integer|distinct|exists:orders_products,id',
                'courier_name' => 'nullable|string|max:100', // required_if:status,shipping removed for flexibility, but logic handles it
                'tracking_number' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            // Vendor ID check
            $vendor_id = $this->currentVendorId();
            if (! $vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            $status = OrderItemStatus::normalize($data['status']);
            if (! array_key_exists($status, OrderItemStatus::labels())) {
                return response()->json(['status' => false, 'message' => '처리할 수 없는 주문 상태입니다.']);
            }

            try {
                if ($status === OrderItemStatus::SHIPPING && (empty($data['courier_name']) || empty($data['tracking_number']))) {
                    return response()->json(['status' => false, 'message' => '배송 정보를 모두 입력해주세요.']);
                }

                $items = DB::transaction(function () use ($data, $vendor_id, $status) {
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);
                if (! $this->containsAllRequestedItems($items, $data['item_ids'])) {
                    throw ValidationException::withMessages(['item_ids' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                if ($status === OrderItemStatus::PAID && $items->contains(fn ($item) => $item->normalized_status !== OrderItemStatus::AWAITING_PAYMENT)) {
                    throw ValidationException::withMessages(['status' => '입금대기 상태에서만 결제완료로 변경할 수 있습니다.']);
                }
                $changedItems = $items->filter(fn ($item) => $item->normalized_status !== $status);
                foreach ($items as $item) {
                    if ($status === OrderItemStatus::SHIPPING) {
                        $item->courier_name = $data['courier_name'];
                        $item->tracking_number = $data['tracking_number'];
                    }

                    if ($status === OrderItemStatus::SHIPPING && !in_array($item->normalized_status, [OrderItemStatus::READY_TO_SHIP, OrderItemStatus::SHIPPING], true)) {
                        throw ValidationException::withMessages(['status' => '배송대기 / 배송중 상품만 배송정보를 관리할 수 있습니다.']);
                    }
                    app(\App\Services\OrderFulfillmentService::class)->change($item, $status);
                    $this->logItemStatus($item);
                }
                return $changedItems;
                });

                $this->handleStatusSms($vendor_id, $status, $items);

                // Check if all items in order are shipped, then maybe update main order status?
                // For now, we stick to item status updates.

                return response()->json(['status' => true, 'message' => '주문 상품 상태가 업데이트되었습니다.']);

            } catch (ValidationException $e) {
                return response()->json(['status' => false, 'message' => $e->validator->errors()->first()], 422);
            } catch (\Exception $e) {
                report($e);
                return response()->json(['status' => false, 'message' => '주문 처리에 실패했습니다. 잠시 후 다시 시도해 주세요.'], 500);
            }
        }
    }

    // 취소 요청
    public function requestCancel(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'message' => 'AJAX 요청만 허용됩니다.'], 422);
        }

        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'item_ids' => 'required|array|min:1|max:500',
                'item_ids.*' => 'integer|distinct|exists:orders_products,id',
                'reason' => 'required|string|max:1000',
                'detail_reason' => 'required|string|max:3000',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            $vendor_id = $this->currentVendorId();
            if (! $vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            DB::beginTransaction();
            try {
                // Fetch the order to get user_id
                $order = Order::find($data['order_id']);

                // Fetch items to verify ownership and get details
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);

                if (! $this->containsAllRequestedItems($items, $data['item_ids'])) {
                    DB::rollback();

                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    if (! in_array($item->normalized_status, [OrderItemStatus::AWAITING_PAYMENT, OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP], true)) {
                        throw ValidationException::withMessages(['item_ids' => '취소할 수 없는 주문 상태입니다.']);
                    }
                    $claim = OrderClaim::where('order_product_id', $item->id)->where('type', 'cancel')
                        ->where('status', 'requested')->latest('id')->lockForUpdate()->first();
                    if (! $claim) {
                        OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0, // Fallback if guest order
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'cancel',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'],
                        'status' => 'requested',
                    ]);

                    }
                    $item->setStatus(OrderItemStatus::CANCEL_REQUESTED);
                    $item->save();
                    $this->logItemStatus($item);
                }

                DB::commit();

                return response()->json(['status' => true, 'message' => '취소 요청이 접수되었습니다.']);

            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => $e->validator->errors()->first()], 422);
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return response()->json(['status' => false, 'message' => '주문 처리에 실패했습니다. 잠시 후 다시 시도해 주세요.'], 500);
            }
        }
    }

    // 반품 요청
    public function requestReturn(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'message' => 'AJAX 요청만 허용됩니다.'], 422);
        }

        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'item_ids' => 'required|array|min:1|max:500',
                'item_ids.*' => 'integer|distinct|exists:orders_products,id',
                'reason' => 'required|string|max:1000',
                'detail_reason' => 'nullable|string|max:3000',
                ...$this->pickupRules(),
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            $vendor_id = $this->currentVendorId();
            if (! $vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            DB::beginTransaction();
            try {
                $order = Order::find($data['order_id']);
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);

                if (! $this->containsAllRequestedItems($items, $data['item_ids'])) {
                    DB::rollback();

                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    if (! OrderItemStatus::customerActionAllowed('return', $item->normalized_status)) {
                        throw ValidationException::withMessages(['item_ids' => '반품 요청할 수 없는 주문 상태입니다.']);
                    }
                    OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0,
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'return',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'] ?? $data['reason'], // Use detailed reason if available
                        'status' => 'requested',
                        ...$this->pickupValues($data),
                    ]);
                    $item->return_shipping_fee = $this->claimFeeForItem($items, $item, $data);
                    $item->setStatus(OrderItemStatus::RETURN_REQUESTED);
                    $this->applyStatusTimestamps($item, OrderItemStatus::RETURN_REQUESTED);
                    $item->save();
                    $this->logItemStatus($item);
                }

                DB::commit();
                $this->handleStatusSms($vendor_id, OrderItemStatus::RETURN_REQUESTED, $items);

                return response()->json(['status' => true, 'message' => '반품 요청이 접수되었습니다.']);
            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => $e->validator->errors()->first()], 422);
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return response()->json(['status' => false, 'message' => '주문 처리에 실패했습니다. 잠시 후 다시 시도해 주세요.'], 500);
            }
        }
    }

    // 교환 요청
    public function requestExchange(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'message' => 'AJAX 요청만 허용됩니다.'], 422);
        }

        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'order_id' => 'required|exists:orders,id',
                'item_ids' => 'required|array|min:1|max:500',
                'item_ids.*' => 'integer|distinct|exists:orders_products,id',
                'reason' => 'required|string|max:1000',
                'detail_reason' => 'nullable|string|max:3000',
                ...$this->pickupRules(),
                'exchange_options'=>'nullable|array|max:500',
                'exchange_options.*'=>'nullable|string|max:100',
                'exchange_recipient_name'=>'nullable|string|max:100',
                'exchange_recipient_mobile'=>'nullable|string|max:30',
                'exchange_recipient_address'=>'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            $vendor_id = $this->currentVendorId();
            if (! $vendor_id) {
                return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            DB::beginTransaction();
            try {
                $order = Order::find($data['order_id']);
                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);

                if (! $this->containsAllRequestedItems($items, $data['item_ids'])) {
                    DB::rollback();

                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    if (! OrderItemStatus::customerActionAllowed('exchange', $item->normalized_status)) {
                        throw ValidationException::withMessages(['item_ids' => '교환 요청할 수 없는 주문 상태입니다.']);
                    }
                    $originalOption = $item->product_size;
                    $option = $data['exchange_options'][$item->id] ?? null;
                    if ($option && $option !== $originalOption) {
                        $attribute=$item->product?->attributes->first(fn($attribute)=>(int)$attribute->status===1 && $attribute->size===$option);
                        if (!$attribute || abs((float)$attribute->price_delta-(float)$item->option_price_adjustment)>0.001) throw ValidationException::withMessages(['exchange_options'=>'차액이 없는 판매 중 옵션을 선택해 주세요.']);
                        $item->product_size=$option;
                    }
                    OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0,
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'exchange',
                        'original_option'=>$originalOption,
                        'exchange_recipient_name'=>$data['exchange_recipient_name']??$order->name,
                        'exchange_recipient_mobile'=>$data['exchange_recipient_mobile']??$order->mobile,
                        'exchange_recipient_address'=>$data['exchange_recipient_address']??$order->address,
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'] ?? $data['reason'],
                        'status' => 'requested',
                        ...$this->pickupValues($data),
                    ]);
                    $item->exchange_shipping_fee = $this->claimFeeForItem($items, $item, $data);
                    $item->setStatus(OrderItemStatus::EXCHANGE_REQUESTED);
                    $this->applyStatusTimestamps($item, OrderItemStatus::EXCHANGE_REQUESTED);
                    $item->save();
                    $this->logItemStatus($item);
                }

                DB::commit();

                return response()->json(['status' => true, 'message' => '교환 요청이 접수되었습니다.']);
            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => $e->validator->errors()->first()], 422);
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return response()->json(['status' => false, 'message' => '주문 처리에 실패했습니다. 잠시 후 다시 시도해 주세요.'], 500);
            }
        }
    }

    public function claimAction(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'message' => 'AJAX 요청만 허용됩니다.'], 422);
        }

        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'item_ids' => 'required|array|min:1|max:500',
            'item_ids.*' => 'integer|distinct|exists:orders_products,id',
            'action' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Support\OrderClaimActions::definitions()))],
            'reason' => 'required_if:action,return_hold,exchange_hold_before,exchange_hold_after|nullable|string|max:1000',
            'courier_name' => 'required_if:action,return_invoice,exchange_invoice|nullable|string|max:100',
            'tracking_number' => 'required_if:action,return_invoice,exchange_invoice|nullable|string|max:100',
            'option' => 'required_if:action,exchange_option|nullable|string|max:100',
        ]);

        $vendorId = $this->currentVendorId();
        if (! $vendorId) {
            return response()->json(['status' => false, 'message' => '로그인이 필요합니다.'], 401);
        }

        try {
            $items = DB::transaction(function () use ($data, $vendorId) {
                $items = OrdersProduct::whereIn('id', $data['item_ids'])
                    ->where('vendor_id', $vendorId)
                    ->where('order_id', $data['order_id'])
                    ->with(['order', 'shopChannel', 'exchangeReplacement'])
                    ->orderBy('id')->lockForUpdate()
                    ->get();

                if ($items->count() !== count(array_unique($data['item_ids']))) {
                    throw ValidationException::withMessages(['item_ids' => '선택한 주문상품 중 권한이 없는 항목이 있습니다.']);
                }

                foreach ($items as $item) {
                    $this->applyClaimAction($item, $data, $vendorId);
                }

                return $items;
            });

            if (in_array($data['action'], ['cancel_approve', 'return_complete', 'exchange_to_return'], true)) {
                $this->handleStatusSms($vendorId, $data['action'] === 'cancel_approve' ? OrderItemStatus::CANCELLED : OrderItemStatus::RETURNED, $items);
            }

            return response()->json([
                'status' => true,
                'message' => '요청한 주문 처리가 완료되었습니다.',
                'items' => $items->map(fn ($item) => [
                    'id' => $item->id,
                    'status' => $item->status_code,
                    'status_label' => $item->item_status,
                ])->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status'=>false,'message'=>'주문 처리에 실패했습니다. 잠시 후 다시 시도해 주세요.'],500);
        }
    }

    private function applyClaimAction(OrdersProduct $item, array $data, int $vendorId): void
    {
        $action = $data['action'];
        $current = $item->normalized_status;
        $definition = \App\Support\OrderClaimActions::definitions()[$action];

        if (! in_array($current, $definition['from'], true)) {
            throw ValidationException::withMessages([
                'action' => OrderItemStatus::label($current).'에서는 '.$definition['label'].' 처리를 할 수 없습니다.',
            ]);
        }

        $claimType = str_starts_with($action, 'cancel_')
            ? 'cancel'
            : (str_starts_with($action, 'return_') ? 'return' : 'exchange');
        $claim = OrderClaim::where('order_id', $item->order_id)
            ->where('order_product_id', $item->id)
            ->where('vendor_id', $vendorId)
            ->where('type', $claimType)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (! $claim) {
            throw ValidationException::withMessages(['action' => '해당 클레임 요청을 찾을 수 없습니다.']);
        }

        if (in_array($action, ['return_invoice', 'exchange_invoice'], true)) {
            $item->courier_name = $data['courier_name'];
            $item->tracking_number = $data['tracking_number'];
            $claim->customer_courier_name = $data['courier_name'];
            $claim->customer_tracking_number = $data['tracking_number'];
        }

        if ($action === 'exchange_option') {
            $attributes = $item->product?->attributes ?? collect();
            if (! $attributes->contains(fn ($attribute) => (int) $attribute->status === 1 && $attribute->size === $data['option'])) {
                throw ValidationException::withMessages(['option' => '판매 중인 옵션을 선택해 주세요.']);
            }
            $selected = $attributes->firstWhere('size', $data['option']);
            if ($selected && abs((float) $selected->price_delta - (float) $item->option_price_adjustment) > 0.001) {
                throw ValidationException::withMessages(['option' => '차액 결제가 필요한 옵션은 교환할 수 없습니다. 반품 후 새로 주문해 주세요.']);
            }
            $item->product_size = $data['option'];
        }

        $isMetadataOnly = in_array($action, ['return_invoice', 'exchange_option', 'exchange_invoice'], true);
        $targetStatus = $isMetadataOnly ? $current : $definition['to'];
        if ($action === 'cancel_reject') {
            $targetStatus = in_array($item->claim_previous_status, [OrderItemStatus::AWAITING_PAYMENT, OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP], true)
                ? $item->claim_previous_status : OrderItemStatus::PAID;
        }
        if (in_array($action, ['return_withdraw', 'exchange_withdraw'], true) && in_array($item->claim_previous_status, [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED], true)) {
            $targetStatus = $item->claim_previous_status;
        }
        if ($action === 'return_withdraw') $item->return_shipping_fee = 0;
        if ($action === 'exchange_withdraw') {
            $item->exchange_shipping_fee = 0;
            if ($claim->original_option !== null) $item->product_size = $claim->original_option;
        }
        $item->setStatus($targetStatus);
        $item->save();
                    $this->logItemStatus($item);
        app(\App\Services\CustomerPointService::class)->reverseItem($item);

        $comment = $definition['label'];
        if (! empty($data['reason'])) {
            $comment .= ': '.$data['reason'];
        }
        if ($action === 'exchange_option') {
            $comment .= ': '.$data['option'];
        }
        if (in_array($action, ['return_invoice', 'exchange_invoice'], true)) {
            $comment .= ': '.$data['courier_name'].' '.$data['tracking_number'];
        }

        if (! $isMetadataOnly) {
            $claim->status = $definition['claim_status'];
        }
        $claim->admin_comment = trim(implode("\n", array_filter([$claim->admin_comment, '['.now()->format('Y-m-d H:i:s').'] '.$comment])));
        $claim->save();

        if ($action === 'exchange_complete') {
            $this->createExchangeReplacement($item);
        }

        if ($action === 'exchange_to_return') {
            OrderClaim::updateOrCreate(
                [
                    'order_id' => $item->order_id,
                    'order_product_id' => $item->id,
                    'vendor_id' => $vendorId,
                    'type' => 'return',
                ],
                [
                    'user_id' => $item->user_id ?: 0,
                    'reason' => '교환에서 반품으로 전환',
                    'pickup_method'=>$claim->pickup_method,
                    'pickup_name'=>$claim->pickup_name,
                    'pickup_mobile'=>$claim->pickup_mobile,
                    'return_address'=>$claim->return_address,
                    'customer_courier_name'=>$claim->customer_courier_name,
                    'customer_tracking_number'=>$claim->customer_tracking_number,
                    'detail_reason' => $data['reason'] ?? null,
                    'status' => 'completed',
                    'admin_comment' => '['.now()->format('Y-m-d H:i:s').'] 교환 클레임에서 반품완료로 전환',
                ]
            );
        }
    }

    private function createExchangeReplacement(OrdersProduct $item): OrdersProduct
    {
        if ($item->exchangeReplacement) {
            return $item->exchangeReplacement;
        }

        app(\App\Services\OrderFulfillmentService::class)->transferExchangeStock($item);

        $replacement = $item->replicate();
        $replacement->replacement_for_order_product_id = $item->id;
        $replacement->is_exchange_replacement = true;
        $replacement->product_price = 0;
        $replacement->selling_price = 0;
        $replacement->supply_price = 0;
        $replacement->line_total = 0;
        $replacement->commission = 0;
        $replacement->settlement_status = 'excluded_exchange_replacement';
        $replacement->courier_name = null;
        $replacement->tracking_number = null;
        $replacement->shipped_at = null;
        $replacement->delivered_at = null;
        $replacement->confirmed_at = null;
        $replacement->sms_count = 0;
        $replacement->sms_fee = 0;
        $replacement->used_point_amount = 0;
        $replacement->point_usage_snapshot = ['channel' => 0, 'me9' => 0];
        $replacement->paid_line_total_snapshot = 0;
        $replacement->shipping_amount_snapshot = 0;
        $replacement->stock_deducted_qty = 0;
        $replacement->stock_attribute_id = null;
        $replacement->attribute_stock_deducted_qty = 0;
        $replacement->financial_reversed_at = null;
        $replacement->refund_status = null;
        $replacement->refund_cash_amount = 0;
        $replacement->claim_previous_status = null;
        $replacement->return_shipping_fee = 0;
        $replacement->exchange_shipping_fee = 0;
        $replacement->extra_shipping_fee = 0;
        $exchangeClaim=OrderClaim::where('order_product_id',$item->id)->where('type','exchange')->latest('id')->first();
        $replacement->recipient_name=$exchangeClaim?->exchange_recipient_name;
        $replacement->recipient_mobile=$exchangeClaim?->exchange_recipient_mobile;
        $replacement->recipient_address=$exchangeClaim?->exchange_recipient_address;
        $replacement->setStatus(OrderItemStatus::READY_TO_SHIP);
        $replacement->save();

        return $replacement;
    }

    private function logItemStatus(OrdersProduct $item): void
    {
        $log = new \App\Models\OrdersLog;
        $log->order_id = $item->order_id;
        $log->order_item_id = $item->id;
        $log->order_status = $item->status_label;
        $log->actor = Auth::guard('admin')->user()?->name ?? '채널관리자';
        $log->save();
    }

    private function pickupRules(): array
    {
        return [
            'pickup_method' => 'nullable|in:manual,automatic',
            'return_address' => 'required_if:pickup_method,automatic|nullable|string|max:1000',
            'pickup_name' => 'required_if:pickup_method,automatic|nullable|string|max:100',
            'pickup_mobile' => 'required_if:pickup_method,automatic|nullable|string|max:30',
            'customer_courier_name' => 'required_if:pickup_method,manual|nullable|string|max:100',
            'customer_tracking_number' => 'nullable|string|max:100',
            'claim_shipping_fee' => 'nullable|integer|min:0|max:10000000',
        ];
    }

    private function pickupValues(array $data): array
    {
        return [
            'pickup_method' => $data['pickup_method'] ?? null,
            'return_address' => $data['return_address'] ?? null,
            'pickup_name' => $data['pickup_name'] ?? null,
            'pickup_mobile' => $data['pickup_mobile'] ?? null,
            'customer_courier_name' => $data['customer_courier_name'] ?? null,
            'customer_tracking_number' => $data['customer_tracking_number'] ?? null,
        ];
    }

    private function claimFeeForItem($items, OrdersProduct $item, array $data): int
    {
        $fee = (int) ($data['claim_shipping_fee'] ?? 0);
        $cash = $items->mapWithKeys(fn ($line) => [$line->id => (int) app(\App\Services\SettlementCalculator::class)->paymentAmounts($line)['cash']]);
        if ($fee > $cash->sum()) {
            throw ValidationException::withMessages(['claim_shipping_fee' => '배송비가 선택 상품의 결제금액을 초과합니다.']);
        }
        // Allocate one total fee deterministically across selected lines without duplicate deductions.
        $remaining = $fee;
        foreach ($items as $line) {
            $allocated = min($remaining, $cash[$line->id]);
            if ($line->id === $item->id) return $allocated;
            $remaining -= $allocated;
        }
        return 0;
    }

    private function currentVendorId(): ?int
    {
        $admin = Auth::guard('admin')->user();

        return $admin?->vendor_id ? (int) $admin->vendor_id : null;
    }

    private function vendorItems(array $itemIds, int $vendorId, int $orderId)
    {
        return OrdersProduct::whereIn('id', $itemIds)
            ->with(['order', 'shopChannel'])
            ->where('vendor_id', $vendorId)
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function containsAllRequestedItems($items, array $itemIds): bool
    {
        return $items->count() === count(array_unique(array_map('intval', $itemIds)));
    }

    private function applyStatusTimestamps(OrdersProduct $item, string $status): void
    {
        if ($status === OrderItemStatus::SHIPPING && ! $item->shipped_at) {
            $item->shipped_at = now();
        }

        if ($status === OrderItemStatus::DELIVERED && ! $item->delivered_at) {
            $item->delivered_at = now();
        }

        if ($status === OrderItemStatus::CONFIRMED && ! $item->confirmed_at) {
            $item->confirmed_at = now();
        }
    }

    private function handleStatusSms(int $vendorId, string $status, $items): void
    {
        $this->sendTemplateStatusSms($status, $items);
    }

    private function sendTemplateStatusSms(string $status, $items): void
    {
        $type = match ($status) {
            OrderItemStatus::CONFIRMED => ShopChannelSmsService::TYPE_PURCHASE_CONFIRMED,
            OrderItemStatus::CANCELLED => ShopChannelSmsService::TYPE_CANCEL,
            OrderItemStatus::RETURNED, OrderItemStatus::RETURN_REQUESTED => ShopChannelSmsService::TYPE_RETURN,
            default => null,
        };

        if (! $type) {
            return;
        }

        foreach ($items as $item) {
            if ($item->shopChannel && $item->order) {
                app(ShopChannelSmsService::class)->send($item->shopChannel, $item->order, $item, $type);
            }
        }
    }
}
