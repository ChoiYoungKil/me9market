document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('joint-list');
    if (list) {
        const toggle = document.getElementById('joint-search-toggle');
        toggle.addEventListener('click', () => {
            const details = document.getElementById('joint-search-details');
            details.hidden = !details.hidden;
            toggle.setAttribute('aria-expanded', String(!details.hidden));
        });
        const all = document.getElementById('joint-all-states');
        const states = [...list.querySelectorAll('[name="states[]"]')];
        all.addEventListener('change', () => { if (all.checked) states.forEach(s => s.checked = false); });
        states.forEach(s => s.addEventListener('change', () => all.checked = !states.some(s => s.checked)));
        list.querySelector('[name=per_page]').addEventListener('change', () => document.getElementById('joint-search').requestSubmit());
        const dialog = document.getElementById('joint-delete-dialog');
        list.querySelectorAll('[data-delete-joint]').forEach(button => button.addEventListener('click', () => {
            document.getElementById('joint-delete-name').textContent = button.dataset.jointName;
            document.getElementById('joint-delete-form').action = '/channel/joint-purchase/delete/' + button.dataset.deleteJoint;
            dialog.showModal();
        }));
        document.getElementById('joint-delete-cancel').addEventListener('click', () => dialog.close());
        list.dataset.ready = 'true';
    }
    const form = document.getElementById('joint-form');
    if (!form) return;
    const state = JSON.parse(document.getElementById('joint-form-state').textContent);
    const find = name => form.querySelector(`[name="${name}"]`);
    const checked = name => form.querySelector(`[name="${name}"]:checked`)?.value;
    const group = (id, enabled) => {
        const box = document.getElementById(id); box.hidden = !enabled;
        box.querySelectorAll('input,select,textarea').forEach(input => input.disabled = !enabled);
    };
    function update() {
        group('joint-period', checked('period_enabled') === '1');
        group('joint-detail-images', checked('detail_display_type') === 'image');
        group('joint-detail-text', checked('detail_display_type') === 'text');
        group('joint-manager-fields', checked('order_manager_enabled') === '1');
        group('joint-shipping-base', find('shipping_policy_type').value !== 'free');
        group('joint-shipping-threshold', find('shipping_policy_type').value === 'free_conditional');
        document.getElementById('joint-policy-preview').textContent = state.policies[find('cancel_refund_policy_id').value] || '총관리자가 등록한 기본 안내가 없습니다.';
    }
    form.addEventListener('change', update); update();
    const major = document.getElementById('joint-major'), middle = document.getElementById('joint-middle'), minor = document.getElementById('joint-minor');
    const populate = (select, rows, selected) => {
        select.replaceChildren(new Option(select.getAttribute('aria-label') + ' 선택', ''));
        rows.forEach(row => select.add(new Option(row.name, row.id, false, String(selected) === String(row.id))));
        select.disabled = !rows.length;
    };
    const match = (rows, id) => rows.find(row => String(row.id) === String(id));
    function notice() {
        const name = match(state.categories, major.value)?.name || '';
        const template = Object.values(state.noticeTemplates).find(t => t.keywords.some(k => name.replace(/\s/g, '').includes(k.replace(/\s/g, ''))));
        const panel = document.getElementById('joint-notice-panel'), fields = document.getElementById('joint-notice-fields');
        fields.querySelectorAll('textarea').forEach(input => state.noticeItems[input.dataset.key] = input.value);
        fields.replaceChildren(); panel.hidden = !template;
        if (!template) return;
        document.getElementById('joint-notice-title').textContent = `(${template.title})`;
        template.fields.forEach(field => {
            const label = document.createElement('label'); label.className = 'wide'; label.textContent = field.label;
            const input = document.createElement('textarea'); input.rows = 2; input.maxLength = 2000;
            input.name = `product_notice_items[${field.key}]`; input.dataset.key = field.key; input.value = state.noticeItems[field.key] || '';
            label.append(input); fields.append(label);
        });
    }
    populate(major, state.categories, major.dataset.selected);
    populate(middle, match(state.categories, major.value)?.children || [], middle.dataset.selected);
    populate(minor, match(match(state.categories, major.value)?.children || [], middle.value)?.children || [], minor.dataset.selected);
    major.addEventListener('change', () => { populate(middle, match(state.categories, major.value)?.children || [], ''); populate(minor, [], ''); notice(); });
    middle.addEventListener('change', () => populate(minor, match(match(state.categories, major.value)?.children || [], middle.value)?.children || [], ''));
    notice();
    const editor = document.getElementById('joint-editor');
    form.querySelectorAll('[data-joint-command]').forEach(button => button.addEventListener('mousedown', event => {
        event.preventDefault(); editor.focus(); document.execCommand(button.dataset.jointCommand);
    }));
    form.addEventListener('submit', event => {
        if (checked('detail_display_type') === 'text') {
            if (!editor.innerText.trim()) {event.preventDefault(); editor.focus(); return;}
            find('detail_text').value = editor.innerHTML;
        }
    });
    const benefitRows = document.getElementById('benefit-rows');
    document.getElementById('benefit-add')?.addEventListener('click', () => {
        const quantity = Number(document.getElementById('benefit-quantity').value);
        const amount = Number(document.getElementById('benefit-amount').value);
        const error = document.getElementById('benefit-error');
        error.hidden = true;
        let message = '';
        if (!Number.isInteger(quantity) || quantity < 1 || amount <= 0) message = '판매 수량과 개당 할인액을 입력해 주세요.';
        else if (amount > Number(find('sale_price').value)) message = '할인액은 상품 판매가 이하로 입력해 주세요.';
        else if ([...benefitRows.children].some(row => Number(row.dataset.quantity) === quantity)) message = '판매 수량 기준은 중복할 수 없습니다.';
        if (message) {error.textContent = message; error.hidden = false; return;}
        const row = document.createElement('div'); row.className = 'benefit-row'; row.dataset.quantity = quantity;
        const text = document.createElement('span'); text.textContent = `${quantity.toLocaleString()}개 이상일 경우 개당 ${amount.toLocaleString()}원 할인`;
        row.append(text);
        [['benefit_quantity[]', quantity], ['benefit_amount[]', amount]].forEach(([name, value]) => {const input = document.createElement('input'); input.type = 'hidden'; input.name = name; input.value = value; row.append(input);});
        const button = document.createElement('button'); button.type = 'button'; button.dataset.removeBenefit = ''; button.textContent = '삭제'; row.append(button);
        benefitRows.append(row); document.getElementById('benefit-quantity').value = ''; document.getElementById('benefit-amount').value = '';
    });
    benefitRows.addEventListener('click', event => event.target.closest('[data-remove-benefit]')?.closest('.benefit-row').remove());
    form.querySelectorAll('.joint-image-slot input[type=file]').forEach(input => input.addEventListener('change', () => {
        const file = input.files[0]; if (!file) return;
        const image = input.closest('label').querySelector('img');
        if (image.dataset.objectUrl) URL.revokeObjectURL(image.dataset.objectUrl);
        image.dataset.objectUrl = URL.createObjectURL(file); image.src = image.dataset.objectUrl; image.hidden = false;
    }));
    const dialog = document.getElementById('joint-manager-dialog');
    document.getElementById('joint-manager-find').addEventListener('click', () => dialog.showModal());
    document.getElementById('joint-manager-close').addEventListener('click', () => dialog.close());
    const search = () => {
        const q = document.getElementById('joint-manager-query').value.toLowerCase().trim(); let count = 0;
        dialog.querySelectorAll('.joint-manager-row').forEach(row => {row.hidden = !row.dataset.search.includes(q); if (!row.hidden) count++;});
        document.getElementById('joint-manager-empty').hidden = count > 0;
    };
    document.getElementById('joint-manager-query').addEventListener('input', search);
    document.getElementById('joint-manager-search').addEventListener('click', search);
    dialog.addEventListener('click', event => {const button = event.target.closest('[data-pick-manager]'); if (button) {find('distributor_id').value = button.dataset.pickManager; dialog.close();}});
    form.dataset.ready = 'true';
});
