(() => {
    'use strict';

    function start() {
        if (!window.CKEDITOR) return;
        keepFloatingUiInDialog();

        document.querySelectorAll('textarea[data-admin-editor]').forEach(textarea => {
            if (CKEDITOR.instances[textarea.id] || !textarea.getClientRects().length) return;

            // Keep the editor identical to channel product registration's description.
            const editor = CKEDITOR.replace(textarea.id, {
                height: 200,
                language: 'ko',
                versionCheck: false,
                toolbar: [
                    {name: 'document', items: ['Source', '-', 'Preview']},
                    {name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
                    {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']},
                    {name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']},
                    {name: 'links', items: ['Link', 'Unlink']},
                    {name: 'insert', items: ['Table', 'HorizontalRule']},
                    {name: 'styles', items: ['Format', 'Font', 'FontSize']},
                    {name: 'colors', items: ['TextColor', 'BGColor']},
                    {name: 'tools', items: ['Maximize']}
                ]
            });
            editor.on('change', () => editor.updateElement());
        });
    }

    let dialogCompatibilityInstalled = false;
    function keepFloatingUiInDialog() {
        if (dialogCompatibilityInstalled) return;
        dialogCompatibilityInstalled = true;
        const append = CKEDITOR.dom.element.prototype.append;
        // Insert floating UI in the native dialog before CKEditor fills its iframe.
        // Moving an already populated iframe afterwards would erase its document.
        CKEDITOR.dom.element.prototype.append = function (node, toStart) {
            const element = node && node.$;
            const dialog = document.querySelector('dialog[open]');
            const floating = element && element.classList &&
                ['cke_panel', 'cke_dialog_container', 'cke_dialog_background_cover'].some(name => element.classList.contains(name));
            if (dialog && this.$ === document.body && floating) {
                if (element.classList.contains('cke_panel')) {
                    element.style.setProperty('position', 'fixed', 'important');
                    element.style.translate = `${-window.scrollX}px ${-window.scrollY}px`;
                }
                return append.call(new CKEDITOR.dom.element(dialog), node, toStart);
            }
            return append.call(this, node, toStart);
        };
    }

    window.initAdminEditors = start;
    document.addEventListener('DOMContentLoaded', start);
    document.addEventListener('click', event => {
        if (event.target.closest('[data-dialog-open]')) setTimeout(start, 0);
    });
    // Run before the form's Ajax handler constructs its FormData, including Source mode.
    document.addEventListener('submit', () => {
        if (!window.CKEDITOR) return;
        Object.values(CKEDITOR.instances).forEach(editor => editor.updateElement());
    }, true);
})();
