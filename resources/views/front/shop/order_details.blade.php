@extends('layouts.frontend')

@section('content')
<div id="contents" style="padding: 100px 0; min-height: 640px; background:#f6f7f9;">
    <div style="max-width:1180px; margin:0 auto; padding:0 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px;">
            <div>
                <div style="color:#667085; font-weight:800;">{{ $shop->channel_name }}</div>
                <h1 style="margin:4px 0 0; font-size:30px;">주문 상세 내역</h1>
            </div>
            <a href="{{ route('shop.channel_main') }}" style="height:42px; padding:0 18px; display:inline-flex; align-items:center; border:1px solid #111827; border-radius:6px; color:#111827; text-decoration:none; font-weight:900;">쇼핑 계속하기</a>
        </div>

        @if(!$order)
            <div style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:40px; text-align:center; color:#667085;">
                조회할 주문 내역이 없습니다.
            </div>
        @else
            @if(session('flash_message_success'))
                <div style="background:#dcfae6; color:#087443; border:1px solid #abefc6; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-weight:800;">{{ session('flash_message_success') }}</div>
            @endif

            <section style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:24px; margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; border-bottom:1px solid #eef1f5; padding-bottom:16px; margin-bottom:18px;">
                    <div>
                        <div style="color:#667085; font-size:13px; font-weight:800;">주문번호</div>
                        <strong style="font-size:20px;">Me9-Shop-{{ str_pad($order->id, 7, '0', STR_PAD_LEFT) }}</strong>
                    </div>
                    <div>
                        <div style="color:#667085; font-size:13px; font-weight:800;">주문일시</div>
                        <strong>{{ optional($order->created_at)->format('Y-m-d H:i') }}</strong>
                    </div>
                    <div>
                        <div style="color:#667085; font-size:13px; font-weight:800;">주문상태</div>
                        <strong>{{ $order->order_status }}</strong>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px;">
                    <div>
                        <h2 style="font-size:18px; margin:0 0 12px;">주문자 정보</h2>
                        <div style="display:grid; gap:8px; color:#344054;">
                            <div><strong>이름</strong> {{ $order->name }}</div>
                            <div><strong>연락처</strong> {{ $order->mobile }}</div>
                            <div><strong>이메일</strong> {{ $order->email }}</div>
                        </div>
                    </div>
                    <div>
                        <h2 style="font-size:18px; margin:0 0 12px;">배송 정보</h2>
                        <div style="display:grid; gap:8px; color:#344054;">
                            <div><strong>받는 사람</strong> {{ $order->name }}</div>
                            <div><strong>주소</strong> {{ $order->pincode }} {{ $order->address }}</div>
                            <div><strong>지역</strong> {{ $order->city }} {{ $order->state }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:24px; margin-bottom:18px;">
                <h2 style="font-size:18px; margin:0 0 12px;">구매 상품</h2>
                @forelse($order->orders_products as $item)
                    <div style="display:grid; grid-template-columns:120px minmax(0,1fr) 160px 150px; gap:14px; align-items:center; border-top:1px solid #eef1f5; padding:14px 0;">
                        <div style="height:86px; background:#eef1f5; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#667085; font-weight:900; font-size:12px; text-align:center;">{{ $item->product_code }}</div>
                        <div>
                            <span style="display:inline-flex; padding:4px 8px; border-radius:999px; background:#eef4ff; color:#3538cd; font-size:12px; font-weight:900;">{{ $item->status_label }}</span>
                            <strong style="display:block; margin-top:8px; font-size:17px;">{{ $item->product_name }}</strong>
                            <div style="color:#667085; font-size:13px;">{{ $item->product_color }} / {{ $item->product_size }} / {{ $item->product_qty }}개</div>
                            @if($item->tracking_number)
                                <div style="color:#067647; font-size:13px; margin-top:4px;">{{ $item->courier_name }} {{ $item->tracking_number }}</div>
                            @endif
                        </div>
                        <div style="text-align:right; font-weight:900;">{{ number_format($item->line_total ?: $item->product_price * $item->product_qty) }}원</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                            @foreach(['cancel' => '취소요청', 'return' => '반품요청', 'exchange' => '교환요청', 'confirm' => '구매확정'] as $action => $label)
                                <form action="{{ route('front.shop.order.item.status', ['id' => $item->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="{{ $action }}">
                                    <input type="hidden" name="reason" value="Shop 채널 주문상세 요청">
                                    <button type="submit" style="width:100%; height:34px; border:1px solid #d0d5dd; border-radius:6px; background:#fff; color:#344054; font-weight:800; cursor:pointer;">{{ $label }}</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div style="padding:20px; background:#f8fafc; border-radius:6px; color:#667085;">이 채널의 주문상품이 없습니다.</div>
                @endforelse
            </section>

            <section style="background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:24px;">
                <h2 style="font-size:18px; margin:0 0 12px;">결제 정보</h2>
                <div style="display:grid; gap:8px; max-width:420px; margin-left:auto;">
                    <div style="display:flex; justify-content:space-between;"><span>상품금액</span><strong>{{ number_format(max(0, $order->grand_total - $order->shipping_charges)) }}원</strong></div>
                    <div style="display:flex; justify-content:space-between;"><span>배송비</span><strong>{{ number_format($order->shipping_charges) }}원</strong></div>
                    <div style="display:flex; justify-content:space-between; border-top:1px solid #d9dee7; padding-top:12px; font-size:20px;"><span>최종 결제금액</span><strong>{{ number_format($order->grand_total) }}원</strong></div>
                    <div style="display:flex; justify-content:space-between; color:#667085;"><span>결제수단</span><span>{{ $order->payment_method }}</span></div>
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
