@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>원상품 배송·환불 정책</h1>
@include('admin.shared.messages')
@if($previewUrl)
<div class="admin-toolbar"><a class="admin-btn" href="{{ $previewUrl }}" target="_blank" rel="noopener">Shop 상품 보기</a></div>
@endif
<section class="admin-panel" style="max-width:900px"><h2>{{ $product->product_name }}</h2><p class="admin-help">상품코드 {{ $product->product_code }} · 판매자 #{{ $product->vendor_id }}</p>
<form method="post">@csrf
<label class="admin-field">배송비 정책<select name="shipping_policy_id"><option value="">개별 배송 설정 사용</option>
@foreach($deliveryPolicies as $policy)
<option value="{{ $policy->id }}" @selected((string)old('shipping_policy_id',$product->shipping_policy_id)===(string)$policy->id) @disabled(!$policy->status)>{{ $policy->name }}{{ $policy->status?'':' (중지)' }}</option>
@endforeach
</select></label><p class="admin-help">Shop 상품에 별도 배송비 정책이 선택되어 있으면 Shop 관리자 정책을 우선 적용합니다. 이 화면은 아직 별도 정책을 선택하지 않은 상품의 원상품 배송 기준을 관리합니다.</p>
<label class="admin-field">취소/환불 안내<select name="cancel_refund_policy_id"><option value="">Me9 기본 안내</option>
@foreach($refundPolicies as $policy)
<option value="{{ $policy->id }}" @selected((string)old('cancel_refund_policy_id',$product->cancel_refund_policy_id)===(string)$policy->id) @disabled($policy->status!=='active')>{{ $policy->name }}{{ $policy->status==='active'?'':' (미사용)' }}</option>
@endforeach
</select></label><p class="admin-help">해당 판매자의 사용 중인 정책만 선택할 수 있습니다. 기존 주문의 결제금액·배송비 기록은 유지됩니다.</p>
<div class="admin-modal-footer"><a class="admin-btn" href="{{ route('admin.products') }}">목록</a><button class="admin-btn primary" type="submit">정책 저장</button></div>
</form></section></div>
@endsection
