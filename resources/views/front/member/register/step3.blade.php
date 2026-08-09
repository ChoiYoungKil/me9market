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
                                <li class="on">
                                    <div class="round">
                                        <div>
                                            <p>Step 2</p>
                                            <strong>회원사권한</strong>
                                        </div>
                                    </div>
                                    <!--<div class="txt">자사상품을 등록할 수 있으며, <br class="pc_show2">Shop 채널에서 판매를 할 수 있는 단계</div>-->
                                </li>
                                <li class="ing">
                                    <div class="round">
                                        <div>
                                            <p>Step 3</p>
                                            <strong>판매자권한</strong>
                                        </div>
                                    </div>
                                    <div class="txt">플랫폼(사이트) 및 Shop 채널을 <br class="pc_show2">이용할 수 있는 단계</div>
                                </li>
                            </ul>
                        </div>
                        <form id="step3Form" enctype="multipart/form-data">
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
                                                            <td><a href="javascript:void(0);" class="btn01" id="btnChangePassword">비밀번호 변경</a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 공유 및
                                                    공동구매 상품을 판매하기 위해서는 판매인증이 필요합니다. )</span></div>
                                            <div class="tb01 type2">
                                                <table>
                                                    <tbody>
                                                        <tr>
                                                            <th class="w160">판매인증</th>
                                                            <td>
                                                                @if(isset($vendorDetails) && $vendorDetails->status == 1)
                                                                    <strong class="fcol1" style="color:green;">인증완료</strong>
                                                                @else
                                                                    <strong class="fcol3">미인증</strong>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">사업자등록증</th>
                                                            <td>
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly"
                                                                        placeholder="사업자등록증 이미지 업로드"
                                                                        value="{{ $businessDetails->address_proof_image ?? '' }}">
                                                                    <label for="uploadBtnLicense"
                                                                        class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtnLicense"
                                                                        class="uploadBtn" name="address_proof_image">
                                                                </div>
                                                                @if(isset($businessDetails->address_proof_image))
                                                                    <div style="margin-top:5px;">
                                                                        <a href="{{ asset('front/images/bank_copies/' . $businessDetails->address_proof_image) }}"
                                                                            target="_blank"
                                                                            style="color:blue; text-decoration:underline; font-size:12px;">[기존
                                                                            등록증 보기]</a>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                                            <td>
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
                                                            <th class="w160">사업자 명의 통장사본</th>
                                                            <td>
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly"
                                                                        placeholder="입력한 계좌번호와 동일한 통장첨부"
                                                                        value="{{ $businessDetails->bank_copy_image ?? '' }}">
                                                                    <label for="uploadBtnBankbook"
                                                                        class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtnBankbook"
                                                                        class="uploadBtn" name="bank_copy_image">
                                                                </div>
                                                                @if(isset($businessDetails->bank_copy_image))
                                                                    <div style="margin-top:5px;">
                                                                        <a href="{{ asset('front/images/bank_copies/' . $businessDetails->bank_copy_image) }}"
                                                                            target="_blank"
                                                                            style="color:blue; text-decoration:underline; font-size:12px;">[기존
                                                                            통장사본 보기]</a>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                        </div>
                                        <div class="f_w">
                                            <div class="f_ttl">약관동의</div>
                                            <div class="agree_bx">
                                                <ul class="con_bx">
                                                    <li>
                                                        <div class="s_txt">
                                                            <input type="checkbox" id="agree1" name="agree1" value="1"
                                                                checked>
                                                            <label for="agree1">판매권한 약관 동의 <span
                                                                    class="col2">(필수)</span></label>
                                                            <div class="btn">전문보기</div>
                                                        </div>
                                                        <div class="h_txt">
                                                            판매권한 약관 동의 내용입니다.
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="btm_btn type2" style="justify-content:center; display:flex;">
                                    <a href="javascript:;" onclick="submitStep3(true);" class="col4" style="flex:1;">판매 권한 심사 요청하기 (가입 최종 완료)</a>
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

    <!-- 비밀번호 변경 모달 (Step 1, 2와 동기화) -->
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

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                // 전체 텍스트 보기 토글
                $(".agree_bx .s_txt .btn").click(function () {
                    $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
                });

                // 파일 업로드 미리보기 텍스트 로직
                $('.fileBox .uploadBtn').on('change', function () {
                    var filename = $(this)[0].files[0].name;
                    $(this).parents('.fileBox').find('.fileName').val(filename);
                });

                // 비밀번호 모달 로직
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

            // Step 3 폼 제출
            function submitStep3(isCertification) {
                if (!$('#agree1').is(':checked')) {
                    alert('필수 약관에 동의해야 합니다.');
                    return;
                }

                var form = $('#step3Form')[0];
                var formData = new FormData(form);

                $.ajax({
                    url: "{{ route('front.member.register.step3.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            if (isCertification) {
                                alert('인증 요청이 성공적으로 접수되었습니다.\n심사 후 판매 권한이 부여됩니다.');
                            } else {
                                alert('저장되었습니다.');
                            }
                            window.location.href = "{{ route('mypage.dashboard') }}";
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
