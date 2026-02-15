@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">{{ $title }}</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>관리자 관리</li>
                        <li>{{ $title }}</li>
                    </ul>
                </div>

                @if(Session::has('error_message'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px;">
                        <strong>오류: </strong> {{ Session::get('error_message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(Session::has('success_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 20px;">
                        <strong>성공: </strong> {{ Session::get('success_message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <style>
                    .f_ttl {
                        font-size: 20px !important;
                        font-weight: 800 !important;
                        color: #111 !important;
                        margin-bottom: 25px !important;
                        border-left: 5px solid #3470f7;
                        padding-left: 15px;
                    }
                </style>

                <div class="conbx">
                    <div class="con_w">
                        <form @if(empty($admin['id'])) action="{{ url('admin/add-edit-admin') }}" @else
                        action="{{ url('admin/add-edit-admin/' . $admin['id']) }}" @endif method="post"
                            enctype="multipart/form-data">
                            @csrf
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <div class="f_w">
                                            <div class="f_ttl">기본 정보</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>아이디 (이메일)</span></th>
                                                            <td>
                                                                <input type="email" name="email" class="w300" required
                                                                    value="{{ !empty($admin['email']) ? $admin['email'] : old('email') }}">
                                                            </td>
                                                            <th class="w160"><span>이름</span></th>
                                                            <td>
                                                                <input type="text" name="name" class="w300" required
                                                                    value="{{ !empty($admin['name']) ? $admin['name'] : old('name') }}">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>구분</span></th>
                                                            <td>
                                                                <select name="type" id="admin_type" class="w300" @if(!empty($admin['id'])) readonly style="background-color: #e9ecef;" @endif>
                                                                    <option value="">구분 선택</option>
                                                                    <option value="admin" @if(isset($admin['type']) && $admin['type'] == 'admin') selected @endif>관리자</option>
                                                                    <option value="subadmin" @if(isset($admin['type']) && $admin['type'] == 'subadmin') selected @endif>서브 관리자</option>
                                                                    <option value="vendor" @if(isset($admin['type']) && $admin['type'] == 'vendor') selected @endif>판매자</option>
                                                                </select>
                                                                @if(!empty($admin['id']))
                                                                    <input type="hidden" name="type" value="{{ $admin['type'] }}">
                                                                @endif
                                                            </td>
                                                            <th class="w160"><span>연락처</span></th>
                                                            <td>
                                                                <div class="r_btn_w w457">
                                                                    <div class="tel_bx">
                                                                        @php
                                                                            $mobileParts = !empty($admin['mobile']) ? explode('-', $admin['mobile']) : ['', '', ''];
                                                                            $m1 = $mobileParts[0] ?? '010';
                                                                            $m2 = $mobileParts[1] ?? '';
                                                                            $m3 = $mobileParts[2] ?? '';
                                                                        @endphp
                                                                        <select name="mobile_1" required="required">
                                                                            <option value="010" {{ $m1 == '010' ? 'selected' : '' }}>010</option>
                                                                            <option value="011" {{ $m1 == '011' ? 'selected' : '' }}>011</option>
                                                                            <option value="016" {{ $m1 == '016' ? 'selected' : '' }}>016</option>
                                                                            <option value="017" {{ $m1 == '017' ? 'selected' : '' }}>017</option>
                                                                            <option value="018" {{ $m1 == '018' ? 'selected' : '' }}>018</option>
                                                                            <option value="019" {{ $m1 == '019' ? 'selected' : '' }}>019</option>
                                                                        </select>
                                                                        <span>-</span>
                                                                        <input type="text" class="tel1" name="mobile_2" required="required" value="{{ $m2 }}">
                                                                        <span>-</span>
                                                                        <input type="text" class="tel2" name="mobile_3" required="required" value="{{ $m3 }}">
                                                                    </div>
                                                                    <a href="javascript:alert('본인인증 서비스는 준비중입니다.');" class="btn01 col2">본인인증</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>비밀번호</span></th>
                                                            <td>
                                                                <input type="password" name="password" class="w300"
                                                                    placeholder="{{ !empty($admin['id']) ? '변경 시에만 입력' : '비밀번호 입력' }}" @if(empty($admin['id'])) required @endif>
                                                            </td>
                                                            <th class="w160"><span>상태</span></th>
                                                            <td>
                                                                <ul class="chk01">
                                                                    <li>
                                                                        <input type="radio" name="status" id="status_active" value="1" @if(!isset($admin['status']) || $admin['status'] == 1) checked @endif>
                                                                        <label for="status_active">활성</label>
                                                                    </li>
                                                                    <li>
                                                                        <input type="radio" name="status" id="status_inactive" value="0" @if(isset($admin['status']) && $admin['status'] == 0) checked @endif>
                                                                        <label for="status_inactive">비활성</label>
                                                                    </li>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>프로필 이미지</span></th>
                                                            <td colspan="3">
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" readonly="readonly" placeholder="이미지 이름">
                                                                    <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                                    <input type="file" id="uploadBtn" class="uploadBtn" name="image">
                                                                </div>
                                                                @if(!empty($admin['image']))
                                                                    <div style="margin-top: 10px;">
                                                                        <a target="_blank" href="{{ url('admin/images/photos/' . $admin['image']) }}">이미지 보기</a>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div id="vendor_sections" style="{{ ($admin->type == 'vendor') ? '' : 'display:none;' }}">
                                            <!-- 회원사 정보 (Step 2와 동일) -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">회원사 정보 (Step 2)</div>
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>상호명</span></th>
                                                                <td>
                                                                    <input type="text" name="shop_name" class="w300" value="{{ $admin->vendorBusiness->shop_name ?? old('shop_name') }}">
                                                                </td>
                                                                <th class="w160"><span>사업자 구분</span></th>
                                                                <td>
                                                                    @php $btype = $admin->vendorBusiness->shop_business_type ?? old('shop_business_type'); @endphp
                                                                    <ul class="chk01">
                                                                        <li>
                                                                            <input type="radio" name="shop_business_type" id="btype_1" value="개인사업자" {{ $btype == '개인사업자' ? 'checked' : '' }}>
                                                                            <label for="btype_1">개인사업자</label>
                                                                        </li>
                                                                        <li>
                                                                            <input type="radio" name="shop_business_type" id="btype_2" value="법인사업자" {{ $btype == '법인사업자' ? 'checked' : '' }}>
                                                                            <label for="btype_2">법인사업자</label>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>사업자등록번호</span></th>
                                                                <td>
                                                                    @php
                                                                        $bln = !empty($admin->vendorBusiness->business_license_number) ? explode('-', $admin->vendorBusiness->business_license_number) : ['', '', ''];
                                                                    @endphp
                                                                    <div class="tel_bx2" style="display: flex; align-items: center; gap: 5px;">
                                                                        <input type="text" name="business_license_1" style="width: 60px;" value="{{ $bln[0] ?? '' }}">
                                                                        <span>-</span>
                                                                        <input type="text" name="business_license_2" style="width: 40px;" value="{{ $bln[1] ?? '' }}">
                                                                        <span>-</span>
                                                                        <input type="text" name="business_license_3" style="width: 80px;" value="{{ $bln[2] ?? '' }}">
                                                                    </div>
                                                                </td>
                                                                <th class="w160"><span>회원사 연락처</span></th>
                                                                <td>
                                                                    <input type="text" name="shop_mobile" class="w300" value="{{ $admin->vendorBusiness->shop_mobile ?? old('shop_mobile') }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>회원사 주소지</span></th>
                                                                <td colspan="3">
                                                                    <div class="addr_bx">
                                                                        <input type="text" name="zipcode" id="zipcode" class="addr1 off" placeholder="우편번호" value="{{ $admin->vendorBusiness->shop_pincode ?? '' }}" readonly>
                                                                        <a href="javascript:;" onclick="execDaumPostcode()" class="btn01">우편번호찾기</a>
                                                                        <input type="text" name="address1" id="address1" class="addr2 off" placeholder="주소" value="{{ $admin->vendorBusiness->shop_address ?? '' }}" readonly>
                                                                        <input type="text" name="address2" id="address2" class="addr3 off" placeholder="상세주소" value="{{ $admin->vendorBusiness->shop_address_detail ?? '' }}">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- 회원사 판매권한 정보 (Step 3와 동일) -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">회원사 판매권한 정보 (Step 3)</div>
                                                <div class="tb01 type2">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>판매인증</span></th>
                                                                <td colspan="3">
                                                                    <ul class="chk01">
                                                                        @php $sellerStatus = $admin->vendorPersonal->status ?? 0; @endphp
                                                                        <li>
                                                                            <input type="radio" name="seller_status" id="seller_status_1" value="1" {{ $sellerStatus == 1 ? 'checked' : '' }}>
                                                                            <label for="seller_status_1">인증</label>
                                                                        </li>
                                                                        <li>
                                                                            <input type="radio" name="seller_status" id="seller_status_0" value="0" {{ $sellerStatus == 0 ? 'checked' : '' }}>
                                                                            <label for="seller_status_0">미인증</label>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>사업자등록증</span></th>
                                                                <td colspan="3">
                                                                    <div class="fileBox">
                                                                        <input type="text" class="fileName" readonly="readonly" placeholder="사업자등록증 이미지">
                                                                        <label for="uploadLicense" class="btn_file">찾아보기</label>
                                                                        <input type="file" id="uploadLicense" class="uploadBtn" name="address_proof_image">
                                                                    </div>
                                                                    @if(!empty($admin->vendorBusiness->address_proof_image))
                                                                        <div style="margin-top: 10px;">
                                                                            <a target="_blank" href="{{ asset('front/images/bank_copies/' . $admin->vendorBusiness->address_proof_image) }}" style="color:blue; text-decoration:underline;">[기존 등록증 보기]</a>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>정산 계좌번호</span></th>
                                                                <td colspan="3">
                                                                    <div class="bank_bx" style="display: flex; gap: 10px;">
                                                                        <select name="bank_name" class="w150">
                                                                            <option value="">은행선택</option>
                                                                            @php $banks = ['국민은행', '신한은행', '우리은행', '하나은행', '농협은행', 'IBK기업은행', '수협은행', 'SC제일은행', '씨티은행', '대구은행', '부산은행', '광주은행', '제주은행', '전북은행', '경남은행', '새마을금고', '신협', '우체국', '케이뱅크', '카카오뱅크', '토스뱅크']; @endphp
                                                                            @foreach($banks as $bank)
                                                                                <option value="{{ $bank }}" {{ (isset($admin->vendorBank->bank_name) && $admin->vendorBank->bank_name == $bank) ? 'selected' : '' }}>{{ $bank }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input type="text" name="account_number" class="w300" placeholder="계좌번호 (‘-’없이 숫자만 입력)" value="{{ $admin->vendorBank->account_number ?? '' }}">
                                                                        <input type="text" name="account_holder_name" class="w150" placeholder="예금주" value="{{ $admin->vendorBank->account_holder_name ?? '' }}">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>정산용 통장사본</span></th>
                                                                <td colspan="3">
                                                                    <div class="fileBox">
                                                                        <input type="text" class="fileName" readonly="readonly" placeholder="통장사본 이미지">
                                                                        <label for="uploadBankbook" class="btn_file">찾아보기</label>
                                                                        <input type="file" id="uploadBankbook" class="uploadBtn" name="bank_copy_image">
                                                                    </div>
                                                                    @if(!empty($admin->vendorBank->bank_copy_image))
                                                                        <div style="margin-top: 10px;">
                                                                            <a target="_blank" href="{{ asset('front/images/bank_copies/' . $admin->vendorBank->bank_copy_image) }}" style="color:blue; text-decoration:underline;">[기존 통장사본 보기]</a>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="btm_btn center mt40" style="display: flex; justify-content: center; gap: 10px;">
                                    <a href="{{ url('admin/admins') }}" class="btn01 col3">취소</a>
                                    <button type="submit" class="btn01 col5">저장</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    <!-- 다음 우편번호 레이어 -->
    <div id="daumPostcodeLayer" style="display:none;position:fixed;overflow:hidden;z-index:10000;-webkit-overflow-scrolling:touch; border:1px solid #333; background:#fff;">
        <img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnCloseLayer" style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:1" onclick="closeDaumPostcode()" alt="닫기 버튼">
    </div>
    </div>
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script>
        $(document).ready(function() {
            $('#admin_type').on('change', function() {
                if ($(this).val() == 'vendor') {
                    $('#vendor_sections').show();
                } else {
                    $('#vendor_sections').hide();
                }
            });
        });

        var element_layer = document.getElementById('daumPostcodeLayer');

        function closeDaumPostcode() {
            element_layer.style.display = 'none';
        }

        function execDaumPostcode() {
            new daum.Postcode({
                oncomplete: function (data) {
                    var addr = '';
                    var extraAddr = '';

                    if (data.userSelectedType === 'R') {
                        addr = data.roadAddress;
                    } else {
                        addr = data.jibunAddress;
                    }

                    if (data.userSelectedType === 'R') {
                        if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                            extraAddr += data.bname;
                        }
                        if (data.buildingName !== '' && data.apartment === 'Y') {
                            extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                        }
                        if (extraAddr !== '') {
                            extraAddr = ' (' + extraAddr + ')';
                        }
                    }

                    document.getElementById('zipcode').value = data.zonecode;
                    document.getElementById("address1").value = addr + extraAddr;
                    document.getElementById("address2").focus();
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
            var width = 450;
            var height = 500;
            var borderWidth = 1;

            element_layer.style.width = width + 'px';
            element_layer.style.height = height + 'px';
            element_layer.style.border = borderWidth + 'px solid #333';
            element_layer.style.left = (((window.innerWidth || document.documentElement.clientWidth) - width) / 2) + 'px';
            element_layer.style.top = (((window.innerHeight || document.documentElement.clientHeight) - height) / 2) + 'px';
        }

        var uploadFile = $('.fileBox .uploadBtn');
        uploadFile.on('change', function () {
            if (window.FileReader) {
                var filename = $(this)[0].files[0].name;
            } else {
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }
            $(this).parents('.fileBox').find('.fileName').val(filename);
        });
    </script>
@endsection