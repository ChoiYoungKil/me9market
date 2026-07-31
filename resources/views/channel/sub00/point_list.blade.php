@extends('layouts.channel')

@php
    $dep1_id = "00";
    $dep1_tit = "포인트관리";
    $typeLabels = [
        'purchase' => '구매(이전)',
        'customer_payback' => '분배',
        'sms' => '문자 차감',
        'refund' => '환급(이전)',
    ];
    $statusLabels = [
        'all' => '전체',
        'pending' => '승인대기',
        'approved' => '승인완료',
        'rejected' => '반려',
    ];
    $historyLabels = [
        'all' => '전체',
        'purchase' => '구매내역(이전)',
        'use' => '분배/소진내역',
        'refund' => '환급내역(이전)',
    ];
@endphp

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">포인트 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>포인트 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if(session('success_message'))
                            <div style="background:#e8f5e9; color:#1b5e20; padding:12px; margin-bottom:15px; border-radius:4px;">
                                {{ session('success_message') }}
                            </div>
                        @endif
                        @if($errors->any())
                            <div style="background:#ffebee; color:#b71c1c; padding:12px; margin-bottom:15px; border-radius:4px;">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="list_top1 btn">
                            <div class="count">보유 포인트 <strong>{{ number_format($summary['balance'] ?? 0) }}</strong> P</div>
                        </div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th><span>이전 구매</span></th>
                                        <td>{{ number_format($summary['purchased'] ?? 0) }} P</td>
                                        <th><span>분배 포인트</span></th>
                                        <td>{{ number_format($summary['customer_payback'] ?? 0) }} P</td>
                                        <th><span>소진 포인트</span></th>
                                        <td>{{ number_format($summary['sms_used'] ?? 0) }} P</td>
                                    </tr>
                                    <tr>
                                        <th><span>이전 환급</span></th>
                                        <td>{{ number_format($summary['refunded'] ?? 0) }} P</td>
                                        <th><span>이전 구매 승인대기</span></th>
                                        <td>{{ number_format($summary['pending_purchase'] ?? 0) }} P</td>
                                        <th><span>이전 환급 승인대기</span></th>
                                        <td>{{ number_format($summary['pending_refund'] ?? 0) }} P</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="mt10 fcol1">
                            Ver3 정책에서는 포인트 구매/충전 요청 없이 상품 구매확정 시 분배되고, 고객 사용 또는 Shop 채널 포인트 전환 시 소진됩니다.
                        </p>
                    </div>
                </div>
            </div>

            <div class="box box1">
                <div class="conbx">
                    <div class="con_w">
                        <form method="GET" action="{{ route('channel.point.list') }}" class="tb01" style="margin-bottom:15px;">
                            <table>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>내역 구분</span></th>
                                        <td>
                                            <select name="history" class="w160">
                                                @foreach($historyLabels as $value => $label)
                                                    <option value="{{ $value }}" {{ ($filters['history'] ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <th class="w160"><span>상태</span></th>
                                        <td>
                                            <select name="status" class="w160">
                                                @foreach($statusLabels as $value => $label)
                                                    <option value="{{ $value }}" {{ ($filters['status'] ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="t_c">
                                            <button type="submit" class="btn02 col5">검색</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                        <div class="list_top1">
                            <div class="count">총 <strong>{{ number_format($transactions->total()) }}</strong> 건</div>
                        </div>
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="70px">
                                    <col width="140px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>등록일</th>
                                        <th>구분</th>
                                        <th>상태</th>
                                        <th>포인트</th>
                                        <th>금액</th>
                                        <th>내역</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                            <td>{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ $typeLabels[$transaction->type] ?? $transaction->type }}</td>
                                            <td>{{ $statusLabels[$transaction->status] ?? $transaction->status }}</td>
                                            <td class="t_r">
                                                <span class="{{ $transaction->points >= 0 ? 'fcol5' : 'fcol3' }}">
                                                    {{ $transaction->points >= 0 ? '+' : '' }}{{ number_format($transaction->points) }} P
                                                </span>
                                            </td>
                                            <td class="t_r">{{ number_format($transaction->payment_amount ?? 0) }} 원</td>
                                            <td>{{ $transaction->memo ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="t_c">등록된 포인트 내역이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="page_bx1">
                            {{ $transactions->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
