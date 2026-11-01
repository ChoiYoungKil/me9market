@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('common/policy-rich-text.css') }}">
@endpush

@section('content')
<div id="contents" class="admin-workspace admin-legacy admin-account-editor admin-simple-setting-editor admin-contact-detail">
    <div class="page_info">
        <h1 class="ttl">제휴/문의 상세</h1>
        <ul class="dep"><li>홈</li><li>고객센터</li><li>제휴/문의</li><li>상세</li></ul>
    </div>
    @include('admin.shared.messages')
    <form id="contact-detail-form" action="{{ url('admin/update-contact/'.$contact->id) }}" method="post">
        @csrf
        <div id="board"><div class="write02"><div class="f_bx">
            <section class="f_w">
                <h2 class="f_ttl">문의 정보</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                        <tr><th scope="row">회사명</th><td>{{ $contact->company ?: '—' }}</td><th scope="row">담당자명</th><td>{{ $contact->name ?: '—' }}</td></tr>
                        <tr><th scope="row">연락처</th><td>{{ $contact->phone ?: '—' }}</td><th scope="row">이메일</th><td>{{ $contact->email ?: '—' }}</td></tr>
                        <tr><th scope="row">등록일</th><td>{{ $contact->created_at?->format('Y-m-d H:i') ?: '—' }}</td><th scope="row">답변 일시</th><td>{{ $contact->replied_at?->format('Y-m-d H:i') ?: '미답변' }}</td></tr>
                    </tbody>
                </table></div>
            </section>
            <section class="f_w">
                <h2 class="f_ttl">문의 내용</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                        <tr><th scope="row">제목</th><td colspan="3" class="contact-subject">{{ $contact->subject }}</td></tr>
                        <tr><th scope="row">문의 내용</th><td colspan="3"><div class="policy-rich-text contact-message">{!! app(\App\Services\RefundPolicyContent::class)->render($contact->message) !!}</div></td></tr>
                    </tbody>
                </table></div>
            </section>
            <section class="f_w">
                <h2 class="f_ttl">관리자 답변 및 처리 상태</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                        <tr><th><label for="contact-status">처리 상태</label></th><td colspan="3">
                            <select name="status" id="contact-status" required>
                                @foreach(['pending'=>'대기중', 'processing'=>'처리중', 'completed'=>'완료'] as $value=>$label)
                                    <option value="{{ $value }}" @selected(old('status', $contact->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td></tr>
                        <tr><th><label for="contact-reply">관리자 답변</label></th><td colspan="3">
                            <textarea name="admin_reply" id="contact-reply" rows="7" maxlength="10000" placeholder="문의에 대한 답변을 입력해 주세요." aria-describedby="contact-reply-help">{{ old('admin_reply', $contact->admin_reply) }}</textarea>
                            <p class="admin-help" id="contact-reply-help">최대 10,000자까지 입력할 수 있습니다.</p>
                        </td></tr>
                    </tbody>
                </table></div>
            </section>
            <div class="admin-modal-footer account-editor-footer">
                <a href="{{ url('admin/contacts') }}" class="admin-btn">목록</a>
                <button type="reset" class="admin-btn">입력 초기화</button>
                <button type="submit" class="admin-btn primary">저장</button>
            </div>
        </div></div></div>
    </form>
</div>
@endsection
