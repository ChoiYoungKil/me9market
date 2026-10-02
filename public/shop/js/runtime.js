document.addEventListener('DOMContentLoaded', function () {
    var searchButton = document.querySelector('.search_btn');
    if (searchButton) searchButton.addEventListener('click', function () {
        var expanded = searchButton.getAttribute('aria-expanded') === 'true';
        searchButton.setAttribute('aria-expanded', String(!expanded));
        searchButton.classList.toggle('on', !expanded);
        document.getElementById('shop-search').style.display = expanded ? '' : 'block';
    });
    document.querySelectorAll('[data-dialog]').forEach(function (button) {
        button.addEventListener('click', function () { document.getElementById(button.dataset.dialog).showModal(); });
    });
    document.querySelectorAll('.shop-dialog').forEach(function (dialog) {
        dialog.querySelector('[data-close]').addEventListener('click', function () { dialog.close(); });
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                var rect = dialog.getBoundingClientRect();
                if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
            }
        });
    });
    document.querySelectorAll('[data-pickup]').forEach(function (select) {
        function update() { select.closest('form').querySelector('[data-manual]').hidden = select.value !== 'manual'; }
        select.addEventListener('change', update);
        update();
    });
    document.querySelectorAll('[data-select-all]').forEach(function (checkbox) {
        var items = Array.from(document.querySelectorAll(checkbox.dataset.selectAll)).filter(function (item) { return !item.disabled; });
        function syncSelection() {
            checkbox.checked = items.length > 0 && items.every(function (item) { return item.checked; });
            checkbox.indeterminate = !checkbox.checked && items.some(function (item) { return item.checked; });
        }
        checkbox.addEventListener('change', function () { items.forEach(function (item) { item.checked = checkbox.checked; }); });
        items.forEach(function (item) {
            item.addEventListener('change', function () {
                syncSelection();
            });
        });
        syncSelection();
    });
    document.querySelectorAll('[data-gallery-image]').forEach(function (button) {
        button.addEventListener('click', function () { document.getElementById('product-main-image').src = button.dataset.galleryImage; });
    });
    document.querySelectorAll('[data-stepper]').forEach(function (stepper) {
        var input = stepper.querySelector('input');
        stepper.querySelectorAll('[data-step]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (input.disabled) return;
                if (Number(button.dataset.step) > 0) input.stepUp(); else input.stepDown();
                input.dispatchEvent(new Event('input', {bubbles: true}));
            });
        });
    });
    document.querySelectorAll('[data-price]').forEach(function (form) {
        var quantity = form.querySelector('[name="qty"]');
        var selections = form.querySelector('[data-option-selections]');
        var optionSelect = form.querySelector('[name="option"]');
        function selectedPrice() {
            return Number(form.dataset.price) + Number(optionSelect.selectedOptions[0].dataset.priceAdjustment || 0);
        }
        var nextIndex = 0;
        function updateTotal() {
            var selectedInputs = selections.querySelectorAll('input[type="number"]');
            var total = selectedInputs.length ? Array.from(selectedInputs).reduce(function (sum, input) { return sum + Number(input.dataset.unitPrice) * Math.max(0, Number(input.value)); }, 0) : selectedPrice() * Math.max(0, Number(quantity.value));
            form.querySelector('[data-price-total]').textContent = total.toLocaleString('ko-KR') + '원';
            var unit = form.querySelector('[data-option-unit-price]');
            if (unit) unit.textContent = selectedPrice().toLocaleString('ko-KR') + '원';
        }
        var addOption = form.querySelector('[data-add-option]');
        if (addOption) addOption.addEventListener('click', function () {
            var option = form.querySelector('[name="option"]').value;
            var existing = Array.from(selections.children).find(function (row) { return row.dataset.option === option; });
            if (existing) { existing.querySelector('input[type="number"]').focus(); return; }
            if (selections.children.length >= 20) return;
            var index = nextIndex++;
            var row = document.createElement('div');
            row.className = 'shop-option-row';
            row.dataset.option = option;
            var label = document.createElement('label');
            var title = document.createElement('span');
            title.textContent = option;
            var optionInput = document.createElement('input');
            optionInput.type = 'hidden';
            optionInput.name = 'options[' + index + '][option]';
            optionInput.value = option;
            var count = document.createElement('input');
            count.className = 'shop-control';
            count.type = 'number';
            count.name = 'options[' + index + '][qty]';
            count.min = '1';
            count.max = quantity.max;
            count.value = quantity.value;
            count.dataset.unitPrice = selectedPrice();
            count.required = true;
            label.append(title, count);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'shop-dialog-close';
            remove.textContent = '×';
            remove.title = option + ' 삭제';
            remove.setAttribute('aria-label', option + ' 삭제');
            row.append(optionInput, label, remove);
            selections.append(row);
            var baseRow = quantity.closest('.shop-option-row');
            baseRow.hidden = true;
            quantity.disabled = true;
            count.addEventListener('input', updateTotal);
            remove.addEventListener('click', function () {
                row.remove();
                if (!selections.children.length) { baseRow.hidden = false; quantity.disabled = false; }
                updateTotal();
            });
            updateTotal();
        });
        if (selections) {
            selections.addEventListener('input', updateTotal);
        }
        quantity.addEventListener('input', updateTotal);
        optionSelect.addEventListener('change', updateTotal);
        updateTotal();
    });
    document.querySelectorAll('[data-tabs]').forEach(function (group) {
        var tabs = Array.from(group.querySelectorAll('[role="tab"]'));
        function activate(tab) {
            tabs.forEach(function (candidate) {
                var selected = candidate === tab;
                candidate.setAttribute('aria-selected', String(selected));
                candidate.tabIndex = selected ? 0 : -1;
                document.getElementById(candidate.getAttribute('aria-controls')).hidden = !selected;
            });
        }
        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab); });
            tab.addEventListener('keydown', function (event) {
                var next = event.key === 'ArrowRight' ? (index + 1) % tabs.length : event.key === 'ArrowLeft' ? (index + tabs.length - 1) % tabs.length : event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : null;
                if (next === null) return;
                event.preventDefault();
                activate(tabs[next]);
                tabs[next].focus();
            });
        });
    });
    document.querySelectorAll('[data-carousel]').forEach(function (carousel) {
        var slides = Array.from(carousel.querySelectorAll('[data-slide]'));
        if (slides.length < 2) return;
        var current = 0;
        var pause = carousel.querySelector('[data-slide-pause]');
        var paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function show(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach(function (slide, i) { slide.hidden = i !== current; });
            carousel.querySelector('[data-slide-count]').textContent = (current + 1) + ' / ' + slides.length;
        }
        function syncPause() {
            pause.classList.toggle('paused', paused);
            pause.setAttribute('aria-label', paused ? '자동 재생 시작' : '자동 재생 일시정지');
            pause.title = pause.getAttribute('aria-label');
        }
        carousel.querySelector('[data-slide-prev]').addEventListener('click', function () { show(current - 1); });
        carousel.querySelector('[data-slide-next]').addEventListener('click', function () { show(current + 1); });
        pause.addEventListener('click', function () { paused = !paused; syncPause(); });
        syncPause();
        window.setInterval(function () {
            if (!paused && !document.hidden && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) show(current + 1);
        }, 5000);
    });
    document.querySelectorAll('[data-period]').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = button.closest('form');
            var end = new Date();
            var start = new Date();
            start.setMonth(start.getMonth() - Number(button.dataset.period));
            function localDate(date) {
                return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
            }
            form.querySelector('[name="from"]').value = localDate(start);
            form.querySelector('[name="to"]').value = localDate(end);
        });
    });
    document.querySelectorAll('[data-copy-buyer]').forEach(function (checkbox) {
        var form = checkbox.closest('form');
        function copyBuyer() {
            if (!checkbox.checked) return;
            form.elements.name.value = form.elements.buyer_name.value;
            form.elements.mobile.value = form.elements.buyer_mobile.value;
        }
        checkbox.addEventListener('change', copyBuyer);
        form.elements.buyer_name.addEventListener('input', copyBuyer);
        form.elements.buyer_mobile.addEventListener('input', copyBuyer);
        [form.elements.name, form.elements.mobile].forEach(function (input) {
            input.addEventListener('input', function () { checkbox.checked = false; });
        });
    });
    document.querySelectorAll('[data-delivery-address]').forEach(function (select) {
        select.addEventListener('change', function () {
            var data = select.selectedOptions[0].dataset.address;
            if (!data) return;
            var address = JSON.parse(data);
            var form = select.closest('form');
            Object.keys(address).forEach(function (key) { form.elements[key].value = address[key] || ''; });
            form.querySelector('[data-copy-buyer]').checked = false;
        });
    });
    document.querySelectorAll('[data-postcode]').forEach(function (button) {
        button.addEventListener('click', function () {
            var dialog = document.getElementById('postcode-dialog');
            var container = dialog.querySelector('[data-postcode-container]');
            var form = button.closest('form');
            dialog.showModal();
            function openPostcode() {
                if (!dialog.open) return;
                container.textContent = '';
                new window.daum.Postcode({
                    width:'100%', height:420,
                    oncomplete:function (data) {
                        form.elements.pincode.value = data.zonecode;
                        form.elements.address.value = data.userSelectedType === 'R' ? data.roadAddress : data.jibunAddress;
                        form.elements.state.value = data.sido || '';
                        form.elements.city.value = data.sigungu || '';
                        dialog.close();
                        form.elements.address.focus();
                    }
                }).embed(container);
            }
            if (window.daum && window.daum.Postcode) { openPostcode(); return; }
            var script = document.createElement('script');
            script.src = 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js';
            script.onload = openPostcode;
            script.onerror = function () { container.textContent = '주소 검색을 불러오지 못했습니다. 닫은 후 주소를 직접 입력해 주세요.'; };
            document.head.appendChild(script);
        });
    });
    document.querySelectorAll('[data-checkout-total]').forEach(function (form) {
        var inputs = Array.from(form.querySelectorAll('[data-point-use]'));
        function updatePayment() {
            var total = Number(form.dataset.checkoutTotal);
            var used = inputs.reduce(function (sum, input) { return sum + Math.max(0, Number(input.value) || 0); }, 0);
            inputs.forEach(function (input) { input.setCustomValidity(used > total ? '사용 포인트는 결제금액을 초과할 수 없습니다.' : ''); });
            form.querySelector('[data-used-points]').textContent = used.toLocaleString('ko-KR') + 'P';
            form.querySelector('[data-checkout-payable]').textContent = Math.max(0, total - used).toLocaleString('ko-KR') + '원';
        }
        inputs.forEach(function (input) { input.addEventListener('input', updatePayment); });
        updatePayment();
    });
    var otpButton = document.getElementById('requestOtp');
    if (otpButton) {
        otpButton.addEventListener('click', async function () {
            var message = document.getElementById('otp-message');
            otpButton.disabled = true;
            try {
                var response = await fetch(otpButton.dataset.url, {
                    method:'POST',
                    headers:{'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                    body:JSON.stringify({entry_code:document.getElementById('entry_code').value, phone:document.getElementById('phone').value})
                });
                var data = await response.json();
                message.textContent = data.message || '인증번호 발송에 실패했습니다.';
                if (response.ok) document.getElementById('otp').focus();
            } catch (error) { message.textContent = '인증번호 발송에 실패했습니다. 다시 시도해 주세요.'; }
            finally { otpButton.disabled = false; }
        });
    }
});
