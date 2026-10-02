<dialog id="claim-{{ $claim->id }}" class="shop-dialog" aria-labelledby="claim-title-{{ $claim->id }}">
    <div class="shop-dialog-head"><h2 id="claim-title-{{ $claim->id }}">{{ ['cancel'=>'취소', 'return'=>'반품', 'exchange'=>'교환'][$claim->type] }} 상세</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
    <div class="shop-dialog-body">
        <dl class="shop-meta">
            <dt>상품</dt><dd>{{ $item->product_name }}</dd><dt>처리상태</dt><dd>{{ $item->status_label }}</dd>
            <dt>신청일</dt><dd>{{ $claim->created_at?->format('Y-m-d H:i') }}</dd>
            <dt>신청 사유</dt><dd>{{ $claim->detail_reason ?: $claim->reason }}</dd>
            <dt>판매자 답변</dt><dd>{{ $claim->admin_comment ?: '처리 중입니다.' }}</dd>
            <dt>결제수단</dt><dd>{{ $order->payment_method }}</dd>
            @if($item->refund_status)
                <dt>환불상태</dt><dd>{{ $item->refund_status_label }}</dd>
                @if($item->refund_status !== 'reversed_original')
                    <dt>현금 환불금액</dt><dd>{{ number_format($item->refund_cash_amount) }}원</dd>
                    <dt>사용 포인트</dt><dd>{{ number_format($item->used_point_amount ?? 0) }}P</dd>
                @endif
            @endif
            @if($claim->type !== 'cancel')
                <dt>회수방법</dt><dd>{{ $claim->pickup_method === 'manual' ? '수동회수' : '자동회수' }}</dd>
                <dt>반송지</dt><dd>{{ $claim->return_address ?: '판매자가 회수 정보를 안내합니다.' }}</dd>
                <dt>추가 배송비</dt><dd>{{ number_format($claim->type === 'return' ? $item->return_shipping_fee : $item->exchange_shipping_fee) }}원</dd>
            @endif
            @if($item->exchangeReplacement)<dt>교환상품</dt><dd>{{ $item->exchangeReplacement->product_name }} / {{ $item->exchangeReplacement->status_label }}<br>{{ $item->exchangeReplacement->courier_name }} {{ $item->exchangeReplacement->tracking_number }}</dd>@endif
        </dl>
        @if($claim->pickup_method === 'manual')
            <form action="{{ route('front.shop.order.claim.shipment', $claim->id) }}" method="POST" class="shop-section">
                @csrf<h3>수동회수 발송정보</h3>
                <label>택배사<input class="shop-control" name="customer_courier_name" value="{{ $claim->customer_courier_name }}" required maxlength="100"></label>
                <label>송장번호<input class="shop-control" name="customer_tracking_number" value="{{ $claim->customer_tracking_number }}" required maxlength="100"></label>
                <button class="shop-btn primary" type="submit">송장정보 저장</button>
            </form>
        @endif
    </div>
</dialog>
