@extends('layouts.admin')
@section('page_type', 'commerce-dashboard-page')
@section('content')
<div id="contents" class="commerce-dashboard">
    <h1>운영 현황</h1>
    <ul class="order_list01">
        <li class="icon0"><div class="txt_w"><div class="txt1">전체 주문상품</div><div class="txt2"><strong>{{ number_format($totalItems) }}</strong> 건</div></div></li>
        @foreach(['paid' => '결제완료', 'ready_to_ship' => '배송대기', 'shipping' => '배송중', 'confirmed' => '구매확정', 'cancel_requested' => '취소요청', 'return_requested' => '반품요청'] as $status => $label)
            <li class="icon{{ $loop->iteration }}"><div class="txt_w"><div class="txt1">{{ $label }}</div><div class="txt2"><strong>{{ number_format($counts[$status] ?? 0) }}</strong> 건</div></div></li>
        @endforeach
    </ul>
    <p class="dashboard-period">구매확정 기준 {{ $periodStart->format('Y-m-d') }} ~ {{ $periodEnd->format('Y-m-d') }}</p>
    <div class="dashboard-columns">
        @foreach([['title' => '당월 상품 매출 Top 20', 'rows' => $topProducts, 'label' => 'product_name'], ['title' => '당월 카테고리 매출 Top 20', 'rows' => $topCategories, 'label' => 'category_name']] as $chart)
        <section>
            <h2>{{ $chart['title'] }}</h2>
            @if($chart['rows']->isNotEmpty())<div class="dashboard-chart"><canvas id="sales-chart-{{ $loop->index }}" aria-label="{{ $chart['title'] }}" role="img"></canvas></div>@endif
            <div class="dashboard-table tb01"><table><thead><tr><th>구분</th><th>수량</th><th>상품 매출</th></tr></thead><tbody>
                @forelse($chart['rows'] as $row)<tr><td>{{ $row->{$chart['label']} ?: '미분류' }}</td><td>{{ number_format($row->quantity) }}</td><td>{{ number_format($row->amount) }}원</td></tr>
                @empty<tr><td colspan="3">해당 기간 구매확정 내역이 없습니다.</td></tr>@endforelse
            </tbody></table></div>
        </section>
        @endforeach
    </div>
    <div class="dashboard-columns">
        <section><h2>최근 주문</h2><div class="dashboard-table tb01"><table><thead><tr><th>주문일</th><th>주문번호</th><th>상품 금액</th></tr></thead><tbody>
            @forelse($recentOrders as $order)<tr><td>{{ \Illuminate\Support\Carbon::parse($order->ordered_at)->format('Y-m-d') }}</td><td><a href="{{ url('admin/orders/'.$order->order_id) }}">Me9-{{ $order->order_id }}</a></td><td>{{ number_format($order->amount) }}원</td></tr>
            @empty<tr><td colspan="3">주문 내역이 없습니다.</td></tr>@endforelse
        </tbody></table></div></section>
        <section><h2>최근 고객 포인트</h2><div class="dashboard-table tb01"><table><thead><tr><th>일시</th><th>Shop 채널</th><th>구분</th><th>포인트</th></tr></thead><tbody>
            @forelse($recentPoints as $entry)<tr><td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td><td>{{ $entry->shopChannel?->channel_name ?: 'Me9' }}</td><td>{{ ['earn'=>'분배','use'=>'사용','refund'=>'복구','convert_in'=>'전환입금','convert_out'=>'전환출금','first_visit'=>'첫 방문(이전)'][$entry->type] ?? $entry->type }}</td><td>{{ number_format($entry->points) }}P</td></tr>
            @empty<tr><td colspan="4">포인트 내역이 없습니다.</td></tr>@endforelse
        </tbody></table></div></section>
    </div>
</div>
<style>
#commerce-dashboard-page{min-width:0}
#commerce-dashboard-page *{letter-spacing:0}
#container #container_w #contents.commerce-dashboard{display:block}
@media(max-width:1100px){
    #commerce-dashboard-page #header{height:auto;min-height:60px;padding:12px 16px;display:flex;align-items:center;flex-wrap:wrap;gap:12px}
    #commerce-dashboard-page #header .logo{position:static;flex:0 0 155px}
    #commerce-dashboard-page #header .r_bx{position:static;margin-left:auto;max-width:100%}
    #commerce-dashboard-page #header .r_bx .name{overflow-wrap:anywhere;word-break:normal}
    #commerce-dashboard-page #header .t_menu{order:3;width:100%;height:36px;padding:0;overflow-x:auto;white-space:nowrap}
    #commerce-dashboard-page #header .t_menu > ul > li{margin-right:24px}
    #commerce-dashboard-page #container #l_menu{width:200px;padding:0 12px}
    #commerce-dashboard-page #container #container_w{width:calc(100% - 200px);padding:24px 16px}
}
@media(max-width:700px){
    #commerce-dashboard-page #container{display:block}
    #commerce-dashboard-page #container #container_w,#commerce-dashboard-page #container #l_menu{width:100%}
    #commerce-dashboard-page #l_menu .con1{padding:16px 0}
    #commerce-dashboard-page #l_menu .con1 .con_w{margin-bottom:0}
    #commerce-dashboard-page #l_menu .dep1_wrap{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    #commerce-dashboard-page #l_menu .con1 .con_w .dep1{margin:0}
    #commerce-dashboard-page #l_menu .con1 .con_w .dep1 > a{font-size:13px;padding-right:12px}
    #commerce-dashboard-page #l_menu .con1 .con_w .dep2_wrap{padding:0 8px;border-radius:4px}
    #commerce-dashboard-page #l_menu .con_w:has(.dep1_wrap.type2){display:none}
}
.commerce-dashboard h1{font-size:24px;line-height:1.4;margin-bottom:24px}.commerce-dashboard h2{font-size:18px;line-height:1.4;margin-bottom:16px}.commerce-dashboard .order_list01{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px}.commerce-dashboard .order_list01 li{width:auto;min-width:0}.dashboard-period{margin:24px 0;color:#555}.dashboard-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:32px;margin-bottom:32px}.dashboard-columns section{min-width:0;padding-top:20px;border-top:1px solid #ddd}.dashboard-chart{height:300px;margin-bottom:16px}.dashboard-table{overflow:auto}.dashboard-table table{width:100%;min-width:320px;table-layout:fixed}.dashboard-table td{overflow-wrap:anywhere}.dashboard-table a{text-decoration:underline}@media(max-width:800px){.dashboard-columns{grid-template-columns:1fr}.dashboard-chart{height:260px}}
</style>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    var charts = @json($charts);
    charts.forEach(function (data, index) {
        var canvas = document.getElementById('sales-chart-' + index);
        if (!canvas) return;
        new Chart(canvas, {type:'bar', data:{labels:data.labels.map(function (label) { return label || '미분류'; }), datasets:[{label:'상품 매출', data:data.values, backgroundColor:index === 0 ? '#187e77' : '#bd5365'}]}, options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}});
    });
});
</script>
@endpush
