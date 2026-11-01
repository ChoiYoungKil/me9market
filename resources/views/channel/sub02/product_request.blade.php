@extends('layouts.channel')
@section('page_type','sub')
@php $dep1_id='02'; @endphp
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}?v={{ filemtime(public_path('channel_assets/css/shop-configuration.css')) }}">@endpush
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/catalog-settings.css') }}?v={{ filemtime(public_path('channel_assets/css/catalog-settings.css')) }}">@endpush
@section('content')
<div id="contents" class="channel-product-list-page"><div class="channel-shop" id="shop-sales-requests">
@include('channel.sub02.partials.product_heading', ['catalogTitle' => '판매 요청 관리'])
<section class="shop-panel">
<form method="GET" class="sales-request-search" role="search" aria-label="판매 요청 검색">
    <div class="sales-request-search-fields">
        <label for="sales-request-keyword">상품명 또는 상품코드<input id="sales-request-keyword" name="q" value="{{ $filters['q'] }}" maxlength="150"></label>
        <label for="sales-request-requester">요청자<input id="sales-request-requester" name="requester" value="{{ $filters['requester'] }}" maxlength="150"></label>
    </div>
    <div class="sales-request-search-bottom">
        <fieldset class="catalog-status-group sales-request-status">
            <legend>요청상태</legend>
            @foreach([''=>'전체','0'=>'대기','1'=>'허용','2'=>'거부'] as $key=>$value)
                <label><input type="radio" name="request_status" value="{{ $key }}" @checked((string)$filters['request_status']===(string)$key)>{{ $value }}</label>
            @endforeach
        </fieldset>
        <div class="sales-request-search-actions">
            <button type="submit">검색</button>
            <a href="{{ route('channel.product_request') }}" class="reset-search">초기화</a>
        </div>
    </div>
</form><div class="catalog-toolbar"><h2>총 {{ $requests->total() }}건</h2><div class="field-inline"><button type="button" data-batch-review="1">선택 판매승인</button><button type="button" data-batch-review="2">선택 승인거절</button></div></div><p class="shop-feedback error" data-batch-error hidden role="alert"></p><div class="table-scroll"><table><thead><tr><th><input type="checkbox" aria-label="전체선택" data-select-all-requests></th><th>상품코드</th><th>상품명</th><th>요청자</th><th>신청사유</th><th>신청일시</th><th>상태</th><th>상세</th></tr></thead><tbody>
@forelse($requests as $row)<tr><td><input type="checkbox" aria-label="{{ $row->product?->product_name }} 요청 선택" data-request-check="{{ $row->id }}"></td><td>{{ $row->product?->product_code }}</td><td class="product-name">{{ $row->product?->product_name }}</td><td>{{ $row->shopChannel?->channel_name }}</td><td class="product-name">{{ $row->request_reason }}</td><td>{{ $row->requested_at?->format('Y-m-d H:i') }}</td><td>{{ ['pending'=>'대기','approved'=>'허용','rejected'=>'거부'][$row->approval_status]??'-' }}</td><td><button type="button" data-review-request="{{ $row->id }}">보기</button></td></tr>@empty
@include('components.table-empty-row', ['columns' => 8, 'kind' => 'product'])@endforelse</tbody></table></div>{{ $requests->links() }}</section>
<dialog id="sales-request-dialog"><h2>상품 판매 요청 상세</h2><dl class="shop-details" data-request-information></dl><p class="shop-feedback error" data-request-error hidden role="alert"></p><div class="shop-actions"><button type="button" data-review-status="1">허용</button><button type="button" data-review-status="2">거부</button><button type="button" data-close-request class="secondary">닫기</button></div></dialog>
<script type="application/json" id="sales-request-state">{!! json_encode(['url'=>route('channel.product.request.update'),'rows'=>$requests->getCollection()->map(fn($r)=>['id'=>$r->id,'name'=>$r->product?->product_name,'channel'=>$r->shopChannel?->channel_name,'reason'=>$r->request_reason,'price'=>$r->selling_price,'status'=>$r->approval_status])->values()],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
</div></div>
@endsection
@push('scripts')<script src="{{ asset('channel_assets/js/shop-sales-requests.js') }}" defer></script>@endpush
