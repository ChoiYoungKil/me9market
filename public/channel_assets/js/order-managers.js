(() => {
    document.querySelectorAll('[data-manager-open]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.managerOpen)?.showModal()));
    document.querySelectorAll('[data-manager-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelectorAll('[data-manager-form]').forEach(form => {
        const field = name => form.elements.namedItem(name);
        const feedback = form.querySelector('[data-email-feedback]');
        const original = form.dataset.originalEmail;
        const initialToken = field('email_check_token').value;
        const email = () => (field('email_local').value.trim() + '@' + field('email_domain').value.trim()).toLowerCase();
        let version = 0;
        const clearCheck = () => {
            version++;
            field('email_check_token').value = '';
            feedback.textContent = original && original === email() ? '기존 이메일입니다. 중복확인 없이 수정할 수 있습니다.' : '이메일 중복확인을 진행해 주세요.';
        };
        ['email_local','email_domain'].forEach(name => field(name).addEventListener('input',clearCheck));
        const domain = form.querySelector('[data-email-domain]');
        domain.addEventListener('change', () => {
            if (domain.value) field('email_domain').value = domain.value;
            field('email_domain').readOnly = Boolean(domain.value);
            clearCheck();
        });
        form.querySelector('[data-check-email]').addEventListener('click', async event => {
            if (!field('email_local').reportValidity() || !field('email_domain').reportValidity()) return;
            clearCheck();
            const requestVersion = version;
            const button = event.currentTarget;
            const checkedEmail = email();
            button.disabled = true;
            feedback.textContent = '이메일을 확인하고 있습니다.';
            try {
                const response = await fetch(button.dataset.url,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({email:checkedEmail,manager_id:field('manager_id').value || null})});
                const result = await response.json();
                if (requestVersion !== version || email() !== checkedEmail) return;
                if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || '중복확인에 실패했습니다. 새로고침 후 다시 진행해 주세요.');
                feedback.textContent = result.message;
                field('email_check_token').value = result.available ? result.token : '';
            } catch (error) { if (requestVersion === version) feedback.textContent = error.message; }
            finally { button.disabled = false; }
        });
        form.addEventListener('submit', event => {
            if (email() !== original && !field('email_check_token').value) {
                event.preventDefault();
                feedback.textContent = '이메일 중복확인을 완료해 주세요.';
                field('email_local').focus();
            }
        });
        form.addEventListener('invalid', event => { const details=event.target.closest('details'); if (details) details.open=true; },true);
        form.closest('dialog').addEventListener('close', () => {
            version++;
            form.reset();
            field('email_check_token').value = initialToken;
            field('email_domain').readOnly = false;
            feedback.textContent = original ? '이메일을 변경하지 않으면 중복확인을 다시 하지 않아도 됩니다.' : '이메일 입력 후 중복확인을 진행해 주세요.';
        });
        form.querySelector('[data-manager-postcode]').addEventListener('click', () => {
            if (!window.daum?.Postcode) { alert('주소 검색을 불러오지 못했습니다. 우편번호와 주소를 직접 입력하거나 잠시 후 다시 시도해 주세요.'); return; }
            const dialog=document.getElementById('manager-postcode');
            const widget=document.getElementById('manager-postcode-widget');
            widget.replaceChildren();
            dialog.showModal();
            new window.daum.Postcode({oncomplete:data=>{
                field('dispatch_postcode').value=data.zonecode;
                field('dispatch_address').value=data.userSelectedType==='R'?data.roadAddress:data.jibunAddress;
                dialog.close();field('dispatch_address_detail').focus();
            },width:'100%',height:'100%'}).embed(widget);
        });
    });
    const view=document.getElementById('manager-view');
    const body=view.querySelector('[data-manager-detail-body]');
    let detailVersion=0;
    const loadDetails=async url=>{
        const version=++detailVersion;
        body.textContent='발주담당 정보를 불러오고 있습니다.';
        try {
            const response=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}});
            if (!response.ok) throw new Error('상세 정보를 불러오지 못했습니다. 다시 시도해 주세요.');
            const html=await response.text();
            if (version!==detailVersion) return;
            body.innerHTML=html;
        } catch(error){if(version===detailVersion)body.textContent=error.message;}
    };
    view.addEventListener('close',()=>{detailVersion++;body.replaceChildren();});
    document.querySelectorAll('[data-manager-view]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();view.showModal();loadDetails(link.href);}));
    body.addEventListener('click',event=>{const link=event.target.closest('[data-manager-detail-pagination] a');if(link){event.preventDefault();loadDetails(link.href);}});
    const active=document.querySelector('[data-manager-active-form]')?.dataset.managerActiveForm;
    if(active)document.getElementById('manager-'+active)?.showModal();
})();
