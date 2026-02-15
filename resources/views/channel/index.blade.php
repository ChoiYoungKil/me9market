@extends('layouts.channel')

@section('page_type', 'main')
@php
    $dep1_id = "00";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <ul class="order_list01">
                    <li class="icon0">
                        <div class="txt_w">
                            <div class="txt1">전체</div>
                            <div class="txt2"><strong>{{ $counts['total'] }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon1">
                        <div class="txt_w">
                            <div class="txt1">결제완료</div>
                            <div class="txt2"><strong>{{ $counts['paid'] ?? 0 }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon2">
                        <div class="txt_w">
                            <div class="txt1">배송대기중</div>
                            <div class="txt2"><strong>{{ $counts['shipping_ready'] ?? 0 }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon3">
                        <div class="txt_w">
                            <div class="txt1">배송중</div>
                            <div class="txt2"><strong>{{ $counts['shipping'] ?? 0 }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon4">
                        <div class="txt_w">
                            <div class="txt1">구매확정</div>
                            <div class="txt2"><strong>{{ $counts['complete'] ?? 0 }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon5">
                        <div class="txt_w">
                            <div class="txt1">취소요청</div>
                            <div class="txt2"><strong>{{ $counts['cancel_request'] ?? 0 }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon6">
                        <div class="txt_w">
                            <div class="txt1">반품신청</div>
                            <div class="txt2"><strong>{{ $counts['return_request'] ?? 0 }}</strong> 건</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row col4">
            <div class="box box2">
                <div class="ttl01">채널관리 정보보기</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="35%">
                            <col width="">
                        </colgroup>
                        <tbody class="textL">
                            <tr>
                                <th>회원사명</th>
                                <td>{{ $user->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>판매단계</th>
                                <td>{{ $user->status == 1 ? '승인' : '대기' }}</td>
                            </tr>
                            <tr>
                                <th>대표연락처</th>
                                <td>{{ $user->mobile ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>대표이메일</th>
                                <td>{{ $user->email ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row col4">
            <div class="box box3">
                <div class="con_bx">
                    <div class="txt_bx">
                        <strong>Shop 채널 판매현황</strong>
                        <p>수집기간 : {{ date('Y-01-01') }} ~ {{ date('Y-12-31') }}</p>
                    </div>
                    <div class="chart_w">
                        <canvas id="myChart1" style="width:100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row col2 mr0">
            <div class="box box4">
                <div class="con_bx">
                    <div class="txt_bx">
                        <strong>월별 판매현황 (단위: 원)</strong>
                        <p>수집기간 : {{ date('Y-01-01') }} ~ {{ date('Y-12-31') }}</p>
                    </div>
                    <div class="chart_w">
                        <canvas id="myChart2" style="width:100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row col2-1">
            <div class="box box5">
                <div class="ttl01">최근 주문목록</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="20%">
                            <col width="15%">
                            <col width="15%">
                            <col width="">
                            <col width="">
                            <col width="15%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>주문일</th>
                                <th>주문단계</th>
                                <th>주문형태</th>
                                <th>shop채널명</th>
                                <th>주문명</th>
                                <th>주문자</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentOrders) && count($recentOrders) > 0)
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $order->items->first()->item_status ?? '-' }}</td>
                                        <td>일반</td>
                                        <td>Me9 Market</td>
                                        <td>{{ $order->items->first()->product_name ?? '상품정보 없음' }} 외
                                            {{ $order->items->count() - 1 }}건</td>
                                        <td>{{ $order->user->name ?? $order->name }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="no_data">최근 주문 내역이 없습니다.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row col1-2 mr0">
            <div class="box box6">
                <div class="ttl01">고객 문의사항</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="80px">
                            <col width="">
                            <col width="100px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>번호</th>
                                <th>제목</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody class="textL">
                            <tr>
                                <td colspan="3" class="t_c">등록된 문의사항이 없습니다.</td>
                            </tr>
                            <!--
                                <tr>
                                    <td class="t_c">00</td>
                                    <td class="ovH">
                                        <a href="#" class="subject on fcol1">문의 공지 입니다.</a>
                                    </td>
                                    <td class="t_c">0000-00-00</td>
                                </tr>
                                -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        // Pie Chart (Mock Data for Shop Categories or similar - since we lack breakdown in controller right now)
        // You can update controller later to pass this data.
        const ctx1 = document.getElementById('myChart1').getContext('2d');
        const myChart1 = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['자사몰'], // Single channel for now in this context
                datasets: [{
                    label: '판매 비중',
                    data: [100],
                    backgroundColor: [
                        'rgba(51, 102, 204, 1)',
                    ]
                }]
            }
        });

        // Line Chart (Actual Data)
        const xValues = ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"];
        var monthlyData = @json($chartData ?? []);

        // Flatten data if needed? No, it's array of ints.
        // Ensure data exists
        if (monthlyData.length === 0) monthlyData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        new Chart("myChart2", {
            type: "line",
            data: {
                labels: xValues,
                datasets: [{
                    label: '월별 판매액',
                    data: monthlyData,
                    borderColor: "rgba(220, 57, 18, 1)",
                    fill: false
                }]
            },
            options: {
                legend: { display: true }
            }
        });
    </script>
@endpush