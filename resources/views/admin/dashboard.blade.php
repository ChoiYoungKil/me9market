@extends('layouts.admin')
@section('page_type', 'commerce-dashboard-page')
@section('content')
<div id="contents" class="commerce-dashboard">
    <h1>운영 현황</h1>
    <ul class="order_list01">
        <li class="icon0"><div class="txt_w"><div class="txt1">전체 주문상품</div><div class="txt2"><strong>{{ number_format($totalItems) }}</strong> 건</div></div></li>
        @foreach($statusLabels as $status => $label)
        <li class="status-card"><a href="{{ route('admin.orders',['item_status'=>$status]) }}"><span>{{ $label }}</span><strong>{{ number_format($counts[$status] ?? 0) }}</strong><span>건</span></a></li>
        @endforeach
    </ul>
    <p class="dashboard-period">구매확정 기준 {{ $periodStart->format('Y-m-d') }} ~ {{ $periodEnd->format('Y-m-d') }}</p>
    <div class="admin-grid cols-3">
        @foreach([['title' => '당월 상품 매출 Top 20', 'rows' => $topProducts, 'label' => 'product_name'], ['title' => '당월 카테고리 매출 Top 20', 'rows' => $topCategories, 'label' => 'category_name']] as $chart)
        <section class="admin-panel">
            <h2>{{ $chart['title'] }}</h2>
            @if($chart['rows']->isNotEmpty())<div class="dashboard-chart"><div id="sales-chart-{{ $loop->index }}" class="google-sales-chart" aria-label="{{ $chart['title'] }}" role="img"></div></div>@endif
            <div class="dashboard-table tb01"><table><thead><tr><th>구분</th><th>수량</th><th>상품 매출</th></tr></thead><tbody>
                @forelse($chart['rows'] as $row)<tr><td>{{ $row->{$chart['label']} ?: '미분류' }}</td><td>{{ number_format($row->quantity) }}</td><td>{{ number_format($row->amount) }}원</td></tr>
                @empty<tr><td colspan="3">해당 기간 구매확정 내역이 없습니다.</td></tr>@endforelse
            </tbody></table></div>
        </section>
        @endforeach
        <section class="admin-panel"><h2>처리 대기</h2><ul class="admin-queue">
        @foreach(['seller'=>['판매자 승인','/admin/users?seller_status=pending'],'closures'=>['채널 운영중지 승인','/admin/shop-channel-closures'],'contacts'=>['미답변 문의','/admin/contacts']] as $key=>$entry)
        @if(array_key_exists($key,$workQueue))<li><a href="{{ url($entry[1]) }}">{{ $entry[0] }} <strong>{{ $workQueue[$key] }}건</strong></a></li>@endif
        @endforeach</ul><p class="admin-help">전체 주문상품 기준이며 교환 재발송 상품은 중복 집계하지 않습니다. 운영종료 채널의 과거 거래도 포함됩니다.</p></section>
    </div>
    <div class="admin-grid cols-3">
        <section class="admin-panel span-2"><h2>최근 주문</h2><div class="dashboard-table tb01"><table><thead><tr><th>주문일</th><th>주문번호</th><th>상품 금액</th></tr></thead><tbody>
            @forelse($recentOrders as $order)<tr><td>{{ \Illuminate\Support\Carbon::parse($order->ordered_at)->format('Y-m-d') }}</td><td><a href="{{ url('admin/orders/'.$order->order_id) }}">Me9-{{ $order->order_id }}</a></td><td>{{ number_format($order->amount) }}원</td></tr>
            @empty<tr><td colspan="3">주문 내역이 없습니다.</td></tr>@endforelse
        </tbody></table></div></section>
        <section class="admin-panel"><h2>최근 고객 포인트</h2><div class="dashboard-table tb01"><table><thead><tr><th>일시</th><th>Shop 채널</th><th>구분</th><th>포인트</th></tr></thead><tbody>
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
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const charts=@json($charts);
 const draw=()=>charts.forEach((entry,index)=>{const node=document.getElementById('sales-chart-'+index);if(!node||!entry.labels.length)return;
 const data=google.visualization.arrayToDataTable([['구분','상품 매출'],...entry.labels.map((label,i)=>[label||'미분류',Number(entry.values[i])])]);
 new google.visualization.PieChart(node).draw(data,{fontName:'Arial',height:280,chartArea:{left:10,top:15,width:'95%',height:'90%'},legend:{position:'bottom'},pieSliceText:'percentage'});
 });
 if(window.google?.charts){google.charts.load('current',{packages:['corechart'],language:'ko'});google.charts.setOnLoadCallback(draw);window.addEventListener('resize',()=>{clearTimeout(window.adminChartResize);window.adminChartResize=setTimeout(draw,150);});}
});
</script>
@endpush
