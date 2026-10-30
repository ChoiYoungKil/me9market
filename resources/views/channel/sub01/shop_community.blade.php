@extends('layouts.channel')
@section('page_type','sub')
@php $dep1_id='01'; @endphp
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}?v={{ filemtime(public_path('channel_assets/css/shop-configuration.css')) }}">@endpush
@section('content')
<div id="contents"><div class="channel-shop"><header><p>MY CHANNEL</p><h1>커뮤니티</h1><p>{{ $shop->channel_name }}</p></header>@include('channel.sub01.inc.community_tabs')
@if(session('success_message'))<p class="shop-feedback" role="status">{{ session('success_message') }}</p>@endif
<section class="shop-panel"><form method="GET"><input type="hidden" name="shop_id" value="{{ $shopId }}"><div class="community-filters"><label>검색구분<select name="search_type" aria-label="검색구분">@foreach(['both'=>'제목+내용','title'=>'제목','content'=>'내용'] as $key=>$label)<option value="{{ $key }}" @selected($searchType===$key)>{{ $label }}</option>@endforeach</select></label><label>검색어<input name="search_value" aria-label="검색어" maxlength="150" value="{{ $searchValue }}"></label><label>목록 개수<select name="per_page" aria-label="목록 개수">@foreach([20,40,60,80,100] as $count)<option @selected((int)$perPage===$count)>{{ $count }}</option>@endforeach</select></label><button type="submit">검색</button></div></form>
<div class="catalog-toolbar"><h2>총 {{ $notices->total() }}건</h2><div class="shop-actions"><a href="{{ route('channel.community.register',['shop_id'=>$shopId]) }}">글쓰기</a></div></div>
<div class="table-scroll"><table><thead><tr><th>번호</th><th>분류</th><th>제목</th><th>작성자</th><th>등록일</th><th>조회</th></tr></thead><tbody>@forelse($notices as $row)<tr><td>{{ $notices->total()-($notices->firstItem()-1)-$loop->index }}</td><td>{{ $row->type==='notice'?'공지':'일반' }}</td><td class="product-name"><a href="{{ route('channel.community.view',$row->id) }}">{{ $row->title }}</a>@if($row->attachment) · 첨부@endif</td><td>{{ $row->author }}</td><td>{{ $row->created_at->format('Y-m-d') }}</td><td>{{ $row->view_count }}</td></tr>@empty<tr><td colspan="6">등록된 게시글이 없습니다.</td></tr>@endforelse</tbody></table></div>{{ $notices->withQueryString()->links() }}</section></div></div>
@endsection
