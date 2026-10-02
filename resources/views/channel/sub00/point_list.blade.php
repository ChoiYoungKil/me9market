@extends('layouts.channel')

@php
    $dep1_id = "00";
    $dep1_tit = "포인트관리";
    $typeLabels = [
        'earn' => '분배',
        'first_visit' => '첫 방문 지급(이전)',
        'use' => '구매 사용',
        'convert_out' => 'Me9 전환',
        'refund' => '취소/반품 복구',
    ];
    $historyLabels = [
        'all' => '전체',
        'earn' => '분배내역',
        'use' => '소진내역',
        'refund' => '복구내역',
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
                            <div class="count">고객 보유 포인트 <strong>{{ number_format($summary['balance'] ?? 0) }}</strong> P</div>
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
                                        <th><span>분배 포인트</span></th>
                                        <td>{{ number_format($summary['distributed'] ?? 0) }} P</td>
                                        <th><span>구매 사용</span></th>
                                        <td>{{ number_format($summary['used'] ?? 0) }} P</td>
                                        <th><span>Me9 전환</span></th>
                                        <td>{{ number_format($summary['converted'] ?? 0) }} P</td>
                                    </tr>
                                    <tr>
                                        <th><span>취소/반품 복구</span></th>
                                        <td colspan="5">{{ number_format($summary['restored'] ?? 0) }} P</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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
                                        <th>Shop 채널</th>
                                        <th>포인트</th>
                                        <th>주문번호</th>
                                        <th>내역</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                            <td>{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ $typeLabels[$transaction->type] ?? $transaction->type }}</td>
                                            <td>{{ $transaction->shopChannel?->channel_name }}</td>
                                            <td class="t_r">
                                                <span class="{{ $transaction->points >= 0 ? 'fcol5' : 'fcol3' }}">
                                                    {{ $transaction->points >= 0 ? '+' : '' }}{{ number_format($transaction->points) }} P
                                                </span>
                                            </td>
                                            <td>{{ $transaction->order_id ?: '-' }}</td>
                                            <td>{{ $transaction->description ?: '-' }}</td>
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
