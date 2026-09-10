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
            @if($errors->any())
                <div style="background:#fee4e2; color:#b42318; border:1px solid #fecdca; padding:12px 16px; border-radius:6px; margin-bottom:16px;">{{ $errors->first() }}</div>
            @endif

            <nav style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;" aria-label="주문 상태 필터">
                @foreach(['all' => '전체', 'confirm_pending' => '확정대기', 'shipping' => '배송중', 'claims' => '취소·교환·반품'] as $key => $label)
                    <a href="{{ route('front.shop.order.details', ['status' => $key]) }}" style="padding:10px 14px; border:1px solid {{ $status === $key ? '#111827' : '#d0d5dd' }}; background:{{ $status === $key ? '#111827' : '#fff' }}; color:{{ $status === $key ? '#fff' : '#344054' }}; border-radius:6px; text-decoration:none; font-weight:800;">{{ $label }} {{ number_format($counts[$key] ?? 0) }}건</a>
                @endforeach
            </nav>

            <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:12px; margin-bottom:8px;">
                @foreach($orders as $listedOrder)
                    <a href="{{ route('front.shop.order.details', ['id' => $listedOrder->id, 'status' => $status]) }}" style="min-width:190px; padding:12px; border:1px solid {{ $order && $order->id === $listedOrder->id ? '#111827' : '#d9dee7' }}; border-radius:6px; color:#111827; text-decoration:none; background:#fff;">
                        <strong>Me9-Shop-{{ str_pad($listedOrder->id, 7, '0', STR_PAD_LEFT) }}</strong><br>
                        <span style="font-size:12px; color:#667085;">{{ optional($listedOrder->created_at)->format('Y-m-d H:i') }}</span>
                    </a>
                @endforeach
            </div>
            @if($orders->hasPages())
                <div style="margin-bottom:18px;">{{ $orders->links() }}</div>
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
                    @php($latestClaim = $item->claims->sortByDesc('id')->first())
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
                            @foreach(['cancel' => '취소요청', 'confirm' => '구매확정'] as $action => $label)
                                @continue(!\App\Support\OrderItemStatus::customerActionAllowed($action, $item->normalized_status))
                                <form action="{{ route('front.shop.order.item.status', ['id' => $item->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="{{ $action }}">
                                    <input type="hidden" name="reason" value="Shop 채널 주문상세 요청">
                                    <button type="submit" style="width:100%; height:34px; border:1px solid #d0d5dd; border-radius:6px; background:#fff; color:#344054; font-weight:800; cursor:pointer;">{{ $label }}</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; padding:0 0 16px; border-bottom:1px solid #eef1f5;">
                        @foreach(['return' => '반품', 'exchange' => '교환'] as $claimAction => $claimLabel)
                            @continue(!\App\Support\OrderItemStatus::customerActionAllowed($claimAction, $item->normalized_status))
                            <form action="{{ route('front.shop.order.item.status', ['id' => $item->id]) }}" method="POST" style="padding:14px; background:#f8fafc; border:1px solid #e4e7ec; border-radius:6px; display:grid; gap:8px;">
                                @csrf
                                <strong>{{ $claimLabel }} 요청</strong>
                                <input type="hidden" name="action" value="{{ $claimAction }}">
                                <input type="text" name="reason" required maxlength="255" placeholder="{{ $claimLabel }} 사유" style="height:38px; border:1px solid #d0d5dd; padding:0 10px;">
                                <select name="pickup_method" required class="pickup-method" style="height:38px; border:1px solid #d0d5dd; padding:0 10px;">
                                    <option value="automatic">자동회수</option>
                                    <option value="manual">수동회수</option>
                                </select>
                                <div class="manual-pickup-fields" style="display:none; padding:10px; background:#fff; border:1px solid #d0d5dd;">
                                    <div style="font-size:12px; color:#475467; margin-bottom:8px;"><strong>보내실 곳</strong><br>{{ $item->manual_return_address }}</div>
                                    <input type="text" name="customer_courier_name" maxlength="100" placeholder="택배사 (나중에 등록 가능)" style="width:100%; height:36px; border:1px solid #d0d5dd; padding:0 8px; box-sizing:border-box; margin-bottom:6px;">
                                    <input type="text" name="customer_tracking_number" maxlength="100" placeholder="송장번호 (나중에 등록 가능)" style="width:100%; height:36px; border:1px solid #d0d5dd; padding:0 8px; box-sizing:border-box;">
                                </div>
                                <button type="submit" style="height:38px; border:0; border-radius:6px; background:#111827; color:#fff; font-weight:800; cursor:pointer;">{{ $claimLabel }} 신청</button>
                            </form>
                        @endforeach
                    </div>
                    @if($latestClaim && $latestClaim->pickup_method === 'manual')
                        <form action="{{ route('front.shop.order.claim.shipment', $latestClaim->id) }}" method="POST" style="margin:10px 0 16px; padding:14px; border:1px solid #f79009; background:#fffaeb; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            @csrf
                            <strong style="width:100%;">수동회수 발송정보</strong>
                            <span style="width:100%; font-size:13px;">반송지: {{ $latestClaim->return_address }}</span>
                            <input name="customer_courier_name" value="{{ $latestClaim->customer_courier_name }}" required placeholder="택배사" style="height:36px; border:1px solid #d0d5dd; padding:0 8px;">
                            <input name="customer_tracking_number" value="{{ $latestClaim->customer_tracking_number }}" required placeholder="송장번호" style="height:36px; border:1px solid #d0d5dd; padding:0 8px;">
                            <button type="submit" style="height:36px; border:0; background:#b54708; color:#fff; font-weight:800; padding:0 12px; cursor:pointer;">송장정보 저장</button>
                        </form>
                    @endif
                    <form action="{{ route('front.shop.order.inquiry') }}" method="POST" style="display:grid; grid-template-columns:160px 1fr; gap:8px; margin:0 0 18px; padding:14px; border:1px solid #e4e7ec;">
                        @csrf
                        <input type="hidden" name="order_product_id" value="{{ $item->id }}">
                        <select name="inquiry_category" required style="height:38px; border:1px solid #d0d5dd; padding:0 8px;">
                            <option value="">문의 분류</option><option value="delivery">배송문의</option><option value="claim">교환·반품</option><option value="product">상품관련</option><option value="payment">결제문의</option><option value="other">기타</option>
                        </select>
                        <input name="subject" required maxlength="255" placeholder="문의 제목" style="height:38px; border:1px solid #d0d5dd; padding:0 8px;">
                        <textarea name="message" required maxlength="3000" placeholder="문의 내용을 입력해 주세요." style="grid-column:1 / -1; min-height:76px; border:1px solid #d0d5dd; padding:8px;"></textarea>
                        <button type="submit" style="grid-column:2; justify-self:end; height:36px; border:0; border-radius:6px; background:#344054; color:#fff; padding:0 14px; font-weight:800; cursor:pointer;">문의하기</button>
                    </form>
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
<script>
document.querySelectorAll('.pickup-method').forEach(function (select) {
    function toggleManualFields() {
        var fields = select.closest('form').querySelector('.manual-pickup-fields');
        fields.style.display = select.value === 'manual' ? 'block' : 'none';
    }
    select.addEventListener('change', toggleManualFields);
    toggleManualFields();
});
</script>
@endsection
