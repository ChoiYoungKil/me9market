@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>제휴/문의 관리</h1>
@include('admin.shared.messages')
<section class="admin-panel"><form class="admin-filter" method="get"><label>제목 / 회사 / 이름<input type="search" name="keyword" maxlength="100" value="{{ request('keyword') }}"></label><label>처리 상태<select name="status"><option value="">전체</option>
@foreach(['pending'=>'대기중','processing'=>'처리중','completed'=>'완료'] as $value=>$label)
<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>
@endforeach
</select></label><label>목록 개수<select name="per_page">
@foreach([10,20,50,100] as $count)
<option value="{{ $count }}" @selected(request('per_page',20)==$count)>{{ $count }}개</option>
@endforeach
</select></label><button class="admin-btn primary">검색</button><a class="admin-btn" href="{{ route('admin.contacts') }}">초기화</a></form></section>
<div class="admin-toolbar"><p>총 <strong>{{ number_format($contacts->total()) }}</strong>건</p></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>번호</th><th>회사 / 이름</th><th>제목</th><th>등록일</th><th>처리 상태</th><th>답변</th><th>관리</th></tr></thead><tbody>
@forelse($contacts as $contact)
<tr><td>{{ $contact->id }}</td><td>{{ $contact->company }}<br>{{ $contact->name }}<br><small>{{ $contact->email }}</small></td><td class="text-left">{{ $contact->subject }}</td><td>{{ $contact->created_at?->format('Y-m-d H:i') }}</td><td><span class="admin-status">{{ ['pending'=>'대기중','processing'=>'처리중','completed'=>'완료'][$contact->status]??$contact->status }}</span></td><td>{{ $contact->admin_reply?'답변 등록':'미답변' }}</td><td class="nowrap"><a class="admin-btn primary" href="{{ url('admin/view-contact/'.$contact->id) }}">보기 / 답변</a></td></tr>
@empty
<tr><td colspan="7" class="admin-empty">검색된 문의가 없습니다.</td></tr>
@endforelse
</tbody></table></div><div class="admin-pagination">{{ $contacts->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div></div>
@endsection
