@extends('layouts.channel')
@section('page_type','sub')
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/information.css') }}?v={{ filemtime(public_path('channel_assets/css/information.css')) }}">@endpush
@section('content')
<div id="contents"><div id="channel-information">
<header class="info-heading"><p>MY CHANNEL</p><h1>정보관리</h1></header>
@if(session('success_message'))<p class="info-message success" role="status">{{ session('success_message') }}</p>@endif
@if(session('error_message'))<p class="info-message error" role="alert">{{ session('error_message') }}</p>@endif
@if($errors->any())<div class="info-message error" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
<section class="info-panel"><h2>채널 정보</h2><dl class="info-summary">
    <div><dt>채널 코드</dt><dd>{{ $admin->vendor_id ?: '미등록' }}</dd></div>
    <div><dt>대표 관리자 아이디</dt><dd>{{ $member?->username ?: $admin->email }}</dd></div>
    <div><dt>비밀번호</dt><dd><button type="button" class="info-small-button" data-open-dialog="info-password-dialog">비밀번호 변경</button></dd></div>
    <div><dt>판매 권한 / 수수료</dt><dd>{{ $certification === 'approved' ? '획득' : '미획득' }}@if($vendor?->seller_approved_at) ({{ $vendor->seller_approved_at->format('Y.m.d') }})@endif / 수수료 {{ isset($vendor->commission) ? rtrim(rtrim(number_format($vendor->commission,2,'.',''),'0'),'.').'%' : '미등록' }}</dd></div>
</dl></section>
<form id="infoForm" method="POST" enctype="multipart/form-data" action="{{ route('channel.info.update') }}">@csrf
<details class="info-panel info-company" @if($errors->any()) open @endif><summary><span>회원사 정보</span><small>{{ $details?->shop_name ?: $vendor?->name ?: '미등록' }} · 펼치기 / 접기</small></summary>
    <div class="info-grid">
        <label class="wide">회원사명 <em>필수</em><input name="shop_name" value="{{ old('shop_name',$details?->shop_name ?: $vendor?->name) }}" maxlength="255" required></label>
        <fieldset class="wide"><legend>사업자구분 <em>필수</em></legend><div class="info-radio-row">@foreach(['개인판매','개인사업자','법인사업자'] as $type)<label><input type="radio" name="shop_business_type" value="{{ $type }}" @checked(old('shop_business_type',$details?->shop_business_type) === $type) required>{{ $type }}</label>@endforeach</div></fieldset>
        <label>사업자등록번호<input name="business_license_number" inputmode="numeric" value="{{ old('business_license_number',$details?->business_license_number) }}" placeholder="000-00-00000" maxlength="12"></label>
        <label>대표연락처<input type="tel" name="shop_mobile" value="{{ old('shop_mobile',$details?->shop_mobile) }}" placeholder="010-0000-0000" maxlength="15"></label>
        <label class="wide">대표이메일<div class="info-email"><input name="shop_email" type="email" value="{{ old('shop_email',$details?->shop_email) }}" placeholder="name@example.com" maxlength="150"><select aria-label="이메일 도메인 선택" data-email-domain><option value="">직접입력</option><option>naver.com</option><option>gmail.com</option><option>daum.net</option></select></div></label>
        <label class="wide">우편번호<div class="info-postcode"><input name="shop_pincode" id="shop_pincode" value="{{ old('shop_pincode',$details?->shop_pincode) }}" inputmode="numeric" maxlength="5"><button type="button" class="info-small-button" data-postcode>우편번호 찾기</button></div></label>
        <label class="wide">주소<input name="shop_address" id="shop_address" value="{{ old('shop_address',$details?->shop_address) }}" maxlength="255"></label>
        <label class="wide">상세주소<input name="shop_address_detail" id="shop_address_detail" value="{{ old('shop_address_detail',$details?->shop_address_detail) }}" maxlength="255"></label>
        <label>은행명<input name="bank_name" value="{{ old('bank_name',$details?->bank_name) }}" maxlength="100" list="info-banks"><datalist id="info-banks"><option>국민은행</option><option>신한은행</option><option>우리은행</option><option>하나은행</option><option>농협은행</option><option>기업은행</option><option>카카오뱅크</option><option>토스뱅크</option></datalist></label>
        <label>정산용 계좌번호<input name="bank_account_number" value="{{ old('bank_account_number',$details?->bank_account_number) }}" inputmode="numeric" maxlength="30" placeholder="숫자만 입력"></label>
        <label class="wide">예금주<input name="bank_account_holder_name" value="{{ old('bank_account_holder_name',$details?->bank_account_holder_name) }}" maxlength="100"></label>
        <div class="wide"><h3>정산용 통장사본</h3>@include('channel.sub00.info_document',['field'=>'bank_copy','kind'=>'bankbook','path'=>$details?->bank_copy_image,'label'=>'통장사본'])</div>
    </div>
