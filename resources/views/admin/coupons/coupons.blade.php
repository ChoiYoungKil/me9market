@extends('admin.layout.layout')
@section('content')
<div id="contents" class="admin-workspace admin-settings-list">
    <div class="admin-toolbar"><h1>쿠폰 관리</h1><div class="admin-actions"><a class="admin-btn primary" href="{{ url('admin/add-edit-coupon') }}">쿠폰 등록</a></div></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-setting-panel">
<table id="coupons" class="admin-table">
<colgroup><col style="width:64px"><col><col style="width:130px"><col style="width:140px"><col style="width:150px"><col style="width:90px"><col style="width:164px"></colgroup>
                                    <thead>
                                        <tr>
                                            <th>번호</th>
                                            <th>쿠폰코드</th>
                                            <th>사용 횟수</th>
                                            <th>할인 혜택</th>
                                            <th>만료일</th>
                                            <th>상태</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($coupons as $coupon)
                                            <tr>
                                                <td>{{ $coupon['id'] }}</td>
                                                <td>{{ $coupon['coupon_code'] }}</td>
                                                <td>{{ ['Multiple Times'=>'여러 번 사용','Single Time'=>'한 번 사용'][$coupon['coupon_type']] ?? $coupon['coupon_type'] }}</td>
                                                <td>
                                                    {{ $coupon['amount'] }}
                                                    @if ($coupon['amount_type'] == 'Percentage')
                                                        %
                                                    @else
                                                        원
                                                    @endif
                                                </td>
                                                <td>{{ $coupon['expiry_date'] }}</td>
                                                <td>
                                                    @if ($coupon['status'] == 1)
                                                        <a class="updateCouponStatus" id="coupon-{{ $coupon['id'] }}"
                                                            coupon_id="{{ $coupon['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status enabled" status="Active">사용</i>
                                                        </a>
                                                    @else
                                                        <a class="updateCouponStatus" id="coupon-{{ $coupon['id'] }}"
                                                            coupon_id="{{ $coupon['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status disabled" status="Inactive">중지</i>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-coupon/' . $coupon['id']) }}" class="admin-btn primary">수정</a>

                                                    <a href="JavaScript:void(0)" class="admin-btn danger confirmDelete" module="coupon"
                                                        moduleid="{{ $coupon['id'] }}">
                                                        삭제
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
    </section>
</div>
@endsection
