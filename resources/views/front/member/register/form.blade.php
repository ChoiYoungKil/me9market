@php
    $titles = [1 => '기본정보', 2 => '회원사 정보', 3 => '판매자 정보'];
    $descriptions = [1 => '미구마켓과 Shop 채널에서 사용할 기본정보를 입력해 주세요.', 2 => '상품 등록과 Shop 채널 이용을 위한 회원사 정보를 입력해 주세요.', 3 => 'Shop 채널 판매를 위한 서류를 저장하고 판매 권한 인증을 요청해 주세요.'];
    $phone = explode('-', $step === 1 ? ($user->mobile ?? '') : ($businessDetails->shop_mobile ?? ''));
    $email = explode('@', $step === 1 ? $user->email : ($businessDetails->shop_email ?? ''));
    $isRequested = (bool) $vendorDetails?->seller_requested_at;
@endphp
<div class="market-shell member-flow" data-member-id="{{ $user->id }}" data-member-number="{{ $memberNumber }}">
    <header class="market-register-head"><p class="home-eyebrow">JOIN ME9 MARKET · STEP {{ $step }}</p><h1>함께하는 시작,<br><em>{{ $titles[$step] }}.</em></h1><p>{{ $descriptions[$step] }}</p></header>
    <nav class="member-steps" aria-label="회원가입 단계">
        @foreach([1 => '기본정보', 2 => '회원사권한', 3 => '판매자권한'] as $number => $label)
            @php($done = $number === 1 ? (bool) $user->profile_completed_at : ($number === 2 ? (bool) $vendorDetails?->company_terms_accepted_at : $isRequested))
            <a href="{{ route('front.member.register.step'.$number) }}" data-step-link @if($number === $step) aria-current="step" @endif><span>STEP {{ $number }} <small>{{ $done ? ($number === 3 ? ($vendorDetails?->status == 1 ? '인증완료' : '요청완료') : '입력완료') : ($number === 3 && $vendorDetails?->seller_terms_accepted_at ? '임시저장' : '미입력') }}</small></span><strong>{{ $label }}</strong></a>
        @endforeach
    </nav>
    <section class="member-section"><h2>회원정보</h2><dl class="member-facts"><div><dt>회원번호</dt><dd data-member-number>{{ $memberNumber }}</dd></div><div><dt>가입일</dt><dd>{{ $user->created_at->format('Y-m-d H:i') }}</dd></div><div><dt>아이디</dt><dd>{{ $isSocialMember ? '소셜회원 · '.($user->socialProviderLabel ?? '간편가입') : ($user->username ?? $user->email) }}</dd></div><div><dt>비밀번호</dt><dd>@if($isSocialMember)<span class="member-help">소셜 계정으로 로그인합니다.</span>@else<button type="button" id="btnChangePassword">비밀번호 변경</button>@endif</dd></div></dl></section>
    <p class="member-feedback" role="status" tabindex="-1" hidden></p>
    <form id="step{{ $step }}Form" class="member-form" data-member-form data-step="{{ $step }}" action="{{ route('front.member.register.step'.$step.'.update') }}" method="POST" enctype="multipart/form-data" data-next-url="{{ $step < 3 ? route('front.member.register.step'.($step + 1)) : route('front.member.register.complete') }}">
        @csrf
        <section class="member-section"><h2>{{ $titles[$step] }}</h2><div class="member-fields">
        @if($step === 1)
            <div class="member-field"><label for="name">이름</label><input id="name" name="name" value="{{ $user->name }}" maxlength="100" required></div>
            <div class="member-field" role="group" aria-labelledby="gender-label"><span id="gender-label">성별</span><div class="member-radio"><label><input type="radio" name="gender" value="Male" @checked($user->gender === 'Male')>남성</label><label><input type="radio" name="gender" value="Female" @checked($user->gender === 'Female')>여성</label></div></div>
            <div class="member-field member-wide"><label for="mobile_1">연락처</label><div><div class="member-inline member-phone">
                <select id="mobile_1" name="mobile_1" aria-label="연락처 앞자리" required>@foreach(['010','011','016','017','018','019'] as $prefix)<option value="{{ $prefix }}" @selected(($phone[0] ?? '010') === $prefix)>{{ $prefix }}</option>@endforeach</select><span>-</span><input name="mobile_2" aria-label="연락처 중간자리" inputmode="numeric" pattern="[0-9]{3,4}" maxlength="4" value="{{ $phone[1] ?? '' }}" required><span>-</span><input name="mobile_3" aria-label="연락처 끝자리" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" value="{{ $phone[2] ?? '' }}" required><button type="button" data-identity>본인인증</button>
            </div><p class="member-help">연락처 입력과 본인인증 완료는 별도로 처리됩니다.</p></div></div>
            <div class="member-field"><label for="birth_date">생년월일</label><input type="date" id="birth_date" name="birth_date" value="{{ $user->birth_date?->format('Y-m-d') }}" min="1900-01-02" max="{{ now()->format('Y-m-d') }}"></div>
            <div class="member-field member-wide"><label for="profile-email">이메일</label>@if($isSocialMember)<div><input id="profile-email" name="email" type="email" value="{{ $user->email }}" readonly><p class="member-help">인증한 소셜 계정의 이메일입니다.</p></div>@else<div class="member-email"><input id="profile-email" name="email_prefix" aria-label="이메일 아이디" value="{{ $email[0] ?? '' }}" required><span>@</span><input name="email_domain" aria-label="이메일 도메인" value="{{ $email[1] ?? '' }}" required><select data-email-domain aria-label="이메일 도메인 선택"><option value="">직접입력</option><option>naver.com</option><option>gmail.com</option><option>daum.net</option></select></div>@endif</div>
            @include('front.member.register.fields-address', ['postcode' => $user->pincode, 'address' => $user->address, 'addressDetail' => $user->city])
        @elseif($step === 2)
            <div class="member-field member-wide"><label for="shop_name">회원사 명</label><input id="shop_name" name="shop_name" value="{{ $businessDetails->shop_name ?? '' }}" maxlength="255" required></div>
            <div class="member-field member-wide" role="group" aria-labelledby="business-type-label"><span id="business-type-label">사업자구분</span><div class="member-radio">@foreach(['개인판매','개인사업자','법인사업자'] as $type)<label><input type="radio" name="shop_business_type" value="{{ $type }}" @checked(($businessDetails->shop_business_type ?? '개인판매') === $type)>{{ $type }}</label>@endforeach</div></div>
            <div class="member-field member-wide"><label for="business_license_1">사업자등록번호</label><div class="member-number-group">@php($license = explode('-', $businessDetails->business_license_number ?? ''))@foreach([1=>3,2=>2,3=>5] as $part=>$length)<input id="business_license_{{ $part }}" name="business_license_{{ $part }}" aria-label="사업자등록번호 {{ $part }}자리" inputmode="numeric" pattern="{{ '[0-9]{'.$length.'}' }}" maxlength="{{ $length }}" value="{{ $license[$part-1] ?? '' }}" required>@if($part < 3)<span>-</span>@endif @endforeach</div></div>
            <div class="member-field member-wide"><label for="mobile_1">대표연락처</label><div class="member-inline member-phone"><select id="mobile_1" name="mobile_1" aria-label="연락처 앞자리">@foreach(['010','011','016','017','018','019','02','031','032','051','053','062','042','052','044','033','043','041','063','061','054','055','064','070'] as $prefix)<option value="{{ $prefix }}" @selected(($phone[0] ?? '010') === $prefix)>{{ $prefix }}</option>@endforeach</select><span>-</span><input name="mobile_2" aria-label="연락처 중간자리" pattern="[0-9]{3,4}" maxlength="4" value="{{ $phone[1] ?? '' }}" required><span>-</span><input name="mobile_3" aria-label="연락처 끝자리" pattern="[0-9]{4}" maxlength="4" value="{{ $phone[2] ?? '' }}" required></div></div>
            <div class="member-field member-wide"><label for="email_1">대표이메일</label><div class="member-email"><input id="email_1" name="email_1" aria-label="이메일 아이디" value="{{ $email[0] ?? '' }}" required><span>@</span><input name="email_2" aria-label="이메일 도메인" value="{{ $email[1] ?? '' }}" required><select data-email-domain aria-label="이메일 도메인 선택"><option value="">직접입력</option><option>naver.com</option><option>gmail.com</option><option>daum.net</option></select></div></div>
            @include('front.member.register.fields-address', ['addressLabel'=>'회원사 주소지', 'postcode'=>$businessDetails->shop_pincode ?? '', 'address'=>$businessDetails->shop_address ?? '', 'addressDetail'=>$businessDetails->shop_address_detail ?? ''])
            @include('front.member.register.fields-bank')
            @include('front.member.register.field-document', ['field'=>'bank_copy_image', 'label'=>'정산용 통장사본', 'required'=>true])
        @else
            <div class="member-field member-wide"><span>판매인증</span><div><strong class="member-badge">{{ $vendorDetails?->status == 1 ? '인증완료' : ($isRequested ? '인증 요청 접수 · 심사 대기' : '미인증') }}</strong><p class="member-help">{{ $vendorDetails?->status == 1 ? '관리자 승인이 완료되었습니다.' : '서류 저장만으로 판매 권한이 승인되지는 않습니다.' }} @if($vendorDetails?->seller_approved_at)승인일: {{ $vendorDetails->seller_approved_at->format('Y-m-d H:i') }}@endif</p></div></div>
            @include('front.member.register.field-document', ['field'=>'address_proof_image', 'label'=>'사업자등록증'])
            @include('front.member.register.fields-bank', ['bankLabel'=>'사업자 명의 계좌번호 (정산용)'])
            @include('front.member.register.field-document', ['field'=>'bank_copy_image', 'label'=>'사업자 명의 통장사본'])
        @endif
        </div></section>
        <section class="member-section member-consents" data-consent-group><h2>약관 동의</h2>
        @if($step === 1)
            <label class="member-consent-all"><input type="checkbox" data-consent-all>전체 약관 동의</label>
            @foreach([['agree_terms','이용약관 동의','terms_url',true],['agree_privacy','개인정보 수집 및 이용에 관한 안내','privacy_url',true],['agree_third_party','제3자 정보제공 동의','third_party_url',true],['marketing_opt_in','마케팅활용동의','marketing_url',false],['notification_opt_in','알림정보수신동의','notification_url',false]] as [$field,$label,$config,$required])
                <div class="member-consent-row"><label><input type="checkbox" name="{{ $field }}" value="1" data-consent-item @required($required) @checked($required ? (bool)$user->terms_accepted_at : (bool)$user->$field)>{{ $label }} <small>({{ $required ? '필수' : '선택' }})</small></label><details><summary>전문보기</summary>@if(config('shop_channel.'.$config))<a href="{{ config('shop_channel.'.$config) }}" target="_blank" rel="noopener">약관 원문 열기</a>@else<p>약관 원문이 아직 등록되지 않았습니다.</p>@endif</details></div>
            @endforeach
        @else
            <div class="member-consent-row"><label><input type="checkbox" name="agree1" value="1" required @checked($step === 2 ? (bool)$vendorDetails?->company_terms_accepted_at : (bool)$vendorDetails?->seller_terms_accepted_at)>{{ $step === 2 ? '상품 판매시 약관 동의' : '판매권한 약관 동의' }} <small>(필수)</small></label><details><summary>전문보기</summary>@if($policy = config('shop_channel.'.($step === 2 ? 'company_terms_url' : 'seller_terms_url')))<a href="{{ $policy }}" target="_blank" rel="noopener">약관 원문 열기</a>@else<p>약관 원문이 아직 등록되지 않았습니다.</p>@endif</details></div>
        @endif
        </section>
        <div class="member-actions">
            <button type="button" data-cancel class="member-secondary">취소</button><button type="submit" name="action" value="save">저장</button>
            @if($step === 1)<button type="submit" name="action" value="next" class="member-primary">Step2 회원사 등록</button>@elseif($step === 2)<a href="{{ route('front.member.register.step1') }}" data-step-link>Step1 기본정보</a><button type="submit" name="action" value="next" class="member-primary">Step3 판매권한 신청</button>@else<a href="{{ route('front.member.register.step2') }}" data-step-link>Step2 회원사권한</a>@if($vendorDetails?->status == 1)<a href="{{ route('front.member.register.complete') }}" class="member-primary" data-step-link>가입 완료 확인</a>@else<button type="submit" name="action" value="request" class="member-primary">인증 요청 · 가입 최종 완료</button>@endif @endif
        </div>
    </form>
