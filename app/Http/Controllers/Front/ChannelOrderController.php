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

                $items = $this->vendorItems($data['item_ids'], $vendor_id, (int) $data['order_id']);
                if (! $this->containsAllRequestedItems($items, $data['item_ids'])) {
                    return response()->json(['status' => false, 'message' => '선택된 상품이 없거나 권한이 없습니다.']);
                }

                foreach ($items as $item) {
                    $item->setStatus($status);

                    if ($status === OrderItemStatus::SHIPPING) {
                        $item->courier_name = $data['courier_name'];
                        $item->tracking_number = $data['tracking_number'];
                    }

                    $this->applyStatusTimestamps($item, $status);
                    $item->save();
                }

                $this->handleStatusSms($vendor_id, $status, $items);

                // Check if all items in order are shipped, then maybe update main order status?
                // For now, we stick to item status updates.

                return response()->json(['status' => true, 'message' => '주문 상품 상태가 업데이트되었습니다.']);

            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: '.$e->getMessage()]);
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
                'item_ids' => 'required|array',
                'reason' => 'required|string',
                'detail_reason' => 'required|string',
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
                    // Check if claim already exists for this item to avoid duplicates (optional, strictly speaking)
                    // For now, allow multiple claims if previous one was rejected or cancelled, but here we assume simple flow.

                    // Create Claim
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

                    // 판매자 화면의 취소 처리는 요청 접수와 동시에 취소 완료 상태로 마감한다.
                    $item->setStatus(OrderItemStatus::CANCELLED);
                    $this->applyStatusTimestamps($item, OrderItemStatus::CANCELLED);
                    $item->save();
                }

                DB::commit();
                $this->handleStatusSms($vendor_id, OrderItemStatus::CANCELLED, $items);

                return response()->json(['status' => true, 'message' => '취소 처리가 완료되었습니다.']);

            } catch (\Exception $e) {
                DB::rollback();

                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: '.$e->getMessage()]);
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
                'item_ids' => 'required|array',
                'reason' => 'required|string',
                // 'detail_reason' => 'required|string', // View might need to send this
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
                    OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0,
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'return',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'] ?? $data['reason'], // Use detailed reason if available
                        'status' => 'requested',
                    ]);

                    $item->setStatus(OrderItemStatus::RETURN_REQUESTED);
                    $this->applyStatusTimestamps($item, OrderItemStatus::RETURN_REQUESTED);
                    $item->save();
                }

                DB::commit();
                $this->handleStatusSms($vendor_id, OrderItemStatus::RETURN_REQUESTED, $items);

                return response()->json(['status' => true, 'message' => '반품 요청이 접수되었습니다.']);
            } catch (\Exception $e) {
                DB::rollback();

                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: '.$e->getMessage()]);
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
                'item_ids' => 'required|array',
                'reason' => 'required|string',
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
                    OrderClaim::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? 0,
                        'vendor_id' => $vendor_id,
                        'order_product_id' => $item->id,
                        'type' => 'exchange',
                        'reason' => $data['reason'],
                        'detail_reason' => $data['detail_reason'] ?? $data['reason'],
                        'status' => 'requested',
                    ]);

                    $item->setStatus(OrderItemStatus::EXCHANGE_REQUESTED);
                    $this->applyStatusTimestamps($item, OrderItemStatus::EXCHANGE_REQUESTED);
                    $item->save();
                }

                DB::commit();

                return response()->json(['status' => true, 'message' => '교환 요청이 접수되었습니다.']);
            } catch (\Exception $e) {
                DB::rollback();

                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: '.$e->getMessage()]);
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
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:orders_products,id',
            'action' => 'required|in:cancel_approve,cancel_reject,return_receive,return_complete,return_hold,return_withdraw,return_invoice,exchange_approve,exchange_hold_before,exchange_withdraw,exchange_receive,exchange_complete,exchange_hold_after,exchange_to_return,exchange_option,exchange_invoice',
            'reason' => 'nullable|string|max:1000',
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
                    ->lockForUpdate()
                    ->get();

                if ($items->count() !== count(array_unique($data['item_ids']))) {
                    throw ValidationException::withMessages(['item_ids' => '선택한 주문상품 중 권한이 없는 항목이 있습니다.']);
                }

                foreach ($items as $item) {
                    $this->applyClaimAction($item, $data, $vendorId);
                }

                return $items;
            });

            if (in_array($data['action'], ['return_complete', 'exchange_to_return'], true)) {
                $this->handleStatusSms($vendorId, OrderItemStatus::RETURNED, $items);
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
        }
    }

    private function applyClaimAction(OrdersProduct $item, array $data, int $vendorId): void
    {
        $action = $data['action'];
        $current = $item->normalized_status;
        $definition = $this->claimActionDefinitions()[$action];

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
        }

        if ($action === 'exchange_option') {
            $item->product_size = $data['option'];
        }

        $isMetadataOnly = in_array($action, ['return_invoice', 'exchange_option', 'exchange_invoice'], true);
        $targetStatus = $isMetadataOnly ? $current : $definition['to'];
        $item->setStatus($targetStatus);
        $item->save();

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
            OrderClaim::firstOrCreate(
                [
                    'order_id' => $item->order_id,
                    'order_product_id' => $item->id,
                    'vendor_id' => $vendorId,
                    'type' => 'return',
                ],
                [
                    'user_id' => $item->user_id ?: 0,
                    'reason' => '교환에서 반품으로 전환',
                    'detail_reason' => $data['reason'] ?? null,
                    'status' => 'completed',
                    'admin_comment' => '['.now()->format('Y-m-d H:i:s').'] 교환 클레임에서 반품완료로 전환',
                ]
            );
        }
    }

    private function claimActionDefinitions(): array
    {
        return [
            'cancel_approve' => ['from' => [OrderItemStatus::CANCEL_REQUESTED], 'to' => OrderItemStatus::CANCELLED, 'claim_status' => 'completed', 'label' => '취소 승인'],
            'cancel_reject' => ['from' => [OrderItemStatus::CANCEL_REQUESTED], 'to' => OrderItemStatus::PAID, 'claim_status' => 'rejected', 'label' => '취소 거절'],
            'return_receive' => ['from' => [OrderItemStatus::RETURN_REQUESTED, OrderItemStatus::RETURN_HOLD], 'to' => OrderItemStatus::RETURN_RECEIVED, 'claim_status' => 'received', 'label' => '반품 회수 완료'],
            'return_complete' => ['from' => [OrderItemStatus::RETURN_RECEIVED, OrderItemStatus::RETURN_HOLD], 'to' => OrderItemStatus::RETURNED, 'claim_status' => 'completed', 'label' => '반품 확정'],
            'return_hold' => ['from' => [OrderItemStatus::RETURN_REQUESTED, OrderItemStatus::RETURN_RECEIVED], 'to' => OrderItemStatus::RETURN_HOLD, 'claim_status' => 'held', 'label' => '반품 보류'],
            'return_withdraw' => ['from' => [OrderItemStatus::RETURN_REQUESTED, OrderItemStatus::RETURN_HOLD], 'to' => OrderItemStatus::SHIPPING, 'claim_status' => 'withdrawn', 'label' => '반품 철회'],
            'return_invoice' => ['from' => [OrderItemStatus::RETURN_REQUESTED, OrderItemStatus::RETURN_RECEIVED, OrderItemStatus::RETURN_HOLD], 'to' => OrderItemStatus::RETURN_REQUESTED, 'claim_status' => 'requested', 'label' => '반품 송장 수정'],
            'exchange_approve' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED, OrderItemStatus::EXCHANGE_HOLD_BEFORE], 'to' => OrderItemStatus::EXCHANGE_APPROVED, 'claim_status' => 'approved', 'label' => '교환 승인'],
            'exchange_hold_before' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED, OrderItemStatus::EXCHANGE_APPROVED], 'to' => OrderItemStatus::EXCHANGE_HOLD_BEFORE, 'claim_status' => 'held_before', 'label' => '교환 회수 전 보류'],
            'exchange_withdraw' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED, OrderItemStatus::EXCHANGE_APPROVED, OrderItemStatus::EXCHANGE_HOLD_BEFORE], 'to' => OrderItemStatus::DELIVERED, 'claim_status' => 'withdrawn', 'label' => '교환 철회'],
            'exchange_receive' => ['from' => [OrderItemStatus::EXCHANGE_APPROVED, OrderItemStatus::EXCHANGE_HOLD_BEFORE], 'to' => OrderItemStatus::EXCHANGE_RECEIVED, 'claim_status' => 'received', 'label' => '교환 회수 완료'],
            'exchange_complete' => ['from' => [OrderItemStatus::EXCHANGE_RECEIVED, OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::EXCHANGED, 'claim_status' => 'completed', 'label' => '교환 확정'],
            'exchange_hold_after' => ['from' => [OrderItemStatus::EXCHANGE_RECEIVED], 'to' => OrderItemStatus::EXCHANGE_HOLD_AFTER, 'claim_status' => 'held_after', 'label' => '교환 회수 후 보류'],
            'exchange_to_return' => ['from' => [OrderItemStatus::EXCHANGE_RECEIVED, OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::RETURNED, 'claim_status' => 'converted_to_return', 'label' => '반품 전환'],
            'exchange_option' => ['from' => [OrderItemStatus::EXCHANGE_REQUESTED, OrderItemStatus::EXCHANGE_APPROVED, OrderItemStatus::EXCHANGE_HOLD_BEFORE, OrderItemStatus::EXCHANGE_RECEIVED, OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::EXCHANGE_REQUESTED, 'claim_status' => 'requested', 'label' => '교환 옵션 변경'],
            'exchange_invoice' => ['from' => [OrderItemStatus::EXCHANGE_APPROVED, OrderItemStatus::EXCHANGE_RECEIVED, OrderItemStatus::EXCHANGE_HOLD_AFTER], 'to' => OrderItemStatus::EXCHANGE_APPROVED, 'claim_status' => 'approved', 'label' => '교환 송장 수정'],
        ];
    }

    private function createExchangeReplacement(OrdersProduct $item): OrdersProduct
    {
        if ($item->exchangeReplacement) {
            return $item->exchangeReplacement;
        }

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
        $replacement->setStatus(OrderItemStatus::READY_TO_SHIP);
        $replacement->save();

        return $replacement;
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
        $this->debitLegacyStatusSmsPoints($vendorId, $status, $items);
        $this->sendTemplateStatusSms($status, $items);
    }

    private function debitLegacyStatusSmsPoints(int $vendorId, string $status, $items): void
    {
        if (! in_array($status, [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED], true)) {
            return;
        }

        $itemsByChannel = $items
            ->filter(fn ($item) => ! empty($item->shop_channel_id))
            ->groupBy('shop_channel_id');

        foreach ($itemsByChannel as $shopChannelId => $channelItems) {
            $transaction = app(ChannelPointService::class)->recordSmsDebit(
                $vendorId,
                1,
                20,
                (int) $shopChannelId,
                OrderItemStatus::label($status).' 안내 문자 발송'
            );

            if ($transaction) {
                $item = $channelItems->first();
                $item->sms_count = (int) $item->sms_count + 1;
                $item->sms_fee = (int) $item->sms_fee + abs((int) $transaction->points);
                $item->save();
            }
        }
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
