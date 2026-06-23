@extends('layouts.channel')

@section('page_type', 'sub')
@php
    $dep1_id = "03";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">공동구매 상품등록</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>공동구매 관리</li>
                        <li>공동구매 상품등록</li>
                    </ul>
                </div>

                <div class="conbx">
                    <form id="jointPurchaseForm" action="{{ route('channel.joint_purchase.store') }}" method="POST">
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
                            <div class="ttl01">공동구매 상품 설정</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>* 대상 상품 선택</span></th>
                                            <td>
                                                <select name="product_id" required style="width: 100%; max-width: 400px; padding: 5px; height: 32px;">
                                                    <option value="">대상을 선택해 주세요</option>
                                                    @foreach($products as $prod)
                                                        <option value="{{ $prod->id }}">{{ $prod->product_name }} (코드: {{ $prod->product_code }})</option>
                                                    @endforeach
                                                    {{-- Fallback mock products for testing if DB table is empty --}}
                                                    @if(count($products) == 0)
                                                        <option value="1">BlueViolet a omnis (샘플상품 A)</option>
                                                        <option value="2">Comfortable Cotton T-Shirt (샘플상품 B)</option>
                                                        <option value="3">Premium Leather Wallet (샘플상품 C)</option>
                                                    @endif
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 목표 최소 수량 (개)</span></th>
                                            <td>
                                                <input type="number" name="min_quantity" value="{{ old('min_quantity', 100) }}" required style="width: 200px;">
                                                <span class="fs2 col2" style="margin-left: 10px;">달성 목표 최소 수량을 숫자로 입력하세요.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 공동구매 할인가격 (원)</span></th>
                                            <td>
                                                <input type="number" name="discount_price" value="{{ old('discount_price', 15000) }}" required style="width: 200px;">
                                                <span class="fs2 col2" style="margin-left: 10px;">공동구매 시 할인가를 숫자로 입력하세요.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 진행 기간 설정</span></th>
                                            <td>
                                                <div class="date_bx" style="display: flex; align-items: center; gap: 5px;">
                                                    <input type="text" name="start_date" id="start_date" class="datepicker" value="{{ old('start_date', date('Y-m-d')) }}"
                                                        style="width: 120px;" readonly placeholder="시작일 선택">
                                                    <span>~</span>
                                                    <input type="text" name="end_date" id="end_date" class="datepicker" value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}"
                                                        style="width: 120px;" readonly placeholder="종료일 선택">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn mt40">
                                <button type="submit" class="col7" style="width: 140px; height: 48px; border-radius: 5px; font-size: 14px; font-weight: 700; border: none; cursor: pointer;">등록</button>
                                <a href="{{ route('channel.joint_purchase.list') }}" class="col3" style="max-width: 140px; line-height: 48px; border-radius: 5px; font-size: 14px; font-weight: 700; text-align: center;">취소</a>
                            </div>
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
        });
    </script>
@endpush
