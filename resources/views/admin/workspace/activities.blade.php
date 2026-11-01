@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>관리자 처리 이력</h1>
<section class="admin-panel"><form class="admin-filter" method="get"><label>담당자 / 대상 / 처리 내용<input type="search" name="keyword" value="{{ request('keyword') }}" maxlength="100"></label><button class="admin-btn primary">검색</button><a class="admin-btn admin-button-reset" href="{{ route('admin.activities') }}">초기화</a></form></section>
<p class="admin-help">완료된 변경 요청의 대상과 변경 전·후 값을 기록합니다. 비밀번호·연락처·본문 전문은 저장하지 않습니다. 이전 기록은 당시 수집된 정보만 표시합니다.</p>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>시각</th><th>담당자</th><th>처리 대상</th><th>처리 내용</th><th>처리 방식</th><th>결과</th><th>상세</th></tr></thead><tbody>
@forelse($entries as $entry)
<tr><td class="nowrap">{{ $entry->created_at?->format('Y-m-d H:i:s') }}</td><td>{{ $entry->actor }}</td><td class="text-left">
@forelse(array_slice($entry->metadata['changes']??[],0,2) as $change)
<div>{{ $change['resource'] }} #{{ $change['id'] }} · {{ $change['name'] }}</div>
@empty
{{ collect($entry->metadata['ids']??[])->map(fn($id)=>'#'.$id)->implode(', ') ?: '상세 대상 미수집' }}
@endforelse
@if(count($entry->metadata['changes']??[])>2)
<small class="admin-help">외 {{ count($entry->metadata['changes'])-2 }}개 변경 기록</small>
@endif
</td><td>{{ $entry->description }}</td><td>{{ $entry->metadata['mode']??'이전 기록' }}</td><td><span class="admin-status">{{ isset($entry->metadata['result'])?'처리 완료':'요청 기록' }}</span></td><td><button type="button" class="admin-btn" data-activity-detail="{{ route('admin.activities.show',$entry->id) }}">상세보기</button></td></tr>
@empty
@include('components.table-empty-row', ['columns' => 7])
@endforelse
</tbody></table></div><div class="admin-pagination">{{ $entries->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div>
<dialog id="admin-activity-detail" class="admin-modal"><div class="admin-modal-header"><h2>처리 상세</h2><button type="button" class="admin-btn" data-dialog-close aria-label="처리 상세 닫기">×</button></div><div data-activity-body></div><div class="admin-modal-footer"><button type="button" class="admin-btn" data-dialog-close>닫기</button></div></dialog>
</div>
@endsection
