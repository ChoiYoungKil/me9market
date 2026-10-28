@extends('layouts.market-sub')
@section('page_title', '고객센터 · 자주 묻는 질문')
@section('content')
@include('partials.market-support-heading')
<section class="market-shell market-support-content" aria-label="자주 묻는 질문">
    <div class="market-list-tools market-faq-tools"><nav class="market-category-nav" aria-label="질문 분류"><a href="{{ route('cs.faq') }}" @if(!request('category') || request('category') === '전체') aria-current="page" @endif>전체</a>@foreach(['주문/배송', '결제', '회원', '상품', '포인트', '기타'] as $category)<a href="{{ route('cs.faq', ['category' => $category]) }}" @if(request('category') === $category) aria-current="page" @endif>{{ $category }}</a>@endforeach</nav><form method="GET" action="{{ route('cs.faq') }}" class="market-search">@if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif<input type="hidden" name="search_type" value="both"><label class="sr-only" for="faq-search">질문과 답변 검색</label><input id="faq-search" type="search" name="search_value" value="{{ request('search_value') }}" placeholder="궁금한 내용을 검색하세요"><button type="submit" aria-label="질문 검색">검색 <span aria-hidden="true">↗</span></button></form></div>
    <div class="market-faq-list">
        @forelse($faqs as $faq)
            <details class="market-faq-item" name="market-faq"><summary><span>{{ $faq->category }}</span><strong>{{ $faq->question }}</strong><span class="faq-plus" aria-hidden="true">+</span></summary><div class="market-faq-answer">{!! $faq->answer !!}</div></details>
        @empty
            <div class="market-empty"><span aria-hidden="true">?</span><h2>검색 결과가 없습니다.</h2><p>다른 검색어로 찾아보거나 제휴·문의를 이용해 주세요.</p></div>
        @endforelse
    </div>
    @include('partials.market-pagination', ['paginator' => $faqs])
</section>
@endsection
