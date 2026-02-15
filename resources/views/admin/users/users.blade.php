@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">회원 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>회원 관리</li>
                        <li>회원 리스트</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <form action="{{ url('admin/users') }}" method="get">
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
                                                        <select name="search_type">
                                                            <option value="">전체</option>
                                                            <option value="id" @if(request('search_type') == 'id') selected
                                                            @endif>회원번호</option>
                                                            <option value="name" @if(request('search_type') == 'name')
                                                            selected @endif>이름</option>
                                                            <option value="email" @if(request('search_type') == 'email')
                                                            selected @endif>이메일</option>
                                                            <option value="mobile" @if(request('search_type') == 'mobile')
                                                            selected @endif>연락처</option>
                                                        </select>
                                                        <input type="text" name="search_value"
                                                            value="{{ request('search_value') }}" placeholder="검색어를 입력하세요">
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
                                            <th class="w160"><span>가입일</span></th>
                                            <td>
                                                <div class="term_w">
                                                    <input type="date" name="start_date"
                                                        value="{{ request('start_date') }}">
                                                    <span>~</span>
                                                    <input type="date" name="end_date" value="{{ request('end_date') }}">
                                                </div>
                                            </td>
                                            <th class="w160"><span>상태</span></th>
                                            <td>
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

                            <div class="btm_btn right mt10" style="display: flex; justify-content: flex-end; gap: 5px;">
                                <button type="submit" class="btn01">검색</button>
                                <a href="{{ url('admin/users') }}" class="btn01 col3">초기화</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box box1">
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $users->total() }}</strong> 명</div>
                            <div class="r_btn_w ml20">
                                <a href="{{ url('admin/add-edit-user') }}" class="btn02">회원 추가</a>
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
                                    <col width="120px">
                                    <col width="100px">
                                    <col width="200px">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="120px">
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
                                        <th>가입일</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($users->count() > 0)
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $user['id'] }}</td>
                                                <td>{{ $user['name'] }}</td>
                                                <td>
                                                    @if($user['type'] == 'vendor')
                                                        판매자
                                                    @elseif($user['type'] == 'company')
                                                        회원사
                                                    @else
                                                        일반회원
                                                    @endif
                                                </td>
                                                <td>{{ $user['email'] }}</td>
                                                <td>{{ $user['mobile'] }}</td>
                                                <td>
                                                    @if ($user['status'] == 1)
                                                        <a class="updateUserStatus" id="user-{{ $user['id'] }}"
                                                            user_id="{{ $user['id'] }}" href="javascript:void(0)">
                                                            <span style="color:green">활성</span>
                                                        </a>
                                                    @else
                                                        <a class="updateUserStatus" id="user-{{ $user['id'] }}"
                                                            user_id="{{ $user['id'] }}" href="javascript:void(0)">
                                                            <span style="color:red">비활성</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>{{ date('Y-m-d', strtotime($user['created_at'])) }}</td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-user/' . $user['id']) }}" class="btn02">수정</a>
                                                    <a href="{{ url('admin/delete-user/' . $user['id']) }}"
                                                        class="btn02 confirmDelete" module="user" moduleid="{{ $user['id'] }}"
                                                        style="color: red;">삭제</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="no_data">검색된 회원이 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="page_bx1">
                            {{ $users->links() }}
                        </div>
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

            // Datepicker (Removed as per instruction to use type="date" directly)
            // if($(".datepicker").length > 0) {
            //      $(".datepicker").datepicker({
            //         dateFormat: 'yy-mm-dd',
            //         showOtherMonths: true,
            //         showMonthAfterYear: true,
            //         changeYear: true,
            //         changeMonth: true,
            //         yearSuffix: "년",
            //         monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            //         monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            //         dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
            //         dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
            //         minDate: "-5y",
            //         maxDate: "+5y",
            //     });
            // }

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
            });

            // Update User Status
            $(".updateUserStatus").click(function () {
                var status = $(this).text().trim();
                var user_id = $(this).attr("user_id");

                $.ajax({
                    type: 'post',
                    url: '/admin/update-user-status',
                    data: { status: status, user_id: user_id },
                    success: function (resp) {
                        if (resp['status'] == 0) {
                            $("#user-" + user_id).html("<span style='color:red'>비활성</span>");
                        } else if (resp['status'] == 1) {
                            $("#user-" + user_id).html("<span style='color:green'>활성</span>");
                        }
                    }, error: function () {
                        alert("오류가 발생했습니다.");
                    }
                });
            });
        });
    </script>
@endsection