(() => {
    'use strict';
    const rows = document.getElementById('option-input-rows');
    if (!rows) return;
    const add = document.getElementById('add-option-row');
    const feedback = document.getElementById('option-row-feedback');
    function refreshRows() {
        const items = rows.querySelectorAll('.option-input-row');
        items.forEach((row, index) => {
            const remove = row.querySelector('[data-remove-option]');
            remove.disabled = items.length === 1;
            remove.setAttribute('aria-label', `${index + 1}번째 입력행 삭제`);
        });
        add.disabled = items.length >= 50;
        feedback.textContent = `입력행 ${items.length}개 · 최대 50개까지 함께 등록할 수 있습니다.`;
    }
    add.addEventListener('click', () => {
        if (rows.children.length >= 50) return;
        const row = rows.firstElementChild.cloneNode(true);
        row.querySelectorAll('input').forEach(input => { input.value = ''; });
        rows.append(row);
        refreshRows();
        row.querySelector('input').focus();
    });
    rows.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove-option]');
        if (!remove || rows.children.length <= 1) return;
        remove.closest('.option-input-row').remove();
        refreshRows();
    });
    refreshRows();

    document.querySelectorAll('[data-option-status]').forEach(button => {
        button.addEventListener('click', async () => {
            const feedback = document.getElementById('option-status-feedback');
            button.disabled = true;
            try {
                const response = await fetch('/admin/update-attribute-status', {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({attribute_id: button.dataset.optionStatus,
                        status: button.dataset.active === '1' ? 'Active' : 'Inactive'})
                });
                if (!response.ok) throw new Error('옵션 상태를 변경하지 못했습니다. 새로고침 후 다시 시도해 주세요.');
                const data = await response.json();
                const active = Number(data.status) === 1;
                button.dataset.active = active ? '1' : '0';
                button.textContent = active ? '판매중' : '판매중지';
                button.classList.toggle('primary', active);
                const name = button.closest('tr').querySelector('.options-name').textContent.trim();
                button.setAttribute('aria-label', `${name} ${active ? '판매중지로 변경' : '판매중으로 변경'}`);
                feedback.classList.remove('error');
                feedback.textContent = `${name} 옵션을 ${active ? '판매중' : '판매중지'} 상태로 변경했습니다.`;
            } catch (error) {
                feedback.classList.add('error');
                feedback.textContent = error.message;
            } finally {
                feedback.hidden = false;
                button.disabled = false;
            }
        });
    });
})();
