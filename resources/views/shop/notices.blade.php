@extends('layouts.shop')
@section('content')
<div id="contents"><div class="notice"><div class="top_v"><h1 class="title">공지사항</h1></div><div class="shop-inner"><div id="board">
    <div class="list_top"><div class="count">총 <strong>{{ $notices->total() }}</strong> 건</div>
        <form method="GET" class="search_bx type2" action="{{ route('shop.notices') }}"><input type="search" name="search" value="{{ request('search') }}" maxlength="100" aria-label="공지 검색" placeholder="검색어를 입력해 주세요"><button class="s_btn" type="submit" aria-label="검색" title="검색"></button></form>
    </div>
    <div class="list01"><ul>
        @forelse($notices as $notice)<li><a href="{{ route('shop.notices.show', $notice->id) }}"><div class="num">{{ $notices->total() - $notices->firstItem() - $loop->index + 1 }}</div><div class="subject">{{ $notice->title }}</div><div class="date">{{ $notice->created_at?->format('Y-m-d') }}</div></a></li>@empty<li><div class="shop-empty">등록된 공지사항이 없습니다.</div></li>@endforelse
    </ul></div>
    @include('shop.partials.pagination', ['paginator'=>$notices])
</div></div></div></div>
@endsection
