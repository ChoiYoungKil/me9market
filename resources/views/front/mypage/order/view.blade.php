@extends('layouts.mypage')

@section('page_type', 'sub')

@php
    $purchasedItems = $order->orders_products->filter(function($p) {
        return !in_array($p->normalized_status, [
            \App\Support\OrderItemStatus::CANCELLED,
            \App\Support\OrderItemStatus::CANCEL_REQUESTED,
            \App\Support\OrderItemStatus::RETURN_REQUESTED,
            \App\Support\OrderItemStatus::RETURNED,
            \App\Support\OrderItemStatus::EXCHANGE_REQUESTED,
            \App\Support\OrderItemStatus::EXCHANGED,
        ], true);
    });

    $cancelledItems = $order->orders_products->filter(function($p) {
        return in_array($p->normalized_status, [
            \App\Support\OrderItemStatus::CANCEL_REQUESTED,
            \App\Support\OrderItemStatus::CANCELLED,
        ], true);
    });

    $returnedItems = $order->orders_products->filter(function($p) {
        return in_array($p->normalized_status, [
            \App\Support\OrderItemStatus::RETURN_REQUESTED,
            \App\Support\OrderItemStatus::RETURNED,
        ], true);
    });

    $exchangedItems = $order->orders_products->filter(function($p) {
        return in_array($p->normalized_status, [
            \App\Support\OrderItemStatus::EXCHANGE_REQUESTED,
            \App\Support\OrderItemStatus::EXCHANGED,
        ], true);
    });
@endphp

