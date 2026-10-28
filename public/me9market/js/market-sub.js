(() => {
    'use strict';
    const button = document.querySelector('.home-menu-toggle');
    const menu = document.getElementById('home-menu');
    const content = document.getElementById('market-content');
    const footer = document.querySelector('.market-site-footer');
    let open = false;
    function setMenu(next, restore = false) {
        open = next;
        document.body.classList.toggle('is-menu-open', open);
        button.setAttribute('aria-expanded', String(open));
        button.querySelector('.sr-only').textContent = open ? '전체 메뉴 닫기' : '전체 메뉴 열기';
        content.inert = open;
        footer.inert = open;
        if (open) menu.querySelector('a').focus();
        else if (restore) button.focus();
    }
    button.addEventListener('click', () => setMenu(!open));
    window.addEventListener('resize', () => { if (window.innerWidth > 1000 && open) setMenu(false); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && open) { setMenu(false, true); return; }
        if (event.key !== 'Tab' || !open) return;
        const items = [button, ...menu.querySelectorAll('a,button')].filter(el => el.getClientRects().length);
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    document.querySelectorAll('[data-market-top]').forEach(el => el.addEventListener('click', () => {
        window.scrollTo({top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
    }));
    document.querySelectorAll('[data-password-toggle]').forEach(el => el.addEventListener('click', () => {
        const input = document.getElementById(el.getAttribute('aria-controls'));
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        el.textContent = show ? '숨기기' : '표시';
        el.setAttribute('aria-pressed', String(show));
        el.setAttribute('aria-label', show ? '비밀번호 숨기기' : '비밀번호 표시');
    }));
    document.querySelectorAll('.market-reg-domain-select select').forEach(select => {
        const label = select.parentElement.querySelector('[data-domain-label]');
        const update = () => { label.textContent = select.selectedOptions[0].textContent; };
        select.addEventListener('change', update);
        update();
    });
    document.querySelectorAll('[data-consent-group]').forEach(group => {
        const all = group.querySelector('[data-consent-all]');
        if (!all) return;
        const items = [...group.querySelectorAll('[data-consent-item]')].filter(input => !input.disabled);
        const update = () => {
            const count = items.filter(input => input.checked).length;
            all.checked = items.length > 0 && count === items.length;
            all.indeterminate = count > 0 && count < items.length;
        };
        all.addEventListener('change', () => { items.forEach(input => { input.checked = all.checked; }); update(); });
        items.forEach(input => input.addEventListener('change', update));
        update();
    });
})();
