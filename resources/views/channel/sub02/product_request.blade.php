@extends('layouts.channel')

@php
    $dep1_id = "02";
    $dep1_tit = "상품관리";
@endphp

@section('page_type', 'sub')

@section('content')
    <div id="container_w">
        <div id="contents">
            <div class="row">
                <div class="box box1">
                    <div class="page_info">
                        <div class="ttl">판매 요청 관리</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>상품관리</li>
                            <li>판매 요청 관리</li>
                        </ul>
                    </div>
                    
                    <div class="tab_bx1">
                        <ul>
                            <li><a href="{{ route('channel.product_own') }}"><span>자사상품관리</span></a></li>
                            <li><a href="{{ route('channel.product_public') }}"><span>공개상품관리</span></a></li>
                            <li><a href="{{ route('channel.product_partial') }}"><span>부분공개상품관리</span></a></li>
                            <li><a href="{{ url()->current() }}" class="on"><span>판매 요청 관리</span></a></li>
                        </ul>
                    </div>

                    <div class="conbx">
                        <div class="con_w">
                            <form method="GET" action="{{ route('channel.product_request') }}" id="requestSearchForm">
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
                                                <th class="w160"><span>상품명</span></th>
                                                <td>
                                                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="상품명 또는 상품코드">
                                                </td>
                                                <th class="w160"><span>요청자</span></th>
                                                <td>
                                                    <input type="text" name="requester" value="{{ $filters['requester'] ?? '' }}" placeholder="채널명, 채널코드, 벤더명">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>요청상태</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="request_status" id="request_status_all" value="" {{ ($filters['request_status'] ?? '') === '' ? 'checked' : '' }}>
                                                            <label for="request_status_all">전체</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="request_status" id="request_status_wait" value="0" {{ (string)($filters['request_status'] ?? '') === '0' ? 'checked' : '' }}>
                                                            <label for="request_status_wait">대기</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="request_status" id="request_status_approved" value="1" {{ (string)($filters['request_status'] ?? '') === '1' ? 'checked' : '' }}>
                                                            <label for="request_status_approved">허용</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="request_status" id="request_status_rejected" value="2" {{ (string)($filters['request_status'] ?? '') === '2' ? 'checked' : '' }}>
                                                            <label for="request_status_rejected">거부</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="btm_btn right mt10 search-actions mb40">
                                    <button type="submit" class="type2">검색</button>
                                    <a href="{{ route('channel.product_request') }}" class="type2 col5">초기화</a>
                                </div>
                            </form>

                            <div class="list_top1 channel-list-top">
                                <div class="count">총 <strong>{{ $requests->total() }}</strong> 건</div>
                                <div class="right_bx list-top-actions">
                                    <select id="perPageSelect" class="w160">
                                        @foreach([20, 40, 60, 80, 100] as $perPageOption)
                                            <option value="{{ $perPageOption }}" {{ (int)($filters['per_page'] ?? 20) === $perPageOption ? 'selected' : '' }}>{{ $perPageOption }}개씩 보기</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="tb01 ovS">
                                <table>
                                    <colgroup>
                                        <col width="70px">
                                        <col width="100px">
                                        <col width="">
                                        <col width="150px">
                                        <col width="150px">
                                        <col width="110px">
                                        <col width="110px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>번호</th>
                                            <th>상품코드</th>
                                            <th>상품정보</th>
                                            <th>요청자 (채널/벤더)</th>
                                            <th>요청일</th>
                                            <th>상태</th>
                                            <th>상세보기</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($requests as $index => $req)
                                            @php
                                                $product = $req->product;
                                                $mainImage = $product->images->first();
                                                $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');
                                                $statusText = match($req->status) {
                                                    0 => '대기',
                                                    1 => '허용',
                                                    2 => '거부',
                                                    default => '알수없음'
                                                };
                                                $statusColor = match($req->status) {
                                                    0 => 'col1',
                                                    1 => 'col2',
                                                    2 => 'col4',
                                                    default => 'col5'
                                                };
                                            @endphp
                                            <tr>
                                                <td>{{ $requests->total() - ($requests->currentPage() - 1) * $requests->perPage() - $index }}</td>
                                                <td>{{ $product->product_code }}</td>
                                                <td class="t_l">
                                                    <div class="thum01">
                                                        <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                                        <div class="txt_bx">
                                                            <p>{{ $product->category->category_name ?? '카테고리 없음' }}</p>
                                                            <strong>{{ $product->product_name }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $req->shopChannel->channel_name ?? '-' }}<br>
                                                    ({{ $req->shopChannel->vendor->name ?? '벤더정보없음' }})
                                                </td>
                                                <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                                <td><span class="btn02 {{ $statusColor }}">{{ $statusText }}</span></td>
                                                <td>
                                                    <a href="{{ url()->current() }}" class="btn02 col5 pop_btn" data-pop="pop_detail_{{ $req->id }}">보기</a>
                                                </td>
                                            </tr>

                                            <!-- 상세 팝업 (Slide 95) -->
                                            <div class="popup_bx" data-id="pop_detail_{{ $req->id }}">
                                                <div class="pop_w">
                                                    <div class="pop_inner">
                                                        <div class="pop_con w640">
                                                            <div class="close_btn close1">닫기</div>
                                                            <div class="page_info type2">
                                                                <div class="ttl">상품 판매 요청 상세 ({{ $product->product_name }})</div>
                                                            </div>

                                                            <div class="conbx">
                                                                <div class="con_w">
                                                                    <div class="ttl01">상품 정보</div>
                                                                    <div class="tb01 textL">
                                                                        <table>
                                                                            <colgroup><col width="160px"><col width=""></colgroup>
                                                                            <tbody>
                                                                                <tr><th>상품코드</th><td>{{ $product->product_code }}</td></tr>
                                                                                <tr><th>카테고리</th><td>{{ $product->category->category_name ?? '-' }}</td></tr>
                                                                                <tr><th>기본가격</th><td>{{ number_format($product->product_price) }}원</td></tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>

                                                                <div class="con_w">
                                                                    <div class="ttl01">요청 정보</div>
                                                                    <div class="tb01 textL">
                                                                        <table>
                                                                            <colgroup><col width="160px"><col width=""></colgroup>
                                                                            <tbody>
                                                                                <tr><th>요청 채널</th><td>{{ $req->shopChannel->channel_name ?? '-' }} ({{ $req->shopChannel->channel_code ?? '-' }})</td></tr>
                                                                                <tr><th>요청 벤더</th><td>{{ $req->shopChannel->vendor->name ?? '-' }} ({{ $req->shopChannel->vendor->email ?? '-' }})</td></tr>
                                                                                <tr><th>요청 판매가</th><td>{{ number_format($req->selling_price) }}원</td></tr>
                                                                                <tr><th>현재 상태</th><td>{{ $statusText }}</td></tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- 하단버튼 -->
                                                            <div class="btm_btn mt10">
                                                                @if($req->status == 0)
                                                                    <a href="{{ url()->current() }}" class="btn01 col2" onclick='updateRequestStatus("{{ $req->id }}", 1); return false;'>허용</a>
                                                                    <a href="{{ url()->current() }}" class="btn01 col4" onclick='updateRequestStatus("{{ $req->id }}", 2); return false;'>거부</a>
                                                                @endif
                                                                <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="t_c" style="padding: 50px 0;">
                                                    판매 요청 내역이 없습니다.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="page_bx1">
                                {{ $requests->links() }}
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
        $(function () {
            $("#perPageSelect").change(function () {
                var url = new URL(window.location.href);
                url.searchParams.set("per_page", $(this).val());
                url.searchParams.delete("page");
                window.location.href = url.toString();
            });

            /* 팝업 */
            $(".pop_btn").click(function () {
                var popId = $(this).attr("data-pop");
                $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                $(".popup_bx[data-id='" + popId + "']").scrollTop(0);
                return false;
            });
            $(".popup_bx .close_btn").click(function () {
                $(this).parents(".popup_bx").stop().fadeOut(300);
                return false;
            });
        });

        function updateRequestStatus(requestId, status) {
            var actionName = status == 1 ? '허용' : '거부';
            if (!confirm('정말로 해당 요청을 ' + actionName + ' 하시겠습니까?')) return;

            var token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: "{{ route('channel.product.request.update') }}",
                type: 'POST',
                data: {
                    _token: token,
                    request_id: requestId,
                    status: status
                },
                success: function (response) {
                    if (response.status) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '오류가 발생했습니다.');
                    }
                },
                error: function (xhr) {
                    alert('오류가 발생했습니다: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }
    </script>
@endpush
