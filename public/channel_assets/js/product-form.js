document.addEventListener('DOMContentLoaded', () => {
    const scope = document.getElementById('sale_scope');
    if (!scope) return;
    const publicBox = document.getElementById('share_public');
    const affiliateBox = document.getElementById('share_affiliate');
    const number = name => Number(document.querySelector(`[name="${name}"]`)?.value || 0);
    const checked = name => document.querySelector(`[name="${name}"]:checked`)?.value;
    const money = value => Math.ceil(Math.round(value * 1e6) / 1e6 / 10) * 10;
    const preview = document.getElementById('price-calculation-preview');
    function calculate() {
        if (scope.value === 'own' || checked('price_constraint_enabled') !== '1') {
            preview.hidden = true;
            return;
        }
        preview.hidden = false;
        const base = number('product_price');
        if (checked('price_constraint_type') === 'fixed') {
            const type = document.getElementById('profit_share_type').value;
            const value = number('profit_share_value');
            // Preserve legacy gross-price percentage while new PPT entries use base-price percentage.
            const price = type === 'percent' ? (value < 100 ? base / (1 - value / 100) : NaN)
                : base + (type === 'base_percent' ? base * value / 100 : type === 'fixed' ? value : 0);
            const field = document.querySelector('[name="price_fixed"]');
            field.value = Number.isFinite(price) ? money(price) : '';
            preview.textContent = Number.isFinite(price) ? `판매가격 ${money(price).toLocaleString()}원 (상품가격 + 공유판매자 이익금, 십원 단위 올림)` : '비율을 확인해 주세요.';
        } else {
            const delivery = document.getElementById('shipping_policy_type').value === 'free' ? 0 : number('shipping_base_fee');
            const minimum = money((base + delivery) / (1 - .05 * 1.1) - delivery);
            preview.textContent = `최소판매비용 예시 ${minimum.toLocaleString()}원 · 수수료 5%, 부가세 1.1 적용. 실제 채널 판매등록 시 해당 회원사의 수수료로 다시 검증합니다.`;
        }
    }
    function sharingChanged() {
        scope.value = publicBox.checked ? (affiliateBox.checked ? 'both' : 'public') : (affiliateBox.checked ? 'affiliate' : 'own');
        if (scope.value === 'own') document.getElementById('price_constraint_disabled').checked = true;
        document.getElementById('price_constraint_disabled').dispatchEvent(new Event('change', { bubbles: true }));
        calculate();
    }
    publicBox.addEventListener('change', sharingChanged);
    affiliateBox.addEventListener('change', sharingChanged);
    document.querySelector('.product-base-form').addEventListener('input', calculate);
    document.querySelector('.product-base-form').addEventListener('change', calculate);
    const dialog = document.getElementById('order-manager-dialog');
    document.getElementById('find-order-manager').addEventListener('click', () => dialog.showModal());
    document.getElementById('manager-close').addEventListener('click', () => dialog.close());
    function search() {
        const query = document.getElementById('manager-query').value.trim().toLowerCase();
        let count = 0;
        dialog.querySelectorAll('[data-manager-row]').forEach(row => {
            row.hidden = !row.dataset.search.includes(query);
            if (!row.hidden) count++;
        });
        document.getElementById('manager-empty').hidden = count > 0;
    }
    document.getElementById('manager-search').addEventListener('click', search);
    document.getElementById('manager-query').addEventListener('input', search);
    dialog.addEventListener('click', event => {
        const button = event.target.closest('[data-select-manager]');
        if (!button) return;
        document.getElementById('order_manager_select').value = button.dataset.selectManager;
        dialog.close();
    });
    sharingChanged();
    scope.dataset.formReady = 'true';
});
