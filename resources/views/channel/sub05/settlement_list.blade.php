@extends('layouts.channel')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">정산관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>정산관리</li>
                        <li>정산내역</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $settlements->total() }}</strong> 건</div>
                        </div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="150px">
                                    <col width="150px">
                                    <col width="150px">
                                    <col width="100px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>정산기간</th>
                                        <th>주문건수</th>
                                        <th>총 매출액</th>
                                        <th>수수료 (10%)</th>
                                        <th>정산금액</th>
                                        <th>상태</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($settlements->count() > 0)
                                        @foreach($settlements as $settlement)
                                            <tr>
                                                <td>{{ $settlement->settlement_period }}</td>
                                                <td>{{ number_format($settlement->order_count) }} 건</td>
                                                <td class="t_r">{{ number_format($settlement->total_sales) }} 원</td>
                                                <td class="t_r">{{ number_format($settlement->commission) }} 원</td>
                                                <td class="t_r"><span
                                                        class="bold fcol4">{{ number_format($settlement->settlement_amount) }}
                                                        원</span></td>
                                                <td>{{ $settlement->status }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="no_data">정산 내역이 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="page_bx1">
                            {{ $settlements->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection