@extends('layouts.admin')

@section('content')
@php
    $selectedCategories = array_map('strval', (array) old('categories', $selCats));
    $selectedBrands = array_map('strval', (array) old('brands', $selBrands));
    $selectedUsers = (array) old('users', $selUsers);
    $codeMode = old('coupon_option', $coupon->coupon_option ?: 'Automatic');
    $amountType = old('amount_type', $coupon->amount_type);
    $couponType = old('coupon_type', $coupon->coupon_type);
@endphp
<div id="contents" class="admin-workspace admin-legacy admin-account-editor admin-simple-setting-editor admin-coupon-editor">
    <div class="page_info"><h1 class="ttl">{{ $title }}</h1><ul class="dep"><li>홈</li><li>상품 관리</li><li>쿠폰 관리</li><li>{{ $title }}</li></ul></div>
    @include('admin.shared.messages')
    <form id="coupon-editor-form" method="post" action="{{ url('admin/add-edit-coupon'.($coupon->id ? '/'.$coupon->id : '')) }}">
        @csrf
        <div id="board"><div class="write02"><div class="f_bx">
            <section class="f_w">
                <h2 class="f_ttl">쿠폰 기본 정보</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                    @if(!$coupon->coupon_code)
                        <tr><th>코드 생성 방식</th><td colspan="3">
                            <ul class="chk01"><li><input type="radio" id="AutomaticCoupon" name="coupon_option" value="Automatic" @checked($codeMode === 'Automatic')><label for="AutomaticCoupon">자동 생성</label></li><li><input type="radio" id="ManualCoupon" name="coupon_option" value="Manual" @checked($codeMode === 'Manual')><label for="ManualCoupon">직접 입력</label></li></ul>
                        </td></tr>
                        <tr id="couponField" @if($codeMode !== 'Manual') hidden @endif><th><label for="coupon_code">쿠폰코드</label></th><td colspan="3"><input class="w300" type="text" name="coupon_code" id="coupon_code" value="{{ old('coupon_code') }}" placeholder="쿠폰코드를 입력해 주세요" @required($codeMode === 'Manual')></td></tr>
                    @else
                        <tr><th><label for="coupon_code">쿠폰코드</label></th><td colspan="3">
                            <input type="hidden" name="coupon_option" value="{{ $coupon->coupon_option }}">
                            <input class="w300" type="text" id="coupon_code" name="coupon_code" value="{{ $coupon->coupon_code }}" readonly>
                        </td></tr>
                    @endif
                    <tr><th>사용 횟수</th><td colspan="3"><ul class="chk01">
                        <li><input type="radio" id="coupon-multiple" name="coupon_type" value="Multiple Times" @checked($couponType === 'Multiple Times') required><label for="coupon-multiple">여러 번 사용</label></li>
                        <li><input type="radio" id="coupon-single" name="coupon_type" value="Single Time" @checked($couponType === 'Single Time') required><label for="coupon-single">한 번 사용</label></li>
                    </ul></td></tr>
                    </tbody>
                </table></div>
            </section>
            <section class="f_w">
                <h2 class="f_ttl">할인 혜택 및 기간</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                        <tr><th>할인 방식</th><td colspan="3"><ul class="chk01">
                            <li><input type="radio" id="coupon-percent" name="amount_type" value="Percentage" @checked($amountType === 'Percentage') required><label for="coupon-percent">정률 할인 (%)</label></li>
                            <li><input type="radio" id="coupon-fixed" name="amount_type" value="Fixed" @checked($amountType === 'Fixed') required><label for="coupon-fixed">정액 할인 (원)</label></li>
                        </ul></td></tr>
                        <tr><th><label for="amount">할인 혜택</label></th><td><div class="coupon-amount"><input class="w300" type="number" name="amount" id="amount" value="{{ old('amount', $coupon->amount) }}" placeholder="할인 혜택을 입력해 주세요" min="0" step="0.01" required aria-describedby="coupon-amount-unit"><span id="coupon-amount-unit">{{ $amountType === 'Fixed' ? '원' : ($amountType === 'Percentage' ? '%' : '') }}</span></div></td><th><label for="expiry_date">만료일</label></th><td><input class="w300" type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', $coupon->expiry_date) }}" required></td></tr>
                    </tbody>
                </table></div>
            </section>
            <section class="f_w">
                <h2 class="f_ttl">적용 대상</h2>
                <p class="admin-help">각 목록에서 여러 항목은 Command(Mac) 또는 Ctrl(Windows) 키를 누른 채 선택할 수 있습니다.</p>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                        <tr><th><label for="coupon-categories">적용 분류</label></th><td colspan="3"><select name="categories[]" id="coupon-categories" multiple size="8" required>
                            @foreach($categories as $section)
                                @if(count($section['categories']))
                                    <optgroup label="{{ $section['name'] }}">
                                        @foreach($section['categories'] as $category)
                                            @include('admin.filters.partials.category_options', ['category' => $category, 'depth' => 0])
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select></td></tr>
                        <tr><th><label for="coupon-brands">적용 브랜드</label></th><td colspan="3"><select name="brands[]" id="coupon-brands" multiple size="5" required>
                            @foreach($brands as $brand)<option value="{{ $brand['id'] }}" @selected(in_array((string) $brand['id'], $selectedBrands, true))>{{ $brand['name'] }}</option>@endforeach
                        </select></td></tr>
                        <tr><th><label for="coupon-users">적용 회원 (이메일)</label></th><td colspan="3"><select name="users[]" id="coupon-users" multiple size="5">
                            @foreach($users as $user)<option value="{{ $user->email }}" @selected(in_array($user->email, $selectedUsers, true))>{{ $user->email }}</option>@endforeach
                        </select></td></tr>
                    </tbody>
                </table></div>
            </section>
            <div class="admin-modal-footer account-editor-footer"><a class="admin-btn" href="{{ url('admin/coupons') }}">취소</a><button class="admin-btn" type="reset">입력 초기화</button><button class="admin-btn primary" type="submit">저장</button></div>
        </div></div></div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('common/admin-coupon-editor.js') }}?v={{ filemtime(public_path('common/admin-coupon-editor.js')) }}"></script>
@endpush
