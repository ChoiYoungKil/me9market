@extends('layouts.channel')

@php
    $dep1_id = "00";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">취소/환불안내 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>취소/환불안내 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1 btn">
                            <div class="count">총 <strong>{{ $policies->count() }}</strong> 건</div>
                            <div class="btn_bx">
                                <a href="{{ url()->current() }}" class="btn01 col5 pop_btn" data-pop="pop1_1">취소/환불안내 등록</a>
                            </div>
                        </div>
                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="">
                                    <col width="120px">
                                    <col width="130px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>설정구분</th>
                                        <th>상태</th>
                                        <th>취소/환불안내 명</th>
                                        <th>상품수</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($policies as $policy)
                                        <tr>
                                            <td>{{ $policy->type == 'default' ? '기본' : '사용자' }}</td>
                                            <td>{{ $policy->status == 'active' ? '사용' : '중지' }}</td>
                                            <td class="t_l">{{ $policy->name }}</td>
                                            <td>{{ $policy->product_count }}</td>
                                            <td>
                                                <a href="{{ url()->current() }}" class="btn02 col5 pop_btn view-policy" data-pop="pop2_1"
                                                    data-id="{{ $policy->id }}">보기</a>
                                                <a href="{{ url()->current() }}" class="btn02 col2 copy-policy" data-id="{{ $policy->id }}">복사</a>
                                                <a href="{{ url()->current() }}" class="btn02 col7 mt5 pop_btn edit-policy" data-pop="pop3_1"
                                                    data-id="{{ $policy->id }}">수정</a>
                                                <a href="{{ url()->current() }}" class="btn02 mt5 delete-policy" data-id="{{ $policy->id }}">삭제</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="no_data">등록된 데이터가 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($policies->count() == 0)
                            <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                        @endif

                        @if($policies->count() > 0)
                            <!-- 페이지네이션은 나중에 구현 -->
                            <!--
                                                        <div class="page_bx1">
                                                            <a href="{{ url()->current() }}" class="page_first">first</a>
                                                            <a href="{{ url()->current() }}" class="page_prev">prev</a>
                                                            <a href="{{ url()->current() }}" class="num on">1</a>
                                                            <a href="{{ url()->current() }}" class="num">2</a>
                                                            <a href="{{ url()->current() }}" class="num">3</a>
                                                            <a href="{{ url()->current() }}" class="num">4</a>
                                                            <a href="{{ url()->current() }}" class="num">5</a>
                                                            <a href="{{ url()->current() }}" class="page_next">next</a>
                                                            <a href="{{ url()->current() }}" class="page_last">last</a>
                                                        </div>
                                                        -->
                        @endif

                        <!-- 취소/환불안내 등록 팝업 -->
                        <div class="popup_bx" data-id="pop1_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">취소/환불안내 등록</div>
                                        </div>
                                        <div class="conbx">
                                            <div class="con_w">
                                                <form id="createPolicyForm">
                                                    @csrf
                                                    <div class="tb01">
                                                        <table>
                                                            <colgroup>
                                                                <col width="170px">
                                                                <col width="">
                                                                <col width="170px">
                                                                <col width="">
                                                            </colgroup>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>설정구분</span></th>
                                                                    <td colspan="3">
                                                                        <ul class="chk01">
                                                                            <li>
                                                                                <input type="radio" name="type"
                                                                                    value="default" id="create_type_default"
                                                                                    checked="">
                                                                                <label for="create_type_default">기본</label>
                                                                            </li>
                                                                            <li>
                                                                                <input type="radio" name="type"
                                                                                    value="custom" id="create_type_custom">
                                                                                <label for="create_type_custom">사용자</label>
                                                                            </li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>상태</span></th>
                                                                    <td colspan="3">
                                                                        <ul class="chk01">
                                                                            <li>
                                                                                <input type="radio" name="status"
                                                                                    value="active" id="create_status_active"
                                                                                    checked="">
                                                                                <label for="create_status_active">사용</label>
                                                                            </li>
                                                                            <li>
                                                                                <input type="radio" name="status"
                                                                                    value="inactive"
                                                                                    id="create_status_inactive">
                                                                                <label
                                                                                    for="create_status_inactive">중지</label>
                                                                            </li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>취소/환불안내 명칭<em>필수</em></span></th>
                                                                    <td colspan="3">
                                                                        <input type="text" name="name" id="create_name"
                                                                            value="" required="required">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>취소/환불안내 내용</span></th>
                                                                    <td colspan="3">
                                                                        <textarea name="content" id="create_content"
                                                                            required="required"></textarea>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="{{ url()->current() }}" id="submitCreatePolicy">취소/환불안내 등록</a>
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 보기 팝업 -->
                        <div class="popup_bx" data-id="pop2_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w800">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">취소/환불 안내 정보</div>
                                        </div>
                                        <div class="conbx">
                                            <div class="con_w">
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
                                                                <th class="w160"><span>설정구분</span></th>
                                                                <td colspan="3" id="view_type"></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>상태</span></th>
                                                                <td colspan="3" id="view_status"></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>취소/환불안내 명칭</span></th>
                                                                <td colspan="3" id="view_name"></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>취소/환불안내 내용</span></th>
                                                                <td colspan="3" id="view_content"
                                                                    style="white-space: pre-wrap;"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 수정 팝업 -->
                        <div class="popup_bx" data-id="pop3_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">취소/환불안내 수정</div>
                                        </div>
                                        <div class="conbx">
                                            <div class="con_w">
                                                <form id="updatePolicyForm">
                                                    @csrf
                                                    <input type="hidden" name="policy_id" id="update_policy_id">
                                                    <div class="tb01">
                                                        <table>
                                                            <colgroup>
                                                                <col width="170px">
                                                                <col width="">
                                                                <col width="170px">
                                                                <col width="">
                                                            </colgroup>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>설정구분</span></th>
                                                                    <td colspan="3">
                                                                        <ul class="chk01">
                                                                            <li>
                                                                                <input type="radio" name="type"
                                                                                    value="default"
                                                                                    id="update_type_default">
                                                                                <label for="update_type_default">기본</label>
                                                                            </li>
                                                                            <li>
                                                                                <input type="radio" name="type"
                                                                                    value="custom" id="update_type_custom">
                                                                                <label for="update_type_custom">사용자</label>
                                                                            </li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>상태</span></th>
                                                                    <td colspan="3">
                                                                        <ul class="chk01">
                                                                            <li>
                                                                                <input type="radio" name="status"
                                                                                    value="active"
                                                                                    id="update_status_active">
                                                                                <label for="update_status_active">사용</label>
                                                                            </li>
                                                                            <li>
                                                                                <input type="radio" name="status"
                                                                                    value="inactive"
                                                                                    id="update_status_inactive">
                                                                                <label
                                                                                    for="update_status_inactive">중지</label>
                                                                            </li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>취소/환불안내 명칭<em>필수</em></span></th>
                                                                    <td colspan="3">
                                                                        <input type="text" name="name" id="update_name"
                                                                            value="" required="required">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>취소/환불안내 내용</span></th>
                                                                    <td colspan="3">
                                                                        <textarea name="content" id="update_content"
                                                                            required="required"></textarea>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="{{ url()->current() }}" id="submitUpdatePolicy">취소/환불안내 수정</a>
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('scripts')
        <script type="text/javascript">
            /* 팝업 */
            $(".pop_btn").click(function () {
                var popId = $(this).attr("data-pop");
                if (popId == "pop1") {
                    var thisImg = $(this).children("img").clone();
                    $(".popup_bx[data-id='" + popId + "']").find(".img_bx").html(thisImg);
                    $(".popup_bx[data-id='" + popId + "']").find(".img_bx").children("img").css({ "max-width": "100%", "width": "auto", "margin": "0 auto", "display": "block" });
                }
                $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

                return false;
            });
            $(".popup_bx .close_btn").click(function () {
                $(this).parents(".popup_bx").stop().fadeOut(300);

                return false;
            });

            /* 달력 */
            $(".datepicker").datepicker({
                dateFormat: 'yy-mm-dd', //달력 날짜 형태
                showOtherMonths: true, //빈 공간에 현재월의 앞뒤월의 날짜를 표시
                showMonthAfterYear: true, // 월- 년 순서가아닌 년도 - 월 순서
                changeYear: true, //option값 년 선택 가능
                changeMonth: true, //option값  월 선택 가능                      
                yearSuffix: "년", //달력의 년도 부분 뒤 텍스트
                monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 텍스트
                monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 Tooltip
                dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'], //달력의 요일 텍스트
                dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'], //달력의 요일 Tooltip
                minDate: "-5y", //최소 선택일자(-1D:하루전, -1M:한달전, -1Y:일년전)
                maxDate: "+5y", //최대 선택일자(+1D:하루후, -1M:한달후, -1Y:일년후)  
            });

            // CSRF 토큰 설정
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 정책 등록
            $('#submitCreatePolicy').click(function (e) {
                e.preventDefault();

                var formData = {
                    type: $('input[name="type"]:checked', '#createPolicyForm').val(),
                    status: $('input[name="status"]:checked', '#createPolicyForm').val(),
                    name: $('#create_name').val(),
                    content: $('#create_content').val()
                };

                if (!formData.name) {
                    alert('취소/환불안내 명칭을 입력해 주세요.');
                    return;
                }

                $.ajax({
                    url: '{{ route("channel.refund.store") }}',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('오류가 발생했습니다.');
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMsg = '';
                            $.each(errors, function (key, value) {
                                errorMsg += value[0] + '\n';
                            });
                            alert(errorMsg);
                        } else {
                            alert('오류가 발생했습니다.');
                        }
                    }
                });
            });

            // 정책 보기
            $('.view-policy').click(function (e) {
                e.preventDefault();
                var policyId = $(this).data('id');

                $.ajax({
                    url: '/channel/settings/refund/' + policyId,
                    type: 'GET',
                    success: function (response) {
                        if (response.status === 'success') {
                            var policy = response.policy;
                            $('#view_type').text(policy.type === 'default' ? '기본' : '사용자');
                            $('#view_status').text(policy.status === 'active' ? '사용' : '중지');
                            $('#view_name').text(policy.name);
                            $('#view_content').text(policy.content || '');
                        }
                    },
                    error: function () {
                        alert('정책 정보를 불러오는데 실패했습니다.');
                    }
                });
            });

            // 정책 수정 - 데이터 로드
            $('.edit-policy').click(function (e) {
                e.preventDefault();
                var policyId = $(this).data('id');

                $.ajax({
                    url: '/channel/settings/refund/' + policyId,
                    type: 'GET',
                    success: function (response) {
                        if (response.status === 'success') {
                            var policy = response.policy;
                            $('#update_policy_id').val(policy.id);

                            // 타입 설정
                            if (policy.type === 'default') {
                                $('#update_type_default').prop('checked', true);
                            } else {
                                $('#update_type_custom').prop('checked', true);
                            }

                            // 상태 설정
                            if (policy.status === 'active') {
                                $('#update_status_active').prop('checked', true);
                            } else {
                                $('#update_status_inactive').prop('checked', true);
                            }

                            $('#update_name').val(policy.name);
                            $('#update_content').val(policy.content || '');
                        }
                    },
                    error: function () {
                        alert('정책 정보를 불러오는데 실패했습니다.');
                    }
                });
            });

            // 정책 수정 제출
            $('#submitUpdatePolicy').click(function (e) {
                e.preventDefault();

                var policyId = $('#update_policy_id').val();
                var formData = {
                    type: $('input[name="type"]:checked', '#updatePolicyForm').val(),
                    status: $('input[name="status"]:checked', '#updatePolicyForm').val(),
                    name: $('#update_name').val(),
                    content: $('#update_content').val()
                };

                if (!formData.name) {
                    alert('취소/환불안내 명칭을 입력해 주세요.');
                    return;
                }

                $.ajax({
                    url: '/channel/settings/refund/' + policyId + '/update',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('오류가 발생했습니다.');
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMsg = '';
                            $.each(errors, function (key, value) {
                                errorMsg += value[0] + '\n';
                            });
                            alert(errorMsg);
                        } else {
                            alert('오류가 발생했습니다.');
                        }
                    }
                });
            });

            // 정책 삭제
            $('.delete-policy').click(function (e) {
                e.preventDefault();

                if (!confirm('정말 삭제하시겠습니까?')) {
                    return;
                }

                var policyId = $(this).data('id');

                $.ajax({
                    url: '/channel/settings/refund/' + policyId + '/delete',
                    type: 'POST',
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('오류가 발생했습니다.');
                        }
                    },
                    error: function () {
                        alert('삭제에 실패했습니다.');
                    }
                });
            });

            // 정책 복사
            $('.copy-policy').click(function (e) {
                e.preventDefault();

                if (!confirm('이 정책을 복사하시겠습니까?')) {
                    return;
                }

                var policyId = $(this).data('id');

                $.ajax({
                    url: '/channel/settings/refund/' + policyId + '/copy',
                    type: 'POST',
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('오류가 발생했습니다.');
                        }
                    },
                    error: function () {
                        alert('복사에 실패했습니다.');
                    }
                });
            });
        </script>
    @endpush