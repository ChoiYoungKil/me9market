@extends('layouts.channel')
@section('page_type','sub')
@php($dep1_id='00')
@push('styles')
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/delivery-policies.css?v={{ filemtime(public_path('channel_assets/css/delivery-policies.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace delivery-workspace" data-delivery-active-form="{{ $activeForm }}">
<div class="order-page-heading"><div><p>환경설정</p><h1>배송비 설정 관리</h1></div><button class="primary" type="button" data-delivery-open="delivery-create">배송비 등록</button></div>
@if(session('flash_message_success'))<section class="order-panel" role="status">{{ session('flash_message_success') }}</section>@endif
@if($errors->any())<section class="order-panel delivery-error" role="alert">{{ $errors->first() }}</section>@endif
<section class="order-panel"><form action="{{ route('channel.delivery.list') }}" method="GET" class="order-search-row"><label class="grow">배송비 명칭<input name="keyword" value="{{ $keyword }}" placeholder="배송비 명칭 검색"></label><button class="primary" type="submit">검색</button><a class="order-button" href="{{ route('channel.delivery.list') }}">초기화</a></form></section>
<section class="order-panel"><div class="order-toolbar"><h2>배송비 설정 목록</h2><p>총 <strong>{{ $charges->total() }}</strong> 건</p></div>
<div class="order-table-scroll" tabindex="0" role="region" aria-label="배송비 설정 목록"><table class="order-table"><thead><tr><th>배송구분</th><th>상태</th><th>배송비 명칭</th><th>지정택배사</th><th>배송비 유형</th><th>배송비 결제</th><th>연결 상품수</th><th>관리</th></tr></thead><tbody>
@forelse($charges as $charge)<tr><td>사용자</td><td><span class="order-state">{{ $charge->status ? '사용' : '중지' }}</span></td><td>{{ $charge->name }}</td><td>{{ $charge->courier ?: '자체배송' }}</td><td>{{ $shippingTypes[$charge->shipping_type] ?? $charge->shipping_type }}</td><td>{{ $paymentTypes[$charge->payment_type] ?? $charge->payment_type }}</td><td>{{ number_format($charge->linked_product_count) }}</td><td><div class="delivery-actions"><a class="order-button" href="{{ route('channel.delivery.show',$charge->id) }}" data-delivery-view>보기</a><button type="button" data-delivery-open="delivery-edit-{{ $charge->id }}">수정</button><form method="POST" action="{{ route('channel.delivery.copy',$charge->id) }}">@csrf<button type="submit">복사</button></form><form method="POST" action="{{ route('channel.delivery.delete',$charge->id) }}" data-delivery-delete>@csrf<button type="submit" @disabled($charge->linked_product_count > 0) title="{{ $charge->linked_product_count > 0 ? '상품에서 사용 중인 정책은 삭제할 수 없습니다.' : '배송비 설정 삭제' }}">삭제</button></form></div></td></tr>
@empty<tr><td colspan="8" class="order-empty">등록된 배송비 설정이 없습니다.</td></tr>@endforelse
</tbody></table></div>{{ $charges->links() }}</section></div></div>
<dialog id="delivery-create" class="order-dialog delivery-dialog" aria-labelledby="delivery-create-title"><div class="order-dialog-heading"><h2 id="delivery-create-title">배송비 등록</h2><button type="button" data-delivery-close>창닫기</button></div><form method="POST" action="{{ route('channel.delivery.store') }}" data-delivery-form data-preview-url="{{ route('channel.delivery.preview') }}">@csrf @include('channel.sub00.inc.channel_delivery_form',['charge'=>null])</form></dialog>
@foreach($charges as $charge)<dialog id="delivery-edit-{{ $charge->id }}" class="order-dialog delivery-dialog" aria-labelledby="delivery-edit-title-{{ $charge->id }}"><div class="order-dialog-heading"><h2 id="delivery-edit-title-{{ $charge->id }}">배송비 수정</h2><button type="button" data-delivery-close>창닫기</button></div><form method="POST" action="{{ route('channel.delivery.update',$charge->id) }}" data-delivery-form data-preview-url="{{ route('channel.delivery.preview') }}">@csrf @include('channel.sub00.inc.channel_delivery_form',['charge'=>$charge])</form></dialog>@endforeach
<dialog id="delivery-view" class="order-dialog delivery-dialog" aria-labelledby="delivery-view-title"><div class="order-dialog-heading"><h2 id="delivery-view-title">배송비 정보</h2><button type="button" data-delivery-close>창닫기</button></div><div data-delivery-details aria-live="polite"></div></dialog>
@endsection
@push('scripts')<script src="/channel_assets/js/delivery-policies.js?v={{ filemtime(public_path('channel_assets/js/delivery-policies.js')) }}" defer></script>@endpush
