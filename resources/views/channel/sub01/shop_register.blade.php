@extends('layouts.channel')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">Shop채널등록</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>Shop채널관리</li>
                        <li>Shop채널등록</li>
                    </ul>
                </div>

                <div class="conbx">
                    <form id="shopRegisterForm" action="{{ route('channel.shop_register.submit') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger" style="background: #fff5f5; color: #e53e3e; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #feb2b2;">
                                <ul style="list-style: none; padding: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li style="margin-bottom: 5px;">• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="con_w">
                            <!-- 기본정보 -->
                            <div class="ttl01">Shop 채널 기본정보</div>
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
                                            <th class="w160"><span>Shop 채널코드</span></th>
                                            @php
                                                $auto_code = 'Me9-' . date('Y-md') . rand(10, 99);
                                            @endphp
                                            <td>{{ $auto_code }}<input type="hidden" name="channel_code"
                                                    value="{{ $auto_code }}"></td>
                                            <th class="w160"><span>채널상태</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="status" id="radio1_1" value="1" {{ old('status') == '1' ? 'checked' : '' }}>
                                                        <label for="radio1_1">운영</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="status" id="radio1_2" value="0" {{ old('status', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio1_2">중지</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>공개여부</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="is_public" value="1" id="radio2_1" {{ old('is_public', '1') == '1' ? 'checked' : '' }}
                                                            class="pw_toggle">
                                                        <label for="radio2_1">공개</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="is_public" value="0" id="radio2_2" {{ old('is_public') == '0' ? 'checked' : '' }} class="pw_toggle">
                                                        <label for="radio2_2">비공개</label>
                                                        <input type="password" name="password" class="pw_input" placeholder="Password keyword"
                                                            value="{{ old('password') }}"
                                                            style="display: {{ old('is_public') == '0' ? 'block' : 'none' }}; width: 180px; margin-left: 10px;">
                                                    </li>
                                                </ul>
                                            </td>
                                            <th class="w160"><span>구매권한</span></th>
                                            <td>
                                                <ul class="chk02">
                                                    <li>
                                                        <input type="checkbox" name="is_member_only" value="1" id="chk1_1" {{ old('is_member_only') == '1' ? 'checked' : '' }}>
                                                        <label for="chk1_1">회원전용</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 채널명</span></th>
                                            <td colspan="3">
                                                <input type="text" name="channel_name" value="{{ old('channel_name') }}" required="required">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>카피라이트</span></th>
                                            <td colspan="3">
                                                <input type="text" name="copyright" value="{{ old('copyright') }}" required="required">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>그룹 keyword</span></th>
                                            <td colspan="3">
                                                <div class="keyword_wrap"
                                                    style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                    <input type="text" id="keyword_input" placeholder="Keyword 입력"
                                                        style="width: 250px;">
                                                    <div id="keyword_list"
                                                        style="display: none; flex-wrap: wrap; gap: 5px; align-items: center;">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 사용기간 -->
                            <div class="ttl01 mt40">Shop 채널 사용주기</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>사용기간 여부</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="use_period_type" id="radio3_1" value="0" {{ old('use_period_type', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio3_1">무기한</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="use_period_type" id="radio3_2" value="1" {{ old('use_period_type') == '1' ? 'checked' : '' }}
                                                            class="use_period_toggle">
                                                        <label for="radio3_2">기간제</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr class="use_period_bx" style="display: {{ old('use_period_type') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>사용기간</span></th>
                                            <td>
                                                <div class="date_bx" style="display: flex; align-items: center; gap: 5px;">
                                                    <input type="text" name="start_date" class="datepicker" value="{{ old('start_date') }}"
                                                        style="width: 120px;" readonly placeholder="날짜 선택">
                                                    <select name="start_hour" class="w160">
                                                        <option value="">시 선택</option>
                                                        @for($i = 0; $i < 24; $i++)
                                                            <option value="{{ $i }}" {{ old('start_hour') !== null && old('start_hour') == $i ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}시</option>
                                                        @endfor
                                                    </select>
                                                    <span>~</span>
                                                    <input type="text" name="end_date" class="datepicker" value="{{ old('end_date') }}"
                                                        style="width: 120px;" readonly placeholder="날짜 선택">
                                                    <select name="end_hour" class="w160">
                                                        <option value="">시 선택</option>
                                                        @for($i = 0; $i < 24; $i++)
                                                            <option value="{{ $i }}" {{ old('end_hour') !== null && old('end_hour') == $i ? 'selected' : '' }}>{{ sprintf('%02d', $i) }}시</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 로고 이미지 -->
                            <div class="ttl01 mt40">Shop 채널 로고 이미지 <span class="col2 fs2">( Shop 채널 로고를 생략 할 경우 채널명 노출
                                    )</span>
                            </div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>로고 사용여부</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="use_logo" value="0" id="radio4_1" {{ old('use_logo', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio4_1">미사용</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="use_logo" value="1" id="radio4_2" {{ old('use_logo') == '1' ? 'checked' : '' }}>
                                                        <label for="radio4_2">이미지 사용</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr class="logo_img_bx" style="display: {{ old('use_logo') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>로고 이미지</span></th>
                                            <td>
                                                <div class="file_bx" style="display: flex; align-items: center; gap: 5px;">
                                                    <input type="text" class="file_name w300" readonly>
                                                    <label for="logo_file"
                                                        style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; cursor: pointer; border-radius: 2px;">찾아보기</label>
                                                    <input type="file" name="logo_image" id="logo_file" class="file_input"
                                                        style="display: none;">
                                                    <span class="col2 fs2" style="margin-left: 10px;">최적사이즈 000px *
                                                        000px</span>
                                                </div>
                                                @error('logo_image')
                                                    <span class="text-danger" style="color: #e53e3e; font-size: 12px; margin-top: 5px; display: block;">{{ $message }} (파일을 다시 선택해주세요)</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 메인 배너 이미지 -->
                            <div class="ttl01 mt40">메인 배너 이미지</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>메인 배너 사용여부</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="use_banner" value="0" id="radio5_1" {{ old('use_banner', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio5_1">미사용</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="use_banner" value="1" id="radio5_2" {{ old('use_banner') == '1' ? 'checked' : '' }}>
                                                        <label for="radio5_2">이미지로 등록</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr class="banner_img_bx" style="display: {{ old('use_banner') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>배너 이미지</span></th>
                                            <td id="banner_container">
                                                @if(old('banner_files') && count(old('banner_files')) > 0)
                                                    @foreach(old('banner_files') as $key => $banner_file)
                                                        <div class="file_bx banner_row"
                                                            style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                                                            <input type="text" class="file_name w300" readonly value="{{ old('banner_files_names.' . $key) }}">
                                                            <label class="btn_file"
                                                                style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; cursor: pointer; border-radius: 2px;">찾아보기
                                                                <input type="file" name="banner_files[]" class="file_input"
                                                                    style="display: none;">
                                                            </label>
                                                            <span class="col2 fs2" style="margin-left: 10px;">최적사이즈 000px *
                                                                000px</span>
                                                            @if($key == 0)
                                                                <button type="button" class="btn_add_banner"
                                                                    style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; border-radius: 2px; margin-left: auto;">추가하기</button>
                                                            @else
                                                                <button type="button" class="btn_del_banner" style="background: #888; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; border-radius: 2px; border: none; cursor: pointer;">삭제</button>
                                                            @endif
                                                        </div>
                                                        @error('banner_files.' . $key)
                                                            <span class="text-danger" style="color: #e53e3e; font-size: 12px; margin-top: 5px; display: block;">{{ $message }} (파일을 다시 선택해주세요)</span>
                                                        @enderror
                                                    @endforeach
                                                @else
                                                    <div class="file_bx banner_row"
                                                        style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                                                        <input type="text" class="file_name w300" readonly>
                                                        <label class="btn_file"
                                                            style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; cursor: pointer; border-radius: 2px;">찾아보기
                                                            <input type="file" name="banner_files[]" class="file_input"
                                                                style="display: none;">
                                                        </label>
                                                        <span class="col2 fs2" style="margin-left: 10px;">최적사이즈 000px *
                                                            000px</span>
                                                        <button type="button" class="btn_add_banner"
                                                            style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; border-radius: 2px; margin-left: auto;">추가하기</button>
                                                    </div>
                                                    @error('banner_files.0')
                                                        <span class="text-danger" style="color: #e53e3e; font-size: 12px; margin-top: 5px; display: block;">{{ $message }} (파일을 다시 선택해주세요)</span>
                                                    @enderror
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- OG TAG -->
                            <div class="ttl01 mt40">OG (오픈 그래프) TAG <span class="col2 fs2">( SNS에 게시되는데 최적화된 데이터 )</span>
                            </div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>OG 사용여부</span></th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="use_og" value="0" id="radio6_1" {{ old('use_og', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio6_1">미사용</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="use_og" value="1" id="radio6_2" {{ old('use_og') == '1' ? 'checked' : '' }}>
                                                        <label for="radio6_2">사용</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr class="og_tag_bx" style="display: {{ old('use_og') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>OG TAG</span></th>
                                            <td>
                                                <div class="og_input_wrap">
                                                    <div
                                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                                        <span style="width: 80px; font-weight: 500;">Title</span>
                                                        <input type="text" name="og_title" value="{{ old('og_title') }}" placeholder="Title"
                                                            style="flex: 1;">
                                                    </div>
                                                    <div
                                                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                                        <span style="width: 80px; font-weight: 500;">Description</span>
                                                        <input type="text" name="og_description" value="{{ old('og_description') }}" placeholder="Description"
                                                            style="flex: 1;">
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <span style="width: 80px; font-weight: 500;">Image</span>
                                                        <div class="file_bx"
                                                            style="display: flex; align-items: center; gap: 5px; flex: 1;">
                                                            <input type="text" class="file_name" readonly
                                                                style="width: 300px;">
                                                            <label class="btn_file"
                                                                style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; cursor: pointer; border-radius: 2px;">찾아보기
                                                                <input type="file" name="og_image" class="file_input"
                                                                    style="display: none;">
                                                            </label>
                                                            <span class="col2 fs2" style="margin-left: 10px;">최적사이즈 000px *
                                                                000px</span>
                                                        </div>
                                                    </div>
                                                    @error('og_image')
                                                        <span class="text-danger" style="color: #e53e3e; font-size: 12px; margin-top: 5px; display: block;">{{ $message }} (파일을 다시 선택해주세요)</span>
                                                    @enderror
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="ttl01 mt40">Shop 채널 PG 정보 <span class="col2 fs2">( 자사 PG를 사용하는 경우 자사상품만 판매 가능하며, 적립 포인트 제공이 불가능합니다. )</span></div>
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
                                            <th class="w160"><span>자사 PG 사용여부</span></th>
                                            <td colspan="3">
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="use_own_pg" value="0" id="radio_pg_1" {{ old('use_own_pg', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio_pg_1">미사용</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="use_own_pg" value="1" id="radio_pg_2" {{ old('use_own_pg') == '1' ? 'checked' : '' }}>
                                                        <label for="radio_pg_2">사용</label>
                                                    </li>
                                                </ul>
                                                <div class="col2 fs2 mt5">판매인증 완료 채널만 사용할 수 있습니다. 사용 시 공유상품/제휴상품 탭과 지급포인트 설정이 제한됩니다.</div>
                                            </td>
                                        </tr>
                                        <tr class="own_pg_row" style="display: {{ old('use_own_pg') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>자사 PG 모듈</span></th>
                                            <td>
                                                <select name="pg_provider" class="w160">
                                                    <option value="">PG사 선택</option>
                                                    <option value="inicis" {{ old('pg_provider') == 'inicis' ? 'selected' : '' }}>KG 이니시스</option>
                                                    <option value="kcp" {{ old('pg_provider') == 'kcp' ? 'selected' : '' }}>NHN KCP</option>
                                                    <option value="toss" {{ old('pg_provider') == 'toss' ? 'selected' : '' }}>토스페이먼츠</option>
                                                </select>
                                            </td>
                                            <th class="w160"><span>상점 ID</span></th>
                                            <td><input type="text" name="pg_merchant_id" value="{{ old('pg_merchant_id') }}" class="wFull"></td>
                                        </tr>
                                        <tr class="own_pg_row" style="display: {{ old('use_own_pg') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>사이트코드</span></th>
                                            <td><input type="text" name="pg_site_code" value="{{ old('pg_site_code') }}" class="wFull"></td>
                                            <th class="w160"><span>Client Key</span></th>
                                            <td><input type="text" name="pg_client_key" value="{{ old('pg_client_key') }}" class="wFull"></td>
                                        </tr>
                                        <tr class="own_pg_row" style="display: {{ old('use_own_pg') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>Secret Key</span></th>
                                            <td colspan="3"><input type="password" name="pg_secret_key" class="wFull"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 관리자 정보 -->
                            <div class="ttl01 mt40">Shop 채널 (모니터링) 관리자 정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                        <col width="120px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>Shop 채널 관리자 여부</span></th>
                                            <td colspan="3">
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="use_admin" value="0" id="radio7_1" {{ old('use_admin', '0') == '0' ? 'checked' : '' }}>
                                                        <label for="radio7_1">미사용</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="use_admin" value="1" id="radio7_2" {{ old('use_admin') == '1' ? 'checked' : '' }}>
                                                        <label for="radio7_2">사용</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr class="admin_detail_row" style="display: {{ old('use_admin') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>Shop 채널 관리자 성명</span></th>
                                            <td colspan="3">
                                                <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="wFull">
                                            </td>
                                        </tr>
                                        <tr class="admin_detail_row" style="display: {{ old('use_admin') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>로그인 ID</span></th>
                                            <td>
                                                <input type="text" name="admin_login_id" value="{{ old('admin_login_id') }}" class="w160">
                                            </td>
                                            <th class="w160"><span>로그인 PW</span></th>
                                            <td>
                                                <input type="password" name="admin_password" class="w160">
                                            </td>
                                        </tr>
                                        <tr class="admin_detail_row" style="display: {{ old('use_admin') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>정산(지급)방법</span></th>
                                            <td colspan="3">
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="settlement_type" id="radio8_1" value="1" {{ old('settlement_type', '1') == '1' ? 'checked' : '' }}>
                                                        <label for="radio8_1">판매금액 대비 % 지급</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="settlement_type" id="radio8_2" value="2" {{ old('settlement_type') == '2' ? 'checked' : '' }}>
                                                        <label for="radio8_2">판매 개당 금액</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr class="admin_detail_row" style="display: {{ old('use_admin') == '1' ? 'table-row' : 'none' }};">
                                            <th class="w160"><span>정산요율</span></th>
                                            <td colspan="3">
                                                <div class="settle_rate settle_rate_1"
                                                    style="display: {{ old('settlement_type', '1') == '1' ? 'flex' : 'none' }}; align-items: center; gap: 5px;">
                                                    <input type="text" name="settlement_rate_percent" value="{{ old('settlement_rate_percent') }}" class="w160" inputmode="decimal">
                                                    <span>% ( 총 판매금액에서 지급되는 % )</span>
                                                </div>
                                                <div class="settle_rate settle_rate_2"
                                                    style="display: {{ old('settlement_type') == '2' ? 'flex' : 'none' }}; align-items: center; gap: 5px;">
                                                    <input type="text" name="settlement_rate_amount" value="{{ old('settlement_rate_amount') }}" class="w160" inputmode="numeric" pattern="[0-9]*">
                                                    <span>원 ( 판매당 지급되는 비용 )</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn mt40">
                                <button type="submit" class="col7"
                                    style="width: 140px; height: 48px; border-radius: 5px; font-size: 14px; font-weight: 700; border: none; cursor: pointer;">등록</button>
                                <a href="{{ route('channel.shop_list') }}" class="col3"
                                    style="max-width: 140px; line-height: 48px; border-radius: 5px; font-size: 14px; font-weight: 700; text-align: center;">목록</a>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function () {
            // 비공개 비밀번호 토글
            $(".pw_toggle").change(function () {
                if ($("#radio2_2").is(":checked")) {
                    $(".pw_input").stop().fadeIn(200).focus();
                } else {
                    $(".pw_input").stop().fadeOut(200).val("");
                }
            });

            // 상점 등록 폼 제출 시 키워드 데이터 포함
            $("#shopRegisterForm").on("submit", function (e) {
                // 기존 키워드 히든 인풋 제거
                $(".temp_keywords").remove();

                // 현재 태그들을 가져와서 히든 인풋으로 추가
                $("#keyword_list .tag_item .tag_val").each(function () {
                    var keyword = $(this).text().trim();
                    // 유효한 키워드만 전송 (✕, 빈 문자열 제외)
                    if (keyword !== '' && keyword !== '✕') {
                        $("#shopRegisterForm").append('<input type="hidden" name="keywords[]" class="temp_keywords" value="' + keyword + '">');
                    }
                });
            });

            // 사용기간 토글
            $("input[name='use_period_type']").change(function () {
                if ($("#radio3_2").is(":checked")) {
                    $(".use_period_bx").stop().fadeIn(200);
                } else {
                    $(".use_period_bx").stop().fadeOut(200);
                }
            });

            // 로고 이미지 토글
            $("input[name='use_logo']").change(function () {
                if ($("#radio4_2").is(":checked")) {
                    $(".logo_img_bx").stop().fadeIn(200);
                } else {
                    $(".logo_img_bx").stop().fadeOut(200);
                }
            });

            // 메인 배너 토글
            $("input[name='use_banner']").change(function () {
                if ($("#radio5_2").is(":checked")) {
                    $(".banner_img_bx").stop().fadeIn(200);
                } else {
                    $(".banner_img_bx").stop().fadeOut(200);
                }
            });

            // 파일 선택 시 이름 표시 (이벤트 위임)
            $(document).on("change", ".file_input", function () {
                var fileName = $(this).val().split('\\').pop();
                $(this).closest(".file_bx").find(".file_name").val(fileName);
            });

            // 배너 행 추가
            $(document).on("click", ".btn_add_banner", function () {
                var count = $(".banner_row").length;
                if (count >= 5) {
                    alert("최대 5개까지 등록 가능합니다.");
                    return;
                }
                var newRow = '<div class="file_bx banner_row" style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">' +
                    '    <input type="text" class="file_name w300" readonly>' +
                    '    <label class="btn_file" style="background: #000; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; cursor: pointer; border-radius: 2px;">찾아보기' +
                    '        <input type="file" name="banner_files[]" class="file_input" style="display: none;">' +
                    '    </label>' +
                    '    <span class="col2 fs2" style="margin-left: 10px;">최적사이즈 000px * 000px</span>' +
                    '    <button type="button" class="btn_del_banner" style="background: #888; color: #fff; padding: 0 20px; height: 32px; line-height: 32px; border-radius: 2px; border: none; cursor: pointer; margin-left: auto;">삭제</button>' +
                    '</div>';
                $("#banner_container").append(newRow);
            });

            // 배너 행 삭제
            $(document).on("click", ".btn_del_banner", function () {
                $(this).closest(".banner_row").remove();
            });

            // OG TAG 토글
            $("input[name='use_og']").change(function () {
                if ($("#radio6_2").is(":checked")) {
                    $(".og_tag_bx").stop().fadeIn(200);
                } else {
                    $(".og_tag_bx").stop().fadeOut(200);
                }
            });

            $("input[name='use_own_pg']").change(function () {
                if ($("#radio_pg_2").is(":checked")) {
                    $(".own_pg_row").stop().fadeIn(200);
                } else {
                    $(".own_pg_row").stop().fadeOut(200);
                }
            });

            // 관리자 정보 토글
            $("input[name='use_admin']").change(function () {
                if ($("#radio7_2").is(":checked")) {
                    $(".admin_detail_row").stop().fadeIn(200);
                } else {
                    $(".admin_detail_row").stop().fadeOut(200);
                }
            });

            // 정산방법에 따른 요율 필드 토글
            $("input[name='settlement_type']").change(function () {
                $(".settle_rate").hide();
                if ($("#radio8_1").is(":checked")) {
                    $(".settle_rate_1").css("display", "flex");
                } else {
                    $(".settle_rate_2").css("display", "flex");
                }
            });

            // 키워드 입력 핸들러
            $("#keyword_input").on("keypress", function (e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    var val = $(this).val().trim();
                    if (val !== "") {
                        addKeywordTag(val);
                        $(this).val("");
                    }
                }
            });

            // 키워드 삭제 핸들러 (이벤트 위임)
            $(document).on("click", ".btn_tag_del", function () {
                $(this).closest(".tag_item").remove();
                checkKeywordList();
            });

            function addKeywordTag(text) {
                $("#keyword_list").css("display", "flex");
                var tagHtml = '<div class="tag_item" style="display: flex; align-items: center; gap: 2px;">' +
                    '<span class="tag_val" style="background: #bbb; color: #fff; padding: 2px 15px; border-radius: 2px; font-size: 13px;">' + text + '</span>' +
                    '<button type="button" class="btn_tag_del" style="width: 18px; height: 18px; border: 2px solid #333; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;"><span style="font-size: 12px; font-weight: 900; color: #333; line-height: 1;">✕</span></button>' +
                    '</div>';
                $("#keyword_list").append(tagHtml);
            }

            function checkKeywordList() {
                if ($("#keyword_list .tag_item").length === 0) {
                    $("#keyword_list").hide();
                } else {
                    $("#keyword_list").css("display", "flex");
                }
            }

            // 달력 초기화
            $(".datepicker").datepicker({
                dateFormat: 'yy-mm-dd',
                showOtherMonths: true,
                showMonthAfterYear: true,
                changeYear: true,
                changeMonth: true,
                yearSuffix: "년",
                monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
                dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
            });

            // 기존 키워드(old) 복구 처리
            var oldKeywords = @json(old('keywords', []));
            if (oldKeywords && oldKeywords.length > 0) {
                oldKeywords.forEach(function(kw) {
                    // 유효한 키워드만 추가 (✕, 빈 문자열, 공백만 있는 문자열 제외)
                    if (kw && typeof kw === 'string' && kw.trim() !== '' && kw.trim() !== '✕') {
                        addKeywordTag(kw.trim());
                    }
                });
            }

            checkKeywordList();
        });
    </script>
@endpush
