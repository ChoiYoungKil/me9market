@extends('layouts.channel')

@section('content')
    <style>
        .product-own-search {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-own-search .btn01.arrow {
            width: 100px;
            height: 34px;
            line-height: 32px;
        }

        .product-own-list-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .product-own-list-actions .btn01 {
            height: 34px;
            line-height: 32px;
            font-size: 12px;
            text-align: center;
            padding: 0;
        }

        .product-own-list-actions .btn-excel {
            width: 140px;
        }

        .product-own-list-actions .btn-create {
            width: 100px;
        }
    </style>


    @php
        $page_type = "sub";
        $dep1_id = "02";
        $dep1_tit = "상품관리";
    @endphp
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">자사상품관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>상품관리</li>
                        <li>자사상품관리</li>
                    </ul>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.shop_info') }}"><span>Shop채널 정보</span></a></li>
                        <li><a href="{{ route('channel.product_own') }}" class="on"><span>판매상품</span></a></li>
                        <li><a href="{{ route('channel.shop_community') }}"><span>커뮤니티</span></a></li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <form method="GET" action="{{ route('channel.product_own') }}" id="productSearchForm">
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
                                        <td colspan="3">
                                            <div class="r_btn_w product-own-search">
                                                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="상품명 또는 상품코드를 입력해 주세요."
                                                    class="wFull">
                                                <a id="arrow1" class="btn01 arrow"><span>상세검색</span></a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tb01 bN arrowbx" data-arrowbx="arrow1">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>상품분류</span></th>
                                        <td colspan="3">
                                            <ul class="type_bx w600">
                                                <li>
                                                    <select name="category_id" class="wFull">
                                                        <option value="">전체 상품분류</option>
                                                        @foreach($categoryOptions ?? [] as $category)
                                                            <option value="{{ $category['id'] }}" {{ (int)($filters['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' }}>
                                                                {{ $category['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>상품상태</span></th>
                                        <td>
	                                            <ul class="chk01">
	                                                <li>
	                                                    <input type="radio" name="status" id="status_all" value="" {{ ($filters['status'] ?? '') === '' ? 'checked' : '' }}>
	                                                    <label for="status_all">전체</label>
	                                                </li>
	                                                <li>
	                                                    <input type="radio" name="status" id="status_sale" value="1" {{ (string)($filters['status'] ?? '') === '1' ? 'checked' : '' }}>
	                                                    <label for="status_sale">판매</label>
	                                                </li>
	                                                <li>
	                                                    <input type="radio" name="status" id="status_stop" value="0" {{ (string)($filters['status'] ?? '') === '0' ? 'checked' : '' }}>
	                                                    <label for="status_stop">중지</label>
	                                                </li>
	                                                <li>
	                                                    <input type="radio" name="status" id="status_stop_notice" value="stop_notice" {{ ($filters['status'] ?? '') === 'stop_notice' ? 'checked' : '' }}>
	                                                    <label for="status_stop_notice">판매중지예고</label>
	                                                </li>
	                                            </ul>
                                        </td>
                                        <th class="w160"><span>판매범위</span></th>
                                        <td>
	                                            <ul class="chk01">
	                                                <li>
	                                                    <input type="radio" name="sale_scope" id="sale_scope_all" value="" {{ ($filters['sale_scope'] ?? '') === '' ? 'checked' : '' }}>
	                                                    <label for="sale_scope_all">전체</label>
	                                                </li>
	                                                <li>
	                                                    <input type="radio" name="sale_scope" id="sale_scope_own" value="own" {{ ($filters['sale_scope'] ?? '') === 'own' ? 'checked' : '' }}>
	                                                    <label for="sale_scope_own">자사상품</label>
	                                                </li>
	                                                <li>
	                                                    <input type="radio" name="sale_scope" id="sale_scope_public" value="public" {{ ($filters['sale_scope'] ?? '') === 'public' ? 'checked' : '' }}>
	                                                    <label for="sale_scope_public">공개상품</label>
	                                                </li>
	                                                <li>
	                                                    <input type="radio" name="sale_scope" id="sale_scope_partial" value="partial" {{ ($filters['sale_scope'] ?? '') === 'partial' ? 'checked' : '' }}>
	                                                    <label for="sale_scope_partial">부분공개상품</label>
	                                                </li>
	                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10 search-actions mb40">
                            <button type="submit" class="type2">검색</button>
                            <a href="{{ route('channel.product_own') }}" class="type2 col5">초기화</a>
                        </div>
                        </form>

                        <div class="list_top1 channel-list-top">
                            <div class="count">총 <strong>{{ $products->total() }}</strong> 건</div>
                            <div class="right_bx list-top-actions product-own-list-actions">
                                <select id="perPageSelect" class="w160">
                                    @foreach([20, 40, 60, 80, 100] as $perPageOption)
                                        <option value="{{ $perPageOption }}" {{ (int)($filters['per_page'] ?? 20) === $perPageOption ? 'selected' : '' }}>{{ $perPageOption }}개씩 보기</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('channel.product.own.export', request()->query()) }}" class="btn01 col2 btn-excel">EXCEL
                                    다운로드</a>
                                <a href="{{ route('channel.product.base.create') }}" class="btn01 col5 btn-create">상품등록</a>
                            </div>
                        </div>

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="80px">
                                    <col width="80px">
                                    <col width="">
                                    <col width="100px">
                                    <col width="100px">
                                    <col width="80px">
                                    <col width="120px">
                                    <col width="130px">
                                    <col width="120px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>상품코드</th>
                                        <th>상품상태</th>
                                        <th>상품명</th>
                                        <th>금액</th>
                                        <th>채널범위</th>
                                        <th>게시채널</th>
                                        <th>판매요청목록</th>
                                        <th>판매중지신청</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody class="textL">
                                    @forelse($products as $product)
                                        @php
                                            $mainImage = $product->images->first();
                                            $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');
                                            $statusText = $product->status == 1 ? '판매' : '중지';
                                            $statusClass = $product->status == 1 ? '' : 'fcol1';
                                            $stopNoticePayload = [
                                                'id' => $product->id,
                                                'name' => $product->product_name,
                                                'code' => $product->product_code,
                                                'stop_notice_at' => optional($product->stop_notice_at)->format('Y-m-d'),
                                                'stop_notice_reason' => $product->stop_notice_reason,
                                            ];
                                        @endphp
                                        <tr>
                                            <td class="t_c">{{ $product->product_code }}</td>
                                            <td class="t_c {{ $statusClass }}">{{ $statusText }}</td>
                                            <td>
                                                <div class="thum01">
                                                    <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                                    <div class="txt_bx">
                                                        <p>{{ $product->category_path }}</p>
                                                        <strong>{{ $product->product_name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="t_c">₩ {{ number_format($product->product_price) }}</td>
                                            <td class="t_c">
                                                {{ in_array($product->is_public, ['Yes', 1, '1'], true) ? '공개' : '비공개' }},
                                                {{ in_array($product->is_partial, ['Yes', 1, '1'], true) ? '부분공개' : '전체공개' }}
                                            </td>
                                            <td class="t_c">
                                                <a href="javascript:void(0);" class="btn02 col3 pop_btn" data-pop="pop4_1">
                                                    {{ $product->shop_channels_count ?? '0' }}
                                                </a>
                                            </td>
                                            <td class="t_c">
                                                <a href="javascript:void(0);" class="btn02 col5 pop_btn" data-pop="pop_request_{{ $product->id }}">
                                                    판매요청목록
                                                    @if(($product->sales_request_count ?? 0) > 0)
                                                        ({{ $product->sales_request_count }})
                                                    @endif
                                                </a>
                                            </td>
                                            <td class="t_c">
                                                <a href="javascript:void(0);" class="btn02 col7"
                                                    onclick='openStopNoticeModal(@json($stopNoticePayload)); return false;'>판매중지 예고신청</a>
                                            </td>
                                            <td class="t_c">
                                                <a href="javascript:void(0);" class="btn02 col5 pop_btn" data-pop="pop3_1" data-id="{{ $product->id }}">보기</a>
                                                <a href="javascript:void(0);" class="btn02 col2" onclick="copyProduct('{{ $product->id }}'); return false;">복사</a>
                                                <a href="{{ route('channel.product.base.edit', $product->id) }}" class="btn02 col4 mt5">수정</a>
                                                <a href="javascript:void(0);" class="btn02 mt5" onclick="deleteProduct('{{ $product->id }}'); return false;">삭제</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="t_c" style="padding: 100px 0;">
                                                등록된 자사 상품이 없습니다.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                        </div>

                        <!-- 페이징 -->
                        <div class="page_bx1">
                            {{ $products->links() }}
                        </div>


                        <!-- 팝업 -->
                        <!-- 게시채널 팝업 -->
                        <div class="popup_bx" data-id="pop4_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">상품게시한 채널목록</div>
                                        </div>

                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="thum01">
                                                    <div class="img_bx"
                                                        style="background-image:url({{ asset('channel_assets/images/sub/thum01.jpg') }})">
                                                    </div>
                                                    <div class="txt_bx">
                                                        <p>대분류 &gt; 중분류 &gt; 소분류</p>
                                                        <strong>상품명 111111</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="con_w">
                                                <div class="list_top1">
                                                    <div class="count">총 <strong>00</strong> 건</div>
                                                </div>
                                                <div class="tb01 ovS">
                                                    <table>
                                                        <colgroup>
                                                            <col width="80px">
                                                            <col width="80px">
                                                            <col width="">
                                                            <col width="100px">
                                                            <col width="80px">
                                                            <col width="10%">
                                                            <col width="10%">
                                                        </colgroup>
                                                        <thead>
                                                            <tr>
                                                                <th>채널코드</th>
                                                                <th>채널상태</th>
                                                                <th>채널명</th>
                                                                <th>채널범위</th>
                                                                <th>상품수</th>
                                                                <th>QR 코드</th>
                                                                <th>단축주소</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="textL">
                                                            <tr>
                                                                <td class="t_c">a20392</td>
                                                                <td class="t_c">운영</td>
                                                                <td>
                                                                    채널명 123
                                                                    <ul class="tag_list">
                                                                        <li>#그룹 키워드 #1</li>
                                                                        <li>#키워드 #2</li>
                                                                    </ul>
                                                                </td>
                                                                <td class="t_c">공개, 회원용</td>
                                                                <td class="t_c">03</td>
                                                                <td class="t_c">
                                                                    <div class="pop_btn" data-pop="pop4_1_1">
                                                                        <img src="/images/channel/sub/qr_sample1.jpg"
                                                                            style="max-width: 60px; width:100%;">
                                                                    </div>
                                                                </td>
                                                                <td class="t_c">//qcc112ko</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="t_c">a20392</td>
                                                                <td class="t_c">중지</td>
                                                                <td>
                                                                    비공개 채널명 123
                                                                    <ul class="tag_list">
                                                                        <li>#그룹 키워드 #1</li>
                                                                    </ul>
                                                                </td>
                                                                <td class="t_c">비공개, 회원용</td>
                                                                <td class="t_c">--</td>
                                                                <td class="t_c">
                                                                    <div class="pop_btn" data-pop="pop4_1_1">
                                                                        <img src="/images/channel/sub/qr_sample1.jpg"
                                                                            style="max-width: 60px; width:100%;">
                                                                    </div>
                                                                </td>
                                                                <td class="t_c">//qcc112ko</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="t_c">a20392</td>
                                                                <td class="t_c">운영</td>
                                                                <td>
                                                                    일반용 채널명 123
                                                                    <ul class="tag_list">
                                                                        <li>#그룹 키워드 #1</li>
                                                                        <li>#키워드 #2</li>
                                                                    </ul>
                                                                </td>
                                                                <td class="t_c">공개, 일반용</td>
                                                                <td class="t_c">13</td>
                                                                <td class="t_c">
                                                                    <div class="pop_btn" data-pop="pop4_1_1">
                                                                        <img src="/images/channel/sub/qr_sample1.jpg"
                                                                            style="max-width: 60px; width:100%;">
                                                                    </div>
                                                                </td>
                                                                <td class="t_c">//qcc112ko</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

                                                    <!-- 페이징 -->
                                                    <div class="page_bx1">
                                                        <a href="javascript:void(0);" class="page_first">first</a>
                                                        <a href="javascript:void(0);" class="page_prev">prev</a>
                                                        <a href="javascript:void(0);" class="num on">1</a>
                                                        <a href="javascript:void(0);" class="num">2</a>
                                                        <a href="javascript:void(0);" class="num">3</a>
                                                        <a href="javascript:void(0);" class="num">4</a>
                                                        <a href="javascript:void(0);" class="num">5</a>
                                                        <a href="javascript:void(0);" class="page_next">next</a>
                                                        <a href="javascript:void(0);" class="page_last">last</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 게시채널 팝업 ==> RQ 팝업 -->
                        <div class="popup_bx" data-id="pop4_1_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w457">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="ttl01">QR 코드</div>
                                                <div class="img_bx"></div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt20">
                                            <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript">
                            $(".pop_btn[data-pop='pop4_1_1']").click(function () {
                                var popId = $(this).attr("data-pop");
                                if (popId == "pop4_1_1") {
                                    var thisImg = $(this).children("img").clone();
                                    $(".popup_bx[data-id='" + popId + "']").find(".img_bx").html(thisImg);
                                    $(".popup_bx[data-id='" + popId + "']").find(".img_bx").children("img").css({ "max-width": "100%", "width": "auto", "margin": "0 auto", "display": "block" });
                                }

                                return false;
                            });
                        </script>

                        <!-- 판매요청목록 팝업 -->
                        @foreach($products as $requestProduct)
                            @php
                                $salesRequests = $requestProduct->shopChannelProducts
                                    ->where('product_type', 'partial')
                                    ->values();
                            @endphp
                            <div class="popup_bx" data-id="pop_request_{{ $requestProduct->id }}">
                                <div class="pop_w">
                                    <div class="pop_inner">
                                        <div class="pop_con">
                                            <div class="close_btn close1">닫기</div>
                                            <div class="page_info type2">
                                                <div class="ttl">판매요청목록</div>
                                            </div>

                                            <div class="conbx">
                                                <div class="con_w">
                                                    <div class="tb01">
                                                        <table>
                                                            <colgroup>
                                                                <col width="160px">
                                                                <col width="">
                                                            </colgroup>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>상품명</span></th>
                                                                    <td>{{ $requestProduct->product_name }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>상품분류</span></th>
                                                                    <td>{{ $requestProduct->category->category_name ?? '카테고리 없음' }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="con_w">
                                                    <div class="list_top1">
                                                        <div class="count">총 <strong>{{ $salesRequests->count() }}</strong> 건</div>
                                                    </div>

                                                    <div class="tb01 ovS">
                                                        <table>
                                                            <colgroup>
                                                                <col width="50px">
                                                                <col width="60px">
                                                                <col width="150px">
                                                                <col width="17%">
                                                                <col width="130px">
                                                                <col width="">
                                                                <col width="100px">
                                                            </colgroup>
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" onclick="toggleSalesRequestChecks({{ $requestProduct->id }}, this.checked)"></th>
                                                                    <th>번호</th>
                                                                    <th>신청일시</th>
                                                                    <th>상품명</th>
                                                                    <th>신청자</th>
                                                                    <th>신청사유</th>
                                                                    <th>상태</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($salesRequests as $index => $salesRequest)
                                                                    @php
                                                                        $statusLabel = match($salesRequest->approval_status) {
                                                                            'approved' => '승인',
                                                                            'rejected' => '승인거절',
                                                                            default => '미승인',
                                                                        };
                                                                        $isPending = $salesRequest->approval_status === 'pending';
                                                                        $requesterName = $salesRequest->shopChannel->channel_name ?? '-';
                                                                        $vendorName = $salesRequest->shopChannel->vendor->name ?? null;
                                                                    @endphp
                                                                    <tr>
                                                                        <td>
                                                                            <input type="checkbox"
                                                                                class="sales-request-check sales-request-check-{{ $requestProduct->id }}"
                                                                                value="{{ $salesRequest->id }}"
                                                                                @if(!$isPending) disabled @endif>
                                                                        </td>
                                                                        <td>{{ $salesRequests->count() - $index }}</td>
                                                                        <td>{{ optional($salesRequest->requested_at ?? $salesRequest->created_at)->format('Y-m-d H:i') }}</td>
                                                                        <td class="t_l">{{ $requestProduct->product_name }}</td>
                                                                        <td>
                                                                            {{ $requesterName }}
                                                                            @if($vendorName)
                                                                                <br>({{ $vendorName }})
                                                                            @endif
                                                                        </td>
                                                                        <td class="t_l">{{ $salesRequest->request_reason ?: '-' }}</td>
                                                                        <td>{{ $statusLabel }}</td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="7" class="t_c" style="padding: 40px 0;">판매요청 내역이 없습니다.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="btm_btn right mt10">
                                                        <a href="javascript:void(0);" class="col5" onclick="handleSalesRequests({{ $requestProduct->id }}, 2); return false;">승인거절</a>
                                                        <a href="javascript:void(0);" onclick="handleSalesRequests({{ $requestProduct->id }}, 1); return false;">판매승인</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- 판매중지 예고신청 팝업 -->
                        <div class="popup_bx" data-id="pop2_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">판매중지예고 설정</div>
                                        </div>

                                        <form id="stop_notice_form">
                                            <input type="hidden" name="product_id" id="stop_notice_product_id">
                                            <div class="conbx">
                                                <div class="con_w">
                                                    <div class="imp_bx01">
                                                        <div class="txt1"><span>유의사항</span></div>
                                                        <div class="txt2">
                                                            판매중지 예고설정 시 <br>해당 상품이 게시 된 상세페이지에 중지 예정일이 표기되지만 <br>상품이 자동으로 판매중지
                                                            되지는 않습니다.
                                                        </div>
                                                    </div>

                                                    <div class="tb01 mt10">
                                                        <table>
                                                            <colgroup>
                                                                <col width="180px">
                                                                <col width="">
                                                            </colgroup>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>상품</span></th>
                                                                    <td id="stop_notice_product_name">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>판매중지 예고일자 설정</span></th>
                                                                    <td>
                                                                        <input class="datepicker w160" type="text"
                                                                            name="stop_notice_at" id="stop_notice_at"
                                                                            required="required" readonly="">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>예고 사유</span></th>
                                                                    <td>
                                                                        <textarea name="stop_notice_reason" id="stop_notice_reason"
                                                                            style="width:100%; min-height:90px;"></textarea>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="javascript:void(0);" class="col5 close_btn">취소</a>
                                            <a href="javascript:void(0);" onclick="submitStopNotice(); return false;">확인</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 보기 팝업 -->
                        @include('channel.sub02.inc.pop_product_own_view')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $(".btn01.arrow").click(function () {
            var thisId = $(this).attr("id");
            $(this).toggleClass("on");
            $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
        });

        $("#perPageSelect").change(function () {
            var url = new URL(window.location.href);
            url.searchParams.set("per_page", $(this).val());
            url.searchParams.delete("page");
            window.location.href = url.toString();
        });

        function numberFormat(value) {
            var number = parseFloat(value || 0);
            return number.toLocaleString();
        }

        function rebuildProductImages($pop, images) {
            var fallback = "{{ asset('channel_assets/images/sub/thum01.jpg') }}";
            var imageUrls = images && images.length ? images : [fallback];
            var $mainImage = $pop.find(".product-detail-main-image");
            var $slider = $pop.find(".product-detail-image-list");

            if ($slider.hasClass("slick-initialized")) {
                $slider.slick("unslick");
            }

            $slider.empty();
            imageUrls.forEach(function (url, index) {
                $slider.append(
                    '<li><div class="con ' + (index === 0 ? 'on' : '') + '"><img src="' + url + '"></div></li>'
                );
            });
            $mainImage.attr("src", imageUrls[0] || fallback);

            $slider.slick({
                dots: false,
                arrows: true,
                autoplay: false,
                infinite: false,
                autoplaySpeed: 4000,
                slidesToShow: 4,
                slidesToScroll: 1,
                draggable: true,
                focusOnSelect: false,
                pauseOnFocus: false,
                pauseOnHover: false,
                swipe: false,
            });

            $slider.find(".con").off("click").on("click", function () {
                $slider.find(".con").removeClass("on");
                $(this).addClass("on");
                $mainImage.attr("src", $(this).find("img").attr("src"));
            });
        }

        function renderProductOptions($pop, options) {
            var $select = $pop.find(".product-detail-option-select");
            var $list = $pop.find(".product-detail-option-list");
            $select.empty();
            $list.empty();

            if (!options || !options.length) {
                $select.append('<option>-선택-</option>');
                $list.append('<li style="padding:10px 0; border-bottom:1px solid #eee;">등록된 옵션이 없습니다.</li>');
                return;
            }

            $select.append('<option>-선택-</option>');
            options.forEach(function (option) {
                var label = (option.name ? option.name + ' : ' : '') + (option.value || '-');
                $select.append('<option>' + label + '</option>');
                $list.append(
                    '<li style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eee;">' +
                        '<div class="txt1" style="flex:1; font-weight:bold;">' + label + '</div>' +
                        '<div class="txt2" style="width:90px; text-align:right; font-weight:bold; margin-right:10px;">' + numberFormat(option.price) + '원</div>' +
                        '<div style="width:70px; text-align:right; color:#777;">재고 ' + numberFormat(option.stock) + '</div>' +
                    '</li>'
                );
            });
        }

        function applyProductDetail(response) {
            var p = response.product;
            var $pop = $(".popup_bx[data-id='pop3_1']");

            $pop.find(".product-detail-category").text(response.category_path || '카테고리 없음');
            $pop.find(".product-detail-name").text(p.product_name || '-');
            $pop.find(".product-detail-code").text("상품코드 : " + (p.product_code || '-'));
            $pop.find(".product-detail-seller").text("판매자 : " + (p.seller_name || '-'));
            $pop.find(".product-detail-reward").text(p.reward_points_label || '0 point');
            $pop.find(".product-detail-tax").text(p.tax_label || '-');
            $pop.find(".product-detail-price").text(p.price_condition_label || (numberFormat(p.product_price) + ' 원'));
            $pop.find(".product-detail-profit").text(p.profit_share_label || '-');
            $pop.find(".product-detail-stock").text(p.stock_label || '-');
            $pop.find(".product-detail-purchase-limit").text(p.purchase_limit_label || '-');
            $pop.find(".product-detail-html").html(p.detail_html || p.description || '등록된 상세 설명이 없습니다.');

            rebuildProductImages($pop, p.image_urls || []);
            renderProductOptions($pop, p.option_rows || []);

            $pop.stop().fadeIn(300);
            $pop.scrollTop(0);
        }

        /* 팝업 */
        $(".pop_btn").click(function () {
            var popId = $(this).attr("data-pop");
            var productId = $(this).attr("data-id");

            if (popId === 'pop3_1' && productId) {
                $.get("/channel/product/base/detail/" + productId, function(response) {
                    if (response.status) {
                        applyProductDetail(response);
                    } else {
                        alert(response.message || '데이터를 불러오지 못했습니다.');
                    }
                });
                return false;
            }

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

        function toggleSalesRequestChecks(productId, checked) {
            $(".sales-request-check-" + productId + ":not(:disabled)").prop("checked", checked);
        }

        function handleSalesRequests(productId, status) {
            var ids = $(".sales-request-check-" + productId + ":checked").map(function () {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                alert('처리할 판매요청을 선택해 주세요.');
                return;
            }

            var actionName = status == 1 ? '판매승인' : '승인거절';
            if (!confirm('선택한 판매요청을 ' + actionName + ' 처리하시겠습니까?')) return;

            $.ajax({
                url: "{{ route('channel.product.request.update') }}",
                type: 'POST',
                data: {
                    _token: csrfToken,
                    request_ids: ids,
                    status: status
                },
                success: function(response) {
                    if (response.status) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '오류가 발생했습니다.');
                    }
                },
                error: function(xhr) {
                    alert('오류가 발생했습니다: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }

        function openStopNoticeModal(product) {
            $("#stop_notice_product_id").val(product.id);
            $("#stop_notice_product_name").text(product.name + " (" + product.code + ")");
            $("#stop_notice_at").val(product.stop_notice_at || "");
            $("#stop_notice_reason").val(product.stop_notice_reason || "");
            $(".popup_bx[data-id='pop2_1']").stop().fadeIn(300);
            $(".popup_bx[data-id='pop2_1']").scrollTop(0);
        }

        function submitStopNotice() {
            var productId = $("#stop_notice_product_id").val();
            var noticeAt = $("#stop_notice_at").val();

            if (!productId || !noticeAt) {
                alert('판매중지 예고일자를 선택해 주세요.');
                return;
            }

            $.ajax({
                url: "/channel/product/base/stop-notice/" + productId,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    stop_notice_at: noticeAt,
                    stop_notice_reason: $("#stop_notice_reason").val()
                },
                success: function(response) {
                    if (response.status) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '오류가 발생했습니다.');
                    }
                },
                error: function(xhr) {
                    alert('오류가 발생했습니다: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }

        function deleteProduct(productId) {
            if (!confirm('정말로 이 상품을 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;

            $.ajax({
                url: "/channel/product/base/delete/" + productId,
                type: 'POST',
                data: {
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.status) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '오류가 발생했습니다.');
                    }
                },
                error: function(xhr) {
                    alert('오류가 발생했습니다: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }

        function copyProduct(productId) {
            if (!confirm('이 상품을 복사하시겠습니까?')) return;

            $.ajax({
                url: "/channel/product/base/copy/" + productId,
                type: 'POST',
                data: {
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.status) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '오류가 발생했습니다.');
                    }
                },
                error: function(xhr) {
                    alert('오류가 발생했습니다: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }
    </script>
@endpush
