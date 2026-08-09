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
                                <li class="on">
                                    <div class="round">
                                        <div>
                                            <p>Step 1</p>
                                            <strong>기본정보</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>-->
                                </li>
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                    <div class="txt">자사상품을 등록할 수 있으며, <br class="pc_show2">Shop 채널에서 판매를 할 수 있는 단계</div>
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
                        <form id="step2Form" enctype="multipart/form-data">
                            @csrf
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
                                                            <td>{{ $memberNumber }}</td>
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
                                            <div class="f_ttl">회원사 정보</div>
                                            <div class="tb01 type2">
                                                <table class="two">
                                                    <colgroup>
                                                        <col width="160px">
                                                        <col width="">
                                                        <col width="160px">
                                                        <col width="">
                                                    </colgroup>
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160"><span>회원사 명</span></th>
                                                            <td colspan="3"><input type="text" name="shop_name" class="w300"
                                                                    required="required"
                                                                    value="{{ $businessDetails->shop_name ?? '' }}"></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>사업자구분</span></th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    @php
                                                                        $btype = $businessDetails->shop_business_type ?? '개인판매';
                                                                    @endphp
                                                                    <li>
                                                                        <input type="radio" name="shop_business_type"
                                                                            id="type2_1_1" value="개인판매" {{ $btype == '개인판매' ? 'checked' : '' }}>
                                                                        <label for="type2_1_1">개인판매</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="shop_business_type"
                                                                            id="type2_1_2" value="개인사업자" {{ $btype == '개인사업자' ? 'checked' : '' }}>
                                                                        <label for="type2_1_2">개인사업자</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="shop_business_type"
                                                                            id="type2_1_3" value="법인사업자" {{ $btype == '법인사업자' ? 'checked' : '' }}>
                                                                        <label for="type2_1_3">법인사업자</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                            <th class="w160"><span>사업자등록번호</span></th>
                                                            <td>
                                                                @php
                                                                    $bln = !empty($businessDetails->business_license_number) ? explode('-', $businessDetails->business_license_number) : ['', '', ''];
                                                                @endphp
                                                                <div class="tel_bx2">
                                                                    <input type="text" name="business_license_1"
                                                                        class="tel1" required="required"
                                                                        value="{{ $bln[0] ?? '' }}">
                                                                    <span>-</span>
                                                                    <input type="text" name="business_license_2"
                                                                        class="tel2" required="required"
                                                                        value="{{ $bln[1] ?? '' }}">
                                                                    <span>-</span>
                                                                    <input type="text" name="business_license_3"
                                                                        class="tel3" required="required"
                                                                        value="{{ $bln[2] ?? '' }}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td colspan="3">
                                                                <div class="tel_bx">
                                                                    @php
                                                                        $mbl = !empty($businessDetails->shop_mobile) ? explode('-', $businessDetails->shop_mobile) : ['', '', ''];
                                                                        $m1 = $mbl[0] ?? '010';
                                                                    @endphp
                                                                    <select name="mobile_1" required="required">
                                                                        <option value="010" {{ $m1 == '010' ? 'selected' : '' }}>010</option>
                                                                        <option value="011" {{ $m1 == '011' ? 'selected' : '' }}>011</option>
                                                                        <option value="016" {{ $m1 == '016' ? 'selected' : '' }}>016</option>
                                                                        <option value="017" {{ $m1 == '017' ? 'selected' : '' }}>017</option>
                                                                        <option value="018" {{ $m1 == '018' ? 'selected' : '' }}>018</option>
                                                                        <option value="019" {{ $m1 == '019' ? 'selected' : '' }}>019</option>
                                                                    </select>
                                                                    <span>-</span>
                                                                    <input type="text" name="mobile_2" class="tel1"
                                                                        required="required" value="{{ $mbl[1] ?? '' }}">
                                                                    <span>-</span>
                                                                    <input type="text" name="mobile_3" class="tel2"
                                                                        required="required" value="{{ $mbl[2] ?? '' }}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>이메일</span></th>
                                                            <td colspan="3">
                                                                @php
                                                                    $eml = !empty($businessDetails->shop_email) ? explode('@', $businessDetails->shop_email) : ['', ''];
                                                                @endphp
                                                                <div class="email_bx">
                                                                    <input type="text" name="email_1" class="email1"
                                                                        required="required" value="{{ $eml[0] ?? '' }}">
                                                                    <span>@</span>
                                                                    <input type="text" name="email_2" class="email2"
                                                                        required="required" value="{{ $eml[1] ?? '' }}">
                                                                    <select class="off">
                                                                        <option value="" selected="">직접입력</option>
                                                                        <option value="naver.com">naver.com</option>
                                                                        <option value="gmail.com">gmail.com</option>
                                                                        <option value="hanmail.net">hanmail.net</option>
                                                                        <option value="nate.com">nate.com</option>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>회원사 주소지</span></th>
                                                            <td colspan="3">
                                                                <div class="addr_bx">
                                                                    <input type="text" name="zipcode" id="zipcode"
                                                                        class="addr1 off" placeholder="우편번호"
                                                                        required="required"
                                                                        value="{{ $businessDetails->shop_pincode ?? '' }}"
                                                                        readonly>
                                                                    <a href="javascript:;" onclick="execDaumPostcode()"
                                                                        class="btn01">우편번호찾기</a>
                                                                    <input type="text" name="address1" id="address1"
                                                                        class="addr2 off" placeholder="주소"
                                                                        required="required"
                                                                        value="{{ $businessDetails->shop_address ?? '' }}"
                                                                        readonly>
                                                                    <input type="text" name="address2" id="address2"
                                                                        class="addr3 off" placeholder="상세주소"
                                                                        required="required"
                                                                        value="{{ $businessDetails->shop_address_detail ?? '' }}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>정산용 계좌번호</span></th>
                                                            <td colspan="3">
                                                                <div class="bank_bx">
                                                                    <select name="bank_name" required="required">
                                                                        <option value="" disabled="" selected="">은행선택
                                                                        </option>
                                                                        @php
                                                                            $banks = ['국민은행', '신한은행', '우리은행', '하나은행', '농협은행', 'IBK기업은행', '수협은행', 'SC제일은행', '씨티은행', '대구은행', '부산은행', '광주은행', '제주은행', '전북은행', '경남은행', '새마을금고', '신협', '우체국', '케이뱅크', '카카오뱅크', '토스뱅크'];
                                                                            $currentBank = $businessDetails->bank_name ?? '';
                                                                        @endphp
                                                                        @foreach($banks as $bank)
                                                                            <option value="{{ $bank }}" {{ $currentBank == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="text" name="account_number" class="bank1"
                                                                        placeholder="계좌번호 (‘-’없이 숫자만 입력)"
                                                                        required="required"
                                                                        value="{{ $businessDetails->bank_account_number ?? '' }}">
                                                                    <input type="text" name="account_holder_name"
                                                                        class="bank2" placeholder="예금주" required="required"
                                                                        value="{{ $businessDetails->bank_account_holder_name ?? '' }}">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>정산용 통장사본</span></th>
                                                            <td colspan="3">
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly"
                                                                        placeholder="입력한 계좌번호와 동일한 통장첨부"
                                                                        value="{{ $businessDetails->bank_copy_image ?? '' }}">
                                                                    <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtn" class="uploadBtn"
                                                                        name="bank_copy_image">
                                                                </div>
                                                                @if(isset($businessDetails->bank_copy_image))
                                                                    <div style="margin-top:10px;">
                                                                        <a href="{{ asset('front/images/bank_copies/' . $businessDetails->bank_copy_image) }}"
                                                                            target="_blank"
                                                                            style="color:blue; text-decoration:underline;">[기존
                                                                            파일 보기]</a>
                                                                    </div>
                                                                @endif
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
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree1" name="agree1" value="1"
                                                                checked>
                                                            <label for="agree1">상품 판매시 약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            약관 내용...
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="btm_btn type2">
                                    <a href="javascript:;" onclick="submitStep2(false);" class="col3">나중에 인증하기 (메인으로 이동)</a>
                                    <a href="javascript:;" onclick="submitStep2(true);" class="col4" style="flex:2;">정보 저장 및 다음 단계 (판매권한 인증)</a>
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

    <!-- 비밀번호 변경 모달 (Step 1과 동기화) -->
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

    <!-- 스크립트 -->
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script>
        var element_layer = document.getElementById('daumPostcodeLayer');
        function closeDaumPostcode() { element_layer.style.display = 'none'; }
        function execDaumPostcode() {
            new daum.Postcode({
                oncomplete: function (data) {
                    var addr = '';
                    if (data.userSelectedType === 'R') { addr = data.roadAddress; } else { addr = data.jibunAddress; }
                    document.getElementById('zipcode').value = data.zonecode;
                    document.getElementById("address1").value = addr;
                    document.getElementById("address2").focus();
                    element_layer.style.display = 'none';
                },
                width: '100%', height: '100%', maxSuggestItems: 5
            }).embed(element_layer);
            element_layer.style.display = 'block';
            initLayerPosition();
        }
        function initLayerPosition() {
            var width = 400; var height = 500; var borderWidth = 1;
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

                $('.fileBox .uploadBtn').on('change', function () {
                    var filename = $(this)[0].files[0].name;
                    $(this).parents('.fileBox').find('.fileName').val(filename);
                });

                $('.email_bx select').change(function () {
                    var val = $(this).val();
                    var target = $('.email_bx .email2');
                    if (val === "") {
                        target.val("").prop('readonly', false).focus();
                    } else {
                        target.val(val).prop('readonly', true);
                    }
                });

                $('#btnChangePassword').click(function (e) {
                    e.preventDefault();
                    $('#passwordChangeModal').css('display', 'flex').show();
                });
                $('#btnCancelPassword').click(function () { $('#passwordChangeModal').hide(); });
                $('#btnSavePassword').click(function () {
                    var current = $('#current_password').val();
                    var new_pw = $('#new_password').val();
                    var confirm_pw = $('#confirm_password').val();
                    if (!current || !new_pw || !confirm_pw) { alert('모든 항목을 입력해주세요.'); return; }
                    if (new_pw !== confirm_pw) { alert('새 비밀번호와 확인 비밀번호가 일치하지 않습니다.'); return; }
                    $.ajax({
                        url: "{{ route('user.update.password') }}",
                        type: "POST",
                        data: { current_password: current, new_password: new_pw, confirm_password: confirm_pw, _token: "{{ csrf_token() }}" },
                        success: function (response) {
                            if (response.type === 'success') {
                                alert('비밀번호가 변경되었습니다.');
                                $('#passwordChangeModal').hide();
                                $('#current_password').val(''); $('#new_password').val(''); $('#confirm_password').val('');
                            } else if (response.type === 'incorrect') {
                                alert('기존 비밀번호가 일치하지 않습니다.');
                            } else {
                                alert('오류가 발생했습니다.');
                            }
                        }
                    });
                });
            });

            function submitStep2(goToStep3) {
                if (!$('#agree1').is(':checked')) {
                    alert('필수 약관에 동의해야 합니다.');
                    return;
                }

                var form = $('#step2Form')[0];
                var formData = new FormData(form);

                $.ajax({
                    url: "{{ route('front.member.register.step2.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            alert('정보가 성공적으로 저장되었습니다.');
                            if (goToStep3) {
                                window.location.href = "{{ route('front.member.register.step3') }}";
                            } else {
                                window.location.href = "/";
                            }
                        } else {
                            alert('오류가 발생했습니다: ' + (response.message || '알 수 없는 오류'));
                        }
                    },
                    error: function (xhr) {
                        alert('서버 통신 오류가 발생했습니다.');
                    }
                });
            }
        </script>
    @endpush
@endsection