</details>
<section class="info-panel"><h2>회원사 판매권한 정보</h2><p class="info-help">자사상품을 판매하거나 자사 PG를 사용하려면 판매인증이 필요합니다.</p>
    <p class="info-certification"><strong data-certification="{{ $certification }}">{{ ['approved'=>'인증완료','pending'=>'인증대기','unverified'=>'미인증'][$certification] }}</strong>
        @if($certification === 'approved') 인증완료일: {{ $vendor?->seller_approved_at?->format('Y-m-d') ?: '인증일 미등록' }}@elseif($vendor?->seller_requested_at) 신청일: {{ $vendor->seller_requested_at->format('Y-m-d H:i') }}@endif</p>
    <div class="info-grid"><div class="wide"><h3>사업자등록증</h3><p class="info-help">사업자등록번호: {{ $details?->business_license_number ?: '미등록' }}</p>@include('channel.sub00.info_document',['field'=>'cert_brn','kind'=>'license','path'=>$details?->address_proof_image,'label'=>'사업자등록증'])</div>
    <div class="wide info-account-summary"><h3>사업자 명의 계좌번호 / 통장사본</h3><p>{{ $details?->bank_name ?: '은행 미등록' }} / {{ $details?->bank_account_number ?: '계좌 미등록' }} / {{ $details?->bank_account_holder_name ?: '예금주 미등록' }}</p>@if($details?->bank_copy_image)<div class="info-file"><a href="{{ route('channel.info.document',['kind'=>'bankbook','mode'=>'view']) }}" target="_blank" rel="noopener">통장사본 보기</a><a href="{{ route('channel.info.document',['kind'=>'bankbook','mode'=>'download']) }}">통장사본 다운로드</a></div>@endif<p class="info-help">계좌 및 통장사본은 위 회원사 정보에서 변경할 수 있습니다.</p></div></div>
    @if($admin->type === 'vendor')<p class="info-help">인증된 사업자·계좌·서류를 변경하는 경우 재인증 요청으로 제출해 주세요. 접수 후 관리자 심사 전까지 판매인증은 대기 상태로 변경됩니다.</p><div class="info-actions"><button type="submit" name="action" value="request">{{ $certification === 'approved' ? '재인증 요청' : '인증 요청' }}</button></div>@endif
