(() => {
    const close = button => button.closest('dialog')?.close();
    document.querySelectorAll('[data-dialog-open]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.dialogOpen)?.showModal()));
    document.querySelectorAll('[data-dialog-close]').forEach(button => button.addEventListener('click', () => close(button)));
    document.querySelectorAll('[data-subaccount-form]').forEach(form => {
        const field = name => form.elements.namedItem(name);
        const message = form.querySelector('[data-member-message]');
        const results = form.querySelector('[data-member-results]');
        const initialMember = Array.from(form.querySelectorAll('[data-member-field]')).map(node => [node, node.textContent]);
        const initialMemberId = field('member_id').value;
        const initialMemberToken = field('member_token').value;
        const hadMember = Boolean(field('member_token').value);
        let lookupVersion = 0;
        form.closest('dialog').addEventListener('close', () => {
            lookupVersion++;
            form.reset();
            field('member_id').value = initialMemberId;
            field('member_token').value = initialMemberToken;
            results.replaceChildren();
            message.textContent = '';
            initialMember.forEach(([node, value]) => node.textContent = value);
            form.querySelector('[data-member-info]').hidden = !hadMember;
            form.querySelector('[data-member-placeholder]').hidden = hadMember;
        });
        const clear = () => {
            lookupVersion++;
            field('member_id').value = '';
            field('member_token').value = '';
            results.replaceChildren();
            form.querySelector('[data-member-info]').hidden = true;
            form.querySelector('[data-member-placeholder]').hidden = false;
            message.textContent = '';
        };
        field('member_keyword').addEventListener('input', clear);
        field('search_type').addEventListener('change', clear);
        form.querySelector('[data-member-search]').addEventListener('click', async event => {
            clear();
            const version = lookupVersion;
            const keyword = field('member_keyword').value.trim();
            if (!keyword) { message.textContent = '회원 검색어를 입력해 주세요.'; return; }
            const button = event.currentTarget;
            button.disabled = true;
            message.textContent = '회원을 찾고 있습니다.';
            try {
                const response = await fetch(button.dataset.url, {method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({search_type:field('search_type').value,keyword})});
                const data = await response.json();
                if (version !== lookupVersion) return;
                if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || '회원찾기에 실패했습니다. 새로고침 후 다시 진행해 주세요.');
                message.textContent = data.message || '검색된 회원을 확인하고 선택해 주세요.';
                data.members.forEach(member => {
                    const select = document.createElement('button');
                    select.type = 'button';
                    select.textContent = `${member.member_no} / ${member.login_id} / ${member.name} · 이 회원 선택`;
                    select.addEventListener('click', () => {
                        field('member_id').value = member.id;
                        field('member_token').value = member.token;
                        form.querySelectorAll('[data-member-field]').forEach(node => node.textContent = member[node.dataset.memberField]);
                        form.querySelector('[data-member-info]').hidden = false;
                        form.querySelector('[data-member-placeholder]').hidden = true;
                        results.replaceChildren();
                        message.textContent = '회원이 선택되었습니다.';
                    });
                    results.append(select);
                });
            } catch (error) { if (version === lookupVersion) message.textContent = error.message; }
            finally { button.disabled = false; }
        });
        form.addEventListener('submit', event => {
            if (!field('member_id').value || !field('member_token').value) {
                event.preventDefault();
                message.textContent = '회원찾기로 회원을 선택한 후 저장해 주세요.';
                field('member_keyword').focus();
            }
        });
    });
    const key = document.querySelector('[data-active-form]')?.dataset.activeForm;
    if (key) document.getElementById('subaccount-' + key)?.showModal();
})();
