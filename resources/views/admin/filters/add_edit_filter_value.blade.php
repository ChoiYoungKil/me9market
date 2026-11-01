@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace admin-legacy admin-account-editor admin-simple-setting-editor admin-filter-value-editor">
    <div class="page_info"><h1 class="ttl">{{ $title }}</h1><ul class="dep"><li>홈</li><li>상품 관리</li><li>필터값 관리</li><li>{{ $title }}</li></ul></div>
    @include('admin.shared.messages')
    <form method="post" action="{{ url('admin/add-edit-filter-value'.($filter->id ? '/'.$filter->id : '')) }}">
        @csrf
        <section class="f_w">
            <h2 class="f_ttl">필터값 정보</h2>
            <div class="tb01"><table class="two"><colgroup><col><col><col><col></colgroup><tbody>
                <tr><th><label for="filter_id">필터 선택</label></th><td colspan="3">
                    <select name="filter_id" id="filter_id" class="w300" required>
                        <option value="">필터를 선택해 주세요</option>
                        @foreach($filters as $availableFilter)
                            <option value="{{ $availableFilter['id'] }}" @selected((string) old('filter_id', $filter->filter_id) === (string) $availableFilter['id'])>{{ $availableFilter['filter_name'] }}</option>
                        @endforeach
                    </select>
                </td></tr>
                <tr><th><label for="filter_value">필터값</label></th><td colspan="3"><input type="text" class="w300" id="filter_value" name="filter_value" value="{{ old('filter_value', $filter->filter_value) }}" placeholder="필터값을 입력해 주세요" required></td></tr>
            </tbody></table></div>
        </section>
        <div class="admin-modal-footer account-editor-footer"><a class="admin-btn" href="{{ url('admin/filters-values') }}">취소</a><button class="admin-btn" type="reset">입력 초기화</button><button class="admin-btn primary" type="submit">저장</button></div>
    </form>
</div>
@endsection
