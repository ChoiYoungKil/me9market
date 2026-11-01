@extends('layouts.admin')
@section('content')
<div id="contents" class="admin-workspace admin-account-list">
    <h1>관리자/판매자 관리</h1>
    @include('admin.shared.messages')
                        <form action="{{ url('admin/admins') }}" method="get" class="admin-account-search">
                            <div class="admin-account-search-row">
                                <label class="admin-account-type">구분
                                    <select name="type">
                                        <option value="">전체 유형</option>
                                        <option value="admin" @selected(request('type')==='admin')>관리자</option>
                                        <option value="subadmin" @selected(request('type')==='subadmin')>서브 관리자</option>
                                        <option value="vendor" @selected(request('type')==='vendor')>판매자</option>
                                    </select>
                                </label>
                                <label class="admin-account-keyword">검색어
                                    <input type="search" name="search_value" value="{{ request('search_value') }}" placeholder="이름, 이메일, 연락처" maxlength="100">
                                </label>
                                <div class="admin-account-search-actions">
                                    <button type="button" id="arrow1" class="admin-btn" aria-expanded="{{ request()->filled('status')?'true':'false' }}" aria-controls="admin-account-status"><span>상세 검색</span></button>
                                    <button type="submit" class="admin-btn primary">검색</button>
                                    <a href="{{ url('admin/admins') }}" class="admin-btn admin-button-reset">초기화</a>
                                </div>
                            </div>
                            <div id="admin-account-status" class="arrowbx admin-account-status" data-arrowbx="arrow1" @if(!request()->filled('status')) style="display:none" @endif>
                                <span>상태</span>
                                <label><input type="checkbox" name="status[]" value="1" @checked(is_array(request('status')) && in_array(1,request('status')))>활성</label>
                                <label><input type="checkbox" name="status[]" value="0" @checked(is_array(request('status')) && in_array(0,request('status')))>비활성</label>
                            </div>
                        </form>

    <div class="admin-toolbar"><p>총 <strong>{{ number_format($admins->total()) }}</strong>명</p><a class="admin-btn primary" href="{{ url('admin/add-edit-admin') }}">관리자/판매자 추가</a></div>
    <div class="admin-table-wrap" tabindex="0" role="region" aria-label="관리자 및 판매자 목록">
        <table class="admin-table admin-account-table">
            <colgroup><col style="width:64px"><col style="width:190px"><col style="width:110px"><col><col style="width:150px"><col style="width:90px"><col style="width:240px"></colgroup>
            <thead><tr><th>번호</th><th>이름</th><th>구분</th><th>이메일</th><th>연락처</th><th>상태</th><th>관리</th></tr></thead>
            <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td>{{ $admin->id }}</td><td class="account-name">{{ $admin->name }}</td>
                    <td>{{ ['superadmin'=>'최고관리자','admin'=>'관리자','subadmin'=>'서브 관리자','vendor'=>'판매자'][$admin->type] ?? '기타' }}</td>
                    <td class="account-email" title="{{ $admin->email }}">{{ $admin->email }}</td><td class="nowrap">{{ $admin->mobile }}</td>
                    <td><a class="updateAdminStatus" id="admin-{{ $admin->id }}" admin_id="{{ $admin->id }}" href="javascript:void(0)" title="계정 상태 변경"><span class="admin-status {{ $admin->status?'enabled':'disabled' }}">{{ $admin->status?'활성':'비활성' }}</span></a></td>
                    <td><div class="admin-row-actions">
                    @if($admin->type==='vendor')
                        <a class="admin-btn" href="{{ url('admin/view-vendor-details/'.$admin->id) }}">상세</a>
                    @endif
                        <a class="admin-btn primary" href="{{ url('admin/add-edit-admin/'.$admin->id) }}">수정</a>
                        <a class="admin-btn danger confirmDelete" href="{{ url('admin/delete-admin/'.$admin->id) }}" module="admin" moduleid="{{ $admin->id }}">삭제</a>
                    </div></td>
                </tr>
            @empty
                @include('components.table-empty-row', ['columns' => 7])
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">{{ $admins->withQueryString()->onEachSide(2)->links('admin.shared.pagination') }}</div>
</div>
    <script>
        $(document).ready(function () {
            // Arrow Detail Search Toggle
            $("#arrow1").click(function () {
                var thisId = $(this).attr("id");
                $(this).attr("aria-expanded", $(this).attr("aria-expanded") !== "true" ? "true" : "false");
                $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
            });

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
                submitAdminDelete("/admin/delete-" + module + "/" + moduleid);
            });

            // Update Admin Status
            $(".updateAdminStatus").click(function () {
                var status = $(this).text().trim();
                var admin_id = $(this).attr("admin_id");

                $.ajax({
                    type: 'post',
                    url: '/admin/update-admin-status',
                    data: { status: status, admin_id: admin_id },
                    success: function (resp) {
                        if (resp['status'] == 0) {
                            $("#admin-" + admin_id).html("<span class='admin-status disabled'>비활성</span>");
                        } else if (resp['status'] == 1) {
                            $("#admin-" + admin_id).html("<span class='admin-status enabled'>활성</span>");
                        }
                    }, error: function () {
                        alert("오류가 발생했습니다.");
                    }
                });
            });
        });
    </script>
@endsection
