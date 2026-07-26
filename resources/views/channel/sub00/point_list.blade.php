@extends('layouts.channel')

@php
    $dep1_id = "00";
    $dep1_tit = "포인트관리";
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
    $historyLabels = [
        'all' => '전체',
        'purchase' => '포인트 구매내역',
        'use' => '포인트 사용내역',
        'refund' => '환급내역',
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
                            <div class="btn_bx">
                                <a href="#" class="btn01 col5 pop_btn" data-pop="pop_purchase">포인트 구매</a>
                                <a href="#" class="btn01 pop_btn" data-pop="pop_refund">환급 요청</a>
                            </div>
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
                                        <th><span>누적 구매</span></th>
                                        <td>{{ number_format($summary['purchased'] ?? 0) }} P</td>
                                        <th><span>고객 페이백</span></th>
                                        <td>{{ number_format($summary['customer_payback'] ?? 0) }} P</td>
                                        <th><span>문자 차감</span></th>
                                        <td>{{ number_format($summary['sms_used'] ?? 0) }} P</td>
                                    </tr>
                                    <tr>
                                        <th><span>환급 완료</span></th>
                                        <td>{{ number_format($summary['refunded'] ?? 0) }} P</td>
                                        <th><span>구매 승인대기</span></th>
                                        <td>{{ number_format($summary['pending_purchase'] ?? 0) }} P</td>
                                        <th><span>환급 승인대기</span></th>
                                        <td>{{ number_format($summary['pending_refund'] ?? 0) }} P</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="mt10 fcol1">
                            판매자 포인트는 고객 상품 구매확정 페이백과 문자 발송 비용으로 차감됩니다. 환급은 운영 중인 Shop 채널이 없는 경우에만 요청할 수 있습니다.
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
                                            <select name="history" style="width:180px;">
                                                @foreach($historyLabels as $value => $label)
                                                    <option value="{{ $value }}" {{ ($filters['history'] ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <th class="w160"><span>상태</span></th>
                                        <td>
                                            <select name="status" style="width:160px;">
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
                                        <th>결제/환급액</th>
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

                        <div class="popup_bx" data-id="pop_refund">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w800">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">포인트 환급</div>
                                        </div>
                                        <form method="POST" action="{{ route('channel.point.refund') }}">
                                            @csrf
                                            <div class="conbx">
                                                <div class="con_w">
                                                    <div class="tb01">
                                                        <table>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>환급 조건</span></th>
                                                                    <td colspan="3">
                                                                        @if($canRequestRefund)
                                                                            운영 중인 Shop 채널이 없어 환급 요청이 가능합니다.
                                                                        @else
                                                                            운영 중인 Shop 채널이 있어 환급 요청이 제한됩니다.
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>보유 포인트</span></th>
                                                                    <td colspan="3">{{ number_format($summary['balance'] ?? 0) }} P</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>환급 포인트<em>필수</em></span></th>
                                                                    <td colspan="3">
                                                                        <input type="number" name="points" min="1000" step="1000" required {{ $canRequestRefund ? '' : 'disabled' }}>
                                                                        <p class="mt10 fcol3">포인트는 1,000P 단위로 환급 요청할 수 있습니다.</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>메모</span></th>
                                                                    <td colspan="3"><textarea name="memo" {{ $canRequestRefund ? '' : 'disabled' }}></textarea></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="btm_btn mt10">
                                                <button type="submit" class="btn01" style="border:0;" {{ $canRequestRefund ? '' : 'disabled' }}>환급 요청</button>
                                                <a href="#" class="col5 close_btn">닫기</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="popup_bx" data-id="pop_purchase">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w800">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">포인트 구매</div>
                                        </div>
                                        <form method="POST" action="{{ route('channel.point.purchase') }}">
                                            @csrf
                                            <div class="conbx">
                                                <div class="con_w">
                                                    <div class="tb01">
                                                        <table>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>구매 목적</span></th>
                                                                    <td colspan="3">고객 구매확정 페이백과 판매/배송/회원 안내 문자 발송 비용으로 사용할 보증 포인트입니다.</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>포인트<em>필수</em></span></th>
                                                                    <td colspan="3">
                                                                        <input type="number" name="points" min="1000" step="1000" required>
                                                                        <p class="mt10 fcol1">1P = 1원 기준으로 승인 후 보유 포인트에 반영됩니다.</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>결제 방법</span></th>
                                                                    <td colspan="3">
                                                                        <ul class="chk01">
                                                                            <li>
                                                                                <input type="radio" name="payment_method" id="point_payment_card" value="card" checked>
                                                                                <label for="point_payment_card">카드 결제</label>
                                                                            </li>
                                                                            <li>
                                                                                <input type="radio" name="payment_method" id="point_payment_transfer" value="transfer">
                                                                                <label for="point_payment_transfer">실시간 계좌이체</label>
                                                                            </li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>메모</span></th>
                                                                    <td colspan="3"><textarea name="memo"></textarea></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="btm_btn mt10">
                                                <button type="submit" class="btn01" style="border:0;">구매 요청</button>
                                                <a href="#" class="col5 close_btn">닫기</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function() {
            $(".pop_btn").click(function(){
                var popId = $(this).attr("data-pop");
                $(".popup_bx[data-id='"+popId+"']").stop().fadeIn(300);
                $(".popup_bx[data-id='"+popId+"']").scrollTop(0);
                return false;
            });
            $(".popup_bx .close_btn").click(function(){
                $(this).parents(".popup_bx").stop().fadeOut(300);
                return false;
            });
        });
    </script>
@endpush
