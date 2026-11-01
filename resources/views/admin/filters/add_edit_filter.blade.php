@extends('layouts.admin')

@section('content')
@php($selectedCategories = array_map('strval', (array) old('cat_ids', array_filter(explode(',', $filter->cat_ids ?? '')))))
<div id="contents" class="admin-workspace admin-legacy admin-account-editor admin-filter-editor">
    <div class="page_info"><h1 class="ttl">{{ $title }}</h1><ul class="dep"><li>홈</li><li>상품 관리</li><li>필터 관리</li><li>{{ $title }}</li></ul></div>
    @include('admin.shared.messages')
    <form method="post" action="{{ url('admin/add-edit-filter'.($filter->id ? '/'.$filter->id : '')) }}">
        @csrf
        <div id="board"><div class="write02"><div class="f_bx">
            <section class="f_w">
                <h2 class="f_ttl">적용 분류</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody><tr>
                        <th><label for="cat_ids">분류 선택</label></th>
                        <td colspan="3">
                            <select name="cat_ids[]" id="cat_ids" multiple size="10" required aria-describedby="filter-category-help">
                                @foreach($categories as $section)
                                    @if(count($section['categories']))
                                        <optgroup label="{{ $section['name'] }}">
                                            @foreach($section['categories'] as $category)
                                                @include('admin.filters.partials.category_options', ['category' => $category, 'depth' => 0])
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            <p class="admin-help" id="filter-category-help">필터를 적용할 분류를 선택해 주세요. 여러 분류는 Command(Mac) 또는 Ctrl(Windows) 키를 누른 채 선택할 수 있습니다.</p>
                        </td>
                    </tr></tbody>
                </table></div>
            </section>
            <section class="f_w filter-information">
                <h2 class="f_ttl">필터 정보</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody>
                        <tr><th><label for="filter_name">필터명</label></th><td colspan="3"><input class="w300" type="text" name="filter_name" id="filter_name" value="{{ old('filter_name', $filter->filter_name) }}" placeholder="예: 소재, 화면 크기" required></td></tr>
                        <tr><th><label for="filter_column">연결 항목 코드</label></th><td colspan="3">
                            <input class="w300" type="text" name="filter_column" id="filter_column" value="{{ old('filter_column', $filter->filter_column) }}" pattern="[a-z_]+" maxlength="64" placeholder="예: material, screen_size" required aria-describedby="filter-column-help">
                            <p class="admin-help" id="filter-column-help">영문 소문자와 밑줄만 입력해 주세요.</p>
                        </td></tr>
                    </tbody>
                </table></div>
            </section>
            <div class="admin-modal-footer account-editor-footer">
                <a class="admin-btn" href="{{ url('admin/filters') }}">취소</a>
                <button type="reset" class="admin-btn">입력 초기화</button>
                <button type="submit" class="admin-btn primary">저장</button>
            </div>
        </div></div></div>
    </form>
</div>
@endsection
