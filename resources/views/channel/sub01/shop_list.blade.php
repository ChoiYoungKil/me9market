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
                                        <th class="w160"><span>Shop 채널명</span></th>
                                        <td colspan="3">
                                            <div class="r_btn_w">
                                                <input type="text" value="" required="required">
                                                <a id="arrow1" class="btn01 arrow"><span>상세</span></a>
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
                                        <th class="w160"><span>채널키워드</span></th>
                                        <td colspan="3">
                                            <input type="text" value="" required="required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>채널상태</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_1" checked="">
                                                    <label for="radio1_1">전체</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_2">
                                                    <label for="radio1_2">운영</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_3">
                                                    <label for="radio1_3">중지</label>
                                                </li>
                                            </ul>
                                        </td>
                                        <th class="w160"><span>채널범위</span></th>
                                        <td>
                                            <ul class="chk02">
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_1" checked="">
                                                    <label for="chk1_1">공개</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_2">
                                                    <label for="chk1_2">비공개</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_3">
                                                    <label for="chk1_3">일반용</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="chk1" id="chk1_4">
                                                    <label for="chk1_4">회원용</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10">
                            <a href="#">검색</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="box box2">
                <div class="conbx">
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
                                    <col width="200px">
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
                                            <div class="pop_btn" data-pop="pop1">
                                                <img src="{{ asset('channel_assets/images/sub/qr_sample1.jpg') }}"
                                                    style="max-width: 60px; width:100%;">
                                            </div>
                                        </td>
                                        <td class="t_c">//qcc112ko</td>
                                        <td class="t_c">
                                            <a href="#" class="btn02 col6">복사</a>
                                            <a href="{{ route('channel.shop_info') }}" class="btn02 col3">관리</a>
                                            <a href="#" class="btn02 col4">삭제</a>
                                        </td>
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
                                            <div class="pop_btn" data-pop="pop1">
                                                <img src="{{ asset('channel_assets/images/sub/qr_sample1.jpg') }}"
                                                    style="max-width: 60px; width:100%;">
                                            </div>
                                        </td>
                                        <td class="t_c">//qcc112ko</td>
                                        <td class="t_c">
                                            <a href="#" class="btn02 col6">복사</a>
                                            <a href="{{ route('channel.shop_info') }}" class="btn02 col3">관리</a>
                                            <a href="#" class="btn02 col4">삭제</a>
                                        </td>
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
                                            <div class="pop_btn" data-pop="pop1">
                                                <img src="{{ asset('channel_assets/images/sub/qr_sample1.jpg') }}"
                                                    style="max-width: 60px; width:100%;">
                                            </div>
                                        </td>
                                        <td class="t_c">//qcc112ko</td>
                                        <td class="t_c">
                                            <a href="#" class="btn02 col6">복사</a>
                                            <a href="{{ route('channel.shop_info') }}" class="btn02 col3">관리</a>
                                            <a href="#" class="btn02 col4">삭제</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

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

                        <div class="btm_btn right mt10">
                            <!-- 페이징 -->
                            <div class="page_bx1">
                                <a href="#" class="page_first">first</a>
                                <a href="#" class="page_prev">prev</a>
                                <a href="#" class="num on">1</a>
                                <a href="#" class="num">2</a>
                                <a href="#" class="num">3</a>
                                <a href="#" class="num">4</a>
                                <a href="#" class="num">5</a>
                                <a href="#" class="page_next">next</a>
                                <a href="#" class="page_last">last</a>
                            </div>

                            <a href="{{ route('channel.shop_register') }}">Shop 채널등록</a>
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