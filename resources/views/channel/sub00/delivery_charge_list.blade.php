@extends('layouts.channel')

@php
    $dep1_id = "00";
@endphp

@section('content')
<div id="contents">
    <div class="row">
        <div class="box box1">
            <div class="page_info">
                <div class="ttl">배송비설정 관리</div>
                <ul class="dep">
                    <li>HOME</li>
                    <li>배송비설정 관리</li>
                </ul>
            </div>
            <div class="conbx">
                <div class="con_w">
                    @if(session('flash_message_success'))
                        <div class="alert alert-success">{{ session('flash_message_success') }}</div>
                    @endif

                    <div class="list_top1 btn">
                        <div class="count">총 <strong>{{ $charges->total() }}</strong> 건</div>
                        <div class="btn_bx">
                            <a href="{{ url()->current() }}" class="btn01 col5 pop_btn" data-pop="pop_delivery_create">배송비 등록</a>
                        </div>
                    </div>
                    <div class="tb01 ovS">
                        <table>
                            <colgroup>
                                <col width="120px">
                                <col width="120px">
                                <col width="">
                                <col width="150px">
                                <col width="150px">
                                <col width="120px">
                                <col width="120px">
                                <col width="150px">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>배송구분</th>
                                    <th>상태</th>
                                    <th>배송명</th>
                                    <th>지정택배사</th>
                                    <th>배송비 유형</th>
                                    <th>배송비 결제</th>
                                    <th>상품수</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($charges as $charge)
                                    <tr>
                                        <td>사용자</td>
                                        <td>{{ $charge->status ? '사용' : '중지' }}</td>
                                        <td class="t_l">{{ $charge->name }}</td>
                                        <td class="t_l">{{ $charge->courier ?: '자체배송' }}</td>
                                        <td class="t_l">{{ $shippingTypes[$charge->shipping_type] ?? $charge->shipping_type }}</td>
                                        <td>{{ $paymentTypes[$charge->payment_type] ?? $charge->payment_type }}</td>
                                        <td>{{ number_format($charge->product_count) }}</td>
                                        <td>
                                            <a href="{{ url()->current() }}" class="btn02 col5 pop_btn" data-pop="pop_delivery_view_{{ $charge->id }}">보기</a>
                                            <a href="{{ url()->current() }}" class="btn02 col7 mt5 pop_btn" data-pop="pop_delivery_edit_{{ $charge->id }}">수정</a>
                                            <form method="POST" action="{{ route('channel.delivery.delete', $charge->id) }}" style="display:inline;" onsubmit="return confirm('배송비 설정을 삭제하시겠습니까?');">
                                                @csrf
                                                <button type="submit" class="btn02 mt5">삭제</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="no_data" style="padding: 80px 0;">등록된 배송비 설정이 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="page_bx1">
                        {{ $charges->links() }}
                    </div>

                    <div class="popup_bx" data-id="pop_delivery_create">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">배송비 등록</div>
                                    </div>
                                    <form method="POST" action="{{ route('channel.delivery.store') }}">
                                        @csrf
                                        @include('channel.sub00.inc.delivery_charge_form', ['charge' => null])
                                        <div class="btm_btn mt10">
                                            <button type="submit">배송비 등록</button>
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach($charges as $charge)
                        <div class="popup_bx" data-id="pop_delivery_view_{{ $charge->id }}">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">배송비 정보</div>
                                        </div>
                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="tb01">
                                                    <table>
                                                        <tbody class="textL">
                                                            <tr><th class="w160"><span>상태</span></th><td>{{ $charge->status ? '사용' : '중지' }}</td></tr>
                                                            <tr><th class="w160"><span>배송비 명칭</span></th><td>{{ $charge->name }}</td></tr>
                                                            <tr><th class="w160"><span>지정택배사</span></th><td>{{ $charge->courier ?: '자체배송' }}</td></tr>
                                                            <tr><th class="w160"><span>배송비 유형</span></th><td>{{ $shippingTypes[$charge->shipping_type] ?? $charge->shipping_type }}</td></tr>
                                                            <tr><th class="w160"><span>배송비 결제</span></th><td>{{ $paymentTypes[$charge->payment_type] ?? $charge->payment_type }}</td></tr>
                                                            <tr><th class="w160"><span>기본 배송비</span></th><td>{{ number_format($charge->base_fee) }} 원</td></tr>
                                                            <tr><th class="w160"><span>무료 조건</span></th><td>{{ $charge->free_order_amount ? number_format($charge->free_order_amount) . ' 원 이상' : '-' }} / {{ $charge->free_order_quantity ? number_format($charge->free_order_quantity) . ' 개 이상' : '-' }}</td></tr>
                                                            <tr><th class="w160"><span>메모</span></th><td>{!! nl2br(e($charge->memo ?: '-')) !!}</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btm_btn mt10">
                                            <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="popup_bx" data-id="pop_delivery_edit_{{ $charge->id }}">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">배송비 수정</div>
                                        </div>
                                        <form method="POST" action="{{ route('channel.delivery.update', $charge->id) }}">
                                            @csrf
                                            @include('channel.sub00.inc.delivery_charge_form', ['charge' => $charge])
                                            <div class="btm_btn mt10">
                                                <button type="submit">배송비 수정</button>
                                                <a href="{{ url()->current() }}" class="col5 close_btn">닫기</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
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
</script>
@endpush
