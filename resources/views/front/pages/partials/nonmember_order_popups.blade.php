@foreach($actions as $action=>$label)
<dialog id="{{ $action === 'inquiry' ? 'qna' : $action }}-modal" class="market-order-dialog" data-action="{{ $action }}" aria-labelledby="{{ $action }}-modal-title">
    <div class="modal-box">
        <header class="market-order-dialog-head"><h2 id="{{ $action }}-modal-title">{{ $action === 'inquiry' ? '상품 문의하기' : $label.'하기' }}</h2><button type="button" data-close aria-label="창 닫기">×</button></header>
        <div data-modal-errors hidden class="market-alert market-alert-error" role="alert"></div>
        <form action="{{ route($action === 'inquiry' ? 'front.nonmember.order_inquiry.submit' : 'front.nonmember.order_claim.submit') }}" method="POST">
            @csrf<input type="hidden" name="order_id" value="{{ $order->id }}"><input type="hidden" name="order_product_id"><input type="hidden" name="type" value="{{ $action }}">
            <p class="market-order-field-title">주문상품</p>
            <div class="market-modal-product"><img data-modal-image alt="" width="64" height="64"><div><strong data-modal-name></strong><p data-modal-option></p><p data-modal-category></p></div></div>
            @if($action === 'confirm')
                <p class="market-order-help">상품을 받으셨나요? 구매 확정 후에는 취소·반품·교환 신청이 제한됩니다.</p>
                @include('shop.partials.rating_input', ['ratingId'=>'guest-confirm'])
                <label for="confirmation-review">구매 후기 (선택)</label><textarea id="confirmation-review" name="review" maxlength="2000" placeholder="상품에 대한 평가와 만족도를 작성해 주세요."></textarea>
            @elseif($action === 'inquiry')
                <label for="inquiry-category">문의 분류</label><select name="inquiry_category" id="inquiry-category" required><option value="">선택해 주세요</option><option value="delivery">배송문의</option><option value="claim">교환·반품</option><option value="product">상품관련</option><option value="payment">결제문의</option><option value="other">기타</option></select>
                <label for="inquiry-subject">질문 제목</label><input id="inquiry-subject" name="subject" maxlength="255" required placeholder="제목을 입력해 주세요">
                <label for="inquiry-message">문의내용</label><textarea id="inquiry-message" name="message" maxlength="3000" required placeholder="판매자에게 상품, 배송, 취소, 교환, 반품 등 궁금한 내용을 문의하세요."></textarea>
            @else
                <label for="{{ $action }}_reason">{{ ['cancel'=>'취소','return'=>'반품','exchange'=>'교환'][$action] }}사유</label>
                <select name="reason" id="{{ $action }}_reason" data-claim-reason required>
                    @php($reasons = match($action) { 'cancel'=>['고객 단순 변심','배송 지연','기타'], 'return'=>['단순 변심','상품 불량','오배송','기타'], default=>['다른 상품이 배송되었습니다','상품 불량','사이즈 변경','색상 변경','기타'] })
                    @foreach($reasons as $reason)<option value="{{ $reason }}">{{ $reason }}</option>@endforeach
                </select>
                @if(in_array($action,['return','exchange']))
                    <fieldset class="market-order-pickup"><legend>상품회수방법</legend><label><input type="radio" name="recovery_method" value="자동회수" checked>자동회수</label><label><input type="radio" name="recovery_method" value="수동회수">수동회수</label></fieldset>
                    <p class="market-order-help" data-auto-pickup>자동회수로 접수됩니다. 회수 일정은 판매자가 안내합니다. 상품 문제 유무에 따라 회수 배송비가 발생할 수 있습니다.</p>
                    <div data-manual-pickup hidden><p class="market-order-field-title">상품회수주소</p><p class="market-order-return-address" data-return-address></p><p class="market-order-help">위 주소로 직접 발송한 뒤 택배사와 송장번호를 입력해 주세요.</p><label>택배사<input name="customer_courier_name" maxlength="100" placeholder="택배사 (선택)"></label><label>송장번호<input name="customer_tracking_number" maxlength="100" placeholder="송장번호 (선택)"></label></div>
                @endif
                <div data-other-reason id="{{ $action }}_etc_wrap" hidden><label for="{{ $action }}_detail_reason">기타내용</label><textarea id="{{ $action }}_detail_reason" name="detail_reason" maxlength="2000" placeholder="기타 내용을 입력해 주세요." disabled></textarea></div>
            @endif
            <footer class="market-order-dialog-actions"><button type="submit">{{ $action === 'confirm' ? '구매확정하기' : ($action === 'inquiry' ? '문의하기' : $label) }}</button><button type="button" data-close>창닫기</button></footer>
        </form>
    </div>
</dialog>
@endforeach
