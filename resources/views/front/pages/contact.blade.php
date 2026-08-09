@extends('layouts.frontend')

@section('page_type', 'sub')

@php
    $dep1_id = "04";
    $dep1_tit = "고객센터";
@endphp

@section('content')
@include('layouts.inc.sub_header', [
    'dep1_id' => $dep1_id,
    'dep2_id' => '03',
    'dep1_tit' => $dep1_tit,
    'dep2_tit' => '제휴/문의',
    'dep2_sub' => '미구마켓의 다양한 소식을 안내해 드립니다.'
])

<div id="container">
    <div id="contents">
        <div id="contact">
            <div class="box box1">
                <div class="inner_bx">
                    <div id="board">
                        <form action="{{ route('cs.contact') }}" method="POST">
                            @csrf
                            <div class="write01">
                                <div class="f_bx">
                                    <div class="f_inner">
                                        <div class="f_w">
                                            <div class="ttl">회사명<span class="imp">필수</span></div>
                                            <input type="text" name="company_name" required>
                                        </div>
                                        <div class="f_w">
                                            <div class="ttl">담당자명 / 직책<span class="imp">필수</span></div>
                                            <input type="text" name="manager_name" required>
                                        </div>
                                        <div class="f_w">
                                            <div class="ttl">연락처<span class="imp">필수</span></div>
                                            <div class="tel_bx">
                                                <select name="manager_tel_1" required>
                                                    <option value="010">010</option>
                                                    <option value="02">02</option>
                                                    <option value="031">031</option>
                                                </select>
                                                <span>-</span>
                                                <input type="text" name="manager_tel_2" required maxlength="4">
                                                <span>-</span>
                                                <input type="text" name="manager_tel_3" required maxlength="4">
                                            </div>
                                        </div>
                                        <div class="f_w">
                                            <div class="ttl">이메일<span class="imp">필수</span></div>
                                            <div class="email_bx">
                                                <input type="text" name="manager_email_1" class="email1" required>
                                                <span>@</span>
                                                <input type="text" name="manager_email_2" class="email2" required>
                                                <select onchange="$(this).parent().find('.email2').val(this.value)">
                                                    <option value="">직접입력</option>
                                                    <option value="naver.com">naver.com</option>
                                                    <option value="daum.net">daum.net</option>
                                                    <option value="gmail.com">gmail.com</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="f_inner">
                                        <div class="f_w">
                                            <div class="ttl">문의내용<span class="imp">필수</span></div>
                                            <textarea name="message" required></textarea>
                                        </div>
                                        <div class="f_w">
                                            <div class="ttl">스크립트 방지 태그<span class="imp">필수</span></div>
                                            <div class="spam_bx">
                                                <div class="l_txt">{{ $captcha }}</div>
                                                <input type="text" name="captcha" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="agree_bx">
                                    <div class="ttl">약관동의</div>
                                    <ul class="con_bx">
                                        <li>
                                            <div class="s_txt">
                                                <input type="checkbox" id="agree1" name="agree_terms" required>
                                                <label for="agree1">이용약관 동의 <span class="col2">(필수)</span></label>
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
                                                <input type="checkbox" id="agree2" name="agree_privacy" required>
                                                <label for="agree2">개인정보 수집 및 이용에 관한 안내 <span class="col2">(필수)</span></label>
                                                <div class="btn">전문보기</div>
                                            </div>
                                            <div class="h_txt">
                                                개인정보 수집 및 이용에 관한 안내입니다.<br>
                                                개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다.<br><br>
                                                개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다. 개인정보 수집 및 이용에 관한 안내입니다.<br>
                                                개인정보 수집 및 이용에 관한 안내입니다.
                                            </div>
                                        </li>
                                        <li>
                                            <div class="s_txt">
                                                <input type="checkbox" id="agree3" name="agree_marketing">
                                                <label for="agree3">마케팅활용동의 <span class="col3">(선택)</span></label>
                                                <div class="btn">전문보기</div>
                                            </div>
                                            <div class="h_txt">
                                                마케팅활용동의 내용입니다.<br>
                                                마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다.<br><br>
                                                마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다. 마케팅활용동의 내용입니다.<br>
                                                마케팅활용동의 내용입니다.
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="btm_btn">
                                    <a href="#" onclick="submitContactForm(event)">문의하기</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
      </div><!-- //contents -->
</div><!-- //container -->

<!-- 모달 팝업 HTML -->
<div id="contactModal" class="modal_bg" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal_content" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:30px; border-radius:10px; text-align:center; min-width:300px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <p id="modalMessage" style="font-size:16px; color:#333; margin-bottom:20px; line-height: 1.5; word-break: keep-all; white-space: pre-wrap;"></p>
        <button onclick="closeModal()" style="padding:10px 30px; background:#3470f7; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size: 14px; font-weight: 700;">확인</button>
    </div>
</div>

@push('scripts')
    <script type="text/javascript">
        // 기존 세션 메시지 처리 (새로고침 시 등)
        @if(Session::has('success_message'))
            alert(@json(Session::get('success_message')));
        @endif

        @if($errors->any())
            var errors = @json($errors->all());
            alert(errors.join('\n'));
        @endif

        $(".agree_bx .s_txt .btn").click(function(){
            $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
        });

        // Axios CSRF 토큰 설정
        try {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        } catch(e) {
            console.error('CSRF Token Meta Tag not found');
        }

        function closeModal() {
            $('#contactModal').fadeOut(300);
        }

        function submitContactForm(event) {
            event.preventDefault();
            var form = $(event.target).closest('form')[0];
            
            // HTML5 유효성 검사
            if (!form.reportValidity()) return;

            var formData = new FormData(form);

            axios.post(form.action, formData)
                .then(function (response) {
                    // 성공 시
                    $('#modalMessage').text(response.data.success_message);
                    $('#contactModal').fadeIn(300);
                    form.reset(); // 폼 초기화
                    
                    // 새 캡차 갱신
                    if (response.data.new_captcha) {
                        $('.spam_bx .l_txt').text(response.data.new_captcha);
                    }
                })
                .catch(function (error) {
                    // 실패 시
                    var message = '오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
                    
                    if (error.response && error.response.data && error.response.data.errors) {
                        // 유효성 검사 에러
                        var errors = Object.values(error.response.data.errors).flat();
                        message = errors.join('\n');
                    } else if (error.response && error.response.data && error.response.data.message) {
                        // 기타 서버 에러 메시지
                        message = error.response.data.message;
                    }
                    
                    $('#modalMessage').text(message); // 줄바꿈 처리를 위해 text 대신 html을 쓰거나 pre-wrap 스타일 적용 필요. 여기서는 간단히 text로.
                    $('#contactModal').fadeIn(300);
                });
        }
    </script>
@endpush