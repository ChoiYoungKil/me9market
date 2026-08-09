@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '01')
@section('dep3_id', '01')

@section('content')
    <div id="contents">
        <div id="modify">
            <form id="profileForm" action="{{ route('mypage.profile.update') }}" method="post">
                @csrf
                <div class="box_w">
                    <div class="box box1">
                        <!-- 페이지 정보 -->
                        <div class="page_info">
                            <div class="ttl">회원정보수정</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>정보관리</li>
                                <li>회원정보수정</li>
                            </ul>
                        </div>

                        <div class="ttl01">회원정보</div>

                        <div class="tb01">
                            <table class="two">
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>회원번호</span></th>
                                        <td>{{ Auth::user()->member_number }}</td>
                                        <th class="w160"><span>가입일</span></th>
                                        <td>{{ Auth::user()->created_at }}</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>아이디</span></th>
                                        <td>{{ Auth::user()->username }}</td>
                                        <th class="w160"><span>비밀번호</span></th>
                                        <td><a class="btn01 pop_btn" href="javascript:void(0);" data-pop="pop1_1">비밀번호변경</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 비밀번호 변경 팝업 -->
                        <div class="popup_bx" data-id="pop1_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1"><img
                                                src="{{ asset('frontend/images/btn/pop_close.png') }}" alt="닫기"></div>
                                        <div class="page_info type2">
                                            <div class="ttl brb">비밀번호 변경</div>
                                        </div>

                                        <div id="passwordForm" data-action="{{ route('user.update.password') }}">
                                            @csrf
                                            <div class="conbx">
                                                <div class="con_w">
                                                    <div class="tb01 type2">
                                                        <table class="two">
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160">현재 비밀번호</th>
                                                                    <td>
                                                                        <input class="w100p" type="password"
                                                                            name="current_password" required="required">
                                                                        <p id="password-current_password" style="color:red">
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160">새로운 비밀번호</th>
                                                                    <td>
                                                                        <input class="w100p" type="password"
                                                                            name="new_password" required="required">
                                                                        <p id="password-new_password" style="color:red"></p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160">비밀번호 확인</th>
                                                                    <td>
                                                                        <input class="w100p" type="password"
                                                                            name="confirm_password" required="required">
                                                                        <p id="password-confirm_password" style="color:red">
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="mt10">
                                                        <p id="password-error" style="color:red"></p>
                                                        <p id="password-success" style="color:green"></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 하단버튼 -->
                                            <div class="btm_btn mt10">
                                                <button type="button" id="submitPasswordChange" class="btn_submit" style="border:0; cursor:pointer;">변경하기</button>
                                                <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box box2">
                        <div class="ttl01">기본정보</div>

                        <div class="tb01 type2">
                            <table class="two">
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>이름</span></th>
                                        <td><input type="text" name="name" value="{{ Auth::user()->name }}"
                                                required="required"></td>
                                        <th class="w160"><span>성별</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="gender" id="gender_m" value="Male"
                                                        @if(Auth::user()->gender == 'Male') checked @endif>
                                                    <label for="gender_m">남성</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="gender" id="gender_w" value="Female"
                                                        @if(Auth::user()->gender == 'Female') checked @endif>
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
                                                        $userMobile = !empty(Auth::user()->mobile) ? explode('-', Auth::user()->mobile) : ['', '', ''];
                                                    @endphp
                                                    <select name="mobile_1" required="required">
                                                        <option value="" disabled></option>
                                                        <option value="010" @if(Str::startsWith(Auth::user()->mobile, '010')) selected @endif>010</option>
                                                        <option value="011" @if(Str::startsWith(Auth::user()->mobile, '011')) selected @endif>011</option>
                                                    </select>
                                                    <span>-</span>
                                                    <input type="text" class="tel1" name="mobile_2" required="required"
                                                        value="{{ $userMobile[1] ?? '' }}">
                                                    <span>-</span>
                                                    <input type="text" class="tel2" name="mobile_3" required="required"
                                                        value="{{ $userMobile[2] ?? '' }}">
                                                </div>
                                                <a href="javascript:void(0);" class="btn01 col5">본인인증</a>
                                            </div>
                                            <span class="fcol2 r_txt">( 현재 개발중 )</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>이메일</span></th>
                                        <td colspan="3">
                                            <div class="email_bx">
                                                @php
                                                    $userEmail = !empty(Auth::user()->email) ? explode('@', Auth::user()->email) : ['', ''];
                                                @endphp
                                                <input type="text" class="email1" name="email_1" required="required"
                                                    value="{{ $userEmail[0] ?? '' }}">
                                                <span>@</span>
                                                <input type="text" class="email2" name="email_2" required="required"
                                                    value="{{ $userEmail[1] ?? '' }}">
                                                <select class="off">
                                                    <option value="">직접입력</option>
                                                    <option value="naver.com">naver.com</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>주소<br class="pc_show">(배송지)</span></th>
                                        <td colspan="3">
                                            <div class="addr_bx">
                                                <input type="text" id="profile_zipcode" class="addr1 off" name="zipcode" placeholder="우편번호"
                                                    required="required" value="{{ Auth::user()->pincode }}">
                                                <a href="javascript:;" onclick="execDaumPostcode('profile_zipcode', 'profile_address1', 'profile_address2')" class="btn01">우편번호찾기</a>
                                                <input type="text" id="profile_address1" class="addr2 off" name="address1" placeholder="주소"
                                                    required="required" value="{{ Auth::user()->address }}">
                                                <input type="text" id="profile_address2" class="addr3 off" name="address2" placeholder="상세주소"
                                                    required="required" value="{{ Auth::user()->city }}">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="btm_btn">
                    <a href="{{ route('mypage.dashboard') }}" class="btn_cancel">취소</a>
                    <button type="submit" class="btn_submit" style="border:0; cursor:pointer;">정보수정</button>
                </div>
            </form>

            <form id="companyInfoForm" action="{{ route('front.member.register.step2.update') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="agree1" value="1">
                <div class="box_w">
                    {{-- 회원사 정보 (Box 3) --}}
                    <div class="box box3">
                        <div class="ttl01">
                            회원사 정보
                            <button type="button" class="btn_toggle" style="float:right; cursor:pointer; border:none; background:none; font-size:16px;">▼</button>
                        </div>

                        <div class="tb01 type2" style="display:none;">
                            <table class="two">
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>회원사 명</span></th>
                                        <td colspan="3"><input type="text" class="w310" name="shop_name"
                                                value="{{ $business->shop_name ?? '' }}" required="required"></td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>사업자구분</span></th>
                                        <td>
                                            <ul class="chk01">
                                                <li>
                                                    <input type="radio" name="shop_business_type" id="type2_1_1"
                                                        value="individual" {{ ($business->shop_business_type ?? '') == 'individual' ? 'checked' : '' }}>
                                                    <label for="type2_1_1">개인판매</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="shop_business_type" id="type2_1_2"
                                                        value="business" {{ ($business->shop_business_type ?? '') == 'business' ? 'checked' : '' }}>
                                                    <label for="type2_1_2">개인사업자</label>
                                                </li>
                                                <li>
                                                    <input type="radio" name="shop_business_type" id="type2_1_3"
                                                        value="corporation" {{ ($business->shop_business_type ?? '') == 'corporation' ? 'checked' : '' }}>
                                                    <label for="type2_1_3">법인사업자</label>
                                                </li>
                                            </ul>
                                        </td>
                                        <th class="w160"><span>사업자등록번호</span></th>
                                        <td>
                                            @php
                                                $bizLicense = !empty($business->business_license_number) ? explode('-', $business->business_license_number) : ['', '', ''];
                                            @endphp
                                            <div class="tel_bx2">
                                                <input type="text" class="tel1" name="business_license_1"
                                                    value="{{ $bizLicense[0] ?? '' }}" required="required">
                                                <span>-</span>
                                                <input type="text" class="tel2" name="business_license_2"
                                                    value="{{ $bizLicense[1] ?? '' }}" required="required">
                                                <span>-</span>
                                                <input type="text" class="tel3" name="business_license_3"
                                                    value="{{ $bizLicense[2] ?? '' }}" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>연락처</span></th>
                                        <td colspan="3">
                                            @php
                                                $shopMobile = !empty($business->shop_mobile) ? explode('-', $business->shop_mobile) : ['', '', ''];
                                            @endphp
                                            <div class="tel_bx">
                                                <select name="mobile_1" required="required">
                                                    <option value="" disabled selected></option>
                                                    <option value="010" {{ ($shopMobile[0] ?? '') == '010' ? 'selected' : '' }}>010</option>
                                                    <option value="011" {{ ($shopMobile[0] ?? '') == '011' ? 'selected' : '' }}>011</option>
                                                </select>
                                                <span>-</span>
                                                <input type="text" class="tel1" name="mobile_2"
                                                    value="{{ $shopMobile[1] ?? '' }}" required="required">
                                                <span>-</span>
                                                <input type="text" class="tel2" name="mobile_3"
                                                    value="{{ $shopMobile[2] ?? '' }}" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>이메일</span></th>
                                        <td colspan="3">
                                            @php
                                                $shopEmail = !empty($business->shop_email) ? explode('@', $business->shop_email) : ['', ''];
                                            @endphp
                                            <div class="email_bx">
                                                <input type="text" class="email1" name="email_1"
                                                    value="{{ $shopEmail[0] ?? '' }}" required="required">
                                                <span>@</span>
                                                <input type="text" class="email2" name="email_2"
                                                    value="{{ $shopEmail[1] ?? '' }}" required="required">
                                                <select class="off">
                                                    <option value="" selected>직접입력</option>
                                                    <option value="naver.com">naver.com</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>회원사 주소지</span></th>
                                        <td colspan="3">
                                            <div class="addr_bx">
                                                <input type="text" id="company_zipcode" class="addr1 off" name="zipcode" placeholder="우편번호"
                                                    value="{{ $business->shop_pincode ?? '' }}" required="required">
                                                <a href="javascript:;" onclick="execDaumPostcode('company_zipcode', 'company_address1', 'company_address2')" class="btn01">우편번호찾기</a>
                                                <input type="text" id="company_address1" class="addr2 off" name="address1" placeholder="주소"
                                                    value="{{ $business->shop_address ?? '' }}" required="required">
                                                <input type="text" id="company_address2" class="addr3 off" name="address2" placeholder="상세주소"
                                                    value="{{ $business->shop_address_detail ?? '' }}" required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>정산용 계좌번호</span></th>
                                        <td colspan="3">
                                            <div class="bank_bx">
                                                <select name="bank_name" required="required">
                                                    <option value="" disabled selected>은행선택</option>
                                                    <option value="국민은행" {{ ($business->bank_name ?? '') == '국민은행' ? 'selected' : '' }}>국민은행</option>
                                                    <!-- 추가 은행 옵션 필요 -->
                                                </select>
                                                <input type="text" class="bank1" name="account_number" placeholder="계좌번호"
                                                    value="{{ $business->bank_account_number ?? '' }}" required="required">
                                                <input type="text" class="bank2" name="account_holder_name"
                                                    placeholder="예금주"
                                                    value="{{ $business->bank_account_holder_name ?? '' }}"
                                                    required="required">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>정산용 통장사본</span></th>
                                        <td colspan="3">
                                            <div class="fileBox">
                                                <input type="text" class="fileName" readonly="readonly"
                                                    value="{{ $business->bank_copy_image ?? '' }}"
                                                    placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                <input type="file" id="uploadBtn" class="uploadBtn" name="bank_copy_image">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="btm_btn t_r pt10" style="display:none;">
                            <button type="submit" class="btn_submit" style="border:0; cursor:pointer;">회원사 정보 저장</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- 판매 권한 정보 (Box 4/5/6) --}}
            @php
                $vendorStatus = -1;
                $hasProof = false;
                if($vendor) {
                    $vendorStatus = $vendor->status;
                    $hasProof = $business && !empty($business->address_proof_image);
                }
            @endphp

            <form id="sellerCertificationForm" action="{{ route('front.member.register.step3.update') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="agree1" value="1">
                <div class="box_w">
                    @if($vendorStatus == 1)
                        {{-- Box 6: 인증 완료 --}}
                        <div class="box box6">
                            <div class="ttl01">
                                회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 공유 및 공동구매 상품을 판매하기 위해서는 판매인증이 필요합니다. )</span>
                                <button type="button" class="btn_toggle" style="float:right; cursor:pointer; border:none; background:none; font-size:16px;">▼</button>
                            </div>

                            <div class="tb01 type2" style="display:none;">
                                <table>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160">판매인증</th>
                                            <td><strong class="fcol2">인증완료 ( 인증완료일 : {{ $vendor->updated_at->format('Y-m-d') }} )</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자등록증</th>
                                            <td>
                                                <div class="f_img">
                                                    @if(!empty($business->address_proof_image))
                                                    <div class="img_w" style="background-image:url({{ asset('front/images/bank_copies/' . $business->address_proof_image) }})"></div>
                                                    @endif
                                                    <span class="f_txt">사업자등록번호 / {{ $business->business_license_number ?? '' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                            <td>
                                                <div class="f_down">
                                                    <div class="f_name">{{ $business->bank_name ?? '' }} / {{ $business->bank_account_number ?? '' }} / {{ $business->bank_account_holder_name ?? '' }} </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @elseif($hasProof)
                        {{-- Box 5: 인증 대기 --}}
                        <div class="box box5">
                            <div class="ttl01">
                                회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 공유 및 공동구매 상품을 판매하기 위해서는 판매인증이 필요합니다. )</span>
                                <button type="button" class="btn_toggle" style="float:right; cursor:pointer; border:none; background:none; font-size:16px;">▼</button>
                            </div>

                            <div class="tb01 type2" style="display:none;">
                                <table>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160">판매인증</th>
                                            <td><strong class="fcol4">인증대기</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자등록증</th>
                                            <td>
                                                <div class="f_img">
                                                    @if(!empty($business->address_proof_image))
                                                    <div class="img_w" style="background-image:url({{ asset('front/images/bank_copies/' . $business->address_proof_image) }})"></div>
                                                    @endif
                                                </div>
                                                <div class="fileBox">
                                                    <input type="text" class="fileName on" readonly="readonly" value="{{ $business->address_proof_image ?? '' }}">
                                                    <label for="uploadBtn4" class="btn_file">찾아보기</label>
                                                    <input type="file" id="uploadBtn4" class="uploadBtn" name="address_proof_image">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                            <td>
                                                <div class="bank_bx">
                                                    <select name="bank_name" required="required">
                                                        <option value="" disabled>은행선택</option>
                                                        <option value="국민은행" {{ ($business->bank_name ?? '') == '국민은행' ? 'selected' : '' }}>국민은행</option>
                                                    </select>
                                                    <input type="text" class="bank1" name="account_number" placeholder="계좌번호" required="required" value="{{ $business->bank_account_number ?? '' }}">
                                                    <input type="text" class="bank2" name="account_holder_name" placeholder="예금주" required="required" value="{{ $business->bank_account_holder_name ?? '' }}">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자 명의 통장사본</th>
                                            <td>
                                                <div class="f_img">
                                                    @if(!empty($business->bank_copy_image))
                                                    <div class="img_w" style="background-image:url({{ asset('front/images/bank_copies/' . $business->bank_copy_image) }})"></div>
                                                    @endif
                                                </div>
                                                <div class="fileBox">
                                                    <input type="text" class="fileName on" readonly="readonly" value="{{ $business->bank_copy_image ?? '' }}">
                                                    <label for="uploadBtn5" class="btn_file">찾아보기</label>
                                                    <input type="file" id="uploadBtn5" class="uploadBtn" name="bank_copy_image">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn t_r pt10" style="display:none;">
                                <button type="submit" class="btn_submit" style="border:0; cursor:pointer;">재인증요청</button>
                            </div>
                        </div>

                        @else
                        {{-- Box 4: 미인증 --}}
                        <div class="box box4">
                            <div class="ttl01">
                                회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 공유 및 공동구매 상품을 판매하기 위해서는 판매인증이 필요합니다. )</span>
                                <button type="button" class="btn_toggle" style="float:right; cursor:pointer; border:none; background:none; font-size:16px;">▼</button>
                            </div>

                            <div class="tb01 type2" style="display:none;">
                                <table>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160">판매인증</th>
                                            <td><strong class="fcol3">미인증</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자등록증</th>
                                            <td>
                                                <div class="fileBox">
                                                    <input type="text" class="fileName" readonly="readonly" placeholder="">
                                                    <label for="uploadBtn2" class="btn_file">찾아보기</label>
                                                    <input type="file" id="uploadBtn2" class="uploadBtn" name="address_proof_image">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자 명의 계좌번호 <br class="pc_show">(정산용)</th>
                                            <td>
                                                <div class="bank_bx">
                                                    <select name="bank_name" required="required">
                                                        <option value="" disabled selected>은행선택</option>
                                                        <option value="국민은행">국민은행</option>
                                                    </select>
                                                    <input type="text" class="bank1" name="account_number" placeholder="계좌번호 (‘-’없이 숫자만 입력)" required="required">
                                                    <input type="text" class="bank2" name="account_holder_name" placeholder="예금주" required="required">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160">사업자 명의 통장사본</th>
                                            <td>
                                                <div class="fileBox">
                                                    <input type="text" class="fileName" readonly="readonly" placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                    <label for="uploadBtn3" class="btn_file">찾아보기</label>
                                                    <input type="file" id="uploadBtn3" class="uploadBtn" name="bank_copy_image">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn t_r pt10" style="display:none;">
                                <button type="submit" class="btn_submit" style="border:0; cursor:pointer;">인증요청</button>
                            </div>
                        </div>
                    @endif
                </div>
            </form>

            <div class="box_w">
                    <div class="box box7">
                        <div class="ttl01 brb">약관동의</div>
                        <div class="agree01">
                            <div class="c_w">
                                <div class="c_ttl">회원 권한 약관</div>
                                <ul class="con_bx">
                                    <li>
                                        <div class="s_txt">
                                            <input type="checkbox" id="agree1_1" checked>
                                            <label for="agree1_1">이용약관 동의 <span class="col2">(필수)</span></label>
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
                                            <input type="checkbox" id="agree1_2" checked>
                                            <label for="agree1_2">개인정보 수집 및 이용에 관한 안내 <span class="col2">(필수)</span></label>
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
                                            <input type="checkbox" id="agree1_3" checked>
                                            <label for="agree1_3">제3자 정보제공 동의 <span class="col2">(필수)</span></label>
                                            <div class="btn">전문보기</div>
                                        </div>
                                        <div class="h_txt">
                                            제3자 정보제공 동의 내용입니다.<br>
                                            제3자 정보제공 동의 내용입니다. 제3자 정보제공 동의 내용입니다.<br><br>
                                            제3자 정보제공 동의 내용입니다. 제3자 정보제공 동의 내용입니다. 제3자 정보제공 동의 내용입니다.<br>
                                            제3자 정보제공 동의 내용입니다.
                                        </div>
                                    </li>
                                    <li>
                                        <div class="s_txt">
                                            <input type="checkbox" id="agree1_4">
                                            <label for="agree1_4">마케팅활용동의 <span class="col3">(선택)</span></label>
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
                                            <input type="checkbox" id="agree1_5">
                                            <label for="agree1_5">알림정보수신동의 <span class="col3">(선택)</span></label>
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
                            <div class="c_w">
                                <div class="c_ttl">회원사 권한 약관</div>
                                <ul class="con_bx">
                                    <li>
                                        <div class="s_txt">
                                            <input type="checkbox" id="agree2_1" checked>
                                            <label for="agree2_1">상품 판매시 약관 동의 <span class="col2">(필수)</span></label>
                                            <div class="btn">전문보기</div>
                                        </div>
                                        <div class="h_txt">
                                            상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br><br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br><br>
                                            상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다. 상품 판매시 약관 내용입니다.<br>
                                            상품 판매시 약관 내용입니다.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="c_w">
                                <div class="c_ttl">판매 권한 약관</div>
                                <ul class="con_bx">
                                    <li>
                                        <div class="s_txt">
                                            <input type="checkbox" id="agree3_1" checked>
                                            <label for="agree3_1">판매권한 약관 동의 <span class="col2">(필수)</span></label>
                                            <div class="btn">전문보기</div>
                                        </div>
                                        <div class="h_txt">
                                            판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br><br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br><br>
                                            판매권한 약관 내용입니다. 판매권한 약관 내용입니다. 판매권한 약관 내용입니다.<br>
                                            판매권한 약관 내용입니다.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- //contents -->


    <div id="daumPostcodeLayer"
        style="display:none;position:fixed;overflow:hidden;z-index:10000;-webkit-overflow-scrolling:touch;">
        <img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnCloseLayer"
            style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:1" onclick="closeDaumPostcode()"
            alt="닫기 버튼">
    </div>

    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script type="text/javascript">
        var element_layer = document.getElementById('daumPostcodeLayer');

        function closeDaumPostcode() {
            element_layer.style.display = 'none';
        }

        function execDaumPostcode(zipcodeId, addr1Id, addr2Id) {
            new daum.Postcode({
                oncomplete: function (data) {
                    var addr = '';

                    if (data.userSelectedType === 'R') {
                        addr = data.roadAddress;
                    } else {
                        addr = data.jibunAddress;
                    }

                    document.getElementById(zipcodeId).value = data.zonecode;
                    document.getElementById(addr1Id).value = addr;
                    document.getElementById(addr2Id).focus();

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

        /* 파일 */
        var uploadFile = $('.fileBox .uploadBtn');
        uploadFile.on('change', function () {
            if (window.FileReader) {
                var filename = $(this)[0].files[0].name;
            } else {
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }
            $(this).parents('.fileBox').find('.fileName').val(filename);
            $(this).parents('.fileBox').find('.fileName').addClass("on");
        });

        /* 팝업 */
        $(".pop_btn").click(function () {
            var popId = $(this).attr("data-pop");
            $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
            $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

            return false;
        });
        $(".popup_bx .close_btn").click(function () {
            $(this).parents(".popup_bx").stop().fadeOut(300);

            return false;
        });

        /* 아코디언 토글 */
        $(".btn_toggle").click(function () {
             var $content = $(this).closest('.box').find('.tb01');
             var $btnItems = $(this).closest('.box').find('.btm_btn'); // 하단 버튼도 같이 토글

             $content.stop().slideToggle(300);
             $btnItems.stop().slideToggle(300);
             
             if ($(this).text() == '▲') {
                 $(this).text('▼');
             } else {
                 $(this).text('▲');
             }
        });

        /* 약관 */
        $(".agree01 .s_txt .btn").click(function () {
            $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
        });

        function submitProfileAjaxForm($form, successMessage) {
            var formData = new FormData($form[0]);

            $.ajax({
                url: $form.attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        alert(successMessage || response.message || "저장되었습니다.");
                        window.location.reload();
                    } else {
                        alert(response.message || "처리에 실패했습니다.");
                    }
                },
                error: function (xhr) {
                    var message = "처리에 실패했습니다.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert(message);
                }
            });
        }

        $("#companyInfoForm").on("submit", function (e) {
            e.preventDefault();
            submitProfileAjaxForm($(this), "회원사 정보가 저장되었습니다.");
        });

        $("#sellerCertificationForm").on("submit", function (e) {
            e.preventDefault();
            submitProfileAjaxForm($(this), "판매권한 인증 요청이 접수되었습니다.");
        });

        $("#submitPasswordChange").click(function () {
            var $form = $("#passwordForm");
            $("#password-error, #password-success").text("");
            $("[id^='password-']").not("#password-error, #password-success").text("");

            $.ajax({
                url: $form.data("action"),
                type: "POST",
                data: {
                    _token: $form.find("input[name='_token']").val(),
                    current_password: $form.find("input[name='current_password']").val(),
                    new_password: $form.find("input[name='new_password']").val(),
                    confirm_password: $form.find("input[name='confirm_password']").val()
                },
                success: function (response) {
                    if (response.type === "success") {
                        $("#password-success").text(response.message);
                        $form.find("input[type='password']").val("");
                    } else if (response.type === "error" && response.errors) {
                        $.each(response.errors, function (key, messages) {
                            $("#password-" + key).text(messages[0]);
                        });
                    } else {
                        $("#password-error").text(response.message || "비밀번호 변경에 실패했습니다.");
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function (key, messages) {
                            $("#password-" + key).text(messages[0]);
                        });
                    } else {
                        $("#password-error").text("비밀번호 변경에 실패했습니다.");
                    }
                }
            });
        });
    </script>
@endsection
