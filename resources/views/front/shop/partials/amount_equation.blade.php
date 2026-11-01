<div class="shop-summary shop-cart-summary shop-amount-equation" aria-label="상품금액과 배송비 합계">
    <div><span>총 상품금액</span><strong>{{ number_format($totals['subtotal']) }}원</strong></div><span class="shop-amount-operator" aria-hidden="true">+</span>
    <div><span>배송비</span><strong @if($live ?? false) data-equation-shipping @endif>{{ number_format($totals['shipping']) }}원</strong></div><span class="shop-amount-operator" aria-hidden="true">=</span>
    <div class="total"><span>{{ $amountLabel ?? '최종 결제금액' }}</span><strong @if($live ?? false) data-equation-total @endif>{{ number_format($totals['total']) }}원</strong></div>
</div>
