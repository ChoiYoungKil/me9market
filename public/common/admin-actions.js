(() => {
    'use strict';
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (window.jQuery && token) {
        window.jQuery.ajaxSetup({headers: {'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest'}});
    }
    window.submitAdminDelete = action => {
        const target = new URL(action, location.origin);
        if (!token || target.origin !== location.origin || !target.pathname.startsWith('/admin/delete-')) {
            throw new Error('관리자 삭제 요청을 확인할 수 없습니다.');
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = target.href;
        form.hidden = true;
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = '_token';
        field.value = token;
        form.append(field);
        document.body.append(form);
        form.requestSubmit();
    };
})();
