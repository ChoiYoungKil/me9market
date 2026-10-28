(() => {
    'use strict';
    if (!['127.0.0.1', 'localhost', '[::1]'].includes(location.hostname)) return;
    let version = null;
    let documentVersion = null;
    let styleVersion = null;
    let waiting = false;
    let dirty = document.body?.dataset.previewPreserve === 'true';
    let stopped = false;
    document.addEventListener('input', event => { if (event.target.closest('form')) dirty = true; }, {capture:true});
    document.addEventListener('change', event => { if (event.target.closest('form')) dirty = true; }, {capture:true});
    async function refreshStyles(next) {
        const links = [...document.querySelectorAll('link[rel="stylesheet"][href]')]
            .filter(link => new URL(link.href).origin === location.origin);
        await Promise.all(links.map(link => new Promise((resolve, reject) => {
            const replacement = link.cloneNode();
            const url = new URL(link.href);
            url.searchParams.set('__preview', next);
            replacement.href = url.href;
            replacement.onload = () => { link.remove(); resolve(); };
            replacement.onerror = () => { replacement.remove(); reject(new Error('Stylesheet refresh failed')); };
            link.after(replacement);
        })));
    }
    async function check() {
        if (waiting || stopped || document.hidden) return;
        waiting = true;
        try {
            const response = await fetch('/__preview/version', {cache:'no-store', credentials:'same-origin'});
            if (response.status === 404 || response.status === 403) { stopped = true; return; }
            if (!response.ok) return;
            const data = await response.json();
            const next = data.version;
            if (typeof next !== 'string') return;
            if (typeof data.documentVersion === 'string' && typeof data.styleVersion === 'string') {
                if (styleVersion !== null && styleVersion !== data.styleVersion) {
                    await refreshStyles(data.styleVersion);
                }
                styleVersion = data.styleVersion;
                if (documentVersion !== null && documentVersion !== data.documentVersion && !dirty) location.reload();
                if (documentVersion === null) documentVersion = data.documentVersion;
            } else if (version !== null && version !== next && !dirty) {
                location.reload();
            }
            if (version === null) version = next;
        } catch (_) { /* Keep the preview usable while the local server restarts. */ }
        finally { waiting = false; }
    }
    check();
    window.setInterval(check, 1500);
})();
