@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="join">
                <div class="box box1">
                    <div class="inner_bx">
                        <div class="top_ttl">회원가입</div>
                        <form name="joinForm" method="POST" action="{{ route('front.member.register.submit') }}">
                            @csrf
                            <input type="hidden" name="register_type" value="{{ $type ?? 'general' }}">
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <!-- Social Registration Block -->
                                        @if(($type ?? 'general') === 'social')
                                            <div class="f_w">
                                                <div class="f_ttl">소셜회원 가입정보</div>
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <colgroup>
                                                            <col width="160px">
                                                            <col>
                                                            <col width="160px">
                                                            <col>
                                                        </colgroup>
                                                        <tbody>
                                                            <tr>
                                                                <th class="w160"><span>가입경로</span></th>
                                                                <td>{{ $socialData['provider'] ?? 'Social' }}</td>
                                                                <th class="w160"><span>이메일</span></th>
                                                                <td>
                                                                    {{ $socialData['email'] ?? '-' }}
                                                                    <input type="hidden" name="email"
                                                                        value="{{ $socialData['email'] ?? '' }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>이름</span></th>
                                                                <td colspan="3">
                                                                    <input type="text" name="name"
                                                                        value="{{ $socialData['name'] ?? '' }}" required
                                                                        placeholder="이름을 입력하세요">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>휴대폰번호</span></th>
                                                                <td colspan="3">
                                                                    <input type="text" name="mobile" required
                                                                        placeholder="- 없이 입력하세요" maxlength="11">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- General Registration Block -->
                                        @if(($type ?? 'general') === 'general')
                                            <div class="f_w">
                                                <div class="f_ttl">회원가입 정보입력</div>
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
                                                                <th class="w160"><span>아이디</span></th>
                                                                <td colspan="3">
                                                                    <div class="r_btn_w w430">
                                                                        <input type="text" name="username" id="username"
                                                                            required placeholder="아이디를 입력하세요">
                                                                        <a href="javascript:void(0);" onclick="checkId();"
                                                                            class="btn01 col2">중복확인</a>
                                                                    </div>
                                                                    <span class="imp_txt" id="id_check_msg"
                                                                        style="display:none;"></span>
                                                                </td>
                                                            </tr>
                                                            <!-- Name and Mobile removed -->
                                                            <tr>
                                                                <th class="w160"><span>이메일</span></th>
                                                                <td colspan="3">
                                                                    <div class="email_bx">
                                                                        <input type="text" class="email1" name="email_prefix"
                                                                            required="required">
                                                                        <span>@</span>
                                                                        <input type="text" class="email2" name="email_domain"
                                                                            required="required">
                                                                        <select class="off" name="email_domain_select">
                                                                            <option value="" selected="">직접입력</option>
                                                                            <option value="naver.com">naver.com</option>
                                                                            <option value="gmail.com">gmail.com</option>
                                                                            <option value="daum.net">daum.net</option>
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>비밀번호</span></th>
                                                                <td colspan="3">
                                                                    <input class="w300" type="password" name="password"
                                                                        id="password" required="required">
                                                                    <p class="txt_guide"
                                                                        style="margin-top:5px; color:#888; font-size:12px;">
                                                                        ※ 비밀번호는 영문 대문자, 소문자, 특수문자(!@#$%^&*)를 포함하여 6자 이상이어야 합니다.
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>비밀번호확인</span></th>
                                                                <td colspan="3">
                                                                    <input class="w300" type="password"
                                                                        name="password_confirmation" id="password_confirmation"
                                                                        required="required">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
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
                                                            <input type="checkbox" id="agree1" name="agree_terms">
                                                            <label for="agree1">이용약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            이용약관 내용입니다.<br>
                                                            이용약관 내용입니다. 이용약관 내용입니다.<br><br>
                                                            이용약관 내용입니다. 이용약관 내용입니다. 이용약관 내용입니다.<br>
                                                            이용약관 내용입니다.<br>
                                                            이용약관 내용입니다.<br>
                                                            이용약관 내용입니다. 이용약관 내용입니다.<br><br>
                                                            이용약관 내용입니다. 이용약관 내용입니다. 이용약관 내용입니다.<br>
                                                            이용약관 내용입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree2" name="agree_privacy">
                                                            <label for="agree2">개인정보 수집 및 이용에 관한 안내 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            개인정보 수집 및 이용에 관한 안내입니다.<br>
                                                            개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다.<br><br>
                                                            개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한
                                                            안내입니다.<br>
                                                            개인정보 수집 및 이용에 관한 안내입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree3" name="agree_third_party">
                                                            <label for="agree3">제3자 정보제공 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            제3자 정보제공 동의에 관한 안내입니다.<br>
                                                            제3자 정보제공 동의에 관한 안내입니다. 제3자 정보제공 동의에 관한 안내입니다.<br><br>
                                                            제3자 정보제공 동의에 관한 안내입니다. 제3자 정보제공 동의에 관한 안내입니다. 제3자 정보제공 동의에 관한
                                                            안내입니다.<br>
                                                            제3자 정보제공 동의에 관한 안내입니다.
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree4" name="agree_marketing">
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
                                                            <input type="checkbox" id="agree5" name="agree_notification">
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
                                    <a href="javascript:;" onclick="checkForm();" class="col2">가입완료</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                // View Full Text Toggle
                $(".agree_bx .s_txt .btn").click(function () {
                    $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
                });

                // Agree All Logic
                $('#agree_all').change(function () {
                    var checked = $(this).prop('checked');
                    $('input[type="checkbox"][id^="agree"]').not('#agree_all').prop('checked', checked);
                });
                $('input[type="checkbox"][id^="agree"]').not('#agree_all').change(function () {
                    var total = $('input[type="checkbox"][id^="agree"]').not('#agree_all').length;
                    var checked = $('input[type="checkbox"][id^="agree"]:checked').not('#agree_all').length;
                    $('#agree_all').prop('checked', total === checked);
                });

                // Email Domain Select
                $('select[name="email_domain_select"]').change(function () {
                    var val = $(this).val();
                    var target = $('input[name="email_domain"]');
                    if (val === "") {
                        target.val("").prop('readonly', false).focus();
                    } else {
                        target.val(val).prop('readonly', true);
                    }
                });
            });

            function checkId() {
                var username = $('#username').val();
                if (username == "") {
                    alert('아이디를 입력해주세요.');
                    return;
                }
                var idRegExp = /^[a-zA-Z0-9]+$/;
                if (!idRegExp.test(username)) {
                    alert('아이디는 영문 대소문자와 숫자만 사용 가능합니다.');
                    return;
                }
                $.ajax({
                    url: "{{ route('front.member.check_id') }}",
                    type: "POST",
                    data: { username: username, _token: "{{ csrf_token() }}" },
                    success: function (resp) {
                        if (resp.status == 'available') {
                            $('#id_check_msg').text(resp.message).css('color', 'blue').show();
                        } else {
                            $('#id_check_msg').text(resp.message).css('color', 'red').show();
                        }
                    },
                    error: function () {
                        alert('오류가 발생했습니다.');
                    }
                });
            }

            function checkForm() {
                var type = $('input[name="register_type"]').val();

                // 1. ID Check (General Member Only)
                if (type === 'general') {
                    var username = $('#username').val();
                    if (username == "") {
                        alert('아이디를 입력해주세요.');
                        $('#username').focus();
                        return;
                    }
                    var idRegExp = /^[a-zA-Z0-9]+$/;
                    if (!idRegExp.test(username)) {
                        alert('아이디는 영문 대소문자와 숫자만 사용 가능합니다.');
                        $('#username').focus();
                        return;
                    }
                }

                // 2. Email Check (General Member)
                if (type === 'general') {
                    if ($('input[name="email_prefix"]').val() == "" || $('input[name="email_domain"]').val() == "") {
                        alert('이메일을 입력해주세요.');
                        if ($('input[name="email_prefix"]').val() == "") $('input[name="email_prefix"]').focus();
                        else $('input[name="email_domain"]').focus();
                        return;
                    }
                }

                // 3. Password Validation (General Member Only)
                if (type === 'general') {
                    var pwd = $('#password').val();
                    var pwdConfirm = $('#password_confirmation').val();

                    if (pwd == '') {
                        alert('비밀번호를 입력해주세요.');
                        $('#password').focus();
                        return;
                    }

                    // Password Rules: Upper, Lower, Special, Min 6
                    var regExp = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*])[a-zA-Z\d!@#$%^&*]{6,}$/;
                    if (!regExp.test(pwd)) {
                        alert('비밀번호는 영문 대문자, 소문자, 특수문자(!@#$%^&*)를 포함하여 6자 이상이어야 합니다.');
                        $('#password').focus();
                        return;
                    }

                    if (pwd != pwdConfirm) {
                        alert('비밀번호가 일치하지 않습니다.');
                        $('#password_confirmation').focus();
                        return;
                    }
                }

                // 4. Mandatory Terms Check
                if (!$('#agree1').is(':checked')) {
                    alert('이용약관에 동의해주세요.');
                    $('#agree1').focus();
                    return;
                }
                if (!$('#agree2').is(':checked')) {
                    alert('개인정보 수집 및 이용에 동의해주세요.');
                    $('#agree2').focus();
                    return;
                }
                if (!$('#agree3').is(':checked')) {
                    alert('제3자 정보제공에 동의해주세요.');
                    $('#agree3').focus();
                    return;
                }

                // 5. Axios Submission
                var formData = new FormData(document.joinForm);

                axios.post("{{ route('front.member.register.submit') }}", formData)
                    .then(function (response) {
                        if (response.data.status === 'success') {
                            alert(response.data.message);
                            location.href = response.data.redirect_url;
                        }
                    })
                    .catch(function (error) {
                        if (error.response) {
                            // Server responded with a status other than 2xx
                            if (error.response.status === 422) {
                                var errors = error.response.data.errors;
                                var resMsg = '';
                                for (var key in errors) {
                                    resMsg += errors[key][0] + '\n';
                                }
                                alert(resMsg);
                            } else {
                                alert('오류가 발생했습니다. 다시 시도해주세요.');
                            }
                        } else {
                            // Request setup error
                            alert('서버 통신 중 오류가 발생했습니다.');
                        }
                    });
            }
        </script>
    @endpush
@endsection