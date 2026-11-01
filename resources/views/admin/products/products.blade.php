
@extends('layouts.admin')
@section('content')
@php($isGallery = request('view') === 'gallery')
<div id="contents" class="admin-workspace"><h1>{{ $isGallery ? '상품 갤러리' : '상품 관리' }}</h1>
@include('admin.shared.messages')
<section class="admin-panel"><form method="get" action="{{ route('admin.products') }}" class="admin-filter">
<label>검색구분<select name="search_type"><option value="all">전체</option><option value="product_name"
@selected(request('search_type')==='product_name')>상품명</option><option value="product_code"
@selected(request('search_type')==='product_code')>상품코드</option></select></label><label>검색어<input type="search" name="keyword" value="{{ request('keyword') }}" maxlength="100"></label>
<label>상태<select name="status"><option value="">전체</option><option value="1"
@selected(request('status')==='1')>활성</option><option value="0"
@selected(request('status')==='0')>비활성</option></select></label><label>시작일<input type="date" name="from" value="{{ request('from') }}"></label><label>종료일<input type="date" name="to" value="{{ request('to') }}"></label><label>목록 개수<select name="per_page">
@foreach([10,20,50,100] as $count)<option value="{{ $count }}"
@selected(request('per_page',20)==$count)>{{ $count }}개</option>
@endforeach</select></label><input type="hidden" name="view" value="{{ request('view','table') }}"><button class="admin-btn primary">검색</button><a class="admin-btn admin-button-reset" href="{{ route('admin.products', $isGallery ? ['view'=>'gallery'] : []) }}">초기화</a>
</form></section>
<div class="admin-toolbar"><p>총 <strong>{{ number_format($products->total()) }}</strong>개</p><div class="admin-actions"><a class="admin-btn" href="{{ request()->fullUrlWithQuery(['view'=>'table','page'=>null,'export'=>null]) }}">목록 보기</a><a class="admin-btn" href="{{ request()->fullUrlWithQuery(['view'=>'gallery','page'=>null,'export'=>null]) }}">갤러리 보기</a><a class="admin-btn admin-button-export" href="{{ request()->fullUrlWithQuery(['export'=>1,'page'=>null]) }}">엑셀 다운로드</a><a href="{{ url('admin/add-edit-product') }}" class="admin-btn primary">상품 추가</a></div></div>
<form method="post" action="{{ url('admin/lists/products/bulk') }}">
@csrf
@if(request('view')==='gallery')
<div class="admin-gallery">
@forelse($products as $product)<article><img src="{{ asset('front/images/product_images/small/'.($product->product_image ?: 'no-image.png')) }}" alt="{{ $product->product_name }}"><div class="copy"><label><input type="checkbox" name="ids[]" value="{{ $product->id }}" class="admin-row-check"> {{ $product->product_code }}</label><h2>{{ $product->product_name }}</h2><p>{{ number_format($product->product_price) }}원 · {{ $product->status?'활성':'비활성' }}</p><a class="admin-btn small" href="{{ url('admin/add-edit-product/'.$product->id) }}">수정</a><a class="admin-btn small" href="{{ url('admin/add-images/'.$product->id) }}">이미지</a> <a class="admin-btn small" href="{{ route('admin.products.policies',$product->id) }}">배송·환불 정책</a></div></article>
@empty<div class="admin-empty list-empty-state">등록된 상품이 없습니다.</div>
@endforelse</div>
@else
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th><input type="checkbox" data-select-all=".admin-row-check" aria-label="목록 전체 선택"></th><th>번호</th><th>상품명</th><th>상품코드</th><th>분류</th><th>가격</th><th>상태</th><th>관리</th></tr></thead><tbody>
@forelse($products as $product)<tr><td><input class="admin-row-check" type="checkbox" name="ids[]" value="{{ $product->id }}" aria-label="상품 {{ $product->id }} 선택"></td><td>{{ $product->id }}</td><td class="text-left"><div class="admin-product-info"><img class="admin-product-thumb" src="{{ asset('front/images/product_images/small/'.($product->product_image ?: 'no-image.png')) }}" alt=""><div><strong>{{ $product->product_name }}</strong><small>{{ $product->section?->name }} / {{ $product->category?->category_name }}</small></div></div></td><td>{{ $product->product_code }}</td><td>{{ $product->category?->category_name ?: '분류 없음' }}</td><td>{{ number_format($product->product_price) }}원</td><td><span class="admin-status {{ $product->status?'enabled':'disabled' }}">{{ $product->status?'활성':'비활성' }}</span></td><td class="nowrap"><a class="admin-btn small" href="{{ url('admin/add-edit-product/'.$product->id) }}">수정</a> <a class="admin-btn small" href="{{ route('admin.products.policies',$product->id) }}">배송·환불 정책</a> <a class="admin-btn small" href="{{ url('admin/add-edit-attributes/'.$product->id) }}">옵션</a> <a class="admin-btn small" href="{{ url('admin/add-images/'.$product->id) }}">이미지</a> <button type="button" class="admin-btn small danger" data-admin-delete="{{ url('admin/delete-product/'.$product->id) }}">삭제</button></td></tr>
@empty
@include('components.table-empty-row', ['columns' => 8, 'kind' => 'product'])
@endforelse</tbody></table></div>
@endif
<div class="admin-toolbar admin-bulk-bar"><div class="admin-actions">
@if(request('view')==='gallery')<label><input type="checkbox" data-select-all=".admin-row-check"> 목록 전체 선택</label>
@endif<label>선택 상품 상태 <select name="status" required><option value="">선택</option><option value="1">활성</option><option value="0">비활성</option></select></label><button class="admin-btn primary" type="submit">선택 상품 일괄 적용</button></div></div></form>
<div class="admin-pagination">{{ $products->onEachSide(2)->links('admin.shared.pagination') }}</div></div>
@endsection
