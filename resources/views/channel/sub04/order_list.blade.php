@extends('layouts.channel')
@php
    $dep1_id = '04';
    $orderTab = $orderPageType ?? 'list';
    $searchAction = match($orderTab) { 'joint' => route('channel.order.joint_list'), 'cancel' => route('channel.order.cancel_list'), 'return' => route('channel.order.return_list'), 'exchange' => route('channel.order.exchange_list'), default => route('channel.order.list') };
    $tabs = ['list'=>'주문목록','cancel'=>'취소목록','return'=>'반품목록','exchange'=>'교환목록'];
    $detailOpen = request('detail_open') === '1' || !empty($filters['buyer']) || !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['order_statuses']) || ($filters['price_min'] ?? '') !== '' || ($filters['price_max'] ?? '') !== '';
    $payload = $orders->map(fn($order) => array_merge($order->only(['id','order_no','created_at','payment_date','shop_name','user_name','user_id','buyer_name','buyer_mobile','name','mobile','email','recipient_name','recipient_mobile','recipient_zipcode','recipient_address','recipient_address_detail','pincode','address','delivery_memo','payment_method','payment_gateway','total_payment_price','used_point','earned_point']), ['items'=>$order->items->values(), 'claims_data'=>$order->claims_data->values(), 'events'=>$order->events, 'payment_logs'=>$order->payment_logs]));
