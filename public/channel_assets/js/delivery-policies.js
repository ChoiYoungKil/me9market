(() => {
    const workspace = document.querySelector('[data-delivery-active-form]');
    if (!workspace) return;
    const dialogs = [...document.querySelectorAll('.delivery-dialog')];
    const previewVersions = new WeakMap();
    function resetForm(form) {
        form.reset();
        const tiers = form.querySelector('[data-tiers]');
        tiers.innerHTML = tiers.dataset.original;
        form.querySelector('[data-preview-result]').textContent = '';
        form.querySelector('[data-delivery-error]').textContent = '';
        previewVersions.set(form, (previewVersions.get(form) || 0) + 1);
        refresh(form);
    }
    function tiersRefresh(form) {
        const rows = [...form.querySelectorAll('[data-tier]')];
        rows.forEach((row, i) => {
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => { input.name = input.name.replace(/\[tiers\]\[\d+\]/, `[tiers][${i}]`); });
            inputs[0].value = i === 0 ? '0' : rows[i - 1].querySelectorAll('input')[1].value;
            const last = i === rows.length - 1;
            inputs[1].readOnly = last;
            inputs[1].required = !last;
            inputs[1].placeholder = last ? '상한 없음' : '상한 미만';
            inputs[1].min = String(Number(inputs[0].value) + 1);
            if (last) inputs[1].value = '';
            row.querySelector('[data-remove-tier]').disabled = rows.length === 1;
        });
    }
    function refresh(form) {
        const type = form.querySelector('[name="shipping_type"]:checked')?.value || 'free';
        form.querySelectorAll('[data-types]').forEach(section => { section.hidden = !section.dataset.types.split(',').includes(type); });
        const regional = form.querySelector('[name="region_enabled"]:checked')?.value === '1';
        const mode = form.querySelector('[name="calculation_rules[region_mode]"]').value;
        form.querySelector('[data-region-settings]').hidden = !regional;
        form.querySelector('[data-three-region]').hidden = mode !== 'three';
        form.querySelector('[data-jeju-label]').textContent = mode === 'two' ? '제주/도서산간 추가 배송비 (원)' : '제주지역 추가 배송비 (원)';
        form.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = !!input.closest('[hidden]'); });
        form.querySelectorAll('[data-threshold]').forEach(toggle => {
            const input = form.querySelector(`[data-threshold-input="${toggle.dataset.threshold}"]`);
            input.disabled = type !== 'conditional' || !toggle.checked;
            input.required = type === 'conditional' && toggle.checked;
        });
        const basis = form.querySelector('[name="calculation_rules[basis]"]:checked')?.value || 'amount';
        form.querySelectorAll('[data-basis-unit]').forEach(unit => { unit.textContent = basis === 'amount' ? '원' : '개'; });
        tiersRefresh(form);
    }
    async function preview(form) {
        const result = form.querySelector('[data-preview-result]');
        const button = form.querySelector('[data-preview-delivery]');
        const version = (previewVersions.get(form) || 0) + 1;
        previewVersions.set(form, version);
        button.disabled = true;
        result.textContent = '계산 중입니다.';
        try {
            const response = await fetch(form.dataset.previewUrl, { method:'POST', body:new FormData(form), headers:{'Accept':'application/json'} });
            const data = await response.json();
            if (version !== previewVersions.get(form)) return;
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join('\n') || '배송비를 계산할 수 없습니다. 입력값을 확인해 주세요.');
            result.textContent = data.message;
        } catch (error) {
            if (version === previewVersions.get(form)) {
                result.textContent = error.message || '계산에 실패했습니다. 다시 시도해 주세요.';
                if (result.textContent.includes('우편번호')) form.querySelector('.delivery-postcodes').open = true;
            }
        } finally { button.disabled = false; }
    }
    dialogs.forEach(dialog => {
        dialog.querySelectorAll('[data-delivery-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => {
            const form = dialog.querySelector('[data-delivery-form]');
            if (form) resetForm(form);
            if (workspace.dataset.deliveryActiveForm && dialog.id === `delivery-${workspace.dataset.deliveryActiveForm}`) window.location.assign(window.location.pathname);
        });
    });
    document.querySelectorAll('[data-delivery-open]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.deliveryOpen)?.showModal()));
    document.querySelectorAll('[data-delivery-form]').forEach(form => {
        const tiers = form.querySelector('[data-tiers]');
        tiers.dataset.original = tiers.innerHTML;
        form.addEventListener('change', () => refresh(form));
        form.addEventListener('input', () => {
            tiersRefresh(form);
            previewVersions.set(form, (previewVersions.get(form) || 0) + 1);
            const result = form.querySelector('[data-preview-result]');
            if (result.textContent) result.textContent = '조건이 변경되었습니다. 다시 계산해 주세요.';
        });
        form.addEventListener('invalid', event => { const details = event.target.closest('details'); if (details) details.open = true; }, true);
        form.querySelector('[data-add-tier]').addEventListener('click', () => {
            const rows = tiers.querySelectorAll('[data-tier]');
            if (rows.length >= 50) return;
            const last = rows[rows.length - 1];
            const inputs = last.querySelectorAll('input');
            const increment = form.querySelector('[name="calculation_rules[basis]"]:checked').value === 'amount' ? 10000 : 1;
            inputs[1].value = String(Number(inputs[0].value) + increment);
            const row = last.cloneNode(true);
            row.querySelectorAll('input')[2].value = '0';
            tiers.appendChild(row);
            previewVersions.set(form, (previewVersions.get(form) || 0) + 1);
            form.querySelector('[data-preview-result]').textContent = '조건이 변경되었습니다. 다시 계산해 주세요.';
            refresh(form);
        });
        tiers.addEventListener('click', event => {
            if (event.target.matches('[data-remove-tier]') && tiers.children.length > 1) { event.target.closest('[data-tier]').remove(); previewVersions.set(form, (previewVersions.get(form) || 0) + 1); form.querySelector('[data-preview-result]').textContent = '조건이 변경되었습니다. 다시 계산해 주세요.'; refresh(form); }
        });
        form.querySelector('[data-preview-delivery]').addEventListener('click', () => preview(form));
        form.querySelector('.delivery-preview').addEventListener('keydown', event => { if (event.key === 'Enter' && event.target.matches('input')) { event.preventDefault(); preview(form); } });
        refresh(form);
    });
    document.querySelectorAll('[data-delivery-delete]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm('배송비 설정을 삭제하시겠습니까? 삭제 후 복원할 수 없습니다.')) event.preventDefault(); }));
    let viewVersion = 0;
    const view = document.getElementById('delivery-view');
    const body = view.querySelector('[data-delivery-details]');
    view.addEventListener('close', () => { viewVersion++; body.textContent = ''; });
    document.querySelectorAll('[data-delivery-view]').forEach(link => link.addEventListener('click', async event => {
        event.preventDefault();
        const version = ++viewVersion;
        body.textContent = '배송비 정보를 불러오고 있습니다.';
        view.showModal();
        try {
            const response = await fetch(link.href, {headers:{'Accept':'text/html'}});
            if (!response.ok) throw new Error();
            const html = await response.text();
            if (version === viewVersion) body.innerHTML = html;
        } catch { if (version === viewVersion) body.textContent = '배송비 정보를 불러올 수 없습니다. 창을 닫고 다시 시도해 주세요.'; }
    }));
    if (workspace.dataset.deliveryActiveForm) document.getElementById(`delivery-${workspace.dataset.deliveryActiveForm}`)?.showModal();
})();
