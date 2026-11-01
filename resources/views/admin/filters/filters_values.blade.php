@extends('admin.layout.layout')
@section('content')
<div id="contents" class="admin-workspace admin-settings-list">
    <div class="admin-toolbar"><h1>필터값 관리</h1><div class="admin-actions"><a class="admin-btn primary" href="{{ url('admin/add-edit-filter-value') }}">필터값 등록</a><a class="admin-btn" href="{{ url('admin/filters') }}">필터 목록</a></div></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-setting-panel">
<table id="filters" class="admin-table">
<colgroup><col style="width:64px"><col style="width:90px"><col style="width:180px"><col><col style="width:100px"></colgroup>
                                    <thead>
                                        <tr>
                                            <th>번호</th>
                                            <th>필터 번호</th>
                                            <th>필터명</th>
                                            <th>필터값</th>
                                            <th>상태</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($filters_values as $filter)
                                            <tr>
                                                <td>{{ $filter['id'] }}</td>
                                                <td>{{ $filter['filter_id'] }}</td>
                                                <td>
                                                    {{ \App\Models\ProductsFilter::getFilterName($filter['filter_id']) }}
                                                </td>
                                                <td>{{ $filter['filter_value'] }}</td>
                                                <td>
                                                    @if ($filter['status'] == 1)
                                                        <a class="updateFilterValueStatus" id="filter-{{ $filter['id'] }}" filter_id="{{ $filter['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status enabled" status="Active">사용</i>
                                                        </a>
                                                    @else
                                                        <a class="updateFilterValueStatus" id="filter-{{ $filter['id'] }}" filter_id="{{ $filter['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status disabled" status="Inactive">중지</i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
    </section>
</div>
@endsection
