@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">분류 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>상품 관리</li>
                        <li>분류 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ count($categories) }}</strong> 개</div>
                            <div class="r_btn_w ml20">
                                <a href="{{ url('admin/add-edit-category') }}" class="btn02">분류 추가</a>
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

                        <div class="tb01 ovS">
                            <table class="table table-bordered">
                                <colgroup>
                                    <col width="80px">
                                    <col width="300px">
                                    <col width="200px">
                                    <col width="200px">
                                    <col width="250px">
                                    <col width="100px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>분류명</th>
                                        <th>상위 분류</th>
                                        <th>섹션</th>
                                        <th>URL</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($categories) > 0)
                                        @foreach ($categories as $category)
                                            @php
                                                if (isset($category['parent_category']['category_name']) && !empty($category['parent_category']['category_name'])) {
                                                    $parent_category = $category['parent_category']['category_name'];
                                                } else {
                                                    $parent_category = '최상위';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $category['id'] }}</td>
                                                <td style="text-align: left;">{{ $category['category_name'] }}</td>
                                                <td>{{ $parent_category }}</td>
                                                <td>{{ $category['section']['name'] }}</td>
                                                <td style="text-align: left;">{{ $category['url'] }}</td>
                                                <td>
                                                    @if ($category['status'] == 1)
                                                        <a class="updateCategoryStatus" id="category-{{ $category['id'] }}"
                                                            category_id="{{ $category['id'] }}" href="javascript:void(0)">
                                                            <span style="color:green">활성</span>
                                                        </a>
                                                    @else
                                                        <a class="updateCategoryStatus" id="category-{{ $category['id'] }}"
                                                            category_id="{{ $category['id'] }}" href="javascript:void(0)">
                                                            <span style="color:red">비활성</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-category/' . $category['id']) }}"
                                                        class="btn02">수정</a>
                                                    <a href="JavaScript:void(0)" class="btn02 confirmDelete" module="category"
                                                        moduleid="{{ $category['id'] }}" style="color: red;">삭제</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="no_data">등록된 분류가 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Update Category Status
            $(".updateCategoryStatus").click(function () {
                var status = $(this).text().trim();
                var category_id = $(this).attr("category_id");

                $.ajax({
                    type: 'post',
                    url: '/admin/update-category-status',
                    data: { status: status, category_id: category_id },
                    success: function (resp) {
                        if (resp['status'] == 0) {
                            $("#category-" + category_id).html("<span style='color:red'>비활성</span>");
                        } else if (resp['status'] == 1) {
                            $("#category-" + category_id).html("<span style='color:green'>활성</span>");
                        }
                    }, error: function () {
                        alert("오류가 발생했습니다.");
                    }
                });
            });

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
                window.location.href = "/admin/delete-category/" + moduleid;
            });
        });
    </script>
@endsection