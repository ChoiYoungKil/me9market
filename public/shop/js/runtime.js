document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.shop-dialog form').forEach(function(form) {
        var reason=form.querySelector('[data-claim-reason]');
        var pickups=form.querySelectorAll('[data-claim-pickup]');
        function updateClaimFields(){
            if(reason){var other=form.querySelector('[data-other-reason]');other.hidden=reason.value!=='other';other.querySelector('textarea').required=!other.hidden;}
            if(pickups.length){var manual=form.querySelector('[name="pickup_method"]:checked')?.value==='manual';form.querySelector('[data-manual]').hidden=!manual;form.querySelector('[data-automatic]').hidden=manual;}
        }
        reason?.addEventListener('change',updateClaimFields);
        pickups.forEach(function(input){input.addEventListener('change',updateClaimFields);});
        updateClaimFields();
        form.closest('dialog').addEventListener('close',function(){form.reset();updateClaimFields();});
    });
    document.querySelectorAll('[data-privacy-expand]').forEach(function (button) {
        button.addEventListener('click', function () {
            var expanded = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(expanded));
            button.textContent = expanded ? '접어보기' : '전문 펼쳐보기';
            document.getElementById(button.getAttribute('aria-controls')).classList.toggle('expanded', expanded);
        });
    });
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
        dialog.querySelectorAll('[data-close]').forEach(function(button){button.addEventListener('click', function () { dialog.close(); });});
        if(dialog.hasAttribute('data-reset-on-close')) dialog.addEventListener('close',function(){dialog.querySelector('form')?.reset();});
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
    document.querySelectorAll('[data-option-toggle]').forEach(function(button) {
        button.addEventListener('click',function(){var expanded=button.getAttribute('aria-expanded')==='true';button.setAttribute('aria-expanded',String(!expanded));var panel=document.getElementById(button.getAttribute('aria-controls'));panel.classList.toggle('collapsed',expanded);panel.inert=expanded;button.querySelector('span').textContent=expanded?'⌄':'⌃';});
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
        var requested = tabs.find(function(tab){return '#'+tab.getAttribute('aria-controls') === window.location.hash;});
        if (requested) activate(requested);
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
        var gesture = null, suppressClickUntil = 0;
        carousel.querySelectorAll('img').forEach(function(img){img.draggable=false;});
        carousel.addEventListener('pointerdown', function (event) {
            if (event.button !== 0 || event.target.closest('button')) return;
            gesture = {x:event.clientX,y:event.clientY,id:event.pointerId};
        });
        window.addEventListener('pointerup', function (event) {
            if (!gesture || event.pointerId !== gesture.id) return;
            var dx=event.clientX-gesture.x, dy=event.clientY-gesture.y;
            if (Math.abs(dx)>40 && Math.abs(dx)>Math.abs(dy)) {
                show(current+(dx<0?1:-1));
                suppressClickUntil=Date.now()+500;
            }
            gesture=null;
        });
        window.addEventListener('pointercancel',function(){gesture=null;});
        carousel.addEventListener('click',function(event){if(Date.now()<suppressClickUntil){event.preventDefault();event.stopPropagation();}},true);
        pause.addEventListener('click', function () { paused = !paused; syncPause(); });
        syncPause();
        window.setInterval(function () {
            if (!paused && !document.hidden && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) show(current + 1);
        }, 5000);
    });
    document.querySelectorAll('[data-product-carousel]').forEach(function (group) {
        var track = group.querySelector('[data-product-track]');
        group.querySelector('[data-products-prev]').addEventListener('click', function () { track.scrollBy({left:-track.clientWidth * .8, behavior:'smooth'}); });
        group.querySelector('[data-products-next]').addEventListener('click', function () { track.scrollBy({left:track.clientWidth * .8, behavior:'smooth'}); });
    });
    document.querySelectorAll('.shop-periods').forEach(function(group){
        var form=group.closest('form'),buttons=Array.from(group.querySelectorAll('[data-period]'));
        function localDate(date){return date.getFullYear()+'-'+String(date.getMonth()+1).padStart(2,'0')+'-'+String(date.getDate()).padStart(2,'0');}
        function dates(months){var end=new Date(),start=new Date();start.setDate(1);start.setMonth(start.getMonth()-months);start.setDate(Math.min(end.getDate(),new Date(start.getFullYear(),start.getMonth()+1,0).getDate()));return [localDate(start),localDate(end)];}
        function sync(){buttons.forEach(function(button){var range=dates(Number(button.dataset.period));button.setAttribute('aria-pressed',String(form.elements.from.value===range[0] && form.elements.to.value===range[1]));});}
        buttons.forEach(function(button){button.addEventListener('click',function(){var range=dates(Number(button.dataset.period));form.elements.from.value=range[0];form.elements.to.value=range[1];sync();});});
        [form.elements.from,form.elements.to].forEach(function(input){input.addEventListener('input',sync);});sync();
    });
    document.querySelectorAll('[data-recipient-phone]').forEach(function (group) {
        var form = group.closest('form');
        form.querySelectorAll('[name^="recipient_phone_"]').forEach(function(input) {
            input.addEventListener('input', function () {
                form.elements.mobile.value = [form.elements.recipient_phone_prefix.value, form.elements.recipient_phone_middle.value, form.elements.recipient_phone_last.value].join('-');
                form.elements.mobile.dispatchEvent(new Event('input'));
            });
            input.addEventListener('change', function () { input.dispatchEvent(new Event('input')); });
        });
    });
    function setRecipientPhone(form, phone) {
        form.elements.mobile.value = phone || '';
        var digits = (phone || '').replace(/\D/g, '');
        form.elements.recipient_phone_prefix.value = digits.slice(0,3);
        form.elements.recipient_phone_middle.value = digits.slice(3,-4);
        form.elements.recipient_phone_last.value = digits.slice(-4);
    }
    document.querySelectorAll('[data-email-domain]').forEach(function(select) {
        select.addEventListener('change', function () {
            var input = select.closest('form').elements.recipient_email_domain;
            if (select.value) input.value = select.value;
            input.readOnly = !!select.value;
            input.dispatchEvent(new Event('input'));
        });
    });
    document.querySelectorAll('[data-copy-buyer]').forEach(function (checkbox) {
        var form = checkbox.closest('form');
        function copyBuyer() {
            if (!checkbox.checked) return;
            form.elements.name.value = form.elements.buyer_name.value;
            setRecipientPhone(form, form.elements.buyer_mobile.value);
            var email = form.elements.email.value.split('@');
            form.elements.recipient_email_local.value = email[0] || '';
            form.elements.recipient_email_domain.value = email[1] || '';
            form.querySelector('[data-email-domain]').value = '';
            form.elements.recipient_email_domain.readOnly = false;
        }
        checkbox.addEventListener('change', copyBuyer);
        form.elements.buyer_name.addEventListener('input', copyBuyer);
        form.elements.buyer_mobile.addEventListener('input', copyBuyer);
        [form.elements.name, form.elements.mobile, form.elements.recipient_email_local, form.elements.recipient_email_domain].forEach(function (input) {
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
            setRecipientPhone(form, address.mobile);
            form.querySelector('[data-copy-buyer]').checked = false;
            form.dispatchEvent(new Event('shipping-address-change'));
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
                        form.dispatchEvent(new Event('shipping-address-change'));
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
            form.querySelectorAll('[data-checkout-payable]').forEach(function(element){element.textContent = Math.max(0, total - used).toLocaleString('ko-KR') + '원';});
        }
        inputs.forEach(function (input) { input.addEventListener('input', updatePayment); });
        updatePayment();
        if (form.dataset.shippingQuote) {
            var version = 0, pendingQuote = null;
            var submit = form.querySelector('[data-checkout-submit]');
            var checkoutDisabled = submit.disabled;
            var message = form.querySelector('[data-shipping-message]');
            async function quote() {
                var current = ++version;
                submit.disabled = true;
                form.elements.order_confirmed.checked = false;
                if (!form.elements.address.value.trim() || !/^[0-9]{5}$/.test(form.elements.pincode.value)) {
                    message.textContent = '배송주소와 5자리 우편번호를 입력해 주세요.';
                    return;
                }
                message.textContent = '배송비를 확인하고 있습니다.';
                try {
                    var response = await fetch(form.dataset.shippingQuote, {
                        method: 'POST', headers: {'Accept':'application/json', 'X-CSRF-TOKEN':form.elements._token.value},
                        body: new FormData(form)
                    });
                    var data = await response.json();
                    if (current !== version) return;
                    if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || '배송비를 확인할 수 없습니다.');
                    form.dataset.checkoutTotal = data.total;
                    if(form.querySelector('[data-gross-total]'))form.querySelector('[data-gross-total]').textContent=Number(data.total).toLocaleString('ko-KR')+'원';
                    form.elements.expected_total.value = data.total;
                    inputs.forEach(function(input){if(input.dataset.pointBalance)input.max=Math.max(0,Math.min(Number(input.dataset.pointBalance),Math.floor(data.total)));});
                    if(form.querySelector('[data-equation-shipping]'))form.querySelector('[data-equation-shipping]').textContent=Number(data.shipping).toLocaleString('ko-KR')+'원';
                    if(form.querySelector('[data-equation-total]'))form.querySelector('[data-equation-total]').textContent=Number(data.total).toLocaleString('ko-KR')+'원';
                    form.querySelectorAll('[data-item-shipping]').forEach(function (element) {
                        element.textContent = Number(data.shipping_by_item[element.dataset.itemShipping] || 0).toLocaleString('ko-KR') + '원';
                    });
                    form.querySelectorAll('[data-shipping-group]').forEach(function (group) {
                        var fee = Number(data.shipping_by_product[group.dataset.shippingGroup] || 0);
                        group.querySelector('[data-group-shipping]').textContent = fee.toLocaleString('ko-KR')+'원';
                        group.querySelector('[data-group-total]').textContent = (Number(group.dataset.groupSubtotal)+fee).toLocaleString('ko-KR')+'원';
                    });
                    updatePayment();
                    message.textContent = '배송비를 반영했습니다. 최종 결제금액을 확인해 주세요.';
                    submit.disabled = checkoutDisabled;
                } catch (error) {
                    if (current === version) message.textContent = error.message;
                }
            }
            function scheduleQuote() {
                ++version;
                submit.disabled = true;
                clearTimeout(pendingQuote);
                pendingQuote = setTimeout(quote, 300);
            }
            ['address','pincode'].forEach(function (name) { form.elements[name].addEventListener('input', scheduleQuote); });
            form.addEventListener('shipping-address-change', scheduleQuote);
            quote();
        }
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
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-category-filter]').forEach(function(form) {
        var tree = JSON.parse(form.querySelector('[data-category-tree]').textContent);
        form.querySelectorAll('[data-category-level]').forEach(function(select) {
            select.addEventListener('change', function() {
                var level = Number(select.dataset.categoryLevel);
                for (var next = level + 1; next <= 3; next++) {
                    var child = form.querySelector('[data-category-level="'+next+'"]');
                    child.replaceChildren(new Option('전체',''));
                    if (next === level + 1 && select.value) tree.filter(function(c){return Number(c.parent_id) === Number(select.value);}).forEach(function(c){child.add(new Option(c.category_name,c.id));});
                }
            });
        });
    });
});
document.addEventListener('DOMContentLoaded',function(){
    var form=document.querySelector('[data-cart-totals-url]');if(!form)return;
    var version=0;var feedback=document.querySelector('[data-cart-feedback]');
    async function totals(){var current=++version;try{var response=await fetch(form.dataset.cartTotalsUrl,{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':form.elements._token.value},body:new FormData(form)});var data=await response.json();if(current!==version)return;if(!response.ok)throw new Error('선택 상품 금액을 확인할 수 없습니다.');['subtotal','shipping','total'].forEach(function(key){document.querySelector('[data-cart-'+key+']').textContent=Number(data[key]).toLocaleString('ko-KR')+'원';});document.querySelector('[data-cart-quantity]').textContent=Number(data.quantity);document.querySelector('[data-cart-bar-total]').textContent=Number(data.total).toLocaleString('ko-KR')+'원';feedback.textContent='';}catch(error){if(current===version)feedback.textContent=error.message;}}
    document.querySelectorAll('.shop-cart-select,[data-select-all=".shop-cart-select"]').forEach(function(input){input.addEventListener('change',totals);});
    form.addEventListener('submit',function(event){if(!document.querySelector('.shop-cart-select:checked')){event.preventDefault();feedback.textContent='주문하거나 삭제할 상품을 선택해 주세요.';}});
    totals();
});
document.addEventListener('DOMContentLoaded',function(){
    var form=document.querySelector('[data-mock-checkout]'),dialog=document.getElementById('shop-mock-payment');if(!form||!dialog)return;var approved=false;
    form.addEventListener('submit',function(event){if(event.submitter&&event.submitter.formAction!==form.action)return;if(approved){approved=false;form.querySelector('[data-checkout-submit]').disabled=true;return;}event.preventDefault();dialog.querySelector('[data-mock-method]').textContent=form.elements.payment_method.value==='BankTransfer'?'실시간계좌이체':'신용카드';dialog.querySelector('[data-mock-amount]').textContent=form.querySelector('[data-checkout-payable]').textContent;dialog.showModal();});
    dialog.querySelector('[data-mock-confirm]').addEventListener('click',function(){dialog.close();approved=true;form.requestSubmit(form.querySelector('[data-checkout-submit]'));});
});