@section('content')
    <div id="contents">
        <div id="order">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">주문 상세</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>주문 상세</li>
                        </ul>
                    </div>

                    <div class="ttl01">주문자 정보 <span class="col2">{{ $order->created_at->format('Y.m.d') }} ( 주문번호: Me9-{{ sprintf('%08d', $order->id) }} )</span></div>

                    <div class="tb01">
                        <table>
                            <tbody class="textL">
                                <tr>
                                    <th class="w160">주문자 이름</th>
                                    <td>{{ $order->name }}</td>
                                </tr>
                                <tr>
                                    <th class="w160">휴대폰 번호</th>
                                    <td>{{ $order->mobile }}</td>
                                </tr>
                                <tr>
                                    <th class="w160">이메일 주소</th>
                                    <td>{{ $order->email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box2">
                    <div class="ttl01">배송 정보</div>

                    <div class="tb01">
                        <table>
                            <tbody class="textL">
                                <tr>
                                    <th class="w160">받는 사람</th>
                                    <td>{{ $order->name }}</td>
                                </tr>
                                <tr>
                                    <th class="w160">휴대폰 번호</th>
                                    <td>{{ $order->mobile }}</td>
                                </tr>
                                <tr>
                                    <th class="w160">주소</th>
                                    <td>[{{ $order->pincode }}] {{ $order->address }} {{ $order->city }} {{ $order->state }}</td>
                                </tr>
                                <tr>
                                    <th class="w160">배송메모</th>
                                    <td>{{ $order->delivery_instruction ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box3">
                    <div class="ttl01">구매 상품</div>

                    <div class="tb02">
                        <table>
                            <colgroup>
                                <col width="96px">
                                <col width="">
                                <col width="150px">
                                <col width="111px">
                            </colgroup>
                            <tbody>
                                @forelse($purchasedItems as $item)
                                    @php
                                        $shopName = \Illuminate\Support\Facades\DB::table('vendors_business_details')
                                            ->where('vendor_id', $item->vendor_id)
                                            ->value('shop_name') ?? 'Me9 브랜드 전용관';
                                    @endphp
                                    <tr>
                                        <td class="status col1">{{ $item->status_label }}</td>
                                        <td class="info">
                                            <div class="con_w">
                                                <div class="img_bx"
                                                    style="background-image: url('{{ $item->product && !empty($item->product->product_image) ? asset('front/images/product_images/small/' . $item->product->product_image) : 'https://placehold.co/100' }}'); background-size: cover; background-position: center; width: 70px; height: 70px; border: 1px solid #eee;">
                                                </div>
                                                <div class="txt_w">
                                                    <div style="font-size: 11px; color: #888; margin-bottom: 3px;">{{ $shopName }}</div>
                                                    <strong class="subject">{{ $item->product_name }}</strong>
                                                    <p>옵션: {{ $item->product_size }} / {{ $item->product_qty }}개</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="price t_r">{{ number_format($item->product_price * $item->product_qty) }} 원</td>
                                        <td class="t_r">
                                            @if($item->normalized_status === \App\Support\OrderItemStatus::CONFIRMED)
                                                구매확정일<br> {{ $item->updated_at->format('Y.m.d') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="textC" style="padding: 40px 0; color: #888;">구매 진행 중인 상품이 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box4">
                    <div class="ttl01">결제 정보</div>

                    <div class="tb03">
                        <div class="l_bx">
                            <table>
                                <tbody>
                                    <tr>
                                        <th>결제수단</th>
                                        <td>{{ $order->payment_method }}</td>
                                    </tr>
                                    <tr>
                                        <th>결제대행사</th>
                                        <td>{{ $order->payment_gateway }}</td>
                                    </tr>
                                    <tr>
                                        <th>적립포인트</th>
                                        <td>+ 0 point</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="r_bx">
                            <table>
                                <tbody>
                                    <tr>
                                        <th>총 상품금액</th>
                                        <td>{{ number_format($order->grand_total - $order->shipping_charges) }} 원</td>
                                    </tr>
                                    <tr>
                                        <th>배송비</th>
                                        <td>+ {{ number_format($order->shipping_charges) }} 원</td>
                                    </tr>
                                    <tr class="last">
                                        <th>최종 결제금액</th>
                                        <td><strong>{{ number_format($order->grand_total) }}</strong> 원</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="txt01 t_r">( 배송비 ) {{ $order->shipping_charges > 0 ? number_format($order->shipping_charges) . ' 원' : '무료배송' }}</div>
                </div>

                @if($cancelledItems->count() > 0)
                    @foreach($cancelledItems as $item)
                        @php
                            $shopName = \Illuminate\Support\Facades\DB::table('vendors_business_details')
                                ->where('vendor_id', $item->vendor_id)
                                ->value('shop_name') ?? 'Me9 브랜드 전용관';
                            $claim = $order->claims->where('order_product_id', $item->id)->where('type', 'cancel')->first();
                        @endphp
                        <div class="box box5">
                            <div class="ttl01">취소 상품 <span class="col3">판매자 ( {{ $shopName }} )</span></div>

                            <div class="tb02">
                                <table>
                                    <colgroup>
                                        <col width="96px">
                                        <col width="">
                                        <col width="150px">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <td class="status col2">{{ $item->status_label }}</td>
                                            <td class="info">
                                                <div class="con_w">
                                                    <div class="img_bx"
                                                        style="background-image: url('{{ $item->product && !empty($item->product->product_image) ? asset('front/images/product_images/small/' . $item->product->product_image) : 'https://placehold.co/100' }}'); background-size: cover; background-position: center; width: 70px; height: 70px; border: 1px solid #eee;">
                                                    </div>
                                                    <div class="txt_w">
                                                        <strong class="subject">{{ $item->product_name }}</strong>
                                                        <p>옵션: {{ $item->product_size }} / {{ $item->product_qty }}개</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price t_r">{{ number_format($item->product_price * $item->product_qty) }} 원</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tb04">
                                <table>
                                    <tbody>
                                        <tr>
                                            <th>취소 사유</th>
                                            <td>{{ $claim ? config('array.order_cancel_reasons.' . $claim->reason, $claim->reason) : '정보 없음' }}@if($claim && $claim->detail_reason) ({{ $claim->detail_reason }})@endif</td>
                                        </tr>
                                        <tr>
                                            <th>처리 상태</th>
                                            <td>{{ $claim ? $claim->status : '완료' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($returnedItems->count() > 0)
                    @foreach($returnedItems as $item)
                        @php
                            $shopName = \Illuminate\Support\Facades\DB::table('vendors_business_details')
                                ->where('vendor_id', $item->vendor_id)
                                ->value('shop_name') ?? 'Me9 브랜드 전용관';
                            $claim = $order->claims->where('order_product_id', $item->id)->where('type', 'return')->first();
                        @endphp
                        <div class="box box6">
                            <div class="ttl01">반품 상품 <span class="col3">판매자 ( {{ $shopName }} )</span></div>

                            <div class="tb02">
                                <table>
                                    <colgroup>
                                        <col width="96px">
                                        <col width="">
                                        <col width="150px">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <td class="status col3">{{ $item->status_label }}</td>
                                            <td class="info">
                                                <div class="con_w">
                                                    <div class="img_bx"
                                                        style="background-image: url('{{ $item->product && !empty($item->product->product_image) ? asset('front/images/product_images/small/' . $item->product->product_image) : 'https://placehold.co/100' }}'); background-size: cover; background-position: center; width: 70px; height: 70px; border: 1px solid #eee;">
                                                    </div>
                                                    <div class="txt_w">
                                                        <strong class="subject">{{ $item->product_name }}</strong>
                                                        <p>옵션: {{ $item->product_size }} / {{ $item->product_qty }}개</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price t_r">{{ number_format($item->product_price * $item->product_qty) }} 원</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tb04">
                                <table>
                                    <tbody>
                                        <tr>
                                            <th>반품 사유</th>
                                            <td>{{ $claim ? config('array.order_return_reasons.' . $claim->reason, $claim->reason) : '정보 없음' }}@if($claim && $claim->detail_reason) ({{ $claim->detail_reason }})@endif</td>
                                        </tr>
                                        <tr>
                                            <th>처리 상태</th>
                                            <td>{{ $claim ? $claim->status : '완료' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($exchangedItems->count() > 0)
                    @foreach($exchangedItems as $item)
                        @php
                            $shopName = \Illuminate\Support\Facades\DB::table('vendors_business_details')
                                ->where('vendor_id', $item->vendor_id)
                                ->value('shop_name') ?? 'Me9 브랜드 전용관';
                            $claim = $order->claims->where('order_product_id', $item->id)->where('type', 'exchange')->first();
                        @endphp
                        <div class="box box7">
                            <div class="ttl01">교환 상품 <span class="col3">판매자 ( {{ $shopName }} )</span></div>

                            <div class="tb02">
                                <table>
                                    <colgroup>
                                        <col width="96px">
                                        <col width="">
                                        <col width="150px">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <td class="status col4">{{ $item->status_label }}</td>
                                            <td class="info">
                                                <div class="con_w">
                                                    <div class="img_bx"
                                                        style="background-image: url('{{ $item->product && !empty($item->product->product_image) ? asset('front/images/product_images/small/' . $item->product->product_image) : 'https://placehold.co/100' }}'); background-size: cover; background-position: center; width: 70px; height: 70px; border: 1px solid #eee;">
                                                    </div>
                                                    <div class="txt_w">
                                                        @if($claim)
                                                            <p class="col2">교환 주문번호: Me9-EX-{{ sprintf('%08d', $claim->id) }}</p>
                                                        @endif
                                                        <strong class="subject">{{ $item->product_name }}</strong>
                                                        <p>옵션: {{ $item->product_size }} / {{ $item->product_qty }}개</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price t_r">{{ number_format($item->product_price * $item->product_qty) }} 원</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tb04">
                                <table>
                                    <tbody>
                                        <tr>
                                            <th>교환 사유</th>
                                            <td>{{ $claim ? config('array.order_return_reasons.' . $claim->reason, $claim->reason) : '정보 없음' }}@if($claim && $claim->detail_reason) ({{ $claim->detail_reason }})@endif</td>
                                        </tr>
                                        <tr>
                                            <th>처리 상태</th>
                                            <td>{{ $claim ? $claim->status : '완료' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
