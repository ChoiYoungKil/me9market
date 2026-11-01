
@if($tab==='info')
<div class="admin-grid cols-2"><section class="admin-panel"><h2>주문자 정보</h2><dl class="admin-detail-grid"><dt>회원번호</dt><dd>{{ $order->user_id ?: '비회원' }}</dd><dt>주문자 이름</dt><dd>{{ $order->buyer_name ?: $order->name }}</dd><dt>연락처</dt><dd>{{ $order->buyer_mobile ?: $order->mobile }}</dd><dt>이메일</dt><dd>{{ $order->email }}</dd><dt>주문일시</dt><dd>{{ $order->created_at?->format('Y-m-d H:i:s') }}</dd></dl></section><section class="admin-panel"><h2>주문서 배송 정보</h2><dl class="admin-detail-grid"><dt>수령인</dt><dd>{{ $order->name }}</dd><dt>연락처</dt><dd>{{ $order->mobile }}</dd><dt>배송지</dt><dd>{{ $order->pincode }} {{ $order->address }} {{ $order->address_detail }}</dd><dt>배송 메모</dt><dd>{{ $order->delivery_memo ?: '-' }}</dd></dl><p class="admin-help">발주사에서 변경한 상품별 배송지는 상품·배송 탭에 표시됩니다.</p></section></div>
<section class="admin-panel"><h2>결제 정보</h2><dl class="admin-detail-grid"><dt>결제수단</dt><dd>{{ $order->payment_method }}</dd><dt>결제서비스</dt><dd>{{ $order->payment_gateway }}</dd><dt>주문 결제금액</dt><dd>{{ number_format($order->grand_total) }}원</dd><dt>배송비</dt><dd>{{ number_format($order->shipping_charges) }}원</dd><dt>쿠폰</dt><dd>{{ $order->coupon_code ?: '-' }} / {{ number_format($order->coupon_amount) }}원</dd></dl><div class="admin-table-wrap" style="margin-top:20px"><table class="admin-table"><thead><tr><th>상품</th><th>상품 상태</th><th>환불 상태</th><th>현금 환불금액</th><th>사용 포인트</th><th>배송정보</th></tr></thead><tbody>
@foreach($order->orders_products as $item)<tr><td class="text-left">{{ $item->product_name }}</td><td>{{ $item->status_label }}</td><td>{{ $item->refund_status_label }}</td><td>{{ number_format($item->refund_cash_amount) }}원</td><td>{{ number_format($item->used_point_amount) }}P</td><td>{{ $item->courier_name }} {{ $item->tracking_number }}<br>{{ $item->recipient_name ?? $order->name }} / {{ $item->recipient_postcode ?? $order->pincode }} {{ $item->recipient_address ?? $order->address }} {{ $item->recipient_address_detail ?? $order->address_detail }}</td></tr>
@endforeach</tbody></table></div><p class="admin-help">‘모의 환불 반영’과 ‘PG 환불 대기’를 구분합니다. 외부 결제기관의 환불 완료를 임의로 표시하지 않습니다.</p></section>
@elseif($tab==='products')
@foreach($order->orders_products as $item)<section class="admin-panel" style="margin-bottom:20px"><div class="admin-toolbar"><h2>{{ $item->product_name }}</h2><span class="admin-status">{{ $item->status_label }}</span></div><dl class="admin-detail-grid"><dt>주문상품번호 / 코드</dt><dd>#{{ $item->id }} / {{ $item->product_code }}</dd><dt>채널 / 판매자번호</dt><dd>{{ $item->shopChannel?->channel_name ?: '-' }} / {{ $item->vendor_id }}</dd><dt>옵션 / 수량</dt><dd>{{ $item->product_color }} / {{ $item->product_size }} / {{ $item->product_qty }}개</dd><dt>수령인 / 연락처</dt><dd>{{ $item->recipient_name ?? $order->name }} / {{ $item->recipient_mobile ?? $order->mobile }}</dd><dt>배송지</dt><dd>{{ $item->recipient_postcode ?? $order->pincode }} {{ $item->recipient_address ?? $order->address }} {{ $item->recipient_address_detail ?? $order->address_detail }}</dd><dt>택배사 / 송장번호</dt><dd>{{ $item->courier_name ?: '-' }} / {{ $item->tracking_number ?: '-' }}</dd><dt>발송 / 배송완료</dt><dd>{{ $item->shipped_at?->format('Y-m-d H:i:s') ?: '-' }} / {{ $item->delivered_at?->format('Y-m-d H:i:s') ?: '-' }}</dd>
@if($item->is_exchange_replacement)<dt>교환 재발송</dt><dd>원상품 #{{ $item->replacement_for_order_product_id }} / 추가 상품대금 없음</dd>
@endif</dl>
@php($shippingTargets = match($item->normalized_status) {
    'paid' => ['ready_to_ship' => '배송대기', 'shipping' => '배송중'],
    'ready_to_ship' => ['ready_to_ship' => '배송대기', 'shipping' => '배송중'],
    'shipping' => ['shipping' => '배송중(송장 수정)', 'delivered' => '배송완료'],
    default => [],
})
@if($shippingTargets)
<form method="post" action="{{ url('admin/update-order-item-status') }}" style="margin-top:20px">
@csrf<input type="hidden" name="order_item_id" value="{{ $item->id }}"><div class="admin-filter"><label>배송 처리<select name="order_item_status" required><option value="">선택</option>
@foreach($shippingTargets as $code=>$label)<option value="{{ $code }}">{{ $label }}</option>
@endforeach</select></label><label>택배사<input name="item_courier_name" maxlength="100" value="{{ $item->courier_name }}"></label><label>송장번호<input name="item_tracking_number" maxlength="100" value="{{ $item->tracking_number }}"></label><button type="submit" class="admin-btn primary">배송정보 적용</button></div></form>
@else<p class="admin-help">현재 상태에서는 배송정보를 변경할 수 없습니다.</p>
@if($item->normalized_status==='delivered')
<form method="post" action="{{ url('admin/update-order-item-status') }}">
@csrf
<input type="hidden" name="order_item_id" value="{{ $item->id }}"><input type="hidden" name="order_item_status" value="confirmed"><button type="submit" class="admin-btn primary">구매확정 처리</button>
</form>
@endif
@endif</section>
@endforeach
@elseif($tab==='claims')
@foreach($order->orders_products as $item)<section class="admin-panel" style="margin-bottom:22px"><div class="admin-toolbar"><h2>{{ $item->product_name }} · #{{ $item->id }}</h2><span class="admin-status">{{ $item->status_label }}</span></div>
@forelse($item->claims->sortByDesc('id') as $claim)<div class="admin-claim"><h3>{{ ['cancel'=>'취소','return'=>'반품','exchange'=>'교환'][$claim->type] ?? $claim->type }} 신청 · {{ $claim->status==='requested'?'접수 대기':$claim->status_label }}</h3><dl class="admin-detail-grid"><dt>신청일시</dt><dd>{{ $claim->created_at?->format('Y-m-d H:i:s') }}</dd><dt>사유</dt><dd>{{ $claim->reason }}<br>{{ $claim->detail_reason }}</dd><dt>회수방법</dt><dd>{{ ['automatic'=>'자동회수','manual'=>'수동회수'][$claim->pickup_method] ?? '-' }}</dd><dt>회수지 / 담당자</dt><dd>{{ $claim->return_address }} / {{ $claim->pickup_name }} {{ $claim->pickup_mobile }}</dd><dt>회수 송장</dt><dd>{{ $claim->customer_courier_name }} / {{ $claim->customer_tracking_number }}</dd><dt>처리 내역</dt><dd class="admin-preserve-lines">{{ $claim->admin_comment ?: '처리 대기' }}</dd></dl></div>
@empty<p class="admin-help">접수된 취소·반품·교환 요청이 없습니다.</p>
@endforelse
@if($item->exchangeReplacement)<p class="admin-alert">재발송 상품 #{{ $item->exchangeReplacement->id }} / {{ $item->exchangeReplacement->status_label }} / {{ $item->exchangeReplacement->courier_name }} {{ $item->exchangeReplacement->tracking_number }}</p>
@endif
@php($actions=collect(\App\Support\OrderClaimActions::definitions())->filter(fn($definition)=>in_array($item->normalized_status,$definition['from'],true)))
@if($actions->isNotEmpty())<form method="post" action="{{ route('admin.order.workspace.process',[$order->id,'action']) }}" data-admin-ajax data-reload data-claim-form>
@csrf<input type="hidden" name="item_ids[]" value="{{ $item->id }}"><div class="admin-filter" style="margin-top:18px"><label>처리 선택<select name="action" required data-claim-action><option value="">선택해 주세요</option>
@foreach($actions as $code=>$definition)<option value="{{ $code }}">{{ $definition['label'] }}</option>
@endforeach</select></label><label data-claim-field="reason" hidden>보류 사유<input name="reason" maxlength="1000"></label><label data-claim-field="invoice" hidden>택배사<input name="courier_name" maxlength="100"></label><label data-claim-field="invoice" hidden>송장번호<input name="tracking_number" maxlength="100"></label><label data-claim-field="option" hidden>교환 옵션<select name="option"><option value="">선택</option>
@foreach($item->product?->attributes??[] as $attribute)
@if($attribute->status)<option value="{{ $attribute->size }}">{{ $attribute->size }}</option>
@endif
@endforeach</select></label><button class="admin-btn primary" type="submit">선택 처리 적용</button></div></form>
@endif
@php($requestActions = collect(['cancel'=>'취소 요청','return'=>'반품 요청','exchange'=>'교환 요청'])->filter(fn($label,$operation)=>\App\Support\OrderItemStatus::customerActionAllowed($operation,$item->normalized_status)))
@if($requestActions->isNotEmpty())
<details style="margin-top:18px"><summary>새 취소·반품·교환 요청</summary><div class="admin-actions" style="margin-top:16px">
@foreach($requestActions as $operation=>$label)
<a class="admin-btn" href="{{ route('admin.order.workspace.request',[$order->id,$item->id,$operation]) }}">{{ $label }}</a>
@endforeach</div></details>
@endif
</section>
@endforeach
@elseif($tab==='history')
<section class="admin-panel"><h2>처리 이력</h2><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>시각</th><th>상품번호</th><th>처리 상태</th><th>담당자</th></tr></thead><tbody>
@forelse($order->statusLogs as $log)<tr><td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td><td>{{ $log->order_item_id }}</td><td>{{ $log->order_status }}</td><td>{{ $log->actor ?: '이전 기록(담당자 미기록)' }}</td></tr>
@empty<tr><td colspan="4" class="admin-empty">처리 이력이 없습니다.</td></tr>
@endforelse</tbody></table></div></section>
@endif