</section>
<section class="info-panel"><h2>자사 PG 모듈</h2><div class="info-radio-row"><label><input type="radio" name="use_own_pg" value="0" @checked(!old('use_own_pg',$vendor?->use_own_pg))>미사용</label><label><input type="radio" name="use_own_pg" value="1" @checked(old('use_own_pg',$vendor?->use_own_pg)) @disabled($certification !== 'approved')>사용</label></div>
    @if($certification !== 'approved')<p class="info-help">판매인증 완료 후 등록할 수 있습니다.</p>@endif
    <div class="info-grid" data-pg-fields @if(!old('use_own_pg',$vendor?->use_own_pg)) hidden @endif>
        <label class="wide">PG사<select name="pg_provider" data-pg-provider><option value="">선택해 주세요</option>@foreach(['inicis'=>'KG 이니시스','kcp'=>'NHN KCP','toss'=>'토스페이먼츠'] as $key=>$label)<option value="{{ $key }}" @selected(old('pg_provider',$vendor?->pg_provider) === $key)>{{ $label }}</option>@endforeach</select></label>
        @foreach(['pg_merchant_id'=>'상점 ID','pg_site_code'=>'사이트 코드','pg_client_key'=>'Client Key','pg_secret_key'=>'Secret Key'] as $field=>$label)<label class="{{ in_array($field,['pg_client_key','pg_secret_key']) ? 'wide' : '' }}">{{ $label }}<input type="{{ in_array($field,['pg_client_key','pg_secret_key']) ? 'password' : 'text' }}" name="{{ $field }}" value="" maxlength="255" autocomplete="off" placeholder="{{ $vendor?->$field ? '등록된 값 유지 · 변경할 때만 입력' : $label.' 입력' }}"></label>@endforeach
        <p class="wide info-help">PG사를 바꾸면 해당 PG사의 연결 정보를 모두 새로 입력해 주세요. 저장은 연결 정보 등록이며 실제 결제 승인 확인과는 구분됩니다.</p>
    </div>
</section>
<section class="info-panel"><h2>약관동의</h2>
    @foreach([['company_terms_accepted_at','회원사 권한 약관 · 상품 판매시 약관','service'],['seller_terms_accepted_at','판매 권한 약관','seller']] as [$field,$label,$policy])
    <div class="info-terms"><label><input type="checkbox" disabled @checked((bool)$vendor?->$field)>{{ $label }} (필수)</label><span>{{ $vendor?->$field?->format('Y-m-d H:i') ?: '동의 기록 없음' }}</span><details><summary>전문보기</summary><div class="info-policy-body" tabindex="0" role="region" aria-label="{{ $label }} 전문">@include('front.policies.content',['document'=>$policyDocuments[$policy]])</div><p class="info-help">2026-10-26 제공 수정안 · 미확정 항목 포함. 기존 동의 기록을 이 수정안에 대한 신규 동의로 변경하지 않습니다.</p></details></div>
    @endforeach
</section>
<div class="info-actions"><button type="submit" name="action" value="save">정보 수정</button><button type="button" class="secondary" data-open-dialog="info-stop-dialog">채널운영 중지요청</button></div>
</form>
<dialog id="info-password-dialog" class="info-dialog"><form action="{{ route('channel.update_password') }}" method="POST" data-password-form>@csrf<h2>비밀번호 변경</h2><p class="info-help">영문 대문자·소문자와 숫자를 포함해 12자 이상 입력해 주세요.</p><label>현재 비밀번호<input name="current_password" type="password" autocomplete="current-password" required></label><label>새로운 비밀번호<input name="new_password" type="password" autocomplete="new-password" minlength="12" required></label><label>비밀번호 확인<input name="confirm_password" type="password" autocomplete="new-password" required></label><p data-password-feedback role="status"></p><div class="info-actions"><button type="submit">변경하기</button><button type="button" class="secondary" data-close-dialog>창닫기</button></div></form></dialog>
<dialog id="info-stop-dialog" class="info-dialog"><form action="{{ route('channel.operation_stop.request') }}" method="POST">@csrf<h2>채널운영 중지요청</h2><p>운영 중인 Shop 채널: {{ $activeChannels }}개</p><p class="info-help">운영 중인 Shop 채널이 있으면 중지신청이 불가능합니다. 접수 후 총괄 관리자의 최종 승인이 필요합니다.</p><label>요청 메모<textarea name="closure_memo" maxlength="255"></textarea></label><div class="info-actions"><button type="submit" @disabled($activeChannels>0)>중지요청</button><button type="button" class="secondary" data-close-dialog>창닫기</button></div></form></dialog>
</div></div>
@endsection
@push('scripts')<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script><script src="{{ asset('channel_assets/js/information.js') }}?v={{ filemtime(public_path('channel_assets/js/information.js')) }}" defer></script>@endpush