@endphp
@section('page_type', 'sub')
@push('styles')
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace">
    <div class="order-page-heading"><div><p>주문관리</p><h1>{{ $orderPageTitle }}</h1></div><span>HOME · 주문관리 · {{ $orderPageTitle }}</span></div>
    <section class="order-panel">
        <form method="GET" action="{{ $searchAction }}" id="orderSearchForm">
            <input type="hidden" name="detail_open" value="{{ $detailOpen ? 1 : 0 }}">
            @if(!empty($filters['joint_purchase_id']))<input type="hidden" name="joint_purchase_id" value="{{ $filters['joint_purchase_id'] }}">@endif
            @if(!empty($filters['order_id']))<input type="hidden" name="order_id" value="{{ $filters['order_id'] }}">@endif
            <div class="order-search-row">
                <label>검색구분<select name="search_type">@foreach(['all'=>'전체','order_no'=>'주문번호','product_name'=>'상품명','product_code'=>'상품코드','tracking_number'=>'송장번호'] as $value=>$label)<option value="{{ $value }}" @selected($filters['search_type']===$value)>{{ $label }}</option>@endforeach</select></label>
                <label class="grow">검색어<input name="keyword" value="{{ $filters['keyword'] }}" placeholder="검색어를 입력해 주세요."></label>
                <button type="button" id="order-detail-toggle" aria-controls="order-search-details" aria-expanded="{{ $detailOpen ? 'true' : 'false' }}">상세 {{ $detailOpen ? '⌃' : '⌄' }}</button>
                <button type="submit" class="primary">검색</button><a class="order-button" href="{{ $searchAction }}">초기화</a>
            </div>
            <div id="order-search-details" @if(!$detailOpen) hidden @endif>
                <div class="order-fields"><label>주문기간 시작<input type="date" name="date_from" value="{{ $filters['date_from'] }}"></label><label>주문기간 종료<input type="date" name="date_to" value="{{ $filters['date_to'] }}"></label><label>주문자<input name="buyer" value="{{ $filters['buyer'] }}" placeholder="이름, 이메일, 연락처"></label><label>판매가격 최소 (원)<input type="number" min="0" name="price_min" value="{{ $filters['price_min'] }}"></label><label>판매가격 최대 (원)<input type="number" min="0" name="price_max" value="{{ $filters['price_max'] }}"></label></div>
                <fieldset><legend>주문상태</legend><div class="order-checks">@foreach($orderStatusOptions as $key=>$label)<label><input type="checkbox" name="order_statuses[]" value="{{ $key }}" @checked(in_array($key,$filters['order_statuses'],true))>{{ $label }}</label>@endforeach</div></fieldset>
                <fieldset><legend>주문 유형</legend><div class="order-checks">@foreach([''=>'전체','normal'=>'일반','joint'=>'공동구매'] as $key=>$label)<label><input type="radio" name="order_type" value="{{ $key ?: 'all' }}" @checked($filters['order_type']===$key)>{{ $label }}</label>@endforeach</div></fieldset>
            </div>
        </form>
    </section>
    <section class="order-panel">
        <nav class="order-tabs" aria-label="주문 목록 구분">@foreach($tabs as $key=>$label)<a href="{{ route('channel.order.'.($key==='list'?'list':$key.'_list')) }}" @if($orderTab===$key || ($orderTab==='joint' && $key==='list')) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
        <div class="order-toolbar"><p>총 <strong>{{ $orders->total() }}</strong>건 <small>주문 기준 · 상품별 표시 / 표를 좌우로 이동할 수 있습니다.</small></p><label>목록 개수<select id="order-per-page">@foreach([20,40,60,80,100] as $count)<option value="{{ $count }}" @selected($filters['per_page']===$count)>{{ $count }}개씩 보기</option>@endforeach</select></label></div>
        <div class="order-table-scroll" tabindex="0" aria-label="주문 목록 가로 스크롤"><table class="order-table"><thead><tr>
            @if(in_array($orderTab,['cancel','return','exchange']))<th>{{ ['cancel'=>'취소','return'=>'반품','exchange'=>'교환'][$orderTab] }}주문번호</th><th>요청일시</th><th>철회일시</th><th>거부일시</th><th>완료일시</th>@endif
            <th>주문일시</th><th>결제일시</th><th>주문번호</th><th>순번</th><th>상품등록 ID</th><th>공유판매 ID</th><th>Shop채널</th><th>주문상태</th><th>주문자</th><th>주문 유형</th><th>상품 유형</th><th>상품명</th><th>상품코드</th><th>옵션</th><th>주문수량</th><th>취소수량</th><th>반품수량</th><th>교환수량</th><th>판매형태</th><th>결제비용<br>PG 결제</th><th>결제비용<br>포인트결제</th><th>채널매출<br>상품가</th><th>채널매출<br>배송비</th><th>할인비용</th><th>채널매입<br>상품+수수료</th><th>채널매입<br>배송비</th><th>제공포인트</th>
            @if(in_array($orderTab,['cancel','return','exchange']))<th>상품회수방법</th><th>환불방법</th><th>회수포인트</th><th>회수 택배사</th><th>회수 송장번호</th><th>배송비 차감</th><th>환불금액</th><th>환불상태</th>@endif
        </tr></thead><tbody>
        @forelse($orders as $order)
            @foreach($order->list_items as $item)
            @php $itemClaims=in_array($orderTab,['cancel','return','exchange']) ? $order->claims_data->where('order_product_id',$item['id'])->where('type',$orderTab)->sortByDesc('id') : collect(); if($itemClaims->isEmpty()) $itemClaims=collect([null]); @endphp
            @foreach($itemClaims as $claim)
            <tr>
                @if(in_array($orderTab,['cancel','return','exchange']))<td><button type="button" class="order-link" data-order-open="{{ $order->id }}" data-order-tab="{{ $orderTab }}">{{ $claim ? 'Me9-'.ucfirst($orderTab).'-'.str_pad($claim->id,8,'0',STR_PAD_LEFT) : '-' }}</button></td>@foreach(['requested','withdrawn','rejected','completed'] as $step)<td>{{ $claim?->lifecycle_dates[$step] ?? '-' }}</td>@endforeach
                @endif
                <td>{{ $order->created_at }}</td><td>{{ $order->events->firstWhere('status_label','결제완료')['created_at'] ?? '-' }}</td><td><button type="button" class="order-link" data-order-open="{{ $order->id }}">{{ $order->order_no }}</button></td><td>{{ $loop->parent->iteration }}</td><td>{{ $item['product_id'] }}</td><td>{{ $order->orders_products->firstWhere('id',$item['id'])?->shop_channel_product_id ?? '-' }}</td><td>{{ $item['shop_name'] }}</td><td><span class="order-state">{{ $item['status_label'] }}</span>@if($claim)<small>{{ $claim->status_label }}</small>@endif</td><td>{{ $order->user_name }}</td><td>{{ $item['order_type_label'] }}</td><td>{{ $item['product_type'] }}</td><td class="order-product-name"><button type="button" class="order-link" data-product-open="{{ $order->id }}:{{ $item['id'] }}">{{ $item['product_name'] }}</button>@if(($item['reprice_adjustment_amount']??0)>0)<small>공동구매 재결제 예정: {{ number_format($item['original_line_total']) }}원 → {{ number_format($item['repriced_line_total']) }}원 / 차액 {{ number_format($item['reprice_adjustment_amount']) }}원</small>@endif</td><td>{{ $item['product_code'] }}</td><td>{{ $item['option_name'] }}</td><td>{{ $item['qty'] }}</td><td>{{ str_starts_with($item['status'],'cancel') ? $item['qty'] : 0 }}</td><td>{{ str_starts_with($item['status'],'return') ? $item['qty'] : 0 }}</td><td>{{ str_starts_with($item['status'],'exchange') ? $item['qty'] : 0 }}</td><td>{{ $item['channel_financial']['form'] }}</td>
                @foreach(['paid_cash_amount','used_point_amount','paid_line_total','shipping_amount','discount_amount'] as $amount)<td>{{ number_format($item[$amount]) }} {{ $amount==='used_point_amount'?'p':'원' }}</td>@endforeach
                <td>{{ number_format($item['channel_financial']['purchase']) }} 원</td><td>{{ number_format($item['channel_financial']['purchase_shipping']) }} 원</td><td>{{ number_format($item['reward_points']) }} p</td>
                @if(in_array($orderTab,['cancel','return','exchange']))<td>{{ $claim?->pickup_method==='manual'?'고객 직접 회수':($claim?->pickup_method==='automatic'?'업체 회수':'-') }}</td><td>{{ $order->payment_method }} 환불</td><td>{{ number_format($item['used_point_amount']) }} p</td><td>{{ $claim?->customer_courier_name ?? '-' }}</td><td>{{ $claim?->customer_tracking_number ?? '-' }}</td><td>{{ number_format($orderTab==='exchange'?$item['exchange_shipping_fee']:$item['return_shipping_fee']) }} 원</td><td>{{ number_format(in_array($claim?->status,['withdrawn','rejected'],true) ? 0 : $item['refund_cash_amount']) }} 원@if(!$item['refund_status'] && !in_array($claim?->status,['withdrawn','rejected'],true))<small>예상</small>@endif</td><td>{{ $item['refund_status_label'] }}</td>@endif
            </tr>
            @endforeach
            @endforeach
        @empty<tr><td colspan="40" class="order-empty">등록된 데이터가 없습니다.</td></tr>@endforelse
        </tbody></table></div>
        <div class="page_bx1">{{ $orders->links() }}</div>
    </section>
    <dialog id="order-detail" class="order-dialog"><div class="order-dialog-heading"><h2>주문 상세 보기</h2><button type="button" data-close-dialog="order-detail" aria-label="주문 상세 닫기">닫기 ×</button></div><nav id="order-detail-tabs" class="order-tabs" aria-label="주문 상세 구분"></nav><div id="order-detail-body"></div></dialog>
    <dialog id="order-postcode" class="order-dialog order-action-dialog"><div class="order-dialog-heading"><h2>우편번호 찾기</h2><button type="button" data-close-dialog="order-postcode">주소 검색 닫기</button></div><div id="order-postcode-widget" style="height:500px"></div></dialog>
    <dialog id="order-action" class="order-dialog order-action-dialog"><form id="order-action-form"><div class="order-dialog-heading"><h2 id="order-action-title"></h2><button type="button" data-close-dialog="order-action" aria-label="처리 창 닫기">닫기 ×</button></div><div id="order-action-body"></div><p id="order-action-error" role="alert"></p><div class="order-actions"><button type="button" data-close-dialog="order-action">취소</button><button type="submit" class="primary" id="order-action-submit">확인</button></div></form></dialog>
</div></div>
@endsection
@push('scripts')
<script>window.Me9OrderData = @json($payload); window.Me9ClaimDefinitions = @json(\App\Support\OrderClaimActions::definitions());</script>
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script src="/channel_assets/js/order-workspace.js?v={{ filemtime(public_path('channel_assets/js/order-workspace.js')) }}"></script>
@endpush
