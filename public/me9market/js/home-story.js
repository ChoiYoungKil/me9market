(() => {
    'use strict';
    const stage = document.querySelector('.home-stage');
    if (!stage) return;
    const panels = Array.from(stage.querySelectorAll('.home-panel'));
    const sectionNav = Array.from(document.querySelectorAll('.home-section-nav [data-home-go]'));
    const count = document.querySelector('[data-home-count]');
    const toggle = document.querySelector('.home-menu-toggle');
    const menu = document.getElementById('home-menu');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0;
    let busy = false;
    let motionTimer;
    let wheelTimer;
    let wheelUsed = false;
    let wheelTotal = 0;
    let touch = null;
    let menuOpen = false;

    function replaceHash(id) {
        try { window.history.replaceState(null, '', '#' + id); } catch (_) { /* Navigation still works without history access. */ }
    }
    function show(index, options = {}) {
        index = Math.max(0, Math.min(panels.length - 1, index));
        if (index === current && !options.initial) return;
        const previous = panels[current];
        const restoreFocus = !options.initial && previous.contains(document.activeElement);
        const next = panels[index];
        document.body.dataset.homeDirection = index > current ? 'down' : 'up';
        panels.forEach((panel, i) => {
            panel.style.setProperty('--home-panel-position', (i < index ? '-100%' : i > index ? '100%' : '0%'));
            panel.classList.toggle('is-active', i === index);
            panel.inert = i !== index;
            panel.setAttribute('aria-hidden', String(i !== index));
        });
        current = index;
        next.scrollTop = 0;
        document.body.dataset.homeTheme = next.dataset.homeTheme;
        sectionNav.forEach(button => {
            if (button.dataset.homeGo === next.id) button.setAttribute('aria-current', 'true');
            else button.removeAttribute('aria-current');
        });
        if (count) count.textContent = String(index + 1).padStart(2, '0');
        if (!options.initial || window.location.hash) replaceHash(next.id);
        if (restoreFocus || options.focus) {
            const heading = next.querySelector('h1,h2');
            heading.setAttribute('tabindex', '-1');
            heading.focus({ preventScroll: true });
        }
        clearTimeout(motionTimer);
        busy = !options.initial && !reduced.matches;
        motionTimer = window.setTimeout(() => { busy = false; }, reduced.matches ? 0 : 800);
        stage.dispatchEvent(new CustomEvent('home:section', { detail: { id: next.id, index } }));
    }
    function canScroll(panel, direction) {
        if (panel.scrollHeight <= panel.clientHeight + 2) return false;
        return direction > 0 ? panel.scrollTop + panel.clientHeight < panel.scrollHeight - 2 : panel.scrollTop > 2;
    }
    function editable(target) {
        return Boolean(target.closest('input,textarea,select,[contenteditable="true"]'));
    }
    function setMenu(open, restore = false) {
        menuOpen = open;
        document.body.classList.toggle('is-menu-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.sr-only').textContent = open ? '전체 메뉴 닫기' : '전체 메뉴 열기';
        stage.inert = open;
        document.querySelector('.home-section-nav').inert = open;
        if (open) menu.querySelector('a').focus();
        else if (restore) toggle.focus();
    }

    const hashIndex = panels.findIndex(panel => '#' + panel.id === window.location.hash);
    document.documentElement.classList.add('has-home-motion');
    show(hashIndex >= 0 ? hashIndex : 0, { initial: true });
    toggle.addEventListener('click', () => setMenu(!menuOpen));
    menu.addEventListener('click', event => {
        if (event.target.closest('a') && menuOpen) setMenu(false);
    });
    document.querySelectorAll('[data-home-go]').forEach(button => {
        button.addEventListener('click', () => {
            const index = panels.findIndex(panel => panel.id === button.dataset.homeGo);
            if (index >= 0) show(index);
        });
    });
    window.addEventListener('hashchange', () => {
        const index = panels.findIndex(panel => '#' + panel.id === window.location.hash);
        if (index >= 0) show(index);
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1000 && menuOpen) setMenu(false);
    });

    document.addEventListener('wheel', event => {
        if (event.ctrlKey || event.metaKey || Math.abs(event.deltaX) > Math.abs(event.deltaY) || editable(event.target) || menuOpen) return;
        const delta = event.deltaY * (event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? window.innerHeight : 1);
        if (!delta || canScroll(panels[current], Math.sign(delta))) return;
        event.preventDefault();
        clearTimeout(wheelTimer);
        wheelTimer = window.setTimeout(() => { wheelUsed = false; wheelTotal = 0; }, 180);
        if (busy || wheelUsed) return;
        wheelTotal += delta;
        if (Math.abs(wheelTotal) >= 42) {
            wheelUsed = true;
            show(current + Math.sign(wheelTotal));
        }
    }, { passive: false });

    stage.addEventListener('touchstart', event => {
        if (event.touches.length !== 1 || editable(event.target)) { touch = null; return; }
        touch = { x: event.touches[0].clientX, y: event.touches[0].clientY, scroll: panels[current].scrollTop, canMove: false };
    }, { passive: true });
    stage.addEventListener('touchmove', event => {
        if (!touch || event.touches.length !== 1 || menuOpen) return;
        const dy = touch.y - event.touches[0].clientY;
        const dx = touch.x - event.touches[0].clientX;
        if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 12 && !canScroll(panels[current], Math.sign(dy)) && Math.abs(panels[current].scrollTop - touch.scroll) < 6) {
            touch.canMove = true;
            event.preventDefault();
        }
    }, { passive: false });
    stage.addEventListener('touchend', event => {
        if (!touch || !event.changedTouches.length) return;
        const dy = touch.y - event.changedTouches[0].clientY;
        const dx = touch.x - event.changedTouches[0].clientX;
        if (!busy && !menuOpen && touch.canMove && Math.abs(dy) > 55 && Math.abs(dy) > Math.abs(dx)) show(current + Math.sign(dy));
        touch = null;
    }, { passive: true });
    stage.addEventListener('touchcancel', () => { touch = null; }, { passive: true });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menuOpen) { setMenu(false, true); return; }
        if (menuOpen && event.key === 'Tab') {
            const focusable = [toggle, ...menu.querySelectorAll('a,button')].filter(el => el.getClientRects().length);
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            return;
        }
        if (menuOpen || event.altKey || event.ctrlKey || event.metaKey || editable(event.target) || event.target.closest('button,a,[role="tab"],[role="tabpanel"]')) return;
        const keys = { ArrowDown: current + 1, PageDown: current + 1, ' ': current + 1, ArrowUp: current - 1, PageUp: current - 1, Home: 0, End: panels.length - 1 };
        if (Object.prototype.hasOwnProperty.call(keys, event.key)) {
            event.preventDefault();
            if (!busy || event.key === 'Home' || event.key === 'End') show(keys[event.key], { focus: true });
        }
    });

    const guideTabs = Array.from(document.querySelectorAll('[data-guide]'));
    function selectGuide(tab, focus = false) {
        guideTabs.forEach(button => {
            const selected = button === tab;
            button.setAttribute('aria-selected', String(selected));
            button.tabIndex = selected ? 0 : -1;
            document.getElementById(button.getAttribute('aria-controls')).hidden = !selected;
        });
        if (focus) tab.focus();
    }
    guideTabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectGuide(tab));
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') next = (index + 1) % guideTabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = guideTabs.length - 1;
            if (next !== undefined) { event.preventDefault(); selectGuide(guideTabs[next], true); }
        });
    });
})();
