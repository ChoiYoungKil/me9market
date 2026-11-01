@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace admin-closures">
    <div class="admin-closure-heading"><div><p>SHOP CHANNEL</p><h1>Shop 채널 운영중지 승인</h1></div><span class="admin-closure-caption">운영중지 요청 검토 · 처리</span></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-closure-search" aria-label="운영중지 요청 검색">
        <form method="get" action="{{ route('admin.shop_channel_closures.index') }}" class="admin-filter">
            <label>요청 상태<select name="status">
                @foreach(['all'=>'전체','requested'=>'승인대기','approved'=>'승인완료','rejected'=>'반려','none'=>'미요청'] as $value=>$label)
                <option value="{{ $value }}" @selected($status===$value)>{{ $label }}</option>
                @endforeach
            </select></label>
            <label class="admin-closure-keyword">검색어<input type="search" name="keyword" value="{{ $keyword }}" placeholder="채널명 · 채널코드 · 판매자" maxlength="100"></label>
            <div class="admin-actions"><button type="submit" class="admin-btn primary">검색</button><a href="{{ route('admin.shop_channel_closures.index') }}" class="admin-btn admin-button-reset">초기화</a></div>
        </form>
    </section>
    <div class="admin-toolbar"><p>운영중지 요청 목록 <strong>{{ number_format($channels->total()) }}</strong>건</p><span class="admin-help">승인대기 요청부터 표시합니다.</span></div>
    <div class="admin-table-wrap" tabindex="0" role="region" aria-label="채널 운영중지 요청 목록">
        <table class="admin-table admin-closure-table">
            <colgroup><col style="width:5%"><col style="width:17%"><col style="width:24%"><col style="width:7%"><col style="width:8%"><col style="width:12%"><col style="width:12%"><col style="width:15%"></colgroup>
            <thead><tr><th>번호</th><th>판매자</th><th>Shop 채널 / 채널코드</th><th>운영상태</th><th>요청상태</th><th>요청일시</th><th>메모</th><th>처리</th></tr></thead>
            <tbody>
            @forelse($channels as $channel)
                <tr>
                    <td>{{ $channels->firstItem()+$loop->index }}</td>
                    <td class="closure-name">{{ $channel->vendor?->name ?? '-' }}</td>
                    <td class="closure-name"><strong>{{ $channel->channel_name }}</strong><small>{{ $channel->channel_code }}</small></td>
                    <td><span class="admin-status {{ (int)$channel->status===1?'enabled':'disabled' }}">{{ (int)$channel->status===1?'운영':'중지' }}</span></td>
                    <td><span class="admin-status closure-state-{{ $channel->closure_status }}">{{ ['requested'=>'승인대기','approved'=>'승인완료','rejected'=>'반려','none'=>'미요청'][$channel->closure_status] ?? '미요청' }}</span></td>
                    <td class="nowrap">{{ $channel->closure_requested_at?->format('Y-m-d') ?? '-' }}<small class="closure-time">{{ $channel->closure_requested_at?->format('H:i') }}</small></td>
                    <td class="closure-memo">{{ $channel->closure_memo ?: '-' }}</td>
                    <td>
                    @if($channel->closure_status==='requested')
                        <div class="admin-row-actions">
                            <form method="post" action="{{ route('admin.shop_channel_closures.approve',$channel->id) }}">@csrf<button type="submit" class="admin-btn primary">승인</button></form>
                            <form method="post" action="{{ route('admin.shop_channel_closures.reject',$channel->id) }}">@csrf<button type="submit" class="admin-btn danger">반려</button></form>
                        </div>
                    @else
                        <span class="admin-help">{{ in_array($channel->closure_status,['approved','rejected'],true)?'처리 완료':'요청 없음' }}</span>
                    @endif
                    </td>
                </tr>
            @empty
                @include('components.table-empty-row', ['columns' => 8])
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">{{ $channels->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div>
</div>
@endsection
