(() => {
    'use strict';
    const root = document.querySelector('[data-order-view]');
    if (!root) return;
    const dialogs = [...document.querySelectorAll('.market-order-dialog')];
    let previousOverflow = '';
    function otherReason(dialog) {
        const wrap = dialog.querySelector('[data-other-reason]');
        if (!wrap) return;
        const other = dialog.querySelector('[data-claim-reason]').value === '기타';
        wrap.hidden = !other;
        wrap.querySelector('textarea').disabled = !other;
        wrap.querySelector('textarea').required = other;
    }
    function pickup(dialog) {
        const selected = dialog.querySelector('[name=recovery_method]:checked');
        if (!selected) return;
        const manual = selected.value === '수동회수';
        dialog.querySelector('[data-auto-pickup]').hidden = manual;
        const fields = dialog.querySelector('[data-manual-pickup]');
        fields.hidden = !manual;
        fields.querySelectorAll('input').forEach(input => { input.disabled = !manual; });
    }
    function open(button) {
        const dialog = dialogs.find(item => item.dataset.action === button.dataset.orderAction);
        const card = button.closest('[data-order-item]');
        if (!dialog || !card) return;
        button.focus({preventScroll:true});
        dialog.querySelector('form').reset();
        dialog.querySelector('[data-modal-errors]').hidden = true;
        dialog.querySelector('[name=order_product_id]').value = card.dataset.orderItem;
        dialog.querySelector('[data-modal-name]').textContent = card.querySelector('h3').textContent;
        dialog.querySelector('[data-modal-option]').textContent = card.querySelector('.market-order-option').textContent;
        dialog.querySelector('[data-modal-category]').textContent = card.querySelector('.market-order-category')?.textContent || '';
        dialog.querySelector('[data-modal-image]').src = card.querySelector('.market-order-image').src;
        const address = dialog.querySelector('[data-return-address]');
        if (address) address.textContent = card.dataset.returnAddress;
        dialog.querySelector('.me9-rating-clear')?.click();
        otherReason(dialog); pickup(dialog);
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        dialog.showModal();
    }
    root.querySelectorAll('[data-order-action]').forEach(button => button.addEventListener('click', () => open(button)));
    dialogs.forEach(dialog => {
        dialog.querySelectorAll('[data-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => { document.body.style.overflow = previousOverflow; });
        dialog.querySelector('[data-claim-reason]')?.addEventListener('change', () => otherReason(dialog));
        dialog.querySelectorAll('[name=recovery_method]').forEach(input => input.addEventListener('change', () => pickup(dialog)));
        dialog.querySelector('form').addEventListener('submit', () => {
            const submit = dialog.querySelector('[type=submit]');
            submit.disabled = true; submit.dataset.label = submit.textContent; submit.textContent = '접수 중…';
        });
    });
    window.addEventListener('pageshow', () => dialogs.forEach(dialog => {
        const submit = dialog.querySelector('[type=submit]');
        submit.disabled = false; if (submit.dataset.label) submit.textContent = submit.dataset.label;
    }));
    document.querySelectorAll('.market-order-image,[data-modal-image]').forEach(image => image.addEventListener('error', () => {
        const fallback = '/front/images/product_images/small/no-image.png';
        if (!image.src.endsWith(fallback)) image.src = fallback;
    }));
    if (root.dataset.retryAction) {
        const button = [...root.querySelectorAll('[data-order-action]')].find(item => item.dataset.orderAction === root.dataset.retryAction && item.dataset.orderProduct === root.dataset.retryItem);
        if (button) {
            open(button);
            const dialog = dialogs.find(item => item.open);
            const values = JSON.parse(document.getElementById('order-retry-values').textContent);
            Object.entries(values).forEach(([name,value]) => {
                const fields = [...dialog.querySelectorAll('[name]')].filter(field => field.name === name);
                fields.forEach(field => {
                    if (field.type === 'radio') field.checked = field.value === value;
                    else if (field.tagName !== 'SELECT' || [...field.options].some(option => option.value === value)) field.value = value;
                });
            });
            otherReason(dialog); pickup(dialog);
            const errors = root.querySelector('[data-order-errors]');
            if (errors) {
                const target = dialog.querySelector('[data-modal-errors]');
                target.replaceChildren(...[...errors.children].map(item => item.cloneNode(true))); target.hidden = false;
            }
        }
    }
})();
