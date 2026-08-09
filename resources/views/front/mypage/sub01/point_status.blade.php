@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '3')
@section('dep3_id', '1')

@section('content')
    <style>
        .btn_black {
            background-color: #000 !important;
            border-color: #000 !important;
            color: #fff !important;
        }

        .w40 {
            width: 40px;
        }
    </style>
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">포인트 현황</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>포인트관리</li>
                            <li>포인트 현황</li>
                        </ul>
                    </div>

                    <div class="conbx">
                        <div class="con_w">
                            <!-- 포인트 요약 -->
                            <div class="tb01 type2">
                                <table class="two">
                                    <tbody>
                                        <tr>
                                            <th class="w160" style="background-color: #f8f8f8; text-align: center;">채널 포인트
                                            </th>
                                            <td
                                                style="text-align: right; padding-right: 20px; font-weight: bold; border-right: 1px solid #eeeeee;">
                                                {{ number_format($channelPoints) }} P
                                            </td>
                                            <th class="w160" style="background-color: #f8f8f8; text-align: center;">Me9 포인트
                                            </th>
                                            <td style="text-align: right; padding-right: 20px; font-weight: bold;">
                                                {{ number_format($me9Points) }} P
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w mt40">
                            <!-- 포인트 리스트 -->
                            <div class="tb01 type2">
                                <table>
                                    <colgroup>
                                        <col width="80px">
                                        <col width="">
                                        <col width="120px">
                                        <col width="180px">
                                        <col width="180px">
                                        <col width="140px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>판매자 정보</th>
                                            <th>상태</th>
                                            <th>사용 가능 포인트</th>
                                            <th>사용 가능 Shop 채널</th>
                                            <th>포인트 전환</th>
                                        </tr>
                                    </thead>
                                    <tbody class="t_c">
                                        @foreach($pointsList as $point)
                                            <tr>
                                                <td>{{ $point['no'] }}</td>
                                                <td class="t_l pl20">
                                                    {{ $point['seller_name'] }} ({{ $point['channel_code'] }})
                                                </td>
                                                <td>
                                                    <span class="{{ $point['status'] == '중지' ? 'fcol3' : '' }}">
                                                        {{ $point['status'] }}
                                                    </span>
                                                </td>
                                                <td class="t_r pr20 text_bold">
                                                    {{ number_format($point['available_points']) }} P
                                                </td>
                                                <td>
                                                    @if($point['has_shop'])
                                                        <a href="javascript:;" class="pop_btn btn01 col5"
                                                            data-pop="pop_shop_channels">보기</a>
                                                    @else
                                                        Shop 채널 없음
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($point['can_convert'])
                                                        <a href="javascript:;" class="pop_btn btn01 col2" data-pop="pop_convert"
                                                            data-points="{{ $point['available_points'] }}"
                                                            data-shop-channel-id="{{ $point['shop_channel_id'] }}">전환</a>
                                                    @else
                                                        <span class="fcol6">전환 불가</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- 페이징 -->
                            <div class="page_bx1 text-center mt30">
                                <a href="javascript:void(0);" class="page_prev dimmed">prev</a>
                                <a href="#" class="num on">1</a>
                                <a href="#" class="num">2</a>
                                <a href="#" class="num">3</a>
                                <a href="#" class="num">4</a>
                                <a href="#" class="num">5</a>
                                <a href="javascript:void(0);" class="page_next dimmed">next</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->

    <!-- 포인트 전환 팝업 -->
    <div class="popup_bx" data-id="pop_convert">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con w560">
                    <div class="close_btn close1">닫기</div>
                    <div class="page_info type2">
                        <div class="ttl">Confirm</div>
                    </div>
                    <div class="conbx">
                        <div class="con_w" style="text-align: center; padding: 40px 0;">
                            <div class="txt2" style="font-size: 18px; line-height: 1.5;">
                                <span id="convert_points_display">0</span> P 를 Me9 포인트로 <br>전환하시겠습니까?
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <form method="POST" action="{{ route('mypage.point.convert') }}">
                        @csrf
                        <input type="hidden" name="shop_channel_id" id="convert_shop_channel_id" value="">
                        <div class="btm_btn mt10" style="display: flex; justify-content: center; gap: 10px;">
                            <button type="submit" style="max-width: 120px; background-color: #444; color: #fff; border: 0;">확인</button>
                            <a href="#" class="col5 close_btn" style="max-width: 120px; background-color: #aaa;">취소</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop 채널 목록 팝업 -->
    <div class="popup_bx" data-id="pop_shop_channels">
        <div class="pop_w">
            <div class="pop_inner">
                <div class="pop_con w800">
                    <div class="close_btn close1">닫기</div>
                    <div class="page_info type2">
                        <div class="ttl">○ Shop 채널 목록</div>
                    </div>
                    <div class="conbx">
                        <div class="con_w">
                            <div class="tb01 type2">
                                <table>
                                    <colgroup>
                                        <col style="width: 80px;">
                                        <col>
                                        <col style="width: 150px;">
                                        <col style="width: 150px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Shop 채널명</th>
                                            <th>QR 코드</th>
                                            <th>바로가기</th>
                                        </tr>
                                    </thead>
                                    <tbody class="t_c">
                                        @forelse($shopChannels as $channel)
                                            <tr>
                                                <td>{{ $channel['no'] }}</td>
                                                <td class="t_l pl20">
                                                    <div class="bold">{{ $channel['name'] }}</div>
                                                    <div class="fs12 fcol6">{{ $channel['info'] }}</div>
                                                </td>
                                                <td>
                                                    <img src="{{ $channel['qr'] }}" alt="QR" class="w40"
                                                        style="cursor: pointer;" onclick="window.open(this.src)">
                                                </td>
                                                <td>
                                                    <a href="{{ $channel['url'] }}" class="btn01"
                                                        style="background-color: #ffb400; border-color: #ffb400; color: #fff;">바로가기</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="no_data" style="padding: 60px 0;">방문한 Shop 채널이 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 하단버튼 -->
                    <div class="btm_btn mt40">
                        <a href="javascript:;" class="close_btn btn01 btn_black">창닫기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $(".pop_btn").click(function () {
                var popId = $(this).attr("data-pop");
                var points = $(this).attr("data-points");
                var shopChannelId = $(this).attr("data-shop-channel-id");

                $("#convert_points_display").text(points);
                $("#convert_shop_channel_id").val(shopChannelId || "");

                $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
                $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

                return false;
            });

            $(".popup_bx .close_btn").click(function () {
                $(this).parents(".popup_bx").stop().fadeOut(300);
                return false;
            });
        });
    </script>
@endsection
