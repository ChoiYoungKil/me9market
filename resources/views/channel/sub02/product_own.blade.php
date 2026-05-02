@extends('layouts.channel')

@section('content')


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
                                            <div class="r_btn_w" style="display: flex; align-items: center; gap: 5px;">
                                                <input type="text" value="" required="required" placeholder="상품명을 입력해 주세요."
                                                    style="flex: 1; height: 34px; border: 1px solid #ddd; padding: 0 10px;">
                                                <a id="arrow1" class="btn01 arrow"
                                                    style="width: 100px; height: 34px; line-height: 32px;"><span>상세검색</span></a>
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
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">대분류</option>
                                                        <option value="1">대분류1</option>
                                                    </select>
                                                </li>
                                                <li>
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">중분류</option>
                                                        <option value="1">중분류1</option>
                                                    </select>
                                                </li>
                                                <li>
                                                    <select required="required">
                                                        <option value="" disabled="" selected="">세분류</option>
                                                        <option value="1">세분류1</option>
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
                                                    <input type="radio" name="radio1" id="radio1_1" checked="">
                                                    <label for="radio1_1">판매</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_2">
                                                    <label for="radio1_2">중지</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio1" id="radio1_3">
                                                    <label for="radio1_3">판매중지예고</label>
                                                </li>
                                            </ul>
                                        </td>
                                        <th class="w160"><span>판매범위</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="radio2" id="radio2_1" checked="">
                                                    <label for="radio2_1">자사상품</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio2" id="radio2_2">
                                                    <label for="radio2_2">공개상품</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="radio2" id="radio2_3">
                                                    <label for="radio2_3">부분공개상품</label>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10" style="margin-bottom: 40px;">
                            <button type="button" class="type2"
                                style="border: none; cursor: pointer; width: 120px; height: 32px; line-height: 32px; font-size: 14px; font-weight: 700;">검색</button>
                        </div>

                        <div class="list_top1"
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: none; padding-bottom: 0;">
                            <div class="left_bx">
                                <div class="count">총 <strong>{{ $products->total() }}</strong> 건</div>
                            </div>
                            <div class="right_bx" style="display: flex; gap: 10px; align-items: center;">
                                <select id="perPageSelect" style="padding: 0 10px; border: 1px solid #ddd; height: 34px;">
                                    <option value="20">20개씩 보기</option>
                                    <option value="40">40개씩 보기</option>
                                    <option value="60">60개씩 보기</option>
                                    <option value="80">80개씩 보기</option>
                                    <option value="100">100개씩 보기</option>
                                </select>
                                <a href="#" class="btn01 col2"
                                    style="height: 34px; line-height: 32px; font-size: 12px; width: 140px; text-align: center; padding: 0;">EXCEL
                                    다운로드</a>
                                <a href="{{ route('channel.product_request') }}" class="btn01 col5"
                                    style="height: 34px; line-height: 32px; font-size: 12px; width: 100px; text-align: center; padding: 0;">상품등록</a>
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
                                        @endphp
                                        <tr>
                                            <td class="t_c">{{ $product->product_code }}</td>
                                            <td class="t_c {{ $statusClass }}">{{ $statusText }}</td>
                                            <td>
                                                <div class="thum01">
                                                    <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                                    <div class="txt_bx">
                                                        <p>{{ $product->category->category_name ?? '카테고리 없음' }}</p>
                                                        <strong>{{ $product->product_name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="t_c">₩ {{ number_format($product->product_price) }}</td>
                                            <td class="t_c">
                                                {{ $product->is_public ? '공개' : '비공개' }}, 
                                                {{ $product->is_partial ? '부분공개' : '전체공개' }}
                                            </td>
                                            <td class="t_c">
                                                <a href="#" class="btn02 col3 pop_btn" data-pop="pop4_1">
                                                    {{ $product->shop_channels_count ?? '0' }}
                                                </a>
                                            </td>
                                            <td class="t_c">
                                                <a href="#" class="btn02 col5 pop_btn" data-pop="pop1_1">판매요청목록</a>
                                            </td>
                                            <td class="t_c">
                                                <a href="#" class="btn02 col7 pop_btn" data-pop="pop2_1">판매중지 예고신청</a>
                                            </td>
                                            <td class="t_c">
                                                <a href="#" class="btn02 col5 pop_btn" data-pop="pop3_1" data-id="{{ $product->id }}">보기</a>
                                                <a href="#" class="btn02 col2" onclick="copyProduct('{{ $product->id }}'); return false;">복사</a>
                                                <a href="{{ route('channel.product.base.edit', $product->id) }}" class="btn02 col4 mt5">수정</a>
                                                <a href="#" class="btn02 mt5" onclick="deleteProduct('{{ $product->id }}'); return false;">삭제</a>
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
                                            <a href="#" class="col5 close_btn">닫기</a>
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
                        <div class="popup_bx" data-id="pop1_1">
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
                                                                <td>
                                                                    <input type="text" value="" required="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>상품분류</span></th>
                                                                <td>
                                                                    <ul class="type_bx w600">
                                                                        <li>
                                                                            <select required="required">
                                                                                <option value="" disabled="" selected="">대분류
                                                                                </option>
                                                                                <option value="1">대분류1</option>
                                                                            </select>
                                                                        </li>
                                                                        <li>
                                                                            <select required="required">
                                                                                <option value="" disabled="" selected="">중분류
                                                                                </option>
                                                                                <option value="1">중분류1</option>
                                                                            </select>
                                                                        </li>
                                                                        <li>
                                                                            <select required="required">
                                                                                <option value="" disabled="" selected="">세분류
                                                                                </option>
                                                                                <option value="1">세분류1</option>
                                                                            </select>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>신청자</span></th>
                                                                <td>
                                                                    <input type="text" value="" required="required">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="btm_btn right mt10">
                                                    <a href="#">검색</a>
                                                </div>
                                            </div>

                                            <div class="con_w">
                                                <div class="list_top1">
                                                    <div class="count">총 <strong>00</strong> 건</div>
                                                </div>

                                                <div class="tb01 ovS">
                                                    <table>
                                                        <colgroup>
                                                            <col width="70px">
                                                            <col width="70px">
                                                            <col width="150">
                                                            <col width="17%">
                                                            <col width="100px">
                                                            <col width="">
                                                            <col width="100px">
                                                        </colgroup>
                                                        <thead>
                                                            <tr>
                                                                <th><input type="checkbox"></th>
                                                                <th>번호</th>
                                                                <th>신청일지</th>
                                                                <th>상품명</th>
                                                                <th>신청자</th>
                                                                <th>신청사유</th>
                                                                <th>상태</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><input type="checkbox"></td>
                                                                <td>00</td>
                                                                <td>0000-00-00 00:00</td>
                                                                <td class="t_l">나이키 운동화</td>
                                                                <td>아무개</td>
                                                                <td class="t_l">신청사유 신청사유 신청사유 신청사유</td>
                                                                <td>미승인</td>
                                                            </tr>
                                                            <tr>
                                                                <td><input type="checkbox"></td>
                                                                <td>00</td>
                                                                <td>0000-00-00 00:00</td>
                                                                <td class="t_l">고추 비료 DDT 3호</td>
                                                                <td>아무개</td>
                                                                <td class="t_l">신청사유 신청사유 <br>신청사유 신청사유 신청사유 </td>
                                                                <td>승인</td>
                                                            </tr>
                                                            <tr>
                                                                <td><input type="checkbox"></td>
                                                                <td>00</td>
                                                                <td>0000-00-00 00:00</td>
                                                                <td class="t_l">나이키 운동화</td>
                                                                <td>홍길동</td>
                                                                <td class="t_l">신청사유</td>
                                                                <td>승인거절</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->

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

                                                    <a href="#" class="col5 close_btn">승인거절</a>
                                                    <a href="#">판매승인</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 판매중지 예고신청 팝업 -->
                        <div class="popup_bx" data-id="pop2_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">판매중지예고 설정</div>
                                        </div>

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
                                                                <th class="w160"><span>판매중지 예고일자 설정</span></th>
                                                                <td>
                                                                    <input class="datepicker w160" type="text"
                                                                        required="required" readonly="">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="#" class="col5 close_btn">취소</a>
                                            <a href="#">확인</a>
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
        $(".btn01.arrow").click(function () {
            var thisId = $(this).attr("id");
            $(this).toggleClass("on");
            $(".arrowbx[data-arrowbx='" + thisId + "']").stop().slideToggle(300);
        });

        /* 팝업 */
        $(".pop_btn").click(function () {
            var popId = $(this).attr("data-pop");
            var productId = $(this).attr("data-id");

            if (popId === 'pop3_1' && productId) {
                $.get("/channel/product/base/detail/" + productId, function(response) {
                    if (response.status) {
                        var p = response.product;
                        var $pop = $(".popup_bx[data-id='pop3_1']");
                        
                        $pop.find(".txt_bx p").text(response.category_path);
                        $pop.find(".txt_bx strong").text(p.product_name);
                        $pop.find(".txt_bx ul li:eq(0)").text("상품코드 : " + p.product_code);
                        $pop.find(".tab_w.tab1 table tr:nth-child(3) td").text(parseFloat(p.product_price).toLocaleString() + " 원");
                        
                        if (p.images && p.images.length > 0) {
                            var mainImgUrl = "/front/images/product_images/small/" + p.images[0].image;
                            $pop.find(".l_bx .img_bx img").attr("src", mainImgUrl);
                        }

                        $pop.stop().fadeIn(300);
                        $pop.scrollTop(0);
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

        function deleteProduct(productId) {
            if (!confirm('정말로 이 상품을 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;

            $.ajax({
                url: "/channel/product/base/delete/" + productId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
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
                    alert('오류가 발생했습니다.');
                }
            });
        }

        function copyProduct(productId) {
            if (!confirm('이 상품을 복사하시겠습니까?')) return;

            $.ajax({
                url: "/channel/product/base/copy/" + productId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
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
                    alert('오류가 발생했습니다.');
                }
            });
        }
        });
    </script>
@endpush