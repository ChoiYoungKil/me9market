@extends('layouts.admin')

@section('content')
    <style>
        /* 페이징 */
        .page_bx {
            font-size: 0;
            text-align: center;
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .page_bx a {
            display: inline-block;
            vertical-align: middle;
            min-width: 30px;
            line-height: 40px;
            height: 40px;
            text-decoration: none;
        }

        .page_bx .num {
            font-size: 18px;
            color: #111111;
            margin: 0 4px;
            position: relative;
            z-index: 1;
        }

        .page_bx .num.on {
            color: #fff;
        }

        .page_bx .num.on::before {
            content: '';
            width: 40px;
            height: 40px;
            background-color: #000000;
            border-radius: 50%;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            z-index: -1;
        }

        .page_bx .page_first {
            background: url(/front/images/icon/page_first.png) no-repeat center;
            margin-right: 6px;
            font-size: 0;
            width: 30px;
        }

        .page_bx .page_prev {
            background: url(/front/images/icon/page_prev.png) no-repeat center;
            margin-right: 12px;
            font-size: 0;
            width: 30px;
        }

        .page_bx .page_next {
            background: url(/front/images/icon/page_next.png) no-repeat center;
            margin-left: 12px;
            font-size: 0;
            width: 30px;
        }

        .page_bx .page_last {
            background: url(/front/images/icon/page_last.png) no-repeat center;
            margin-left: 6px;
            font-size: 0;
            width: 30px;
        }

        .page_bx .disabled {
            opacity: 0.3;
            cursor: default;
        }
    </style>
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">공지사항 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>고객센터</li>
                        <li>공지사항</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1"
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div class="left_bx" style="display: flex; gap: 10px; align-items: center;">
                                <div class="count">총 <strong>{{ $notices->total() }}</strong> 개</div>

                                <form action="{{ url('admin/notices') }}" method="GET"
                                    style="display: flex; gap: 5px; align-items: center; margin-left: 20px;">
                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                    <select name="search_type"
                                        style="padding: 5px; border: 1px solid #ddd; border-radius: 4px; height: 34px;">
                                        <option value="title" {{ request('search_type') == 'title' ? 'selected' : '' }}>제목
                                        </option>
                                        <option value="content" {{ request('search_type') == 'content' ? 'selected' : '' }}>내용
                                        </option>
                                        <option value="both" {{ request('search_type') == 'both' ? 'selected' : '' }}>제목+내용
                                        </option>
                                    </select>
                                    <input type="text" name="search_value" value="{{ request('search_value') }}"
                                        placeholder="검색어를 입력하세요"
                                        style="padding: 5px; border: 1px solid #ddd; border-radius: 4px; height: 34px;">
                                    <button type="submit" class="btn02"
                                        style="height: 34px; line-height: 34px; padding: 0 15px; border: none; cursor: pointer; background-color: #3470f7; color: #fff; border-radius: 4px;">검색</button>
                                </form>
                            </div>

                            <div class="right_bx" style="display: flex; gap: 10px; align-items: center;">
                                <select id="perPageSelect"
                                    style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; height: 34px;">
                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20개씩 보기</option>
                                    <option value="40" {{ $perPage == 40 ? 'selected' : '' }}>40개씩 보기</option>
                                    <option value="60" {{ $perPage == 60 ? 'selected' : '' }}>60개씩 보기</option>
                                    <option value="80" {{ $perPage == 80 ? 'selected' : '' }}>80개씩 보기</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100개씩 보기</option>
                                </select>
                                <div class="r_btn_w">
                                    <a href="{{ url('admin/add-edit-notice') }}" class="btn02">공지사항 추가</a>
                                </div>
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
                                    <col width="100px">
                                    <col width="">
                                    <col width="80px">
                                    <col width="100px">
                                    <col width="120px">
                                    <col width="100px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>중요</th>
                                        <th>제목</th>
                                        <th>첨부</th>
                                        <th>조회수</th>
                                        <th>등록일</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($notices->count() > 0)
                                        @foreach ($notices as $notice)
                                            <tr>
                                                <td>{{ $notice['id'] }}</td>
                                                <td>
                                                    @if($notice['is_important'])
                                                        <span style="color: red; font-weight: bold;">중요</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td style="text-align: left; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                    title="{{ $notice['title'] }}">
                                                    {{ $notice['title'] }}
                                                </td>
                                                <td>
                                                    @if(!empty($notice['attachment']))
                                                        <a href="{{ url('admin/attachments/notices/' . $notice['attachment']) }}"
                                                            target="_blank" title="{{ $notice['attachment'] }}">📎</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $notice['view_count'] }}</td>
                                                <td>{{ date('Y-m-d', strtotime($notice['created_at'])) }}</td>
                                                <td>
                                                    @if ($notice['status'] == 1)
                                                        <a class="updateNoticeStatus" id="notice-{{ $notice['id'] }}"
                                                            notice_id="{{ $notice['id'] }}" href="javascript:void(0)">
                                                            <span style="color:green">노출</span>
                                                        </a>
                                                    @else
                                                        <a class="updateNoticeStatus" id="notice-{{ $notice['id'] }}"
                                                            notice_id="{{ $notice['id'] }}" href="javascript:void(0)">
                                                            <span style="color:red">비노출</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-notice/' . $notice['id']) }}"
                                                        class="btn02">수정</a>
                                                    <a href="JavaScript:void(0)" class="btn02 confirmDelete" module="notice"
                                                        moduleid="{{ $notice['id'] }}" style="color: red;">삭제</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="no_data">등록된 공지사항이 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>


                        {{-- 페이지네이션 --}}
                        @if($notices->hasPages())
                            <div class="page_bx" style="margin-top: 20px; text-align: center;">
                                {{-- 첫 페이지 --}}
                                @if($notices->onFirstPage())
                                    <a href="#" class="page_first disabled" style="pointer-events: none; opacity: 0.5;">first</a>
                                @else
                                    <a href="{{ $notices->appends(request()->query())->url(1) }}" class="page_first">first</a>
                                @endif

                                {{-- 이전 페이지 --}}
                                @if($notices->onFirstPage())
                                    <a href="#" class="page_prev disabled" style="pointer-events: none; opacity: 0.5;">prev</a>
                                @else
                                    <a href="{{ $notices->appends(request()->query())->previousPageUrl() }}"
                                        class="page_prev">prev</a>
                                @endif

                                {{-- 페이지 번호 --}}
                                @php
                                    $start = max($notices->currentPage() - 2, 1);
                                    $end = min($start + 4, $notices->lastPage());
                                    $start = max($end - 4, 1);
                                @endphp
                                @for($i = $start; $i <= $end; $i++)
                                    @if($i == $notices->currentPage())
                                        <a href="#" class="num on">{{ $i }}</a>
                                    @else
                                        <a href="{{ $notices->appends(request()->query())->url($i) }}" class="num">{{ $i }}</a>
                                    @endif
                                @endfor

                                {{-- 다음 페이지 --}}
                                @if($notices->hasMorePages())
                                    <a href="{{ $notices->appends(request()->query())->nextPageUrl() }}" class="page_next">next</a>
                                @else
                                    <a href="#" class="page_next disabled" style="pointer-events: none; opacity: 0.5;">next</a>
                                @endif

                                {{-- 마지막 페이지 --}}
                                @if($notices->currentPage() == $notices->lastPage())
                                    <a href="#" class="page_last disabled" style="pointer-events: none; opacity: 0.5;">last</a>
                                @else
                                    <a href="{{ $notices->appends(request()->query())->url($notices->lastPage()) }}"
                                        class="page_last">last</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Per Page 변경
            $('#perPageSelect').change(function () {
                var perPage = $(this).val();
                var searchType = $('select[name="search_type"]').val();
                var searchValue = $('input[name="search_value"]').val();

                var url = '{{ url("admin/notices") }}?per_page=' + perPage;
                if (searchValue) {
                    url += '&search_type=' + searchType + '&search_value=' + searchValue;
                }

                window.location.href = url;
            });
            // Update Notice Status
            $(".updateNoticeStatus").click(function () {
                var status = $(this).text().trim();
                var notice_id = $(this).attr("notice_id");

                $.ajax({
                    type: 'post',
                    url: '/admin/update-notice-status',
                    data: { status: status, notice_id: notice_id },
                    success: function (resp) {
                        if (resp['status'] == 0) {
                            $("#notice-" + notice_id).html("<span style='color:red'>비노출</span>");
                        } else if (resp['status'] == 1) {
                            $("#notice-" + notice_id).html("<span style='color:green'>노출</span>");
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
                window.location.href = "/admin/delete-notice/" + moduleid;
            });
        });
    </script>
@endsection