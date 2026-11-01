@extends('layouts.admin')
@section('page_type', 'commerce-dashboard-page')
@push('styles')
<link rel="stylesheet" href="{{ asset('common/admin-dashboard.css').'?v='.filemtime(public_path('common/admin-dashboard.css')) }}">
@endpush
@section('content')
<div id="contents" class="commerce-dashboard">
    <header class="admin-dashboard-heading">
        <h1>대시보드</h1>
        <nav aria-label="현재 위치"><span>HOME</span><span>전체관리자</span><strong>대시보드</strong></nav>
    </header>
    <section class="admin-dashboard-status" aria-label="운영 현황">
        <div class="admin-dashboard-metric"><span>전체 주문상품</span><div><strong>{{ number_format($totalItems) }}</strong><small>건</small></div></div>
        @foreach($statusLabels as $status => $label)
        <a class="admin-dashboard-metric" href="{{ route('admin.orders',['item_status'=>$status]) }}"><span>{{ $label }}</span><div><strong>{{ number_format($counts[$status] ?? 0) }}</strong><small>건</small></div></a>
        @endforeach
    </section>
    <p class="dashboard-period">구매확정 기준 {{ $periodStart->format('Y-m-d') }} ~ {{ $periodEnd->format('Y-m-d') }}</p>
    @php
        $adminChartColors = ['#e87924','#2f80ed','#279b63','#8b5bd6','#d64c7f','#159cac','#b38c21','#5d61c9','#b34b3c','#4d8d36','#316f9e','#a94b99','#358071','#bf652b','#6245a8','#69822d','#b43763','#9d752b','#3e8da2','#784886'];
        $adminChartPalettes = [$adminChartColors, array_merge(array_slice($adminChartColors, 3), array_slice($adminChartColors, 0, 3))];
    @endphp
    <div class="admin-grid cols-3 admin-dashboard-overview">
        @foreach([['title' => '당월 상품 매출 Top 20', 'rows' => $topProducts, 'label' => 'product_name'], ['title' => '당월 카테고리 매출 Top 20', 'rows' => $topCategories, 'label' => 'category_name']] as $chart)
        @php($chartPalette = $adminChartPalettes[$loop->index])
        <section class="admin-panel">
            <h2>{{ $chart['title'] }}</h2>
            @if($chart['rows']->isNotEmpty())<div class="dashboard-chart"><div id="sales-chart-{{ $loop->index }}" class="google-sales-chart" aria-label="{{ $chart['title'] }}" role="img"></div></div>@endif
            @if($chart['rows']->isNotEmpty())
                <ul class="admin-chart-legend" aria-label="{{ $chart['title'] }} 색상 안내">
                    @foreach($chart['rows'] as $row)
                        <li title="{{ $row->{$chart['label']} ?: '미분류' }}">
                            <span class="admin-chart-swatch" style="--legend-color:{{ $chartPalette[$loop->index % count($chartPalette)] }}" aria-hidden="true"></span>
                            <span class="admin-chart-legend-label">{{ $row->{$chart['label']} ?: '미분류' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="dashboard-table tb01"><table><thead><tr><th>구분</th><th>수량</th><th>상품 매출</th></tr></thead><tbody>
                @forelse($chart['rows'] as $row)<tr><td>{{ $row->{$chart['label']} ?: '미분류' }}</td><td>{{ number_format($row->quantity) }}</td><td>{{ number_format($row->amount) }}원</td></tr>
                @empty
@include('components.table-empty-row', ['columns' => 3])@endforelse
            </tbody></table></div>
        </section>
        @endforeach
        <section class="admin-panel"><h2>처리 대기</h2><ul class="admin-queue">
        @foreach(['seller'=>['판매자 승인','/admin/users?seller_status=pending'],'closures'=>['채널 운영중지 승인','/admin/shop-channel-closures'],'contacts'=>['미답변 문의','/admin/contacts']] as $key=>$entry)
        @if(array_key_exists($key,$workQueue))<li><a href="{{ url($entry[1]) }}">{{ $entry[0] }} <strong>{{ $workQueue[$key] }}건</strong></a></li>@endif
        @endforeach</ul><p class="admin-help">전체 주문상품 기준이며 교환 재발송 상품은 중복 집계하지 않습니다. 운영종료 채널의 과거 거래도 포함됩니다.</p></section>
    </div>
    <div class="admin-grid cols-3 admin-dashboard-lists">
        <section class="admin-panel span-2"><h2>최근 주문</h2><div class="dashboard-table tb01"><table><thead><tr><th>주문일</th><th>주문번호</th><th>상품 금액</th></tr></thead><tbody>
            @forelse($recentOrders as $order)<tr><td>{{ \Illuminate\Support\Carbon::parse($order->ordered_at)->format('Y-m-d') }}</td><td><a href="{{ url('admin/orders/'.$order->order_id) }}">Me9-{{ $order->order_id }}</a></td><td>{{ number_format($order->amount) }}원</td></tr>
            @empty
@include('components.table-empty-row', ['columns' => 3])@endforelse
        </tbody></table></div></section>
        <section class="admin-panel"><h2>최근 고객 포인트</h2><div class="dashboard-table tb01"><table><thead><tr><th>일시</th><th>Shop 채널</th><th>구분</th><th>포인트</th></tr></thead><tbody>
            @forelse($recentPoints as $entry)<tr><td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td><td>{{ $entry->shopChannel?->channel_name ?: 'Me9' }}</td><td>{{ ['earn'=>'분배','use'=>'사용','refund'=>'복구','convert_in'=>'전환입금','convert_out'=>'전환출금','first_visit'=>'첫 방문(이전)'][$entry->type] ?? $entry->type }}</td><td>{{ number_format($entry->points) }}P</td></tr>
            @empty
@include('components.table-empty-row', ['columns' => 4])@endforelse
        </tbody></table></div></section>
    </div>
</div>

@endsection
@push('scripts')
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const charts=@json($charts);
 const chartPalettes=@json($adminChartPalettes);
 const draw=()=>charts.forEach((entry,index)=>{const node=document.getElementById('sales-chart-'+index);if(!node||!entry.labels.length)return;
 const data=google.visualization.arrayToDataTable([['구분','상품 매출'],...entry.labels.map((label,i)=>[label||'미분류',Number(entry.values[i])])]);
 new google.visualization.PieChart(node).draw(data,{fontName:'Arial',height:280,colors:chartPalettes[index % chartPalettes.length],chartArea:{left:10,top:15,width:'95%',height:'90%'},legend:{position:'none'},pieSliceText:'percentage'});
 });
 if(window.google?.charts){google.charts.load('current',{packages:['corechart'],language:'ko'});google.charts.setOnLoadCallback(draw);window.addEventListener('resize',()=>{clearTimeout(window.adminChartResize);window.adminChartResize=setTimeout(draw,150);});}
});
</script>
@endpush
