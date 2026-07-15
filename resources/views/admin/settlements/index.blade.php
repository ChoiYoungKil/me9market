@extends('layouts.admin')

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
                        <li>전체관리자</li>
                        <li>정산관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if(session('success_message'))
                            <div style="background:#e8f5e9; color:#1b5e20; padding:12px; margin-bottom:15px; border-radius:4px;">
                                {{ session('success_message') }}
                            </div>
                        @endif

                        <form method="GET" action="{{ route('admin.settlements.index') }}" class="tb01" style="margin-bottom:15px;">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th><span>정산 기간</span></th>
                                        <td>
                                            <select name="period" style="width:200px;">
                                                @foreach($periodOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <span class="fcol1" style="margin-left:10px;">구매확정 품목 기준, 1개월 단위 집계</span>
                                        </td>
                                        <td class="t_c">
                                            <button type="submit" class="btn02 col5">검색</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                        <form method="POST" action="{{ route('admin.settlements.generate') }}" style="display:flex; justify-content:flex-end; margin-bottom:10px;">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                            <button type="submit" class="btn02">정산자료 생성/갱신</button>
                        </form>

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="100px">
                                    <col width="150px">
                                    <col width="180px">
                                    <col width="150px">
                                    <col width="160px">
                                    <col width="90px">
                                    <col width="90px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="100px">
                                    <col width="110px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>정산기간</th>
                                        <th>판매자</th>
                                        <th>Shop 채널</th>
                                        <th>정산역할</th>
                                        <th>정산방식</th>
                                        <th>주문</th>
                                        <th>수량</th>
                                        <th>매출</th>
                                        <th>매입</th>
                                        <th>포인트 예수금</th>
                                        <th>포인트 사용</th>
                                        <th>지급액</th>
                                        <th>Me9 수수료</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $row)
                                        <tr>
                                            <td>{{ $row['period'] }}</td>
                                            <td>{{ $row['vendor_name'] }}</td>
                                            <td>{{ $row['shop_channel_name'] }}</td>
                                            <td>{{ $roleLabels[$row['settlement_role'] ?? 'seller'] ?? ($row['settlement_role'] ?? 'seller') }}</td>
                                            <td>
                                                {{ (int)$row['settlement_type'] === 2 ? '판매 개당 금액' : '판매금액 대비 %' }}
                                                / {{ number_format($row['settlement_rate'], 2) }}{{ (int)$row['settlement_type'] === 2 ? '원' : '%' }}
                                            </td>
                                            <td class="t_r">{{ number_format($row['order_count']) }}건</td>
                                            <td class="t_r">{{ number_format($row['quantity']) }}개</td>
                                            <td class="t_r">{{ number_format($row['invoice_sales_amount'] ?? $row['gross_sales_amount']) }}원</td>
                                            <td class="t_r">{{ number_format($row['invoice_purchase_amount'] ?? 0) }}원</td>
                                            <td class="t_r">{{ number_format($row['point_deposit_amount'] ?? 0) }}P</td>
                                            <td class="t_r">{{ number_format($row['point_used_amount'] ?? 0) }}P</td>
                                            <td class="t_r bold fcol4">{{ number_format($row['payout_amount'] ?? $row['settlement_amount']) }}원</td>
                                            <td class="t_r">{{ number_format($row['admin_amount']) }}원</td>
                                            <td>
                                                @if($row['status'] === 'completed')
                                                    정산완료
                                                @elseif($row['status'] === 'pending')
                                                    생성완료
                                                @else
                                                    미생성
                                                @endif
                                            </td>
                                            <td>
                                                @if($row['run_id'])
                                                    <a href="{{ route('admin.settlements.show', $row['run_id']) }}" class="btn02 col5">보기</a>
                                                @else
                                                    <a href="{{ route('admin.settlements.preview', ['period' => $row['period'], 'vendor_id' => $row['vendor_id'], 'shop_channel_id' => $row['shop_channel_id'] ?: 0]) }}" class="btn02 col5">보기</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="no_data">정산 대상 구매확정 주문이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($rows->count() > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">합계</th>
                                            <th class="t_r">{{ number_format($totals['order_count']) }}건</th>
                                            <th class="t_r">{{ number_format($totals['quantity']) }}개</th>
                                            <th class="t_r">{{ number_format($totals['invoice_sales_amount'] ?? $totals['gross_sales_amount']) }}원</th>
                                            <th class="t_r">{{ number_format($totals['invoice_purchase_amount'] ?? 0) }}원</th>
                                            <th class="t_r">{{ number_format($totals['point_deposit_amount'] ?? 0) }}P</th>
                                            <th class="t_r">{{ number_format($totals['point_used_amount'] ?? 0) }}P</th>
                                            <th class="t_r">{{ number_format($totals['payout_amount'] ?? $totals['settlement_amount']) }}원</th>
                                            <th class="t_r">{{ number_format($totals['admin_amount']) }}원</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
