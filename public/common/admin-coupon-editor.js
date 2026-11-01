(() => {
    'use strict';
    const form = document.getElementById('coupon-editor-form');
    if (!form) return;
    function update() {
        const manual = form.querySelector('input[name="coupon_option"]:checked')?.value === 'Manual';
        const field = document.getElementById('couponField');
        if (field) {
            field.hidden = !manual;
            field.querySelector('input').required = manual;
        }
        const amountType = form.querySelector('input[name="amount_type"]:checked')?.value;
        document.getElementById('coupon-amount-unit').textContent = amountType === 'Fixed' ? '원' : amountType === 'Percentage' ? '%' : '';
    }
    form.addEventListener('change', update);
    // Native reset restores checked values after the reset event has been dispatched.
    form.addEventListener('reset', () => setTimeout(update, 0));
    update();
})();
