@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="step_bx">
                            <ul>
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 1</p>
                                            <strong>기본정보</strong>
                                        </div>
                                    </div>
                                    <div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>
                                </li>
                                <li>
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">자사상품을 등록할 수 있으며, <br class="pc_show2">Shop 채널에서 판매를 할 수 있는 단계</div>-->
                                </li>
                                <li>
                                    <div class="round">
                                        <div>
                                            <p>Step 3</p>
                                            <strong>판매자권한</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>-->
                                </li>
                            </ul>
                        </div>
                        <form id="step1Form" method="POST" action="{{ route('front.member.register.step1.update') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <div class="f_w">
                                            <div class="f_ttl">회원정보입력</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>회원번호</span></th>
                                                            <td>
                                                                <input type="text" name="member_number"
                                                                    value="{{ $memberNumber }}" readonly
                                                                    style="border:none; width:100%; background:transparent; font-weight:bold; color:#333;">
                                                            </td>
                                                            <th class="w160"><span>가입일</span></th>
                                                            <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>아이디</span></th>
                                                            <td>{{ $user->username ?? $user->email }}</td>
                                                            <th class="w160"><span>비밀번호</span></th>
                                                            <td><a href="#" class="btn01" id="btnChangePassword">비밀번호 변경</a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">회원정보입력</div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>이름</span></th>
                                                            <td><input type="text" name="name" value="{{ $user->name }}"
                                                                    required="required"></td>
                                                            <th class="w160"><span>성별</span></th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="gender" id="gender_m"
                                                                            value="Male" {{ (isset($user->gender) && $user->gender == 'Male') ? 'checked' : '' }}>
                                                                        <label for="gender_m">남성</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="gender" id="gender_w"
                                                                            value="Female" {{ (isset($user->gender) && $user->gender == 'Female') ? 'checked' : '' }}>
                                                                        <label for="gender_w">여성</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td colspan="3">
                                                                <div class="r_btn_w w457">
                                                                    <div class="tel_bx">
                                                                        @php
                                                                            $mobileParts = !empty($user->mobile) ? explode('-', $user->mobile) : ['', '', ''];
                                                                            $m1 = $mobileParts[0] ?? '';
                                                                            $m2 = $mobileParts[1] ?? '';
                                                                            $m3 = $mobileParts[2] ?? '';
                                                                        @endphp
                                                                        <select name="mobile_1" required="required">
                                                                            <option value="" disabled=""></option>
                                                                            <option value="010" {{ $m1 == '010' ? 'selected' : '' }}>010</option>
                                                                            <option value="011" {{ $m1 == '011' ? 'selected' : '' }}>011</option>
                                                                            <option value="016" {{ $m1 == '016' ? 'selected' : '' }}>016</option>
                                                                            <option value="017" {{ $m1 == '017' ? 'selected' : '' }}>017</option>
                                                                            <option value="018" {{ $m1 == '018' ? 'selected' : '' }}>018</option>
                                                                            <option value="019" {{ $m1 == '019' ? 'selected' : '' }}>019</option>
                                                                        </select>
                                                                        <span>-</span>
                                                                        <input type="text" class="tel1" name="mobile_2"
                                                                            required="required" value="{{ $m2 }}">
                                                                        <span>-</span>
                                                                        <input type="text" class="tel2" name="mobile_3"
                                                                            required="required" value="{{ $m3 }}">
                                                                    </div>
                                                                    <a href="javascript:alert('본인인증 서비스는 준비중입니다.');"
                                                                        class="btn01 col2">본인인증</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>이메일</span></th>
                                                            <td colspan="3">
                                                                {{ $user->email }}
                                                                <input type="hidden" name="email"
                                                                    value="{{ $user->email }}">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>주소<br class="pc_show">(배송지)</span></th>
                                                            <td colspan="3">
                                                                <div class="addr_bx">
                                                                    <input type="text" name="zipcode" id="zipcode"
                                                                        class="addr1 off" placeholder="우편번호"
                                                                        value="{{ $user->pincode ?? '' }}"
                                                                        required="required" readonly>
                                                                    <a href="javascript:;" onclick="execDaumPostcode()"
                                                                        class="btn01">우편번호찾기</a>
                                                                    <input type="text" name="address1" id="address1"
                                                                        class="addr2 off" placeholder="주소"
                                                                        value="{{ $user->address ?? '' }}"
                                                                        required="required" readonly>
                                                                    <input type="text" name="address2" id="address2"
                                                                        class="addr3 off" placeholder="상세주소"
                                                                        value="{{ $user->city ?? '' }}" required="required">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">약관동의</div>
                                            <div class="agree_bx">

                                                <ul class="con_bx">
                                                    <li class="all_chk">
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree_all">
                                                            <label for="agree_all">전체 약관 동의</label>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree1" checked>
                                                            <label for="agree1">이용약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            약관 내용...
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree2" checked>
                                                            <label for="agree2">개인정보 수집 및 이용에 관한 안내 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            약관 내용...
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree3" checked>
                                                            <label for="agree3">제3자 정보제공 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            약관 내용...
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree4">
                                                            <label for="agree4">마케팅활용동의 <span
                                                                    class="col3">(선택)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            마케팅활용동의 내용입니다.<br>
                                                            마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다.<br><br>
                                                            마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다.<br>
                                                            마케팅활용동의 내용입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree5">
                                                            <label for="agree5">알림정보수신동의 <span
                                                                    class="col3">(선택)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            알림정보수신동의 내용입니다.<br>
                                                            알림정보수신동의 내용입니다. 알림정보수신동의 내용입니다.<br><br>
                                                            알림정보수신동의 내용입니다. 알림정보수신동의 내용입니다. 알림정보수신동의 내용입니다.<br>
                                                            알림정보수신동의 내용입니다.
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="btm_btn type2">
                                    <a href="javascript:;" onclick="submitStep1(false);" class="col3">일반 가입 완료 및 메인으로 이동</a>
                                    <a href="javascript:;" onclick="submitStep1(true);" class="col4" style="flex:2;">기본정보 저장 및 다음 단계 (회원사 등록)</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- //콘텐츠 -->
    </div><!-- //컨테이너 -->

    <style>
        .password-modal-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
    </style>

    <!-- 비밀번호 변경 모달 -->
    <div id="passwordChangeModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:30px; border-radius:10px; width:400px; position:relative;">
            <h3 style="margin-bottom:20px; font-size:18px; font-weight:bold;">비밀번호 변경</h3>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;">기존 비밀번호</label>
                <input type="password" id="current_password" class="password-modal-input">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;">새 비밀번호</label>
                <input type="password" id="new_password" class="password-modal-input">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px;">새 비밀번호 확인</label>
                <input type="password" id="confirm_password" class="password-modal-input">
            </div>
            <div style="text-align:right;">
                <button type="button" id="btnCancelPassword"
                    style="padding:8px 15px; border:1px solid #ddd; background:#fff; cursor:pointer;">취소</button>
                <button type="button" id="btnSavePassword"
                    style="padding:8px 15px; border:none; background:#333; color:#fff; cursor:pointer; margin-left:5px;">변경</button>
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
    <script>
        var element_layer = document.getElementById('daumPostcodeLayer');

        function closeDaumPostcode() {
            element_layer.style.display = 'none';
        }

        function execDaumPostcode() {
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

                    document.getElementById('zipcode').value = data.zonecode;
                    document.getElementById("address1").value = addr;
                    document.getElementById("address2").focus();
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
    </script>

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                $(".agree_bx .s_txt .btn").click(function () {
                    $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
                });

                $('#agree_all').change(function () {
                    var checked = $(this).prop('checked');
                    $('input[type="checkbox"][id^="agree"]').not('#agree_all').prop('checked', checked);
                });
                $('input[type="checkbox"][id^="agree"]').not('#agree_all').change(function () {
                    var total = $('input[type="checkbox"][id^="agree"]').not('#agree_all').length;
                    var checked = $('input[type="checkbox"][id^="agree"]:checked').not('#agree_all').length;
                    $('#agree_all').prop('checked', total === checked);
                });

                $('#btnChangePassword').click(function (e) {
                    e.preventDefault();
                    $('#passwordChangeModal').css('display', 'flex').show();
                });
                $('#btnCancelPassword').click(function () {
                    $('#passwordChangeModal').hide();
                });

                $('#btnSavePassword').click(function () {
                    var current = $('#current_password').val();
                    var new_pw = $('#new_password').val();
                    var confirm_pw = $('#confirm_password').val();

                    if (!current || !new_pw || !confirm_pw) {
                        alert('모든 항목을 입력해주세요.');
                        return;
                    }

                    if (new_pw !== confirm_pw) {
                        alert('새 비밀번호와 확인 비밀번호가 일치하지 않습니다.');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('user.update.password') }}",
                        type: "POST",
                        data: {
                            current_password: current,
                            new_password: new_pw,
                            confirm_password: confirm_pw,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.type === 'success') {
                                alert('비밀번호가 변경되었습니다.');
                                $('#passwordChangeModal').hide();
                                $('#current_password').val('');
                                $('#new_password').val('');
                                $('#confirm_password').val('');
                            } else if (response.type === 'incorrect') {
                                alert('기존 비밀번호가 일치하지 않습니다.');
                            } else {
                                alert('오류가 발생했습니다: ' + (response.message || '알 수 없는 오류'));
                            }
                        },
                        error: function (xhr) {
                            alert('서버 통신 오류가 발생했습니다.');
                        }
                    });
                });
            });

            function submitStep1(goToStep2) {
                if (!$('#agree1').is(':checked') || !$('#agree2').is(':checked') || !$('#agree3').is(':checked')) {
                    alert('필수 약관에 동의해야 합니다.');
                    return;
                }

                var formData = $('#step1Form').serialize();

                $.ajax({
                    url: "{{ route('front.member.register.step1.update') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        if (response.status === 'success') {
                            alert('기본정보가 저장되었습니다.');
                            if (goToStep2) {
                                window.location.href = "{{ route('front.member.register.step2') }}";
                            } else {
                                window.location.href = "/";
                            }
                        } else {
                            alert('오류가 발생했습니다: ' + (response.message || '알 수 없는 오류'));
                        }
                    },
                    error: function (xhr) {
                        var msg = '서버 통신 오류가 발생했습니다.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg += '\n';
                            for (var key in xhr.responseJSON.errors) {
                                msg += xhr.responseJSON.errors[key][0] + '\n';
                            }
                        }
                        alert(msg);
                    }
                });
            }
        </script>
    @endpush
@endsection
