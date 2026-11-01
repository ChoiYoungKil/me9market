<form method="post" action="{{ $notice->exists ? route('admin.content.notices.update',$notice->id) : route('admin.content.notices.store') }}" enctype="multipart/form-data" data-admin-ajax>
@csrf
<div data-form-message></div><label class="admin-field">제목<input name="title" maxlength="500" required value="{{ old('title',$notice->title) }}" placeholder="공지사항 제목을 입력해 주세요"></label>
<label class="admin-field" for="notice-content-{{ $editorId }}">내용</label><textarea id="notice-content-{{ $editorId }}" name="content" data-admin-editor>{{ old('content',$notice->content) }}</textarea>
<fieldset class="admin-field"><legend>게시 설정</legend><div class="admin-checks"><label><input type="radio" name="status" value="1"
@checked(old('status',$notice->status)==1)>공개</label><label><input type="radio" name="status" value="0"
@checked(old('status',$notice->status)==0)>비공개</label><label><input type="checkbox" name="is_important" value="1"
@checked(old('is_important',$notice->is_important))>중요 공지</label></div></fieldset>
<label class="admin-field">첨부파일<input type="file" name="attachment" accept=".txt,.pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png" data-file-name="notice-file-{{ $editorId }}"><span class="admin-help" id="notice-file-{{ $editorId }}">{{ $notice->attachment ?: '선택한 파일 없음' }}</span></label><p class="admin-help">문서·이미지 파일 최대 10MB. 새 파일을 첨부하면 기존 첨부파일을 교체합니다.</p>
@if($notice->exists && $notice->attachment)<a class="admin-btn small" href="{{ route('admin.content.notices.download',$notice->id) }}">현재 첨부 다운로드</a>
@endif
<div class="admin-modal-footer">
@if($insideModal)<button type="button" class="admin-btn" data-dialog-close>취소</button>
@elseif($popup)<button type="button" class="admin-btn" data-popup-close>창닫기</button>
@else<a class="admin-btn" href="{{ url('admin/notices') }}">취소</a>
@endif<button type="submit" class="admin-btn primary">{{ $notice->exists?'수정 저장':'공지 등록' }}</button></div>
</form>
