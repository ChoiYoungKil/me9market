@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">판매자 상세 정보</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>관리자 관리</li>
                        <li>판매자 상세 정보</li>
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

                <div class="conbx">
                    <div class="con_w">
                        <div id="board">
                            <div class="write02">
                                <div class="f_bx">
                                    {{-- 회원정보입력 (Header info from Step 2/3 format) --}}
                                    <div class="f_w">
                                        <div class="f_ttl">회원정보</div>
                                        <div class="tb01">
                                            <table class="two">
                                                <tbody class="textL">
                                                    <tr>
                                                        <th class="w160"><span>회원번호</span></th>
                                                        <td>{{ $vendorDetails['id'] }}</td>
                                                        <th class="w160"><span>가입일</span></th>
                                                        <td>{{ date('Y-m-d H:i:s', strtotime($vendorDetails['created_at'])) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>아이디</span></th>
                                                        <td>{{ $vendorDetails['vendor_personal']['email'] ?? '' }}</td>
                                                        <th class="w160"><span>이름</span></th>
                                                        <td>{{ $vendorDetails['vendor_personal']['name'] ?? '' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- 회원사 정보 (Format from Step 2) --}}
                                    <div class="f_w mt40">
                                        <div class="f_ttl">회원사 정보</div>
                                        <div class="tb01 type2">
                                            <table class="two">
                                                <colgroup>
                                                    <col width="160px">
                                                    <col width="">
                                                    <col width="160px">
                                                    <col width="">
                                                </colgroup>
                                                <tbody class="textL">
                                                    <tr>
                                                        <th class="w160"><span>상호명</span></th>
                                                        <td colspan="3">{{ $vendorDetails['vendor_business']['shop_name'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>사업자구분</span></th>
                                                        <td>{{ $vendorDetails['vendor_business']['shop_business_type'] ?? '개인판매' }}</td>
                                                        <th class="w160"><span>사업자등록번호</span></th>
                                                        <td>{{ $vendorDetails['vendor_business']['business_license_number'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>연락처</span></th>
                                                        <td>{{ $vendorDetails['vendor_business']['shop_mobile'] ?? '' }}</td>
                                                        <th class="w160"><span>이메일</span></th>
                                                        <td>{{ $vendorDetails['vendor_business']['shop_email'] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>회원사 주소지</span></th>
                                                        <td colspan="3">
                                                            {{ $vendorDetails['vendor_business']['shop_address'] ?? '' }}
                                                            {{ $vendorDetails['vendor_business']['shop_city'] ?? '' }}
                                                            {{ $vendorDetails['vendor_business']['shop_state'] ?? '' }}
                                                            {{ $vendorDetails['vendor_business']['shop_country'] ?? '' }}
                                                            ({{ $vendorDetails['vendor_business']['shop_pincode'] ?? '' }})
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- 회원사 판매권한 정보 (Format from Step 3) --}}
                                    <div class="f_w mt40">
                                        <div class="f_ttl">회원사 판매권한 정보</div>
                                        <div class="tb01 type2">
                                            <table>
                                                <tbody class="textL">
                                                    <tr>
                                                        <th class="w160"><span>판매인증</span></th>
                                                        <td>
                                                            <ul class="chk01">
                                                                <li>
                                                                    <input type="radio" name="seller_status" id="status_1" value="1">
                                                                    <label for="status_1">인증</label>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" name="seller_status" id="status_0" value="0" checked>
                                                                    <label for="status_0">미인증</label>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>사업자등록증</span></th>
                                                        <td>
                                                            @if (!empty($vendorDetails['vendor_business']['address_proof_image']))
                                                                <a href="{{ url('admin/images/proofs/' . $vendorDetails['vendor_business']['address_proof_image']) }}" target="_blank">
                                                                    <img style="width: 200px" src="{{ url('admin/images/proofs/' . $vendorDetails['vendor_business']['address_proof_image']) }}">
                                                                </a>
                                                            @else
                                                                등록된 이미지가 없습니다.
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>정산용 계좌번호</span></th>
                                                        <td>
                                                            @if(!empty($vendorDetails['vendor_bank']))
                                                                {{ $vendorDetails['vendor_bank']['bank_name'] }} / 
                                                                {{ $vendorDetails['vendor_bank']['account_number'] }} 
                                                                (예금주: {{ $vendorDetails['vendor_bank']['account_holder_name'] }})
                                                            @else
                                                                등록된 계좌 정보가 없습니다.
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>정산용 통장사본</span></th>
                                                        <td>
                                                            @if (!empty($vendorDetails['vendor_business']['bank_copy_image']))
                                                                <a href="{{ url('front/images/bank_copies/' . $vendorDetails['vendor_business']['bank_copy_image']) }}" target="_blank">
                                                                    <img style="width: 200px" src="{{ url('front/images/bank_copies/' . $vendorDetails['vendor_business']['bank_copy_image']) }}">
                                                                </a>
                                                            @else
                                                                등록된 이미지가 없습니다.
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Commission Information --}}
                                    <div class="f_w mt40">
                                        <div class="f_ttl">수수료 설정</div>
                                        <div class="tb01 type2">
                                            <table class="two">
                                                <colgroup>
                                                    <col width="160px">
                                                    <col width="">
                                                </colgroup>
                                                <tbody class="textL">
                                                    <tr>
                                                        <th class="w160"><span>주문당 수수료 (%)</span></th>
                                                        <td>
                                                            <form method="post" action="{{ url('admin/update-vendor-commission') }}">
                                                                @csrf
                                                                <div class="r_btn_w">
                                                                    <input type="hidden" name="vendor_id" value="{{ $vendorDetails['id'] }}">
                                                                    <input class="w100" type="text" name="commission" value="{{ $vendorDetails['commission'] ?? '' }}" required>
                                                                    <button type="submit" class="btn01 ml10">수정</button>
                                                                </div>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btm_btn center mt40">
                                <a href="{{ url('admin/admins') }}" class="btn01 col3">목록</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection