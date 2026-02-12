{{-- 이 페이지는 헤더의 드롭다운 메뉴에 있는 내 계정 탭에서 액세스됩니다 (front/layout/header.blade.php). Front/UserController.php의 userAccount() 메소드
확인 --}}
@extends('front.layout.layout')


@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>My Account</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="index.html">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="account.html">Account</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->
    <!-- Account-Page -->
    <div class="page-account u-s-p-t-80">
        <div class="container">



            {{-- 유효성 검사 오류 표시: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors 및
            https://laravel.com/docs/9.x/blade#validation-errors --}}
            {{-- 세션에 항목이 존재하는지 확인 (has() 메서드 사용):
            https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
            {{-- 관리자 비밀번호 업데이트 성공 시 부트스트랩 성공 메시지: --}}
            {{-- Displaying Success Message --}}
            @if (Session::has('success_message')) <!-- Check userRegister() method in Front/UserController.php -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success:</strong> {{ Session::get('success_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            {{-- Displaying Error Messages --}}
            @if (Session::has('error_message')) <!-- Check userRegister() method in Front/UserController.php -->
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ Session::get('error_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            {{-- Displaying Error Messages --}}
            @if ($errors->any()) <!-- Check userRegister() method in Front/UserController.php -->
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> @php echo implode('', $errors->all('<div>:message</div>')); @endphp
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif



            <div class="row">
                <!-- Update User Account Contact Details -->
                <div class="col-lg-6">
                    <div class="login-wrapper">
                        <h2 class="account-h2 u-s-m-b-20" style="font-size: 18px">Update Contact Details</h2>




                        {{-- 참고: AJAX 호출 응답(백엔드)에서 폼의 유효성 검사 오류 메시지(Laravel의 유효성 검사 오류 메시지)를 표시하기 위해 모든 <input> 필드 뒤에 <p>
                            태그를 생성합니다. jQuery 루프가 작동하려면
                        <p> ID 패턴이 delivery-x(예: delivery-mobile, delivery-email...)와 같아야 합니다. 그리고 x는 'name' HTML 속성과 동일해야
                            합니다(예: name='mobile' HTML 속성이 있는 <input>에는 id="delivery-mobile" HTML 속성이 있는
                        <p>가 있어야 함). 그래야 유효성 검사 오류 배열이 백엔드/서버에서 AJAX 요청으로 응답으로 전송될 때(컨트롤러 내부의 메소드에서 $validator->messages()
                            확인), jQuery $.each() 루프로 편리하게/쉽게 처리할 수 있습니다. front/js/custom.js 확인 --}}
                            {{--
                        <p id="account-error" style="color: red"></p> --}} {{-- 유효성 검사는 통과했지만 사용자가 제공한 로그인 자격 증명이 틀린 경우,
                        jQuery가 일반적인 '잘못된 자격 증명!' 메시지를 표시하는 데 사용됩니다. 또는 사용자 계정이 비활성/비활성화된 경우 메시지를 표시하는 데 사용됩니다. --}}
                        <p id="account-error"></p> {{-- 유효성 검사는 통과했지만 사용자가 제공한 로그인 자격 증명이 틀린 경우, jQuery가 일반적인 '잘못된 자격 증명!'
                        메시지를 표시하는 데 사용됩니다. 또는 사용자 계정이 비활성/비활성화된 경우 메시지를 표시하는 데 사용됩니다. --}}


                        {{-- jQuery를 사용한 세부 정보 업데이트 성공 메시지. front/js/custom.js의 $('#accountForm').submit(); 확인 --}}
                        {{-- <p id="account-success" style="color: green"></p> --}}
                        <p id="account-success"></p>


                        <form id="accountForm" action="javascript:;" method="post"> {{-- AJAX 호출을 사용하여 제출할 것이므로 'action'
                            HTML 속성을 비활성화(action="javascript:;" 사용)해야 합니다. front/js/custom.js 확인 --}}
                            @csrf {{-- Preventing CSRF Requests: https://laravel.com/docs/9.x/csrf#preventing-csrf-requests
                            --}}


                            <div class="u-s-m-b-30">
                                <label for="user-email">Email
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" value="{{ \Illuminate\Support\Facades\Auth::user()->email }}"
                                    style="background-color: #e9e9e9" readonly disabled> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-email" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성
                                검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}} {{-- jQuery 루프가 작동하려면 패턴이
                                register-x(예: register-mobile, register-email...)와 같아야 합니다. --}}
                                <p id="account-email"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를
                                표시하기 위해 jQuery에서 사용됩니다. --}} {{-- jQuery 루프가 작동하려면 패턴이 register-x(예: register-mobile,
                                register-email...)와 같아야 합니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-name">Name
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" type="text" id="user-name" name="name"
                                    value="{{ \Illuminate\Support\Facades\Auth::user()->name }}"> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-name" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사
                                오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-name"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를
                                표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-address">Address
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" type="text" id="user-address" name="address"
                                    value="{{ \Illuminate\Support\Facades\Auth::user()->address }}"> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-address" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성
                                검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-address"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류
                                메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-city">City
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" type="text" id="user-city" name="city"
                                    value="{{ \Illuminate\Support\Facades\Auth::user()->city }}"> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-city" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사
                                오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-city"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를
                                표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-state">State
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" type="text" id="user-state" name="state"
                                    value="{{ \Illuminate\Support\Facades\Auth::user()->state }}"> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-state" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성
                                검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-state"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를
                                표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-country">Country
                                    <span class="astk">*</span>
                                </label>
                                <select class="text-field" id="user-country" name="country" style="color: #495057">
                                    <option value="">Select Country</option>

                                    @foreach ($countries as $country) {{-- $countries는 compact() 메소드를 사용하여 UserController에서
                                        뷰로 전달되었습니다. --}}
                                        <option value="{{ $country['country_name'] }}" @if ($country['country_name'] == \Illuminate\Support\Facades\Auth::user()->country)
                                        selected @endif>{{ $country['country_name'] }}</option>
                                    @endforeach

                                </select>
                                {{-- <p id="account-country" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성
                                검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-country"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류
                                메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-pincode">Pincode
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" type="text" id="user-pincode" name="pincode"
                                    value="{{ \Illuminate\Support\Facades\Auth::user()->pincode }}"> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-pincode" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성
                                검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-pincode"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류
                                메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="user-mobile">Mobile
                                    <span class="astk">*</span>
                                </label>
                                <input class="text-field" type="text" id="user-mobile" name="mobile"
                                    value="{{ \Illuminate\Support\Facades\Auth::user()->mobile }}"> {{-- 인증된 사용자 검색:
                                https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                {{-- <p id="account-mobile" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성
                                검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="account-mobile"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류
                                메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="m-b-45">
                                <button class="button button-outline-secondary w-100">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Update User Account Contact Details /- -->



                <!-- Update User Password via AJAX -->
                <div class="col-lg-6">
                    <div class="reg-wrapper">
                        <h2 class="account-h2 u-s-m-b-20" style="font-size: 18px">Update Password</h2>


                        {{-- jQuery를 사용한 가입 성공 메시지. front/js/custom.js 확인 --}}
                        {{-- <p id="password-success" style="color: green"></p> --}}
                        <p id="password-success"></p>


                        {{-- 사용자 비밀번호 업데이트 오류 표시 --}}
                        {{-- <p id="account-error" style="color: red"></p> --}} {{-- 유효성 검사는 통과했지만 사용자가 제공한 로그인 자격 증명이 틀린
                        경우, jQuery가 일반적인 '잘못된 자격 증명!' 메시지를 표시하는 데 사용됩니다. 또는 사용자 계정이 비활성/비활성화된 경우 메시지를 표시하는 데 사용됩니다. --}}
                        <p id="password-error"></p> {{-- 유효성 검사는 통과했지만 사용자가 제공한 로그인 자격 증명이 틀린 경우, jQuery가 일반적인 '잘못된 자격 증명!'
                        메시지를 표시하는 데 사용됩니다. 또는 사용자 계정이 비활성/비활성화된 경우 메시지를 표시하는 데 사용됩니다. --}}



                        <form id="passwordForm" action="javascript:;" method="post"> {{-- AJAX 호출을 사용하여 제출할 것이므로 'action'
                            HTML 속성을 비활성화(action="javascript:;" 사용)해야 합니다. front/js/custom.js 확인 --}}
                            @csrf {{-- Preventing CSRF Requests: https://laravel.com/docs/9.x/csrf#preventing-csrf-requests
                            --}}


                            <div class="u-s-m-b-30">
                                <label for="current-password">Current Password
                                    <span class="astk">*</span>
                                </label>
                                <input type="password" id="current-password" class="text-field"
                                    placeholder="Current Password" name="current_password">
                                {{-- <p id="password-current_password" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX
                                호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}} {{-- jQuery 루프가
                                작동하려면 패턴이 password-x(예: password-mobile, register-email...)와 같아야 합니다. --}}
                                <p id="password-current_password"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성
                                검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}} {{-- jQuery 루프가 작동하려면 패턴이 password-x(예:
                                password-mobile, register-email...)와 같아야 합니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="usermobile">New Password
                                    <span class="astk">*</span>
                                </label>
                                <input type="password" id="new-password" class="text-field" placeholder="New Password"
                                    name="new_password">
                                {{-- <p id="password-new_password" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX 호출
                                응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="password-new_password"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사
                                오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-30">
                                <label for="useremail">Confirm Password
                                    <span class="astk">*</span>
                                </label>
                                <input type="password" id="confirm-password" class="text-field"
                                    placeholder="Confirm Password" name="confirm_password">
                                {{-- <p id="password-confirm_password" style="color: red"></p> --}} {{-- 이것은 서버(백엔드)의 AJAX
                                호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                                <p id="password-confirm_password"></p> {{-- 이것은 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성
                                검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                            </div>
                            <div class="u-s-m-b-45">
                                <button class="button button-primary w-100">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Update User Password via AJAX /- -->



            </div>
        </div>
    </div>
    <!-- Account-Page /- -->
@endsection