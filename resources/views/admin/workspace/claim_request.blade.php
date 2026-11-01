
@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>{{ ['cancel'=>'취소','return'=>'반품','exchange'=>'교환'][$operation] }} 요청</h1><section class="admin-panel"><h2>{{ $item->product_name }} / {{ $item->product_size }} / {{ $item->product_qty }}개</h2><form method="post" action="{{ route('admin.order.workspace.process',[$order->id,$operation]) }}" data-admin-ajax data-success-url="{{ url('admin/orders/'.$order->id.'?tab=claims') }}">
@csrf<input type="hidden" name="item_ids[]" value="{{ $item->id }}"><label class="admin-field">요청 사유<input name="reason" maxlength="1000" required></label><label class="admin-field">상세 사유<textarea name="detail_reason" maxlength="3000" required></textarea></label>
@if($operation!=='cancel')<div class="admin-grid cols-2"><label class="admin-field">회수방법<select name="pickup_method"><option value="automatic">자동회수</option><option value="manual">수동회수</option></select></label><label class="admin-field">회수 담당자<input name="pickup_name" maxlength="100" value="{{ $item->recipient_name ?? $order->name }}"></label><label class="admin-field">회수 연락처<input name="pickup_mobile" maxlength="30" value="{{ $item->recipient_mobile ?? $order->mobile }}"></label><label class="admin-field">회수지 주소<input name="return_address" maxlength="1000" value="{{ $item->recipient_address ?? $order->address }}"></label><label class="admin-field">고객 발송 택배사<input name="customer_courier_name" maxlength="100" placeholder="수동회수 시 입력"></label><label class="admin-field">고객 발송 송장번호<input name="customer_tracking_number" maxlength="100"></label><label class="admin-field">회수 배송비<input type="number" name="claim_shipping_fee" value="0" min="0" max="10000000"></label></div>
@endif
@if($operation==='exchange')<div class="admin-grid cols-2"><label class="admin-field">교환 옵션<select name="exchange_options[{{ $item->id }}]"><option value="">동일 옵션</option>
@foreach($item->product?->attributes??[] as $attribute)
@if($attribute->status && abs((float)$attribute->price_delta-(float)$item->option_price_adjustment)<0.001)<option value="{{ $attribute->size }}">{{ $attribute->size }}</option>
@endif
@endforeach</select></label><label class="admin-field">재배송 수령인<input name="exchange_recipient_name" maxlength="100" value="{{ $item->recipient_name ?? $order->name }}"></label><label class="admin-field">재배송 연락처<input name="exchange_recipient_mobile" maxlength="30" value="{{ $item->recipient_mobile ?? $order->mobile }}"></label><label class="admin-field">재배송 주소<input name="exchange_recipient_address" maxlength="1000" value="{{ $item->recipient_address ?? $order->address }}"></label></div>
@endif
<div class="admin-modal-footer"><a class="admin-btn" href="{{ url('admin/orders/'.$order->id.'?tab=claims') }}">취소</a><button type="submit" class="admin-btn primary">요청 등록</button></div></form></section></div>
@endsection
