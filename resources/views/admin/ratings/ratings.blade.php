@extends('admin.layout.layout')
@section('content')
<div id="contents" class="admin-workspace admin-settings-list admin-reviews">
    <div class="admin-toolbar"><h1>리뷰 관리</h1><p class="admin-help">상품 후기와 노출 상태를 확인합니다.</p></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-setting-panel">
        <table id="ratings" class="admin-table">
            <colgroup><col style="width:64px"><col style="width:240px"><col style="width:150px"><col><col style="width:80px"><col style="width:100px"><col style="width:164px"></colgroup>
            <thead><tr><th>번호</th><th>상품명</th><th>작성자 이름</th><th>리뷰 내용</th><th>평점</th><th>노출 상태</th><th>관리</th></tr></thead>
            <tbody>
            @foreach($ratings as $rating)
                <tr>
                    <td>{{ $rating['id'] }}</td>
                    <td class="review-product">
                    @if($rating['product'])
                        <a target="_blank" rel="noopener" href="{{ url('product/'.$rating['product']['id']) }}" title="{{ $rating['product']['product_name'] }}">{{ $rating['product']['product_name'] }}</a>
                    @else
                        <span class="admin-help">삭제된 상품</span>
                    @endif
                    </td>
                    <td class="review-author" title="{{ $rating['user']['name'] ?? '비회원' }}">{{ $rating['user']['name'] ?? '비회원' }}</td>
                    <td class="review-summary" title="{{ $rating['review'] }}">{{ \Illuminate\Support\Str::limit($rating['review'],80) ?: '내용 없음' }}</td>
                    <td><strong class="review-score">{{ number_format($rating['rating'],1) }}</strong></td>
                    <td><a class="updateRatingStatus" id="rating-{{ $rating['id'] }}" rating_id="{{ $rating['id'] }}" href="javascript:void(0)" title="노출 상태 변경"><i class="admin-setting-status {{ $rating['status']?'enabled':'disabled' }}" status="{{ $rating['status']?'Active':'Inactive' }}">{{ $rating['status']?'노출':'비노출' }}</i></a></td>
                    <td><div class="admin-row-actions"><button type="button" class="admin-btn primary" data-dialog-open="review-{{ $rating['id'] }}">보기</button><a href="javascript:void(0)" class="admin-btn danger confirmDelete" module="rating" moduleid="{{ $rating['id'] }}">삭제</a></div></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
    @foreach($ratings as $rating)
    <dialog class="admin-modal" id="review-{{ $rating['id'] }}" aria-labelledby="review-title-{{ $rating['id'] }}">
        <div class="admin-modal-header"><h2 id="review-title-{{ $rating['id'] }}">리뷰 상세 · {{ $rating['id'] }}번</h2><button type="button" class="admin-btn" data-dialog-close aria-label="리뷰 상세 닫기">×</button></div>
        <dl class="admin-detail-grid"><dt>상품명</dt><dd>{{ $rating['product']['product_name'] ?? '삭제된 상품' }}</dd><dt>작성자</dt><dd>{{ $rating['user']['name'] ?? '비회원' }}</dd><dt>평점</dt><dd>{{ number_format($rating['rating'],1) }} / 5점</dd><dt>작성일시</dt><dd>{{ \Illuminate\Support\Carbon::parse($rating['created_at'])->format('Y-m-d H:i') }}</dd></dl>
        <section class="review-full-content"><h3>리뷰 내용</h3><p>{{ $rating['review'] ?: '작성된 리뷰 내용이 없습니다.' }}</p></section>
        <div class="admin-modal-footer"><button type="button" class="admin-btn primary" data-dialog-close>닫기</button></div>
    </dialog>
    @endforeach
</div>
@endsection
