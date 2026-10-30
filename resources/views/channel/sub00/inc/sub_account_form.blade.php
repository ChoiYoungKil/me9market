@php
    $formKey=$account ? 'edit-'.$account->id : 'create';
    $isOld=old('form_key')===$formKey;
    $member=$isOld ? $oldSelectedMember : $account?->member;
    $value=fn($key,$default='')=>$isOld && is_scalar(old($key,$default)) ? old($key,$default) : $default;
    $token=$member ? ($isOld ? old('member_token') : $service->proof($owner,$member)) : '';
    $selectedPermissions=$isOld && is_array(old('permissions')) ? old('permissions') : ($account?->permissions ?? []);
@endphp
<input type="hidden" name="form_key" value="{{ $formKey }}">
<input type="hidden" name="member_id" value="{{ $member?->id }}">
<input type="hidden" name="member_token" value="{{ $token }}">
<section class="subaccount-member-search"><h3>회원찾기</h3>
<div class="subaccount-search-row"><label>검색구분<select name="search_type"><option value="member_no">회원번호</option><option value="login_id">회원아이디</option><option value="name">회원명</option></select></label><label class="grow">회원 검색어<input name="member_keyword" autocomplete="off" placeholder="정확한 회원번호 또는 아이디 입력"></label><button type="button" data-member-search data-url="{{ route('channel.sub_accounts.lookup') }}">회원찾기</button></div>
<p data-member-message role="status"></p><div data-member-results></div></section>
<section><h3>회원정보</h3><p data-member-placeholder @if($member) hidden @endif>회원찾기를 통해 기존 회원을 선택해 주세요.</p>
<dl class="subaccount-facts" data-member-info @if(!$member) hidden @endif><div><dt>회원번호</dt><dd data-member-field="member_no">{{ $member ? app(\App\Services\MemberRegistration::class)->memberNumber($member) : '' }}</dd></div><div><dt>회원아이디</dt><dd data-member-field="login_id">{{ $member?->username ?: $member?->email }}</dd></div><div><dt>회원명</dt><dd data-member-field="name">{{ $member?->name }}</dd></div></dl></section>
<section><h3>서브관리정보</h3>
<div class="order-fields"><label>이용 시작일시<input type="datetime-local" name="started_at" value="{{ $value('started_at',$account?->started_at?->format('Y-m-d\TH:i') ?? now()->startOfDay()->format('Y-m-d\TH:i')) }}" required></label><label>이용 종료일시<input type="datetime-local" name="ended_at" value="{{ $value('ended_at',$account?->ended_at?->format('Y-m-d\TH:i') ?? now()->addMonthNoOverflow()->endOfDay()->format('Y-m-d\TH:i')) }}" required></label></div>
<fieldset><legend>권한 선택</legend><div class="order-checks">@foreach($permissionLabels as $key=>$label)
@if($key!=='settings' || in_array('settings',$account?->permissions ?? [],true))<label><input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key,$selectedPermissions,true))>{{ $label }}</label>@endif
@endforeach</div></fieldset>
<fieldset><legend>이용 상태</legend><div class="order-checks"><label><input type="radio" name="status" value="1" @checked((int)$value('status',$account?->admin?->status ?? 1)===1)>운영</label><label><input type="radio" name="status" value="0" @checked((int)$value('status',$account?->admin?->status ?? 1)===0)>중지</label></div></fieldset>
<p class="subaccount-note">선택한 회원의 기존 로그인 정보로 이용합니다. 지정한 기간과 권한 내에서만 채널을 관리할 수 있습니다.</p></section>
