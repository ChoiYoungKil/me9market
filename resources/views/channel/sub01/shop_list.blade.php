@extends('layouts.channel')

@section('page_type', 'sub')
@php
    $dep1_id = "01";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">Shop채널목록</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>Shop채널관리</li>
                        <li>Shop채널목록</li>
                    </ul>
                </div>
                <div class="conbx">
                    <form action="{{ route('channel.shop_list') }}" method="GET">
                        <div class="con_w">
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>Shop 채널명</span></th>
                                            <td>
                                                <div class="r_btn_w" style="display: flex; align-items: center; gap: 5px;">
                                                    <input type="text" name="search_name"
                                                        value="{{ $params['search_name'] ?? '' }}"
                                                        placeholder="채널명을 입력해 주세요." style="flex: 1;">
                                                    <a id="arrow1" class="btn01 arrow"
                                                        style="width: 100px;"><span>상세검색</span></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tb01 bN arrowbx" data-arrowbx="arrow1"
                                style="display: {{ (isset($params['search_keyword']) || isset($params['status']) || isset($params['is_public'])) ? 'block' : 'none' }};">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>채널키워드</span></th>
                                            <td>
                                                <input type="text" name="search_keyword"
                                                    value="{{ $params['search_keyword'] ?? '' }}"
                                                    placeholder="키워드를 입력해 주세요.">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>채널상태</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="status" id="radio1_1" value="all" {{ ($params['status'] ?? 'all') == 'all' ? 'checked' : '' }}>
                                                        <label for="radio1_1">전체</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="status" id="radio1_2" value="1" {{ ($params['status'] ?? '') == '1' ? 'checked' : '' }}>
                                                        <label for="radio1_2">운영</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="status" id="radio1_3" value="0" {{ ($params['status'] ?? '') == '0' ? 'checked' : '' }}>
                                                        <label for="radio1_3">중지</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>채널범위</span></th>
                                            <td>
                                                <ul class="chk02">
                                                    <li>
                                                        <input type="checkbox" name="is_public[]" id="chk1_1" value="1" {{ in_array('1', $params['is_public'] ?? []) ? 'checked' : '' }}>
                                                        <label for="chk1_1">공개</label>
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" name="is_public[]" id="chk1_2" value="0" {{ in_array('0', $params['is_public'] ?? []) ? 'checked' : '' }}>
                                                        <label for="chk1_2">비공개</label>
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" name="is_member_only[]" id="chk1_3" value="0"
                                                            {{ in_array('0', $params['is_member_only'] ?? []) ? 'checked' : '' }}>
                                                        <label for="chk1_3">일반용</label>
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" name="is_member_only[]" id="chk1_4" value="1"
                                                            {{ in_array('1', $params['is_member_only'] ?? []) ? 'checked' : '' }}>
                                                        <label for="chk1_4">회원용</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn right mt10">
                                <button type="submit" class="type2"
                                    style="border: none; cursor: pointer; width: 120px; height: 32px; line-height: 32px; font-size: 14px; font-weight: 700;">검색</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="box box2">
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1"
                            style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px;">
                            <div class="count">총 <strong>{{ $shops->total() }}</strong> 건</div>
                            <a href="{{ route('channel.shop_register') }}" class="btn02">Shop 채널등록</a>
                        </div>

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="150px">
                                    <col width="80px">
                                    <col width="">
                                    <col width="120px">
                                    <col width="80px">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="220px">
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
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody class="textL">
                                    @forelse($shops as $shop)
                                        <tr>
                                            <td class="t_c">{{ $shop->channel_code }}</td>
                                            <td class="t_c">{{ $shop->status == 1 ? '운영' : '중지' }}</td>
                                            <td>
                                                <div style="font-weight: bold; margin-bottom: 5px;">{{ $shop->channel_name }}
                                                </div>
                                                <ul class="tag_list"
                                                    style="display: flex; gap: 5px; list-style: none; padding: 0; flex-wrap: wrap;">
                                                    @php
                                                        $keywords = is_array($shop->keywords)
                                                            ? $shop->keywords
                                                            : (json_decode($shop->keywords ?? '[]', true) ?: []);
                                                    @endphp
                                                    @foreach($keywords as $keyword)
                                                        <li
                                                            style="background-color: #eeeeee; color: #666; padding: 2px 10px; border-radius: 10px; font-size: 11px;">
                                                            #{{ $keyword }}</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td class="t_c">
                                                {{ $shop->is_public == 1 ? '공개' : '비공개' }},
                                                {{ $shop->is_member_only == 1 ? '회원용' : '일반용' }}
                                            </td>
                                            <td class="t_c">00</td> {{-- 상품수 추후 연동 --}}
                                            <td class="t_c">
                                                <div class="pop_btn" data-pop="pop1">
                                                    {{-- QR 샘플 --}}
                                                    <img src="{{ asset('channel_assets/images/sub/qr_sample1.jpg') }}"
                                                        style="max-width: 60px; width:100%;">
                                                </div>
                                            </td>
                                            <td class="t_c">/{{ $shop->channel_code }}</td> {{-- 단축주소 추후 연동 --}}
                                            <td class="t_c">
                                                <a href="#" class="btn02 col2">복사</a>
                                                <a href="{{ route('channel.shop_info', ['id' => $shop->id]) }}"
                                                    class="btn02 col7">관리</a>
                                                <a href="{{ route('channel.shop_info', ['id' => $shop->id]) }}"
                                                    class="btn02 col5">보기</a>
                                                <form action="#" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn02" onclick="return confirm('정말 삭제하시겠습니까?')"
                                                        style="border: none; cursor: pointer; padding: 0 10px;">삭제</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="t_c" style="padding: 50px 0;">등록된 Shop 채널이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- 팝업 -->
                            <div class="popup_bx" data-id="pop1">
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
                                                <a href="#" class="col5 close_btn">닫기</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btm_btn mt10" style="display: flex; justify-content: center;">
                            {{ $shops->appends($params)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(".btn01.arrow").click(function () {
            var thisId = $(this).attr("id");
            $(this).toggleClass("on");
            $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
        });

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
    </script>
@endpush
