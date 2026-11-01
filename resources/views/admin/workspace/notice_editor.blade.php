
@extends($popup ? 'layouts.admin_popup' : 'layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>{{ $notice->exists?'공지사항 수정':'공지사항 작성' }}</h1>
@include('admin.shared.messages')
<div class="admin-toolbar"><div class="admin-actions"><a class="admin-btn" href="{{ url('admin/notices') }}">공지 목록</a><a class="admin-btn" href="{{ url('admin/newpage') }}" data-new-window>새창에서 작성</a><button class="admin-btn" type="button" data-popover="notice-help" aria-expanded="false">작성 안내</button>
@unless($modal)<button type="button" class="admin-btn" data-dialog-open="notice-layer">작성 창 열기</button>
@endunless</div></div>
<div id="notice-help" class="admin-popover" hidden><h2>공지 작성 안내</h2><p class="admin-help">제목과 내용을 입력한 뒤 공개 여부를 선택해 주세요. 비공개로 저장하면 고객에게 표시되지 않습니다.</p><button class="admin-btn small" type="button" data-popover-close>닫기</button></div>
@if(!$modal)<section class="admin-panel">
@include('admin.workspace.notice_fields',['editorId'=>'page','insideModal'=>false])</section>
@endif
<dialog id="notice-layer" class="admin-modal"
@if($modal) data-auto-dialog
@endif><div class="admin-modal-header"><h2>{{ $notice->exists?'공지사항 수정':'공지사항 작성' }}</h2><button type="button" class="admin-btn small" data-dialog-close aria-label="작성 창닫기">×</button></div>
@include('admin.workspace.notice_fields',['editorId'=>'layer','insideModal'=>true])</dialog>
@if($modal)<section class="admin-panel"><p>공지사항을 새로 작성합니다.</p><button type="button" class="admin-btn primary" data-dialog-open="notice-layer">공지 작성 창 열기</button></section>
@endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script src="{{ asset('common/admin-editor.js').'?v='.filemtime(public_path('common/admin-editor.js')) }}"></script>
@endpush
