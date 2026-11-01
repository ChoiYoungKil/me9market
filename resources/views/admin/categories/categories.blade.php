@extends('admin.layout.layout')

@section('content')
<div id="contents" class="admin-workspace admin-settings-list admin-categories">
    <div class="admin-toolbar"><h1>분류 관리</h1><div class="admin-actions"><a class="admin-btn primary" href="{{ url('admin/add-edit-category') }}">분류 추가</a></div></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-setting-panel">
        <table id="categories" class="admin-table">
            <colgroup><col style="width:64px"><col><col style="width:190px"><col style="width:150px"><col style="width:230px"><col style="width:90px"><col style="width:164px"></colgroup>
            <thead><tr><th>번호</th><th>분류명</th><th>상위 분류</th><th>섹션</th><th>분류 URL</th><th>상태</th><th>관리</th></tr></thead>
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category['id'] }}</td>
                    <td class="category-name">{{ $category['category_name'] }}</td>
                    <td class="category-parent">{{ $category['parent_category']['category_name'] ?? '최상위' }}</td>
                    <td>{{ $category['section']['name'] ?? '미지정' }}</td>
                    <td class="category-url">{{ $category['url'] }}</td>
                    <td><a class="updateCategoryStatus" id="category-{{ $category['id'] }}" category_id="{{ $category['id'] }}" href="javascript:void(0)" aria-label="{{ $category['category_name'] }} 상태 변경"><i class="admin-setting-status {{ $category['status'] ? 'enabled' : 'disabled' }}" status="{{ $category['status'] ? 'Active' : 'Inactive' }}">{{ $category['status'] ? '사용' : '중지' }}</i></a></td>
                    <td><div class="category-row-actions"><a class="admin-btn small primary" href="{{ url('admin/add-edit-category/'.$category['id']) }}">수정</a><button type="button" class="admin-btn small danger" data-admin-delete="{{ url('admin/delete-category/'.$category['id']) }}">삭제</button></div></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection
