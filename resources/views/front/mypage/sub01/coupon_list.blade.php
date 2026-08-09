{{-- 쿠폰 목록 페이지 (PPT Slide 48 기반) --}}
@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '6')

@section('content')
    <div id="contents">
        <div id="coupon">
            <div class="box_w">
                <div class="box box1">
                    {{-- 페이지 정보 --}}
                    <div class="page_info">
                        <div class="ttl">쿠폰</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>마이페이지</li>
                            <li>쿠폰</li>
                        </ul>
                    </div>

                    <div class="con_w">
                        {{-- 기간 검색 필터 --}}
                        <div style="background-color: #f8f8f8; padding: 20px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #eee;">
                            <form method="GET" action="{{ route('mypage.coupon') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                                    style="height: 40px; border: 1px solid #ddd; padding: 0 10px; background-color: #fff;">
                                <span>~</span>
                                <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                                    style="height: 40px; border: 1px solid #ddd; padding: 0 10px; background-color: #fff;">
                                <button type="submit"
                                    style="height: 40px; padding: 0 20px; background-color: #111; color: #fff; border: none; font-size: 14px; font-weight: 700; cursor: pointer; border-radius: 3px;">
                                    검색
                                </button>
                            </form>
                        </div>

                        {{-- 총 건수 --}}
                        <div style="margin-bottom: 15px; font-size: 14px; color: #333;">
                            총 <strong style="color: #2563eb;">{{ $totalCount }}</strong>건
                        </div>

                        {{-- 쿠폰 목록 테이블 --}}
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="60px">
                                    <col width="">
                                    <col width="150px">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="100px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>쿠폰코드</th>
                                        <th>할인</th>
                                        <th>사용유형</th>
                                        <th>유효기간</th>
                                        <th>상태</th>
                                    </tr>
                                </thead>
                                <tbody class="textC">
                                    @forelse($coupons as $index => $coupon)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td style="font-weight: 700; color: #333;">{{ $coupon->coupon_code }}</td>
                                            <td>
                                                @if($coupon->amount_type === 'Percentage')
                                                    <span style="color: #e00; font-weight: bold;">{{ number_format($coupon->amount) }}%</span> 할인
                                                @else
                                                    <span style="color: #e00; font-weight: bold;">{{ number_format($coupon->amount) }}원</span> 할인
                                                @endif
                                            </td>
                                            <td>
                                                @if($coupon->coupon_type === 'Single')
                                                    1회용
                                                @else
                                                    다회용
                                                @endif
                                            </td>
                                            <td>{{ $coupon->expiry_date }}</td>
                                            <td>
                                                @if($coupon->expiry_date >= now()->format('Y-m-d'))
                                                    <span style="color: #2563eb; font-weight: bold;">사용가능</span>
                                                @else
                                                    <span style="color: #999;">만료</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="padding: 60px 0; color: #999;">
                                                보유한 쿠폰이 없습니다.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 페이지네이션 --}}
                        <div class="btm_btn">
                            <div class="page_bx1 text-center">
                                <a href="javascript:void(0);" class="page_prev dimmed">prev</a>
                                <a href="javascript:void(0);" class="num on">1</a>
                                <a href="javascript:void(0);" class="page_next dimmed">next</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
