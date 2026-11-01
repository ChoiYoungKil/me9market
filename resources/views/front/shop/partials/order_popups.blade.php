@foreach(['cancel'=>'취소신청', 'return'=>'반품신청', 'exchange'=>'교환신청', 'confirm'=>'구매확정'] as $action=>$label)
    @if(\App\Support\OrderItemStatus::customerActionAllowed($action, $item->normalized_status))
    <dialog id="{{ $action }}-{{ $item->id }}" class="shop-dialog" aria-labelledby="{{ $action }}-title-{{ $item->id }}">
        <div class="shop-dialog-head"><h2 id="{{ $action }}-title-{{ $item->id }}">{{ $label }}</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
        <div class="shop-dialog-body"><form method="POST" action="{{ route('front.shop.order.item.status', $item->id) }}">
            @csrf<input type="hidden" name="action" value="{{ $action }}">
            <div class="shop-claim-product"><img src="{{ $imageUrl }}" alt="{{ $item->product_name }}"><div><p>{{ $item->product?->category_path }}</p><strong>{{ $item->product_name }}</strong><p>{{ $item->product_size ?: '기본옵션' }} / {{ $item->product_qty }}개</p></div></div>
            @if($action === 'confirm')<p>상품을 받으셨나요? 구매확정 후에는 취소·반품·교환 신청이 제한됩니다.</p>
                @include('shop.partials.rating_input', ['ratingId' => 'shop-'.$item->id])
                <label>구매 후기<textarea name="review" class="shop-control" maxlength="2000"></textarea></label>
            @else
                <label>{{ ['cancel'=>'취소','return'=>'반품','exchange'=>'교환'][$action] }}사유<select class="shop-control" name="reason_code" data-claim-reason required>@foreach(\App\Support\ShopClaimReasons::for($action) as $code=>$reason)<option value="{{ $code }}">{{ $reason }}</option>@endforeach</select></label><div data-other-reason hidden><label>기타내용<textarea class="shop-control" name="reason_detail" maxlength="255" placeholder="기타 내용을 입력해 주세요"></textarea></label></div>
                @if(in_array($action, ['return','exchange']))
                    <fieldset class="shop-pickup-method"><legend>상품회수방법</legend><label class="shop-check"><input type="radio" name="pickup_method" value="automatic" data-claim-pickup checked>자동회수</label><label class="shop-check"><input type="radio" name="pickup_method" value="manual" data-claim-pickup>수동회수</label></fieldset><p class="shop-pickup-notice" data-automatic>상품에 {{ $action==='return' ? '반품' : '교환' }}이라고 표시해 주세요. 회수 일정과 택배사는 판매자가 안내합니다. 상품에 문제가 없는 경우 배송비가 발생할 수 있습니다.</p>
                    <div data-manual hidden><p>보내실 곳: {{ $item->manual_return_address }}</p><label>택배사<input name="customer_courier_name" class="shop-control" maxlength="100"></label><label>송장번호<input name="customer_tracking_number" class="shop-control" maxlength="100"></label></div>
                @endif
            @endif
            <div class="shop-actions"><button type="submit" class="shop-btn primary">{{ $label }}</button><button type="button" class="shop-btn" data-close>창닫기</button></div>
        </form></div>
    </dialog>
    @endif
@endforeach
<dialog id="inquiry-{{ $item->id }}" class="shop-dialog" aria-labelledby="inquiry-title-{{ $item->id }}">
    <div class="shop-dialog-head"><h2 id="inquiry-title-{{ $item->id }}">상품 문의하기</h2><button type="button" data-close class="shop-dialog-close" title="닫기" aria-label="닫기">&times;</button></div>
    <div class="shop-dialog-body"><form method="POST" action="{{ route('front.shop.order.inquiry') }}" data-product-inquiry-form>
        @csrf<input type="hidden" name="order_product_id" value="{{ $item->id }}">
        <div class="shop-claim-product"><img src="{{ $imageUrl }}" alt="{{ $item->product_name }}"><div><p>{{ $item->product?->category_path }}</p><strong>{{ $item->product_name }}</strong><p>{{ $item->product_size ?: '기본옵션' }} / {{ $item->product_qty }}개</p></div></div>
        <input type="hidden" name="inquiry_category" value="product"><h3 class="shop-inquiry-section-title">문의내용</h3><p>판매자에게 상품, 배송, 취소, 교환, 반품 등 궁금한 내용을 문의하세요.</p>
        <label for="order-inquiry-subject-{{ $item->id }}">제목<input id="order-inquiry-subject-{{ $item->id }}" name="subject" class="shop-control" maxlength="255" required></label>
        <label id="order-inquiry-message-label-{{ $item->id }}">질문</label>
        <div data-product-inquiry-editor>
            <textarea name="message" class="shop-control" maxlength="20000" required></textarea>
            <div data-inquiry-toolbar hidden><button type="button" class="ql-bold" aria-label="굵게"></button><button type="button" class="ql-italic" aria-label="기울임"></button><button type="button" class="ql-underline" aria-label="밑줄"></button><button type="button" class="ql-list" value="ordered" aria-label="번호 목록"></button><button type="button" class="ql-list" value="bullet" aria-label="글머리 목록"></button></div>
            <div data-inquiry-editor-body hidden></div><template></template><p data-inquiry-editor-error role="alert" hidden></p>
        </div>
        <div class="shop-actions"><button type="submit" class="shop-btn primary">문의하기</button><button type="button" class="shop-btn" data-close>창닫기</button></div>
    </form></div>
</dialog>

@once
@push('styles')<link rel="stylesheet" href="/channel_assets/vendor/quill/quill.snow.css">@endpush
@push('scripts')<script src="/channel_assets/vendor/quill/quill.min.js"></script><script src="{{ asset('shop/js/product-inquiry.js') }}?v={{ filemtime(public_path('shop/js/product-inquiry.js')) }}" defer></script>@endpush
@endonce
