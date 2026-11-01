<dl class="admin-detail-grid"><dt>처리 일시</dt><dd>{{ $entry->created_at?->format('Y-m-d H:i:s') }}</dd><dt>담당자</dt><dd>{{ $entry->actor }}</dd><dt>처리 내용</dt><dd>{{ $entry->description }}</dd><dt>처리 방식</dt><dd>{{ $entry->metadata['mode']??'이전 기록' }}</dd><dt>처리 결과</dt><dd>{{ isset($entry->metadata['result'])?'처리 완료':'요청 기록' }}</dd>
@if(!empty($entry->metadata['reason']))
<dt>처리 사유</dt><dd class="admin-preserve-lines">{{ $entry->metadata['reason'] }}</dd>
@endif
</dl>
@forelse($entry->metadata['changes']??[] as $change)
<section style="margin-top:24px"><h3>{{ $change['resource'] }} #{{ $change['id'] }} · {{ $change['name'] }}</h3><p class="admin-help">{{ ['created'=>'등록','updated'=>'수정','deleted'=>'삭제'][$change['kind']]??'변경' }}
@if($change['url'])
 · <a href="{{ url($change['url']) }}">현재 자료 보기</a>
@endif
</p><div class="admin-table-wrap"><table class="admin-table" style="min-width:500px"><thead><tr><th>변경 항목</th><th>변경 전</th><th>변경 후</th></tr></thead><tbody>
@foreach(array_filter($change['fields'],fn($field)=>$field['before']!==$field['after']) as $field)
<tr><td>{{ $field['label'] }}</td><td class="text-left">{{ $field['before']??'없음' }}</td><td class="text-left">{{ $field['after']??'없음' }}</td></tr>
@endforeach
</tbody></table></div></section>
@empty
<p class="admin-alert">이 기록에는 변경 전·후 값이 수집되지 않았습니다. 당시 확인 가능한 요청 경로와 대상 번호만 표시합니다.</p>
<p>대상 번호: {{ implode(', ',$entry->metadata['ids']??[]) ?: '미수집' }}</p>
<p class="admin-help">입력 항목: {{ implode(', ',$entry->metadata['fields']??[]) ?: '미수집' }}</p>
@endforelse
<p class="admin-help" style="margin-top:20px">처리 경로: {{ $entry->path }}</p>
