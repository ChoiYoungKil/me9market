document.addEventListener('DOMContentLoaded', function () {
    if (!window.Quill) return;
    document.querySelectorAll('[data-product-inquiry-form]').forEach(function(form,index){
    var wrapper = form.querySelector('[data-product-inquiry-editor]');
    var textarea = wrapper.querySelector('textarea');
    var toolbar = wrapper.querySelector('[data-inquiry-toolbar]');
    var target = wrapper.querySelector('[data-inquiry-editor-body]');
    var error = wrapper.querySelector('[data-inquiry-editor-error]');
    toolbar.hidden = target.hidden = false;
    var editor = new Quill(target, {theme:'snow', placeholder:'문의 내용을 입력해 주세요.', formats:['bold','italic','underline','list','link'], modules:{toolbar:toolbar,history:{userOnly:true}}});
    var initial = wrapper.querySelector('template').innerHTML;
    editor.setContents(editor.clipboard.convert(initial));
    editor.history.clear();
    editor.root.setAttribute('role','textbox');
    editor.root.setAttribute('aria-label','질문 내용 편집기');
    var label = form.querySelector('[id*="message-label"]');
    if(label)editor.root.setAttribute('aria-labelledby',label.id);
    editor.root.setAttribute('aria-multiline','true');
    textarea.hidden = true;
    textarea.required = false;
    function sync() {
        var text = editor.getText().trim();
        textarea.value = text ? editor.root.innerHTML : '';
        error.hidden = true;
        textarea.dispatchEvent(new Event('input',{bubbles:true}));
    }
    editor.on('text-change', sync);
    form.addEventListener('submit',function(event){
        sync();
        var length = editor.getText().trim().length;
        if (!length || length > 3000) {
            event.preventDefault();
            error.textContent = '문의 내용을 1~3,000자로 입력해 주세요.';
            error.hidden = false;
            editor.focus();
        }
    });
    form.closest('dialog').addEventListener('close',function(){
        form.reset();
        editor.setContents(editor.clipboard.convert(initial));
        editor.history.clear();
        textarea.value = textarea.defaultValue;
        error.hidden = true;
    });
    });
});
