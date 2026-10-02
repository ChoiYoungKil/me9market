@foreach(['cancel'=>'취소신청', 'return'=>'반품신청', 'exchange'=>'교환신청', 'confirm'=>'구매확정'] as $action=>$label)
    @if(\App\Support\OrderItemStatus::customerActionAllowed($action, $item->normalized_status))
    <dialog id="{{ $action }}-{{ $item->id }}" class="shop-dialog" aria-labelledby="{{ $action }}-title-{{ $item->id }}">
        <div class="shop-dialog-head"><h2 id="{{ $action }}-title-{{ $item->id }}">{{ $label }}</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
        <div class="shop-dialog-body"><form method="POST" action="{{ route('front.shop.order.item.status', $item->id) }}">
            @csrf<input type="hidden" name="action" value="{{ $action }}">
            <strong>{{ $item->product_name }}</strong>
            @if($action === 'confirm')<p>상품을 받으셨나요? 구매확정 후에는 취소·반품·교환 신청이 제한됩니다.</p>
                @include('shop.partials.rating_input', ['ratingId' => 'shop-'.$item->id])
                <label>구매 후기<textarea name="review" class="shop-control" maxlength="2000"></textarea></label>
            @else
                <label>신청 사유<input class="shop-control" name="reason" required maxlength="255" value="{{ old('reason') }}"></label>
                @if(in_array($action, ['return','exchange']))
                    <label>회수방법<select name="pickup_method" data-pickup class="shop-control" required><option value="automatic">자동회수</option><option value="manual">수동회수</option></select></label>
                    <div data-manual hidden><p>보내실 곳: {{ $item->manual_return_address }}</p><label>택배사<input name="customer_courier_name" class="shop-control" maxlength="100"></label><label>송장번호<input name="customer_tracking_number" class="shop-control" maxlength="100"></label></div>
                @endif
            @endif
            <button type="submit" class="shop-btn primary">{{ $label }}</button>
        </form></div>
    </dialog>
    @endif
@endforeach
<dialog id="inquiry-{{ $item->id }}" class="shop-dialog" aria-labelledby="inquiry-title-{{ $item->id }}">
    <div class="shop-dialog-head"><h2 id="inquiry-title-{{ $item->id }}">상품 문의</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
    <div class="shop-dialog-body"><form method="POST" action="{{ route('front.shop.order.inquiry') }}">
        @csrf<input type="hidden" name="order_product_id" value="{{ $item->id }}">
        <strong>{{ $item->product_name }}</strong>
        <label>문의 분류<select name="inquiry_category" class="shop-control" required><option value="">선택</option><option value="delivery">배송문의</option><option value="claim">교환·반품</option><option value="product">상품관련</option><option value="payment">결제문의</option><option value="other">기타</option></select></label>
        <label>제목<input name="subject" class="shop-control" maxlength="255" required></label>
        <label>문의 내용<textarea name="message" class="shop-control" maxlength="3000" required></textarea></label>
        <button type="submit" class="shop-btn primary">문의하기</button>
    </form></div>
</dialog>
