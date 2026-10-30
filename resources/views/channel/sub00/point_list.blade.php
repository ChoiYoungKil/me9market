@extends('layouts.channel')
@section('page_type','sub')
@php
    $dep1_id = '00';
    $typeLabels = ['earn'=>'상품 구매 지급', 'first_visit'=>'첫 방문 지급(이전)', 'use'=>'구매 사용', 'convert_out'=>'Me9 포인트 전환', 'refund'=>'취소·반품 복구'];
    $historyLabels = ['all'=>'전체', 'earn'=>'분배', 'use'=>'소진'];
    if ($filters['history'] === 'refund') $historyLabels['refund'] = '소진 취소';
    $filterValue = static function ($name) use ($filters) {
        $value = old($name, $filters[$name]);
        return is_string($value) ? $value : $filters[$name];
    };
@endphp
@push('styles')
<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}">
<link rel="stylesheet" href="/channel_assets/css/point-history.css?v={{ filemtime(public_path('channel_assets/css/point-history.css')) }}">
@endpush
@section('content')
<div id="contents"><div class="order-workspace point-workspace">
    <div class="order-page-heading"><div><p>환경설정</p><h1>포인트 관리</h1></div></div>
    @if($errors->any())<section class="order-panel point-error" role="alert">{{ $errors->first() }}</section>@endif
    <section class="order-panel" aria-labelledby="point-summary-title">
        <h2 id="point-summary-title">포인트 현황</h2>
        <div class="point-totals">
            <div class="point-balance"><span>총 포인트 통화량</span><strong>{{ number_format($summary['balance']) }} <small>Point</small></strong></div>
            <dl><div><dt>분배 포인트</dt><dd>{{ number_format($summary['distributed']) }} Point</dd></div><div><dt>소진 포인트</dt><dd>{{ number_format($summary['consumed']) }} Point</dd></div></dl>
        </div>
        <p class="point-help">전체 기간 기준 · 총 포인트 = 분배 포인트 − 소진 포인트</p>
        <p class="point-help">소진 포인트는 구매 사용 {{ number_format($summary['used']) }} Point · Me9 전환 {{ number_format($summary['converted']) }} Point에서 취소·반품 복구 {{ number_format($summary['restored']) }} Point를 반영합니다.</p>
    </section>
    <section class="order-panel" aria-labelledby="point-search-title">
        <h2 id="point-search-title">상세검색</h2>
        <form method="GET" action="{{ route('channel.point.list') }}" class="point-search">
            <fieldset class="point-dates"><legend>날짜</legend><div>
                <label>시작일<input type="date" name="date_from" value="{{ $filterValue('date_from') }}"></label>
                <span aria-hidden="true">~</span>
                <label>종료일<input type="date" name="date_to" value="{{ $filterValue('date_to') }}"></label>
            </div></fieldset>
            <fieldset><legend>구분</legend><div class="order-checks">
                @foreach($historyLabels as $value=>$label)<label><input type="radio" name="history" value="{{ $value }}" @checked($filterValue('history') === $value)>{{ $label }}</label>@endforeach
            </div></fieldset>
            <div class="point-search-actions"><button type="submit" class="primary">검색</button><a class="order-button" href="{{ route('channel.point.list') }}">초기화</a></div>
        </form>
        <p class="point-help">소진 검색에는 구매 사용·Me9 전환과 이를 되돌린 취소·반품 복구 내역이 포함됩니다.</p>
    </section>
    <section class="order-panel" aria-labelledby="point-history-title">
        <div class="order-toolbar"><h2 id="point-history-title">포인트 이력</h2><p>총 <strong>{{ number_format($transactions->total()) }}</strong> 건 · 검색 결과 합계 <strong>{{ $filteredPoints > 0 ? '+' : '' }}{{ number_format($filteredPoints) }}</strong> Point</p></div>
        <div class="order-table-scroll" tabindex="0" role="region" aria-label="포인트 이력 목록"><table class="order-table"><thead><tr><th scope="col">No</th><th scope="col">등록일</th><th scope="col">구분</th><th scope="col">포인트</th><th scope="col">내역</th></tr></thead><tbody>
        @forelse($transactions as $transaction)
            @php($restoration = $transaction->type === 'refund')
            <tr>
                <td>{{ $transactions->total() - $transactions->firstItem() - $loop->index + 1 }}</td>
                <td>{{ $transaction->created_at?->format('Y-m-d H:i:s') ?: '-' }}</td>
                <td><span class="order-state">{{ $restoration ? '소진 취소' : ($transaction->points > 0 ? '분배' : '소진') }}</span><small>{{ $typeLabels[$transaction->type] ?? '포인트 조정' }}</small></td>
                <td class="point-amount {{ $transaction->points >= 0 ? 'point-credit' : 'point-debit' }}">{{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points) }} Point</td>
                <td class="point-description"><strong>{{ $transaction->user?->name ?: '회원정보 없음' }}</strong><p>{{ $transaction->description ?: ($typeLabels[$transaction->type] ?? '포인트 조정') }}</p><small>{{ $transaction->shopChannel?->channel_name ?: '-' }}@if($transaction->order_id) · 주문번호 {{ $transaction->order_id }}@endif</small></td>
            </tr>
        @empty<tr><td colspan="5" class="order-empty">검색 조건에 맞는 포인트 내역이 없습니다.</td></tr>
        @endforelse
        </tbody></table></div>
        {{ $transactions->links() }}
    </section>
</div></div>
@endsection
