@extends('layouts.admin')
@section('content')
<div id="contents" data-review-member-id="{{ $user->id }}">
    <div class="row"><div class="box box1">
        <div class="page_info"><div class="ttl">판매자 승인 검토</div><ul class="dep"><li>홈</li><li>회원 관리</li><li>판매자 승인</li></ul></div>
        <div class="conbx"><div class="con_w seller-review">
            @if(session('success_message'))<p class="seller-review-message" role="status">{{ session('success_message') }}</p>@endif
            @if($errors->any())<div class="seller-review-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <h1>{{ $user->name }} <small>{{ $memberNumber }}</small></h1>
            <p class="seller-review-status" data-seller-status>{{ $vendorDetails?->status == 1 ? '인증완료 · 판매자 승인 완료' : ($vendorDetails?->seller_requested_at ? '판매자 인증 요청 접수 · 심사 대기' : '판매자 인증 미요청') }}</p>
            <section><h2>가입 및 신청 정보</h2><dl><div><dt>가입 방식</dt><dd>{{ $isSocialMember ? '소셜회원가입' : '일반회원가입' }}</dd></div><div><dt>이름</dt><dd>{{ $user->name }}</dd></div><div><dt>회원번호</dt><dd>{{ $memberNumber }}</dd></div><div><dt>연락처</dt><dd>{{ $user->mobile }}</dd></div><div><dt>이메일</dt><dd>{{ $user->email }}</dd></div><div><dt>인증 요청일</dt><dd>{{ $vendorDetails?->seller_requested_at?->format('Y-m-d H:i:s') ?? '-' }}</dd></div></dl></section>
            <section><h2>회원사와 정산 정보</h2><dl><div><dt>회원사명</dt><dd>{{ $businessDetails->shop_name ?? '-' }}</dd></div><div><dt>사업자구분</dt><dd>{{ $businessDetails->shop_business_type ?? '-' }}</dd></div><div><dt>사업자등록번호</dt><dd>{{ $businessDetails->business_license_number ?? '-' }}</dd></div><div><dt>회원사 주소</dt><dd>{{ $businessDetails->shop_address ?? '' }} {{ $businessDetails->shop_address_detail ?? '' }}</dd></div><div><dt>정산 은행</dt><dd>{{ $businessDetails->bank_name ?? '-' }}</dd></div><div><dt>정산 계좌번호</dt><dd>{{ $businessDetails->bank_account_number ?? '-' }}</dd></div><div><dt>예금주</dt><dd>{{ $businessDetails->bank_account_holder_name ?? '-' }}</dd></div></dl></section>
            <section><h2>제출 서류 확인</h2><div class="seller-review-files">
                @foreach([['address_proof_image','license','사업자등록증'],['bank_copy_image','bankbook','통장사본']] as [$field,$kind,$label])
                    @if(!empty($businessDetails?->$field))
                        <div class="seller-document-row">
                            <div class="seller-document-info"><strong>{{ $label }}</strong><span>{{ strtoupper(pathinfo($businessDetails->$field, PATHINFO_EXTENSION)) }} 파일 · 제출 완료</span></div>
                            <a class="seller-document-link" aria-label="{{ $label }} 내려받기" href="{{ route('admin.member.document', ['vendor'=>$user->vendor_id,'kind'=>$kind]) }}"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/></svg><span>다운로드</span></a>
                        </div>
                    @endif
                @endforeach
            </div></section>
            @if($vendorDetails?->status == 1)
                <section><h2>승인 결과</h2><dl><div><dt>판매인증</dt><dd>인증완료</dd></div><div><dt>승인일</dt><dd>{{ $vendorDetails->seller_approved_at?->format('Y-m-d H:i:s') ?? '-' }}</dd></div><div><dt>승인 관리자</dt><dd>{{ $approvedBy?->name ?? '-' }}</dd></div></dl></section>
            @elseif($issues)
                <div class="seller-review-error" role="alert">@foreach($issues as $issue)<p>{{ $issue }}</p>@endforeach</div>
            @else
                <form method="post" action="{{ route('admin.users.seller_approval', $user->id) }}" data-seller-approval>@csrf
                    <label class="seller-review-check"><input type="checkbox" name="review_confirmed" value="1" required>신청 정보와 제출 서류를 확인했습니다.</label>
                    <button type="submit" class="btn01 col2">판매자 승인</button>
                </form>
            @endif
            <div class="seller-review-links"><a href="{{ route('admin.users') }}" class="btn01">회원 목록</a><a href="{{ url('admin/add-edit-user/'.$user->id) }}" class="btn01">회원 상세정보</a></div>
        </div></div>
    </div></div>
