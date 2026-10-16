document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.portal-menu-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const open = document.body.classList.toggle('portal-menu-open');
            button.setAttribute('aria-expanded', String(open));
        });
    });
    document.querySelectorAll('.portal #container_w table').forEach(function (table) {
        if (table.closest('.tb01, .table-responsive, .delivery-policy-form, .portal-table-scroll')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'portal-table-scroll';
        table.before(wrapper);
        wrapper.appendChild(table);
    });
});
