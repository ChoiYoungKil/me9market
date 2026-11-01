@extends('admin.layout.layout')
@section('content')
<div id="contents" class="admin-workspace admin-settings-list">
    <div class="admin-toolbar"><h1>섹션 관리</h1><div class="admin-actions"><a class="admin-btn primary" href="{{ url('admin/add-edit-section') }}">섹션 등록</a></div></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-setting-panel">
<table id="sections" class="admin-table">
<colgroup><col style="width:64px"><col><col style="width:100px"><col style="width:164px"></colgroup>
                                    <thead>
                                        <tr>
                                            <th>번호</th>
                                            <th>이름</th>
                                            <th>상태</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sections as $section)
                                            <tr>
                                                <td>{{ $section['id'] }}</td>
                                                <td>{{ $section['name'] }}</td>
                                                <td>
                                                    @if ($section['status'] == 1)
                                                        <a class="updateSectionStatus" id="section-{{ $section['id'] }}"
                                                            section_id="{{ $section['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status enabled" status="Active">사용</i>
                                                        </a>
                                                    @else
                                                        <a class="updateSectionStatus" id="section-{{ $section['id'] }}"
                                                            section_id="{{ $section['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status disabled" status="Inactive">중지</i>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-section/' . $section['id']) }}" class="admin-btn primary">수정</a>

                                                    <a href="JavaScript:void(0)" class="admin-btn danger confirmDelete" module="section"
                                                        moduleid="{{ $section['id'] }}">
                                                        삭제
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
    </section>
</div>
@endsection
