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
                    <div class="ttl">공동구매 상품 수정</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>공동구매 관리</li>
                        <li>공동구매 상품 수정</li>
                    </ul>
                </div>

                <div class="conbx">
                    <form id="jointPurchaseForm" action="{{ route('channel.joint_purchase.update', ['id' => $jointPurchase->id]) }}" method="POST">
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
                            <div class="ttl01">공동구매 정보 수정</div>
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
                                                <select name="product_id" required class="wFull">
                                                    <option value="">대상을 선택해 주세요</option>
                                                    @foreach($products as $prod)
                                                        <option value="{{ $prod->id }}" {{ $jointPurchase->product_id == $prod->id ? 'selected' : '' }}>{{ $prod->product_name }} (코드: {{ $prod->product_code }})</option>
                                                    @endforeach
                                                    @if(count($products) == 0)
                                                        <option value="" disabled>등록 가능한 실제 상품이 없습니다.</option>
                                                    @endif
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 목표 최소 수량 (개)</span></th>
                                            <td>
                                                <input type="text" name="min_quantity" value="{{ old('min_quantity', $jointPurchase->min_quantity) }}" class="w160" inputmode="numeric" pattern="[0-9]*" required>
                                                <span class="fs2 col2" style="margin-left: 10px;">달성 목표 최소 수량을 숫자로 입력하세요.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 수량별 판매가</span></th>
                                            <td>
                                                <div style="display:grid; gap:8px; max-width:760px;">
                                                    @php
                                                        $tierRows = collect(old('tier_min_quantity', []))->map(function($min, $index) {
                                                            return (object) [
                                                                'min_quantity' => $min,
                                                                'max_quantity' => old('tier_max_quantity.' . $index),
                                                                'unit_price' => old('tier_unit_price.' . $index),
                                                            ];
                                                        });
                                                        if ($tierRows->isEmpty()) {
                                                            $tierRows = ($tiers ?? collect());
                                                        }
                                                        if ($tierRows->isEmpty()) {
                                                            $tierRows = collect([(object) ['min_quantity' => 1, 'max_quantity' => $jointPurchase->min_quantity, 'unit_price' => $jointPurchase->discount_price]]);
                                                        }
                                                    @endphp
                                                    @for($i = 0; $i < max(4, $tierRows->count()); $i++)
                                                        @php $tier = $tierRows->get($i); @endphp
                                                        <div style="display:flex; align-items:center; gap:8px;">
                                                            <input type="text" name="tier_min_quantity[]" value="{{ $tier->min_quantity ?? '' }}" class="w160" inputmode="numeric" pattern="[0-9]*" placeholder="시작수량">
                                                            <span>개부터</span>
                                                            <input type="text" name="tier_max_quantity[]" value="{{ $tier->max_quantity ?? '' }}" class="w160" inputmode="numeric" pattern="[0-9]*" placeholder="종료수량">
                                                            <span>개까지</span>
                                                            <input type="text" name="tier_unit_price[]" value="{{ $tier->unit_price ?? '' }}" class="w160" inputmode="numeric" pattern="[0-9]*" placeholder="개당 판매가">
                                                            <span>원</span>
                                                        </div>
                                                    @endfor
                                                </div>
                                                <p class="fs2 col2 mt10">저장 시 현재 누적 수량 기준으로 기존 공동구매 주문금액이 재산정되며 정산 기준금액도 함께 변경됩니다.</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>* 진행 기간 설정</span></th>
                                            <td>
                                                <div class="date_bx" style="display: flex; align-items: center; gap: 5px;">
                                                    <input type="text" name="start_date" id="start_date" class="datepicker w160" value="{{ old('start_date', $jointPurchase->start_date) }}"
                                                        readonly placeholder="시작일 선택">
                                                    <span>~</span>
                                                    <input type="text" name="end_date" id="end_date" class="datepicker w160" value="{{ old('end_date', $jointPurchase->end_date) }}"
                                                        readonly placeholder="종료일 선택">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn mt40">
                                <button type="submit" class="col7" style="width: 140px; height: 48px; border-radius: 5px; font-size: 14px; font-weight: 700; border: none; cursor: pointer;">수정 완료</button>
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
