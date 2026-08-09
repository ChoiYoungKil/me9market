@extends('layouts.channel')

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
            border: 1px solid #ddd;
            margin: 0 2px;
            font-size: 14px;
            color: #666;
        }

        .page_bx .num {
            font-size: 14px;
            color: #666;
        }

        .page_bx .num.on {
            background-color: #333;
            color: #fff;
            border-color: #333;
        }

        .page_bx .page_first {
            background: url(/front/images/icon/page_first.png) no-repeat center;
            font-size: 0;
        }

        .page_bx .page_prev {
            background: url(/front/images/icon/page_prev.png) no-repeat center;
            font-size: 0;
        }

        .page_bx .page_next {
            background: url(/front/images/icon/page_next.png) no-repeat center;
            font-size: 0;
        }

        .page_bx .page_last {
            background: url(/front/images/icon/page_last.png) no-repeat center;
            font-size: 0;
        }

        .page_bx .disabled {
            opacity: 0.5;
            cursor: default;
            background-color: #f9f9f9;
        }

        /* 검색창 스타일 */
        .list_top1 .left_bx form input,
        .list_top1 .left_bx form select {
            height: 34px;
            border: 1px solid #ddd;
            padding: 0 10px;
            vertical-align: middle;
        }

        .list_top1 .left_bx form button {
            height: 34px;
            line-height: 32px;
            padding: 0 15px;
            border: 1px solid #333;
            background: #333;
            color: #fff;
            vertical-align: middle;
            cursor: pointer;
        }
    </style>

    @php
        $page_type = "sub";
        $dep1_id = "01";
        $dep1_tit = "Shop채널관리";
    @endphp
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">Shop채널 상세페이지</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>Shop채널관리</li>
                        <li>Shop채널 상세페이지</li>
                    </ul>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.shop_info') }}"><span>Shop채널 정보</span></a></li>
                        <li><a href="{{ route('channel.product_own') }}"><span>판매상품</span></a></li>
                        <li><a href="{{ url()->current() }}" class="on"><span>커뮤니티</span></a></li>
                    </ul>
                </div>

                @if (Session::has('success_message'))
                    <div class="alert alert-success"
                        style="margin: 20px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                        {{ Session::get('success_message') }}
                    </div>
                @endif

                @if (Session::has('error_message'))
                    <div class="alert alert-danger"
                        style="margin: 20px; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                        {{ Session::get('error_message') }}
                    </div>
                @endif

                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1"
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: none; padding-bottom: 0;">
                            <div class="left_bx" style="display: flex; gap: 10px; align-items: center;">
                                <div class="count">총 <strong>{{ $notices->total() }}</strong> 개</div>

                                <form action="{{ route('channel.shop_community') }}" method="GET"
                                    class="list-inline-search">
                                    <input type="hidden" name="shop_id" value="{{ $shopId }}">
                                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                                    <select name="search_type" class="w160">
                                        <option value="both" {{ $searchType == 'both' ? 'selected' : '' }}>제목+내용</option>
                                        <option value="title" {{ $searchType == 'title' ? 'selected' : '' }}>제목</option>
                                        <option value="content" {{ $searchType == 'content' ? 'selected' : '' }}>내용</option>
                                    </select>

                                    <input type="text" name="search_value" placeholder="검색어를 입력하세요"
                                        value="{{ $searchValue }}" class="w300">
                                    <button type="submit" class="btn02 col5">검색</button>
                                </form>
                            </div>

                            <div class="right_bx" style="display: flex; gap: 10px; align-items: center;">
                                <select id="perPageSelect" class="w160">
                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20개씩 보기</option>
                                    <option value="40" {{ $perPage == 40 ? 'selected' : '' }}>40개씩 보기</option>
                                    <option value="60" {{ $perPage == 60 ? 'selected' : '' }}>60개씩 보기</option>
                                    <option value="80" {{ $perPage == 80 ? 'selected' : '' }}>80개씩 보기</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100개씩 보기</option>
                                </select>
                            </div>
                        </div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="80px">
                                    <col width="">
                                    <col width="120px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>제목</th>
                                        <th>작성자</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody class="textL">
                                    @forelse($notices as $index => $notice)
                                        <tr>
                                            <td class="t_c">
                                                @if($notice->type == 'notice')
                                                    <span class="badge_notice"
                                                        style="background: #ff0000; color: white; padding: 2px 8px; border-radius: 3px; font-weight: bold;">공지</span>
                                                @else
                                                    {{ $notices->total() - ($notices->currentPage() - 1) * $notices->perPage() - $index }}
                                                @endif
                                            </td>
                                            <td class="ovH">
                                                <a href="{{ route('channel.community.view', ['id' => $notice->id, 'shop_id' => $shopId]) }}"
                                                    class="subject fcol1">
                                                    @if($notice->type == 'notice')
                                                        <span
                                                            style="background: #333; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-right: 5px;">공지</span>
                                                    @endif
                                                    {{ $notice->title }}
                                                </a>
                                            </td>
                                            <td class="t_c">{{ $notice->author ?? 'Shop채널명' }}</td>
                                            <td class="t_c">{{ $notice->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="t_c" style="padding: 50px 0;">
                                                등록된 공지사항이 없습니다.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10">
                            <a href="{{ route('channel.community.register', ['shop_id' => $shopId]) }}">글쓰기</a>
                        </div>

                        <!-- 커스텀 페이징 -->
                        @if($notices->hasPages())
                            <div class="page_bx">
                                {{-- 첫 페이지 --}}
                                @if($notices->onFirstPage())
                                    <a href="{{ url()->current() }}" class="page_first disabled">first</a>
                                @else
                                    <a href="{{ $notices->appends(request()->query())->url(1) }}" class="page_first">first</a>
                                @endif

                                {{-- 이전 페이지 --}}
                                @if($notices->onFirstPage())
                                    <a href="{{ url()->current() }}" class="page_prev disabled">prev</a>
                                @else
                                    <a href="{{ $notices->appends(request()->query())->previousPageUrl() }}"
                                        class="page_prev">prev</a>
                                @endif

                                {{-- 페이지 번호 --}}
                                @php
                                    $start = max($notices->currentPage() - 2, 1);
                                    $end = min($start + 4, $notices->lastPage());
                                    $start = max($end - 4, 1);
                                    $start = max($start, 1); // Ensure start is at least 1
                                @endphp
                                @for($i = $start; $i <= $end; $i++)
                                    @if($i == $notices->currentPage())
                                        <a href="{{ url()->current() }}" class="num on">{{ $i }}</a>
                                    @else
                                        <a href="{{ $notices->appends(request()->query())->url($i) }}" class="num">{{ $i }}</a>
                                    @endif
                                @endfor

                                {{-- 다음 페이지 --}}
                                @if($notices->hasMorePages())
                                    <a href="{{ $notices->appends(request()->query())->nextPageUrl() }}" class="page_next">next</a>
                                @else
                                    <a href="{{ url()->current() }}" class="page_next disabled">next</a>
                                @endif

                                {{-- 마지막 페이지 --}}
                                @if($notices->currentPage() == $notices->lastPage())
                                    <a href="{{ url()->current() }}" class="page_last disabled">last</a>
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
                var shopId = '{{ $shopId }}';

                var url = '{{ route("channel.shop_community") }}?shop_id=' + shopId + '&per_page=' + perPage;
                if (searchValue) {
                    url += '&search_type=' + searchType + '&search_value=' + searchValue;
                }

                window.location.href = url;
            });
        });
    </script>
@endsection

@push('scripts')
    <script type="text/javascript">
    </script>
@endpush
