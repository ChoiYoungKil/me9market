@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '02')

@section('content')
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">방문한 채널</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>방문한 채널</li>
                        </ul>
                    </div>

                    <style>
                        .tb01.type3 thead th {
                            background-color: #f8f8f8 !important;
                            color: #333 !important;
                            border-bottom: 2px solid #333;
                            border-right: 1px solid #ddd;
                        }

                        .tb01.type3 thead th:last-child {
                            border-right: none;
                        }

                        .tb01.type3 tbody td {
                            padding: 20px 10px !important;
                            vertical-align: middle;
                        }

                        .tb01.type3 .shop_info_cell {
                            line-height: 1.4;
                        }

                        .tb01.type3 .shop_code {
                            font-weight: 700;
                            color: #333;
                            margin-bottom: 5px;
                        }

                        .tb01.type3 .shop_name {
                            font-weight: 500;
                            margin-bottom: 15px;
                        }

                        .tb01.type3 .shop_desc {
                            color: #888;
                            font-size: 13px;
                        }

                        .btn_visit {
                            display: inline-block;
                            padding: 5px 15px;
                            border: 1px solid #333;
                            background: #fff;
                            color: #333;
                            border-radius: 3px;
                            font-size: 13px;
                        }

                        /* 검색 버튼 스타일 보완 */
                        .list_top1 .searh_bx .btn {
                            font-size: 14px !important;
                            color: #fff !important;
                            background-color: #3470f7 !important;
                            border-radius: 0 5px 5px 0 !important;
                            width: 60px !important;
                            height: 40px !important;
                            line-height: 40px !important;
                            text-align: center !important;
                            border: none !important;
                            cursor: pointer !important;
                            position: absolute !important;
                            right: 0 !important;
                            top: 0 !important;
                            font-weight: 700 !important;
                        }

                        .list_top1 .searh_bx input[type=text] {
                            padding-right: 65px !important;
                        }
                    </style>

                    <div class="conbx">
                        <!-- 검색 바 -->
                        <div class="list_top1">
                            <div class="count">
                                총 <strong>{{ $visitedChannels->total() }}</strong> 건
                            </div>
                            <div class="searh_bx">
                                <form method="GET" action="{{ route('mypage.visited_channels') }}">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        placeholder="채널명을 입력해주세요">
                                    <button type="submit" class="btn">검색</button>
                                </form>
                            </div>
                        </div>

                        <!-- 전체 삭제 버튼 (검색창 바로 아래) -->
                        <div class="textR" style="margin-bottom: 15px;">
                            @if($visitedChannels->total() > 0)
                                <a href="javascript:void(0);" onclick="deleteAllVisitedChannels()" class="btn02 col7"
                                    style="padding: 10px 40px; font-size: 14px;">전체삭제</a>
                            @endif
                        </div>

                        <div class="con_w">
                            <div class="tb01 type3">
                                <table>
                                    <colgroup>
                                        <col style="width:15%;">
                                        <col style="width:10%;">
                                        <col style="width:auto%;">
                                        <col style="width:20%;">
                                        <col style="width:10%;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>방문 일자</th>
                                            <th>QR 코드</th>
                                            <th>Shop 채널명</th>
                                            <th>Shop 채널 주소</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($visitedChannels as $visit)
                                            <tr>
                                                <td class="textC">
                                                    {{ $visit->updated_at->format('Y.m.d') }}
                                                </td>
                                                <td class="textC">
                                                    <img src="/mypage_assets/images/common/qr_sample.png" alt="QR"
                                                        style="width: 40px; height: 40px; border: 1px solid #ddd;">
                                                </td>
                                                <td class="textL">
                                                    <div class="shop_info_cell">
                                                        <div class="shop_code">[a{{ $visit->vendor_id }}]</div>
                                                        <div class="shop_name">
                                                            {{ $visit->vendor && $visit->vendor->vendorbusinessdetails ? $visit->vendor->vendorbusinessdetails->shop_name : '알 수 없음' }}
                                                        </div>
                                                        <div class="shop_desc">
                                                            채널에 대한 간략 설명 적는 부분
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="textL">
                                                    @if ($visit->vendor && $visit->vendor->vendorbusinessdetails && $visit->vendor->vendorbusinessdetails->shop_website)
                                                        {{ $visit->vendor->vendorbusinessdetails->shop_website }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="textC">
                                                    @php
                                                        $visit_url = '#';
                                                        if ($visit->vendor && $visit->vendor->vendorbusinessdetails && $visit->vendor->vendorbusinessdetails->shop_website) {
                                                            $visit_url = $visit->vendor->vendorbusinessdetails->shop_website;
                                                            if (!Str::startsWith($visit_url, ['http://', 'https://', '//'])) {
                                                                $visit_url = '//' . $visit_url;
                                                            }
                                                        }
                                                    @endphp
                                                    <a href="{{ $visit_url }}" class="btn_visit">방문하기</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="no_data">
                                                    방문한 채널 내역이 없습니다.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- 페이징 -->
                            <div class="paging">
                                {{ $visitedChannels->appends(request()->input())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->
@endsection

@push('scripts')
    <script>
        function deleteAllVisitedChannels() {
            if (confirm('모든 방문 기록을 삭제하시겠습니까?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('mypage.visited_channels.delete_all') }}";

                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = "{{ csrf_token() }}";
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @if(Session::has('success_message'))
        <script>
            alert("{{ Session::get('success_message') }}");
        </script>
    @endif
@endpush