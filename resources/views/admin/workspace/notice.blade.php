
@extends($popup ? 'layouts.admin_popup' : 'layouts.admin')
@section('content')
<div id="contents" class="admin-workspace"><h1>공지사항 상세</h1>
@include('admin.shared.messages')
<article class="admin-panel"><div class="admin-toolbar"><h2>{{ $notice->title }}</h2><span class="admin-status">{{ $notice->status?'공개':'비공개' }}{{ $notice->is_important?' · 중요 공지':'' }}</span></div><p class="admin-help">등록일 {{ $notice->created_at?->format('Y-m-d H:i') }} · 수정일 {{ $notice->updated_at?->format('Y-m-d H:i') }} · 조회수 {{ number_format($notice->view_count??0) }}</p><div class="admin-richtext">{!! app(\App\Services\AdminNoticeContent::class)->clean($notice->content??'') !!}</div>
@if($notice->attachment)<div class="admin-attachment"><span>첨부파일</span><a href="{{ route('admin.content.notices.download',$notice->id) }}">{{ $notice->attachment }}</a>
@if(in_array(strtolower(pathinfo($notice->attachment,PATHINFO_EXTENSION)),['jpg','jpeg','png']))<img src="{{ asset('admin/attachments/notices/'.$notice->attachment) }}" alt="공지 첨부 이미지" style="display:block;max-width:100%;max-height:400px;margin-top:16px">
@endif</div>
@endif
<div class="admin-modal-footer"><a class="admin-btn" href="{{ url('admin/notices') }}">목록</a><a class="admin-btn primary" href="{{ route('admin.content.notices.edit',$notice->id) }}">수정</a><a class="admin-btn" href="{{ request()->fullUrlWithQuery(['hide_comments'=>request()->boolean('hide_comments')?null:1]) }}">{{ request()->boolean('hide_comments')?'관리 댓글 표시':'관리 댓글 숨기기' }}</a></div></article>
@unless(request()->boolean('hide_comments'))<section class="admin-panel" style="margin-top:22px"><h2>관리 댓글 <small>{{ $comments->count() }}건</small></h2><p class="admin-help">관리자 내부 확인용 댓글이며 고객 화면에는 표시되지 않습니다.</p>
<form method="post" action="{{ route('admin.content.comments.store',$notice->id) }}">
@csrf<label class="admin-field">댓글<textarea name="content" maxlength="5000" required rows="3" style="min-height:85px"></textarea></label><button class="admin-btn primary" type="submit">댓글 등록</button></form>
@forelse($comments as $comment)<article class="admin-comment"><p class="admin-help">{{ $comment->author_name }} · {{ $comment->updated_at?->format('Y-m-d H:i:s') }}</p><p class="admin-preserve-lines">{{ $comment->content }}</p><button type="button" class="admin-btn small" data-dialog-open="comment-{{ $comment->id }}">댓글 수정</button><dialog class="admin-modal" id="comment-{{ $comment->id }}"><div class="admin-modal-header"><h2>댓글 수정</h2><button class="admin-btn small" type="button" data-dialog-close aria-label="댓글 수정 창닫기">×</button></div><form method="post" action="{{ route('admin.content.comments.update',[$notice->id,$comment->id]) }}">
@csrf<label class="admin-field">댓글 내용<textarea name="content" maxlength="5000" required>{{ $comment->content }}</textarea></label><div class="admin-modal-footer"><button class="admin-btn" type="button" data-dialog-close>취소</button><button type="submit" class="admin-btn primary">수정 저장</button></div></form></dialog></article>
@empty<p class="admin-help">등록된 관리 댓글이 없습니다.</p>
@endforelse
</section>
@endunless</div>
@endsection
