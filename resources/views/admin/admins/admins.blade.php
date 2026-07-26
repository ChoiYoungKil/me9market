@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">관리자/판매자 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>관리자 관리</li>
                        <li>관리자/판매자</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <form action="{{ url('admin/admins') }}" method="get">
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>검색 구분</span></th>
                                            <td colspan="3">
                                                <div class="r_btn_w">
                                                    <div class="search_w01">
                                                        <select name="type">
                                                            <option value="">전체 유형</option>
                                                            <option value="admin" @if(request('type') == 'admin') selected
                                                            @endif>관리자</option>
                                                            <option value="subadmin" @if(request('type') == 'subadmin')
                                                            selected @endif>서브 관리자</option>
                                                            <option value="vendor" @if(request('type') == 'vendor') selected
                                                            @endif>판매자</option>
                                                        </select>
                                                        <input type="text" name="search_value"
                                                            value="{{ request('search_value') }}"
                                                            placeholder="이름, 이메일, 연락처">
                                                    </div>
                                                    <a id="arrow1" class="btn01 arrow"><span>상세 검색</span></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tb01 bN arrowbx" data-arrowbx="arrow1" style="display: none;">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>상태</span></th>
                                            <td colspan="3">
                                                <ul class="chk02">
                                                    <li>
                                                        <input type="checkbox" name="status[]" value="1" id="status_active"
                                                            @if(is_array(request('status')) && in_array(1, request('status'))) checked @endif>
                                                        <label for="status_active">활성</label>
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" name="status[]" value="0"
                                                            id="status_inactive" @if(is_array(request('status')) && in_array(0, request('status'))) checked @endif>
                                                        <label for="status_inactive">비활성</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn right mt10 search-actions">
                                <button type="submit" class="btn01">검색</button>
                                <a href="{{ url('admin/admins') }}" class="btn01 col3">초기화</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box box1">
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총
                                <strong>{{ is_array($admins) ? count($admins) : $admins->total() }}</strong> 명
                            </div>
                            <div class="r_btn_w ml20">
                                <a href="{{ url('admin/add-edit-admin') }}" class="btn02">관리자/판매자 추가</a>
                            </div>
                        </div>

                        @if (Session::has('success_message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 10px;">
                                <strong>성공:</strong> {{ Session::get('success_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if (Session::has('error_message'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 10px;">
                                <strong>오류:</strong> {{ Session::get('error_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="tb01 ovS">
                            <table class="table table-bordered">
                                <colgroup>
                                    <col width="80px">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="200px">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>이름</th>
                                        <th>구분</th>
                                        <th>이메일</th>
                                        <th>연락처</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if((is_array($admins) && count($admins) > 0) || (!is_array($admins) && $admins->count() > 0))
                                        @foreach ($admins as $admin)
                                            <tr>
                                                <td>{{ $admin['id'] }}</td>
                                                <td>{{ $admin['name'] }}</td>
                                                <td>
                                                    @if($admin['type'] == 'admin') 관리자
                                                    @elseif($admin['type'] == 'subadmin') 서브 관리자
                                                    @elseif($admin['type'] == 'vendor') 판매자
                                                    @else {{ ucfirst($admin['type']) }} @endif
                                                </td>
                                                <td>{{ $admin['email'] }}</td>
                                                <td>{{ $admin['mobile'] }}</td>
                                                <td>
                                                    @if ($admin['status'] == 1)
                                                        <a class="updateAdminStatus" id="admin-{{ $admin['id'] }}"
                                                            admin_id="{{ $admin['id'] }}" href="javascript:void(0)">
                                                            <span style="color:green">활성</span>
                                                        </a>
                                                    @else
                                                        <a class="updateAdminStatus" id="admin-{{ $admin['id'] }}"
                                                            admin_id="{{ $admin['id'] }}" href="javascript:void(0)">
                                                            <span style="color:red">비활성</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($admin['type'] == 'vendor')
                                                        <a href="{{ url('admin/view-vendor-details/' . $admin['id']) }}"
                                                            class="btn02">상세</a>
                                                    @endif
                                                    <a href="{{ url('admin/add-edit-admin/' . $admin['id']) }}" class="btn02">수정</a>
                                                    <a href="{{ url('admin/delete-admin/' . $admin['id']) }}"
                                                        class="btn02 confirmDelete" module="admin" moduleid="{{ $admin['id'] }}"
                                                        style="color: red;">삭제</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="no_data">검색된 관리자/판매자가 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if(!is_array($admins))
                            <div class="page_bx1">
                                {{ $admins->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Arrow Detail Search Toggle
            $(".btn01.arrow").click(function () {
                var thisId = $(this).attr("id");
                $(this).toggleClass("on");
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
                            $("#admin-" + admin_id).html("<span style='color:red'>비활성</span>");
                        } else if (resp['status'] == 1) {
                            $("#admin-" + admin_id).html("<span style='color:green'>활성</span>");
                        }
                    }, error: function () {
                        alert("오류가 발생했습니다.");
                    }
                });
            });
        });
    </script>
@endsection
