document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-shop-login]');
    if (!form) return;
    var id = form.querySelector('[name="login_id"]');
    var save = form.querySelector('[data-save-id]');
    var key = 'me9.shop.saved-login-id';
    try {
        var saved = localStorage.getItem(key);
        if (saved && !id.value) { id.value = saved; save.checked = true; }
    } catch (error) { /* Login remains usable when browser storage is unavailable. */ }
    save.addEventListener('change', function () { if (!save.checked) { try { localStorage.removeItem(key); } catch (error) {} } });
    form.addEventListener('submit', function () {
        try { if (save.checked) localStorage.setItem(key, id.value); else localStorage.removeItem(key); } catch (error) {}
    });
});
