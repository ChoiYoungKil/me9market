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

    /* 탭 메뉴 */
    .tab_bx {
        margin-bottom: 20px;
    }
    .tab_bx ul {
        display: flex;
        /* border-bottom: 1px solid #ddd; */
        margin-bottom: 10px;
    }
    .tab_bx li {
        margin-right: 10px;
    }
    .tab_bx li a {
        display: block;
        padding: 10px 20px;
        border: 1px solid #ddd;
        border-radius: 20px;
        color: #666;
        text-decoration: none;
        background-color: #fff;
    }
    .tab_bx li.on a {
        background-color: #3470f7;
        color: #fff;
        border-color: #3470f7;
    }
</style>
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">자주묻는질문 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>고객센터</li>
                        <li>자주묻는질문</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        
                        <!-- 탭 메뉴 -->
                        <div class="tab_bx">
                            <ul>
                                <li class="{{ request('category') == '' || request('category') == '전체' ? 'on' : '' }}">
                                    <a href="{{ url('admin/faqs') }}">전체</a>
                                </li>
                                <li class="{{ request('category') == '주문/배송' ? 'on' : '' }}">
                                    <a href="{{ url('admin/faqs') }}?category=주문/배송">주문/배송</a>
                                </li>
                                <li class="{{ request('category') == '결제' ? 'on' : '' }}">
                                    <a href="{{ url('admin/faqs') }}?category=결제">결제</a>
                                </li>
                                <li class="{{ request('category') == '회원' ? 'on' : '' }}">
                                    <a href="{{ url('admin/faqs') }}?category=회원">회원</a>
                                </li>
                                <li class="{{ request('category') == '상품' ? 'on' : '' }}">
                                    <a href="{{ url('admin/faqs') }}?category=상품">상품</a>
                                </li>
                                <li class="{{ request('category') == '기타' ? 'on' : '' }}">
                                    <a href="{{ url('admin/faqs') }}?category=기타">기타</a>
                                </li>
                            </ul>
                        </div>

                        <div class="list_top1 list-top-split">
                            <div class="left_bx list-top-actions">
                                <div class="count">총 <strong>{{ $faqs->total() }}</strong> 개</div>
                                
                                <form action="{{ url('admin/faqs') }}" method="GET" class="list-inline-search">
                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                    @if(request('category'))
                                        <input type="hidden" name="category" value="{{ request('category') }}">
                                    @endif
                                    <select name="search_type" class="w160">
                                        <option value="question" {{ request('search_type') == 'question' ? 'selected' : '' }}>질문</option>
                                        <option value="answer" {{ request('search_type') == 'answer' ? 'selected' : '' }}>답변</option>
                                        <option value="both" {{ request('search_type') == 'both' ? 'selected' : '' }}>질문+답변</option>
                                    </select>
                                    <input type="text" name="search_value" value="{{ request('search_value') }}" placeholder="검색어를 입력하세요" class="w300">
                                    <button type="submit" class="btn02 col5">검색</button>
                                </form>
                            </div>

                            <div class="right_bx list-top-actions">
                                <select id="perPageSelect" class="w160">
                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20개씩 보기</option>
                                    <option value="40" {{ $perPage == 40 ? 'selected' : '' }}>40개씩 보기</option>
                                    <option value="60" {{ $perPage == 60 ? 'selected' : '' }}>60개씩 보기</option>
                                    <option value="80" {{ $perPage == 80 ? 'selected' : '' }}>80개씩 보기</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100개씩 보기</option>
                                </select>
                                <div class="r_btn_w">
                                    <a href="{{ url('admin/add-edit-faq') }}" class="btn02">FAQ 추가</a>
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
                                    <col width="150px">
                                    <col width="">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>카테고리</th>
                                        <th>질문</th>
                                        <th>정렬순서</th>
                                        <th>조회수</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($faqs->count() > 0)
                                        @foreach ($faqs as $faq)
                                            <tr>
                                                <td>{{ $faq['id'] }}</td>
                                                <td>{{ $faq['category'] ?? '-' }}</td>
                                                <td style="text-align: left; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $faq['question'] }}">
                                                    {{ $faq['question'] }}
                                                </td>
                                                <td>{{ $faq['order'] }}</td>
                                                <td>{{ $faq['view_count'] }}</td>
                                                <td>
                                                    @if ($faq['status'] == 1)
                                                        <a class="updateFaqStatus" id="faq-{{ $faq['id'] }}"
                                                            faq_id="{{ $faq['id'] }}" href="javascript:void(0)">
                                                            <span style="color:green">활성</span>
                                                        </a>
                                                    @else
                                                        <a class="updateFaqStatus" id="faq-{{ $faq['id'] }}"
                                                            faq_id="{{ $faq['id'] }}" href="javascript:void(0)">
                                                            <span style="color:red">비활성</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-faq/' . $faq['id']) }}" class="btn02">수정</a>
                                                    <a href="JavaScript:void(0)" class="btn02 confirmDelete" module="faq"
                                                        moduleid="{{ $faq['id'] }}" style="color: red;">삭제</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="no_data">등록된 FAQ가 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- 페이지네이션 --}}
                        @if($faqs->hasPages())
                            <div class="page_bx" style="margin-top: 20px; text-align: center;">
                                {{-- 첫 페이지 --}}
                                @if($faqs->onFirstPage())
                                    <a href="#" class="page_first disabled" style="pointer-events: none; opacity: 0.5;">first</a>
                                @else
                                    <a href="{{ $faqs->appends(request()->query())->url(1) }}" class="page_first">first</a>
                                @endif

                                {{-- 이전 페이지 --}}
                                @if($faqs->onFirstPage())
                                    <a href="#" class="page_prev disabled" style="pointer-events: none; opacity: 0.5;">prev</a>
                                @else
                                    <a href="{{ $faqs->appends(request()->query())->previousPageUrl() }}" class="page_prev">prev</a>
                                @endif

                                {{-- 페이지 번호 --}}
                                @php
                                    $start = max($faqs->currentPage() - 2, 1);
                                    $end = min($start + 4, $faqs->lastPage());
                                    $start = max($end - 4, 1);
                                @endphp
                                @for($i = $start; $i <= $end; $i++)
                                    @if($i == $faqs->currentPage())
                                        <a href="#" class="num on">{{ $i }}</a>
                                    @else
                                        <a href="{{ $faqs->appends(request()->query())->url($i) }}" class="num">{{ $i }}</a>
                                    @endif
                                @endfor

                                {{-- 다음 페이지 --}}
                                @if($faqs->hasMorePages())
                                    <a href="{{ $faqs->appends(request()->query())->nextPageUrl() }}" class="page_next">next</a>
                                @else
                                    <a href="#" class="page_next disabled" style="pointer-events: none; opacity: 0.5;">next</a>
                                @endif

                                {{-- 마지막 페이지 --}}
                                @if($faqs->currentPage() == $faqs->lastPage())
                                    <a href="#" class="page_last disabled" style="pointer-events: none; opacity: 0.5;">last</a>
                                @else
                                    <a href="{{ $faqs->appends(request()->query())->url($faqs->lastPage()) }}" class="page_last">last</a>
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
            // Per Page 변경 - 검색 조건 유지
            $('#perPageSelect').change(function() {
                var perPage = $(this).val();
                var searchType = $('select[name="search_type"]').val();
                var searchValue = $('input[name="search_value"]').val();
                var category = $('input[name="category"]').val();
                
                var url = '{{ url("admin/faqs") }}?per_page=' + perPage;
                if(searchValue) {
                    url += '&search_type=' + searchType + '&search_value=' + searchValue;
                }
                if(category) {
                    url += '&category=' + category;
                }
                
                window.location.href = url;
            });

            // Update FAQ Status
            $(".updateFaqStatus").click(function () {
                var status = $(this).text().trim();
                var faq_id = $(this).attr("faq_id");

                $.ajax({
                    type: 'post',
                    url: '/admin/update-faq-status',
                    data: { status: status, faq_id: faq_id },
                    success: function (resp) {
                        if (resp['status'] == 0) {
                            $("#faq-" + faq_id).html("<span style='color:red'>비활성</span>");
                        } else if (resp['status'] == 1) {
                            $("#faq-" + faq_id).html("<span style='color:green'>활성</span>");
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
                window.location.href = "/admin/delete-faq/" + moduleid;
            });
        });
    </script>
@endsection
