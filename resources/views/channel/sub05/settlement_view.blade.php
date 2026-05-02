@extends('layouts.channel')

@section('page_type', 'sub')

@php
    $dep1_id = '05';
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">정산 상세 내역 ({{ $period }})</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>정산관리</li>
                        <li>정산내역</li>
                        <li>상세내역</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $orders->total() }}</strong> 건</div>
                        </div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="100px">
                                    <col width="150px">
                                    <col width="">
                                    <col width="80px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>확정일자</th>
                                        <th>주문번호</th>
                                        <th>상품정보</th>
                                        <th>수량</th>
                                        <th>판매금액</th>
                                        <th>수수료({{ $rate }}%)</th>
                                        <th>정산금액</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $item)
                                        @php
                                            $totalAmount = $item->product_price * $item->product_qty;
                                            $commission = $totalAmount * ($rate / 100);
                                            $settlementAmount = $totalAmount - $commission;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->updated_at->format('Y-m-d') }}</td>
                                            <td>{{ $item->order->order_id ?? '-' }}</td>
                                            <td class="t_l">
                                                <strong>{{ $item->product->product_name ?? '삭제된 상품' }}</strong>
                                                @if($item->product_options)
                                                    <p class="fcol1" style="font-size: 12px;">[{{ $item->product_options }}]</p>
                                                @endif
                                            </td>
                                            <td>{{ $item->product_qty }}</td>
                                            <td class="t_r">{{ number_format($totalAmount) }} 원</td>
                                            <td class="t_r">{{ number_format($commission) }} 원</td>
                                            <td class="t_r"><span class="bold">{{ number_format($settlementAmount) }} 원</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="no_data">내역이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn mt10">
                            <a href="{{ route('channel.settlement.list') }}" class="col5">목록으로</a>
                        </div>

                        <div class="page_bx1">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
