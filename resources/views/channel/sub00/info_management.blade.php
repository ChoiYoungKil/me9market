@extends('layouts.channel')

@php
    $dep1_id = "00";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">정보 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>정보 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if (Session::has('success_message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert"
                                style="margin-bottom: 20px; background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;">
                                <strong>성공:</strong> {{ Session::get('success_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                    style="float: right; border: none; background: none; font-size: 20px; cursor: pointer;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if (Session::has('error_message'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                style="margin-bottom: 20px; background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">
                                <strong>오류:</strong> {{ Session::get('error_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                    style="float: right; border: none; background: none; font-size: 20px; cursor: pointer;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <div class="ttl01">채널 정보</div>

                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>채널 코드</span></th>
                                        <td>{{ $admin->vendor_id ?? '' }}</td>
                                        <th class="w160"><span>대표 관리자 아이디</span></th>
                                        <td>{{ $admin->email ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>비밀 번호</span></th>
                                        <td><a href="#" class="btn01 pop_btn" data-pop="pop1_1"
                                                style="background-color:#000; color:#fff; border:none; padding: 5px 15px; border-radius:3px;">비밀번호변경</a>
                                        </td>
                                        <th class="w160"><span>판매 권한</span></th>
                                        <td>획득 (
                                            {{ $vendor ? date('Y. m. d', strtotime($vendor->created_at)) : '2025. 10. 10' }}
                                            )
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="popup_bx" data-id="pop1_1">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w640">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">비밀번호 변경</div>
                                        </div>

                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="tb01">
                                                    <table>
                                                        <colgroup>
                                                            <col width="160px">
                                                            <col width="">
                                                        </colgroup>
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th>현재 비밀번호</th>
                                                                <td>
                                                                    <input class="w200" type="password"
                                                                        id="current_password" required="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>새로운 비밀번호</th>
                                                                <td>
                                                                    <input class="w200" type="password" id="new_password"
                                                                        required="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>비밀번호 확인</th>
                                                                <td>
                                                                    <input class="w200" type="password"
                                                                        id="confirm_password" required="required">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 하단버튼 -->
                                        <div class="btm_btn mt10">
                                            <a href="javascript:;" id="btnUpdatePassword">변경하기</a>
                                            <a href="#" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php
                        $brn = $details->business_license_number ?? '';
                        $brnParts = explode('-', $brn);
                        if (count($brnParts) < 3)
                            $brnParts = ['', '', ''];

                        $mobile = $vendor->mobile ?? ($details->shop_mobile ?? '');
                        $mobileParts = explode('-', $mobile);
                        if (count($mobileParts) < 3)
                            $mobileParts = ['010', '', ''];

                        $email = $admin->email ?? '';
                        $emailParts = explode('@', $email);
                        if (count($emailParts) < 2)
                            $emailParts = ['', ''];
                    @endphp
                    <form action="{{ route('channel.info.update') }}" method="post" enctype="multipart/form-data"
                        id="infoForm">
                        @csrf
                        <div class="con_w">
                            <div class="ttl01">회원사 정보</div>

                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>회원사 명</span></th>
                                            <td colspan="3">
                                                <input type="text" name="shop_name"
                                                    value="{{ $details->shop_name ?? ($vendor->name ?? '') }}"
                                                    required="required" class="w100p">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>사업자구분</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="shop_business_type" id="radio1_1"
                                                            value="개인판매" {{ ($details->shop_business_type ?? '') == '개인판매' ? 'checked' : '' }}>
                                                        <label for="radio1_1">개인판매</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="shop_business_type" id="radio1_2"
                                                            value="개인사업자" {{ ($details->shop_business_type ?? '') == '개인사업자' ? 'checked' : '' }}>
                                                        <label for="radio1_2">개인사업자</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="shop_business_type" id="radio1_3"
                                                            value="법인사업자" {{ ($details->shop_business_type ?? '') == '법인사업자' ? 'checked' : '' }}>
                                                        <label for="radio1_3">법인사업자</label>
                                                    </li>
                                                </ul>
                                            </td>
                                            <th class="w160"><span>사업자등록번호</span></th>
                                            <td>
                                                <div class="tel_bx2">
                                                    <input type="text" name="brn1" class="tel1" required="required"
                                                        value="{{ $brnParts[0] }}">
                                                    <span>-</span>
                                                    <input type="text" name="brn2" class="tel2" required="required"
                                                        value="{{ $brnParts[1] }}">
                                                    <span>-</span>
                                                    <input type="text" name="brn3" class="tel3" required="required"
                                                        value="{{ $brnParts[2] }}">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>대표연락처</span></th>
                                            <td colspan="3">
                                                <div class="tel_bx">
                                                    <select name="mobile1" required="required">
                                                        <option value="010" {{ $mobileParts[0] == '010' ? 'selected' : '' }}>
                                                            010
                                                        </option>
                                                        <option value="02" {{ $mobileParts[0] == '02' ? 'selected' : '' }}>02
                                                        </option>
                                                        <option value="031" {{ $mobileParts[0] == '031' ? 'selected' : '' }}>
                                                            031
                                                        </option>
                                                    </select>
                                                    <span>-</span>
                                                    <input type="text" name="mobile2" class="tel1" required="required"
                                                        value="{{ $mobileParts[1] }}">
                                                    <span>-</span>
                                                    <input type="text" name="mobile3" class="tel2" required="required"
                                                        value="{{ $mobileParts[2] }}">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>대표이메일</span></th>
                                            <td colspan="3">
                                                <div class="email_bx">
                                                    <input type="text" name="email1" class="email1" required="required"
                                                        value="{{ $emailParts[0] }}">
                                                    <span>@</span>
                                                    <input type="text" name="email2" class="email2" required="required"
                                                        value="{{ $emailParts[1] }}">
                                                    <select onchange="this.previousElementSibling.value=this.value;">
                                                        <option value="" selected="">직접입력</option>
                                                        <option value="naver.com">naver.com</option>
                                                        <option value="gmail.com">gmail.com</option>
                                                        <option value="daum.net">daum.net</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>회원사 주소지</span></th>
                                            <td colspan="3">
                                                <div class="addr_bx">
                                                    <div style="margin-bottom: 5px;">
                                                        <input type="text" name="shop_pincode" class="addr1 off"
                                                            placeholder="우편번호" required="required"
                                                            value="{{ $details->shop_pincode ?? '' }}"
                                                            style="width: 120px;">
                                                        <a href="#" class="btn01"
                                                            style="background-color:#000; color:#fff; border:none; padding: 5px 15px; border-radius:3px; display:inline-block; vertical-align:middle; line-height:30px;">우편번호찾기</a>
                                                    </div>
                                                    <input type="text" name="shop_address" class="addr2 off w100p"
                                                        placeholder="주소" required="required"
                                                        value="{{ $details->shop_address ?? '' }}"
                                                        style="margin-bottom: 5px;">
                                                    <input type="text" name="shop_address_detail" class="addr3 off w100p"
                                                        placeholder="상세주소" required="required"
                                                        value="{{ $details->shop_address_detail ?? '' }}">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>정산용 계좌번호</span></th>
                                            <td colspan="3">
                                                <div class="bank_bx">
                                                    <select name="bank_name" required="required">
                                                        <option value="" disabled="" selected="">은행선택</option>
                                                        <option value="국민은행" {{ ($details->bank_name ?? '') == '국민은행' ? 'selected' : '' }}>국민은행</option>
                                                        <option value="신한은행" {{ ($details->bank_name ?? '') == '신한은행' ? 'selected' : '' }}>신한은행</option>
                                                    </select>
                                                    <input type="text" name="bank_account_number" class="bank1 w400"
                                                        placeholder="계좌번호 (‘-’없이 숫자만 입력)" required="required"
                                                        value="{{ $details->bank_account_number ?? '' }}">
                                                    <input type="text" name="bank_account_holder_name" class="bank2"
                                                        placeholder="예금주" required="required"
                                                        value="{{ $details->bank_account_holder_name ?? '' }}">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>정산용 통장사본</span></th>
                                            <td colspan="3">
                                                @if(isset($details->bank_copy_image))
                                                    <div class="f_img" style="margin-bottom:10px;">
                                                        <div class="img_w"
                                                            style="background-image:url({{ asset('front/images/bank_copies/' . $details->bank_copy_image) }}); width:100px; height:70px; background-size:contain; border:1px solid #ddd;">
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="fileBox">
                                                    <input type="text" class="fileName" readonly="readonly"
                                                        value="{{ $details->bank_copy_image ?? '' }}">
                                                    <label for="uploadBtn1" class="btn_file"
                                                        style="background-color:#000; color:#fff; cursor:pointer;">찾아보기</label>
                                                    <input type="file" id="uploadBtn1" class="uploadBtn" name="bank_copy">
                                                    <div class="del_btn">삭제</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">회원사 판매권한 정보 <span class="col2">( M9 Market 의 Shop 채널에서 자사상품을 판매하기 위해서는 판매인증이
                                    필요합니다. )</span></div>

                            @php
                                // Determine certification status (mock logic for now or use $vendor->status)
                                // 0: Not Certified, 1: Waiting, 2: Certified
                                $certStatus = $vendor->status ?? 0;
                                if ($certStatus == 1) {
                                    $certStatus = 2; // Certified
                                } elseif ($details && $details->business_license_number) {
                                    $certStatus = 1; // Waiting
                                } else {
                                    $certStatus = 0; // Not Certified
                                }
                            @endphp

                            @if($certStatus == 0)
                                <!-- 3. 미인증 상태 -->
                                <div class="tb01" style="display:flex; align-items:stretch; border:1px solid #ddd;">
                                    <table style="flex:1; border:none;">
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>판매인증</span></th>
                                                <td><span style="color:#e00; font-weight:bold;">미인증</span></td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자등록증</th>
                                                <td>
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly"
                                                            placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                        <label for="uploadBtnCert1" class="btn_file"
                                                            style="background-color:#000; color:#fff;">찾아보기</label>
                                                        <input type="file" id="uploadBtnCert1" class="uploadBtn"
                                                            name="cert_brn">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자 명의 계좌번호 (정산용)</th>
                                                <td>
                                                    <div class="bank_bx">
                                                        <select name="bank_name" required="required">
                                                            <option value="" disabled="" selected="">은행선택</option>
                                                            <option value="국민은행">국민은행</option>
                                                        </select>
                                                        <input type="text" name="bank_account_number" class="bank1"
                                                            placeholder="계좌번호 (‘-’없이 숫자만 입력)" required="required"
                                                            style="width:300px;">
                                                        <input type="text" name="bank_account_holder_name" class="bank2"
                                                            placeholder="예금주" required="required">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자 명의 통장사본</th>
                                                <td>
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly"
                                                            placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                        <label for="uploadBtnCert2" class="btn_file"
                                                            style="background-color:#000; color:#fff;">찾아보기</label>
                                                        <input type="file" id="uploadBtnCert2" class="uploadBtn"
                                                            name="cert_bank_copy">
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div
                                        style="width:80px; background-color:#1b2431; color:#fff; display:flex; align-items:center; justify-content:center; text-align:center; font-weight:bold; cursor:pointer; line-height:1.4;">
                                        인증<br>요청
                                    </div>
                                </div>

                            @elseif($certStatus == 1)
                                <!-- 5. 인증대기 상태 -->
                                <div class="tb01" style="display:flex; align-items:stretch; border:1px solid #ddd;">
                                    <table style="flex:1; border:none;">
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>판매인증</span></th>
                                                <td><span style="color:#e00; font-weight:bold;">인증대기</span></td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자등록증</th>
                                                <td>
                                                    @if(isset($details->address_proof_image))
                                                        <div class="f_img" style="margin-bottom:10px;">
                                                            <div class="img_w"
                                                                style="background-image:url({{ asset('front/images/bank_copies/' . $details->address_proof_image) }}); width:100px; height:70px; background-size:contain; border:1px solid #ddd;">
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly"
                                                            value="{{ $details->address_proof_image ?? '' }}"
                                                            placeholder="사업자등록증 첨부">
                                                        <label for="uploadBtnCert3" class="btn_file"
                                                            style="background-color:#000; color:#fff;">찾아보기</label>
                                                        <input type="file" id="uploadBtnCert3" class="uploadBtn"
                                                            name="cert_brn">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자 명의 계좌번호 (정산용)</th>
                                                <td>
                                                    <div class="bank_bx">
                                                        <select name="bank_name" required="required">
                                                            <option value="국민은행" selected="">국민은행</option>
                                                        </select>
                                                        <input type="text" name="bank_account_number" class="bank1"
                                                            required="required"
                                                            value="{{ $details->bank_account_number ?? '' }}"
                                                            style="width:300px;">
                                                        <input type="text" name="bank_account_holder_name" class="bank2"
                                                            required="required"
                                                            value="{{ $details->bank_account_holder_name ?? '' }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자 명의 통장사본</th>
                                                <td>
                                                    @if(isset($details->bank_copy_image))
                                                        <div class="f_img" style="margin-bottom:10px;">
                                                            <div class="img_w"
                                                                style="background-image:url({{ asset('front/images/bank_copies/' . $details->bank_copy_image) }}); width:100px; height:70px; background-size:contain; border:1px solid #ddd;">
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly"
                                                            value="{{ $details->bank_copy_image ?? '' }}"
                                                            placeholder="입력한 계좌번호와 동일한 통장첨부">
                                                        <label for="uploadBtnCert4" class="btn_file"
                                                            style="background-color:#000; color:#fff;">찾아보기</label>
                                                        <input type="file" id="uploadBtnCert4" class="uploadBtn"
                                                            name="cert_bank_copy">
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div id="btnRequestCert"
                                        style="width:80px; background-color:#1b2431; color:#fff; display:flex; align-items:center; justify-content:center; text-align:center; font-weight:bold; cursor:pointer; line-height:1.4;">
                                        재인증<br>요청
                                    </div>
                                </div>

                            @elseif($certStatus == 2)
                                <!-- 6. 인증완료 상태 -->
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>판매인증</span></th>
                                                <td><span style="color:#3470f7; font-weight:bold;">인증완료 ( 인증완료일 :
                                                        {{ $vendor->certified_at ?? '2024-01-01' }} )</span></td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자등록증</th>
                                                <td>
                                                    <div class="f_img">
                                                        <div class="img_w"
                                                            style="background-image:url({{ isset($details->address_proof_image) ? asset('front/images/bank_copies/' . $details->address_proof_image) : asset('channel_assets/images/sub/thumbnail02.jpg') }}); width:60px; height:45px; display:inline-block; vertical-align:middle; border:1px solid #ddd; background-size:contain;">
                                                        </div>
                                                        <span class="f_txt" style="margin-left:10px;">사업자등록번호 /
                                                            {{ $details->business_license_number ?? '000-01-00000' }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160">사업자 명의 계좌번호 (정산용)</th>
                                                <td>
                                                    <div class="f_down">
                                                        <div class="f_name" style="display:inline-block; margin-right:20px;">
                                                            {{ $details->bank_name ?? '국민은행' }} /
                                                            {{ $details->bank_account_number ?? '000000000-02-000000' }} /
                                                            {{ $details->bank_account_holder_name ?? '통장주' }}
                                                        </div>
                                                        <a href="#" class="btn01"
                                                            style="background-color:#000; color:#fff; border:none; padding:5px 15px; border-radius:3px;">통장사본
                                                            내려받기</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="con_w">
                            <div class="ttl01 brb">약관동의</div>
                            <div class="agree01">
                                <div class="c_w" style="border-bottom: 1px solid #eee; padding: 20px 0;">
                                    <div class="s_txt"
                                        style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <input type="checkbox" id="agree1_1"
                                                style="width:20px; height:20px; vertical-align:middle; accent-color: #28a745;"
                                                checked>
                                            <label for="agree1_1" style="font-weight:bold; margin-left:10px;">회원사 권한 약관
                                                <span class="col2" style="color:#28a745; margin-left:10px;">{{-- <i
                                                        class="fa fa-check-circle"></i> --}} 상품 판매시 약관 동의
                                                    (필수)</span></label>
                                        </div>
                                        <div class="btn"
                                            style="background-color:#888; color:#fff; padding:5px 15px; border-radius:3px; font-size:12px; cursor:pointer;">
                                            전문보기</div>
                                    </div>
                                    <div class="h_txt"
                                        style="display:none; background:#f9f9f9; padding:15px; margin-top:10px; border-radius:5px;">
                                        상품 판매시 약관 내용입니다.
                                    </div>
                                </div>

                                <div class="c_w" style="padding: 20px 0;">
                                    <div class="s_txt"
                                        style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <input type="checkbox" id="agree2_1"
                                                style="width:20px; height:20px; vertical-align:middle; accent-color: #28a745;"
                                                checked>
                                            <label for="agree2_1" style="font-weight:bold; margin-left:10px;">판매 권한 약관 <span
                                                    class="col2" style="color:#28a745; margin-left:10px;">판매권한 약관 동의
                                                    (필수)</span></label>
                                        </div>
                                        <div class="btn"
                                            style="background-color:#888; color:#fff; padding:5px 15px; border-radius:3px; font-size:12px; cursor:pointer;">
                                            전문보기</div>
                                    </div>
                                    <div class="h_txt"
                                        style="display:none; background:#f9f9f9; padding:15px; margin-top:10px; border-radius:5px;">
                                        판매권한 약관 내용입니다.
                                    </div>
                                </div>
                            </div>

                            <div class="btm_btn mt10" style="text-align:center;">
                                <button type="submit"
                                    style="background-color:#000; color:#fff; padding:15px 60px; font-size:16px; font-weight:bold; display:inline-block; border-radius:5px; border:none; cursor:pointer;">정보
                                    수정</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
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
        $(".fileBox .del_btn").click(function () {
            $(this).siblings("input").val("");
            $(this).siblings(".fileName").removeClass("on");
        });

        /* 약관 */
        $(".agree01 .s_txt .btn").click(function () {
            $(this).parent(".s_txt").siblings(".h_txt").stop().slideToggle(300);
        });

        /* 비밀번호 변경 AJAX */
        $("#btnUpdatePassword").click(function () {
            var current_password = $("#current_password").val();
            var new_password = $("#new_password").val();
            var confirm_password = $("#confirm_password").val();

            if (!current_password || !new_password || !confirm_password) {
                alert("모든 항목을 입력해 주세요.");
                return;
            }

            if (new_password !== confirm_password) {
                alert("새로운 비밀번호와 확인 비밀번호가 일치하지 않습니다.");
                return;
            }

            $.ajax({
                url: "{{ route('channel.update_password') }}",
                type: "POST",
                data: {
                    current_password: current_password,
                    new_password: new_password,
                    confirm_password: confirm_password,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === "success") {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert("비밀번호 변경 중 오류가 발생했습니다.");
                }
            });
        });

        /* 재인증 요청 */
        $("#btnRequestCert").click(function () {
            alert("관리자에게 재인증 요청이 전달되었습니다. 심사가 완료되면 상태가 변경됩니다.");
        });
    </script>
@endpush