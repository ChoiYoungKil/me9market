(() => {
    const workspace = document.querySelector('[data-refund-active-form]');
    if (!workspace) return;
    const dialogs = [...document.querySelectorAll('.refund-dialog')];
    const editors = new WeakMap();
    function ensureEditor(form) {
        if (!form || editors.has(form) || !window.Quill) return;
        const wrapper = form.querySelector('[data-refund-editor]');
        const toolbar = wrapper.querySelector('[data-refund-toolbar]');
        const target = wrapper.querySelector('[data-refund-editor-body]');
        const textarea = wrapper.querySelector('textarea');
        toolbar.hidden = false;
        target.hidden = false;
        const editor = new Quill(target, {
            theme:'snow', placeholder:'취소/환불안내 내용을 입력해 주세요.',
            formats:['header','bold','italic','underline','strike','list','blockquote','link'],
            modules:{toolbar:{container:toolbar}, history:{userOnly:true}}
        });
        editor.setContents(editor.clipboard.convert(wrapper.querySelector('template').innerHTML));
        editor.history.clear();
        editor.root.setAttribute('role','textbox');
        editor.root.setAttribute('aria-label','취소/환불안내 내용 편집기');
        editor.root.setAttribute('aria-multiline','true');
        textarea.hidden = true;
        editor.on('text-change', () => { textarea.value = editor.getText().trim() ? editor.root.innerHTML : ''; });
        form.addEventListener('submit', () => { textarea.value = editor.getText().trim() ? editor.root.innerHTML : ''; });
        editors.set(form, editor);
    }
    dialogs.forEach(dialog => {
        dialog.querySelectorAll('[data-refund-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => {
            const form = dialog.querySelector('form');
            form?.reset();
            const editor = form && editors.get(form);
            if (editor) {
                const textarea = form.querySelector('textarea[name="content"]');
                editor.setContents(editor.clipboard.convert(form.querySelector('[data-refund-editor-initial]').innerHTML));
                editor.history.clear();
                textarea.value = textarea.defaultValue;
            }
            if (workspace.dataset.refundActiveForm && dialog.id === `refund-${workspace.dataset.refundActiveForm}`) window.location.assign(window.location.pathname);
        });
    });
    document.querySelectorAll('[data-refund-open]').forEach(button => button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.refundOpen);
        dialog?.showModal();
        ensureEditor(dialog?.querySelector('form'));
    }));
    document.querySelectorAll('[data-refund-delete]').forEach(form => form.addEventListener('submit', event => {
        if (!window.confirm('취소/환불안내를 삭제하시겠습니까? 삭제 후 복원할 수 없습니다.')) event.preventDefault();
    }));
    const view = document.getElementById('refund-view');
    const body = view.querySelector('[data-refund-details]');
    let version = 0;
    view.addEventListener('close', () => { version++; body.textContent=''; });
    document.querySelectorAll('[data-refund-view]').forEach(link => link.addEventListener('click', async event => {
        event.preventDefault();
        const current = ++version;
        body.textContent = '안내 정보를 불러오고 있습니다.';
        view.showModal();
        try {
            const response = await fetch(link.href, {headers:{'Accept':'text/html'}});
            if (!response.ok || response.redirected) throw new Error();
            const html = await response.text();
            if (current === version) body.innerHTML=html;
        } catch { if (current === version) body.textContent='안내를 불러올 수 없습니다. 창을 닫고 새로고침한 뒤 다시 확인해 주세요.'; }
    }));
    if (workspace.dataset.refundActiveForm) {
        const dialog = document.getElementById(`refund-${workspace.dataset.refundActiveForm}`);
        dialog?.showModal();
        ensureEditor(dialog?.querySelector('form'));
    }
})();