</div>
<style>
.seller-review{max-width:1100px}.seller-review h1{font-size:25px;line-height:1.7;margin:8px 0 14px}.seller-review h1 small{font-size:14px;color:#8b7f97;margin-left:15px;font-weight:400}.seller-review-status{display:inline-block;background:#f1eafa;border:1px solid #d9c8eb;padding:10px 16px;color:#7650a0;border-radius:4px;margin-bottom:12px;font-size:14px}.seller-review section{margin:24px 0 30px}.seller-review h2{font-size:18px;border-bottom:1px solid #ddd1e9;padding-bottom:14px;margin-bottom:16px}.seller-review dl{display:grid;grid-template-columns:1fr 1fr;gap:17px 25px}.seller-review dl>div{display:grid;grid-template-columns:125px minmax(0,1fr);gap:15px;font-size:14px;line-height:1.8}.seller-review dt{color:#93849f}.seller-review dd{overflow-wrap:anywhere}.seller-review-message{padding:17px;background:#eaf5ee;color:#357c51;margin:0 0 20px}.seller-review-error{padding:17px;background:#fff0f0;color:#a74354;margin:20px 0;line-height:1.8}.seller-review-check{display:flex;align-items:center;gap:10px;font-size:14px;margin-bottom:18px;cursor:pointer}.seller-review-check input{appearance:auto;width:18px;height:18px;accent-color:#7650a0}.seller-review-links{display:flex;flex-wrap:wrap;gap:12px}.seller-review-files{border-top:1px solid #e8dfef}.seller-document-row{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:18px 0;border-bottom:1px solid #e8dfef}.seller-document-info{display:flex;align-items:center;flex-wrap:wrap;gap:9px 20px;min-width:0}.seller-document-info strong{font-size:14px;font-weight:500;color:#493858}.seller-document-info span{font-size:12px;color:#a18dad}.seller-document-link{display:inline-flex;align-items:center;justify-content:center;gap:9px;flex:none;width:auto;min-width:112px;min-height:44px;box-sizing:border-box;border:1px solid #d4c3e3;background:#fff;color:#79549b;padding:11px 16px;border-radius:5px;font-size:13px;line-height:20px;white-space:nowrap;text-decoration:none;transition:background .15s,border-color .15s}.seller-document-link:hover{background:#f3ecfb;border-color:#ad8bc9}.seller-document-link:focus-visible{outline:2px solid #8e62ba;outline-offset:3px}.seller-review-links{margin-top:35px;padding-top:25px;border-top:1px solid #e4d9ec}.seller-review .btn01{display:inline-flex;align-items:center;justify-content:center;min-height:43px;padding:10px 18px;height:auto;font-size:13px;line-height:1.5;box-sizing:border-box}.seller-review button{cursor:pointer}@media(max-width:800px){.seller-review dl{grid-template-columns:1fr}.seller-review dl>div{grid-template-columns:105px minmax(0,1fr)}.seller-review h1 small{display:block;margin-left:0}}
</style>
@push('scripts')<script>document.querySelector('[data-seller-approval]')?.addEventListener('submit', function(event) { if (!confirm(@json($user->name.' ('.$memberNumber.') 회원을 판매자로 승인하시겠습니까?'))) event.preventDefault(); });</script>@endpush
@endsection
