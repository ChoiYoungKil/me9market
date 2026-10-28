(() => {
    'use strict';
    const form = document.querySelector('[data-member-form]');
    if (!form) return;
    let dirty = false, busy = false;
    const readonlyDefaults = new Map([...form.querySelectorAll('input')].map(input => [input, input.readOnly]));
    const feedback = document.querySelector('.member-feedback');
    const show = (message, error = false) => {
        feedback.hidden = false;
        feedback.dataset.error = String(error);
        feedback.textContent = message;
        feedback.focus({preventScroll:true});
        feedback.scrollIntoView({behavior:'smooth',block:'center'});
    };
    const errors = data => Object.values(data.errors || {}).flat().join('\n') || data.message || '저장하지 못했습니다. 입력 내용을 확인해 주세요.';
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('change', () => { dirty = true; });
    document.querySelectorAll('[data-step-link]').forEach(link => link.addEventListener('click', event => {
        if (dirty && !confirm('저장하지 않은 내용은 사라집니다. 이동하시겠습니까?')) event.preventDefault();
    }));
    form.querySelector('[data-cancel]').addEventListener('click', () => {
        if (busy || !confirm('수정한 내용을 취소하고 마지막 저장 상태로 되돌리시겠습니까?')) return;
        form.reset();
        readonlyDefaults.forEach((readonly, input) => { input.readOnly = readonly; });
        const all = form.querySelector('[data-consent-all]');
        if (all) {
            const items = [...form.querySelectorAll('[data-consent-item]')];
            const checked = items.filter(input => input.checked).length;
            all.checked = checked === items.length;
            all.indeterminate = checked > 0 && checked < items.length;
        }
        dirty = false;
        form.querySelectorAll('[type=file]').forEach(input => {
            document.querySelector('[data-file-name="'+input.name+'"]').textContent = 'JPG, PNG, PDF · 최대 5MB';
        });
        show('수정이 취소되었습니다. 마지막으로 저장한 정보는 유지됩니다.');
    });
    form.querySelectorAll('[type=file]').forEach(input => input.addEventListener('change', () => {
        document.querySelector('[data-file-name="'+input.name+'"]').textContent = input.files[0]?.name || '선택한 파일이 없습니다.';
    }));
    form.querySelector('[data-email-domain]')?.addEventListener('change', event => {
        const domain = form.querySelector('[name=email_domain],[name=email_2]');
        domain.value = event.target.value;
        domain.readOnly = !!event.target.value;
        if (!event.target.value) domain.focus();
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !form.reportValidity()) return;
        const action = event.submitter?.value || 'save';
        if (action === 'request' && !confirm('판매 권한 인증을 요청하시겠습니까? 제출한 서류를 검토한 후 권한이 부여됩니다.')) return;
        const data = new FormData(form);
        data.set('action', action);
        if (form.querySelector('[name=email_prefix]')) data.set('email', data.get('email_prefix')+'@'+data.get('email_domain'));
        busy = true;
        form.querySelectorAll('button').forEach(button => { button.disabled = true; });
        try {
            const response = await fetch(form.getAttribute('action'), {method:'POST',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:data});
            const result = await response.json();
            if (!response.ok || result.status !== 'success') { show(errors(result), true); return; }
            dirty = false;
            if (action === 'next') location.assign(form.dataset.nextUrl);
            else if (action === 'request') location.assign(result.redirect_url);
            else {
                sessionStorage.setItem('me9-registration-saved', result.message);
                location.assign(location.pathname);
            }
        } catch (_) { show('서버에 연결하지 못했습니다. 입력한 내용을 유지했습니다. 다시 시도해 주세요.', true); }
        finally { busy = false; form.querySelectorAll('button').forEach(button => { button.disabled = false; }); }
    });
    const saved = sessionStorage.getItem('me9-registration-saved');
    if (saved) { sessionStorage.removeItem('me9-registration-saved'); show(saved); }
    document.querySelectorAll('[data-dialog-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelector('[data-identity]')?.addEventListener('click', () => document.querySelector('#member-info-dialog').showModal());
    form.querySelector('[data-manual-address]')?.addEventListener('click', () => {
        form.querySelector('#zipcode').readOnly = false;
        form.querySelector('#address1').readOnly = false;
        form.querySelector('#zipcode').focus();
    });
    form.querySelector('[data-postcode]')?.addEventListener('click', async () => {
        try {
            if (!window.daum?.Postcode) await new Promise((resolve,reject) => {
                const script = document.createElement('script');
                script.src = 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js';
                script.onload = resolve; script.onerror = reject; document.head.append(script);
            });
            const dialog = document.querySelector('#member-postcode-dialog');
            const container = dialog.querySelector('#member-postcode');
            container.replaceChildren();
            dialog.showModal();
            new daum.Postcode({width:'100%',height:'100%',oncomplete:address => {
                form.querySelector('#zipcode').value = address.zonecode;
                form.querySelector('#address1').value = address.userSelectedType === 'R' ? address.roadAddress : address.jibunAddress;
                dirty = true;
                dialog.close();
                form.querySelector('#address2').focus();
            }}).embed(container);
        } catch (_) { show('우편번호 서비스를 불러오지 못했습니다. 주소 직접입력 버튼을 이용해 주세요.', true); }
    });
    const passwordDialog = document.querySelector('#passwordChangeModal');
    document.querySelector('#btnChangePassword')?.addEventListener('click', () => passwordDialog.showModal());
    passwordDialog?.addEventListener('close', () => { passwordDialog.querySelector('form').reset(); passwordDialog.querySelector('[data-password-feedback]').textContent = ''; });
    passwordDialog?.querySelector('form').addEventListener('submit', async event => {
        event.preventDefault();
        const passwordForm = event.currentTarget, status = passwordForm.querySelector('[data-password-feedback]');
        const data = new FormData(passwordForm);
        if (data.get('current_password') === data.get('new_password')) { status.textContent = '현재 비밀번호와 다른 비밀번호를 입력해 주세요.'; return; }
        if (data.get('new_password') !== data.get('confirm_password')) { status.textContent = '새로운 비밀번호와 비밀번호 확인이 일치하지 않습니다.'; return; }
        const button = passwordForm.querySelector('[type=submit]'); button.disabled = true;
        try {
            const response = await fetch(passwordForm.action, {method:'POST',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:data});
            const result = await response.json();
            if (response.ok && result.type === 'success') { passwordDialog.close(); show('비밀번호가 변경되었습니다.'); }
            else status.textContent = errors(result);
        } catch (_) { status.textContent = '연결에 실패했습니다. 다시 시도해 주세요.'; }
        finally { button.disabled = false; }
    });
})();
