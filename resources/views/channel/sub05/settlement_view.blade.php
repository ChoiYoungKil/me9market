@extends('layouts.channel')

@section('page_type', 'sub')

@php
    $dep1_id = '05';
    $roleLabels = [
        'seller' => '자사상품 판매자',
        'shared_fixed_supplier' => '공유상품 제공자(고정가)',
        'shared_fixed_reseller' => '공유상품 판매자(수수료)',
        'shared_free_supplier' => '공유상품 제공자(자유가)',
        'shared_free_reseller' => '공유상품 판매자(자유가)',
    ];
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">정산 상세 내역 ({{ $period }}{{ $summary ? ' / ' . $summary->shop_channel_name : '' }})</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>정산관리</li>
                        <li>정산내역</li>
                        <li>상세내역</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if($summary)
                            <div class="tb01" style="margin-bottom:15px;">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width="">
                                        <col width="160px"><col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th>Shop 채널</th>
                                            <td>{{ $summary->shop_channel_name }}</td>
                                            <th>정산방식</th>
                                            <td>
                                                {{ (int)$summary->settlement_type === 2 ? '판매 개당 금액' : '판매금액 대비 %' }}
                                                / {{ number_format($summary->settlement_rate, 2) }}{{ (int)$summary->settlement_type === 2 ? '원' : '%' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>주문건수</th>
                                            <td>{{ number_format($summary->order_count) }} 건</td>
                                            <th>품목수량</th>
                                            <td>{{ number_format($summary->quantity) }} 개</td>
                                        </tr>
                                        <tr>
                                            <th>매출 / 매입</th>
                                            <td>{{ number_format($summary->invoice_sales_amount ?? $summary->gross_sales_amount) }} 원 / {{ number_format($summary->invoice_purchase_amount ?? 0) }} 원</td>
                                            <th>지급액</th>
                                            <td><strong class="fcol4">{{ number_format($summary->payout_amount ?? $summary->settlement_amount) }} 원</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif

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
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>확정일자</th>
                                        <th>주문번호</th>
                                        <th>상품정보</th>
                                        <th>수량</th>
                                        <th>역할</th>
                                        <th>매출</th>
                                        <th>매입</th>
                                        <th>포인트</th>
                                        <th>지급액</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $item)
                                        <tr>
                                            <td>{{ optional($item->confirmed_at)->format('Y-m-d') }}</td>
                                            <td>{{ $item->order_no }}</td>
                                            <td class="t_l">
                                                <strong>{{ $item->product_name }}</strong>
                                                <p class="fcol1" style="font-size: 12px;">{{ $item->product_code }}</p>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $roleLabels[$item->settlement_role ?? 'seller'] ?? ($item->settlement_role ?? 'seller') }}</td>
                                            <td class="t_r">{{ number_format($item->invoice_sales_amount ?? $item->gross_sales_amount) }} 원</td>
                                            <td class="t_r">{{ number_format($item->invoice_purchase_amount ?? 0) }} 원</td>
                                            <td class="t_r">{{ number_format($item->point_deposit_amount ?? 0) }} / {{ number_format($item->point_used_amount ?? 0) }} P</td>
                                            <td class="t_r"><span class="bold">{{ number_format($item->payout_amount ?? $item->settlement_amount) }} 원</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="no_data">내역이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($orders->total() > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="3">상세 합계</th>
                                            <th class="t_r">{{ number_format($totals['quantity']) }}</th>
                                            <th></th>
                                            <th class="t_r">{{ number_format($totals['invoice_sales_amount'] ?? $totals['gross_sales_amount']) }} 원</th>
                                            <th class="t_r">{{ number_format($totals['invoice_purchase_amount'] ?? 0) }} 원</th>
                                            <th class="t_r">{{ number_format($totals['point_deposit_amount'] ?? 0) }} / {{ number_format($totals['point_used_amount'] ?? 0) }} P</th>
                                            <th class="t_r">{{ number_format($totals['payout_amount'] ?? $totals['settlement_amount']) }} 원</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        <div class="btm_btn mt10">
                            <a href="{{ route('channel.settlement.list', ['period' => $period]) }}" class="col5">목록으로</a>
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
