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
        $pgLabels = [
            'own_pg' => '자사PG',
            'me9_pg' => '공용PG',
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
                                            <select name="period" class="w160">
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
                                        <th>PG구분</th>
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
                                            <td>{{ $pgLabels[$row['payment_gateway_type'] ?? 'me9_pg'] ?? '공용PG' }}</td>
                                            <td class="t_r">{{ number_format($row['order_count']) }}건</td>
                                            <td class="t_r">{{ number_format($row['quantity']) }}개</td>
                                            <td class="t_r">{{ number_format($row['invoice_sales_amount'] ?? $row['gross_sales_amount']) }}원</td>
                                            <td class="t_r">{{ number_format($row['invoice_purchase_amount'] ?? 0) }}원</td>
                                            <td class="t_r">{{ number_format($row['point_deposit_amount'] ?? 0) }}P</td>
                                            <td class="t_r">{{ number_format($row['point_used_amount'] ?? 0) }}P</td>
                                            <td class="t_r bold fcol4">
                                                {{ number_format($row['payout_amount'] ?? $row['settlement_amount']) }}원
                                                @if(($row['payment_gateway_type'] ?? 'me9_pg') === 'own_pg')
                                                    <p class="fcol1" style="font-size:12px;">자사PG 결제분 제외</p>
                                                @endif
                                            </td>
                                            <td class="t_r">{{ number_format($row['admin_amount']) }}원</td>
                                            <td>
                                                @if(in_array($row['status'], ['completed', 'pending'], true))
                                                    정산자료 생성
                                                @else
                                                    구매확정 기준
                                                @endif
                                            </td>
                                            <td>
                                                @if($row['run_id'])
                                                    <a href="{{ route('admin.settlements.show', $row['run_id']) }}" class="btn02 col5">보기</a>
                                                    <a href="{{ route('admin.settlements.export', $row['run_id']) }}" class="btn02">엑셀</a>
                                                @else
                                                    <a href="{{ route('admin.settlements.preview', ['period' => $row['period'], 'vendor_id' => $row['vendor_id'], 'shop_channel_id' => $row['shop_channel_id'] ?: 0]) }}" class="btn02 col5">보기</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="16" class="no_data">정산 대상 구매확정 주문이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($rows->count() > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="6">합계</th>
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

                        <div class="ttl01 mt30">정산 집행 등록</div>
                        <form method="POST" action="{{ route('admin.settlements.executions.store') }}" enctype="multipart/form-data" class="tb01" style="margin-bottom:15px;">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th><span>정산대상</span></th>
                                        <td colspan="3">
                                            <select name="settlement_run_id" class="wFull">
                                                <option value="">기간 전체 집행</option>
                                                @foreach($rows->whereNotNull('run_id') as $row)
                                                    <option value="{{ $row['run_id'] }}">
                                                        {{ $row['vendor_name'] }} / {{ $row['shop_channel_name'] }} / {{ $roleLabels[$row['settlement_role'] ?? 'seller'] ?? ($row['settlement_role'] ?? 'seller') }} / {{ number_format($row['payout_amount'] ?? $row['settlement_amount']) }}원
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><span>집행 제목<em>필수</em></span></th>
                                        <td><input type="text" name="title" class="wFull" required></td>
                                        <th><span>집행 금액<em>필수</em></span></th>
                                        <td><input type="text" name="amount" class="w160" inputmode="numeric" required></td>
                                    </tr>
                                    <tr>
                                        <th><span>집행일</span></th>
                                        <td><input type="date" name="executed_at" class="w160" value="{{ now()->format('Y-m-d') }}"></td>
                                        <th><span>첨부자료</span></th>
                                        <td><input type="file" name="attachment" class="wFull" accept=".xls,.xlsx,.csv,.pdf"></td>
                                    </tr>
                                    <tr>
                                        <th><span>메모</span></th>
                                        <td colspan="3"><input type="text" name="memo" class="wFull"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="t_r">
                                            <button type="submit" class="btn02 col5">집행 등록</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                        <div class="ttl01">정산 집행 내역</div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="80px">
                                    <col width="140px">
                                    <col width="220px">
                                    <col width="160px">
                                    <col width="180px">
                                    <col width="130px">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>집행일</th>
                                        <th>제목</th>
                                        <th>판매자</th>
                                        <th>Shop 채널</th>
                                        <th>금액</th>
                                        <th>첨부자료</th>
                                        <th>메모</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($executions as $execution)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ optional($execution->executed_at)->format('Y-m-d') }}</td>
                                            <td>{{ $execution->title }}</td>
                                            <td>{{ $execution->vendor?->name ?? '-' }}</td>
                                            <td>{{ $execution->shopChannel?->channel_name ?? '-' }}</td>
                                            <td class="t_r">{{ number_format($execution->amount) }}원</td>
                                            <td>
                                                @if($execution->attachment_path)
                                                    <a href="{{ route('admin.settlements.executions.download', $execution->id) }}" class="btn02">다운로드</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $execution->memo ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="no_data">등록된 정산 집행 내역이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
