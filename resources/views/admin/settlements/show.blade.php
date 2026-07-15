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
                    <div class="ttl">정산 상세{{ $isPreview ? ' 미리보기' : '' }}</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>정산관리</li>
                        <li>{{ $settlement->period }}</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if(session('success_message'))
                            <div style="background:#e8f5e9; color:#1b5e20; padding:12px; margin-bottom:15px; border-radius:4px;">
                                {{ session('success_message') }}
                            </div>
                        @endif

                        <div class="tb01" style="margin-bottom:15px;">
                            <table>
                                <colgroup>
                                    <col width="160px"><col width="">
                                    <col width="160px"><col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th>판매자</th>
                                        <td>{{ $settlement->vendor_name }}</td>
                                        <th>Shop 채널</th>
                                        <td>{{ $settlement->shop_channel_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>정산방식</th>
                                        <td>{{ $settlement->settlement_type == 2 ? '판매 개당 금액' : '판매금액 대비 %' }} / {{ number_format($settlement->settlement_rate, 2) }}{{ $settlement->settlement_type == 2 ? '원' : '%' }}</td>
                                        <th>상태</th>
                                        <td>
                                            @if($settlement->status === 'completed')
                                                정산완료
                                            @elseif($settlement->status === 'preview')
                                                미생성 미리보기
                                            @else
                                                생성완료
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>매출 / 매입</th>
                                        <td>{{ number_format($settlement->invoice_sales_amount ?? $settlement->gross_sales_amount) }}원 / {{ number_format($settlement->invoice_purchase_amount ?? 0) }}원</td>
                                        <th>지급액</th>
                                        <td><strong class="fcol4">{{ number_format($settlement->payout_amount ?? $settlement->settlement_amount) }}원</strong></td>
                                    </tr>
                                    <tr>
                                        <th>포인트 예수금 / 사용</th>
                                        <td>{{ number_format($settlement->point_deposit_amount ?? 0) }}P / {{ number_format($settlement->point_used_amount ?? 0) }}P</td>
                                        <th>Me9 수수료</th>
                                        <td>{{ number_format($settlement->admin_amount) }}원</td>
                                    </tr>
                                    <tr>
                                        <th>주문 / 품목</th>
                                        <td>{{ number_format($settlement->order_count) }}건 / {{ number_format($settlement->item_count) }}품목</td>
                                        <th>합계 검증</th>
                                        <td>
                                            @if($isBalanced)
                                                <strong class="fcol4">목록 합계와 상세 합계 일치</strong>
                                            @else
                                                <strong class="fcol1">상세 합계 확인 필요</strong>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10" style="margin-bottom:10px;">
                            @if(!$isPreview && $settlement->status !== 'completed')
                                <form method="POST" action="{{ route('admin.settlements.complete', $settlement->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn02" onclick="return confirm('정산 완료 처리하시겠습니까? 완료 후 해당 주문 품목은 정산완료 상태가 됩니다.');">정산완료 처리</button>
                                </form>
                            @elseif($isPreview)
                                <span class="fcol1" style="margin-right:10px;">정산자료 생성 전 미리보기입니다.</span>
                            @endif
                            <a href="{{ route('admin.settlements.index', ['period' => $settlement->period]) }}" class="btn02 col5">목록</a>
                        </div>

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="120px">
                                    <col width="140px">
                                    <col width="130px">
                                    <col width="260px">
                                    <col width="80px">
                                    <col width="120px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="130px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>확정일</th>
                                        <th>주문번호</th>
                                        <th>상품코드</th>
                                        <th>상품명</th>
                                        <th>수량</th>
                                        <th>역할</th>
                                        <th>매출</th>
                                        <th>매입</th>
                                        <th>포인트</th>
                                        <th>지급액</th>
                                        <th>Me9 수수료</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr>
                                            <td>{{ optional($item->confirmed_at)->format('Y-m-d') }}</td>
                                            <td>{{ $item->order_no }}</td>
                                            <td>{{ $item->product_code }}</td>
                                            <td class="t_l">{{ $item->product_name }}</td>
                                            <td class="t_r">{{ number_format($item->quantity) }}</td>
                                            <td>{{ $roleLabels[$item->settlement_role ?? 'seller'] ?? ($item->settlement_role ?? 'seller') }}</td>
                                            <td class="t_r">{{ number_format($item->invoice_sales_amount ?? $item->gross_sales_amount) }}원</td>
                                            <td class="t_r">{{ number_format($item->invoice_purchase_amount ?? 0) }}원</td>
                                            <td class="t_r">{{ number_format($item->point_deposit_amount ?? 0) }} / {{ number_format($item->point_used_amount ?? 0) }}P</td>
                                            <td class="t_r bold fcol4">{{ number_format($item->payout_amount ?? $item->settlement_amount) }}원</td>
                                            <td class="t_r">{{ number_format($item->admin_amount) }}원</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="no_data">정산 상세 품목이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($items->total() > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="4">상세 합계</th>
                                            <th class="t_r">{{ number_format($detailTotals['quantity']) }}</th>
                                            <th></th>
                                            <th class="t_r">{{ number_format($detailTotals['invoice_sales_amount'] ?? $detailTotals['gross_sales_amount']) }}원</th>
                                            <th class="t_r">{{ number_format($detailTotals['invoice_purchase_amount'] ?? 0) }}원</th>
                                            <th class="t_r">{{ number_format($detailTotals['point_deposit_amount'] ?? 0) }} / {{ number_format($detailTotals['point_used_amount'] ?? 0) }}P</th>
                                            <th class="t_r">{{ number_format($detailTotals['payout_amount'] ?? $detailTotals['settlement_amount']) }}원</th>
                                            <th class="t_r">{{ number_format($detailTotals['admin_amount']) }}원</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        <div class="page_bx1">
                            {{ $items->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
