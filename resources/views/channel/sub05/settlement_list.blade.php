@extends('layouts.channel')

@section('page_type', 'sub')

@section('content')
    @php
        $roleLabels = [
            'seller' => '자사상품 판매자',
            'shared_fixed_supplier' => '공유상품 제공자(고정가)',
            'shared_fixed_reseller' => '공유상품 판매자(수수료)',
            'shared_free_supplier' => '공유상품 제공자(자유가)',
            'shared_free_reseller' => '공유상품 판매자(자유가)',
        ];
    @endphp
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
                        <form method="GET" action="{{ route('channel.settlement.list') }}" class="tb01" style="margin-bottom:15px;">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="120px">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th><span>정산 기간</span></th>
                                        <td>
                                            <select name="period" class="w160">
                                                @foreach($periodOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="t_c"><button type="submit" class="btn02 col5">검색</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $settlements->total() }}</strong> 건</div>
                        </div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="150px">
                                    <col width="170px">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="140px">
                                    <col width="140px">
                                    <col width="140px">
                                    <col width="140px">
                                    <col width="140px">
                                    <col width="140px">
                                    <col width="100px">
                                    <col width="100px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>정산기간</th>
                                        <th>Shop 채널</th>
                                        <th>정산역할</th>
                                        <th>정산방식</th>
                                        <th>주문건수</th>
                                        <th>매출</th>
                                        <th>매입</th>
                                        <th>포인트 예수금</th>
                                        <th>포인트 사용</th>
                                        <th>지급액</th>
                                        <th>상태</th>
                                        <th>상세보기</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($settlements->count() > 0)
                                        @foreach($settlements as $settlement)
                                            <tr>
                                                <td>{{ $settlement->period }}</td>
                                                <td>{{ $settlement->shop_channel_name }}</td>
                                                <td>{{ $roleLabels[$settlement->settlement_role ?? 'seller'] ?? ($settlement->settlement_role ?? 'seller') }}</td>
                                                <td>
                                                    {{ (int)$settlement->settlement_type === 2 ? '판매 개당 금액' : '판매금액 대비 %' }}
                                                    / {{ number_format($settlement->settlement_rate, 2) }}{{ (int)$settlement->settlement_type === 2 ? '원' : '%' }}
                                                </td>
                                                <td>{{ number_format($settlement->order_count) }} 건</td>
                                                <td class="t_r">{{ number_format($settlement->invoice_sales_amount ?? $settlement->gross_sales_amount) }} 원</td>
                                                <td class="t_r">{{ number_format($settlement->invoice_purchase_amount ?? 0) }} 원</td>
                                                <td class="t_r">{{ number_format($settlement->point_deposit_amount ?? 0) }} P</td>
                                                <td class="t_r">{{ number_format($settlement->point_used_amount ?? 0) }} P</td>
                                                <td class="t_r"><span class="bold fcol4">{{ number_format($settlement->payout_amount ?? $settlement->settlement_amount) }} 원</span></td>
                                                <td>
                                                    @if(($settlement->run_status ?? $settlement->status) === 'completed')
                                                        정산완료
                                                    @elseif(($settlement->run_status ?? $settlement->status) === 'pending')
                                                        생성완료
                                                    @else
                                                        미정산
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('channel.settlement.view', ['period' => $settlement->period, 'shop_channel_id' => $settlement->shop_channel_id ?: 0]) }}"
                                                       class="btn02 col5">보기</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="12" class="no_data">정산 내역이 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                                @if($settlements->total() > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="4">합계</th>
                                            <th>{{ number_format($totals['order_count']) }} 건</th>
                                            <th class="t_r">{{ number_format($totals['invoice_sales_amount'] ?? $totals['gross_sales_amount']) }} 원</th>
                                            <th class="t_r">{{ number_format($totals['invoice_purchase_amount'] ?? 0) }} 원</th>
                                            <th class="t_r">{{ number_format($totals['point_deposit_amount'] ?? 0) }} P</th>
                                            <th class="t_r">{{ number_format($totals['point_used_amount'] ?? 0) }} P</th>
                                            <th class="t_r">{{ number_format($totals['payout_amount'] ?? $totals['settlement_amount']) }} 원</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                @endif
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