</div>
<dialog id="member-info-dialog" class="member-dialog"><h2>본인인증 안내</h2><p>현재 본인인증 서비스가 연결되어 있지 않습니다. 입력한 연락처는 저장되지만 본인인증 완료로 처리되지 않습니다.</p><button type="button" data-dialog-close>닫기</button></dialog>
@unless($isSocialMember)
<dialog id="passwordChangeModal" class="member-dialog"><form data-password-form action="{{ route('user.update.password') }}">@csrf<h2>비밀번호 변경</h2><label>현재 비밀번호<input type="password" name="current_password" autocomplete="current-password" required></label><label>새로운 비밀번호<input type="password" name="new_password" autocomplete="new-password" minlength="12" required></label><label>비밀번호 확인<input type="password" name="confirm_password" autocomplete="new-password" minlength="12" required></label><p class="member-help">영문 대문자·소문자와 숫자를 포함해 12자 이상 입력해 주세요.</p><p data-password-feedback role="status"></p><div class="member-actions"><button type="button" data-dialog-close>창닫기</button><button type="submit" class="member-primary">변경하기</button></div></form></dialog>
@endunless
<dialog id="member-postcode-dialog" class="member-dialog member-postcode"><button type="button" data-dialog-close>창닫기</button><div id="member-postcode"></div></dialog>
@push('styles')<link rel="stylesheet" href="{{ asset('me9market/css/member-registration.css').'?v='.filemtime(public_path('me9market/css/member-registration.css')) }}">@endpush
@push('scripts')<script src="{{ asset('me9market/js/member-registration.js').'?v='.filemtime(public_path('me9market/js/member-registration.js')) }}" defer></script>@endpush
