@extends('layouts.channel')
@section('page_type', 'main')
@push('styles')
<link rel="stylesheet" href="{{ asset('channel_assets/css/dashboard.css') }}?v={{ filemtime(public_path('channel_assets/css/dashboard.css')) }}">
@endpush
@section('content')
<div id="contents"><div id="channel-dashboard">
<div class="dashboard-body">
    <div class="dashboard-heading"><div><p>MY CHANNEL</p><h1>대시보드</h1></div><span>수집기간 : {{ $from->format('Y.m.d') }} ~ {{ $to->format('Y.m.d') }} · 최근 15일</span></div>
    @if(session('success_message'))<p class="dashboard-feedback" role="status">{{ session('success_message') }}</p>@endif
    @if($errors->any())<p class="dashboard-feedback error" role="alert">{{ $errors->first() }}</p>@endif
    <section class="dashboard-shortcuts" aria-label="바로가기 메뉴"><strong>바로가기</strong>
        <nav aria-label="저장한 바로가기">@forelse($selectedShortcuts as $key)<a href="{{ route($shortcuts[$key][1]) }}">{{ $shortcuts[$key][0] }}</a>@empty<span>자주 사용하는 메뉴를 선택해 주세요.</span>@endforelse</nav>
        <details><summary>바로가기 설정</summary><form method="POST" action="{{ route('channel.dashboard.shortcuts') }}">@csrf
            <fieldset><legend>자주 사용하는 메뉴 선택</legend>@foreach($shortcuts as $key => [$label, $route])<label><input type="checkbox" name="shortcuts[]" value="{{ $key }}" @checked(in_array($key, $selectedShortcuts))>{{ $label }}</label>@endforeach</fieldset><button type="submit">바로가기 저장</button>
        </form></details>
    </section>
    @php($range = ['date_from' => $from->toDateString(), 'date_to' => $to->toDateString(), 'dashboard' => 1, 'order_type' => 'all'])
    @if($canViewOrders)<section class="dashboard-status" aria-label="최근 15일 주문 현황">
    @foreach([['total','전체',null,'channel.order.list'],['paid','결제완료','paid','channel.order.list'],['shipping_ready','배송대기중','ready_to_ship','channel.order.list'],['shipping','배송중','shipping','channel.order.list'],['complete','구매확정','confirmed','channel.order.list'],['cancel_request','취소요청','cancel_requested','channel.order.cancel_list'],['return_request','반품신청','return_requested','channel.order.return_list']] as [$key,$label,$status,$route])
        <a href="{{ route($route, $range + ($status ? ['order_statuses'=>[$status]] : [])) }}" class="dashboard-metric" data-metric="{{ $key }}"><span>{{ $label }}</span><div><strong>{{ number_format($counts[$key] ?? 0) }}</strong><small>건</small></div></a>
    @endforeach
    </section>@endif
    <div class="dashboard-overview">
        <section class="dashboard-panel dashboard-company" aria-labelledby="company-title"><h2 id="company-title">채널관리 정보보기</h2><dl>
            <div><dt>회원사명</dt><dd>{{ $company?->shop_name ?: $vendor?->name ?: $user->name }}</dd></div>
            <div><dt>판매단계</dt><dd><span class="dashboard-badge">{{ $vendor ? ($vendor->status == 1 ? '판매인증 완료' : ($vendor->seller_requested_at ? '판매인증 심사 대기' : '회원사 등록')) : '회원사 정보 없음' }}</span></dd></div>
            <div><dt>대표연락처</dt><dd>{{ $company?->shop_mobile ?: $vendor?->mobile ?: '미등록' }}</dd></div>
            <div><dt>대표이메일</dt><dd>{{ $company?->shop_email ?: $vendor?->email ?: '미등록' }}</dd></div>
        </dl>@if($canViewSettings)<a class="dashboard-more" href="{{ route('channel.info.management') }}">정보관리 보기</a>@endif</section>
        @if($canViewOrders)<section class="dashboard-panel dashboard-chart" aria-labelledby="shop-sales-title"><h2 id="shop-sales-title">Shop 채널 판매현황</h2><p class="dashboard-caption">최근 15일 · 판매수량 기준</p>
            <div class="dashboard-chart-wrap"><canvas id="dashboard-shop-chart" role="img" aria-label="최근 15일 Shop 채널별 판매수량 원그래프"></canvas>@if($shopSales['labels']->isEmpty())<p class="dashboard-chart-empty">집계된 판매 내역이 없습니다.</p>@endif</div>
            <details class="dashboard-chart-values"><summary>판매수량 보기</summary><ul>@forelse($shopSales['labels'] as $i => $label)<li>{{ $label }} <strong>{{ number_format($shopSales['data'][$i]) }}개</strong></li>@empty<li>집계된 판매 내역이 없습니다.</li>@endforelse</ul></details>
        </section>
        <section class="dashboard-panel dashboard-chart" aria-labelledby="category-sales-title"><h2 id="category-sales-title">품목별 판매량</h2><p class="dashboard-caption">{{ $yearFrom->format('Y.m.d') }} ~ {{ $yearTo->format('Y.m.d') }} · 단위: 개</p>
            <div class="dashboard-chart-wrap"><canvas id="dashboard-category-chart" role="img" aria-label="올해 품목별 월 판매수량 꺾은선 그래프"></canvas>@if($categorySeries->isEmpty())<p class="dashboard-chart-empty">집계된 판매 내역이 없습니다.</p>@endif</div>
            <details class="dashboard-chart-values"><summary>품목별 월 수량 보기</summary><div class="dashboard-table-scroll"><table><caption class="dashboard-sr-only">올해 품목별 월 판매수량</caption><thead><tr><th>품목</th>@foreach(range(1,12) as $month)<th>{{ $month }}월</th>@endforeach</tr></thead><tbody>@forelse($categorySeries as $series)<tr><th>{{ $series['label'] }}</th>@foreach($series['data'] as $quantity)<td>{{ number_format($quantity) }}</td>@endforeach</tr>@empty<tr><td colspan="13">집계된 판매 내역이 없습니다.</td></tr>@endforelse</tbody></table></div></details>
        </section>@endif
    </div>
    @if($canViewOrders)<p class="dashboard-sales-note">판매수량은 취소·반품 완료 상품과 교환 재발송 수량을 제외합니다.</p>
    <div class="dashboard-lists">
        <section class="dashboard-panel"><div class="dashboard-panel-heading"><h2>최근 주문목록</h2><a href="{{ route('channel.order.list') }}">전체보기</a></div><div class="dashboard-table-scroll"><table><caption class="dashboard-sr-only">최근 주문 5건</caption><thead><tr><th>주문일</th><th>주문단계</th><th>주문형태</th><th>Shop 채널명</th><th>주문명</th><th>주문자</th></tr></thead><tbody>
        @forelse($recentOrders as $order)
            @php($items = $order->orders_products)
            <tr><td>{{ $order->created_at->format('Y.m.d') }}</td><td>{{ $items->pluck('status_label')->unique()->implode(' / ') }}</td>
                <td>{{ $items->contains(fn ($item) => (bool) $item->joint_purchase_id) ? ($items->contains(fn ($item) => !$item->joint_purchase_id) ? '일반 / 공동구매' : '공동구매') : '일반' }}</td>
                <td>{{ $items->map(fn ($item) => $item->shopChannel?->channel_name ?: '채널 정보 없음')->unique()->implode(' / ') }}</td>
                <td><a href="{{ route('channel.order.list', ['dashboard'=>1, 'order_type'=>'all', 'order_id'=>$order->id]) }}">{{ $items->first()?->product_name ?: '상품정보 없음' }}@if($items->count() > 1) 외 {{ $items->count() - 1 }}건@endif</a></td><td>{{ $order->buyer_name ?: $order->name }}</td></tr>
        @empty<tr><td colspan="6" class="dashboard-empty">최근 주문 내역이 없습니다.</td></tr>@endforelse
        </tbody></table></div></section>
        <section class="dashboard-panel"><div class="dashboard-panel-heading"><h2>고객 문의사항</h2><a href="{{ route('channel.inquiries.index') }}">전체보기</a></div><div class="dashboard-table-scroll"><table><caption class="dashboard-sr-only">최근 고객 문의 5건</caption><thead><tr><th>번호</th><th>제목</th><th>등록일</th></tr></thead><tbody>
        @forelse($recentInquiries as $inquiry)<tr><td>{{ $inquiry->id }}</td><td><a href="{{ route('channel.inquiries.show', $inquiry->id) }}">{{ $inquiry->subject }}</a></td><td>{{ $inquiry->created_at->format('Y.m.d') }}</td></tr>
        @empty<tr><td colspan="3" class="dashboard-empty">등록된 문의사항이 없습니다.</td></tr>@endforelse
        </tbody></table></div></section>
    </div>@endif
</div>
@if($canViewShops)<aside class="dashboard-channel-rail" aria-label="운영 Shop 채널 바로가기"><h2>Shop 채널</h2><p>운영 중 {{ $operatingChannels->count() }}개</p>
    @forelse($operatingChannels as $channel)<a href="{{ route('channel.shop.open', ['id'=>$channel->id]) }}" target="_blank" rel="noopener" title="{{ $channel->channel_name }} 새 창으로 열기"><strong>{{ $channel->channel_name }}</strong><span>{{ $channel->is_public ? '공개' : '비공개' }} · 새 창 ↗</span></a>@empty<p class="dashboard-empty">운영 중인 채널이 없습니다.</p>@endforelse
    <a class="dashboard-channel-manage" href="{{ route('channel.shop_list') }}">채널 관리</a>
</aside>@endif
</div></div>
@endsection
@push('scripts')
<script src="{{ asset('channel_assets/js/dashboard.js') }}?v={{ filemtime(public_path('channel_assets/js/dashboard.js')) }}" defer></script>
<script type="application/json" id="dashboard-chart-data">{!! json_encode(['shops'=>$shopSales,'categories'=>$categorySeries], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
