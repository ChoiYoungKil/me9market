@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '1')
@section('dep3_id', '2')

@section('content')
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">배송지 설정</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>정보관리</li>
                            <li>배송지 설정</li>
                        </ul>
                    </div>

                    <div class="ttl01">기본 배송지</div>

                    <form action="{{ route('mypage.delivery.update_default') }}" method="POST">
                        @csrf
                        <div class="tb01 type2">
                            <table class="two">
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>배송지 명</span></th>
                                        <td>
                                            <input type="text" name="name" value="{{ $defaultDelivery->name ?? '' }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>배송지 주소</span></th>
                                        <td>
                                            <div class="addr_bx">
                                                <input type="text" class="addr1 off" name="zipcode" id="zipcode_default"
                                                    value="{{ $defaultDelivery->pincode ?? '' }}" readonly
                                                    placeholder="우편번호">
                                                <a href="javascript:;"
                                                    onclick="execDaumPostcode('zipcode_default', 'address1_default', 'address2_default')"
                                                    class="btn01">우편번호찾기</a>
                                                <input type="text" class="addr2 off" name="address1" id="address1_default"
                                                    value="{{ $defaultDelivery->address ?? '' }}" readonly placeholder="주소">
                                                <input type="text" class="addr3 off" name="address2" id="address2_default"
                                                    value="{{ $defaultDelivery->city ?? '' }}" placeholder="상세주소">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 하단버튼 -->
                        <div class="btm_btn mt10 right">
                            <button type="submit" class="btn01_b"
                                style="padding:10px 20px; border:1px solid #ddd; background:#fff; cursor:pointer;">기본 배송지
                                수정</button>
                        </div>
                    </form>
                </div>

                <div class="box box2">
                    <div class="ttl01">추가 배송지</div>

                    <div class="tb01 ovS size10">
                        <table>
                            <colgroup>
                                <col width="13%">
                                <col width="">
                                <col width="130px">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>배송지 명</th>
                                    <th>주소</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($deliveries as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>({{ $item->pincode }}) {{ $item->address }} {{ $item->city }}</td>
                                        <td>
                                            <a href="javascript:void(0);" class="btn02 col2 pop_btn" data-pop="pop2_1" data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}" data-zipcode="{{ $item->pincode }}"
                                                data-addr1="{{ $item->address }}" data-addr2="{{ $item->city }}"
                                                data-default="{{ $item->is_default }}">수정</a>
                                            <a href="javascript:void(0);" onclick="deleteDelivery({{ $item->id }})"
                                                class="btn02 col7">삭제</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">등록된 추가 배송지가 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="btm_btn right mt10">
                        <!-- 페이징 -->
                        <div class="page_bx1">
                            @if ($deliveries->onFirstPage())
                                <a href="javascript:void(0);" class="page_prev dimmed">prev</a>
                            @else
                                <a href="{{ $deliveries->previousPageUrl() }}" class="page_prev">prev</a>
                            @endif

                            @foreach ($deliveries->getUrlRange(max(1, $deliveries->currentPage() - 2), min($deliveries->lastPage(), $deliveries->currentPage() + 2)) as $page => $url)
                                <a href="{{ $url }}"
                                    class="num {{ $page == $deliveries->currentPage() ? 'on' : '' }}">{{ $page }}</a>
                            @endforeach

                            @if ($deliveries->hasMorePages())
                                <a href="{{ $deliveries->nextPageUrl() }}" class="page_next">next</a>
                            @else
                                <a href="javascript:void(0);" class="page_next dimmed">next</a>
                            @endif
                        </div>

                        <!-- 하단버튼 -->
                        <a href="javascript:void(0);" class="pop_btn" data-pop="pop1_1">배송지 추가하기</a>
                    </div>

                    <!-- 배송지 추가하기 팝업 -->
                    <div class="popup_bx" data-id="pop1_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w640">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">배송지 추가하기</div>
                                    </div>

                                    <form action="{{ route('mypage.delivery.add') }}" method="POST" id="addDeliveryForm">
                                        @csrf
                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160">배송지 명</th>
                                                                <td>
                                                                    <input type="text" name="name" required="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160">주소</th>
                                                                <td>
                                                                    <div class="addr_bx">
                                                                        <input type="text" class="addr1 off" name="zipcode"
                                                                            id="zipcode_add" placeholder="우편번호"
                                                                            required="required" readonly>
                                                                        <a href="javascript:;"
                                                                            onclick="execDaumPostcode('zipcode_add', 'address1_add', 'address2_add')"
                                                                            class="btn01">우편번호찾기</a>
                                                                        <input type="text" class="addr2 off" name="address1"
                                                                            id="address1_add" placeholder="주소"
                                                                            required="required" readonly>
                                                                        <input type="text" class="addr3 off" name="address2"
                                                                            id="address2_add" placeholder="상세주소"
                                                                            required="required">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160">배송지 타입</th>
                                                                <td>
                                                                    <ul class="chk01">
                                                                        <li>
                                                                            <input type="radio" name="is_default" value="1"
                                                                                id="pop1_1_radio1_1" checked="">
                                                                            <label for="pop1_1_radio1_1">기본</label>
                                                                        </li>
                                                                        <li>
                                                                            <input type="radio" name="is_default" value="0"
                                                                                id="pop1_1_radio1_2">
                                                                            <label for="pop1_1_radio1_2">추가</label>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="javascript:;" onclick="submitAddDelivery()">배송지 추가하기</a>
                                            <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 수정 팝업 -->
                    <div class="popup_bx" data-id="pop2_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <form action="{{ route('mypage.delivery.update') }}" method="POST" id="editDeliveryForm">
                                    @csrf
                                    <input type="hidden" name="id" id="edit_id">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">배송지 수정하기</div>
                                        </div>
                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160">배송지 명</th>
                                                                <td><input type="text" name="name" id="edit_name"
                                                                        required="required"></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160">주소</th>
                                                                <td>
                                                                    <div class="addr_bx">
                                                                        <input type="text" class="addr1 off" name="zipcode"
                                                                            id="edit_zipcode" placeholder="우편번호"
                                                                            required="required" readonly>
                                                                        <a href="javascript:;"
                                                                            onclick="execDaumPostcode('edit_zipcode', 'edit_address1', 'edit_address2', this)"
                                                                            class="btn01">우편번호찾기</a>
                                                                        <input type="text" class="addr2 off" name="address1"
                                                                            id="edit_address1" placeholder="주소"
                                                                            required="required" readonly>
                                                                        <input type="text" class="addr3 off" name="address2"
                                                                            id="edit_address2" placeholder="상세주소"
                                                                            required="required">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160">배송지 타입</th>
                                                                <td>
                                                                    <ul class="chk01">
                                                                        <li>
                                                                            <input type="radio" name="is_default" value="1"
                                                                                id="edit_default_1">
                                                                            <label for="edit_default_1">기본</label>
                                                                        </li>
                                                                        <li>
                                                                            <input type="radio" name="is_default" value="0"
                                                                                id="edit_default_0" checked>
                                                                            <label for="edit_default_0">추가</label>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btm_btn mt10">
                                            <a href="javascript:;"
                                                onclick="document.getElementById('editDeliveryForm').submit();">배송지 수정하기</a>
                                            <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 배송지 타입 => 기본 선택 시 팝업 -->
                    <div class="popup_bx" data-id="pop3_1">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w560">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="conbx">
                                        <div class="con_w">
                                            <div class="imp_bx01 bN">
                                                <div class="txt2 mt0">기본 배송지로 변경하시겠습니까? <br>기존 기본 배송지는 추가 배송지로 변경됩니다.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btm_btn mt10">
                                        <a href="javascript:;" onclick="document.getElementById('addDeliveryForm').submit();">확인</a>
                                        <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 다음 우편번호 레이어 -->
    <div id="daumPostcodeLayer"
        style="display:none;position:fixed;overflow:hidden;z-index:10000;-webkit-overflow-scrolling:touch;">
        <img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnCloseLayer"
            style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:1" onclick="closeDaumPostcode()"
            alt="닫기 버튼">
    </div>

    <!-- 다음 우편번호 스크립트 -->
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script type="text/javascript">
        var element_layer = document.getElementById('daumPostcodeLayer');
        var hasDefaultDelivery = {{ $defaultDelivery ? 'true' : 'false' }};

        function submitAddDelivery() {
            var form = document.getElementById('addDeliveryForm');
            var isDefaultInput = form.querySelector('input[name="is_default"][value="1"]');

            if (isDefaultInput && isDefaultInput.checked && hasDefaultDelivery) {
                if (confirm("기본 배송지로 변경하시겠습니까? 기존 기본 배송지는 추가 배송지로 변경됩니다.")) {
                    form.submit();
                }
            } else {
                form.submit();
            }
        }

        function closeDaumPostcode() {
            element_layer.style.display = 'none';
        }

        // target_zipcode, target_addr1, target_addr2 인자를 받도록 수정
        function execDaumPostcode(zipcodeId, addr1Id, addr2Id) {
            new daum.Postcode({
                oncomplete: function (data) {
                    var addr = '';
                    var extraAddr = '';

                    if (data.userSelectedType === 'R') {
                        addr = data.roadAddress;
                    } else {
                        addr = data.jibunAddress;
                    }

                    if (data.userSelectedType === 'R') {
                        if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                            extraAddr += data.bname;
                        }
                        if (data.buildingName !== '' && data.apartment === 'Y') {
                            extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                        }
                    }

                    // 전달받은 ID를 사용하여 값 설정
                    document.getElementById(zipcodeId).value = data.zonecode;
                    document.getElementById(addr1Id).value = addr;
                    document.getElementById(addr2Id).focus();

                    element_layer.style.display = 'none';
                },
                width: '100%',
                height: '100%',
                maxSuggestItems: 5
            }).embed(element_layer);

            element_layer.style.display = 'block';
            initLayerPosition();
        }

        function initLayerPosition() {
            var width = 400;
            var height = 500;
            var borderWidth = 1;

            element_layer.style.width = width + 'px';
            element_layer.style.height = height + 'px';
            element_layer.style.border = borderWidth + 'px solid #333';
            element_layer.style.backgroundColor = '#fff';

            element_layer.style.left = (((window.innerWidth || document.documentElement.clientWidth) - width) / 2) + 'px';
            element_layer.style.top = (((window.innerHeight || document.documentElement.clientHeight) - height) / 2) + 'px';
        }

        /* 팝업 */
        $(".pop_btn").click(function () {
            var popId = $(this).attr("data-pop");

            // 수정 팝업인 경우 데이터 채우기
            if (popId === 'pop2_1') {
                $("#edit_id").val($(this).data('id'));
                $("#edit_name").val($(this).data('name'));
                $("#edit_zipcode").val($(this).data('zipcode'));
                $("#edit_address1").val($(this).data('addr1'));
                $("#edit_address2").val($(this).data('addr2'));

                var isDefault = $(this).data('default');
                if (isDefault == 1) {
                    $("#edit_default_1").prop("checked", true);
                } else {
                    $("#edit_default_0").prop("checked", true);
                }
            }

            $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
            $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

            return false;
        });

        // 배송지 삭제 함수
        function deleteDelivery(id) {
            if (confirm("정말로 삭제하시겠습니까?")) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("mypage.delivery.delete") }}';

                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
        $(".popup_bx .close_btn").click(function () {
            $(this).parents(".popup_bx").stop().fadeOut(300);

            return false;
        });
    </script>
@endsection
