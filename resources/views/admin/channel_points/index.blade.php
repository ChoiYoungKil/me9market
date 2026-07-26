@extends('layouts.admin')

@php
    $typeLabels = [
        'purchase' => '구매',
        'customer_payback' => '고객 페이백',
        'sms' => '문자 차감',
        'refund' => '환급',
    ];
    $statusLabels = [
        'all' => '전체',
        'pending' => '승인대기',
        'approved' => '승인완료',
        'rejected' => '반려',
    ];
@endphp

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">판매자 포인트 판매/사용 내역</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>전체관리자</li>
                        <li>판매자 포인트 판매/사용 내역</li>
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

                        <div class="tb01" style="margin-bottom:15px;">
                            <table>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>포인트 판매완료</span></th>
                                        <td>{{ number_format($totals['approved_purchase'] ?? 0) }} P</td>
                                        <th class="w160"><span>구매 승인대기</span></th>
                                        <td>{{ number_format($totals['pending_purchase'] ?? 0) }} P</td>
                                        <th class="w160"><span>환급 승인대기</span></th>
                                        <td>{{ number_format($totals['pending_refund'] ?? 0) }} P</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>고객 페이백 차감</span></th>
                                        <td>{{ number_format($totals['approved_payback'] ?? 0) }} P</td>
                                        <th class="w160"><span>문자 차감</span></th>
                                        <td>{{ number_format($totals['approved_sms'] ?? 0) }} P</td>
                                        <th class="w160"><span>전체 승인 잔액</span></th>
                                        <td>{{ number_format($totals['approved_balance'] ?? 0) }} P</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <form method="GET" action="{{ route('admin.channel_points.index') }}" class="tb01" style="margin-bottom:15px;">
                            <table>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>상태</span></th>
                                        <td>
                                            <select name="status" style="width:160px;">
                                                @foreach($statusLabels as $value => $label)
                                                    <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <th class="w160"><span>구분</span></th>
                                        <td>
                                            <select name="type" style="width:180px;">
                                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체</option>
                                                @foreach($typeLabels as $value => $label)
                                                    <option value="{{ $value }}" {{ $type === $value ? 'selected' : '' }}>{{ $label }}</option>
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

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="80px">
                                    <col width="150px">
                                    <col width="180px">
                                    <col width="180px">
                                    <col width="110px">
                                    <col width="110px">
                                    <col width="140px">
                                    <col width="">
                                    <col width="170px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>등록일</th>
                                        <th>판매자</th>
                                        <th>Shop 채널</th>
                                        <th>구분</th>
                                        <th>상태</th>
                                        <th>포인트</th>
                                        <th>관리자/판매자 내역</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                            <td>{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ $transaction->vendor?->name ?? '판매자 #' . $transaction->vendor_id }}</td>
                                            <td>{{ $transaction->shopChannel?->channel_name ?? '-' }}</td>
                                            <td>{{ $typeLabels[$transaction->type] ?? $transaction->type }}</td>
                                            <td>{{ $statusLabels[$transaction->status] ?? $transaction->status }}</td>
                                            <td class="t_r">{{ $transaction->points >= 0 ? '+' : '' }}{{ number_format($transaction->points) }} P</td>
                                            <td>{{ $transaction->memo ?: '-' }}</td>
                                            <td class="t_c">
                                                @if($transaction->status === 'pending')
                                                    <form method="POST" action="{{ route('admin.channel_points.approve', $transaction->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn02 col5" style="border:0;">승인</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.channel_points.reject', $transaction->id) }}" style="display:inline-block; margin-left:4px;">
                                                        @csrf
                                                        <input type="hidden" name="memo" value="{{ $transaction->memo }}">
                                                        <button type="submit" class="btn02" style="border:0;">반려</button>
                                                    </form>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="t_c">포인트 내역이 없습니다.</td>
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
