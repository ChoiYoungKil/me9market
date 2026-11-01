(() => {
    'use strict';
    const form = document.getElementById('product-image-form');
    if (!form) return;
    const input = document.getElementById('images');
    const preview = document.getElementById('image-selection-preview');
    const summary = document.getElementById('image-selection-summary');
    const error = document.getElementById('image-selection-error');
    const submit = document.getElementById('upload-product-images');
    const clear = document.getElementById('clear-image-selection');
    let urls = [];
    const release = () => { urls.forEach(url => URL.revokeObjectURL(url)); urls = []; };
    function updateSelection(reset = false) {
        release();
        preview.replaceChildren();
        const files = reset ? [] : Array.from(input.files);
        const invalid = files.length > 10 ? '이미지는 한 번에 최대 10개까지 등록할 수 있습니다.' :
            files.some(file => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) ? 'JPG, PNG, WEBP 이미지 파일을 선택해 주세요.' :
            files.some(file => file.size > 5 * 1024 * 1024) ? '파일당 용량은 5MB 이하여야 합니다.' : '';
        input.setCustomValidity(invalid);
        error.textContent = invalid;
        error.hidden = !invalid;
        summary.textContent = files.length ? `${files.length}개 이미지 선택` : '선택한 이미지가 없습니다.';
        submit.disabled = !files.length || !!invalid;
        clear.disabled = !files.length;
        if (invalid) return;
        files.forEach(file => {
            const card = document.createElement('figure');
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            urls.push(img.src);
            img.alt = file.name;
            const caption = document.createElement('figcaption');
            caption.textContent = `${file.name} · ${(file.size / 1024).toFixed(1)}KB`;
            card.append(img, caption);
            preview.append(card);
        });
    }
    input.addEventListener('change', () => updateSelection());
    form.addEventListener('reset', () => updateSelection(true));
    window.addEventListener('pagehide', release);

    document.querySelectorAll('[data-product-image-status]').forEach(button => {
        button.addEventListener('click', async () => {
            const feedback = document.getElementById('product-image-status-feedback');
            button.disabled = true;
            try {
                const response = await fetch('/admin/update-image-status', {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({image_id: button.dataset.productImageStatus,
                        status: button.dataset.active === '1' ? 'Active' : 'Inactive'})
                });
                if (!response.ok) throw new Error('이미지 노출 상태를 변경하지 못했습니다. 새로고침 후 다시 시도해 주세요.');
                const data = await response.json();
                const active = Number(data.status) === 1;
                button.dataset.active = active ? '1' : '0';
                button.textContent = active ? '노출중' : '숨김';
                button.classList.toggle('primary', active);
                button.setAttribute('aria-label', `이미지 ${button.dataset.productImageStatus} ${active ? '숨김으로 변경' : '노출중으로 변경'}`);
                feedback.classList.remove('error');
                feedback.textContent = `이미지를 ${active ? '노출중' : '숨김'} 상태로 변경했습니다.`;
            } catch (err) {
                feedback.classList.add('error');
                feedback.textContent = err.message;
            } finally {
                feedback.hidden = false;
                button.disabled = false;
            }
        });
    });
})();
