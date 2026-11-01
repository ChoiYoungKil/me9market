(() => {
    'use strict';
    const form = document.getElementById('banner-editor-form');
    if (!form) return;
    const input = document.getElementById('image');
    const filename = document.getElementById('banner-image-filename');
    const preview = document.getElementById('banner-image-preview');
    const image = document.getElementById('banner-preview-image');
    const caption = document.getElementById('banner-preview-caption');
    const error = document.getElementById('banner-image-error');
    let objectUrl;
    function update(reset = false) {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = undefined;
        const file = reset ? null : input.files[0];
        filename.value = file ? file.name : '';
        const message = file && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ? 'JPG, PNG, WEBP 이미지 파일을 선택해 주세요.' :
            file && file.size > 5 * 1024 * 1024 ? '이미지는 5MB 이하로 선택해 주세요.' : '';
        input.setCustomValidity(message);
        error.textContent = message;
        error.hidden = !message;
        if (file && !message) {
            objectUrl = URL.createObjectURL(file);
            image.src = objectUrl;
            preview.hidden = false;
            caption.textContent = `선택한 이미지 · ${file.name}`;
        } else {
            const original = preview.dataset.currentSrc;
            if (original) image.src = original;
            else image.removeAttribute('src');
            preview.hidden = !original;
            caption.textContent = '등록된 이미지';
        }
    }
    input.addEventListener('change', () => update());
    form.addEventListener('reset', () => update(true));
    window.addEventListener('pagehide', event => {
        if (!event.persisted && objectUrl) URL.revokeObjectURL(objectUrl);
    });
})();
